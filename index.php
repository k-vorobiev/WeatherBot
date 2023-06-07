<?php

use Scrimmy\Weather\Service\TelegramService;
use Scrimmy\Weather\Interface\CommandInterface;

require_once 'vendor/autoload.php';

const ABSPATH = __DIR__;
const COMMAND_DIR = 'app/Command/';
const COMMAND_NAMESPACE = 'Scrimmy\\Weather\\Command\\';

class TelegramWeather {
    protected TelegramService $bot;
    protected array $update;

    /**
     * Конструктор класса
     */
    public function __construct()
    {
        $this->bot = new TelegramService();
        $this->update = json_decode(file_get_contents('php://input'), true);
    }

    /**
     * Метод обрабатывает входящий запрос пользователя
     *
     * @return void
     */
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

    /**
     * Метод получает имеющиеся команды, возвращает экземпляр класса, если он существует
     *
     * @param $commandText
     * @return CommandInterface|null
     */
    public function getCommand($commandText): ?CommandInterface
    {
        $className = ucfirst(strtolower(trim($commandText, '/'))) . 'Command';
        $commandsPath = COMMAND_DIR . $className . '.php';
        $fullClassName = COMMAND_NAMESPACE . $className;

        if (file_exists($commandsPath)) {
            require_once $commandsPath;
            if (class_exists($fullClassName)) {
                return new $fullClassName;
            }
        }

        return null;
    }

    /**
     * Метод разбивает входящий запрос пользователя и возвращает массив с командой и информацией
     *
     * @param $userInput
     * @return array
     */
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