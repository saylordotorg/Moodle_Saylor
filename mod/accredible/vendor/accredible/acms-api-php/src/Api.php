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
		    	"cohort_id" => $course_id,
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
		    	"cohort_id" => $course_id,
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

		$response = $client->put($this->api_endpoint.'credentials/'.$id, array(
		    'headers' =>  array('Authorization' => 'Token token="'.$this->getAPIKey().'"'),
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
	public function update_group($id, $name = null, $course_name = null, $course_description = null, $course_link = null){

		$data = array(  
		    "group" => array( 
		    	"name" => $name,
		    	"course_name" => $course_name,
				"course_description" => $course_description,
    			"course_link" => $course_link
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

