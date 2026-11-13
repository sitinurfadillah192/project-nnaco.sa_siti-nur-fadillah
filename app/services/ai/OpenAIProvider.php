<?php
/**
 * OpenAI Official Provider Adapter
 */

require_once __DIR__ . '/OpenAICompatibleProvider.php';

class OpenAIProvider extends OpenAICompatibleProvider {
    public function __construct(array $config) {
        if (empty($config['base_url'])) {
            $config['base_url'] = 'https://api.openai.com/v1/chat/completions';
        }
        $config['provider'] = 'openai';
        parent::__construct($config);
    }
}
