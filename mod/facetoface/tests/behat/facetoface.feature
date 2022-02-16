@mod @mod_facetoface
Feature: An activity module facetoface can be created in a course
  In order to see a facetoface activity module in the course
  As a teacher
  I need to be able to add the activity

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1 | weeks |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |

  @javascript
  Scenario: Add a facetoface activity
    When I am on the "C1" "Course" page logged in as "teacher1"
    And I turn editing mode on
    And I click on "Add an activity or resource" "button" in the "General" "section"
    And I click on "Add a new Face-to-Face" "link"
    And I set the following fields to these values:
      | Name        | Some name description for facetoface    |
      | Description | Some description for facetoface         |
      | Third-party email address(es) | somethird@party.email |
    And I press "Save and display"
    Then I should see "Some name description for facetoface"
    And I should see "Some description for facetoface"
    And I am on "Course 1" course homepage
    Then I should see "Some name description for facetoface"
