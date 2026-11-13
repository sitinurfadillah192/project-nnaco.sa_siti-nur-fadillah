<?php
/**
 * OpenAI Compatible AI Provider Adapter
 * 
 * Supports OpenAI, NVIDIA NIM, Groq, Ollama, DeepSeek, LocalAI, vLLM, etc.
 */

require_once __DIR__ . '/AiProviderInterface.php';
require_once dirname(__DIR__, 2) . '/core/Security.php';

class OpenAICompatibleProvider implements AiProviderInterface {
    protected string $providerName;
    protected string $baseUrl;
    protected ?string $apiKey;
    protected string $modelId;
    protected int $timeout;
    protected int $maxTokens;
    protected float $temperature;

    /**
     * @param array $config Configuration dictionary
     */
    public function __construct(array $config) {
        $this->providerName = $config['provider'] ?? 'openai_compatible';
        $this->baseUrl = trim($config['base_url'] ?? 'https://api.openai.com/v1/chat/completions');
        $this->apiKey = $config['api_key'] ?? null;
        $this->modelId = trim($config['model_id'] ?? 'gpt-4o-mini');
        
        $params = is_array($config['parameters'] ?? null) 
            ? $config['parameters'] 
            : (json_decode($config['parameters_json'] ?? '{}', true) ?: []);

        $this->timeout = (int)($params['timeout'] ?? 35);
        $this->maxTokens = (int)($params['max_tokens'] ?? 800);
        $this->temperature = (float)($params['temperature'] ?? 0.5);
    }

    public function getProviderName(): string {
        return $this->providerName;
    }

    public function getModelId(): string {
        return $this->modelId;
    }

