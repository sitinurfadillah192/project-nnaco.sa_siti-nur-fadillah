<?php
/**
 * AI Provider Interface
 * Contract for all AI engine implementations in NNACOSA.
 */
interface AiProviderInterface {
    /**
     * Call AI engine for regular conversational or plain text output
     *
     * @param string $systemPrompt
     * @param string $userMessage
     * @param array $options Optional overrides (e.g. max_tokens, temperature)
     * @return array ['success' => bool, 'content' => string] or ['error' => string]
     */
    public function chatCompletion(string $systemPrompt, string $userMessage, array $options = []): array;

    /**
     * Call AI engine expecting structured JSON output
     *
     * @param string $systemPrompt
     * @param string $userMessage
     * @param array $options Optional overrides
     * @return array ['success' => bool, 'content' => string] or ['error' => string]
     */
    public function jsonCompletion(string $systemPrompt, string $userMessage, array $options = []): array;

    /**
     * Test connection to the AI provider endpoint with current credentials & model
     *
     * @return array [
     *   'success' => bool,
     *   'latency_ms' => int,
     *   'status' => string (e.g. 'CONNECTED', 'AUTH_FAILED', 'MODEL_UNAVAILABLE', 'TIMEOUT', 'ERROR'),
     *   'message' => string,
     *   'model' => string
     * ]
     */
    public function testConnection(): array;

    /**
     * Get Provider Identifier
     *
     * @return string
     */
    public function getProviderName(): string;

    /**
     * Get Configured Model ID
     *
     * @return string
     */
    public function getModelId(): string;
}
