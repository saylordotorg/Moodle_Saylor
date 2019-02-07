<?php

namespace ACMS\Tests;

use ACMS\Api;

// backward compatibility
if (!class_exists('\PHPUnit\Framework\TestCase') &&
    class_exists('\PHPUnit_Framework_TestCase')) {
    class_alias('\PHPUnit_Framework_TestCase', 'PHPUnit\Framework\TestCase');
}

use PHPUnit\Framework\TestCase;

//fwrite(STDERR, print_r($example_credential, TRUE));

// TODO: Add mocked response tests for speed

class ApiTestEvidence extends TestCase {

    // backward compatibility
    public function expectException($exception) {
        if (!method_exists('TestCase','expectException')) {
            $this->setExpectedException($exception);
        } else {
            $this->expectException($exception);
        }
    }

    public $group;

	protected function setUp(){
        $this->api = new Api("7b47e413b0216b489f0034960db4e84f", true);

        // Create a group
        $group_name = $this->RandomString(20);
        $this->group = $this->api->create_group($group_name, "Test course", "Test course description.");

        // Create a credential
        $this->credential = $this->api->create_credential("John Doe", "john@example.com", $this->group->group->id);
    }

    protected function tearDown(){
        // Remove credential
        $response = $this->api->delete_credential($this->credential->credential->id);

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

    public function testCreateEvidence(){
        $evidence_item = array(
            "evidence_item" => array(
                "description" => "test item",
                "category" => "url",
                "url" => "https://www.accredible.com"
            )
        );

        //Check we can create an evidence item
        $evidence_item = $this->api->create_evidence_item($evidence_item, $this->credential->credential->id);
        $this->assertEquals("test item", $evidence_item->evidence_item->description);
    }

    public function testCreateEvidenceGrade(){
        //Check we can create a grade evidence item
        $evidence_item1 = $this->api->create_evidence_item_grade("90", "some description", $this->credential->credential->id);
        $this->assertEquals("some description", $evidence_item1->evidence_item->description);

        //Check we can't make an invalid grade item
        $this->expectException("\Exception");
        $evidence_item2 = $this->api->create_evidence_item_grade("B", "some description", $this->credential->credential->id);
    }

    public function testCreateEvidenceDuration(){
        //Check we can create a duration evidence item
        $evidence_item1 = $this->api->create_evidence_item_duration(date("Y-m-d", strtotime("2017-10-01")), date("Y-m-d", strtotime("2017-10-10")), $this->credential->credential->id);
        $this->assertEquals("Completed in 9 days", $evidence_item1->evidence_item->description);

        //Check we can't make an invalid duration item
        $this->expectException("\Exception");
        $evidence_item2 = $this->api->create_evidence_item_duration(date("Y-m-d", strtotime("2017-10-01")), date("Y-m-d", strtotime("2017-10-01")), $this->credential->credential->id);
    }

    public function testCreateEvidenceTranscript(){
        $transcript = array(
            "first test" => "10",
            "test 2" => "100",
            "third test" => "50"
        );

        //Check we can create a transcript evidence item
        $evidence_item1 = $this->api->create_evidence_item_transcript($transcript, $this->credential->credential->id);
        $this->assertEquals("Course Transcript", $evidence_item1->evidence_item->description);
    }

}
