<?php
/**
 * Copyright (c) 2020 Nat Taylor.
 *
 * Publish LTV-WSDTH03 observations from La Crosse View to Weather Underground PWS
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once 'config.php';

$pws = new \App\WeatherUndergroudPws();

// The timestamp of the most recent upload to PWS
$latest = strval(max(array_map(function($o) {return $o->epoch;}, $pws->getCurrent($config->apiKey, $config->stationId)->observations)));

$ws = new \App\WeatherStation();

$ws->login($config->email, $config->password);

// Retrieve the feed of the observations since the most recent upload
$feed = $ws->getFeed(['device_id' => $config->deviceId, 'from' => $latest]);

// Transform the feed to data structure like {"columns"->[columns], "index"->[timestamps], "data"->[samples]}
$split = $ws->toSplit($feed);

// Upload each observation
foreach ($split->index as $i => $ts)
{
    $params = [];
    // Special date format for PWS upload protocol
    $params['dateutc'] = urlencode(date("Y-m-d H:i:s", $split->index[$i]));
    // TODO: Make this robust by looking up $split->fields
    $params['winddir'] = $split->data[$i][1];
    // TODO: Configurable units
    $params['windspeedmph'] = $split->data[$i][0] / 1.609;
    $params['tempf'] = $split->data[$i][3] * 9/5 + 32;
    $params['humidity'] = $split->data[$i][4];
    echo $pws->update($config->stationId, $config->key, $params).PHP_EOL;
}
