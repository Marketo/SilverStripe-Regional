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
        $reader = new Reader('/usr/share/GeoIP/GeoLite2-City.mmdb');
        $record = $reader->city($ip);

        try {
            $result['location']['continent_code'] = $record->continent->code;
            $result['location']['continent_name'] = $record->continent->name;

            $result['location']['country_code'] = $record->country->isoCode;
            $result['location']['country_name'] = $record->country->name;

            $result['location']['postal_code'] = $record->postal->code;
            $result['location']['city_name'] = $record->city->name;

            $result['location']['latitude'] = $record->location->latitude;
            $result['location']['longitude'] = $record->location->longitude;
            $result['location']['time_zone'] = $record->location->timeZone;
        } catch (Exception $e) {
            $status = self::setStatus('GEOIP_EXCEPTION', $e, $status);
        }

        $status = self::setStatus('SUCCESS', null, $status);
        $request['status']['code'] = $status;
        $request['status']['message'] = IPInfoCache::getStatuses($status);
        $json =  json_encode(array(
            'request' => $request,
            'status' => $status,
            'result' => $result
        ));
        $cache = IPInfoCache::create();
        $cache->IP = $ip;
        $cache->Info = json_encode($json);
        $cache->write();

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

    public function getStatus() {
        if (!class_exists('Net_GeoIP')) return 501;
    }

    public function getDetails() {
        return json_decode($this->Info, true);
    }

    public function clearIPCache() {
        $this->write(false, false, true);
    }

    public static function ipVersion($ip = null) {
        return (strpos($ip, ':') === false) ? 'IPv4' : 'IPv6';
    }
}
