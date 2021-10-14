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
 * Unit tests for mod/accredible/classes/apiRest/apiRest.php
 *
 * @package    mod
 * @subpackage accredible
 * @copyright  Accredible <dev@accredible.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use mod_accredible\apiRest\apiRest;
use mod_accredible\client\client;

class mod_accredible_apiRest_testcase extends advanced_testcase {
    /**
     * Setup before every test.
     */
    public function setUp(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        // Add plugin settings.
        set_config('accredible_api_key', 'sometestapikey');
        set_config('is_eu', 0);

        // Unset the devlopment environment variable.
        putenv('ACCREDIBLE_DEV_API_ENDPOINT');

        $this->mockapi = new class {
            /**
             * Returns a mock API response based on the fixture json.
             * @param string $jsonpath
             * @return array
             */
            public function resdata($jsonpath) {
                global $CFG;
                $fixturedir = $CFG->dirroot . '/mod/accredible/tests/fixtures/mockapi/v1/';
                $filepath = $fixturedir . $jsonpath;
                return json_decode(file_get_contents($filepath));
            }
        };
    }

    /**
     * Tests that the api endpoint changes depending on the config.
     */
    public function test_api_endpoint() {
        // When is_eu is NOT enabled.
        $api = new apiRest();
        $this->assertEquals($api->api_endpoint, 'https://api.accredible.com/v1/');

        // When is_eu is enabled.
        set_config('is_eu', 1);
        $api = new apiRest();
        $this->assertEquals($api->api_endpoint, 'https://eu.api.accredible.com/v1/');

        // When the environemnt variable is set.
        putenv('ACCREDIBLE_DEV_API_ENDPOINT=http://host.docker.internal:3000/v1/');
        $api = new apiRest();
        $this->assertEquals($api->api_endpoint, 'http://host.docker.internal:3000/v1/');
    }

    /**
     * Tests if `GET /v1/credentials/:id` is properly called.
     */
    public function test_get_credential() {
        /**
         * When the response is successful.
         */
        $mockclient1 = $this->getMockBuilder('client')
                            ->setMethods(['get'])
                            ->getMock();

        // Mock API response data.
        $resdata = $this->mockapi->resdata('credentials/show_success.json');

        // Expect to call the endpoint once with id.
        $url = 'https://api.accredible.com/v1/credentials/1';
        $mockclient1->expects($this->once())
                    ->method('get')
                    ->with($this->equalTo($url))
                    ->willReturn($resdata);

        // Expect to return resdata.
        $api = new apiRest($mockclient1);
        $result = $api->get_credential(1);
        $this->assertEquals($result, $resdata);

        /**
         * When the credential is not found.
         */
        $mockclient2 = $this->getMockBuilder('client')
                            ->setMethods(['get'])
                            ->getMock();
        $mockclient2->error = 'The requested URL returned error: 404 Not found';

        // Mock API response data.
        $resdata = $this->mockapi->resdata('credentials/show_not_found.json');

        // Expect to call the endpoint once with id.
        $url = 'https://api.accredible.com/v1/credentials/9999';
        $mockclient2->expects($this->once())
                    ->method('get')
                    ->with($this->equalTo($url))
                    ->willReturn($resdata);

        // Expect to return resdata.
        $api = new apiRest($mockclient2);
        $result = $api->get_credential(9999);
        $this->assertEquals($result, $resdata);

        /**
         * When the api key is invalid.
         */
        $mockclient3 = $this->getMockBuilder('client')
                            ->setMethods(['get'])
                            ->getMock();
        $mockclient3->error = 'The requested URL returned error: 401 Unauthorized';

        // Mock API response data.
        $resdata = $this->mockapi->resdata('unauthorized_error.json');

        // Expect to call the endpoint once with id.
        $url = 'https://api.accredible.com/v1/credentials/1';
        $mockclient3->expects($this->once())
                    ->method('get')
                    ->with($this->equalTo($url))
                    ->willReturn($resdata);

        // Expect to return resdata.
        $api = new apiRest($mockclient3);
        $result = $api->get_credential(1);
        $this->assertEquals($result, $resdata);
    }

