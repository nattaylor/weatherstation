<?php
/**
 * Copyright (c) 2020 Nat Taylor.
 */

namespace App;

use http\Exception;

/**
 * Class WeatherStation
 * @package App
 *
 * Client for the La Crosse View
 *
 * Mostly copied by https://github.com/dbconfession78/py_weather_station
 *
 */
class WeatherStation
{
    private $token;

    /**
     * Connect to a La Crosse View account
     *
     * @param String $email
     * @param String $password
     */
    public function login(String $email, String $password)
    {
        $url = "https://www.googleapis.com/" .
            "identitytoolkit/v3/relyingparty/verifyPassword?" .
            "key=AIzaSyD-Uo0hkRIeDYJhyyIg-TvAv8HhExARIO4";

        $curl = new \Curl\Curl();
        $curl->post($url, array(
            'email' => $email,
            "returnSecureToken" => true,
            'password' => $password,
        ));

        $this->token = $curl->response->idToken;

        $curl->close();
    }

    public function getLocations(): object
    {
        // TODO: Check for token
        $url = "https://lax-gateway.appspot.com/_ah/api/lacrosseClient/v1.1/active-user/locations";
        $curl = new \Curl\Curl();
        $curl->setHeader('Authorization', "Bearer {$this->token}");
        $curl->get($url);
        return $curl->response;
    }

    public function getDevices(String $location_id): object
    {
        // TODO: Check for token
        $url = "https://lax-gateway.appspot.com/_ah/api/lacrosseClient/v1.1/active-user/location/$location_id/sensorAssociations?prettyPrint=false";
        $curl = new \Curl\Curl();
        $curl->setHeader('Authorization', "Bearer {$this->token}");
        $curl->get($url);
        return $curl->response;
    }

    public function getFeed(Array $params = null): object
    {
        // TODO: Check for token
        // TODO: Configurable paramaters
        $tz = "America/New_York";
        $agg = "ai.ticks.1";
        $fields = "Temperature,FeelsLike,Humidity,WindSpeed,WindChill,HeatIndex,WindHeading";

        extract($params);

        $url = "https://ingv2.lacrossetechnology.com/" .
            "api/v1.1/active-user/device-association/ref.user-device.{$device_id}/" .
            "feed?fields={$fields}&" .
            "tz={$tz}&" .
            "from={$from}&" .
            // "to={$to}" .
            "aggregates={$agg}&" .
            "types=spot";
        $curl = new \Curl\Curl();
        $curl->setHeader('Authorization', "Bearer {$this->token}");
        $curl->get($url);
        $response = $curl->response;
        $response->device_id = $device_id;
        $response->agg = $agg;
        return $response;
    }

    /**
     * Transform the feed and return an object with
     *  {"columns"->[columns], "index"->[timestamps], "data"->[samples]}
     *
     * @param Object $feed
     * @return object
     */
    public function toSplit(Object $feed): object
    {
        $table = (object)[];
        $ref = "ref.user-device.{$feed->device_id}";
        $data = $feed->{$ref}->{$feed->agg};
        $table->columns = array_keys(get_object_vars($data->fields));
        // TODO: arrow functions
        $table->index = array_map(function($sample) {return $sample->u;}, $data->fields->{$table->columns[0]}->values);
        $table->data = array_map(function($i) use ($data, $table) {
            return array_map(function($field) use ($i, $data) { return $data->fields->{$field}->values[$i]->s; }, $table->columns);}
            , range(0, count($table->index)-1)
        );
        return $table;
    }
}
