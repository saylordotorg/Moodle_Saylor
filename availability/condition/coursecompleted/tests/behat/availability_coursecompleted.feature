@ewallah @availability @availability_coursecompleted
Feature: availability_coursecompleted
  In order to control student access to activities
  As a teacher
  I need to set course completion conditions which prevent student access

  Background:
    Given the following "courses" exist:
      | fullname | shortname | format | enablecompletion | numsections |
      | Course 1 | C1        | topics | 1                | 4           |
    And the following "activities" exist:
      | activity   | name   | intro                    | course | idnumber    | section | visible |
      | page       | Page A | page description         | C1     | page1       | 1       | 1       |
      | page       | Page B | page description         | C1     | page2       | 1       | 1       |
      | page       | Page C | page description         | C1     | page3       | 1       | 1       |
      | page       | Page D | page description         | C1     | page4       | 1       | 1       |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | First    | student1@example.com |
      | teacher1 | Teacher   | First    | teacher1@example.com |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |

  @javascript
  Scenario: Complete a course
    Given I am on the "C1" "Course" page logged in as "teacher1"
    And I navigate to "Course completion" in current page administration
    And I expand all fieldsets
    And I set the field "Teacher" to "1"
    And I click on "Save changes" "button"

    # Configure Page A for users who did not completed this course.
    When I am on the "page1" "page activity editing" page
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    And I click on "Course completed" "button" in the "Add restriction..." "dialogue"
    Then I should see "Please set" in the "region-main" "region"
    And I set the field "Course completed" to "No"
    And I click on ".availability-item .availability-eye img" "css_element"
    And I click on "Save and return to course" "button"

    # Configure page B for users who did not completed the course.
    When I am on the "page2" "page activity editing" page
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    And I click on "Course completed" "button"
    Then I should see "Please set" in the "region-main" "region"
    And I set the field "Course completed" to "No"
    Then I should not see "Please set" in the "region-main" "region"
    And I click on "Save and return to course" "button"

    # Configure page C for users who completed the course.
    When I am on the "page3" "page activity editing" page
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    And I click on "Course completed" "button"
    And I set the field "Course completed" to "Yes"
    And I click on "Save and return to course" "button"

    # Configure page D for users who completed the course hidden.
    When I am on the "page4" "page activity editing" page
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    And I click on "Course completed" "button"
    And I set the field "Course completed" to "Yes"
    And I click on ".availability-item .availability-eye img" "css_element"
    And I click on "Save and return to course" "button"
    And I log out

    # Log in as student.
    When I am on the "C1" "Course" page logged in as "student1"
    Then I should see "Page A" in the "region-main" "region"
    And I should see "Page B" in the "region-main" "region"
    And I should see "Page C" in the "region-main" "region"
    And I should not see "Page D" in the "region-main" "region"
    And I log out

    When I am on the "C1" "Course" page logged in as "teacher1"
    And I navigate to "Reports > Course completion" in current page administration
    Then I should see "Student First"
    And "img[title=\"Completed\"]" "css_element" should not exist
    And I follow "Click to mark user complete"
    # Completion cron won't mark the whole course completed unless the
    # individual criteria was marked completed more than a second ago. So
    # run it twice, first to mark the criteria and second for the course.
    And I wait "1" seconds
    And I run the scheduled task "core\task\completion_regular_task"
    And I wait "1" seconds
    And I run the scheduled task "core\task\completion_regular_task"
    And I run all adhoc tasks
    And I am on "Course 1" course homepage
    And I navigate to "Reports > Course completion" in current page administration
    Then I should see "Student First"
    And "img[title=\"Completed\"]" "css_element" should exist
    And I log out

    When I am on the "C1" "Course" page logged in as "student1"
    Then I should not see "Page A" in the "region-main" "region"
    And I should see "Page B" in the "region-main" "region"
    And I should see "Page C" in the "region-main" "region"
    And I should see "Page D" in the "region-main" "region"
