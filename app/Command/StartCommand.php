<?php

namespace Scrimmy\Weather\Command;

use Scrimmy\Weather\Service\TelegramService;

class StartCommand
{
    public TelegramService $telegram;

    public function __construct()
    {
        $this->telegram = new TelegramService();
    }

    public function handle($data): string
    {
        $data['text'] = "Для использования бота, напишите /погода <Город>";
        return $this->telegram->sendMessage($data);
    }
}