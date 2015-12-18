<?php

/**
 * @author Kirk Mayo <kirk.mayo@solnet.co.nz>
 *
 * Some basic testing for the various models
 */

class  GeoIPModelTest extends FunctionalTest {

    private $testIP = '50.206.151.41';

    public function setUp() {
        parent::setUp();
        SS_Datetime::set_mock_now('2015-12-16 00:00:00');
        $ip = $this->testIP;
        $request = $this->get("geoip/$ip");
    }

    public function testIPInfoCache() {
        $ip = $this->testIP;
        $cache = IPInfoCache::get()
            ->filter('IP', $ip)
            ->first();

        $this->assertEquals($cache->IP, $ip);

        $jsonArray = json_decode($cache->Info, true);
        $jsonError = json_last_error();
        $this->assertEquals($jsonError, 0);
    }

    public function testCaching() {
        $ip = $this->testIP;
        $old = IPInfoCache::get()
            ->filter('IP', $ip)
            ->first();

        SS_Datetime::clear_mock_now();
        $request = $this->get("geoip/$ip");

        $new = IPInfoCache::get()
            ->filter('IP', $ip)
            ->first();

        $this->assertNotEquals($old->LastEdited, $new->LastEdited);
    }

    public function testJSONP() {
        $ip = $this->testIP;
        $request = $this->get("geoip/$ip.jsonp?callback=load");

        $ip = $this->testIP;
        $cache = IPInfoCache::get()
            ->filter('IP', $ip)
            ->first();
        $this->assertNotEquals($request->getBody(), $cache->Info);
        $this->assertEquals($cache->IP, $ip);
    }
}
