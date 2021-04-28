@availability @availability_cohort
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

  @javascript
  Scenario: Test condition
    # Basic setup.
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on

    # Start to add a Page. If there aren't any cohorts, there's no Cohort option.
    And I add a "Page" to section "1"
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    Then "Cohort" "button" should not exist in the "Add restriction..." "dialogue"
    And I click on "Cancel" "button" in the "Add restriction..." "dialogue"

    # Back to course page but now cohorts are existent.
    Given the following "cohorts" exist:
      | name     | idnumber |
      | Cohort 1 | CH1      |
      | Cohort 2 | CH2      |
    # This step used to be 'And I follow "C1"', but Chrome thinks the breadcrumb
    # is not clickable, so we'll go via the home page instead.
    And I am on "Course 1" course homepage
    And I add a "Page" to section "1"
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    Then "Cohort" "button" should exist in the "Add restriction..." "dialogue"

    # Page P1 any cohort.
    Given I click on "Cohort" "button"
    And I set the field "Cohort" to "(Any cohort)"
    And I click on ".availability-item .availability-eye img" "css_element"
    And I set the following fields to these values:
      | Name         | P1 |
      | Description  | x  |
      | Page content | x  |
    And I click on "Save and return to course" "button"

    # Page P2 with cohort Co1.
    And I add a "Page" to section "2"
    And I set the following fields to these values:
      | Name         | P2 |
      | Description  | x  |
      | Page content | x  |
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    And I click on "Cohort" "button"
    And I set the field "Cohort" to "Cohort 1"
    And I click on ".availability-item .availability-eye img" "css_element"
    And I click on "Save and return to course" "button"

    # Page P3 with cohort Co2.
    And I add a "Page" to section "3"
    And I set the following fields to these values:
      | Name         | P3 |
      | Description  | x  |
      | Page content | x  |
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    And I click on "Cohort" "button"
    And I set the field "Cohort" to "Cohort 2"
    And I click on ".availability-item .availability-eye img" "css_element"
    And I click on "Save and return to course" "button"

    # Log back in as student.
    When I log out
    And I log in as "student1"
    And I am on "Course 1" course homepage

    # No pages should appear yet.
    Then I should not see "P1" in the "region-main" "region"
    And I should not see "P2" in the "region-main" "region"
    And I should not see "P3" in the "region-main" "region"

    # Add to cohort and log out/in again.
    Given the following "cohort members" exist:
      | user     | cohort |
      | student1 | CH1    |
      | student2 | CH2    |
    And I log out
    And I log in as "student1"
    And I am on "Course 1" course homepage

    # P1 (any cohorts) and P2 should show but not P3.
    Then I should see "P1" in the "region-main" "region"
    And I should see "P2" in the "region-main" "region"
    And I should not see "P3" in the "region-main" "region"

    # Switch user to student2.
    And I log out
    And I log in as "student2"
    And I am on "Course 1" course homepage

    # P1 (any cohorts) and P3 should show but not P2.
    Then I should see "P1" in the "region-main" "region"
    And I should not see "P2" in the "region-main" "region"
    And I should see "P3" in the "region-main" "region"

    # Login as admin and create category cohorts.
    And I log out
    And I log in as "admin"
    And I navigate to "Users > Cohorts" in site administration
    And I click on "Add new cohort" "link"
    And I set the following fields to these values:
      | name      | Cohort 3   |
      | idnumber  | CH3        |
    And I expand the "Context" autocomplete
    And I click on "Category 1" item in the autocomplete list
    And I click on "Save changes" "button"
    And I click on "Add new cohort" "link"
    And I set the following fields to these values:
      | name      | Cohort 4   |
      | idnumber  | CH4        |
    And I expand the "Context" autocomplete
    And I click on "Category 2" item in the autocomplete list
    And I click on "Save changes" "button"

    # Login as teacher 1.
    And I log out
    And I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add a "Page" to section "4"
    And I set the following fields to these values:
      | Name         | P4 |
      | Description  | x  |
      | Page content | x  |
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    And I click on "Cohort" "button"
    Then the "Cohort" select box should contain "Cohort 3"
    And the "Cohort" select box should not contain "Cohort 4"

    # Login as teacher 2.
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
    Then the "Cohort" select box should contain "Cohort 4"
    And the "Cohort" select box should not contain "Cohort 3"
