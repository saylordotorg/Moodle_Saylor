<?php

namespace ACMS;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

/**
 * API Wrappers
 */
class Api {

	private $api_key;

	private $api_endpoint = "https://api.accredible.com/v1/";

	/**
	 * Set API Key
	 * @param String $key
	 * @return null
	 */
	public function setAPIKey($key) {
        $this->api_key = $key;
    }

    /**
     * Get API Key
     * @return String
     */
    public function getAPIKey() {
        return $this->api_key;
    }

    /**
     * Contruct API instance
     * @param String $api_key
     * @param boolean|null $test
     * @return null
     */
    public function __construct($api_key, $test = null){
        $this->setAPIKey($api_key);

        if (null !== $test) {
    	    $this->api_endpoint = "https://staging.accredible.com/v1/";
    	}
    }

    /**
     * Strip out keys with a null value from an object http://stackoverflow.com/a/15953991
     * @param stdObject $object
     * @return stdObject
     */
    public function strip_empty_keys($object){

		$json = json_encode($object);
		$json = preg_replace('/,\s*"[^"]+":null|"[^"]+":null,?/', '', $json);
		$object = json_decode($json);

		return $object;
    }

    /**
     * Get a Credential
     * @param String $id
     * @return stdObject
     */
	public function get_credential($id){
		$client = new \GuzzleHttp\Client();

		$params = array('headers' =>  array('Authorization' => 'Token token="'.$this->getAPIKey().'"'));

		$response = $client->get($this->api_endpoint . 'credentials/' . $id, $params);

		$result = json_decode($response->getBody());
		return $result;
	}

