<?php

namespace Scrimmy\Weather\Command;

use Scrimmy\Weather\Service\TelegramService;
use Scrimmy\Weather\Service\WeatherService;

class WeatherCommand
{
    public TelegramService $telegram;
    public WeatherService $weather;

    public function __construct()
    {
        $this->telegram = new TelegramService();
        $this->weather = new WeatherService();
    }

    public function getImage(string $city)
    {
        if (!empty($city)) {
            $cityWeather = $this->weather->getCityWeather($city);

            if (!isset($weather['error'])) {
                return $this->weather->getWeatherImage($cityWeather);
            }

            return $cityWeather['error'];
        }

        return 'Не указан город';
    }
}