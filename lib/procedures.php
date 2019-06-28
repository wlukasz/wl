<?php 
# lib/procedures.php

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * process_member_registration_step_1
 * 
 * This function is called from index.php (AJAX call)
 * Takes $_POST from user rego form
 * Returns array ['rc'] - return code & ['errmsg'] - message to be displayed to user 
 */

function process_member_registration_step_1 ( $post ) {

	$return_data = array();

	# check check for duplicate email
	$sql = "SELECT id FROM tbl_member WHERE email = ?";
	$params = array( $post['emailjoin'] ); //if no params then must be "array()", otherwise values comma separated
	$pdo_return_data = pdo_db_call ( __FUNCTION__, 1, '', $sql, $params, 'fetch', 2 );
	if ( $pdo_return_data['rc'] == '1' ) {
		if ( $pdo_result = $pdo_return_data['result'] ) { //duplicate email
			$return_data['rc'] = '0';
			$return_data['errmsg'] = 'Email address "' . $post['emailjoin'] . '" is already recorded.\nPlease use "Forgot Password" option to receive instructions at that email address.';
			return $return_data;
		}
	} else {
		$return_data['rc'] = '0';
		$return_data['errmsg'] = $pdo_return_data['result'];
		return $return_data;
	}

	// if username supplied
	if ( $post['usernamejoin'] ) {
		// check check for duplicate username
		$sql = "SELECT id FROM tbl_member WHERE username = ?";
		$params = array( $post['usernamejoin'] ); //if no params then must be "array()", otherwise values comma separated
		$pdo_return_data = pdo_db_call ( __FUNCTION__, 2, '', $sql, $params, 'fetch', 2 );
		if ( $pdo_return_data['rc'] == '1' ) {
			if ( $pdo_result = $pdo_return_data['result'] ) { //duplicate email
				$return_data['rc'] = '0';
				$return_data['errmsg'] = 'Username "' . $post['usernamejoin'] . '" is already recorded.\nPlease use a different one (or none).';
				return $return_data;
			}
		} else {
			$return_data['rc'] = '0';
			$return_data['errmsg'] = $pdo_return_data['result'];
			return $return_data;
		}
		$username = $post['usernamejoin'];
	} else {
		$username = $post['emailjoin'];
	}

	// hash the new plain-text password
	if ( $post['passwordjoin'] == $post['passwordrepeatjoin'] ) {
		$hashed_password = password_hash( $post['passwordjoin'], PASSWORD_DEFAULT );
	} else {
		$return_data['rc'] = '0';
		$return_data['errmsg'] = 'Error! Your passwords do not match.';
		return $return_data;
	}

	// generate random temporary string
	$temporary_random_string = md5(uniqid(time(), TRUE));

	// insert new user details to database
	$sql = "INSERT INTO tbl_member ( group_id,first_name,last_name,email,username,password,temporary_random_string,random_string_active,random_string_expiry )
					VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ? )";
	$params = array( 1, $post['fnamejoin'], $post['lnamejoin'], $post['emailjoin'], $username, $hashed_password, $temporary_random_string, TRUE, (time() + 86400) );
	$pdo_return_data = pdo_db_call ( __FUNCTION__, 3, '', $sql, $params, 'rowCount', '' );
	if ( $pdo_return_data['rc'] == '1' ) {
		if ( $pdo_return_data['result'] == 1 ) { //one row inserted
			// send confirmation request email

			$confirmation_link = TRANSFER_PROTOCOL . $_SERVER['HTTP_HOST'] . APPLICATION_FOLDER . '/' . REGO_FORM_ACTION_PAGE . '/?trs=' . $temporary_random_string;
				
			// set up mail message
			$to       = $post['emailjoin'];
			$subject  = 'Confirmation Request';

			$message  = '
				<html><head></head><body>
				<p>Hello ' . $post['fnamejoin'] . ',</p>
				<p>Thank you for registering.</p>
				<p>Please follow the link below to complete your request:</p>
				<p><a href="' . $confirmation_link . '">Confirmation link</a></p>
				<p>If, for whatever reason, you cannot click on the link above, please copy the link printed below:<br>' . $confirmation_link . '
					<br>and paste it in your browser address box and press enter. Follow instructions as they appear.</p>
				<p>Some of the reasons the link is not clikable are that this message was moved to "spam" or "trash" or "bin" folder.
				<br> You can re-enable the link by moving it to "inbox".</p>
				<p>Best Regards,</p><p>Administrator</p></body></html>
				';

			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			$headers .=	'From: Admin <' . ADMIN_EMAIL . '>' . "\r\n";
			$headers .= 'Reply-To: ' . ADMIN_EMAIL . "\r\n";
			$headers .= 'Return-Path: ' . ADMIN_EMAIL . "\r\n";
			$headers .= 'Message-ID: <' . md5(uniqid(time())) . '@' . $_SERVER['SERVER_NAME'] . ">\r\n";
			$headers .= 'X-Mailer: PHP/' . phpversion() . "\r\n";
			$headers .= 'Date: ' . date('r', time()) . "\r\n";

			# added as a result of Crazy's investigation - some anti spam bizo
			$mailFrom = ADMIN_EMAIL;

			# send mail
			if (mail($to, $subject, $message, $headers, '-f ' . $mailFrom)){ # mail sent ok
				$return_data['rc'] = '1';
				$return_data['errmsg'] = 'You have joined the Happy Crowd! Check your email for confirmation.';
			} else { # problem with sending mail
				$return_data['rc'] = '0';
				$return_data['errmsg'] = 'We have saved your details but failed to send Confirmation Request Email.\nPlease contact Support.';
			} # /if mail sent ok

			return $return_data;
		} else {
			$return_data['rc'] = '0';
			$return_data['errmsg'] = 'Your record could not be saved.\nPlease contact Support.';
			return $return_data;
		}
	} else {
		$return_data['rc'] = '0';
		$return_data['errmsg'] = $pdo_return_data['result'];
		return $return_data;
	}
} // process_member_registration_step_1

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * forgot_password_step_1
 * 
 * This function is called from index.php (AJAX call)
 * Takes $_POST from user forgot-password form
 * Returns array ['rc'] - return code & ['errmsg'] - message to be displayed to user 
 */

