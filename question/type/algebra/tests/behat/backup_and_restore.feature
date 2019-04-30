@qtype @qtype_algebra
Feature: Test duplicating a quiz containing an Algebra question
  As a teacher
  In order re-use my courses containing Algebra questions
  I need to be able to backup and restore them

  Background:
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | C1        | Test questions |
    And the following "questions" exist:
      | questioncategory | qtype       | name             | template     |
      | Test questions   | algebra     | Algebra question | simplemath   |
    And the following "activities" exist:
      | activity   | name      | course | idnumber |
      | quiz       | Test quiz | C1     | quiz1    |
    And quiz "Test quiz" contains the following questions:
      | Algebra question | 1 |
    And I log in as "admin"
    And I am on "Course 1" course homepage

  @javascript
  Scenario: Backup and restore a course containing an algebra question
    When I backup "Course 1" course using this options:
      | Confirmation | Filename | test_backup.mbz |
    And I restore "test_backup.mbz" backup into a new course using this options:
      | Schema | Course name | Course 2 |
    And I navigate to "Question bank" in current page administration
    And I click on "Edit" "link" in the "Algebra question" "table_row"
    Then the following fields match these values:
      | Question name        | Algebra question                                  |
      | Question text        | P(x) = 3x and Q(x) = 4x. Calculate (P + Q)(x)     |
      | General feedback     | Generalfeedback: (P + Q)(x) = 7x.                 |
      | Default mark         | 1                                                 |
      | id_variable_0        | x                                                 |
      | id_varmin_0          | -5                                                |
      | id_varmax_0          | 5                                                 |
      | id_answer_0          | 7*x                                               |
      | id_fraction_0        | 100%                                              |
      | id_feedback_0        | This is a very good answer.                       |
