<?php

namespace App;

class WeatherUndergroudPws
{
    public function get1day(String $apiKey, String $stationId, String $format = 'json', String $units = 'e'): object
    {
        $curl = new \Curl\Curl();
        $curl->get("https://api.weather.com/v2/pws/observations/all/1day?stationId={$stationId}&format={$format}&units={$units}&apiKey={$apiKey}");
        return $curl->response;
    }

    public function getCurrent(String $apiKey, String $stationId, String $format = 'json', String $units = 'e'): object
    {
        $curl = new \Curl\Curl();
        $curl->get("https://api.weather.com/v2/pws/observations/current?stationId={$stationId}&format={$format}&units={$units}&apiKey={$apiKey}");
        return $curl->response;
    }

    public function update($user, $key, $observation)
    {
        $curl = new \Curl\Curl();
        $url = "https://weatherstation.wunderground.com/weatherstation/updateweatherstation.php?action=updateraw&ID={$user}&PASSWORD={$key}&"
            . implode("&", array_map(function($p) use ($observation) {return "$p={$observation[$p]}";}, array_keys($observation)));
        $curl->get($url);
        return $curl->response;
    }
}