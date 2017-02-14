<?php

namespace ACMS\Tests;

use ACMS\Api;

//fwrite(STDERR, print_r($example_credential, TRUE));

// TODO: Add mocked response tests for speed

class ApiTest extends \PHPUnit_Framework_TestCase {

    public $group;

	protected function setUp(){
        $this->api = new Api($_SERVER['API_KEY'], true);

        // Create a group
        $group_name = $this->RandomString(20);
        $this->group = $this->api->create_group($group_name, "Test course", "Test course description.");
    }

    protected function tearDown(){
        // Remove group
    	$response = $this->api->delete_group($this->group->group->id);
    }

    // http://stackoverflow.com/a/12570458
    protected function RandomString($length) {
        $keys = array_merge(range(0,9), range('a', 'z'));

        $key = "";
        for($i=0; $i < $length; $i++) {
            $key .= $keys[mt_rand(0, count($keys) - 1)];
        }
        return $key;
    }

    public function testSetAPIKey(){
        // Check the API key is set
        $this->assertEquals($_SERVER['API_KEY'], $this->api->getAPIKey());
    }

    public function testGetCredential(){
        $new_credential = $this->api->create_credential("John Doe", "john@example.com", $this->group->group->id);

    	// Check if we can get a Credential
        $example_credential = $this->api->get_credential($new_credential->credential->id);
		$this->assertEquals($new_credential->credential->id, $example_credential->credential->id);

        //cleanup
        $this->api->delete_credential($new_credential->credential->id);
    }

    public function testGetCredentials(){
    	$new_credential = $this->api->create_credential("John Doe", "john@example.com", $this->group->group->id);

    	// Check if we can get credentials given an email
		$example_credentials = $this->api->get_credentials(null, "john@example.com", 1);
		$example_credential = array_values($example_credentials->credentials)[0];
		
		$this->assertEquals("john@example.com", $example_credential->recipient->email);

		//cleanup
		$this->api->delete_credential($new_credential->credential->id);
    }

    public function testCreateCredential(){
    	//Check we can create a Credential
		$new_credential = $this->api->create_credential("John Doe", "john@example.com", $this->group->group->id);
		$this->assertEquals("John Doe", $new_credential->credential->recipient->name);

		//cleanup
		$this->api->delete_credential($new_credential->credential->id);
    }

    public function testUpdateCredential(){
    	$new_credential = $this->api->create_credential("John Doe", "john@example.com", $this->group->group->id);

    	//Check we can update a Credential
		$updated_credential = $this->api->update_credential($new_credential->credential->id, "Jonathan Doe");
		$this->assertEquals("Jonathan Doe", $updated_credential->credential->recipient->name);

		//cleanup
		$this->api->delete_credential($updated_credential->credential->id);
    }

    public function testDeleteCredential(){
    	$new_credential = $this->api->create_credential("John Doe", "john@example.com", $this->group->group->id);

    	// Can we delete a Credential
		$response = $this->api->delete_credential($new_credential->credential->id);
		$this->assertEquals("John Doe", $response->credential->recipient->name);
    }

    public function testGetGroup(){

    	// Can we get a group?
		$requested_group = $this->api->get_group($this->group->group->id);
		$this->assertEquals($this->group->group->name, $requested_group->group->name);
    }

    // TODO - implement
  //   public function testGetGroups(){
  //       $group_name = $this->RandomString(20);

  //   	$group = $this->api->create_group($group_name, "Test course", "Test course description.");

  //   	// Can we get a group?
		// $groups = $this->api->get_groups(1);
		// $example_group = array_values($groups->groups)[0];

		// $this->assertEquals($group_name, $example_group->name);

		// //cleanup
		// $response = $this->api->delete_group($example_group->id);
  //   }

    public function testCreateGroup(){
        $group_name = $this->RandomString(20);

    	// Can we create a Group
		$group = $this->api->create_group($group_name, "Test course", "Test course description.");
		$this->assertEquals($group_name, $group->group->name);

		//cleanup
		$response = $this->api->delete_group($group->group->id);
    }

    public function testUpdateGroup(){

        $new_name = $this->RandomString(20);

    	// Can we update a group?
		$requested_group = $this->api->update_group($this->group->group->id, $new_name);
		$this->assertEquals($new_name, $requested_group->group->name);
    }

    public function testDeleteGroup(){
        $group_name = $this->RandomString(20);

    	$group = $this->api->create_group($group_name, "Test course", "Test course description.");

    	// Can we delete a group?
		$response = $this->api->delete_group($group->group->id);
		$this->assertEquals($group_name, $response->group->name);
    }

    public function testSendBatchRequests(){
        $group_name = $this->RandomString(20);

        $group_data = array(  
            "group" => array( 
                "name" => $group_name,
                "course_name" => "Example Course",
                "course_description" => "Example Description",
                "course_link" => "https://www.accredible.com"
            ) 
        );

        $requests = [
            ["method" => "get",    "url" => "/v1/credentials/10000005"],
            ["method" => "post",   "url" => "/v1/issuer/groups",        "params" => $group_data]
        ];

        $response = $this->api->send_batch_requests($requests);

        $response1 = json_decode($response->results[0]->body);
        $this->assertEquals("10000005", $response1->credential->id);

        $response2 = json_decode($response->results[1]->body);
        $this->assertEquals($group_name, $response2->group->name);

    }

}