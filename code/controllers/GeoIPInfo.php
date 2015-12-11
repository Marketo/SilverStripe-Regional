<?php

/**
 * @author Kirk Mayo <kirk.mayo@solnet.co.nz>
 *
 * A controller class for retrieving GeoIP customised data
 */

class GeoIPInfo extends Controller
{
    private static $allowed_actions = array(
        'geoip'
    );

    private static $url_handlers = array(
        '$IP' => 'geoip'
    );

    public function geoip() {
        $ip = $this->getRequest()->param('IP');
        $requestType = $this->getRequest()->getExtension();

        $ipCache = IPInfoCache::get()
            ->filter(array(
                'IP' => $ip
            ))->first();
        if ($ipCache && $ipCache->exists()) {
	        if (strtotime($ipCache->LastEdited) < strtotime('24 hours ago')) {
                $ipCache->clearIPCache();
	        }
            $details = $ipCache->getDetails();
        } else {
            $details = IPInfoCache::setupCache($ip);
        }

        if ($requestType == 'jsonp') {
            $jsonp = Config::inst()->get('IPInfoCache', 'jsonp');
            if ($jsonp) {
                return "$jsonp(" . $details . ');';
            }
        }
        return $details;
    }
}
