Gapfill question type V1.972 for Moodle 

A very easy to use Cloze question type that supports drag/drop answers that work on mobile devices and the mobile app. 
Teachers can define the question with square braces to define the missing words. For example The [cat] sat on the [mat]. 
Alternative delimiting characters can be defined during question edit for example The #cat# sat on the #mat# can be valid.

If the dropdown or dragdrop options are set in the question edit form, it can display a shuffled selection  of correct and 
wrong answers. These can then can be selected via dropddropdown lists or javascript powered drag and drop functionality.

This question type was written by Marcus Green

This question type was created and tested under Moodle 3.1,3.2,3.3,3.4,3.5


Place the files in a directory 

moodle\question\type\gapfill

Where moodle is webroot for your install.

Go to Site Administration/Notifications
Version 1.972 Implement Privacy/GDPR API, minor bug fixes
Version 1.971 Bug fix for ios dragdrop issue
Version 1.97 letter hint from correct response added to gaps in interactive mode when incorrect answers added
Version 1.96 per gap feedback entered via a popup form by clicking on the gap when editing
Version 1.95 add optionsaftertext  option so draggables appear after text
Version 1.94 fix for select dropdowns on webkit/android.Tap to select prompt in mobile app 
Version 1.93 support for mobile app. onfocus CSS on inputs, hover on draggable, bugfix in draggables (#25 on github) 
Version 1.92 CSS to improve dropdowns on chrome mobile, discard gaps in wrong answers:improves display in feedback
Version 1.91 [.+] will make any text a valid answer and if left empty will not show the .+ as aftergap feedback
Version 1.9 added link in the admin interface to make it easy to import_example questions to a course
Version 1.8 added casesensitive option in settings, plugin version information in xml export, | now works with regex off
Version 1.7 updated jQuery, jQuery ui and touchpunch and the way they are called to work with Moodle 2.9
Version 1.6 added display of the correct answer next to gaps with wrong answers entered
Version 1.5 added support for gaps that are marked correct if left empty using !!, and fixedgapsize
Version 1.4 added support for touch devices such as Apple iOS phones and tablets (iPhone/iPad) and Android devices 
Version 1.3 toggle regex for plain string compare. Useful for maths, html and programming language questions
Version 1.2 will colour duplicate answers yellow when discard duplicates mode is used (see help)
Version 1.1 includes a count of correct answers and clears incorrect responses in interactive mode
