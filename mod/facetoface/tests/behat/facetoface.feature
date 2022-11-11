@mod @mod_facetoface
Feature: An activity module facetoface can be created in a course
  In order to see a facetoface activity module in the course
  As a teacher
  I need to be able to add the activity

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher1@example.com |
      | student1 | Student | 1 | student@example.com |
    And the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1 | weeks |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
      | student1 | C1 | student |

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

  @javascript
  Scenario: Add a facetoface activity with multiple signups enabled
    When I am on the "C1" "Course" page logged in as "teacher1"
    And I turn editing mode on
    And I click on "Add an activity or resource" "button" in the "General" "section"
    And I click on "Add a new Face-to-Face" "link"
    And I set the following fields to these values:
      | Name        | Some name description for facetoface    |
      | Description | Some description for facetoface         |
      | Signup type | Multiple                                |
      | Third-party email address(es) | somethird@party.email |
    And I press "Save and display"
    And I click on "Add a new session" "link"
    And I press "Save changes"
    And I am on the "C1" "Course" page logged in as "student1"
    And I click on "Sign-up" "link"
    And I press "Sign-up"
    And I am on the "C1" "Course" page logged in as "teacher1"
    And I click on "View all sessions" "link"
    And I click on "Add a new session" "link"
    And I press "Save changes"
    And I am on the "C1" "Course" page logged in as "student1"
    And I click on "Sign-up" "link"
    And I press "Sign-up"
    And I am on the "C1" "Course" page logged in as "teacher1"
    And I click on "View all sessions" "link"
    And I click on "(//table[contains(@class, 'f2fsessionlist')]//*//a[text()='Attendees'])[1]" "xpath_element"
    And I should see "Student 1"
    And I am on the "C1" "Course" page logged in as "teacher1"
    And I click on "View all sessions" "link"
    And I click on "(//table[contains(@class, 'f2fsessionlist')]//*//a[text()='Attendees'])[2]" "xpath_element"
    Then I should see "Student 1"

  @javascript
  Scenario: Add a facetoface activity with multiple signups disabled
    When I am on the "C1" "Course" page logged in as "teacher1"
    And I turn editing mode on
    And I click on "Add an activity or resource" "button" in the "General" "section"
    And I click on "Add a new Face-to-Face" "link"
    And I set the following fields to these values:
      | Name        | Some name description for facetoface    |
      | Description | Some description for facetoface         |
      | Third-party email address(es) | somethird@party.email |
    And I press "Save and display"
    And I click on "Add a new session" "link"
    And I press "Save changes"
    And I am on the "C1" "Course" page logged in as "student1"
    And I click on "Sign-up" "link"
    And I press "Sign-up"
    And I am on the "C1" "Course" page logged in as "teacher1"
    And I click on "View all sessions" "link"
    And I click on "Add a new session" "link"
    And I press "Save changes"
    And I am on the "C1" "Course" page logged in as "student1"
    And I should not see "Sign-up"
    And I click on "View all sessions" "link"
    Then I should not see "Sign-up"
