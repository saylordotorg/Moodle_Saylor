@availability @availability_mobileapp
Feature: availability_mobileapp
  In order to control student access to activities
  As a teacher
  I need to set Mobile app access conditions which prevent student access

  Background:
    Given the following "courses" exist:
      | fullname | shortname | format | enablecompletion |
      | Course 1 | C1        | topics | 1                |
    And the following "users" exist:
      | username |
      | teacher1 |
      | student1 |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
    And the following config values are set as admin:
      | enableavailability | 1 |
      | enablewebservices | 1 |
      | enablemobilewebservice | 1 |
    And I log in as "admin"
    And I navigate to "Mobile settings" node in "Site administration > Mobile app"
    And I click on "Enable web services for mobile devices" "checkbox"
    And I click on "Save changes" "button"
    And I log out

  @javascript
  Scenario: Test condition
    # Basic setup.
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on

    # Add a Page with a Mobile app condition that does not match.
    And I add a "Page" to section "1"
    And I set the following fields to these values:
      | Name         | Page 1 |
      | Description  | Test   |
      | Page content | Test   |
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    And I click on "Mobile app" "button" in the "Add restriction..." "dialogue"
    And I click on ".availability-item .availability-eye img" "css_element"
    And I set the field "Mobile app" to "Access using the Mobile app"
    And I press "Save and return to course"

    # Add a Page with a date condition that does match.
    And I add a "Page" to section "2"
    And I set the following fields to these values:
      | Name         | Page 2 |
      | Description  | Test   |
      | Page content | Test   |
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    And I click on "Mobile app" "button" in the "Add restriction..." "dialogue"
    And I click on ".availability-item .availability-eye img" "css_element"
    And I set the field "Mobile app" to "Access NOT using the Mobile app"
    And I press "Save and return to course"

    # Log back in as student.
    When I log out
    And I log in as "student1"
    And I am on site homepage
    And I follow "Course 1"

    # Page 1 should appear, but page 2 does not.
    Then I should not see "Page 1" in the "region-main" "region"
    And I should see "Page 2" in the "region-main" "region"
