<?php
// This file is part of the Accredible Certificate module for Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Unit tests for mod/accredible/classes/client/client.php
 *
 * @package    mod
 * @subpackage accredible
 * @copyright  Accredible <dev@accredible.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use mod_accredible\client\client;

class mod_accredible_client_testcase extends advanced_testcase {
    /**
     * Setup before every test.
     */
    public function setUp(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        // Add plugin settings.
        set_config('accredible_api_key', 'sometestapikey');
    }

    /**
     * Tests whether it calls the curl get function.
     */
    public function test_get() {
        $url = 'https://api.accredible.com/v1/all_credentials';
        $options = array(
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_FAILONERROR'    => true,
            'CURLOPT_HTTPHEADER'     => array(
                'Authorization: Token sometestapikey',
                'Content-Type: application/json; charset=utf-8',
                'Accredible-Integration: Moodle'
            )
        );

        // Mock curl.
        $mockcurl = $this->getMockBuilder('curl')
                         ->setMethods(['get'])
                         ->getMock();

        $mockcurl->expects($this->once())
                 ->method('get')
                 ->with($this->equalTo($url),
                        $this->equalTo(null),
                        $this->equalTo($options));

        // Expect to call curl get.
        $client = new client($mockcurl);
        $client->get($url);
    }

    /**
     * Tests whether it calls the curl post function.
     */
    public function test_post() {
        $url = 'https://api.accredible.com/v1/all_credentials';
        $options = array(
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_FAILONERROR'    => true,
            'CURLOPT_HTTPHEADER'     => array(
                'Authorization: Token sometestapikey',
                'Content-Type: application/json; charset=utf-8',
                'Accredible-Integration: Moodle'
            )
        );

        // Mock curl.
        $mockcurl = $this->getMockBuilder('curl')
                         ->setMethods(['post'])
                         ->getMock();

        $reqdata = '{"evidence_item":{"string_object":"100"}}';
        $mockcurl->expects($this->once())
                 ->method('post')
                 ->with($this->equalTo($url),
                        $this->equalTo($reqdata),
                        $this->equalTo($options));

        // Expect to call curl post.
        $client = new client($mockcurl);
        $client->post($url, $reqdata);
    }

    /**
     * Tests whether it calls the curl put function.
     */
    public function test_put() {
        $url = 'https://api.accredible.com/v1/all_credentials';
        $options = array(
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_FAILONERROR'    => true,
            'CURLOPT_HTTPHEADER'     => array(
                'Authorization: Token sometestapikey',
                'Content-Type: application/json; charset=utf-8',
                'Accredible-Integration: Moodle'
            )
        );

        // Mock curl.
        $mockcurl = $this->getMockBuilder('curl')
                         ->setMethods(['put'])
                         ->getMock();

        $reqdata = '{"evidence_item":{"string_object":"100"}}';
        $mockcurl->expects($this->once())
                 ->method('put')
                 ->with($this->equalTo($url),
                        $this->equalTo($reqdata),
                        $this->equalTo($options));

        // Expect to call curl put.
        $client = new client($mockcurl);
        $client->put($url, $reqdata);
    }

    /**
     * Tests whether it returns an error messages when the request fails.
     */
    public function test_error() {
        $url = 'https://api.accredible.com/v1/all_credentials';
        $options = array(
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_FAILONERROR'    => true,
            'CURLOPT_HTTPHEADER'     => array(
                'Authorization: Token sometestapikey',
                'Content-Type: application/json; charset=utf-8',
                'Accredible-Integration: Moodle'
            )
        );

        // Mock curl.
        $mockcurl = $this->getMockBuilder('curl')
                         ->setMethods(['get'])
                         ->getMock();

        $mockcurl->expects($this->once())
                 ->method('get')
                 ->with($this->equalTo($url),
                        $this->equalTo(null),
                        $this->equalTo($options));

        $mockcurl->error = 'The requested URL returned error: 401 Unauthorized';
    
        // Expect to call debugging.
        $client = new client($mockcurl);
        $this->assertDebuggingCalled($client->get($url));
    
        // Expect to return an error message.
        $this->assertEquals($client->error, 'The requested URL returned error: 401 Unauthorized');
    }
}
