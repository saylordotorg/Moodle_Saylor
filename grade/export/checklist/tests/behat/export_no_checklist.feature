@grade @gradeexport @gradeexport_checklist @checklist
Feature: Exporting when there are no checklists on the course, should show a sensible message, not a fatal error

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | 1        | student1@example.com |
      | student2 | Student   | 2        | student2@example.com |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
    And the following "course enrolments" exist:
      | course | user     | role           |
      | C1     | student1 | student        |
      | C1     | student2 | student        |
      | C1     | teacher1 | editingteacher |

  Scenario: The teacher visits the checklist export page
    When I am on the "C1" "gradeexport_checklist > export" page logged in as "teacher1"
    Then I should see "No suitable checklists found"
