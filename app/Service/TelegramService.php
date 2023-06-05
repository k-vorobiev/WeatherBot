<?php

namespace Scrimmy\Weather\Service;

use Symfony\Component\Dotenv\Dotenv;

class TelegramService
{
    public string $token;
    public string $url;

    public function __construct()
    {
        (new Dotenv())->load('.env');

        $this->token = $_ENV['TELEGRAMBOT_TOKEN'];
        $this->url = 'https://api.telegram.org/bot' . $this->token . '/';
    }

    public function setWebhook(bool $destroy = false)
    {
        if ($_SERVER['HTTP_HOST'] == 'localhost') {
            die('Нельзя установить локальный адресс в качестве вебхука');
        }

        $setHookUrl = 'https://api.telegram.org/bot' . $this->token . '/' . 'setWebhook';
        $url = !$destroy ? 'https://' . $_SERVER['HTTP_HOST'] . '/' . 'index.php' : '';
        $request = $setHookUrl . '?url=' . $url;

        return file_get_contents($request);
    }

    public function sendMessage(string $chatId, string $message, array $headers = [])
    {
        $bot['chat_id'] = $chatId;
        $bot['text'] = !empty($message) ? $message : '';
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_POST => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $this->url . 'sendMessage',
            CURLOPT_POSTFIELDS => $bot,
        ]);

        curl_exec($ch);
        curl_close($ch);
    }

    public function sendPhoto(string $chatId, string $photo, array $headers = [])
    {
        $data['chat_id'] = $chatId;
        if (!empty($photo)) {
            $data['photo'] = curl_file_create($photo, 'image/webp', 'result.webp');
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_POST => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $this->url . 'sendPhoto',
            CURLOPT_POSTFIELDS => $data,
        ]);

        curl_exec($ch);
        curl_close($ch);
    }
}