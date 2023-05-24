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

    public function handle($data, $city)
    {
        if (!empty($city)) {
            $weather = $this->weather->getCityWeather($city);

            if (!isset($weather['error'])) {
                $photo = 'temp/' . $this->weather->getWeatherImage($weather);
                $data['photo'] = curl_file_create($photo, 'image/webp', 'result.webp');

                $this->telegram->sendPhoto($data);
                unlink($photo);
            }

            $data['text'] = $weather['error'];

            return $this->telegram->sendMessage($data);
        }

        $data['text'] = 'Не указан город';

        return $this->telegram->sendMessage($data);
    }
}