function forgot_password_step_1 ( $post ) {

	$return_data = array();

	# check if email exists
	$sql = "SELECT id FROM tbl_member WHERE email = ?";
	$params = array( $post['fpassemail'] ); //if no params then must be "array()", otherwise values comma separated
	$pdo_return_data = pdo_db_call ( __FUNCTION__, 1, '', $sql, $params, 'fetch', 2 );
	if ( $pdo_return_data['rc'] == '1' ) {
		if ( $pdo_result = $pdo_return_data['result'] ) { // email exists

			// generate random temporary string
			$temporary_random_string = md5(uniqid(time(), TRUE));
				
			$sql = "UPDATE tbl_member SET temporary_random_string = ?, random_string_active = ?, random_string_expiry = ? WHERE email = ?";
			$params = array( $temporary_random_string, TRUE, (time() + 86400), $post['fpassemail'] );
			$pdo_return_data = pdo_db_call ( __FUNCTION__, 2, '', $sql, $params, 'rowCount', '' );
			if ( $pdo_return_data['rc'] == '1' ) {
				
				$forgot_link = TRANSFER_PROTOCOL . $_SERVER['HTTP_HOST'] . APPLICATION_FOLDER . '/' . FORGOT_PASSWORD_ACTION_PAGE . '/?trs=' . $temporary_random_string;

				// set up mail message
				$to       = $post['fpassemail'];
				$subject  = 'Forgotten Password Request';
				
				$message  = '
					<html><head></head><body>
					<p>Hello,</p>
					<p>Someone, most likely you, has requested a &quot;password reset&quot; for your account.</p>
					<p>If this was not you, someone was trying to hijack your account.<br>
					Please reply to this email immediately to notify the Administrator!</p>
					<p>Otherwise, please follow the link below to complete your request:</p>
					<p><a href="' . $forgot_link . '">Password Reset link</a></p>
					<p>Best Regards,</p><p>Admin</p></body></html>
					';
				
				$headers  = 'MIME-Version: 1.0' . "\r\n";
				$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
				$headers .=	'From: Admin <' . ADMIN_EMAIL . '>' . "\r\n";
				$headers .= 'Reply-To: ' . ADMIN_EMAIL . "\r\n";
				$headers .= 'Return-Path: ' . ADMIN_EMAIL . "\r\n";
				$headers .= 'Message-ID: <' . md5(uniqid(time())) . '@' . $_SERVER['SERVER_NAME'] . ">\r\n";
				$headers .= 'X-Mailer: PHP/' . phpversion() . "\r\n";
				$headers .= 'Date: ' . date('r', time()) . "\r\n";
				
				# added as a result of Crazy's investigation - some anti spam bizo
				$mailFrom = ADMIN_EMAIL;
				
				# send mail
				if (mail($to, $subject, $message, $headers, '-f ' . $mailFrom)){ # mail sent ok
					$return_data['rc'] = '1';
					$return_data['errmsg'] = 'Request accepted!<br>Check your email (including trash/spam folders) for intructions.';
				} else { # problem with sending mail
					$return_data['rc'] = '0';
					$return_data['errmsg'] = 'System Error.<br>Please try again.';
				} # /if mail sent ok
			} else {
				$return_data['rc'] = '0';
				$return_data['errmsg'] = 'Error Updating Database.<br>Please contact Support.';
			}
		} else {
			$return_data['rc'] = '0';
			$return_data['errmsg'] = 'Unknown Email Address "' . $post['fpassemail'] . '".<br>Please re-enter.';
		}
	} else {
		$return_data['rc'] = '0';
		$return_data['errmsg'] = $pdo_return_data['result'];
	}
	
	return $return_data;
	
} // forgot_password_step_1

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * reset_password
 *
 * This function is called from index.php (AJAX call)
 * Takes $_POST from password-reset form
 * Returns array ['rc'] - return code  & ['errmsg'] - message to be displayed to user
 */

function reset_password ( $post) {

	$return_data = array();

	# retrieve user record with supplied trs
	$sql = "SELECT * FROM tbl_member WHERE temporary_random_string = ?";
	$params = array( $post['resetpass-trs'] ); //if no params then must be "array()", otherwise values comma separated
	$pdo_return_data = pdo_db_call ( __FUNCTION__, 1, '', $sql, $params, 'fetch', 2 );
	if ( $pdo_return_data['rc'] == '1' ) {
		if ( $pdo_result = $pdo_return_data['result'] ) { // row returned - match
			# hash new password & store in database
			if ( $new_hash = password_hash( $post['resetpassnew'], PASSWORD_DEFAULT ) ) { # password successfully hashed
				# store new hash in db
				$sql = "UPDATE tbl_member SET password = ? WHERE id = ?";
				$params = array( $new_hash, $pdo_result['id'] );
				$pdo_return_data = pdo_db_call ( __FUNCTION__, 2, '', $sql, $params, 'rowCount', '' );
				if ( $pdo_return_data['rc'] == '1' ){ # query executed successfully
					$return_data['rc'] = '1';
					$return_data['errmsg'] = 'Password reset successfully.';
				} else {
					$return_data['rc'] = '0';
					$return_data['errmsg'] = 'Catastrophic System Error 6.<br>Shut down your computer and run away!';
				}
			} else { # password_hash() function returned false
				$return_data['rc'] = '0';
				$return_data['errmsg'] = 'Catastrophic System Error 5.<br>Shut down your computer and run away!';
			} # /if password successfully hashed
		} else {
			$return_data['rc'] = '0';
			$return_data['errmsg'] = 'Invalid Security String.<br>Try to make a new request.';
		}
	} else {
		$return_data['rc'] = '0';
		$return_data['errmsg'] = 'System Error.\nCould not retrieve user record.';
	}

	return $return_data;

} // reset_password


/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * process_member_login
 * 
 * This function is called from index.php (AJAX call)
 * Takes $_POST from user login form
 * Returns array ['rc'] - return code & ['errmsg'] - message to be displayed to user 
 */

