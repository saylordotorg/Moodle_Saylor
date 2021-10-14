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

namespace mod_accredible\client;
defined('MOODLE_INTERNAL') || die();

class client {
    /**
     * The curl object used to make the request.
     * @var curl
     */
    private $curl;

    private $curl_options;

    public $error;

    public function __construct($curl = null) {
        global $CFG;
        require_once($CFG->libdir . '/filelib.php');

        // A mock curl is passed when unit testing.
        if($curl) {
            $this->curl = $curl;
        } else {
            $this->curl = new \curl();
        }

        $token = $CFG->accredible_api_key;
        $this->curl_options = array(
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_FAILONERROR'    => true,
            'CURLOPT_HTTPHEADER'     => array(
                'Authorization: Token ' . $token,
                'Content-Type: application/json; charset=utf-8',
                'Accredible-Integration: Moodle'
            )
        );

        $error = null;
    }

    function get($url) {
        return $this->send_req($url, 'get');
    }

    function post($url, $postBody) {
        return $this->send_req($url, 'post', $postBody);
    }

    function put($url, $putBody) {
        return $this->send_req($url, 'put', $putBody);
    }

    private function send_req($url, $method, $postBody = null) {
        $curl = $this->curl;
        $response = $curl->$method($url, $postBody, $this->curl_options);

        if($curl->error) {
            $this->error = $curl->error;
            debugging('<div style="padding-top: 70px; font-size: 0.9rem;"><b>ACCREDIBLE API ERROR</b> ' .
                $curl->error . '<br />' . $method . ' ' . $url . '</div>', DEBUG_DEVELOPER);
        };

        return json_decode($response);
    }
}
