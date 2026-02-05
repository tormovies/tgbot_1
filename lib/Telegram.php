<?php
/**
 * Вызовы Telegram Bot API.
 */
class Telegram
{
    private string $base = 'https://api.telegram.org/bot';

    public function __construct(private string $token)
    {
    }

    private function request(string $method, array $params = [], bool $get = false): array
    {
        $url = $this->base . $this->token . '/' . $method;
        if ($get && $params !== []) {
            $url .= '?' . http_build_query($params);
        }
        $ch = curl_init($url);
        $opts = [CURLOPT_RETURNTRANSFER => true];
        if ($get) {
            $opts[CURLOPT_HTTPGET] = true;
        } else {
            $opts[CURLOPT_POST] = true;
            $opts[CURLOPT_POSTFIELDS] = json_encode($params);
            $opts[CURLOPT_HTTPHEADER] = ['Content-Type: application/json'];
        }
        curl_setopt_array($ch, $opts);
        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code !== 200) {
            throw new RuntimeException("Telegram API error: $code $body");
        }
        $data = json_decode($body, true);
        if (!$data['ok']) {
            throw new RuntimeException("Telegram API: " . ($data['description'] ?? $body));
        }
        return $data;
    }

    /** Long polling: получить обновления. $offset — следующий update_id. */
    public function getUpdates(?int $offset = null, int $timeout = 30): array
    {
        $params = ['timeout' => $timeout];
        if ($offset !== null) {
            $params['offset'] = $offset;
        }
        $result = $this->request('getUpdates', $params, true);
        return $result['result'] ?? [];
    }

    /** Отправить сообщение в чат (личка или группа). $parseMode — '' (текст) или 'HTML'. */
    public function sendMessage(int $chatId, string $text, string $parseMode = ''): array
    {
        $params = ['chat_id' => $chatId, 'text' => $text];
        if ($parseMode !== '') {
            $params['parse_mode'] = $parseMode;
        }
        return $this->request('sendMessage', $params);
    }
}