function process_member_login ( $post ) {

	$return_data = array();
	
	# check check for valid email or username
	$sql = "SELECT * FROM tbl_member WHERE email = ? OR username = ?";
	$params = array( $post['login'], $post['login'] ); //if no params then must be "array()", otherwise values comma separated
	$pdo_return_data = pdo_db_call ( __FUNCTION__, 1, '', $sql, $params, 'fetchAll', 4 );
	if ( $pdo_return_data['rc'] == '1' ) {
		if ( !$pdo_result = $pdo_return_data['result'] ) { // null returned - no match
			$return_data['rc'] = '0';
			$return_data['errmsg'] = 'Invalid Login details. Please enter again.';
			return $return_data;
		}
	} else {
		$return_data['rc'] = '0';
		$return_data['errmsg'] = $pdo_return_data['result'];
		return $return_data;
	}	

	# check for pending registration or forgotten password
	if ( $pdo_result[0]['random_string_active'] ) { # pending registration or forgotten password
		$return_data['rc'] = '0';
		$return_data['errmsg'] = 'Registration Pending or Forgot Password Enquiry Active.<br>Check your Email for clues.';
		return $return_data;
	} # /if random_string_active

	# check password
	if ( !password_verify( $post['passwordlogin'], $pdo_result[0]['password'] ) ) { # password invalid
			$return_data['rc'] = '0';
			$return_data['errmsg'] = 'Incorrect Password. Please enter again.';
			return $return_data;
	} # /if valid password
					
	# this bit is technical - if causing problems may be turned off
	# it doesn't change the password - just updates the hash if PASSWORD_DEFAULT has changed - due to PHP version change - forward compatibility
	if ( password_needs_rehash( $pdo_result[0]['password'], PASSWORD_DEFAULT ) ) { # hash needs rehashing - algorithm or options changed (thats PHP bizo)
		# generate new hash using plain-text password from the login form
		if ( $new_hash = password_hash( $post['passwordlogin'], PASSWORD_DEFAULT ) ) { # password successfully hashed
			# store new hash in db
			$sql = "UPDATE tbl_member SET password = ? WHERE id = ?";
			$params = array( $new_hash, $pdo_result[0]['id'] );
			$pdo_return_data = pdo_db_call ( __FUNCTION__, 2, '', $sql, $params, 'rowCount', '' );
			if ( $pdo_return_data['rc'] == '0' ){ # query did not execute successfully
				$return_data['rc'] = '0';
				$return_data['errmsg'] = 'Catastrophic System Error 2.<br>Shut down your computer and run away!';
				return $return_data;
			} # /if new hash stored in db
		} else { # password_hash() function returned false
			$return_data['rc'] = '0';
			$return_data['errmsg'] = 'Catastrophic System Error 1.<br>Shut down your computer and run away!';
			return $return_data;
		} # /if password successfully hashed
	} # /if password needs rehash
	
	# this trick makes COOKIE available without refreshing the page
	# session token is a hashed (user id concatenated with a random number)
	$_COOKIE['session_token'] = md5( $pdo_result[0]['id'] . uniqid( rand(), true ) );
		
	# this sets COOKIE and makes it available for 7 days
	setcookie('session_token',$_COOKIE['session_token'], time() + (86400 * 7),'/'); # 86400 sec = 1 day
		
	# save session validation token & current timestamp to database
	$sql = "UPDATE tbl_member SET last_login = ?, session_token  = ? WHERE id = ?";
	$params = array( date('Y-m-d H:i:s'), $_COOKIE['session_token'], $pdo_result[0]['id'] );
	$pdo_return_data = pdo_db_call ( __FUNCTION__, 3, '', $sql, $params, 'rowCount', '' );
	if ( $pdo_return_data['rc'] == '0' ) { # query did not execute successfully
		setcookie('session_token',NULL,-1,'/');
		unset($_COOKIE['session_token']);
		$return_data['rc'] = '0';
		$return_data['errmsg'] = 'Failed to save Session Token. Please contact Support.';
		return $return_data;
	} # /if new hash stored in db
	
	$return_data['rc'] = '1';
	$return_data['errmsg'] = 'Login Successful';
	return $return_data;

} // process_member_login

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * change_password
 * 
 * This function is called from index.php (AJAX call) 
 * Takes $_POST from password-change form
 * Returns array ['rc'] - return code  & ['errmsg'] - message to be displayed to user 
 */

function change_password ( $post) {

	$return_data = array();
	
	# check current password
	$sql = "SELECT * FROM tbl_member WHERE session_token = ?";
	$params = array( $_COOKIE['session_token'] ); //if no params then must be "array()", otherwise values comma separated
	$pdo_return_data = pdo_db_call ( __FUNCTION__, 1, '', $sql, $params, 'fetch', 2 );
	if ( $pdo_return_data['rc'] == '1' ) {
		if ( $pdo_result = $pdo_return_data['result'] ) { // row returned - match
			if ( password_verify( $post['passwordcpass'], $pdo_result['password'] ) ) { # current password valid
				# hash new password & store in database
				if ( $new_hash = password_hash( $post['passwordnew'], PASSWORD_DEFAULT ) ) { # password successfully hashed
					# store new hash in db
					$sql = "UPDATE tbl_member SET password = ? WHERE session_token = ?";
					$params = array( $new_hash, $_COOKIE['session_token'] );
					$pdo_return_data = pdo_db_call ( __FUNCTION__, 2, '', $sql, $params, 'rowCount', '' );
					if ( $pdo_return_data['rc'] == '1' ){ # query executed successfully
						$return_data['rc'] = '1';
						$return_data['errmsg'] = 'Password updated successfully.';
					} else { 
						$return_data['rc'] = '0';
						$return_data['errmsg'] = 'Catastrophic System Error 4.<br>Shut down your computer and run away!';
					}
				} else { # password_hash() function returned false
					$return_data['rc'] = '0';
					$return_data['errmsg'] = 'Catastrophic System Error 3.<br>Shut down your computer and run away!';
				} # /if password successfully hashed
			} else {
				$return_data['rc'] = '0';
				$return_data['errmsg'] = 'Incorrect Current Password.<br>Try to correct Current Password.';
			}
		} else { 
			$return_data['rc'] = '0';
			$return_data['errmsg'] = 'Invalid Cookie details.<br>Try to login again.';
		}
	} else {
		$return_data['rc'] = '0';
		$return_data['errmsg'] = 'System Error.\nCould not retrieve current password.';
	}
	
	return $return_data;
	
} // change_password

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * update_user_details
 * 
 * This function is called from index.php (AJAX call) after successful logggin & after user details update
 * Takes $_POST from user login form
 * Returns array ['rc'] - return code  & ['errmsg'] - message to be displayed to user 
 */

