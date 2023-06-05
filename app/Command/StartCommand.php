<?php

namespace Scrimmy\Weather\Command;

use Scrimmy\Weather\Interface\CommandInterface;

class StartCommand implements CommandInterface
{
    public function handle($bot, $chatId, $data = null)
    {
        $bot->sendMessage($chatId, 'Для использования бота используйте команду /weather <Город>');
    }
}