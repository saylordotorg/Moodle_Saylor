# moodle-atto_linkadv

This is an atto plugin for Moodle which is a drop-in replacement for the default atto_link plugin.

It adds an extra couple of fields to the link dialog: id and class. These allow the user to create bookmarks or add styles to their links (e.g. btn btn-primary for Bootstrap-based themes).

Note: Not all areas of Moodle allow you to add HTML with the id field (e.g. User profile description).
But it should be available in most places including label and page. You should refer to the individual plugins to discover
if ids are being stripped.

## Installation
1.  Drop code into /lib/editor/atto/plugins
2.  Go to Site administration -> Notifications to install

## Credits

Extended atto_links, and borrowed a little from atto_media for the tabs.
