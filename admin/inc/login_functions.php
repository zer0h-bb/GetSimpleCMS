<?php if(!defined('IN_GS')){ die('you cannot load this page directly.'); }
/**
 * Login Functions
 *
 * @package GetSimple
 * @subpackage Login
 */

$MSG = null;

# if the login cookie is already set, redirect user to control panel
if(cookie_check()) {
	redirect($cookie_redirect);                                             
}

# was the form submitted?
if(isset($_POST['submitted'])) { 
	
	# initial variable setup
	// Initial variable setup
	$user_xml = GSDATAOTHERPATH . _id($_POST['userid']).'.xml';
	$user_xml = GSUSERSPATH . _id($_POST['userid']).'.xml';
	$userid = $_POST['userid'];
	$userid = lowercase($_POST['userid']);
	$password = $_POST['pwd'];
	$error = null;
	
	# check the username or password fields
	if ( !$userid || !$password ) {
		$error = true;
		$MSG .= '<b>'.i18n_r('ERROR').':</b> '.i18n_r('FILL_IN_REQ_FIELD').'.<br />';
	} 
	
	# check for any errors
	if ( !$error ) {
		
		# hash the given password
		$password = passhash($password);
		
		# does this user exist?
		if (file_exists($user_xml)) {

			# pull the data from the user's data file
			$data = getXML($user_xml);
			$PASSWD = $data->PWD;
			$USR = $data->USR;
		
			# do the username and password match?
			if ( ($userid == $USR) && ($password == $PASSWD) ) {
				$authenticated = true;
			} else {
				$authenticated = false;
				
				# add login failure to failed logins page
				$xmlfile = GSDATAOTHERPATH.'logs/failedlogins_'._id($USR).'.log';
				if ( ! file_exists($xmlfile) ) 	{ 
					$xml = new SimpleXMLExtended('<channel></channel>');
				} else {
					$xmldata = file_get_contents($xmlfile);
					$xml = new SimpleXMLExtended($xmldata);
				}
				$thislog = $xml->addChild('entry');
				$thislog->addChild('date', date('r'));
				$cdata = $thislog->addChild('Username');
				$cdata->addCData(htmlentities($userid, ENT_QUOTES));
				$cdata = $thislog->addChild('IP_Address');
				$ip = getenv("REMOTE_ADDR"); 
				$cdata->addCData(htmlentities($ip, ENT_QUOTES));
				XMLsave($xml, $xmlfile);
				
			} # end password match check
			
		} else {
			# user doesnt exist in this system
			$authenticated = false;
		}		
		
		# is this successful?
		if( $authenticated ) {
			# YES - set the login cookie, then redirect user to secure panel		
			create_cookie();
			setcookie('GS_ADMIN_USERNAME', _id($USR));
			redirect($cookie_redirect); 
		} else {
			# NO - show error message
			$MSG .= '<b>'.i18n_r('ERROR').':</b> '.i18n_r('LOGIN_FAILED').'.';
		} # end authenticated check
		
	} # end error check
	
} # end submission check
?>