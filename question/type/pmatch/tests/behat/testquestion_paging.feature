@ou @ou_vle @qtype @qtype_pmatch
Feature: Test the paging functionality of the test this question feature of this question type
  In order to manage the large number of test responses used to test this question
  As an teacher
  I need the test responses paged for pattern match questions.

  Background:
    Given the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1        | topics |
    And the following "users" exist:
      | username | firstname |
      | teacher  | Teacher   |
    And the following "course enrolments" exist:
      | user    | course | role           |
      | teacher | C1     | editingteacher |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | C1        | Test questions |
    And the following "questions" exist:
      | questioncategory | qtype    | name         | template |
      | Test questions   | pmatch   | My first pattern match question | listen    |
    And the default question test responses exist for question "My first pattern match question"

  @javascript
  Scenario: Test this question paging
    # Confirm list responses pagin options is correctly displayed
    When I am on the "My first pattern match question" "qtype_pmatch > test responses" page logged in as teacher
    Then I should see "Pattern-match question testing tool: Testing question: My first pattern match question"
    And I should see "Show responses that are"
    And I should see "Showing the responses for the selected question: My first pattern match question"
    And the field "id_pagesize" matches value "50"

    # No paging should exist yet
    Then ".pagination" "css_element" should not exist

    # Set paging to 10 and check results
    When I set the field "id_pagesize" to "10"
    Then I press "id_submitbutton"
    Then the field "id_pagesize" matches value "10"
    And I should see "1" in the ".pagination .page-item.active" "css_element"
    And I should see "Next" in the ".pagination" "css_element"
