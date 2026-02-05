<?php
/**
 * Запрос к DeepSeek Chat API.
 */
class DeepSeek
{
    private const URL = 'https://api.deepseek.com/v1/chat/completions';

    public function __construct(private string $apiKey)
    {
    }

    public function interpretDream(string $dreamText): string
    {
        $systemPrompt = defined('DEEPSEEK_SYSTEM_PROMPT') ? DEEPSEEK_SYSTEM_PROMPT : 'Ты толкователь снов. Дай краткую и понятную расшифровку сна на русском языке. Пиши по делу, без лишних вступлений.';
        $body = [
            'model' => 'deepseek-chat',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $systemPrompt,
                ],
                [
                    'role' => 'user',
                    'content' => $dreamText,
                ],
            ],
        ];

        $ch = curl_init(self::URL);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($body),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey,
            ],
            CURLOPT_TIMEOUT => 60,
        ]);
        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code !== 200) {
            throw new RuntimeException("DeepSeek API error: $code " . substr($response, 0, 500));
        }

        $data = json_decode($response, true);
        $content = $data['choices'][0]['message']['content'] ?? null;
        if ($content === null) {
            throw new RuntimeException("DeepSeek: no content in response");
        }
        return trim($content);
    }
}
