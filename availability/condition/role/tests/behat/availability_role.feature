@availability @availability_role
Feature: availability_role
  In order to control student access to activities
  As a teacher
  I need to set role conditions which prevent student access

  Background:
    Given the following "courses" exist:
      | fullname | shortname | format | enablecompletion |
      | Course 1 | C1        | topics | 1                |
    And the following "users" exist:
      | username |
      | teacher1 |
      | student1 |
      | student2 |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
    And the following config values are set as admin:
      | enableavailability  | 1 |

  @javascript
  Scenario: Test condition
    # Basic setup.
    Given I log in as "teacher1"
    And I am on site homepage
    And I am on "Course 1" course homepage
    And I turn editing mode on

    # Start to add a Page that can be accessed by teachers only.
    And I add a "Page" to section "1"
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    And I click on "Role" "button"
    And I set the field "Role" to "Teacher"
    And I click on ".availability-item .availability-eye img" "css_element"
    And I set the following fields to these values:
      | Name         | P1 |
      | Description  | x  |
      | Page content | x  |
    And I click on "Save and return to course" "button"
    Then I should see "P1" in the "region-main" "region"

    And I add a "Page" to section "2"
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    And I click on "Role" "button"
    And I set the field "Role" to "Manager"
    And I click on ".availability-item .availability-eye img" "css_element"
    And I set the following fields to these values:
      | Name         | P2 |
      | Description  | x2  |
      | Page content | x2  |
    And I click on "Save and return to course" "button"
    Then I should see "P2" in the "region-main" "region"

    # Log back in as student.
    When I log out
    And I log in as "student1"
    And I am on "Course 1" course homepage

    # No pages should appear yet.
    Then I should not see "P1" in the "region-main" "region"
    And I should not see "P2" in the "region-main" "region"

    # Log back in as teacher.
    When I log out
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I am on site homepage
    And I am on "Course 1" course homepage
    And I turn editing mode on
    And I open "P1" actions menu
    And I click on "Edit settings" "link" in the "P1" activity
    Then I expand all fieldsets
    And I click on ".availability-item .availability-delete" "css_element"
    And I click on "Save and return to course" "button"

    # Log back in as student.
    When I log out
    And I log in as "student1"
    And I am on "Course 1" course homepage
    Then I should see "P1" in the "region-main" "region"
    And I should not see "P2" in the "region-main" "region"

  @javascript
  Scenario: Guest role
    # Basic setup.
    Given the following config values are set as admin:
      | config                   | value | plugin            |
      | setting_supportguestrole | YES   | availability_role |

    When I log in as "teacher1"
    And I am on "Course 1" course homepage

    Given I navigate to "Users > Enrolment methods" in current page administration
    And I click on "Edit" "link" in the "Guest access" "table_row"
    And I set the following fields to these values:
      | Allow guest access | Yes |
    And I press "Save changes"
    And I am on "Course 1" course homepage
    And I turn editing mode on

    # Start to add a Page that can be accessed by teachers only.
    And I add a "Page" to section "1"
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    And I click on "Role" "button"
    And I set the field "Role" to "Teacher"
    And I click on ".availability-item .availability-eye img" "css_element"
    And I set the following fields to these values:
      | Name         | P1 |
      | Description  | x  |
      | Page content | x  |
    And I click on "Save and return to course" "button"
    Then I should see "P1" in the "region-main" "region"

    # Log in as guest.
    When I log out
    And I log in as "student2"
    And I am on "Course 1" course homepage

    # No page should appear yet.
    Then I should not see "P1" in the "region-main" "region"

    # Log back in as teacher.
    When I log out
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I turn editing mode on
    And I open "P1" actions menu
    And I click on "Edit settings" "link" in the "P1" activity
    Then I expand all fieldsets
    And I click on ".availability-item .availability-delete" "css_element"
    And I click on "Save and return to course" "button"

    And I add a "Page" to section "2"
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    And I click on "Role" "button"
    And I set the field "Role" to "Guest"
    And I click on ".availability-item .availability-eye img" "css_element"
    And I set the following fields to these values:
      | Name         | P2 |
      | Description  | x2  |
      | Page content | x2  |
    And I click on "Save and return to course" "button"

    And I add a "Page" to section "3"
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    And I click on "Role" "button"
    And I set the field "Role" to "Student"
    And I click on ".availability-item .availability-eye img" "css_element"
    And I set the following fields to these values:
      | Name         | P3 |
      | Description  | x2  |
      | Page content | x2  |
    And I click on "Save and return to course" "button"

    # Log back in as guest.
    When I log out
    And I log in as "student2"
    And I am on "Course 1" course homepage
    Then I should see "P1" in the "region-main" "region"
    And I should see "P2" in the "region-main" "region"
    And I should not see "P3" in the "region-main" "region"

    # Log back in as student.
    When I log out
    And I log in as "student1"
    And I am on "Course 1" course homepage
    Then I should see "P1" in the "region-main" "region"
    And I should not see "P2" in the "region-main" "region"
    And I should see "P3" in the "region-main" "region"
