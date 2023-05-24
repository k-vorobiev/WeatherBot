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

    public function sendMessage(array $data, array $headers = [])
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_POST => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $this->url . 'sendMessage',
            CURLOPT_POSTFIELDS => $data,
        ]);

        curl_exec($ch);
        curl_close($ch);
    }

    public function sendPhoto(array $data, array $headers = [])
    {
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

    public function getUserCommand($message)
    {
        $command = '';
        $text = '';
        $lenght = strlen($message);

        $delimiter = strpos($message, ' ');

        if ($delimiter) {
            $command = substr($message, 0, $delimiter);
            $text = substr($message, $delimiter + 1, $lenght - 1);
        } else {
            $command = $message;
        }

        return array(
            'command' => $command,
            'text' => $text,
        );
    }
}