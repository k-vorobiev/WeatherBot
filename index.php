<?php

use Scrimmy\Weather\Service\TelegramService;
use Scrimmy\Weather\Interface\CommandInterface;

require_once 'vendor/autoload.php';

const ABSPATH = __DIR__;

class TelegramWeather {
    protected TelegramService $bot;
    protected array $update;

    public function __construct()
    {
        $this->bot = new TelegramService();
        $this->update = json_decode(file_get_contents('php://input'), true);
    }

    public function handleRequest(): void
    {
        $userInput = mb_strtolower($this->update['message']['text'],'utf-8');
        $handingInput = $this->splitCommand($userInput);

        $command = $this->getCommand($handingInput['command']);
        $data = $handingInput['data'];
        $chatId = $this->update['message']['chat']['id'];

        if (!empty($command) && $command instanceof CommandInterface) {
            $command->handle($this->bot, $chatId, $data);
        } else {
            $this->bot->sendMessage($chatId, 'Данная команда не найдена');
        }
    }

    public function getCommand($commandText): ?CommandInterface
    {
        $className = ucfirst(strtolower(trim($commandText, '/'))) . 'Command';
        $commandsPath = 'app/Command/' . $className . '.php';
        $fullClassName = 'Scrimmy\Weather\Command\\' . $className;

        if (file_exists($commandsPath)) {
            require_once $commandsPath;

            if (class_exists($fullClassName)) {
                return new $fullClassName;
            }
        }

        return null;
    }

    public function splitCommand($userInput): array
    {
        $parts = explode(' ', $userInput, 2);

        $command = $parts[0];
        $text = $parts[1] ?? '';

        return [
            'command' => $command,
            'data' => $text
        ];
    }
}

(new TelegramWeather())->handleRequest();
