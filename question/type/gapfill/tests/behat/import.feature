@qtype @qtype_gapfill, @qtype_gapfill_import
Feature: Test importing Gapfill questions
  As a teacher
  In order to reuse Gapfill questions
  I need to import them

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | T1        | Teacher1 | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage

  @javascript @_file_upload
  Scenario: import drag and drop into text question.
    And I navigate to "Question bank > Import" in current page administration
    And I set the field "id_format_xml" to "1"
    And I upload "question/type/gapfill/tests/fixtures/gapfill_examples.xml" file to "Import" filemanager
    And I press "id_submitbutton"
    Then I should see "Parsing questions from import file."
    And I should see "Importing 21 questions from file"
    And I press "Continue"
    #This is just the name of one of the example questions imported that will be listed
    And I should see "Alternative_Delimiters"
