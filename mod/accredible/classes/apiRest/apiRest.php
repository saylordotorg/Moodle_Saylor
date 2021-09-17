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

namespace mod_accredible\apiRest;
defined('MOODLE_INTERNAL') || die();

use mod_accredible\client\client;

class apiRest {
    private $api_endpoint;

    private $token;

    public function __construct($token, $url = null) {
        global $CFG;

        $this->api_endpoint = "https://api.accredible.com/v1/";

        if($CFG->is_eu) {
            $this->api_endpoint = "https://eu.api.accredible.com/v1/";
        }

        if(empty($url)) {
            $this->url = "https://staging.accredible.com/v1/";
        }

        $dev_api_endpoint = getenv("ACCREDIBLE_DEV_API_ENDPOINT");
        if($dev_api_endpoint) {
            $this->api_endpoint = $dev_api_endpoint;
            $this->url = $dev_api_endpoint;
        }

        $this->token = $token;
    }

    /**
     * Get Credentials
     * @param String|null $group_id
     * @param String|null $email
     * @param String|null $page_size
     * @param String $page
     * @return stdObject
     */
    function get_credentials($group_id = null, $email = null, $page_size = null, $page = 1) {
        return client::get("{$this->api_endpoint}all_credentials?group_id={$group_id}&email=" . rawurlencode($email) . "&page_size={$page_size}&page={$page}", $this->token);
    }

    /**
     * Generaate a Single Sign On Link for a recipient for a particular credential.
     * @return stdObject
     */
    function recipient_sso_link($credential_id = null, $recipient_id = null, $recipient_email = null, $wallet_view = null, $group_id = null, $redirect_to = null) {

        $data = array(
            "credential_id" => $credential_id,
            "recipient_id" => $recipient_id,
            "recipient_email" => $recipient_email,
            "wallet_view" => $wallet_view,
            "group_id" => $group_id,
            "redirect_to" => $redirect_to,
        );

        $data = $this->strip_empty_keys($data);

        $data = json_encode($data);

        return client::post("{$this->api_endpoint}sso/generate_link", $this->token, $data);
    }

    /**
     * Update a Group
     * @param String $id
     * @param String|null $name
     * @param String|null $course_name
     * @param String|null $course_description
     * @param String|null $course_link
     * @return stdObject
     */
    function update_group($id, $name = null, $course_name = null, $course_description = null, $course_link = null, $design_id = null) {

        $data = array(
            "group" => array(
                "name" => $name,
                "course_name" => $course_name,
                "course_description" => $course_description,
                "course_link" => $course_link,
                "design_id" => $design_id
            )
        );

        $data = $this->strip_empty_keys($data);

        $data = json_encode($data);

        return client::put("{$this->api_endpoint}issuer/groups/{$id}", $this->token, $data);
    }

    /**
     * Create a new Group
     * @param String $name
     * @param String $course_name
     * @param String $course_description
     * @param String|null $course_link
     * @return stdObject
     */
    function create_group($name, $course_name, $course_description, $course_link = null) {

        $data = array(
            "group" => array(
                "name" => $name,
                "course_name" => $course_name,
                "course_description" => $course_description,
                "course_link" => $course_link
            )
        );

        $data = json_encode($data);

        return client::post("{$this->api_endpoint}issuer/groups", $this->token, $data);
    }

    /**
     * Creates a Credential given an existing Group
     * @param String $recipient_name
     * @param String $recipient_email
     * @param String $course_id
     * @param Date|null $issued_on
     * @param Date|null $expired_on
     * @param stdObject|null $custom_attributes
     * @return stdObject
     */
    function create_credential($recipient_name, $recipient_email, $course_id, $issued_on = null, $expired_on = null, $custom_attributes = null) {

        $data = array(
            "credential" => array(
                "group_id" => $course_id,
                "recipient" => array(
                    "name" => $recipient_name,
                    "email" => $recipient_email
                ),
                "issued_on" => $issued_on,
                "expired_on" => $expired_on,
                "custom_attributes" => $custom_attributes
            )
        );

        $data = json_encode($data);

        return client::post("{$this->api_endpoint}credentials", $this->token, $data);
    }

