

#Moodle question type addon: "JME" (Javascript Molecular Editor)

Original Moodle question type written by Dan Stowell
Upgraded and maintained by Jean-Michel Vedrine

###IMPORTANT:
Previous versions of the jme Moodle question type were using a java applet,
the JME Molecule Editor©.
JME Molecule Editor© is a freely-available molecule editor tool, but it is not
open-source.
Another problem with JME is that it is now very difficult to work with java applets
due to security alerts and rules in web browsers.
This new version uses a javascript applet : JSME.
JSME Molecule Editor by Pter Erl and Bruno Bienfait by Peter Ertl and Bruno Bienfait
is a free molecule editor written in JavaScript. JSME is a direct successor of the
JME Molecule Editor applet.
The JSME Molecule Editor is open source and is released under the terms of the BSD license.
The jme Moodle question type plugin is open-source under the GNU Public Licence
GPL), the same licence as Moodle.

If you downloaded this plugin from the Moodle plugin Directry or from a github repository,
all the JSME files necessary for it to work are NOT INCLUDED. So you must get a copy
of the JSME Molecule Editor to be able to use this plugin.
Go to http://peter-ertl.com/jsme/ and click on the download link to download the latest
release of the JSME Molecule Editor (JSME_2014-06-28 at the time this text is written).
Unzip the archive and copy the CONTENT of the jsme/ subfolder into question/type/jme/jsme/.
Warning : verify you get it right : you should have a question/type/jme/jsme/jsme.nocache.js
file and NOT question/type/jme/jsme/jsme/jsme.nocache.js !
You would not be the first one to make this mistake !



##INSTALLATION:
###Requires
This version works with Moodle 2.6, 2.7, 2.8. Other versions for older
Moodle versions are also availables separately.

###Installation Using Git

To install using git, type this command in the root of your Moodle install:

    git clone git://github.com/jmvedrine/moodle-qtype_jme.git question/type/jme
    echo '/question/type/jme' >> .git/info/exclude

###Installation From Downloaded zip file

Alternatively, download the zip from :
* The Moodle plugin directory https://moodle.org/plugins/
* https://github.com/jmvedrine/moodle-qtype_jme/archive/master.zip

unzip it into the question/type folder, and then rename the new folder to jme.
WARNING: if the folder's name is something like jme_master, the plugin will not work.

###Plugin Initialisation
Once you have installed the files on your server, as for any other Moodle plugin,
visit your Moodle Administration Notifications page and click on "Upgrade Moodle
database now" button, the JME question type plugin will be installed.

###IMPORTANT WARNING
Don't forget that this plugin will not work if all the files from the JSME Molecule Editor
aren't in the question/type/jme/jsme/ subfolder ! Go to http://peter-ertl.com/jsme/ to
get a copy of the JSME Molecule Editor and put it in the right place.

##USAGE:

The JME editor can be used to design molecular structures, so you
can ask questions such as "Please draw the structure of
2,3-dichloro-but-2-ene". In order to mark responses, they need to
be converted to a simple text format called SMILES (see
http://www.daylight.com/smiles/ for more information).

So, the student must design the molecule, using the JSME Javascript Applet.
The content of the student response is automatically saved when the
student change page in the quiz either by pressing on the "Next"
button, or using the quiz navigation panel. When quiz attempt is
submitted, this response is then marked in the same way as a
(case-sensitive) short-answer question.

You can use a similar process when designing the question. Using
the JME applet, design a molecule that is a possible (right or
wrong) answer and then press a button next to the answer boxes
to store the current design as a SMILES code.
