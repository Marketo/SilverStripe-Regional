## Maintainer Contact

Kirk Mayo

<kirk (dot) mayo (at) solnet (dot) co (dot) nz>

## Requirements

* SilverStripe 3.2
* geoip2/geoip2 2.0

# SilverStripe-Regional

Using modern GeoIP lookup mechanisms, this SilverStripe module makes available the coordinates, postal code, city,
state, country, content, timezone, organization, and ISP to the SilverStripe instance and can open a frontend API
for static websites to take advantage of. It can be extended with GeoIP drivers to provide additional or custom contextual data.


## Composer Installation

  composer require marketo/silverstripe-regional

## Config

The current module uses a default path for the GeoIP database this is currently set to `/usr/share/GeoIP/GeoLite2-City.mmdb`
This can be changed via the yml config a example is below.

``
IPInfoCache:
  GeoPath: '/your/own/location/yourdb.mmdb'
```

## API endpoints

The curent endpoint returns a JSON object giving location details for the IP address

```
http://YOURSITE/geoip/IPADDRESS
```

## TODO

Add tests
Split up conection methods make it easy to use other connectors and dbs
