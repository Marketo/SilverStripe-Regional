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

    public static function setupCache($ip) {
        $driver = Config::inst()->get('IPInfoCache', 'Driver');

        $ipService = new $driver();
        $dbJson = $ipService->processIP($ip);

        // do not cache a empty object
        if ($dbJson) {
            $cache = IPInfoCache::create();
            $cache->IP = $ip;
            $cache->Info = $dbJson;
            $cache->write();
        }

        return $ipService->getJSON();
    }


    public function getDetails() {
        return $this->Info;
    }

    public function clearIPCache() {
        $this->write(false, false, true);
    }
}
