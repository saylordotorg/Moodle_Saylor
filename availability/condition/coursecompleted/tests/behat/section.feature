@ewallah @availability @availability_coursecompleted
Feature: Section 0 availability_coursecompleted
  Section 0 cannot be restricted

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category | enablecompletion |
      | Course 1 | C1        | 0        | 1                |
    And the following "users" exist:
      | username |
      | teacher1 |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |

  @javascript
  Scenario: Restrict section0 on completing course
    When I log in as "admin"
    And I am on "Course 1" course homepage
    And I navigate to "Course completion" in current page administration
    And I expand all fieldsets
    And I set the field "Teacher" to "1"
    And I press "Save changes"
    And I turn editing mode on
    And I edit the section "0"
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    Then "Course completed" "button" should exist in the "Add restriction..." "dialogue"

  @javascript
  Scenario: Section0 cannot be restricted without criteria
    When I log in as "admin"
    And I am on "Course 1" course homepage
    And I turn editing mode on
    And I edit the section "0"
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    Then "Course completed" "button" should not exist in the "Add restriction..." "dialogue"
