@availability @availability_cohort @javascript
Feature: availability_cohort
  In order to control student access to activities
  As a teacher
  I need to set cohort conditions which prevent student access

  Background:
    Given the following "categories" exist:
      | name        | category | idnumber |
      | Category 1  | 0        | CAT1     |
      | Category 2  | 0        | CAT2     |
    And the following "courses" exist:
      | fullname | shortname | format | enablecompletion | category |
      | Course 1 | C1        | topics | 1                | CAT1     |
      | Course 2 | C2        | topics | 1                | CAT2     |
    And the following "users" exist:
      | username |
      | teacher1 |
      | teacher2 |
      | student1 |
      | student2 |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
      | student2 | C1     | student        |
      | teacher2 | C2     | editingteacher |

  Scenario: Try to add a cohort condition if no cohorts exist yet
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add a "Page" to section "1"
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    Then "Cohort" "button" should not exist in the "Add restriction..." "dialogue"

  Scenario: Try to add a cohort condition if cohorts exist already
    Given the following "cohorts" exist:
      | name     | idnumber |
      | Cohort 1 | CH1      |
      | Cohort 2 | CH2      |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add a "Page" to section "1"
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    Then "Cohort" "button" should exist in the "Add restriction..." "dialogue"

  Scenario: Add cohort condition for any cohort to a page activity and try to view it with a student who isn't a member of any cohort
    Given the following "cohorts" exist:
      | name     | idnumber |
      | Cohort 1 | CH1      |
      | Cohort 2 | CH2      |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add a "Page" to section "1"
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    And I click on "Cohort" "button"
    And I set the field "Cohort" to "(Any cohort)"
    And I click on ".availability-item .availability-eye img" "css_element"
    And I set the following fields to these values:
      | Name         | P1 |
      | Description  | x  |
      | Page content | x  |
    And I click on "Save and return to course" "button"
    When I log out
    And I log in as "student1"
    And I am on "Course 1" course homepage
    Then I should not see "P1" in the "region-main" "region"

  Scenario: Add cohort condition with particular cohorts to page activities and try to view it with a student who isn't a member of any cohort
    Given the following "cohorts" exist:
      | name     | idnumber |
      | Cohort 1 | CH1      |
      | Cohort 2 | CH2      |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add a "Page" to section "1"
    And I set the following fields to these values:
      | Name         | P1 |
      | Description  | x  |
      | Page content | x  |
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    And I click on "Cohort" "button"
    And I set the field "Cohort" to "Cohort 1"
    And I click on ".availability-item .availability-eye img" "css_element"
    And I click on "Save and return to course" "button"
    And I add a "Page" to section "2"
    And I set the following fields to these values:
      | Name         | P2 |
      | Description  | x  |
      | Page content | x  |
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    And I click on "Cohort" "button"
    And I set the field "Cohort" to "Cohort 2"
    And I click on ".availability-item .availability-eye img" "css_element"
    And I click on "Save and return to course" "button"
    When I log out
    And I log in as "student1"
    And I am on "Course 1" course homepage
    Then I should not see "P1" in the "region-main" "region"
    And I should not see "P2" in the "region-main" "region"

  Scenario: Add cohort condition for any system to a page activity and try to view it with students who are a member of particular cohorts
    Given the following "cohorts" exist:
      | name     | idnumber |
      | Cohort 1 | CH1      |
      | Cohort 2 | CH2      |
    And the following "cohort members" exist:
      | user     | cohort |
      | student1 | CH1    |
      | student2 | CH2    |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add a "Page" to section "1"
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    And I click on "Cohort" "button"
    And I set the field "Cohort" to "(Any cohort)"
    And I click on ".availability-item .availability-eye img" "css_element"
    And I set the following fields to these values:
      | Name         | P1 |
      | Description  | x  |
      | Page content | x  |
    And I click on "Save and return to course" "button"
    When I log out
    And I log in as "student1"
    And I am on "Course 1" course homepage
    Then I should see "P1" in the "region-main" "region"
    And I log out
    And I log in as "student2"
    And I am on "Course 1" course homepage
    Then I should see "P1" in the "region-main" "region"

  Scenario: Add cohort condition with particular cohorts to page activities and try to view it with students who are a member of particular cohorts
    Given the following "cohorts" exist:
      | name     | idnumber |
      | Cohort 1 | CH1      |
      | Cohort 2 | CH2      |
    And the following "cohort members" exist:
      | user     | cohort |
      | student1 | CH1    |
      | student2 | CH2    |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add a "Page" to section "1"
    And I set the following fields to these values:
      | Name         | P1 |
      | Description  | x  |
      | Page content | x  |
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    And I click on "Cohort" "button"
    And I set the field "Cohort" to "Cohort 1"
    And I click on ".availability-item .availability-eye img" "css_element"
    And I click on "Save and return to course" "button"
    And I add a "Page" to section "2"
    And I set the following fields to these values:
      | Name         | P2 |
      | Description  | x  |
      | Page content | x  |
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    And I click on "Cohort" "button"
    And I set the field "Cohort" to "Cohort 2"
    And I click on ".availability-item .availability-eye img" "css_element"
    And I click on "Save and return to course" "button"
    When I log out
    And I log in as "student1"
    And I am on "Course 1" course homepage
    Then I should see "P1" in the "region-main" "region"
    And I should not see "P2" in the "region-main" "region"
    And I log out
    And I log in as "student2"
    And I am on "Course 1" course homepage
    Then I should not see "P1" in the "region-main" "region"
    And I should see "P2" in the "region-main" "region"

  Scenario: Try to add a cohort condition for category cohorts
    Given the following "cohorts" exist:
      | name                 | idnumber | contextlevel | reference | visible |
      | Cohort in category 1 | CCH1     | Category     | CAT1      | 1       |
      | Cohort in category 2 | CCH2     | Category     | CAT2      | 1       |
    When I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add a "Page" to section "1"
    And I set the following fields to these values:
      | Name         | P1 |
      | Description  | x  |
      | Page content | x  |
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    And I click on "Cohort" "button"
    Then the "Cohort" select box should contain "Cohort in category 1"
    And the "Cohort" select box should not contain "Cohort in category 2"
    And I log out
    And I log in as "teacher2"
    And I am on "Course 2" course homepage with editing mode on
    And I add a "Page" to section "1"
    And I set the following fields to these values:
      | Name         | P1 |
      | Description  | x  |
      | Page content | x  |
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    And I click on "Cohort" "button"
    Then the "Cohort" select box should contain "Cohort in category 2"
    And the "Cohort" select box should not contain "Cohort in category 1"
