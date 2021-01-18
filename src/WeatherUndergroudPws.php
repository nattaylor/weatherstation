<?php
/**
 * Copyright (c) 2020 Nat Taylor.
 */

namespace App;
/**
 * Class WeatherUndergroudPws
 * @package App
 *
 * Upload and retrieve data from Weather Underground Personal Weather Stations (PWS)
 *
 * https://support.weather.com/s/article/PWS-Upload-Protocol?language=en_US
 * https://docs.google.com/document/d/1eKCnKXI9xnoMGRRzOL1xPCBihNV2rOet08qpE_gArAY/edit
 */
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
        // TODO: Check for errors
        return $curl->response;
    }
}