    /**
     * Tests if `POST /v1/issuer/groups/search` is properly called.
     */
    public function test_search_groups() {
        /**
         * When the response is successful.
         */
        $mockclient1 = $this->getMockBuilder('client')
                            ->setMethods(['post'])
                            ->getMock();

        // Mock API response data.
        $resdata = $this->mockapi->resdata('groups/search_success.json');

        $reqdata = json_encode(array('page' => 1, 'page_size' => 10000));

        // Expect to call the endpoint once with page and page_size.
        $url = 'https://api.accredible.com/v1/issuer/groups/search';
        $mockclient1->expects($this->once())
                    ->method('post')
                    ->with($this->equalTo($url),
                           $this->equalTo($reqdata),)
                    ->willReturn($resdata);

        // Expect to return resdata.
        $api = new apiRest($mockclient1);
        $result = $api->search_groups(10000, 1);
        $this->assertEquals($result, $resdata);

        /**
         * When the arguments are empty and the response is successful.
         */
        $mockclient2 = $this->getMockBuilder('client')
                            ->setMethods(['post'])
                            ->getMock();

        // Mock API response data.
        $resdata = $this->mockapi->resdata('groups/search_success.json');

        $reqdata = json_encode(array('page' => 1, 'page_size' => 50));

        // Expect to call the endpoint once with default page and page_size.
        $url = 'https://api.accredible.com/v1/issuer/groups/search';
        $mockclient2->expects($this->once())
                    ->method('post')
                    ->with($this->equalTo($url),
                           $this->equalTo($reqdata),)
                    ->willReturn($resdata);

        // Expect to return resdata.
        $api = new apiRest($mockclient2);
        $result = $api->search_groups();
        $this->assertEquals($result, $resdata);

        /**
         * When the api key is invalid.
         */
        $mockclient3 = $this->getMockBuilder('client')
                            ->setMethods(['post'])
                            ->getMock();
        $mockclient3->error = 'The requested URL returned error: 401 Unauthorized';

        // Mock API response data.
        $resdata = $this->mockapi->resdata('unauthorized_error.json');

        $reqdata = json_encode(array('page' => 1, 'page_size' => 10000));

        // Expect to call the endpoint once with page and page_size.
        $url = 'https://api.accredible.com/v1/issuer/groups/search';
        $mockclient3->expects($this->once())
                    ->method('post')
                    ->with($this->equalTo($url),
                           $this->equalTo($reqdata),)
                    ->willReturn($resdata);

        // Expect to return resdata.
        $api = new apiRest($mockclient3);
        $result = $api->search_groups(10000, 1);
        $this->assertEquals($result, $resdata);
    }

    /**
     * Tests if `POST /v1/credentials/:credential_id/evidence_items`
     * is properly called.
     */
    public function test_create_evidence_item() {
        /**
         * When the throw_error is FALSE and the response is successful.
         */
        $mockclient1 = $this->getMockBuilder('client')
                            ->setMethods(['post'])
                            ->getMock();

        // Mock API response data.
        $resdata = $this->mockapi->resdata('evidence_items/create_success.json');

        // Expect to call the endpoint once with url and reqdata
        $url = 'https://api.accredible.com/v1/credentials/1/evidence_items';
        $evidence_item = array(
            'evidence_item' => array(
                "string_object" => "100",
                "description" => "Quiz",
                "custom" => true,
                "category" => "grade"
            )
        );
        $reqdata = json_encode($evidence_item);

        $mockclient1->expects($this->once())
                    ->method('post')
                    ->with($this->equalTo($url),
                           $this->equalTo($reqdata))
                    ->willReturn($resdata);

        // Expect to return resdata.
        $api = new apiRest($mockclient1);
        $result = $api->create_evidence_item($evidence_item, 1);
        $this->assertEquals($result, $resdata);

        /**
         * When the throw_error is FALSE and the response is NOT successful.
         */
        $mockclient2 = $this->getMockBuilder('client')
                            ->setMethods(['post'])
                            ->getMock();
        $mockclient2->error = 'The requested URL returned error: 401 Unauthorized';

        // Mock API response data.
        $resdata = $this->mockapi->resdata('unauthorized_error.json');

        $mockclient2->expects($this->once())
                    ->method('post')
                    ->with($this->equalTo($url),
                           $this->equalTo($reqdata))
                    ->willReturn($resdata);

        // Expect to return resdata without throwing an exception.
        $api = new apiRest($mockclient2);
        $result = $api->create_evidence_item($evidence_item, 1);
        $this->assertEquals($result, $resdata);

        /**
         * When the throw_error is TRUE and the response is NOT successful.
         */
        $mockclient3 = $this->getMockBuilder('client')
                            ->setMethods(['post'])
                            ->getMock();
        $mockclient3->error = 'The requested URL returned error: 401 Unauthorized';

        // Mock API response data.
        $resdata = $this->mockapi->resdata('unauthorized_error.json');

        $mockclient3->expects($this->once())
                    ->method('post')
                    ->with($this->equalTo($url),
                           $this->equalTo($reqdata))
                    ->willReturn($resdata);

        // Expect to return resdata without throwing an exception.
        $api = new apiRest($mockclient3);
        $foundexception = false;
        try {
            $api->create_evidence_item($evidence_item, 1, true);
        } catch (\moodle_exception $error) {
            $foundexception = true;
        }
        $this->assertTrue($foundexception);
    }

