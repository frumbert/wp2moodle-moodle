<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Strings for component 'auth_none', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package   auth_wp2moodle
 * @copyright 2012 onwards Tim St.Clair  {@link http://timstclair.me}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['auth_wp2moodle_secretkey'] = 'Encryption key';
$string['auth_wp2moodle_secretkey_desc'] = 'Must match Wordpress plugin setting';

$string['auth_wp2moodledescription'] = 'Uses Wordpress user details to create user & log onto Moodle';
$string['pluginname'] = 'Wordpress 2 Moodle (SSO)';

$string['auth_wp2moodle_timeout'] = 'Link timeout';
$string['auth_wp2moodle_timeout_desc'] = 'Minutes before incoming link is considered invalid (allow for reading time on Wordpress page)';

$string['auth_wp2moodle_logoffurl'] = 'Logoff Url';
$string['auth_wp2moodle_logoffurl_desc'] = 'Url to redirect to if the user presses Logoff';

$string['auth_wp2moodle_autoopen_desc'] = 'Automatically open the course after successful auth (uses first match in cohort or group)';
$string['auth_wp2moodle_autoopen'] = 'Auto open course?';

$string['auth_wp2moodle_updateuser'] = 'Update user profile fields using Wordpress values?';
$string['auth_wp2moodle_updateuser_desc'] = 'If set, user profile fields such as first and last name will be overwritten each time the SSO occurs. Turn this off if you want to let the user manage their profile fields independantly.';

$string['auth_wp2moodle_redirectnoenrol'] = 'Only redirect user to course?';
$string['auth_wp2moodle_redirectnoenrol_desc'] = 'If set, the user is being redirected to the course. Otherwise the user is enrolled into the course, if that has not been done already.';

