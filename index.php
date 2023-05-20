<?php

require_once 'vendor/autoload.php';

$app = new \Scrimmy\Weather\Service\WeatherService();
var_dump($app->getCityWeather('Минеральные Воды'));
