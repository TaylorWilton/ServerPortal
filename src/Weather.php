<?php

class Weather implements iCacheable
{

    private $weatherForecast;
    private $parsedData;
    private $location;

    public function __construct(string $apiKey, string $lat, string $lon, string $location)
    {
        $this->location = $location;

        $cached = $this->loadCache();

        if (empty($cached) || $this->isStale($cached)) {
            $this->weatherForecast = $this->getWeatherData($apiKey, $lat, $lon);
            $this->parsedData = $this->parseWeatherData();
            $this->cache();
        } else {
            $this->parsedData = $cached;
        }

    }

    function loadCache(): array
    {
        $result = file_get_contents("./data/weather.json");
        if (!$result) {
            return [];
        }

        return json_decode($result, true);
    }

    function isStale(array $cache): bool
    {
        $dateSaved = new DateTime($cache['cachedAt']['date']);
        $now = new DateTime();
        $diff = $now->diff($dateSaved);

        return ($diff->days > 0 || $diff->m > 30);
    }

    /**
     * @param string $apiKey
     * @param string $lat
     * @param string $lon
     * @return array
     */
    private function getWeatherData(
        string $apiKey,
        string $lat,
        string $lon
    ): array {
        $url = sprintf("https://api.darksky.net/forecast/%s/%s,%s?units=auto",
            $apiKey, $lat, $lon);
        $json = json_decode(file_get_contents($url), true);

        return [
            "current" => $json['currently'],
            "forecast" => $json['daily']
        ];
    }

    private function parseWeatherData(): array
    {
        $weatherForecast = $this->weatherForecast;
        $summaryNow = [
            "description" => $weatherForecast['current']['summary'],
            "icon" => $weatherForecast['current']['icon'],
            "temperature" => $weatherForecast['current']['temperature'],
            "chanceOfRain" => $weatherForecast['current']['precipProbability'],
        ];

        $today = $weatherForecast['forecast']['data'][0];

        $summaryToday = [
            "description" => $today['summary'],
            "icon" => $today['icon'],
            "temperatureMin" => $today['temperatureMin'],
            "temperatureMax" => $today['temperatureMax'],
            "chanceOfRain" => $today['precipProbability'],
        ];

        $summaryThreeDays = [];
        for ($i = 1; $i < 4; $i++) {
            $day = $weatherForecast['forecast']['data'][$i];
            $summaryThreeDays[] = [
                "description" => $day['summary'],
                "icon" => $day['icon'],
                "temperatureMin" => $day['temperatureMin'],
                "temperatureMax" => $day['temperatureMax'],
                "chanceOfRain" => $day['precipProbability'],
                "time" => $day['time'],
            ];
        }

        $summaryWeek = [
            "description" => $weatherForecast['forecast']['summary'],
            "icon" => $weatherForecast['forecast']['icon']
        ];

        return [
            "location" => $this->location,
            "summaryNow" => $summaryNow,
            "summaryToday" => $summaryToday,
            "summaryThreeDays" => $summaryThreeDays,
            "summaryWeek" => $summaryWeek
        ];
    }

    function cache()
    {
        $this->parsedData["cachedAt"] = new DateTime();
        file_put_contents("./data/weather.json", json_encode($this->parsedData));
    }

    public function getForecast(): array
    {
        return $this->parsedData;
    }
}


