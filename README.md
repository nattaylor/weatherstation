# Weather Station

Publish LTV-WSDTH03 observations from La Crosse View to Weather Underground PWS

## Installation

1. Deploy to a server that runs PHP
2. Run `composer --no-dev update`
2. Rename `config.sample.php` to `config.php`
3. Configure the variables in `config.php`
    * "email" - La Crosse View email
    * "password" - La Crosse View password
    * "deviceId" - The weather station's id (see `getLocations()` and `getDevices()`)
    * "stationId" - Your Weather Underground station ID
    * "key" - Your Weather Underground station key
    * "apiKey" - Your Weather Underground PWS api key
4. Configure an every minute CRON to run `php -f /path/to/index.php`

## Thanks

Thanks to https://github.com/dbconfession78/py_weather_station for publishing the La Crosse View API details.
