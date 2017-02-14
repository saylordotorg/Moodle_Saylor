# Autoenrol cohort authentication plugin for moodle 3.x

This authentication plugin automatically enrolls users into cohorts.

Cohort name depends on user profile field.

Cohorts are created in CONTEXT_SYSTEM.

## Installation

 * Download the archive and extract the files, or clone the repository from GitHub
 * Copy the 'mcae' folder into your_moodle/auth
 * Visit Site administration - Notifications page and follow the instructions

If you use an Email based self registration or similar plugin and users enrolls into cohort after **second login** copy/paste this code into moodle/themes/your_theme/layout/general.php (or default.php)

> **NOTE:** Enable and configure mcae plugin first!


    <?php

    global $SESSION, $USER;

    if ($USER->id != 0) { // Only for autenticated users
        $mcae = get_auth_plugin('mcae'); //Get mcae plugin

        if (isset($SESSION->mcautoenrolled)) {
            if (!$SESSION->mcautoenrolled) {
                $mcae->user_authenticated_hook($USER,$USER->username,""); //Autoenrol if mcautoenrolled FALSE
            }
        } else {
            $mcae->user_authenticated_hook($USER,$USER->username,""); //Autoenrol if mcautoenrolled NOT SET
        }
    }

    ?>

## Upgrade

 * Replace the your_moodle/auth/mcae folder with the new version
 * Visit Site administration - Notifications page and follow the instructions
 * !!! If you update to version 2.9 you must rewrite templates! See configuration section.
 
## Configuration

**Template for cohort name**

1 template per line.

In the template you may use any characters (except '{' and '}') and profile field values. To insert a profile field value, use `{{ field_name }}` tag.

An email fields have 4 variants:
 * `{{ email.full }}` - full email
 * `{{ email.username }}` - only username
 * `{{ email.domain }}` - only domain
 * `{{ email.rootdomain }}` - root domain

## By default moodle provides this fields

{{ id }}, {{ auth }}, {{ confirmed }}, {{ policyagreed }}, {{ deleted }}, {{ suspended }}, {{ mnethostid }}, {{ username }}, {{ idnumber }}, {{ firstname }}, {{ lastname }},
{{ email.full }}, {{ email.username }}, {{ email.domain }}, {{ email.rootdomain }}, {{ emailstop }},
{{ icq }}, {{ skype }}, {{ yahoo }}, {{ aim }}, {{ msn }}, {{ phone1 }}, {{ phone2 }},
{{ institution }}, {{ department }}, {{ address }}, {{ city }}, {{ country }}, {{ lang }},
{{ calendartype }}, {{ theme }}, {{ timezone }}, {{ firstaccess }}, {{ lastaccess }}, {{ lastlogin }}, {{ currentlogin }}, {{ lastip }},
{{ secret }}, {{ picture }}, {{ url }}, {{ descriptionformat }}, {{ mailformat }}, {{ maildigest }}, {{ maildisplay }}, {{ autosubscribe }}, {{ trackforums }},
{{ timecreated }}, {{ timemodified }}, {{ trustbitmask }}, {{ imagealt }}, {{ lastnamephonetic }}, {{ firstnamephonetic }}, {{ middlename }}, {{ alternatename }},
{{ lastcourseaccess }}, {{ currentcourseaccess }}, {{ groupmember }}

Additional tags become available if you have some custom profile fields.
For example if you create custom profile fields
 * `checkboxtest` - type Checkbox
 * `datetimetest` - type Date/Time
 * `droptest` - type Dropdown menu
 * `textinputtext` - type Text input
 * and `textareatest` - type Text area

You are able to use these tags:
{{ profile.checkboxtest }}, {{ profile.datetimetest }}, {{ profile.droptest }}, {{ profile.textinputtext }}, {{ profile_field_checkboxtest }}, 
{{ profile_field_datetimetest }}, {{ profile_field_droptest }}, {{ profile_field_textareatest.text }}, {{ profile_field_textareatest.format }}, 
{{ profile_field_textinputtext }}

> **Note:** Profile field templates are case sensitive. `{{ username }}` and `{{ UserName }}` are two different fields!

**Split arguments:**
Synopsis: %split(fieldname|delimiter)

Returns multiple cohorts, each of which is formed by splitting field on boundaries formed by the delimiter.

Arguments:
 * fieldname - Profile field name. The same as tag, but without '{{' and '}}'
 * delimiter - The boundary string. 1 - 5 characters.

> **Example:**

> User John fills the custom profile field "Known languages" with the value "English, Spanish, Chinese"

> Main template contains string "Language - %split(knownlanguage|, )"

> John will be enrolled in 3 cohorts: Language - English, Language - Spanish and Language - Chinese


**Replace empty field**

If profile field is empty then it's replaced with this value.

**Replacement array**

You can change the cohort name after it's generation.

1 replacement per line, format - old value|new value

    very long cohort name|shortname

> **Note:** The name must not be longer than 100 characters or it will be truncated

**Unenroll**

Unenroll users from cohorts after profile change.

To use an unenroll feature:

 * Go to Plugins - Authentication - Autoenrol cohort and enable unenrol function
 * Go to yourmoodle/auth/mcae/convert.php and convert cohorts you want to "auth_mcae".

Convert only cohorts that are created by the "auth_mcae" module!

At yourmoodle/auth/mcae/convert.php page you may view, delete or convert cohorts into "manual" or "auth_mcae" mode.

**Ignore users**

List of users to ignore. Comma separated usernames.

    admin,test,manager,teacher1,teacher2

## Usage example

You have a custom profile fields "status" (student, teacher or admin) and "course". 

You wnat to enrol many users into cohorts like "course - status" than enrol cohorts into courses.

At configuration page set:

Main template to `{{ profile_field_course }} - {{ profile_field_status }}`

Empty field text to ` none `

**Result:**

 * When 1st course student logs in, he's enrolled in a cohort named "1 - student"
 * When 1st course teacher logs in, he's enrolled in a cohort named "1 - teacher"
 * When admin logins, he's enrolled in a cohort named "none - admin" (Course not set, status - admin)

To rename "none - admin" cohort to "Administration" you must set a replacement array field at the configuration page
In our case: none - admin|Administrator

**Result:**

When admin logins, he's enrolled in a cohort named "Administrator"