function update_user_details ( $post) {

	$return_data = array();
	
	# update user details in database
	$sql = "UPDATE tbl_member SET first_name = ?, last_name = ?, email = ?, username = ? WHERE session_token = ?";
	$params = array( $post['fnameprof'], $post['lnameprof'], $post['emailprof'], $post['usernameprof'], $_COOKIE['session_token'] );
	$pdo_return_data = pdo_db_call ( __FUNCTION__, 1, '', $sql, $params, 'rowCount', '' );
	if ( $pdo_return_data['rc'] == '0' ) { # query did not execute successfully
		$return_data['rc'] = '0';
		$return_data['errmsg'] = 'Failed to save User Details. Please contact Support.';
		return $return_data;
	} # /if new hash stored in db
	
	$return_data['rc'] = '1';
	$return_data['errmsg'] = 'User Details Updated Successfully';
	return $return_data;
	
} // update_user_details

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * get_user_details_php
 * 
 * This function is called from index.php (AJAX call) after successful login & after user details update
 * Returns array ['rc'] - return code & ['userdata'] 
 */

function get_user_details_php () {

	$return_data = array();
	
	# check check for valid email or username
	$sql = "SELECT * FROM tbl_member WHERE session_token = ?";
	$params = array( $_COOKIE['session_token'] ); //if no params then must be "array()", otherwise values comma separated
	$pdo_return_data = pdo_db_call ( __FUNCTION__, 1, '', $sql, $params, 'fetch', 2 );
	if ( $pdo_return_data['rc'] == '1' ) {
		if ( !$pdo_result = $pdo_return_data['result'] ) { // null returned - no match
			$return_data['rc'] = '0';
			$return_data['errmsg'] = 'Invalid Cookie details.\nCould not retrieve user data.';
			return $return_data;
		} else {
			$return_data['rc'] = '1';
			$return_data['result'] = $pdo_result;
			return $return_data;
		}
	} else {
		$return_data['rc'] = '0';
		$return_data['errmsg'] = 'System Error.\nCould not retrieve user data.';
		return $return_data;
	}
	
} // get_user_details_php

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * process_member_logout
 * 
 * This function is called from index.php (AJAX call)
 * Takes $_POST from user logout click
 * Returns array ['rc'] - return code & ['errmsg'] - message to be displayed to user 
 */

function process_member_logout () {

	$return_data = array();

	setcookie('session_token',NULL,-1,'/');
	unset($_COOKIE['session_token']);
	
	unset ( $_SESSION );
	session_destroy ();
	session_start(); # need to start new session 
	
	$return_data['rc'] = '1';
	$return_data['errmsg'] = 'Log Out Successful';
	return $return_data;

} // process_member_logout

function search_weather_city ( $post ) {

	$return_data = array();

	# get cities matching pattern
	$sql = "SELECT * FROM tbl_cities WHERE name like ?";
	$params = array( $post['cityname'] ); //if no params then must be "array()", otherwise values comma separated
	$pdo_return_data = pdo_db_call ( __FUNCTION__, 1, '', $sql, $params, 'fetchAll', 2 );
	if ( $pdo_return_data['rc'] == '1' ) {
		if ( !$pdo_result = $pdo_return_data['result'] ) { // null returned - no match
			$return_data['result'] = '';
		} else {
         $work_array = $pdo_result;
         foreach ( $work_array as $key => $data ) {
            # get country name
            $country_name_result = get_country_name( $data['country'] );
            switch ( $country_name_result['rc'] ) {
               case "1":
                  $work_array[$key]['country_name'] = $country_name_result['country_name'];
                  break;
               case "0":
               case "2":
               default:
                  $work_array[$key]['country_name'] = $data['country'];
            }  
         } //foreach

			$return_data['result'] = $work_array;
		}
		$return_data['rc'] = '1';
	} else {
		$return_data['rc'] = '0';
		$return_data['errmsg'] = 'System Error.\nCould not retrieve city data.';
	}

	return $return_data;

} //search_weather_city

function search_weather_lang ( $post ) {

	$return_data = array();

	# get languages matching pattern
	$sql = "SELECT * FROM tbl_language WHERE darksky_support = TRUE AND language like ?";
	$params = array( $post['langname'] ); //if no params then must be "array()", otherwise values comma separated
	$pdo_return_data = pdo_db_call ( __FUNCTION__, 1, '', $sql, $params, 'fetchAll', 2 );
	if ( $pdo_return_data['rc'] == '1' ) {
		if ( !$pdo_result = $pdo_return_data['result'] ) { // null returned - no match
			$return_data['result'] = '';
			$return_data['rc'] = '2';
		} else {
			$return_data['result'] = $pdo_result;
			$return_data['rc'] = '1';
		}
	} else {
		$return_data['rc'] = '0';
		$return_data['errmsg'] = 'System Error. Could not retrieve Language ISO Code.';
	}

	return $return_data;

} // search_weather_lang

function get_country_name ( $country_code ) {
   $sql = "SELECT country FROM tbl_country WHERE a2_iso = ?";
   $params = array( $country_code ); //if no params then must be "array()", otherwise values comma separated
   $pdo_return_data = pdo_db_call ( __FUNCTION__, 1, '', $sql, $params, 'fetch', 2 );
   if ( $pdo_return_data['rc'] == '1' ) {
      if ( !$pdo_result = $pdo_return_data['result'] ) { // null returned - no match
         $return_data['rc'] = '2';
      } else {
         $return_data['rc'] = '1';
         $return_data['country_name'] = $pdo_result['country'];
      }
   } else {
      $return_data['rc'] = '0';
      $return_data['errmsg'] = 'System Error.\nCould not retrieve country name.';
   }

   return $return_data;

} // get_country_name

