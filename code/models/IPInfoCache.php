<?php

/**
 * @author Kirk Mayo <kirk.mayo@solnet.co.nz>
 *
 * A cache of geo data for a IP address
 */

class IPInfoCache extends DataObject
{
    public static $defaultDrivers = array(
        'GeoIPLegacyDriver'
    );
    private static $db = array(
        'IP' => 'Varchar',
        'Info' => 'Text'
    );

    public static function setupCache($ip)
    {
        $driver = Config::inst()->get('IPInfoCache', 'Driver');
        if (!$driver) {
            foreach (self::$defaultDrivers as $defaultDriver) {
                if (class_exists($defaultDriver)) {
                    $driver = $defaultDriver;
                    break;
                }
            }
            if (!$driver) {
                user_error('A driver needs to be specified');
            }
        }

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


    public function getDetails()
    {
        return $this->Info;
    }

    public function clearIPCache()
    {
        $this->write(false, false, true);
    }
}
