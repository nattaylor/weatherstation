<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once 'config.php';

$pws = new \App\WeatherUndergroudPws();

$latest = strval(max(array_map(function($o) {return $o->epoch;}, $pws->getCurrent($config->apiKey, $config->stationId)->observations)));

print $latest.PHP_EOL;

$ws = new \App\WeatherStation();

$ws->login($config->email, $config->password);

$location_id = $ws->getLocations()->items[0]->id;

$feed = $ws->getFeed(['device_id' => $config->deviceId, 'from' => $latest]);

$split = $ws->toSplit($feed);

foreach ($split->index as $i => $ts)
{
    $params = [];
    $params['dateutc'] = urlencode(date("Y-m-d H:i:s", $split->index[$i]));
    $params['winddir'] = $split->data[$i][1];
    $params['windspeedmph'] = $split->data[$i][0] / 1.609;
    $params['tempf'] = $split->data[$i][3] * 9/5 + 32;
    $params['humidity'] = $split->data[$i][4];
    echo $pws->update($config->stationId, $config->key, $params).PHP_EOL;
}
