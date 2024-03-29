<?php
# lib/functions.php

# ########################################################################################
	
# This is executed on every page load - show errors - set DEVMODE to TRUE or FALSE in settings.php

	ini_set( 'short_open_tag', true );
	$devmode = DEVMODE;
	if ( $devmode ) {
	    ini_set( 'display_errors', true ); /*change to false in production mode */
	    /* error_reporting(E_ALL); */
	    error_reporting( E_ALL & ~E_NOTICE & ~E_WARNING );
	} else { 
	    error_reporting( E_ALL ^E_NOTICE ^E_WARNING );
	}

# ########################################################################################

# This is executed on every page load - connect to the database
/*
	if ( !$db ) {
		$database_connection_attempt_result = pdo_db_connect();
		if ( $database_connection_attempt_result['rc'] == '1' ) {
			$db = $database_connection_attempt_result['db'];
		} else {
			die ( '1Could not create database connection: ' . $database_connection_attempt_result['db'] );
		}
	} //if ( !$db )
*/	

/* Function pdo_db_connect - creates a PDO object representing database connection
 * Takes no arguments - constants used must be populated prior to call
 * Returns array with return code ('rc') and either database connection or PDO error message ('db')
 */
	
	function pdo_db_connect () {
		$db = null;
		$return_data = array();
		try {
			# Connect to database
			$db = new PDO('mysql:host='.HOSTNAME.';dbname='.DATABASE.';charset=utf8', DBUSER, DBPASS);
	
			# If there is any connection error, it will throw a PDOException object that can be cached to handle Exception further.
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	
			# turn off prepare emulation which is enabled in the MySQL driver by default, but prepare emulation should be turned off to use PDO safely
			$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	
			$return_data['db'] = $db;
			$return_data['rc'] = '1';
		} # /try
	
		catch (PDOException $ex) {
			$return_data['db'] = $ex->getMessage();
			$return_data['rc'] = '0';
		}
		return $return_data;
	} //pdo_db_connect ()
	
# ########################################################################################
	
/* Function pdo_db_call
 * for SELECT returns associative array with result (or null if none)
 * for INSERT / UPDATE / DELETE returns number of affected rows 
 * $php_self is the name of the calling script
 * $call_id is the consecutive number of pdo_db_call in a given script - unique within script - for id purpose in case of error
 * $db is database connection - must exist prior to calling
 * $sql is the query to be executed with '?' placeholders replaced by $params
 * $params is an array (values must be comma separated & order matters as per $sql)
 * $method_name is the method of call. Allowed methods are:
 * 'fetch' - use $call_style = 2 or 4
 * 'fetchAll' - $call_style = 2 or 4
 * 'rowCount' - $call_style = ''
 * $call_style:
 * case '': $result = $stmt-> $method_name (  ); - for INSERT / UPDATE / DELETE
 * case 1: 	$result = $stmt-> $method_name ( PDO::FETCH_LAZY );
 * case 2: 	$result = $stmt-> $method_name ( PDO::FETCH_ASSOC );
 * case 3: 	$result = $stmt-> $method_name ( PDO::FETCH_NUM );
 * case 4: 	$result = $stmt-> $method_name ( PDO::FETCH_BOTH );
 * case 5: 	$result = $stmt-> $method_name ( PDO::FETCH_OBJ );
 * case 11: $result = $stmt-> $method_name ( PDO::FETCH_NAMED );
 */
	
	function pdo_db_call ( $php_self = '', $call_id = 0, $db = null, $sql = '', $params = '', $method_name = '', $call_style = '' ) {
		$valid_method = FALSE;
		$allowed_methods = array ( 'fetch','fetchAll','rowCount' );
		foreach ( $allowed_methods as $key => $value ) {
			if ( $method_name == $value ) {
				$valid_method = TRUE;
				break;
			}
		}
	
		$return_data = array();
		if ( $valid_method ) {
			if ( !$db ) {
				$db_connect_result = pdo_db_connect ();
				if ( $db_connect_result['rc'] == '1' ) {
					$db = $db_connect_result['db'];
				} else {
					$return_data['rc'] = '0';
					$return_data['result'] = 'Could not create database connection: ' . $db_connect_result['db'];
					return $return_data;
				}
			} //!$db
	
			try {
				$stmt = $db->prepare( $sql );
				$stmt->execute( $params );
				$result = $stmt-> $method_name ( $call_style );
				$return_data['result'] = $result;
				$return_data['rc'] = '1';
			} //try
			catch (PDOException $ex) {
				log_pdo_db_error ( $php_self, $call_id, $db, $sql, $params, $method_name, $call_style, $ex->getMessage() );
				$return_data['result'] = 'Your request could not be completed due to system error.<br>Please contact Support if problem persists.';
				$return_data['rc'] = '0';
			} //catch
		} else {
			$return_data['result'] = 'Invalid call method: ' . $method_name;
			$return_data['rc'] = '0';
		}
		
		$stmt->closeCursor();
		$db = null;
		return $return_data;
	} //pdo_db_call ()
	
# ########################################################################################
/*  Function log_pdo_db_error
 * 
 * 
 */	
	
	function log_pdo_db_error ( $php_self = '', $call_id = 0, $db = null, $sql = '' , $params = '' , $method_name = '', $call_style = '', $error = '' ) {
		if ( !$db ) {
			$db_connect_result = pdo_db_connect ();
			if ( $db_connect_result['rc'] == '1' ) {
				$db = $db_connect_result['db'];
			} else {
				return true;
			}
		} //!$db
		
		$params = serialize( $params );
		$error_sql = "INSERT INTO tbl_db_errors ( php_self, call_id, sql_query, params, method_name, call_style, member_id, error ) VALUES ( ?,?,?,?,?,?,?,? )";
		$error_params = array( $php_self, $call_id, $sql, $params, $method_name, $call_style, NULL, $error );
		$stmt = $db->prepare( $error_sql );
		$stmt->execute( $error_params );
		$result = $stmt-> rowCount();
		return true;
	} // log_pdo_db_error ()
	
# ########################################################################################
	
?>	