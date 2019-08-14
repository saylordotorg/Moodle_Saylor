# moodle-report_completionoverview
The course completion overview report is a simple reporting tool based on but extending the existing course completion report (report/completion) written by Aaron Barnes <aaronb@catalyst.net.nz>.
This plugin allows the Moodle Admin and those assigned as system level Managers to view course completion tracking information for all courses
from a centralised location (Administration > Site Administration > Reports) rather than having to access each course individually.
This plugin is for Moodle 3.3 and 3.4

The course completion overview report extends the existing completion report further by:

- Detailing courses where completion tracking isn't enabled
- Showing a simple table with course enrolment numbers, completions and non completions per course where completion tracking is enabled
- Showing a simple table with the date and time learners started modules tracking completion, the date and time they completed the final module tracking completion and their overall course grade
- Enabling access to course and learner completion information from a centralised point
# Requirements
This plugin requires Moodle 3.3+ or Moodle 3.4+
# Install instructions
Install this plugin in the normal way via Administration > Site Administration > Plugins > Install Plugins.
Drag and drop the zipped folder (or browse to it), select 'Site report (report)' as the plugin type and click 'Install plugin from the ZIP file'

# To access the report:
Moodle admins and Managers set at a system level can access the reporting tool by navigating to Site administration > reports > Course Completion Report 
