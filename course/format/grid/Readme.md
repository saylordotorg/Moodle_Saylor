Grid course format
============================
A topics based format that uses a grid of user selectable images to pop up a light box of the section.

Required version of Moodle
==========================
This version works with Moodle 3.11 version 2021051700.00 (Build: 20210517) and above within the 3.11 branch until the
next release.

Please ensure that your hardware and software complies with 'Requirements' in 'Installing Moodle' on
'docs.moodle.org/311/en/Installing_Moodle'

Free software
=============
The Grid format is 'free' software under the terms of the GNU GPLv3 License, please see 'COPYING.txt'.

The primary source for downloading this branch of the format is https://moodle.org/plugins/view.php?plugin=format_grid
with 'Select Moodle version:' set at 'Moodle 3.11'.

The secondary source is a tagged version with the v3.11 prefix on https://github.com/gjb2048/moodle-format_grid/tags

If you download from the development area - https://github.com/gjb2048/moodle-format_grid - consider that
the code is unstable and not for use in production environments.  This is because I develop the next version in stages
and use GitHub as a means of backup.  Therefore the code is not finished, subject to alteration and requires testing.

You have all the rights granted to you by the GPLv3 license.  If you are unsure about anything, then the
FAQ - http://www.gnu.org/licenses/gpl-faq.html - is a good place to look.

If you reuse any of the code then I kindly ask that you make reference to the format.

If you make improvements or bug fixes then I would appreciate if you would send them back to me by forking from
https://github.com/gjb2048/moodle-format_grid and doing a 'Pull Request' so that the rest of the
Moodle community benefits.

Support
=======
Please see Support.md.

Installation
============
1. Ensure you have the version of Moodle as stated above in 'Required version of Moodle'.  This is essential as the
   format relies on underlying core code that is out of my control.
2. Put Moodle in 'Maintenance Mode' (docs.moodle.org/en/admin/setting/maintenancemode) so that there are no
   users using it bar you as the administrator - if you have not already done so.
3. Copy 'grid' to '/course/format/' if you have not already done so.
4. Go back in as an administrator and follow standard the 'plugin' update notification.  If needed, go to
   'Site administration' -> 'Notifications' if this does not happen.
5. Put Moodle out of Maintenance Mode.
6. You may need to check that the permissions within the 'grid' folder are 755 for folders and 644 for files.

Uninstallation
==============
1. Put Moodle in 'Maintenance Mode' so that there are no users using it bar you as the administrator.
2. It is recommended but not essential to change all of the courses that use the format to another.  If this is
   not done Moodle will pick the last format in your list of formats to use but display in 'Edit settings' of the
   course the first format in the list.  You can then set the desired format.
3. In '/course/format/' remove the folder 'grid'.
4. In the database, remove the row with the 'plugin' of 'format_grid' and 'name' of 'version' in the 'config_plugins' table
   and drop the 'format_grid_icon' and 'format_grid_summary' tables.
5. Put Moodle out of Maintenance Mode.

Upgrade instructions
====================
1. Ensure you have the version of Moodle as stated above in 'Required version of Moodle'.  This is essential as the
   format relies on underlying core code that is out of my control.
2. Put Moodle in 'Maintenance Mode' so that there are no users using it bar you as the administrator.
3. In '/course/format/' move old 'grid' directory to a backup folder outside of Moodle.
4. Copy new 'grid' to '/course/format/'.
5. Go back in as an administrator and follow standard the 'plugin' update notification.  If needed, go to
   'Site administration' -> 'Notifications' if this does not happen.
6. If you have upgraded from Moodle 1.9 and were using the Grid format there, please follow 'Upgrading from M1.9' below
   and then return back here.
7. If automatic 'Purge all caches' appears not to work by lack of display etc. then perform a manual 'Purge all caches'
   under 'Home -> Site administration -> Development -> Purge all caches'.
8. Put Moodle out of Maintenance Mode.

Downgrading
===========
If for any reason you need to downgrade to a previous version of the format then the procedure will inform you how to
do so:

1.  Put Moodle in 'Maintenance Mode' so that there are no users using it bar you as the administrator.
2.  In '/course/format/' remove the folder 'grid' i.e. ALL it's contents - this is VITAL.
3.  Put in the replacement 'grid' folder into '/course/format/'.
4.  This step depends on if you are downgrading to a version prior to 15th July 2012, this should therefore only be for
    Moodle 2.3.x and below versions.  If you are, perform step 4.1 otherwise, perform step 4.2.
