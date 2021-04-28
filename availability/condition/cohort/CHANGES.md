moodle-availability_cohort
==========================

Changes
-------

### v3.10-r2

* 2021-02-05 - Move Moodle Plugin CI from Travis CI to Github actions

### v3.10-r1

* 2021-01-09 - Fix Behat test after upstream change in cohort management (see MDL-67278 for details)
* 2021-01-09 - PHPUnit: Remove deprecated assertContains() uses on strings for Moodle 3.10 (see MDL-67673 for details)
* 2021-01-09 - Fix PHPUnit function declaration for Moodle 3.10.
* 2021-01-09 - Prepare compatibility for Moodle 3.10.
* 2021-01-06 - Change in Moodle release support:
               For the time being, this plugin is maintained for the most recent LTS release of Moodle as well as the most recent major release of Moodle.
               Bugfixes are backported to the LTS release. However, new features and improvements are not necessarily backported to the LTS release.
* 2021-01-06 - Improvement: Declare which major stable version of Moodle this plugin supports (see MDL-59562 for details).

### v3.9-r1

* 2020-07-16 - Prepare compatibility for Moodle 3.9.

### v3.8-r1

* 2020-02-12 - Prepare compatibility for Moodle 3.8.

### v3.7-r2

* 2019-06-26 - Fixed bug that only 25 cohorts will be displayed.

### v3.7-r1

* 2019-06-14 - Prepare compatibility for Moodle 3.7.

### v3.6-r1

* 2019-01-10 - Replaced deprecated Behat test step.
* 2019-01-09 - Check compatibility for Moodle 3.6, no functionality change.
* 2018-12-05 - Changed travis.yml due to upstream changes.

### v3.5-r3

* 2018-07-26 - Improved database query by using another function.

### v3.5-r2

* 2018-07-20 - Fixing PHPDoc errors.

### v3.5-r1

* 2018-06-27 - Initial version.
