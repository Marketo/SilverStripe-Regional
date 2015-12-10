<?php

/**
 * @author Kirk Mayo <kirk.mayo@solnet.co.nz>
 *
 * A cache of geo data for a IP address
 */
use GeoIp2\Database\Reader;

class IPInfoCache extends DataObject
{
    private static $db = array(
        'IP' => 'Varchar',
        'Info' => 'Text'
    );

    public $defaultPath = '/usr/share/GeoIP/GeoLite2-City.mmdb';

    public static $statuses = array (
        'SUCCESS' => 'Success',
        'SUCCESS_CACHED' => 'Successfully found and cached response',
        'IP_ADDRESS_INVALID' => 'You have not supplied a valid IPv4 or IPv6 address',
        'IP_ADDRESS_RESERVED' => 'You have supplied an IP address which belongs to ' .
            'a reserved or private range',
        'IP_ADDRESS_NOT_FOUND' => 'The supplied IP address is not in the database',
        'DOMAIN_REGISTRATION_REQUIRED' => 'The domain of your site is not registered.',
        'DOMAIN_REGISTRATION_REQUIRED' => 'The domain of your site is not registered.',
        'GEOIP_EXCEPTION' => 'GEOIP_EXCEPTION [ERROR]',
        'GEOIP_MISSING' => 'GeoIP module does not exist',
    );

    public static $privateAddresses = array(
        '10.0.0.0|10.255.255.255',
        '172.16.0.0|172.31.255.255',
        '192.168.0.0|192.168.255.255',
        '169.254.0.0|169.254.255.255',
        '127.0.0.0|127.255.255.255'
    );

    public static function getStatuses($code = null) {
        if ($code && isset(self::$statuses[$code])) {
            return self::$statuses[$code];
        }
        return self::$statuses;
    }

    public static function setupCache($ip) {
        $status = null;
        $result = array();
        $path = Config::inst()->get('IPInfoCache', 'GeoPath');
        if (!$path) $path = $this->defaultPath;
        if (!file_exists($path)) {
            user_error('Error load Geo database', E_USER_ERROR);
        }

        $request['ip'] = $ip;
        $request['type'] = IPInfoCache::ipVersion($ip);
        if ($request['type'] == 'IPv4') {
            $isPrivate = IPInfoCache::isPrivateIP($ip);
            if ($isPrivate) {
                $status = self::setStatus('IP_ADDRESS_RESERVED', null, $status);
                return json_encode(array(
                    'status' => $status
                ));
            }
        }
        $reader = new Reader('/usr/share/GeoIP/GeoLite2-City.mmdb');
        $record = $reader->city($ip);

        $countryCode = null;
        try {
            $result['location']['continent_code'] = $record->continent->code;
            $result['location']['continent_name'] = $record->continent->name;

            $countryCode = $record->country->isoCode;
            $result['location']['country_code'] = $countryCode;
            $result['location']['country_name'] = $record->country->name;

            $result['location']['postal_code'] = $record->postal->code;
            $result['location']['city_name'] = $record->city->name;

            $result['location']['latitude'] = $record->location->latitude;
            $result['location']['longitude'] = $record->location->longitude;
            $result['location']['time_zone'] = $record->location->timeZone;
        } catch (Exception $e) {
            $status = self::setStatus('GEOIP_EXCEPTION', $e, $status);
        }

        $geoRegion = null;
        if ($countryCode) {
            $geoRegion = GeoRegion::get()
                ->filter('RegionCode', $countryCode)
                ->first();
            if ($geoRegion && $geoRegion->exists()) {
                $result['location']['marketo_region_name'] = $geoRegion->Name;
                $result['location']['marketo_region_code'] = $geoRegion->RegionCode;
                $result['location']['marketo_region_time_zone'] = $geoRegion->TimeZone;
            }
        }

        if ($status) {
            // do not cache a failure
            return json_encode(array(
                'request' => $request,
                'status' => $status,
                'result' => $result
            ));
        } else {
            // return cached success message
            $status = self::setStatus('SUCCESS_CACHED', null, $status);
            $json =  json_encode(array(
                'request' => $request,
                'status' => $status,
                'result' => $result
            ));

            // write a standard success to the cache
            $dbStatus = self::setStatus('SUCCESS', null, null);
            $dbJson =  json_encode(array(
                'request' => $request,
                'status' => $dbStatus,
                'result' => $result
            ));
            $cache = IPInfoCache::create();
            $cache->IP = $ip;
            $cache->Info = $dbJson;
            $cache->write();
        }

        return $json;
    }

    public static function setStatus($code, $e, $status = null) {
        if ($status) return $status;
        if ($code == 'GEOIP_EXCEPTION' && $e && $e instanceof Exception) {
            self::$statuses['GEOIP_EXCEPTION'] = str_replace(
                'ERROR',
                $e->getMessage(),
                self::$statuses['GEOIP_EXCEPTION']
            );
        }
        return $code;
    }

    public function getDetails() {
        return $this->Info;
    }

    public function clearIPCache() {
        $this->write(false, false, true);
    }

    public static function ipVersion($ip = null) {
        return (strpos($ip, ':') === false) ? 'IPv4' : 'IPv6';
    }

    public static function isPrivateIP($ip) {
        $longIP = ip2long($ip);
        if ($longIP != -1) {
            foreach (IPInfoCache::$privateAddresses as $privateAddress) {
                list($start, $end) = explode('|', $privateAddress);
                if ($longIP >= ip2long($start) && $longIP <= ip2long($end)) return (true);
            }
        }
        return false;
    }
}
