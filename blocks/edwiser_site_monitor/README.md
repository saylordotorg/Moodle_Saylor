Edwiser Site Monitor plugin for Moodle
==============================================

# Table of Contents

- [Description](#description)
- [Features](#features)
- [Plugin Version](#plugin-version)
- [Required version of Moodle](#required-version-of-moodle)
- [Free Software](#free-software)
- [Support](#support)
- [Installation](#installation)
- [Uninstallation](#uninstallation)
- [Files Information](#files-information)
- [Languages](#languages)
- [Author](#author)
- [Provided by](#provided-by)

# Description

Edwiser Site Monitor plugin lets you track all the performance parameters related to your Moodle site. And the plugin automatically sends out email notifications to Moodle Site Administrator whenever the Moodle site has any performance issues. 

It also simplifies the process to update all the installed Moodle plugins, just few clicks away within the block.

[(Back to top)](#table-of-contents)

# Features

This is a dashboard block which gets automatically added as part of the dashboard once the plugin is installed.

Edwiser Site Monitor plugin has following tabs as part of it,

1) Live Monitor -
This tab tracks and lists all the Performance parameters like CPU Status Monitoring, Memory Usage etc that are important for a Moodle site.

2) Last 24 hour Usage -
Under this tab, the plugin displays the performance parameters like CPU Status Monitoring, Memory Usage related to the Moodle site for past 24 hours.

The plugin also sends out email notifications whenever the Moodle site performance parameters goes past the set threshold limits.

You can set the threshold limits by following these steps,

* Once the Edwiser Site Monitor block is added to the dashboard you could enable "Editing On"

* Click on the gear icon and click on "Configure Edwiser Site Monitor block",

* Under Configure Edwiser Site Monitor you will find a "Threshold Limit" section,
Out here you can set the threshold limits for CPU, Memory & Storage.

* Once you have set the thresholds the plugin will send out notifications whenever those performance parameters go past it.

3) Plugins Overview - 
Under this tab you will be able to view all the plugins that have an update. Along with an easier way to update the plugins that are installed on your Moodle site.
   
4) Recommended Plugins -
 This tab lists all plugins that'll help you enhance your Moodle experience.

5) Contact Us -
This tab contains a contact form using which users of Edwiser Site Monitor can directly contact us just in case they have any issue or need any assistance related to the product.

6) Notification -
Admins will receive notification containing Tips & Tricks to improve their Moodle LMS.

[(Back to top)](#table-of-contents)

# Plugin Version

v1.0.1 - Resolved few bugs.

v1.0.0 - Plugin Released. 

[(Back to top)](#table-of-contents)

# Required version of Moodle

This version works with Moodle 3.4+ version 2017111300.00 (Build: 20171113) and above until the next release.

Please ensure that your hardware and software complies with 'Requirements' in 'Installing Moodle' on
'docs.moodle.org/36/en/Installing_Moodle'.

[(Back to top)](#table-of-contents)

# Free Software

The Edwiser Site Monitor is 'free' software under the terms of the GNU GPLv3 License, please see 'LICENSE.md'.

The primary source is on https://github.com/WisdmLabs/moodle-block_edwiser_site_monitor

You have all the rights granted to you by the GPLv3 license.  If you are unsure about anything, then the
FAQ - http://www.gnu.org/licenses/gpl-faq.html - is a good place to look.

If you reuse any of the code then we kindly ask that you make reference to the our Edwiser Site Monitor block plugin.

[(Back to top)](#table-of-contents)

# Support

For all support queries related to Edwiser Site Monitor plugin you could email us at edwiser@wisdmlabs.com
Apart from that you could raise your support queries in this forum too - https://forums.edwiser.org/category/39/edwiser-site-monitor
 
And if you wish to see any new features as part of the product then you could share your feature requests here 
forum https://forums.edwiser.org/category/43/request-a-feature for support. 
Together we could make this solution better for your Moodle.

[(Back to top)](#table-of-contents)

# Installation

1. Ensure you have the version of Moodle as stated above in 'Required version of Moodle'.  This is essential as the
   block relies on underlying core code that is out of my control.
2. Put Moodle in 'Maintenance Mode' (docs.moodle.org/en/admin/setting/maintenancemode) so that there are no
   users using it bar you as the administrator - if you have not already done so.
3. Copy 'moodle-block_edwiser_site_monitor/' to '/blocks/' if you have not already done so.
4. Go back in as an administrator and follow standard the 'plugin' update notification.  If needed, go to 'Site administration' -> 'Notifications' if this does not happen.
5. Put Moodle out of Maintenance Mode.
6. You may need to check that the permissions within the 'moodle-block_edwiser_site_monitor/' folder are 755 for folders and 644 for files.

[(Back to top)](#table-of-contents)

# Uninstallation

1. Put Moodle in 'Maintenance Mode' so that there are no users using it bar you as the administrator.
2. In '/blocks/' remove the folder 'moodle-block_edwiser_site_monitor/'.
3. Put Moodle out of Maintenance Mode.

[(Back to top)](#table-of-contents)

# Files Information

# Languages

The moodle-block_edwiser_site_monitor/lang folder contains the language files for the block.

Of course you can have other folders as well as English etc. if you want to
provide multiple languages.

[(Back to top)](#table-of-contents)

# Author

Wisdmlabs

[(Back to top)](#table-of-contents)

# Provided by

[![alt text](https://github.com/WisdmLabs/moodle-block_edwiser_site_monitor/blob/master/pix/edwiser-logo.png)](https://edwiser.org)

-----------------------------

