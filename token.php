<?php
/**
 * based on /login/token.php, this variant is specific to this plugin and expects encrypted username as its param
 * @package    moodle core
 * @copyright  2011 Dongsheng Cai <dongsheng@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);
define('NO_MOODLE_COOKIES', true);

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->libdir.'/moodlelib.php');


$PASSTHROUGH_KEY = get_config('auth/wp2moodle', 'sharedsecret');
if (!isset($PASSTHROUGH_KEY)) {
	echo "Sorry, this plugin has not yet been configured. Please contact the Moodle administrator for details.";
	die();
}


/**
 * Handler for decrypting incoming data (specially handled base-64) in which is encoded a string of key=value pairs
 */
function decrypt_string($base64, $key) {
	if (!$base64) { return ""; }
	$data = str_replace(array('-','_'),array('+','/'),$base64);
    $mod4 = strlen($data) % 4;
    if ($mod4) {
        $data .= substr('====', $mod4);
    }
    $crypttext = base64_decode($data);
    $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
    $decrypttext = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key.$key), $crypttext, MCRYPT_MODE_ECB, $iv);
	return trim($decrypttext);
}

/**
 * querystring helper, returns the value of a key in a string formatted in key=value&key=value&key=value pairs, e.g. saved querystrings
 */
function get_key_value($string, $key) {
    $list = explode( '&', $string);
    foreach ($list as $pair) {
    	$item = explode( '=', $pair);
		if (strtolower($key) == strtolower($item[0])) {
			return urldecode($item[1]); // not for use in $_GET etc, which is already decoded, however our encoder uses http_build_query() before encrypting
		}
    }
    return "";
}

// are web service enabled at all?
if (!$CFG->enablewebservices) {
    throw new moodle_exception('enablewsdescription', 'webservice');
}

// ok, lets start parsing for data
$rawdata = $_GET['data'];
if (!empty($_GET)) {
	$userdata = decrypt_string($rawdata, $PASSTHROUGH_KEY);
} else {
	echo "Ding dong the witch is dead";
	die();
}

// wp2moodle requirements
$PASSTHROUGH_KEY = get_config('auth/wp2moodle', 'sharedsecret');
if (!isset($PASSTHROUGH_KEY)) {
	echo "Sorry, this plugin has not yet been configured. Please contact the Moodle administrator for details.";
	die();
}

// lets find out the username and the service name
$username = trim(textlib::strtolower(get_key_value($userdata, "username")));
$servicename = trim(textlib::strtolower(get_key_value($userdata, "servicename")));

