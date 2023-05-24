<?php

use Scrimmy\Weather\Command\StartCommand;
use Scrimmy\Weather\Command\WeatherCommand;
use Scrimmy\Weather\Service\TelegramService;

require_once 'vendor/autoload.php';

const ABSPATH = __DIR__;

$telegram = new TelegramService();
$data = json_decode(file_get_contents('php://input'), true);
$message = mb_strtolower($data['message']['text'],'utf-8');
$chat = $data['message']['chat'];

$sendData['chat_id'] = $data['message']['chat']['id'];

$messageData = $telegram->getUserCommand($message);
$command = $messageData['command'];
$userText = $messageData['text'];

/*$commands = array(
    '/start' => (new StartCommand())->handle($sendData),
    '/погода' => (new WeatherCommand())->handle($sendData, $userText),
);*/

if (!empty($command)) {
    if ($command == '/погода') {
        (new WeatherCommand())->handle($sendData, $userText);
    } else if ($command == '/start') {
        (new StartCommand())->handle($sendData);
    }
} else {
    $sendData['text'] = 'Не удалось найти команду ' . $command;
}