    /**
     * Creates an evidence item on a given credential. This is a general method used by more specific evidence item creations.
     * @param stdObject $evidence_item
     * @return stdObject
     */
    function create_evidence_item($evidence_item, $credential_id) {

        $data = json_encode($evidence_item);

        return client::post("{$this->api_endpoint}credentials/{$credential_id}/evidence_items", $this->token, $data);
    }

    /**
     * Creates a Grade evidence item on a given credential.
     * @param String $start_date
     * @param String $end_date
     * @return stdObject
     */
    function create_evidence_item_duration($start_date, $end_date, $credential_id, $hidden = false) {

        $duration_info = array(
            'start_date' => date("Y-m-d", strtotime($start_date)),
            'end_date' => date("Y-m-d", strtotime($end_date)),
            'duration_in_days' => floor( (strtotime($end_date) - strtotime($start_date)) / 86400)
        );

        // multi day duration
        if ($duration_info['duration_in_days'] && $duration_info['duration_in_days'] != 0) {

            $evidence_item = array(
                "evidence_item" => array(
                    "description" => 'Completed in ' . $duration_info['duration_in_days'] . ' days',
                    "category" => "course_duration",
                    "string_object" => json_encode($duration_info),
                    "hidden" => $hidden
                )
            );

            $result = $this->create_evidence_item($evidence_item, $credential_id);

            return $result;
            // it may be completed in one day
        } else if ($duration_info['start_date'] != $duration_info['end_date']) {
            $duration_info['duration_in_days'] = 1;

            $evidence_item = array(
                "evidence_item" => array(
                    "description" => 'Completed in 1 day',
                    "category" => "course_duration",
                    "string_object" => json_encode($duration_info),
                    "hidden" => $hidden
                )
            );

            $result = $this->create_evidence_item($evidence_item, $credential_id);

            return $result;

        } else {
            throw new \InvalidArgumentException("Enrollment duration must be greater than 0.");
        }
    }

    /**
     * Creates a Credential given an existing Group. This legacy method uses achievement names rather than group IDs.
     * @param String $recipient_name
     * @param String $recipient_email
     * @param String $achievement_name
     * @param Date|null $issued_on
     * @param Date|null $expired_on
     * @param stdObject|null $custom_attributes
     * @return stdObject
     */
    function create_credential_legacy($recipient_name, $recipient_email, $achievement_name, $issued_on = null, $expired_on = null, $course_name = null, $course_description = null, $course_link = null, $custom_attributes = null){

        $data = array(
            "credential" => array(
                "group_name" => $achievement_name,
                "recipient" => array(
                    "name" => $recipient_name,
                    "email" => $recipient_email
                ),
                "issued_on" => $issued_on,
                "expired_on" => $expired_on,
                "custom_attributes" => $custom_attributes,
                "name" => $course_name,
                "description" => $course_description,
                "course_link" => $course_link
            )
        );

        $data = json_encode($data);

        return client::post("{$this->api_endpoint}credentials", $this->token, $data);
    }

    /**
     * Get all Groups
     * @param String $page_size
     * @param String $page
     * @return stdObject
     */
    function get_groups($page_size = nil, $page = 1) {
        return client::get($this->api_endpoint.'issuer/all_groups?page_size=' . $page_size . '&page=' . $page, $this->token);
    }


    /**
     * Strip out keys with a null value from an object http://stackoverflow.com/a/15953991
     * @param stdObject $object
     * @return stdObject
     */
    function strip_empty_keys($object) {

        $json = json_encode($object);
        $json = preg_replace('/,\s*"[^"]+":null|"[^"]+":null,?/', '', $json);
        $object = json_decode($json);

        return $object;
    }

    /**
     * Creates a Grade evidence item on a given credential.
     * @param String $grade - value must be between 0 and 100
     * @return stdObject
     */
    function create_evidence_item_grade($grade, $description, $credential_id, $hidden = false) {

        if (is_numeric($grade) && intval($grade) >= 0 && intval($grade) <= 100) {

            $evidence_item = array(
                "evidence_item" => array(
                    "description" => $description,
                    "category" => "grade",
                    "string_object" => (string) $grade,
                    "hidden" => $hidden
                )
            );

            return $this->create_evidence_item($evidence_item, $credential_id);
        } else {
            throw new \InvalidArgumentException("$grade must be a numeric value between 0 and 100.");
        }
    }
}