4.1 In the database, remove the row with the 'plugin' of 'format_grid' and 'name' of 'version' in the 'config_plugins' table
    and drop the 'format_grid_icon' and 'format_grid_summary' tables.  If automatic 'Purge all caches' appears not to work by
    lack of display etc. then perform a manual 'Purge all caches' under 'Home -> Site administration -> Development ->
    Purge all caches'.
4.2 In the database, change the row with the 'plugin' of 'format_grid' and 'name' of 'version' in the 'config_plugins' table
    to have the same 'value' as '$plugin->version' in the 'grid/version.php' file i.e. like '2013083000'.  Then perform a manual
    'Purge all caches' under 'Home -> Site administration -> Development -> Purge all caches'.
5.  Go back in as an administrator and follow standard the 'plugin' update notification.  If needed, go to
    'Site administration' -> 'Notifications' if this does not happen.
6.  Put Moodle out of Maintenance Mode.

Reporting issues
================
Please see Support.md.

Usage
=====

Viewing
-------
Click on a grid icon or use 'esc' to open the current selected icon which will then display the shade box containing the section
content.  Click on the 'X' or use 'esc' to close.

Use the 'left' / 'right' cursor keys to select the previous / next section when the shade box is and is not displayed.

Use the 'left' / 'right' arrows that appear when hovering over the middle of the border when the shade box is shown to navigate to
the previous / next section.

When the 'Course layout' course setting are set to 'Show all sections on one page' the shade box will operate.  When set to
'Show one section per page' the shade box will not show but instead the icons will act like links as they do with the
'Topics' format and take you to a single section page.

Editing
-------
Use the 'Change image' link underneath each icon to change the icon's image.

Edit the sections underneath the icons in the normal way.  Note: Some things like current section colour will not update until page
refresh.

The shade box is not shown in this mode.

Accessibility
-------------
If you wish for a user not to see the grid and shadebox and have the course presented like the Topics format, then add a custom
user checkbox field with a shortname of 'accessible' and on the user profile tick it, please see:

https://docs.moodle.org/en/User_profile_fields

and:

https://github.com/gjb2048/moodle-format_grid/issues/23

Making changes
==============

Changing the keyboard control code
----------------------------------
To change the 'gridkeys.js' code then you'll firstly need to read: http://docs.moodle.org/dev/YUI/Shifter
it is used to build the source in '/yui/src/gridkeys/js/gridkeys.js' and bring in the 'gallery-event-nav-keys' to build
the YUI module into 'yui/build/moodle-format_grid-gridkeys' and place a back port minified version in '/yui/gridkeys' for
use in Moodle 2.3 and 2.4 versions - so even if you have those versions you will need this Moodle 2.5 version to
make changes.  The compiled YUI module is then loaded in all versions (2.3, 2.4 and 2.5) in 'renderer.php' by the line:
$PAGE->requires->yui_module('moodle-format_grid-gridkeys', 'M.format_grid.gridkeys.init', null, null, true);
So even though the location is different for M2.3 / M2.4 than M2.5 it's the same - that's a M2.5+ thing.  There is no
rocket science to using / learning Shifter, I did so late on a Saturday night whilst half asleep - admittedly with Andrew's
on-line assistance.

Current selected colour
-----------------------
Edit 'styles.css', change the value in the '.course-content ul.gridicons li.currentselected' selector and perform a 'Purge all caches'
or override in your theme.

Current section
---------------
Edit 'styles.css', change the value in the '.course-content ul.gridicons li.current' selector and perform a 'Purge all caches' or
override in your theme.

File information
================

Languages
---------
The grid/lang folder contains the language files for the format.

Note that existing formats store their language strings in the main
moodle.php, which you can also do, but this separate file is recommended
for contributed formats.

Of course you can have other folders as well as English etc. if you want to
provide multiple languages.

Styles
------
The file grid/styles.css contains the CSS styles for the format which can
be overridden by the theme.

Backup
------
The files:

grid/backup/moodle2/backup_format_grid_plugin.class.php
grid/backup/moodle2/restore_format_grid_plugin.class.php

are responsible for backup and restore.

Backup and restore run automatically when backing up the course.
You can't back up the course format data independently.

Roadmap
=============
1. Maintenance of issues.
2. Ongoing structured walk through and refactoring.
3. Updates to support major releases when they happen.

Parked
------
1. Improved instructions including Moodle docs.
2. User definable grid row icon numbers - https://moodle.org/mod/forum/discuss.php?d=196716

Known issues
=============
All listed on:
1. https://tracker.moodle.org/browse/CONTRIB/component/11231
2. https://github.com/gjb2048/moodle-format_grid/issues

History
=============
Please see Changes.md

Author
------
G J Barnard - Moodle profile: moodle.org/user/profile.php?id=442195 - Web profile: about.me/gjbarnard