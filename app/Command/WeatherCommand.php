<?php

namespace Scrimmy\Weather\Command;

use Scrimmy\Weather\Interface\CommandInterface;
use Scrimmy\Weather\Service\WeatherService;

class WeatherCommand implements CommandInterface
{
    private WeatherService $weather;

    public function __construct()
    {
        $this->weather = new WeatherService();
    }

    public function handle($bot, $chatId, $data)
    {
        if (empty($data)) {
            return $bot->sendMessage($chatId, 'Не указан город');
        }

        $weather = $this->weather->getCityWeather($data);

        if (isset($weather['error'])) {
            return $bot->sendMessage($chatId, $weather['error']);
        }

        $pic = 'temp/' . $this->weather->getWeatherImage($weather);
        $bot->sendPhoto($chatId, $pic);

        unlink($pic);
    }
}