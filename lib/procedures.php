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
	$pdo_return_data = pdo_db_call ( __FUNCTION__, 1, $db, $sql, $params, 'fetch', 2 );
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
		$pdo_return_data = pdo_db_call ( __FUNCTION__, 2, $db, $sql, $params, 'fetch', 2 );
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
	$pdo_return_data = pdo_db_call ( __FUNCTION__, 3, $db, $sql, $params, 'rowCount', '' );
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
	$pdo_return_data = pdo_db_call ( __FUNCTION__, 1, $db, $sql, $params, 'fetch', 2 );
	if ( $pdo_return_data['rc'] == '1' ) {
		if ( $pdo_result = $pdo_return_data['result'] ) { // email exists

			// generate random temporary string
			$temporary_random_string = md5(uniqid(time(), TRUE));
				
			$sql = "UPDATE tbl_member SET temporary_random_string = ?, random_string_active = ?, random_string_expiry = ? WHERE email = ?";
			$params = array( $temporary_random_string, TRUE, (time() + 86400), $post['fpassemail'] );
			$pdo_return_data = pdo_db_call ( __FUNCTION__, 2, $db, $sql, $params, 'rowCount', '' );
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
	$pdo_return_data = pdo_db_call ( __FUNCTION__, 1, $db, $sql, $params, 'fetch', 2 );
	if ( $pdo_return_data['rc'] == '1' ) {
		if ( $pdo_result = $pdo_return_data['result'] ) { // row returned - match
			# hash new password & store in database
			if ( $new_hash = password_hash( $post['resetpassnew'], PASSWORD_DEFAULT ) ) { # password successfully hashed
				# store new hash in db
				$sql = "UPDATE tbl_member SET password = ? WHERE id = ?";
				$params = array( $new_hash, $pdo_result['id'] );
				$pdo_return_data = pdo_db_call ( __FUNCTION__, 2, $db, $sql, $params, 'rowCount', '' );
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
	$pdo_return_data = pdo_db_call ( __FUNCTION__, 1, $db, $sql, $params, 'fetchAll', 4 );
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
			$pdo_return_data = pdo_db_call ( __FUNCTION__, 2, $db, $sql, $params, 'rowCount', '' );
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
	$pdo_return_data = pdo_db_call ( __FUNCTION__, 3, $db, $sql, $params, 'rowCount', '' );
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
	$pdo_return_data = pdo_db_call ( __FUNCTION__, 1, $db, $sql, $params, 'fetch', 2 );
	if ( $pdo_return_data['rc'] == '1' ) {
		if ( $pdo_result = $pdo_return_data['result'] ) { // row returned - match
			if ( password_verify( $post['passwordcpass'], $pdo_result['password'] ) ) { # current password valid
				# hash new password & store in database
				if ( $new_hash = password_hash( $post['passwordnew'], PASSWORD_DEFAULT ) ) { # password successfully hashed
					# store new hash in db
					$sql = "UPDATE tbl_member SET password = ? WHERE session_token = ?";
					$params = array( $new_hash, $_COOKIE['session_token'] );
					$pdo_return_data = pdo_db_call ( __FUNCTION__, 2, $db, $sql, $params, 'rowCount', '' );
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
	$pdo_return_data = pdo_db_call ( __FUNCTION__, 1, $db, $sql, $params, 'rowCount', '' );
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
	$pdo_return_data = pdo_db_call ( __FUNCTION__, 1, $db, $sql, $params, 'fetch', 2 );
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


?>