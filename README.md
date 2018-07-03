wp2moodle--moodle
=================

This is the Moodle-end of a two-part plugin that allows users to authenticate within wordpress and open a Moodle site. To get the Wordpress-end plugin, check this git: https://github.com/frumbert/wp2moodle--wordpress-

Data is encrypted (using aes-256-cbc via openssl) at the Wordpress end and handed over a standard http GET request. Only the minimum required information is sent in order to create a Moodle user record. The user is automatically created if not present at the Moodle end, and then authenticated, and (optionally) enrolled in a Cohort, a Group, or both.

Requirements
------------
Moodle 2.7 or above (tested up to 3.3) on this fork; see branches for older versions.

Demo
-----
Go to my wordpress demo site and register yourself, then try the links on the course page. There's more documentation over there too.

http://wp2moodle.coursesuite.ninja/

How to install this plugin
---------------------
Note, this plugin must exist in a folder named "wp2moodle" - rename the zip file or folder before you upload it (preferably use something like `cd moodle/auth/ && git pull https://github.com/frumbert/wp2moodle-moodle wp2moodle` if you have git tools on your server).

1. Upload/extract this to your moodle/auth folder (should be called "~/auth/wp2moodle/", where ~ is your Moodle root)
2. Activate the plugin in the administration / authentication section
3. Click settings and enter the same shared secret that you enter for the wp2moodle settings in Wordpress. Generate that first, since it creates a secure key using openssl. Copy and paste it here.
4. The logoff url will perform a Moodle logout, then redirect to this url. You can get it to log off in Wordpress as well by hitting the wordpress-end logout page too; typically this is http://<your-wordpress-url/wp-login.php?action=logout
5. The link timeout is the number of minutes before the incoming link is thought to be invalid (to allow for variances in server times). This means links that were generated in the past can't be re-used, copied, bookmarked, etc.
5. Disable any other authentication methods as required. You can still use as many as you like. Manual enrolments must be enabled on courses that use group enrolment.

Usage:
------
You can not use this plugin directly; it is launched by wp2moodle from within Wordpress.

Problems?
---------
If you are having problems, try these first. If you raise an issue, let me know ALL the version numbers of your installations, what server platform they are running on, and any relevent error messages, otherwise I won't be able to help.

1. Confirm that you have the requirement met to run the plugin (e.g. openssl must be installed and show up in phpinfo)
2. Confirm that your course has the appropriate enrolment providers set up already (e.g. cohort based enrolment or manual enrolment)
3. Confirm that your shortcode is working in Wordpress
4. Confirm that you are using the text/string version of an identifier and NOT the numerical id of a course or cohort. the Id Number field is NOT set by default in moodle- you have to add something.
5. Look in your sites php error log to see if you can see if the plugin is silently throwing an error that you are not seeing on the page.
6. If you're trying one lookup type (e.g. group) then try switching to a different type (e.g. cohort). This may help me narrow down if it's a particular lookup type that is affected.

Licence:
--------
GPL3, as per Moodle.