function get_country_flag_plus ( $country_code ) {
   $sql = "SELECT country, flag FROM tbl_country_flag WHERE a2_iso = ?";
   $params = array( $country_code ); //if no params then must be "array()", otherwise values comma separated
   $pdo_return_data = pdo_db_call ( __FUNCTION__, 1, '', $sql, $params, 'fetch', 2 );
   if ( $pdo_return_data['rc'] == '1' ) {
      if ( !$pdo_result = $pdo_return_data['result'] ) { // null returned - no match
         $return_data['rc'] = '2';
      } else {
         $return_data['rc'] = '1';
         $return_data['country_name_lowercase'] = $pdo_result['country'];
         $return_data['flag'] = $pdo_result['flag'];
      }
   } else {
      $return_data['rc'] = '0';
      $return_data['errmsg'] = 'System Error.\nCould not retrieve country flag.';
   }

   return $return_data;

} // get_country_flag_plus

function get_weather_data ( $post ) {

	$return_data = array();
	$return_data['action'] = $post['action'] ;
    $city_id = $post['cityid'];

// OpenWeatherMap
	if ( $post['source'] == 'openweathermap') {
    
		// forecast
		$url = 'https://api.openweathermap.org/data/2.5/forecast?id=' . $city_id . '&units=metric&APPID=' . API_KEY_OPEN_WEATHER;
		if ( !$api_response = file_get_contents( $url ) ) {
			$return_data['rc'] = '0';
			$return_data['errmsg'] = 'Could not retrieve forecast data';
			return $return_data;	
		}
		$forecast_list = json_decode( $api_response, true );

		//get timezone offset
		$tz_offset = $forecast_list['city']['timezone'];

		// set up forecast matrix
		$forecast = $forecast_list['list'];
		$fcts_matrix = array();
		$day_no = 0;
		$day = '';    

		foreach ( $forecast as $key => $list ) {
			
			if ( $day != date( 'j', $list['dt'] + $tz_offset ) ) {
				// var_dump( 'UTC: '.date( 'd M Y H:i', $list['dt'] ) );        
				// var_dump( 'day UTC: '.date( 'j', $list['dt'] ) );        
				// var_dump( 'day OFF: '.date( 'j', $list['dt'] + $tz_offset ) );
				// var_dump( '--------------------------');        
				$day_no++;
				$day = date( 'j', $list['dt'] + $tz_offset );
			}
	
			$fcts_matrix[$key]['hr' . date( 'H', $list['dt'] )] = date( 'H:00', $list['dt'] );
			$fcts_matrix[$key]['day' . $day_no] = date( 'l', $list['dt'] );
				
			$fcts_matrix[$key]['d' . $day_no . date( 'H', $list['dt'] )] .= date( 'l', $list['dt'] + $tz_offset ).' '.date( 'H:00', $list['dt'] + $tz_offset ).'<br>';
			$fcts_matrix[$key]['d' . $day_no . date( 'H', $list['dt'] )] .= '<img src="wicons/'.$list['weather'][0]['icon'].'.png" width="25px" height="25px">';
			$fcts_matrix[$key]['d' . $day_no . date( 'H', $list['dt'] )] .= $list['weather'][0]['description'].'<br>';
			$fcts_matrix[$key]['d' . $day_no . date( 'H', $list['dt'] )] .= '<img src="wicons/temp.png" width="25px" height="25px">'.number_format( $list['main']['temp'], 1 ).'&#176C<br>';
			$fcts_matrix[$key]['d' . $day_no . date( 'H', $list['dt'] )] .= '<img src="wicons/hpa.png" width="25px" height="25px">'.number_format( $list['main']['pressure'], 1 ).'hPa<br>';
			$fcts_matrix[$key]['d' . $day_no . date( 'H', $list['dt'] )] .= '<img src="wicons/humi.png" width="25px" height="25px">'.number_format( $list['main']['humidity'], 0 ).'%<br>';
			$fcts_matrix[$key]['d' . $day_no . date( 'H', $list['dt'] )] .= '<img src="wicons/wind.png" width="25px" height="25px">'.number_format( $list['wind']['speed'] * 3.6 / 1.852 , 1 ).'kt '. convert_wind_direction( $list['wind']['deg'] ) .'<br>';
		}
		
		$return_data['fcst'] = $fcts_matrix;
		
// $return_data['rc'] = '1';
// $return_data['errmsg'] = 'Weather Data Retrieved Successfully';
// return $return_data;
###################################################################################################
// END set up forecast matrix    

		//current weather
		$url = 'https://api.openweathermap.org/data/2.5/weather?id=' . $city_id . '&units=metric&APPID=' . API_KEY_OPEN_WEATHER;
		if ( !$api_response = file_get_contents( $url ) ) {
			$return_data['rc'] = '0';
			$return_data['errmsg'] = 'Could not retrieve current weather data';
			return $return_data;	
		}
		$weather = json_decode($api_response, true);
		$curr_matrix = array();

		//set timezone
		$tz = timezone_name_from_abbr( '', $tz_offset, 0 );
		$local_timezone = date_default_timezone_get() ;
		date_default_timezone_set( $tz );

		# get country name
		$country_name_result = get_country_name( $weather['sys']['country'] );
		switch ( $country_name_result['rc'] ) {
			case "1":
				$country_name = $country_name_result['country_name'];
				break;
			case "0":
			case "2":
			default:
				$country_name = $weather['sys']['country'];
		}  
		
		# get country flag
		$country_flag_result = get_country_flag_plus ( $weather['sys']['country'] );
		switch ( $country_name_result['rc'] ) {
			case "1":
				$flag = $country_flag_result['flag'];
				break;
			case "0":
			case "2":
			default:
				$flag = '';
		}  
		
		$sunrise = date( 'G:i', $weather['sys']['sunrise'] );
		$sunset = date( 'G:i', $weather['sys']['sunset'] );
		
		$curr_matrix['city'] = $weather['name'] . ', <span style="font-size: 0.7em">' . $country_name . '</span>' . '<img src="flags/' . $flag . '" alt="">';
		$curr_matrix['dnow'] = date( 'D, j M Y', $weather['dt'] );
		$curr_matrix['ticn'] = '<img src="wicons/temp.png" width="100px" height="100px" alt="">';
		$curr_matrix['tnow'] = number_format( $weather['main']['temp'], 1 ) . '&#176C';
		$curr_matrix['tmin'] = 'Min ' . number_format( $weather['main']['temp_min'], 1 ) . '&#176C';
		$curr_matrix['tmax'] = 'Max ' . number_format( $weather['main']['temp_max'], 1 ) . '&#176C';
		$curr_matrix['icon'] = '<img src="wicons/' . $weather['weather'][0]['icon'] . '.png" width="100px" height="100px" alt="">';
		$curr_matrix['wdsc'] = $weather['weather'][0]['description'];
		$curr_matrix['phpa'] = '<img src="wicons/hpa.png" width="50px" height="50px" alt="">' . $weather['main']['pressure'] . ' hPa';
		$curr_matrix['humi'] = '<img src="wicons/humi.png" width="50px" height="50px" alt="">' . $weather['main']['humidity'] . '%';
		$curr_matrix['wind'] = '<img src="wicons/wind.png" width="50px" height="50px" alt="">' . number_format( $weather['wind']['speed'] * 3.6 / 1.852 , 1 ) . ' kt ' . convert_wind_direction( $weather['wind']['deg'] ); // m/s->knots
		$curr_matrix['ccvr'] = '<img src="wicons/ccvr.png" width="50px" height="50px" alt="">' . $weather['clouds']['all'] . '%';
		$curr_matrix['sunr'] = '<img src="wicons/sunrise.png" width="50px" height="50px" alt="">' . $sunrise;
		$curr_matrix['suns'] = '<img src="wicons/sunset.png" width="50px" height="50px" alt="">' . $sunset;
			
		$return_data['curr'] = $curr_matrix;
			
		# get lat & lon
		$lat_lon_result = get_lat_lon ( $city_id );
		$return_data['coord']['lat'] = $lat_lon_result['result']['lat'];
		$return_data['coord']['lon'] = $lat_lon_result['result']['lon'];

		// reset timezone
		date_default_timezone_set( $local_timezone );

		$return_data['rc'] = '1';
		$return_data['errmsg'] = 'Weather Data Retrieved Successfully';
		return $return_data;
// END of OpenWeatherMap

// DarkSky API		
	} elseif ( $post['source'] == 'darksky') {

		# get lat, lon, city name & couuntry code
		if ( $city = get_lat_lon( $city_id ) ) {
			if ( $city['rc'] == '1' ) {
				$lat = $city['result']['lat'];
				$lon = $city['result']['lon'];
				$city_name = $city['result']['name'];
				$country_code = $city['result']['country'];
			} else {   
				$return_data['rc'] = $city['rc'];
				$return_data['errmsg'] = 'Lat & Lon unavailable';
				return $return_data;
			}
		} else {
			$return_data['rc'] = '0';
			$return_data['errmsg'] = 'Failed to get lat & lon';
			return $return_data;
		}
	
		# get country name
		$country_name_result = get_country_name( $country_code );
		switch ( $country_name_result['rc'] ) {
			case "1":
			$country_name = $country_name_result['country_name'];
				break;
			case "0":
			case "2":
			default:
			$country_name  = $country_code;
		}  
			
		# get country flag
		$country_flag_result = get_country_flag_plus ( $country_code );
		switch ( $country_name_result['rc'] ) {
			case "1":
				$flag = $country_flag_result['flag'];
				break;
			case "0":
			case "2":
			default:
				$flag = '';
		}

		$lang = !isset( $post['lang'] ) || strlen( $post['lang'] ) != 2 ? '&lang=en' : '&lang=' . $post['lang'];
    
		$url = 'https://api.darksky.net/forecast/' . API_KEY_DARKSKY . '/' . $lat . ',' . $lon . '?exclude=minutely,alerts&units=si' . $lang ;
		$api_response = file_get_contents( $url );
		if ( !$data = json_decode( $api_response ) ) {
			$return_data['rc'] = '0';
			$return_data['errmsg'] = 'DarkSky API failure';
			return $return_data;
		}

////////////////////////////////////////////////////

	$local_timezone = date_default_timezone_get() ;
	date_default_timezone_set( $data->timezone );

	$return_data['data']['coords']['lat'] = $lat;
	$return_data['data']['coords']['lon'] = $lon;
	$return_data['data']['common']['dscity'] = '<span style="font-size: 2.0em">' . $city_name. ', <span style="font-size: 0.7em">' . $country_name . '</span>' . '<span><img src="flags/' . $flag . '" alt=""  style="border-style: solid;border-width: 1px;border-radius: 0px;border-color: lightgrey;"></span></span><br><span style="font-size: 0.7em">Local time is ' . date( 'D, ga', $data->currently->time ) . '</span>';
	$return_data['data']['common']['dssumm'] = $data->daily->summary;
	$return_data['data']['common']['dscurr'] = 'Now<br>' .
	'Temperature <span style="font-size: 1.4em;color: dodgerblue;">' . number_format( $data->currently->temperature, 0 ) . '</span>&#176C<br>' .
	'Pressure <span style="font-size: 1.4em;color: dodgerblue;">' . number_format( $data->currently->pressure, 0 ) . '</span>hPa<br>' .
	'Humidity <span style="font-size: 1.4em;color: dodgerblue;">' . number_format( $data->currently->humidity * 100, 0 ) . '</span>%<br>' .
	'Wind <span style="font-size: 1.4em;color: dodgerblue;">' . number_format( $data->currently->windSpeed, 1 ) . '</span>m/s, ' . convert_wind_direction( $data->currently->windBearing ) . '<br>' .
	'Gusting <span style="font-size: 1.4em;color: dodgerblue;">' . number_format( $data->currently->windGust, 1 ) . '</span>m/s<br>' .
	'Cloud cover <span style="font-size: 1.4em;color: dodgerblue;">' . number_format( $data->currently->cloudCover * 100, 0 ) . '</span>%<br>' .
	'Visibility <span style="font-size: 1.4em;color: dodgerblue;">' . number_format( $data->currently->visibility, 1 ) . '</span>km';

	//arch array data
	foreach ( $data->hourly->data as $key => $value ) {
		if ( $key > 23 ) break;
		
		$return_data['data']['archdata'][$key]['ddd'] = date( 'D', $value->time );
		$return_data['data']['archdata'][$key]['hour'] = date( 'ga', $value->time );
		$return_data['data']['archdata'][$key]['temp'] = number_format( $value->apparentTemperature, 0 );
	}

    //daily summaries
    foreach ( $data->daily->data as $key => $value ) {
		$return_data['data']['daily']['dlysum'.$key] = 
		'<span style="padding: 5px;font-size: 0.8em;">' .
		'<span style="font-size: 1.5em;">' . date( 'l', $value->time ) . '</span>' . 
		'<span style="padding-left: 180px;">' .
		'Sunrise <span style="font-size: 1.4em;color: dodgerblue;">' . date( 'g:ia', $value->sunriseTime ) . '</span>' .
		' | Sunset <span style="font-size: 1.4em;color: dodgerblue;">' . date( 'g:ia', $value->sunsetTime ) . '</span>' .
		'</span>' .
		'<span style="padding: 5px;cursor: pointer;float: right;" class="togglehourly" value="' . $key . '">more...</span><br>' .
		'<span style="display: block;padding: 15px 15px 0px 15px;">' .$value->summary . '</span>' .
		'<span style="display: block;padding: 10px 15px 0px 15px;">' .
		'Low Temperature <span style="font-size: 1.4em;color: dodgerblue;">' . number_format( $value->temperatureLow, 0 ) . '</span>&#176C (' . date( 'g a', $value->temperatureLowTime ) . ') | ' .
		'High Temperature <span style="font-size: 1.4em;color: dodgerblue;">' . number_format( $value->temperatureHigh, 0 ) . '</span>&#176C (' . date( 'g a', $value->temperatureHighTime ) . ') | ' .
		'Pressure <span style="font-size: 1.4em;color: dodgerblue;">' . number_format( $value->pressure, 0 ) . '</span>hPa | ' .
		'Humidity <span style="font-size: 1.4em;color: dodgerblue;">' . number_format( $value->humidity * 100, 0 ) . '</span>% | ' .
		'Wind <span style="font-size: 1.4em;color: dodgerblue;">' . number_format( $value->windSpeed, 1 ) . '</span>m/s, ' . convert_wind_direction( $value->windBearing ) . ' | ' .
		'Gusting <span style="font-size: 1.4em;color: dodgerblue;">' . number_format( $value->windGust, 1 ) . '</span>m/s | ' .
		'Cloud cover <span style="font-size: 1.4em;color: dodgerblue;">' . number_format( $value->cloudCover * 100, 0 ) . '</span>% | ' .
		'Visibility <span style="font-size: 1.4em;color: dodgerblue;">' . number_format( $value->visibility, 1 ) . '</span>km | ' .
		'UV Index <span style="font-size: 1.4em;color: dodgerblue;">' . number_format( $value->uvIndex, 0 ) . '</span> (' . date( 'g a', $value->uvIndexTime ) . ')' .
		'</span';
		'</span';
    }

    //hourly array data
    $count = 0;
    $items_processed = 0;
	$day_no = -1;
	$the_day = '';
    foreach ( $data->hourly->data as $key => $value ) {
        
        if ( ++$count%2 === 1 ) { // process even only
            $items_processed++;
			if ( $the_day != date( 'j', $value->time ) ) {
				$the_day = date( 'j', $value->time);
				$items_processed = 1;
				$day_no++;
			}

            $id_suffix = str_pad( $items_processed * 2, 2 , '0', STR_PAD_LEFT );

            // $return_data['data']['hourly'][$key]['key'] = $key;
            // $return_data['data']['hourly'][$key]['count'] = $count;
            // $return_data['data']['hourly'][$key]['items_processed'] = $items_processed;
            // $return_data['data']['hourly'][$key]['id_suffix'] = $id_suffix;
            $return_data['data']['hourly'][$key]['value_attr'] = $day_no;
            // $return_data['data']['hourly'][$key]['time'] = date( 'j M Y, ga', $value->time );
            $return_data['data']['hourly'][$key]['html'] = 
            '<dshr' . $id_suffix . ' id="dshr' . $id_suffix . '" class="dshr" value="' . $day_no . '">' .
			'<span>' .
			'<span style="display: inline-block;vertical-align:top;width: 50px;font-size: 1.3em;">' . date( 'ga', $value->time ) . '</span>' . 
			'<span style="display: inline-block;margin: 0px 0px 10px 15px;font-size: 0.8em;">' . $value->summary . 
			'<span style="font-size: 1.0em;display: block;padding: 0px;">' .
            'Temperature <span style="font-size: 1.0em;color: dodgerblue;">' . number_format( $value->temperature, 0 ) . '</span>&#176C | ' .
            'Wind <span style="font-size: 1.0em;color: dodgerblue;">' . number_format( $value->windSpeed, 1 ) . '</span>m/s, ' . convert_wind_direction( $value->windBearing ) . ' | ' .
            'Cloud cover <span style="font-size: 1.0em;color: dodgerblue;">' . number_format( $value->cloudCover * 100, 0 ) . '</span>% | ' .
			'Visibility <span style="font-size: 1.0em;color: dodgerblue;">' . number_format( $value->visibility, 1 ) . '</span>km' .
			'</span>' .
			'</span>' .
			'</span>' .
            '</dshr' . $id_suffix . '>';

        }
    }

	date_default_timezone_set( $local_timezone );

	////////////////////////////////////////////////////


		$return_data['rc'] = '1';
		$return_data['errmsg'] = 'DarkSky responded...';
		return $return_data;
// END od DarkSky API

// invalid source		
	} else {
		$return_data['rc'] = '0';
		$return_data['errmsg'] = 'Invalid source';
		return $return_data;
	}
	
} // get_weather_data

