@grade @gradeexport_checklist @checklist
Feature: Checklists export without warnings or errors

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | 1        | student1@example.com |
      | student2 | Student   | 2        | student2@example.com |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
      | Course 2 | C2        |
    And the following "course enrolments" exist:
      | course | user     | role           |
      | C1     | student1 | student        |
      | C1     | student2 | student        |
      | C1     | teacher1 | editingteacher |
      | C2     | student1 | student        |
      | C2     | teacher1 | editingteacher |
    And I log in as "teacher1"
    And I am on site homepage
    And I follow "Course 1"
    And I turn editing mode on
    And I add a "Checklist" to section "1" and I fill the form with:
      | Checklist | Test checklist 1 |
    And I add a "Checklist" to section "1" and I fill the form with:
      | Checklist | Test checklist 2 |
    And I am on site homepage
    And I follow "Course 2"
    And I add a "Checklist" to section "1" and I fill the form with:
      | Checklist | Test checklist 3 |
    And the following items exist in checklist "Test checklist 1":
      | text   |
      | Item 1 |
      | Item 2 |
      | Item 3 |
    And the following items are checked off in checklist "Test checklist 1" for user "student1":
      | itemtext | studentmark |
      | Item 1   | yes         |
      | Item 2   | yes         |
    And the following items are checked off in checklist "Test checklist 1" for user "student2":
      | itemtext | studentmark |
      | Item 1   | yes         |
      | Item 3   | no          |
    And the following items exist in checklist "Test checklist 2":
      | text   |
      | Item 4 |
      | Item 5 |
      | Item 6 |
    And the following items are checked off in checklist "Test checklist 2" for user "student1":
      | itemtext | studentmark |
      | Item 4   | yes         |
      | Item 5   | no          |
      | Item 6   | no          |
    And the following items exist in checklist "Test checklist 3":
      | text   |
      | Item 7 |
      | Item 8 |
    And the following items are checked off in checklist "Test checklist 3" for user "student2":
      | itemtext | studentmark |
      | Item 7   | yes         |
      | Item 8   | yes         |
    And I am on site homepage

  Scenario: The teacher exports the checklist
    When I follow "Course 1"
    And I navigate to "Gradebook setup" node in "Course administration"
    And I navigate to "Checklist" node in "Grade administration > Export"
    And I set the following fields to these values:
      | Checklist to export     | Test checklist 1 |
      | Percentage column       | 1                |
      | Percentage row          | 1                |
      | Percentage for headings | 1                |
    And I press "Export Excel file"

    # Check the item total percentages (across both students).
    Then I should see "(0, 7) = Item 1"
    And I should see "(1, 7) = 100%"
    And I should see "(0, 8) = Item 2"
    And I should see "(1, 8) = 50%"
    And I should see "(0, 9) = Item 3"
    And I should see "(1, 9) = 0%"

    # Check the results for student1.
    And I should see "(2, 0) = 1"
    And I should see "(2, 1) = Student"
    And I should see "(2, 2) = student1"
    And I should see "(2, 6) = 67%"
    And I should see "(2, 7) = 1"
    And I should see "(2, 8) = 1"
    And I should not see "(2, 9) = 1"

    # Check the results for student2.
    And I should see "(3, 0) = 2"
    And I should see "(3, 1) = Student"
    And I should see "(3, 2) = student2"
    And I should see "(3, 6) = 33%"
    And I should see "(3, 7) = 1"
    And I should not see "(3, 8) = 1"
    And I should not see "(3, 9) = 1"

    # Check no extra data from other checklists.
    And I should not see "Item 5"
    And I should not see "Item 7"
