<?php

namespace Scrimmy\Weather\Command;

use Scrimmy\Weather\Service\TelegramService;

class StartCommand
{
    protected string $message = "Для использования бота, напишите /погода <Город>";
    public TelegramService $telegram;

    public function __construct()
    {
        $this->telegram = new TelegramService();
    }

    public function message($data): string
    {
        $data['text'] = $this->message;
        return $this->telegram->sendMessage($data);
    }
}