function get_lat_lon ( $city_id ) {

	$return_data = array();

	# get latitude longitude of a city
	$sql = "SELECT * FROM tbl_cities WHERE id = ?";
	$params = array( $city_id ); //if no params then must be "array()", otherwise values comma separated
	$pdo_return_data = pdo_db_call ( __FUNCTION__, 1, '', $sql, $params, 'fetch', 2 );
	if ( $pdo_return_data['rc'] == '1' ) {
		if ( !$pdo_result = $pdo_return_data['result'] ) { // null returned - no match
			$return_data['result'] = '';
		} else {
         $work_array = $pdo_result;
         foreach ( $work_array as $key => $data ) {
            # get country name
            $country_name_result = get_country_name( $data['country'] );
            switch ( $country_name_result['rc'] ) {
               case "1":
                  $work_array[$key]['country_name'] = $country_name_result['country_name'];
                  break;
               case "0":
               case "2":
               default:
                  $work_array[$key]['country_name'] = $data['country'];
            }  
         } //foreach

			$return_data['result'] = $work_array;
		}
		$return_data['rc'] = '1';
	} else {
		$return_data['rc'] = '0';
		$return_data['errmsg'] = 'System Error.\nCould not retrieve lat-lon data.';
	}

	return $return_data;

} // get_lat_lon

