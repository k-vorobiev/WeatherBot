<?php

use Scrimmy\Weather\Service\TelegramService;

require_once 'vendor/autoload.php';

echo (new TelegramService())->setWebhook();
