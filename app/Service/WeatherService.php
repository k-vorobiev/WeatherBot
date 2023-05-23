<?php

namespace Scrimmy\Weather\Service;

use Scrimmy\Weather\Helper\HelperFunction;
use Symfony\Component\Dotenv\Dotenv;

class WeatherService
{
    public string $token;

    public function __construct()
    {
        (new Dotenv())->load('.env');

        $this->token = $_ENV['WEATHER_TOKEN'];
    }

    protected function getCityDb(string $city)
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

    protected function setCityDb(string $cityName, float $cityLat, float $cityLon)
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

    public function getCityData(string $city)
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

    public function getCityWeather(string $city)
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

            return array (
                'city' => $city,
                'temp' => $temp,
                'status' => $desc,
            );
        } else {
            return array (
                'error' => 'Ошибка при получении погоды',
            );
        }
    }

    private function getStatus($status)
    {
        $statusArray = [
            'ясно' => 'sunny',
            'облачно с прояснениями' => 'cloudy-with-clarifications',
            'пасмурно' => 'dull',
            'небольшой дождь' => 'rain',
            'небольшой снегопад' => 'small-snowfall',
            'переменная облачность' => 'partly-cloudy',
        ];

        if (array_key_exists($status, $statusArray)) {
            return $statusArray[$status];
        }

        return 'null';
    }

    public function getWeatherImage(array $params)
    {
        $city = $params['city'];
        $temp = $params['temp'];
        $status = $this->getStatus($params['status']);
        $helper = (new HelperFunction());

        // Ширина и высота изображения
        $width = 550;
        $height = 300;

        // Размер текста
        $mainSize = 30;
        $descSize = 20;
        // Создаём изображение
        $image = imagecreatetruecolor($width, $height);
        // Указываем шрифт
        $font = ABSPATH . '/public/font/font.otf';
        // Указываем иконку для статуса погоды
        $icon = imagecreatefrompng(ABSPATH . '/public/icon/' . strtolower($status) . '.png');
        // Указываем цвета
        $textColor = imagecolorallocate($image, 229, 229, 229);
        $backgroundColor = imagecolorallocate($image, 24, 24, 26);
        // Центр изображения
        $center = $width / 2;

        // Заливаем прямоугольник белым цветом
        imagefilledrectangle($image, 0, 0, $width, $height, $backgroundColor);

        // Координаты центра для текстов
        $cityCenter = imagettfbbox($mainSize, 0, $font, $helper->ru_ucfirst($city));
        $tempCenter = imagettfbbox($mainSize, 0, $font, $params['temp']);
        $descCenter = imagettfbbox($descSize, 0, $font, $helper->ru_ucfirst($params['status']));
        // Координаты левого края для текстов
        $cityLeft = $center - round(($cityCenter[2] - $cityCenter[0]) / 2);
        $tempLeft = $center - round(($tempCenter[2] - $tempCenter[0]) / 2);
        $descLeft = $center - round(($descCenter[2] - $descCenter[0]) / 2);

        // Текст города
        imagefttext($image, $mainSize, 0, $cityLeft, 60, $textColor, $font, $helper->ru_ucfirst($city));
        // Вставляем иконку
        list($iconWidth, $iconHeight) = getimagesize(ABSPATH . '/public/icon/' . strtolower($status) . '.png');
        $iconLeft = ($width - $iconWidth) / 2;
        imagecopy($image, $icon, $iconLeft, 100, 0, 0, $iconWidth, $iconHeight);

        // Текст температуры
        imagefttext($image, $mainSize, 0, $tempLeft, 220, $textColor, $font, $temp);

        // Текст описания
        imagefttext($image, $descSize, 0, $descLeft, 270, $textColor, $font, $helper->ru_ucfirst($params['status']));

        // Сохраняем альфа-прозрачность
        imagesavealpha($image, true);
        imagealphablending($image, false);

        // Создаем изображение в формате .webp
        $filename = 'result' . time() . '.webp';
        imagewebp($image, 'temp/' . $filename, 100);
        // Уничтожаем из памяти объект
        imagedestroy($icon);
        imagedestroy($image);

        return $filename;
    }

}