<?php

/**
 * @author Kirk Mayo <kirk.mayo@solnet.co.nz>
 *
 * Some controller testing
 */

class  GeoIPControllerTest extends FunctionalTest {

    private $testIP = '50.206.151.41';

    public function testGeoIP() {
        $ip = $this->testIP;
        $request = $this->get("geoip/$ip");
        $this->assertEquals($request->getStatusCode(), 200);

        $body = $request->getBody();

        $jsonArray = json_decode($body, true);
        $jsonError = json_last_error();
        $this->assertEquals($jsonError, 0);

        $this->assertEquals($jsonArray['request']['ip'], $ip);
        $this->assertEquals($jsonArray['request']['type'], 'IPv4');
    }
}
