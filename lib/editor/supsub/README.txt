Superscript/subscript editor

This is a very cut-down configuration of TinyMCE that just lets users
edit one line of input with superscripts and subscripts, for use in Moodle.

This editor was created by Tim Hunt of the Open University
http://www.open.ac.uk/.

This should be compatible with Moodle 2.4+. Older versions are available if you
need to support older versions of Moodle.

To install using git, type this commands in the root of your Moodle install
    git clone git://github.com/moodleou/moodle-editor_supsub.git lib/editor/supsub
    echo '/lib/editor/supsub' >> .git/info/exclude

Alternatively, download the zip from
    https://github.com/moodleou/moodle-editor_supsub/zipball/master
unzip it into the lib/editor folder, and then rename the extracted folder to supsub.