// build the user object and go through common logon scenarious until we get to creating the new token
$user = get_complete_user_data('username', $username);
if (!empty($user)) {

    //Non admin can not authenticate if maintenance mode
    $hassiteconfig = has_capability('moodle/site:config', get_context_instance(CONTEXT_SYSTEM), $user);
    if (!empty($CFG->maintenance_enabled) and !$hassiteconfig) {
        throw new moodle_exception('sitemaintenance', 'admin');
    }

    if (isguestuser($user)) {
        throw new moodle_exception('noguest');
    }
    if (empty($user->confirmed)) {
        throw new moodle_exception('usernotconfirmed', 'moodle', '', $user->username);
    }

    // check credential expiry
    $userauth = get_auth_plugin($user->auth);
    if (!empty($userauth->config->expiration) and $userauth->config->expiration == 1) {
        $days2expire = $userauth->password_expire($user->username);
        if (intval($days2expire) < 0 ) {
            throw new moodle_exception('passwordisexpired', 'webservice');
        }
    }

    // let enrol plugins deal with new enrolments if necessary
    enrol_check_plugins($user);

    // setup user session to check capability
    session_set_user($user);

    //check if the service exists and is enabled
    $service = $DB->get_record('external_services', array('component' => $servicename, 'enabled' => 1));
    if (empty($service)) {
        // will throw exception if no token found
        throw new moodle_exception('servicenotavailable', 'webservice');
    }

    //check if there is any required system capability
    //if ($service->requiredcapability and !has_capability($service->requiredcapability, get_context_instance(CONTEXT_SYSTEM), $user)) {
    //    throw new moodle_exception('missingrequiredcapability', 'webservice', '', $service->requiredcapability);
    //}

    //specific checks related to user restricted service
    if ($service->restrictedusers) {
        $authoriseduser = $DB->get_record('external_services_users',
            array('externalserviceid' => $service->id, 'userid' => $user->id));

        if (empty($authoriseduser)) {
            throw new moodle_exception('usernotallowed', 'webservice', '', $servicename);
        }

        if (!empty($authoriseduser->validuntil) and $authoriseduser->validuntil < time()) {
            throw new moodle_exception('invalidtimedtoken', 'webservice');
        }

        if (!empty($authoriseduser->iprestriction) and !address_in_subnet(getremoteaddr(), $authoriseduser->iprestriction)) {
            throw new moodle_exception('invalidiptoken', 'webservice');
        }
    }

    //Check if a token has already been created for this user and this service
    //Note: this could be an admin created or an user created token.
    //      It does not really matter we take the first one that is valid.
    $tokenssql = "SELECT t.id, t.sid, t.token, t.validuntil, t.iprestriction
              FROM {external_tokens} t
             WHERE t.userid = ? AND t.externalserviceid = ? AND t.tokentype = ?
          ORDER BY t.timecreated ASC";
    $tokens = $DB->get_records_sql($tokenssql, array($user->id, $service->id, EXTERNAL_TOKEN_PERMANENT));

    //A bit of sanity checks
    foreach ($tokens as $key=>$token) {

        /// Checks related to a specific token. (script execution continue)
        $unsettoken = false;
        //if sid is set then there must be a valid associated session no matter the token type
        if (!empty($token->sid)) {
            $session = session_get_instance();
            if (!$session->session_exists($token->sid)){
                //this token will never be valid anymore, delete it
                $DB->delete_records('external_tokens', array('sid'=>$token->sid));
                $unsettoken = true;
            }
        }

        //remove token if no valid anymore
        //Also delete this wrong token (similar logic to the web service servers
        //    /webservice/lib.php/webservice_server::authenticate_by_token())
        if (!empty($token->validuntil) and $token->validuntil < time()) {
            $DB->delete_records('external_tokens', array('token'=>$token->token, 'tokentype'=> EXTERNAL_TOKEN_PERMANENT));
            $unsettoken = true;
        }

        // remove token if its ip not in whitelist
        if (isset($token->iprestriction) and !address_in_subnet(getremoteaddr(), $token->iprestriction)) {
            $unsettoken = true;
        }

        if ($unsettoken) {
            unset($tokens[$key]);
        }
    }

    // if some valid tokens exist then use the most recent
    if (count($tokens) > 0) {
        $token = array_pop($tokens);
    } else {
        if ( ($servicename == MOODLE_OFFICIAL_MOBILE_SERVICE and has_capability('moodle/webservice:createmobiletoken', get_system_context()))
                or (!is_siteadmin($user))) {

            // if service doesn't exist, dml will throw exception
            $service_record = $DB->get_record('external_services', array('component'=>$servicename, 'enabled'=>1), '*', MUST_EXIST);

            // create a new token
            $token = new stdClass;
            $token->token = md5(uniqid(rand(), 1));
            $token->userid = $user->id;
            $token->tokentype = EXTERNAL_TOKEN_PERMANENT;
            $token->contextid = get_context_instance(CONTEXT_SYSTEM)->id;
            $token->creatorid = $user->id;
			$token->validuntil = 0;
            $token->timecreated = time();
            $token->externalserviceid = $service_record->id;
            $tokenid = $DB->insert_record('external_tokens', $token);
            add_to_log(SITEID, 'webservice', get_string('createtokenforuserauto', 'webservice'), '' , 'User ID: ' . $user->id);
            $token->id = $tokenid;
        } else {
            throw new moodle_exception('cannotcreatetoken', 'webservice', '', $servicename);
        }
    }

    // log token access
    $DB->set_field('external_tokens', 'lastaccess', time(), array('id'=>$token->id));

    add_to_log(SITEID, 'webservice', 'user request webservice token', '' , 'User ID: ' . $user->id);

    $usertoken = new stdClass;
    $usertoken->token = $token->token;
    echo json_encode($usertoken);
} else {
    throw new moodle_exception('usernamenotfound', 'moodle');
}