    /**
     * Tests if `PUT /v1/credentials/:credential_id/evidence_items/:id`
     * is properly called.
     */
    public function test_update_evidence_item_grade() {
        /**
         * When the grade is a valid number and the response is successful.
         */
        $mockclient1 = $this->getMockBuilder('client')
                            ->setMethods(['put'])
                            ->getMock();

        // Mock API response data.
        $resdata = $this->mockapi->resdata('evidence_items/update_success.json');

        // Expect to call the endpoint once with url and reqdata.
        $url = 'https://api.accredible.com/v1/credentials/1/evidence_items/1';
        $reqdata = '{"evidence_item":{"string_object":"100"}}';
        $mockclient1->expects($this->once())
                    ->method('put')
                    ->with($this->equalTo($url),
                           $this->equalTo($reqdata))
                    ->willReturn($resdata);

        // Expect to return resdata.
        $api = new apiRest($mockclient1);
        $result = $api->update_evidence_item_grade(1, 1, '100');
        $this->assertEquals($result, $resdata);

        /**
         * When the grade is a valid number but the evidence item is not found.
         */
        $mockclient2 = $this->getMockBuilder('client')
                            ->setMethods(['put'])
                            ->getMock();
        $mockclient2->error = 'The requested URL returned error: 404 Not found';

        // Mock API response data.
        $resdata = $this->mockapi->resdata('evidence_items/update_not_found.json');

        // Expect to call the endpoint once with url and reqdata.
        $url = 'https://api.accredible.com/v1/credentials/1/evidence_items/9999';
        $reqdata = '{"evidence_item":{"string_object":"100"}}';
        $mockclient2->expects($this->once())
                    ->method('put')
                    ->with($this->equalTo($url),
                           $this->equalTo($reqdata))
                    ->willReturn($resdata);

        // Expect to return resdata.
        $api = new apiRest($mockclient2);
        $result = $api->update_evidence_item_grade(1, 9999, '100');
        $this->assertEquals($result, $resdata);

        /**
         * When the grade is a valid number but the api key is invalid.
         */
        $mockclient3 = $this->getMockBuilder('client')
                            ->setMethods(['put'])
                            ->getMock();
        $mockclient3->error = 'The requested URL returned error: 401 Unauthorized';

        // Mock API response data.
        $resdata = $this->mockapi->resdata('unauthorized_error.json');

        // Expect to call the endpoint once with url and reqdata.
        $url = 'https://api.accredible.com/v1/credentials/1/evidence_items/2';
        $reqdata = '{"evidence_item":{"string_object":"100"}}';
        $mockclient3->expects($this->once())
                    ->method('put')
                    ->with($this->equalTo($url),
                           $this->equalTo($reqdata))
                    ->willReturn($resdata);

        // Expect to return resdata.
        $api = new apiRest($mockclient3);
        $result = $api->update_evidence_item_grade(1, 2, '100');
        $this->assertEquals($result, $resdata);

        /**
         * When the grade is NOT a number.
         */
        $foundexception1 = false;
        try {
            $api->update_evidence_item_grade(1, 1, '0x539');
        } catch (\InvalidArgumentException $error) {
            $foundexception1 = true;
        }
        $this->assertTrue($foundexception1);

        /**
         * When the grade is negative.
         */
        $foundexception2 = false;
        try {
            $api->update_evidence_item_grade(1, 1, -1);
        } catch (\InvalidArgumentException $error) {
            $foundexception2 = true;
        }
        $this->assertTrue($foundexception2);

        /**
         * When the grade is greater than 100.
         */
        $foundexception3 = false;
        try {
            $api->update_evidence_item_grade(1, 1, 101);
        } catch (\InvalidArgumentException $error) {
            $foundexception3 = true;
        }
        $this->assertTrue($foundexception3);
    }
}
