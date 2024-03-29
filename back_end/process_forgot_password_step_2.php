<?php 
# backend/process_forgot_password_step_2.php

	# Config -------------------------------------------------------------------
	require_once ( dirname( $_SERVER['DOCUMENT_ROOT'] ) . "/wl_lib/config.php" );
	# --------------------------------------------------------------------------

	if ( isset( $_GET['trs'] ) ) {
		$trs = $_GET['trs'];
		$sql = "SELECT * FROM tbl_member WHERE temporary_random_string	= ? AND random_string_active = ?";
		$params = array( $trs, TRUE ); //if no params then must be "array()", otherwise values comma separated
		$pdo_return_data = pdo_db_call ( $_SERVER['PHP_SELF'], 1, $db, $sql, $params, 'fetch', 2 );
		if ( $pdo_return_data['rc'] == '1' ) { //query executed ok
			if ( $member_data = $pdo_return_data['result'] ) { // row returned ok
				if ( $member_data['random_string_expiry'] > time() ) { // string not expired
					// all ok - deactivate random string
					$sql = "UPDATE tbl_member SET random_string_active = FALSE WHERE id = ?";
					$params = array( $member_data['id'] ); //if no params then must be "array()", otherwise values comma separated
					$pdo_return_data = pdo_db_call ( $_SERVER['PHP_SELF'], 2, $db, $sql, $params, 'rowCount', '' );
					if ( $pdo_return_data['rc'] == '1' ) { //trs deactivated - All Systems Go!
						$redirect_url = TRANSFER_PROTOCOL . $_SERVER['HTTP_HOST'] . APPLICATION_FOLDER . '/' . HOME_PAGE . '?fps=1&trs=' . $trs;
					} else { //db error
						$redirect_url = TRANSFER_PROTOCOL . $_SERVER['HTTP_HOST'] . APPLICATION_FOLDER . '/' . HOME_PAGE . '?fpsmsg=' . $pdo_return_data['result'];
					}
				} else { // string expired
					$redirect_url = TRANSFER_PROTOCOL . $_SERVER['HTTP_HOST'] . APPLICATION_FOLDER . '/' . HOME_PAGE . '?fpsmsg=3';
				}
			} else { // no match to 'trs'
				$redirect_url = TRANSFER_PROTOCOL . $_SERVER['HTTP_HOST'] . APPLICATION_FOLDER . '/' . HOME_PAGE . '?fpsmsg=2';
			}
		} else { //db error
			$redirect_url = TRANSFER_PROTOCOL . $_SERVER['HTTP_HOST'] . APPLICATION_FOLDER . '/' . HOME_PAGE . '?fpsmsg=' . $pdo_return_data['result'];
		}
	} else { // missing $_GET['trs']
		$redirect_url = TRANSFER_PROTOCOL . $_SERVER['HTTP_HOST'] . APPLICATION_FOLDER . '/' . HOME_PAGE . '?fpsmsg=1';
	}
	
	header ( "Location: " . $redirect_url ); 
	exit;

?>