function convert_wind_direction ( $wind_direction ) {
	if ( $wind_direction > 350 || $wind_direction < 10 ) {
		$win_dir_desc = "N";
	} elseif ( $wind_direction >= 10 && $wind_direction <= 35 ) {
		$win_dir_desc = "NNE";
	} elseif ( $wind_direction > 35 && $wind_direction <= 55 ) {
		$win_dir_desc = "NE";
	} elseif ( $wind_direction > 55 && $wind_direction <= 80 ) {
		$win_dir_desc = "ENE";
	} elseif ( $wind_direction > 80 && $wind_direction <= 100 ) {
		$win_dir_desc = "E";
	} elseif ( $wind_direction > 100 && $wind_direction <= 125 ) {
		$win_dir_desc = "ESE";
	} elseif ( $wind_direction > 125 && $wind_direction <= 145 ) {
		$win_dir_desc = "SE";
	} elseif ( $wind_direction > 145 && $wind_direction <= 170 ) {
		$win_dir_desc = "SSE";
	} elseif ( $wind_direction > 170 && $wind_direction <= 190 ) {
		$win_dir_desc = "S";
	} elseif ( $wind_direction > 190 && $wind_direction <= 215 ) {
		$win_dir_desc = "SSW";
	} elseif ( $wind_direction > 215 && $wind_direction <= 235 ) {
		$win_dir_desc = "SW";
	} elseif ( $wind_direction > 235 && $wind_direction <= 260 ) {
		$win_dir_desc = "WSW";
	} elseif ( $wind_direction > 260 && $wind_direction <= 280 ) {
		$win_dir_desc = "W";
	} elseif ( $wind_direction > 280 && $wind_direction <= 305 ) {
		$win_dir_desc = "WNW";
	} elseif ( $wind_direction > 305 && $wind_direction <= 325 ) {
		$win_dir_desc = "NW";
	} elseif ( $wind_direction > 325 && $wind_direction <= 350 ) {
		$win_dir_desc = "NNW";
	}

	return $win_dir_desc;

} // convert_wind_direction

