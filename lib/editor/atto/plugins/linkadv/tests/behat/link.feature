@editor @editor_atto @atto @atto_linkadv @_file_upload
Feature: Add links to Atto
  To write rich text - I need to add links.
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

    @javascript
    Scenario: Insert links
        Given I log in as "admin"
        And I follow "Manage private files..."
        And I upload "lib/editor/atto/tests/fixtures/moodle-logo.png" file to "Files" filemanager
        And I click on "Save changes" "button"
        When I open my profile in edit mode
        And I set the field "Description" to "Super cool"
        And I select the text in the "Description" Atto editor
        And I click on "LinkAdv" "button"
        And I click on "Browse repositories..." "button"
        And I click on "Private files" "link" in the ".fp-repo-area" "css_element"
        And I click on "moodle-logo.png" "link"
        And I click on "Select this file" "button"
        And I click on "Update profile" "button"
        And I follow "Preferences" in the user menu
        And I follow "Editor preferences"
        And I set the field "Text editor" to "Plain text area"
        And I press "Save changes"
        And I click on "Edit profile" "link" in the "region-main" "region"
        Then I should see "Super cool</a>"
