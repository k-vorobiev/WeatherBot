<?php

namespace Scrimmy\Weather\Service;

use Symfony\Component\Dotenv\Dotenv;

class WeatherService
{
    public string $token;

    public function __construct()
    {
        (new Dotenv())->load('.env');

        $this->token = $_ENV['WEATHER_TOKEN'];
    }

    protected function getCityDb($city)
    {
        $conn = new DatabaseService();

        try {
            $query = $conn->prepare('SELECT * FROM `city` WHERE `city_name` = :city');
            $query->bindParam(':city', $city);
            $query->execute();
            $result = $query->fetch(\PDO::FETCH_ASSOC);

            if (empty($result)) {
                return false;
            }

            return $result;
        } catch (\PDOException $e) {
            return false;
        }
    }

    protected function setCityDb($cityName, $cityLat, $cityLon)
    {
        $conn = new DatabaseService();

        try {
            $query = $conn->prepare('INSERT INTO `city` (`city_name`, `city_lat`, `city_lon`) VALUES (:city_name, :city_lat, :city_lon)');
            $query->bindParam(':city_name', $cityName);
            $query->bindParam(':city_lat', $cityLat);
            $query->bindParam(':city_lon', $cityLon);
            $query->execute();

            return true;
        } catch (\PDOException $e) {
            return false;
        }
    }

    public function getCityData($city)
    {
        $cityDb = $this->getCityDb($city);

        if (!$cityDb) {
            $params = [
                'q' => $city,
                'appid' => $this->token,
            ];

            $url = 'http://api.openweathermap.org/geo/1.0/direct?' . http_build_query($params);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);

            $response = json_decode(curl_exec($ch));
            curl_close($ch);

            $cityName = $cityLat = $cityLon = '';

            if(!isset($response->cod)) {
                foreach ($response as $r) {
                    $cityName = $r->local_names->ru;
                    $cityLat = $r->lat;
                    $cityLon = $r->lon;
                }

                $this->setCityDb($cityName, $cityLat, $cityLon);

                return array (
                    'city' => $city,
                    'lat' => $cityLat,
                    'lon' => $cityLon,
                );
            } else {
                return array (
                    'error' => 'Ошибка при получении города',
                );
            }
        } else {
            return array (
                'city' => $cityDb['city_name'],
                'lat' => $cityDb['city_lat'],
                'lon' => $cityDb['city_lon'],
            );
        }
    }

    public function getCityWeather($city)
    {
        if (preg_match('/[^а-я А-Я]+$/u', $city)) {
            return array (
                'error' => 'Введите название города на русском языке',
            );
        }

        $cityData = $this->getCityData($city);

        if (empty($cityData) || isset($cityData['error'])) {
            return array (
                'error' => 'Ошибка при получении погоды',
            );
        }

        $params = [
            'lat' => $cityData['lat'],
            'lon' => $cityData['lon'],
            'units' => 'metric',
            'lang' => 'ru',
            'appid' => $this->token
        ];

        $url = 'https://api.openweathermap.org/data/2.5/weather?' . http_build_query($params);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);

        $response = json_decode(curl_exec($ch));
        curl_close($ch);

        if ($response->cod == 200) {
            $weather = array_shift($response->weather);
            $temp = round($response->main->temp);
            $desc = $weather->description;

            $message = "Погода в городе $city: " . PHP_EOL . "$temp градусов цельсия. " . PHP_EOL . ucfirst($desc);

            return array (
                'message' => $message,
            );
        } else {
            return array (
                'error' => 'Ошибка при получении погоды',
            );
        }
    }
}