## Maintainer Contact

Kirk Mayo

<kirk (dot) mayo (at) solnet (dot) co (dot) nz>

## Requirements

* SilverStripe 3.2
* A driver to return the json/jsonp results

# SilverStripe-Regional

Using modern GeoIP lookup mechanisms, this SilverStripe module makes available the coordinates, postal code, city,
state, country, content, timezone, organization, and ISP to the SilverStripe instance and can open a frontend API
for static websites to take advantage of. It can be extended with GeoIP drivers to provide additional or custom contextual data.


## Composer Installation

  composer require marketo/silverstripe-regional

## Config

The current module uses a default path for the GeoIP database this is currently set to `/usr/share/GeoIP/GeoLite2-City.mmdb`
This can be changed via the yml config a example is below.
A model admin exists called GeoRegion which adds user defined fields to the result that is returned.
It retrieves the details to add via the country code which needs to match a country code in GeoRegion.

```
IPInfoCache:
  GeoPath: '/your/own/location/yourdb.mmdb'
```

You can also return the results as json by setting up a variable in the yml config under IPInfoCache
as per the example below, which also details how to specify the driver to use.

```
IPInfoCache:
  Driver: 'MarketoRegionalDriver'
  CORS: true
  jsonp: 'yourOwnJsonpFunction';
  GeoPathCity: '/usr/share/GeoIP/GeoIPCity.dat'
```

Another way to set the jsonp function is directly by using the get variable fn this will override anything
set by the yml config file as per the following example url.

```
http://marketo.local/geoip/5.71.14.28.jsonp?fn=MarketoLoad
```

If you want to allow third party javascript requests to query the service (like AJAX) you will need to set
the CORS flag in your yml config to true so that the `Access-Control-Allow-Origin` header gets sent back to
the requesting browser.

## GeoIP database

You will neeed to retrive a databse for the module to work with this will need to be stored
on the server and you may need to set the location of GeoPath under IPInfoCache in your config yml file.
The free databases can be downloaded from here <https://github.com/maxmind/GeoIP2-php>

## API endpoints

The curent endpoint returns a JSON object giving location details for the IP address.
The results default to json but they can also be returned as jsonp if this has been defined under
the config for IPInfoCache

```
http://YOURSITE/geoip/IPADDRESS
http://YOURSITE/geoip/IPADDRESS.json
http://YOURSITE/geoip/IPADDRESS.jsonp
```

## TODO

Add tests
Split up conection methods make it easy to use other connectors and dbs
