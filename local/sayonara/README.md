# Sayonara Plugin

## Overview
This plugin allows students to delete their own accounts from a link in their profile.

When a student is logged into your Moodle site, they can navigate to their profile and click "Delete my account" under the Miscellaneous section. After authenticating, the student must confirm that they want to delete their account.

This plugin is a fork of the [Goodbye plugin](https://github.com/bmbrands/moodle-local_goodbye/) by Bas Bands.

## Compatability

This plugin has been tested and is working on Moodle 3.2+.

---

## Plugin Installation

Install the plugin following the [directions](https://docs.moodle.org/32/en/Installing_plugins) from Moodle. The source can be obtained using two methods.

#### Git

If you have git installed, simply visit the Moodle /local directory and clone this repo:

    git clone https://github.com/saylordotorg/moodle-local_sayonara.git sayonara

#### Download the zip

1. Visit [https://github.com/saylordotorg/moodle-local_sayonara](https://github.com/saylordotorg/moodle-local_sayonara) and download the zip. 
2. Extract the zip file's contents and name it 'sayonara'.
3. Place the folder in your /local folder, inside your Moodle directory.

## Plugin Setup

The plugin needs to be enabled in the administration settings prior to use. If not enabled, the "Delete my account" link will not show for students. As a site administrator, navigate to Site Administration->Plugins->Local plugins->Sayonara. Check the "Enabled" checkbox and save to enable the plugin.

The messages shown to students when deleting their account and in the confirmation modal can also be customized in the plugin settings.