    public function chatCompletion(string $systemPrompt, string $userMessage, array $options = []): array {
        $maxTokens = $options['max_tokens'] ?? $this->maxTokens;
        $temperature = $options['temperature'] ?? $this->temperature;

        $payload = [
            'model' => $this->modelId,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userMessage]
            ],
            'max_tokens' => $maxTokens,
            'temperature' => $temperature
        ];

        return $this->sendRequest($payload);
    }

    public function jsonCompletion(string $systemPrompt, string $userMessage, array $options = []): array {
        $maxTokens = $options['max_tokens'] ?? $this->maxTokens;
        $temperature = $options['temperature'] ?? 0.3;

        $payload = [
            'model' => $this->modelId,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt . "\nRespond strictly in valid JSON format."],
                ['role' => 'user', 'content' => $userMessage]
            ],
            'max_tokens' => $maxTokens,
            'temperature' => $temperature,
            'response_format' => ['type' => 'json_object']
        ];

        $res = $this->sendRequest($payload);

        // If response_format caused an error (some older/alternative models don't support it), retry without response_format
        if (isset($res['error']) && (stripos($res['error'], 'response_format') !== false || stripos($res['error'], 'json_object') !== false)) {
            unset($payload['response_format']);
            $res = $this->sendRequest($payload);
        }

        return $res;
    }

    public function testConnection(): array {
        $startTime = microtime(true);

        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'latency_ms' => 0,
                'status' => 'AUTH_FAILED',
                'message' => 'API Key belum dikonfigurasi atau kosong.',
                'model' => $this->modelId
            ];
        }

        // Validate outbound URL against SSRF
        $ssrfCheck = Security::validateOutboundUrl($this->baseUrl);
        if (!$ssrfCheck['valid']) {
            return [
                'success' => false,
                'latency_ms' => 0,
                'status' => 'SSRF_BLOCKED',
                'message' => 'Validasi URL gagal: ' . $ssrfCheck['error'],
                'model' => $this->modelId
            ];
        }

        // Lightweight test payload
        $payload = [
            'model' => $this->modelId,
            'messages' => [
                ['role' => 'user', 'content' => 'Ping. Return pong.']
            ],
            'max_tokens' => 100,
            'temperature' => 0.1
        ];

        $ch = curl_init($this->baseUrl);
        $jsonPayload = json_encode($payload);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
        curl_setopt($ch, CURLOPT_TIMEOUT, min($this->timeout, 20)); // Capped at 20s for interactive test
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For local dev
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);

        $rawResponse = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        $endTime = microtime(true);
        $latencyMs = (int)round(($endTime - $startTime) * 1000);

        if ($curlErr) {
            return [
                'success' => false,
                'latency_ms' => $latencyMs,
                'status' => 'NETWORK_ERROR',
                'message' => 'Gagal menghubungi server AI: ' . $curlErr,
                'model' => $this->modelId
            ];
        }

        $decoded = json_decode($rawResponse, true);

        if ($httpCode === 401 || $httpCode === 403) {
            return [
                'success' => false,
                'latency_ms' => $latencyMs,
                'status' => 'AUTH_FAILED',
                'message' => 'Autentikasi gagal (HTTP ' . $httpCode . '). Periksa kembali API Key Anda.',
                'model' => $this->modelId
            ];
        }

        if ($httpCode === 404) {
            return [
                'success' => false,
                'latency_ms' => $latencyMs,
                'status' => 'MODEL_UNAVAILABLE',
                'message' => 'Endpoint tidak ditemukan atau Model "' . $this->modelId . '" tidak tersedia pada provider ini (HTTP 404).',
                'model' => $this->modelId
            ];
        }

        if ($httpCode >= 500) {
            return [
                'success' => false,
                'latency_ms' => $latencyMs,
                'status' => 'PROVIDER_DOWN',
                'message' => 'Provider AI mengalami gangguan server internal (HTTP ' . $httpCode . ').',
                'model' => $this->modelId
            ];
        }

        if (isset($decoded['error'])) {
            $errMessage = is_array($decoded['error']) ? ($decoded['error']['message'] ?? 'Unknown API error') : $decoded['error'];
            return [
                'success' => false,
                'latency_ms' => $latencyMs,
                'status' => 'API_ERROR',
                'message' => 'Error dari API Provider: ' . $errMessage,
                'model' => $this->modelId
            ];
        }

        if (isset($decoded['choices'][0]['message'])) {
            return [
                'success' => true,
                'latency_ms' => $latencyMs,
                'status' => 'CONNECTED',
                'message' => 'Koneksi berhasil! Provider merespons dalam ' . $latencyMs . ' ms dengan model ' . $this->modelId . '.',
                'model' => $this->modelId
            ];
        }

        return [
            'success' => false,
            'latency_ms' => $latencyMs,
            'status' => 'INVALID_RESPONSE',
            'message' => 'Format respons dari provider tidak dikenali (HTTP ' . $httpCode . ').',
            'model' => $this->modelId
        ];
    }

    protected function sendRequest(array $payload): array {
        // Validate URL against SSRF
        $ssrfCheck = Security::validateOutboundUrl($this->baseUrl);
        if (!$ssrfCheck['valid']) {
            return ['error' => 'Keamanan: URL AI Provider ditolak (' . $ssrfCheck['error'] . ')'];
        }

        $ch = curl_init($this->baseUrl);
        $jsonPayload = json_encode($payload);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . ($this->apiKey ?? '')
        ]);

        $rawResponse = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            return ['error' => 'cURL Error: ' . $curlErr];
        }

        $decoded = json_decode($rawResponse, true);

        if ($httpCode !== 200) {
            if (isset($decoded['error'])) {
                $msg = is_array($decoded['error']) ? ($decoded['error']['message'] ?? 'API Error') : $decoded['error'];
                return ['error' => "HTTP {$httpCode}: {$msg}"];
            }
            return ['error' => "HTTP {$httpCode}: Server provider gagal merespons dengan benar."];
        }

        if (isset($decoded['choices'][0]['message'])) {
            $msg = $decoded['choices'][0]['message'];
            $content = trim($msg['content'] ?? '');
            if (empty($content) && !empty($msg['reasoning_content'])) {
                $content = trim($msg['reasoning_content']);
            }
            // Strip markdown fences
            if (preg_match('/^```(?:json)?\s*([\s\S]*?)\s*```$/i', $content, $matches)) {
                $content = trim($matches[1]);
            }
            return ['success' => true, 'content' => $content];
        }

        return ['error' => 'Format respons AI tidak valid.'];
    }
}