function get_supported_countries () {
   $sql = "SELECT * FROM tbl_country WHERE newsapi_support = true ORDER BY country";
   $params = array(); //if no params then must be "array()", otherwise values comma separated
   $pdo_return_data = pdo_db_call ( __FUNCTION__, 1, '', $sql, $params, 'fetchAll', 2 );
   if ( $pdo_return_data['rc'] == '1' ) {
		if ( !$pdo_result = $pdo_return_data['result'] ) { // null returned - no match
			$return_data['rc'] = '2';
		} else {
			$return_data['rc'] = '1';
			$html = '<select id="hlinescsel" name="hlinescsel" style="height: 36px; color: dodgerblue" value="" title="The country you want to get headlines for. (optional)" placeholder="News country"><option value="">all countries</option>';
		
			foreach ( $pdo_result  as $key => $value ) {
				$html .= '<option value="' . strtolower( $value['a2_iso'] ) . '">' . ucwords( strtolower( $value['country'] ) ) . '</option>';
			}
			$html .= '</select>';
			$return_data['html'] = $html;
		}
   } else {
      $return_data['rc'] = '0';
      $return_data['errmsg'] = 'System Error.\nCould not retrieve country flag.';
   }

   return $return_data;

} // get_supported_countries

function get_news_headlines ( $post ) {

	$return_data = array();

	if ( $post['hlinesrcoption'] == '1') {
		$q = str_replace( ' ', ',', $post['hlinesqbox'] );
		$country = '';
		$category = '';
		$source = $post['hlinessourcesel'];
	} elseif ( $post['hlinesrcoption'] == '0') {
		$q = str_replace( ' ', ',', $post['hlinesqbox'] );
		$country = $post['hlinescsel'];
		$category = $post['hlinesctgsel'];
		$source = '';
		if ( $q == '' && $country == '' && $category == '' ) {
			$category = 'general';
		}
	} else {
		$return_data['rc'] = '0';
		$return_data['errmsg'] = 'Something went wrong...';
	}
   
	$url = 'https://newsapi.org/v2/top-headlines?apiKey=' . API_KEY_NEWSAPI . '&q=' . $q . '&country=' . $country . '&category=' . $category . '&sources=' . $source;
	if ( !$api_response = file_get_contents( $url ) ) {
		$return_data['rc'] = '0';
		$return_data['errmsg'] = $url;
	} else {
		$return_data['response'] = json_decode( $api_response );
		if ( $return_data['response']->status = 'ok' ) {

			foreach ( $return_data['response']->articles as $key => $value ) {
				$return_data['response']->articles[$key]->time_published = date ( 'j M Y, g:ia', strtotime( $value->publishedAt ) );
			}

			$return_data['rc'] = '1';
			$return_data['errmsg'] = 'Data retrieved successfully';

		} elseif ( $return_data['response']->status = 'error' ) {
			$return_data['rc'] = '0';
			$return_data['errmsg'] = $return_data['response']->message;
		} else {
			$return_data['rc'] = '0';
			$return_data['errmsg'] = 'Unknown error';
		}


    }
   
	return $return_data;
} // get_news_headlines

?>