<?php

/**
 * @author Tim St.Clair
 * @author Mike Uding <mike@sebsoft.nl> - Only changes from 2015-01.
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package moodle / wordpress single sign on (wp2moodle)
 *
 * source https://github.com/frumbert/wp2moodle-moodle
 * Authentication Plugin: Wordpress 2 Moodle Single Sign On
 *
 * 2012-05-28  File created.
 * 2015-01-06   Added support for courses to be added via the login hook
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once($CFG->libdir.'/authlib.php');

/**
 * Plugin for no authentication.
 */
class auth_plugin_wp2moodle extends auth_plugin_base {

    /**
     * Constructor.
     */
    function auth_plugin_wp2moodle() {
        $this->authtype = 'wp2moodle';
        $this->config = get_config('auth/wp2moodle');
    }

    /**
     * Returns true if the username and password work or don't exist and false
     * if the user exists and the password is wrong.
     *
     * @param string $username The username
     * @param string $password The password
     * @return bool Authentication success or failure.
     */
    function user_login ($username, $password = null) {
        global $CFG, $DB;
        if ($password == null || $password == '') { return false; }
        if ($user = $DB->get_record('user', array('username'=>$username, 'password'=>$password, 'mnethostid'=>$CFG->mnet_localhost_id))) {
                return true;
            }
        return false;
    }

    function prevent_local_passwords() {
        return false;
    }

    /**
     * Returns true if this authentication plugin is 'internal'.
     *
     * @return bool
     */
    function is_internal() {
        return false;
    }

    /**
     * Returns true if this authentication plugin can change the user's
     * password.
     *
     * @return bool
     */
    function can_change_password() {
        return false;
    }

    /**
     * Returns the URL for changing the user's pw, or empty if the default can
     * be used.
     *
     * @return moodle_url
     */
    function change_password_url() {
        return null;
    }

    /**
     * Returns true if plugin allows resetting of internal password.
     *
     * @return bool
     */
    function can_reset_password() {
        return false;
    }

    function logoutpage_hook() {
        global $SESSION;
        set_moodle_cookie('nobody');
        require_logout();
        if (isset($this->config->logoffurl)) {
            if (ob_get_level() !== 0) ob_end_clean(); // in case we are inside a buffer
            // 301: move permanently
            // 302: found
            // 303: see other
            // 307: temporary redirect
            header("Location: " . $this->config->logoffurl, true, 301);
            exit; // flush header
        }
    }


    /**
     * Prints a form for configuring this authentication plugin.
     *
     * This function is called from admin/auth.php, and outputs a full page with
     * a form for configuring this plugin.
     *
     * @param array $page An object containing all the data for this page.
     */
    function config_form($config, $err, $user_fields) {
        include "config.html";
    }

    /**
     * Processes and stores configuration data for this authentication plugin.
     */
   function process_config($config) {
        // set to defaults if undefined
        if (!isset($config->sharedsecret)) {
            $config->sharedsecret = 'this is not a secure key, change it';
        }
        if (!isset($config->timeout)) {
            $config->timeout = '5';
        }
        if (!isset($config->logoffurl)) {
            $config->logoffurl = '';
        }
        if (!isset($config->autoopen)) {
            $config->autoopen = 'no';
        }
        if (!isset($config->updateuser)) {
            $config->updateuser = 'yes';
        }
        if (!isset($config->redirectnoenrol)) {
            $config->redirectnoenrol = 'no';
        }
        if (!isset($config->idprefix)) {
            $config->idprefix = '';
        }

        // save settings
        set_config('sharedsecret', $config->sharedsecret, 'auth/wp2moodle');
        set_config('logoffurl', $config->logoffurl, 'auth/wp2moodle');
        set_config('timeout', $config->timeout, 'auth/wp2moodle');
        set_config('autoopen', $config->autoopen, 'auth/wp2moodle');
        set_config('updateuser', $config->updateuser, 'auth/wp2moodle');
        set_config('redirectnoenrol', $config->redirectnoenrol, 'auth/wp2moodle');
        set_config('idprefix', $config->idprefix, 'auth/wp2moodle');

        return true;
    }

}


