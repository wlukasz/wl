<?php
# lib/settings.php

	# establish session
	session_start();

	# set default timezone
	date_default_timezone_set('Australia/Brisbane');
	
# SYSTEM CONSTANTS & VARIABLES

define ( "VERSION", "wl_0.0.1" );													// - system release version
define ( "HOME_PAGE", "index.php" );											
define ( "REGO_FORM_ACTION_PAGE", "back_end/process_member_registration_step_2.php" );											
define ( "FORGOT_PASSWORD_ACTION_PAGE", "back_end/process_forgot_password_step_2.php" );											

# different settings are required for different environments
switch (ENVIRONMENT) {
    case "DEVL":
		define ( "DEVMODE", TRUE );					// - error handling
		define ( "HTTP_HOST", "localhost" );		// - our server name
		define ( "TRANSFER_PROTOCOL", "https://" );										// -
		define ( "APPLICATION_FOLDER", "/wl" );		// - where our pages reside on the server
		define ( "DBPREFIX", "" );					// - db prefix - would you like to use a prefix for your table?
		define ( "ADMIN_EMAIL", "wlukasz@localhost" );	// - what email should we use to contact our members?
		break;
    case "TEST":
		define ( "DEVMODE", FALSE );				// - error handling
		define ( "HTTP_HOST", "peterlandlord.com.au" );		// - our server name
		define ( "TRANSFER_PROTOCOL", "https://" );										// -
		define ( "APPLICATION_FOLDER", "/test/wl" );	// - where our pages reside on the server
 		define ( "DBPREFIX", "" );					// - db prefix - would you like to use a prefix for your table?
		define ( "ADMIN_EMAIL", "support@peterlandlord.com.au" );	// - what email should we use to contact our members?
		break;
    case "PROD":
		define ( "DEVMODE", FALSE );					// - error handling
		define ( "HTTP_HOST", "peterlandlord.com.au" );		// - our server name
		define ( "TRANSFER_PROTOCOL", "https://" );										// -
		define ( "APPLICATION_FOLDER", "/wl" );		// - where our pages reside on the server
		define ( "DBPREFIX", "" );					// - db prefix - would you like to use a prefix for your table?
		define ( "ADMIN_EMAIL", "support@peterlandlord.com.au" );	// - what email should we use to contact our members?
    	break;
    default:
    	echo "Invalid ENVIRONMENT constant in environment.php: " . ENVIRONMENT . " - Must be DEVL, TEST or PROD";
    	die;
}
?>