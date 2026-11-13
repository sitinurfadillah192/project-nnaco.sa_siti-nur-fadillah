<?php
/**
 * Centralized AI Service for NNACOSA
 * 
 * Dynamically resolves active AI provider configuration from database (AES-256-GCM decrypted),
 * delegates to provider adapters (OpenAI, NVIDIA NIM, OpenAI-Compatible),
 * and provides seamless legacy fallback.
 */

require_once dirname(__DIR__) . '/services/ai/AiProviderInterface.php';
require_once dirname(__DIR__) . '/services/ai/OpenAICompatibleProvider.php';
require_once dirname(__DIR__) . '/services/ai/OpenAIProvider.php';
require_once dirname(__DIR__) . '/models/AiProviderConfig.php';

class AiService {
    private ?AiProviderInterface $provider = null;
    private ?array $activeConfig = null;

    public function __construct(?array $overrideConfig = null) {
        if ($overrideConfig !== null) {
            $this->initProvider($overrideConfig);
            return;
        }

        // 1. Attempt to load active configuration from database
        try {
            $configModel = new AiProviderConfig();
            $this->activeConfig = $configModel->getActive();
        } catch (Throwable $e) {
            $this->activeConfig = null;
        }

        if ($this->activeConfig && !empty($this->activeConfig['base_url'])) {
            $this->initProvider([
                'provider' => $this->activeConfig['provider'],
                'base_url' => $this->activeConfig['base_url'],
                'api_key' => $this->activeConfig['plain_key'] ?? '',
                'model_id' => $this->activeConfig['model_id'],
                'parameters' => $this->activeConfig['parameters'] ?? []
            ]);
            return;
        }

        // 2. Fallback to legacy environment constants if no DB config active
        $legacyKey = defined('AI_API_KEY') && !empty(AI_API_KEY) 
            ? AI_API_KEY 
            : (defined('OPENAI_API_KEY') && !empty(OPENAI_API_KEY) && OPENAI_API_KEY !== 'sk-YOUR_OPENAI_API_KEY_HERE' 
                ? OPENAI_API_KEY 
                : '');

        $this->initProvider([
            'provider' => 'openai_compatible',
            'base_url' => 'https://integrate.api.nvidia.com/v1/chat/completions',
            'api_key' => $legacyKey,
            'model_id' => 'nvidia/nemotron-3.5-bolt-e4b',
            'parameters' => [
                'timeout' => 20,
                'max_tokens' => 800,
                'temperature' => 0.5
            ]
        ]);
    }

    /**
     * Initialize appropriate adapter based on provider type
     */
    private function initProvider(array $cfg): void {
        $type = strtolower($cfg['provider'] ?? 'openai_compatible');

        switch ($type) {
            case 'openai':
                $this->provider = new OpenAIProvider($cfg);
                break;
            case 'openai_compatible':
            default:
                $this->provider = new OpenAICompatibleProvider($cfg);
                break;
        }
    }

    /**
     * Call AI Engine for standard chat text
     */
    public function chatCompletion(string $systemPrompt, string $userMessage, array $options = []): array {
        if (!$this->provider) {
            return ['error' => 'AI Provider tidak tersedia atau belum dikonfigurasi.'];
        }
        return $this->provider->chatCompletion($systemPrompt, $userMessage, $options);
    }
    
    /**
     * Call AI Engine and force JSON response format
     */
    public function jsonCompletion(string $systemPrompt, string $userMessage, array $options = []): array {
        if (!$this->provider) {
            return ['error' => 'AI Provider tidak tersedia atau belum dikonfigurasi.'];
        }
        return $this->provider->jsonCompletion($systemPrompt, $userMessage, $options);
    }

    /**
     * Test connection to current active provider
     */
    public function testConnection(): array {
        if (!$this->provider) {
            return [
                'success' => false,
                'latency_ms' => 0,
                'status' => 'UNCONFIGURED',
                'message' => 'Provider AI belum dikonfigurasi.',
                'model' => ''
            ];
        }
        return $this->provider->testConnection();
    }

    /**
     * Get active adapter instance
     */
    public function getProvider(): ?AiProviderInterface {
        return $this->provider;
    }

    /**
     * Get configured model identifier
     */
    public function getModelId(): string {
        if ($this->provider) {
            return $this->provider->getModelId();
        }
        return $this->activeConfig['model_id'] ?? 'meta/llama-3.2-11b-vision-instruct';
    }

    /**
     * Get active configuration info (without plaintext secrets)
     */
    public function getActiveConfig(): ?array {
        if (!$this->activeConfig) {
            return null;
        }
        $safe = $this->activeConfig;
        unset($safe['plain_key']);
        unset($safe['encrypted_api_key']);
        return $safe;
    }
}
