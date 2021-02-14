# DiscourseSSO Plugin

## Overview
This plugin enables your Moodle installation to act as a SSO provider for Discourse. This allows single sign on between your Discourse and Moodle sites. 

When a student is logged into your Moodle site and goes to your Discourse site, they can click "Log In" and will be automatically logged into Discourse; On first log in an account will be created in Discourse.

When a student is not currently logged into your Moodle site, they will be redirected to log into Moodle when they click "Log In" in Discourse.

## Compatability

This plugin has been tested and is working on Moodle 3.2+.

---

## Plugin Installation

Install the plugin following the [directions](https://docs.moodle.org/32/en/Installing_plugins) from Moodle. The source can be obtained using two methods.

#### Git

If you have git installed, simply visit the Moodle /local directory and clone this repo:

    git clone https://github.com/saylordotorg/moodle-local_discoursesso.git discoursesso

#### Download the zip

1. Visit [https://github.com/saylordotorg/moodle-local_discoursesso](https://github.com/saylordotorg/moodle-local_discoursesso) and download the zip. 
2. Extract the zip file's contents and name it 'discoursesso'.
3. Place the folder in your /local folder, inside your Moodle directory.

## Plugin Setup

The plugin needs to be set up on both the Moodle side and the Discourse side. The two need to share a unique sso secret, so create a random string that is 10 characters or longer. This string will be entered into both sites.

#### Discourse Setup

1. Navigate to the Discourse admin dashboard and go to Settings->Login.
2. Under the 'sso url' setting enter in "{your-moodle-url}/local/discoursesso/sso.php" substituting the base url of your Moodle installation for {your-moodle-url}.
3. Under the 'sso secret' setting enter in the previously generated secret.
4. Check 'enable sso'.
Optional:
    The following settings are optional and may be checked:
        1. sso overrides bio
        2. sso overrides email
        3. sso overrides username
        4. sso overrides name
        5. sso overrides avatar (Note: the DiscourseSSO plugin will not supply an avatar if the student does not have an avatar set in Moodle. This is to keep the default Discourse avatars if the avatar is not set - otherwise these students will have the generic Moodle avatar which does not look as good.)

#### Moodle Setup

1. Navigate to the DiscourseSSO settings page from the administration block.
2. Enter the Discourse API key into the API key field. This can be found in your Discourse server's settings under Admin->API.
3. Enter the previously generated secret into the "Shared SSO Secret Key" field. Make sure it matches the secret entered into Discourse!
4. Enter your Discourse site URL into the "Discourse URL field"

##### Cohorts

Select Moodle cohorts can now be synced as Discourse groups. On the "Assign cohorts" page under the DiscourseSSO settings, search through your Moodle cohorts and add any that you would like to sync. This will create a new group in Discourse. Any users who are a member of that cohort will be added to the Discourse group when they log in.