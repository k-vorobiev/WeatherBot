<?php

namespace Scrimmy\Weather\Interface;

use Scrimmy\Weather\Service\TelegramService;

interface CommandInterface
{
    public function handle(TelegramService $bot, string $chatId, ?string $data);
}