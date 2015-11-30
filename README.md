wp2moodle--moodle
=================

This is the Moodle-end of a two-part plugin that allows users to authenticate within wordpress and open a Moodle site. To get the Wordpress-end plugin, check this git: https://github.com/frumbert/wp2moodle--wordpress-

Data is encrypted at the Wordpress end and handed over a standard http GET request. Only the minimum required information is sent in order to create a Moodle user record. The user is automatically created if not present at the Moodle end, and then authenticated, and (optionally) enrolled in a Cohort, a Group, or both.

Demo
-----
Go to my wordpress demo site and register yourself, then try the links on the course page.

http://wp2moodle.coursesuite.ninja/

How to install this plugin
---------------------
Note, this plugin must exist in a folder named "wp2moodle" - rename the zip file or folder before you upload it (preferably use something like `cd moodle/auth/ && git pull https://github.com/frumbert/wp2moodle-moodle wp2moodle` if you have git tools on your server).

1. Upload/extract this to your moodle/auth folder (should be called "~/auth/wp2moodle/", where ~ is your Moodle root)
2. Activate the plugin in the administration / authentication section
3. Click settings and enter the same shared secret that you enter for the wp2moodle settings in Wordpress
4. The logoff url will perform a Moodle logout, then redirect to this url. You can get it to log off in Wordpress as well by hitting the wordpress-end logout page too; typically this is http://<your-wordpress-url/wp-login.php?action=logout
5. The link timeout is the number of minutes before the incoming link is thought to be invalid (to allow for variances in server times). This means links that were generated in the past can't be re-used, copied, bookmarked, etc.
5. Disable any other authentication methods as required. You can still use as many as you like. Manual enrolments must be enabled on courses that use group enrolment.

Usage:
------
You can not use this plugin directly; it is launched by wp2moodle from within Wordpress.

Licence:
--------
GPL2, as per Moodle.