	/**
	 * Get Credentials
	 * @param String|null $group_id
	 * @param String|null $email
	 * @param String|null $page_size
	 * @param String $page
	 * @return stdObject
	 */
	public function get_credentials($group_id = null, $email = null, $page_size = null, $page = 1){
		$client = new \GuzzleHttp\Client();

		$params = array('headers' =>  array('Authorization' => 'Token token="'.$this->getAPIKey().'"'));

		$response = $client->get($this->api_endpoint. 'all_credentials?group_id=' . $group_id . '&email=' . rawurlencode($email) . '&page_size=' . $page_size . '&page=' . $page, $params);

		$result = json_decode($response->getBody());
		return $result;
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
	public function create_credential($recipient_name, $recipient_email, $course_id, $issued_on = null, $expired_on = null, $custom_attributes = null){

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

		$client = new \GuzzleHttp\Client();

		$params = array('Authorization' => 'Token token="'.$this->getAPIKey().'"');

		$response = $client->post($this->api_endpoint.'credentials', array(
		    'headers' => $params,
		    'json' => $data
		));

		$result = json_decode($response->getBody());

		return $result;
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
    public function create_credential_legacy($recipient_name, $recipient_email, $achievement_name, $issued_on = null, $expired_on = null, $course_name = null, $course_description = null, $course_link = null, $custom_attributes = null){

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

        $client = new \GuzzleHttp\Client();

        $params = array('Authorization' => 'Token token="'.$this->getAPIKey().'"');

        $response = $client->post($this->api_endpoint.'credentials', array(
            'headers' => $params,
            'json' => $data
        ));

        $result = json_decode($response->getBody());

        return $result;
    }


	/**
	 * Updates a Credential
	 * @param type $id
	 * @param String|null $recipient_name
	 * @param String|null $recipient_email
	 * @param String|null $course_id
	 * @param Date|null $issued_on
	 * @param Date|null $expired_on
	 * @param stdObject|null $custom_attributes
	 * @return stdObject
	 */
	public function update_credential($id, $recipient_name = null, $recipient_email = null, $course_id = null, $issued_on = null, $expired_on = null, $custom_attributes = null){

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
		$data = $this->strip_empty_keys($data);

		$client = new \GuzzleHttp\Client();

        $params = array('Authorization' => 'Token token="'.$this->getAPIKey().'"');

		$response = $client->put($this->api_endpoint.'credentials/'.$id, array(
		    'headers' =>  $params,
		    'json' => $data
		));

		$result = json_decode($response->getBody());

		return $result;
	}

	/**
	 * Delete a Credential
	 * @param String $id
	 * @return stdObject
	 */
	public function delete_credential($id){
		$client = new \GuzzleHttp\Client();

		$response = $client->delete($this->api_endpoint.'credentials/' . $id, array('headers' =>  array('Authorization' => 'Token token="'.$this->getAPIKey().'"')));

		$result = json_decode($response->getBody());

		return $result;
	}

	/**
	 * Create a new Group
	 * @param String $name
	 * @param String $course_name
	 * @param String $course_description
	 * @param String|null $course_link
	 * @return stdObject
	 */
	public function create_group($name, $course_name, $course_description, $course_link = null){
		$data = array(
		    "group" => array(
		    	"name" => $name,
		    	"course_name" => $course_name,
				"course_description" => $course_description,
    			"course_link" => $course_link
		    )
		);

		$client = new \GuzzleHttp\Client();

		$response = $client->post($this->api_endpoint.'issuer/groups', array(
		    'headers' =>  array('Authorization' => 'Token token="'.$this->getAPIKey().'"'),
		    'json' => $data
		));

		$result = json_decode($response->getBody());

		return $result;
	}

	/**
	 * Get a Group
	 * @param String $id
	 * @return stdObject
	 */
	public function get_group($id){
		$client = new \GuzzleHttp\Client();

		$response = $client->get($this->api_endpoint.'issuer/groups/' . $id, array('headers' =>  array('Authorization' => 'Token token="'.$this->getAPIKey().'"')));

		$result = json_decode($response->getBody());
		return $result;
	}

	/**
	 * Get all Groups
	 * @param String $page_size
	 * @param String $page
	 * @return stdObject
	 */
	public function get_groups($page_size = nil, $page = 1){
		$client = new \GuzzleHttp\Client();

		$response = $client->get($this->api_endpoint.'issuer/all_groups?page_size=' . $page_size . '&page=' . $page, array('headers' =>  array('Authorization' => 'Token token="'.$this->getAPIKey().'"')));

		$result = json_decode($response->getBody());
		return $result;
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
	public function update_group($id, $name = null, $course_name = null, $course_description = null, $course_link = null, $design_id = null){

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

		$client = new \GuzzleHttp\Client();

		$response = $client->put($this->api_endpoint.'issuer/groups/'.$id, array(
		    'headers' =>  array('Authorization' => 'Token token="'.$this->getAPIKey().'"'),
		    'json' => $data
		));

		$result = json_decode($response->getBody());

		return $result;
	}

	/**
	 * Delete a Group
	 * @param String $id
	 * @return stdObject
	 */
	public function delete_group($id){
		$client = new \GuzzleHttp\Client();

		$response = $client->delete($this->api_endpoint.'issuer/groups/' . $id, array('headers' =>  array('Authorization' => 'Token token="'.$this->getAPIKey().'"')));

		$result = json_decode($response->getBody());

		return $result;
	}

    /**
     * Get all Designs
     * @param String $page_size
     * @param String $page
     * @return stdObject
     */
    public function get_designs($page_size = nil, $page = 1){
        $client = new \GuzzleHttp\Client();

        $response = $client->get($this->api_endpoint.'issuer/all_designs?page_size=' . $page_size . '&page=' . $page, array('headers' =>  array('Authorization' => 'Token token="'.$this->getAPIKey().'"')));

        $result = json_decode($response->getBody());
        return $result;
    }

    /**
     * Creates an evidence item on a given credential. This is a general method used by more specific evidence item creations.
     * @param stdObject $evidence_item
     * @return stdObject
     */
    public function create_evidence_item($evidence_item, $credential_id){

        $client = new \GuzzleHttp\Client();

        $response = $client->post($this->api_endpoint.'credentials/'.$credential_id.'/evidence_items', array(
            'headers' =>  array('Authorization' => 'Token token="'.$this->getAPIKey().'"'),
            'json' => $evidence_item
        ));

        $result = json_decode($response->getBody());

        return $result;
    }

    /**
     * Creates a Grade evidence item on a given credential.
     * @param String $grade - value must be between 0 and 100
     * @return stdObject
     */
    public function create_evidence_item_grade($grade, $description, $credential_id, $hidden = false){

        if(is_numeric($grade) && intval($grade) >= 0 && intval($grade) <= 100){

            $evidence_item = array(
                "evidence_item" => array(
                    "description" => $description,
                    "category" => "grade",
                    "string_object" => (string) $grade,
                    "hidden" => $hidden
                )
            );

            $result = $this->create_evidence_item($evidence_item, $credential_id);

            return $result;

        } else {
            throw new \InvalidArgumentException("$grade must be a numeric value between 0 and 100.");
        }
    }

    /**
     * Creates a Grade evidence item on a given credential.
     * @param String $start_date
     * @param String $end_date
     * @return stdObject
     */
    public function create_evidence_item_duration($start_date, $end_date, $credential_id, $hidden = false){

        $duration_info = array(
            'start_date' =>  date("Y-m-d", strtotime($start_date)),
            'end_date' => date("Y-m-d", strtotime($end_date)),
            'duration_in_days' => floor( (strtotime($end_date) - strtotime($start_date)) / 86400)
        );

        // multi day duration
        if($duration_info['duration_in_days'] && $duration_info['duration_in_days'] != 0){

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
        } else if($duration_info['start_date'] != $duration_info['end_date']){
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
     * Creates a Transcript evidence item on a given credential.
     * @param String $transcript - Hash of key values.
     * @return stdObject
     */
    public function create_evidence_item_transcript($transcript, $credential_id, $hidden = false){

        $transcript_items = array();

        foreach ($transcript as $key => $value) {
            array_push($transcript_items, array(
                'category' => $key,
                'percent' => $value
                )
            );
        }

        $evidence_item = array(
            "evidence_item" => array(
                "description" => 'Course Transcript',
                "category" => "transcript",
                "string_object" => json_encode($transcript_items),
                "hidden" => $hidden
            )
        );

        $result = $this->create_evidence_item($evidence_item, $credential_id);

        return $result;
    }

    /**
     * Generaate a Single Sign On Link for a recipient for a particular credential.
     * @return stdObject
     */
    public function recipient_sso_link($credential_id = null, $recipient_id = null, $recipient_email = null, $wallet_view = null, $group_id = null, $redirect_to = null){

        $data = array(
            "credential_id" => $credential_id,
            "recipient_id" => $recipient_id,
            "recipient_email" => $recipient_email,
            "wallet_view" => $wallet_view,
            "group_id" => $group_id,
            "redirect_to" => $redirect_to,
        );

        $data = $this->strip_empty_keys($data);

        $client = new \GuzzleHttp\Client();

        $response = $client->post($this->api_endpoint.'sso/generate_link', array(
            'headers' =>  array('Authorization' => 'Token token="'.$this->getAPIKey().'"'),
            'json' => $data
        ));

        $result = json_decode($response->getBody());

        return $result;
    }



	/**
	 * Send an array of batch requests
	 * @param Array $requests
	 * @return stdObject
	 */
	public function send_batch_requests($requests){
		$client = new \GuzzleHttp\Client();

		$response = $client->post($this->api_endpoint.'batch', array(
		    'headers' =>  array('Authorization' => 'Token token="'.$this->getAPIKey().'"'),
		    'json' => array( "ops" => $requests, "sequential" => true )
		));

		$result = json_decode($response->getBody());

		return $result;
	}

}
