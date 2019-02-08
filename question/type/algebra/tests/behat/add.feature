@qtype @qtype_algebra
Feature: Test creating an Algebra question
  As a teacher
  In order to test my students
  I need to be able to create an Algebra question

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email               |
      | teacher1 | T1        | Teacher1 | teacher1@moodle.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration

  Scenario: Create an Algebra question
    When I add a "Algebra" question filling the form with:
      | Question name        | algebra-001                               |
      | Question text        | What is the derivative of f(x) = x^2 ?    |
      | General feedback     | The correct answer is 2*x                 |
      | Default mark         | 1                                         |
      | id_variable_0        | x                                         |
      | id_varmin_0          | -5                                        |
      | id_varmax_0          | 5                                         |
      | id_answer_0          | 2*x                                       |
      | id_fraction_0        | 100%                                      |
      | id_feedback_0        | Well done. 2*x is correct.                |
      | id_answer_1          | x                                         |
      | id_fraction_1        | 20%                                       |
      | id_feedback_1        | It seems that you forgot something.       |
    Then I should see "algebra-001"
