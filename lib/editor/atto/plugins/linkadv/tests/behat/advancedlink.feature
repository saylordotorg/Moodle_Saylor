@editor @editor_atto @atto @atto_linkadv @_file_upload
Feature: Add links to Atto with and ID and class
  To write rich text - I need to add links with ids and classes
    Background:
        Given I log in as "admin"
        And the atto_linkadv config value "toolbar" of "editor_atto" is set as admin to multiline
            """
            collapse = collapse
            style1 = title, bold, italic
            list = unorderedlist, orderedlist
            links = linkadv
            files = image, media, managefiles
            style2 = underline, strike, subscript, superscript
            align = align
            indent = indent
            insert = equation, charmap, table, clear
            undo = undo
            accessibility = accessibilitychecker, accessibilityhelper
            other = html
            """
        And I log out
        And the following "users" exist:
            | username | firstname | lastname | email |
            | teacher  | Teacher   | First    | teacher1@example.com |
            | student  | Student   | First    | student1@example.com |
        And the following "courses" exist:
            | fullname | shortname | category |
            | Test     | C1        | 0 |
        And the following "course enrolments" exist:
            | user    | course | role |
            | teacher | C1     | editingteacher |
            | student | C1     | student |

    @javascript
    Scenario: Insert links with ID and class
        Given I log in as "teacher"
        And I am on "Test" course homepage with editing mode on
        And I add a "label" to section "1"
        And I set the field "Label text" to "Super cool"
        And I select the text in the "Label text" Atto editor
        And I click on "LinkAdv" "button"
        And I set the field "Enter a URL" to "https://nolink.nolink"
        And I click on "Advanced" "link" in the ".atto_form" "css_element"
        And I set the field "Enter an ID" to "attolinkadvid"
        And I set the field "Enter a class" to "attolinkadvclass"
        And I click on "Create link" "button"
        And I press "Save and return to course"
        And I turn editing mode off
        Then I should see "Super cool" in the ".attolinkadvclass" "css_element"
        And I should see "Super cool" in the "#attolinkadvid" "css_element"
        And I log out
        And I log in as "student"
        And I am on "Test" course homepage
        Then I should see "Super cool" in the ".attolinkadvclass" "css_element"
        And I should see "Super cool" in the "#attolinkadvid" "css_element"
