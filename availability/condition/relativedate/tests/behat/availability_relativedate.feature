@eWallah @availability @availability_relativedate @javascript
Feature: availability_relativedate
  In order to control student access to activities
  As a teacher
  I need to set date conditions which prevent student access

  Background:
    Given the following "users" exist:
      | username | timezone |
      | teacher1 | 5        |
      | student1 | 5        |
      | student2 | 5        |
    And the following config values are set as admin:
      | enableavailability   | 1 |
    And the following "course" exists:
      | fullname          | Course 1             |
      | shortname         | C1                   |
      | category          | 0                    |
      | enablecompletion  | 1                    |
      | startdate         | ## -10 days 17:00 ## |
      | enddate           | ## +2 weeks 17:00 ## |
    And selfenrolment exists in course "C1" ending "## tomorrow 17:00 ##"
    And the following "course enrolments" exist:
      | user     | course | role           | timestart             |
      | teacher1 | C1     | editingteacher | ## yesterday 17:00 ## |
      | student1 | C1     | student        | ## yesterday 17:00 ## |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on

  Scenario: Restrict section0
    When I edit the section "0"
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    Then "Relative date" "button" should exist in the "Add restriction..." "dialogue"

  Scenario: Test relative conditions
    And I add a "Page" to section "1"
    And I set the following fields to these values:
      | Name         | Page 1: 2 hours after course start date |
      | Description  | Test |
      | Page content | Test |
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    And I click on "Relative date" "button" in the "Add restriction..." "dialogue"
    And I set the field "relativenumber" to "2"
    And I set the field "relativednw" to "1"
    And I set the field "relativestart" to "1"
    And I press "Save and return to course"

    And I add a "Page" to section "1"
    And I set the following fields to these values:
      | Name         | Page 2: 4 days before course end date |
      | Description  | Test |
      | Page content | Test |
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    And I click on "Relative date" "button" in the "Add restriction..." "dialogue"
    And I set the field "relativenumber" to "4"
    And I set the field "relativednw" to "2"
    And I set the field "relativestart" to "2"
    #And I click on ".availability-item .availability-eye img" "css_element"
    And I press "Save and return to course"

    And I add a "Page" to section "1"
    And I set the following fields to these values:
      | Name         | Page 3: 6 weeks after user enrolment date |
      | Description  | Test   |
      | Page content | Test   |
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    And I click on "Relative date" "button" in the "Add restriction..." "dialogue"
    And I set the field "relativenumber" to "6"
    And I set the field "relativednw" to "3"
    And I set the field "relativestart" to "3"
    #And I click on ".availability-item .availability-eye img" "css_element"
    And I press "Save and return to course"

    And I add a "Page" to section "1"
    And I set the following fields to these values:
      | Name         | Page 4: 7 months after enrolment method end date|
      | Description  | Test   |
      | Page content | Test   |
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    And I click on "Relative date" "button" in the "Add restriction..." "dialogue"
    And I set the field "relativenumber" to "7"
    And I set the field "relativednw" to "4"
    And I set the field "relativestart" to "4"
    And I press "Save and return to course"

    # 5 days before course start date.
    And I edit the section "2"
    And I expand all fieldsets
    Then I should see "None" in the "Restrict access" "fieldset"
    When I click on "Add restriction..." "button"
    And  I click on "Relative date" "button" in the "Add restriction..." "dialogue"
    And I set the field "relativenumber" to "5"
    And I set the field "relativednw" to "2"
    And I set the field "relativestart" to "1"
    And I press "Save changes"

    And I edit the section "2"
    And I expand all fieldsets
    Then I should see "5" in the "Restrict access" "fieldset"
    And I should see "days" in the "Restrict access" "fieldset"
    And I should see "after course start date" in the "Restrict access" "fieldset"
    #And I click on ".availability-item .availability-eye img" "css_element"
    And I press "Cancel"

    # 5 days after course end date.
    And I edit the section "3"
    And I expand all fieldsets
    Then I should see "None" in the "Restrict access" "fieldset"
    When I click on "Add restriction..." "button"
    And  I click on "Relative date" "button" in the "Add restriction..." "dialogue"
    And I set the field "relativenumber" to "5"
    And I set the field "relativednw" to "2"
    And I set the field "relativestart" to "2"
    And I press "Save changes"

    Then I should see "Page 1" in the "region-main" "region"
    And I should see "2 hours after course start date" in the "region-main" "region"
    And I should see "4 days before course end date" in the "region-main" "region"
    And I should see relativedate "##+2 weeks -4 days 17:00##"
    And I should see "6 weeks after user enrolment date" in the "region-main" "region"
    And I should see relativedate "##+6 weeks -1 days 17:00##"
    And I should see "7 months after enrolment method end date" in the "region-main" "region"
    And I should see "5 days after course start date" in the "region-main" "region"
    And I log out

    # Log back in as student 1.
    When I am on the "C1" "Course" page logged in as "student1"
    Then I should see "Page 1" in the "region-main" "region"
    And I should see "2 hours after course start date" in the "region-main" "region"
    And I should see "Page 2" in the "region-main" "region"
    And I should see relativedate "##+2 weeks -4 days 17:00##"
    But I should see "Page 3" in the "region-main" "region"
    And I should see relativedate "##+6 weeks -1 days 17:00##"
    And I should see "Page 4" in the "region-main" "region"
    And I should see "Topic 2" in the "region-main" "region"
    And I should see "Topic 3" in the "region-main" "region"
    #And I should see "Until 10 September 2021, 10:00 PM" in the "region-main" "region"
    And I log out

    # Log back in as student 2.
    When I am on the "C1" "Course" page logged in as "student2"
    And I press "Enrol me"
    Then I should see "Page 1" in the "region-main" "region"
    And I should see "2 hours after course start date" in the "region-main" "region"
    And I should see "Page 2" in the "region-main" "region"
    But I should see "Page 3" in the "region-main" "region"
    And I should see "Page 4" in the "region-main" "region"
    And I should see "Topic 2" in the "region-main" "region"
    And I should see "Topic 3" in the "region-main" "region"
    #And I should see "Until 10 September 2021, 10:00 PM" in the "region-main" "region"
    And I log out

    # Log back in as admin.
    When I am on the "C1" "Course" page logged in as "admin"
    Then I should see "2 hours after course start date" in the "region-main" "region"
    And I should see "4 days before course end date" in the "region-main" "region"
    And I should see "6 weeks after user enrolment date" in the "region-main" "region"
    And I should see "7 months after enrolment method end date" in the "region-main" "region"
    And I should see "5 days after course start date" in the "region-main" "region"
