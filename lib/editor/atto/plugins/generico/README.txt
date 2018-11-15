Generico for Moodle Atto

This is a companion for the Moodle Generico filter. It will display a button and a form for each Generico filter template you have declared.
It will not work without the Generico Filter. You *must* install that first or at the same time. The current version depends on a version of the Generico filter that only runs on Moodle 2.9 ad greater.
If your version of Moodle is older than that, please visit https://moodle.org/plugins/filter_generico to get a version compatible with your version of Moodle.

1. Download
============
Download the plugin from: https://github.com/justinhunt/moodle-atto_generico


2 Unzip / Rename / Upload
============
Expand (unzip) the zip file. 

Rename the main folder to "generico." It should contain all the files like "version.php." 

Upload the "generico" folder into the folder

[PATH TO MOODLE]/lib/editor/atto/plugins 


3. Get Moodle to Install It 
============
Visit Settings > Site Administration > Notifications, and let Moodle guide you through the install.


4. Configure it
============
Go to Site Administration > Plugins > Text Editors > Atto Toolbar Settings  

Now add Generico to the menu structure near the bottom of the page

  e.g style1 = title, bold, italic, generico

  (where widget is the name of your atto plugin)

Thats all. The Generico icon, a big grey G. should now appear on the Atto HTML editor toolbar. When you click it, Generico will give you a list of all the templates. Click the one you want and Generico will build a nice form for you to help you insert it.