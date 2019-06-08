<?php
# index.php

	# Config -------------------------------------------------------------------
	require_once ( dirname( $_SERVER['DOCUMENT_ROOT'] ) . "/wl_lib/config.php" );
	# --------------------------------------------------------------------------

###########
# ajax here

	if ( isset( $_POST['ajax'] ) && $_POST['ajax'] == $_SERVER['PHP_SELF'] ) {
		unset($_POST['ajax']);
	
		$return_array = array();
		//check if this is an ajax request
		if ( !isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) ) {
			$return_array['rc'] = "0";
			$return_array['errmsg'] = "This is not AJAX request";
			$return_array['action'] = $_POST['action'];
			die ($return_data = json_encode($return_array));
		}
		
		if ( isset( $_POST['antibot'] ) && $_POST['antibot'] > '' ) {
			$return_array['rc'] = "0";
			$return_array['errmsg'] = "Data Security Violation!<br>Unauthorized Data Alteration Detected.<br>Please contact Support if you think it is an error.";
			$return_array['action'] = $_POST['action'];
			die ($return_data = json_encode($return_array));
		}
		
		switch ( $_POST['action'] ) {
			case "join":
				$return_array = process_member_registration_step_1 ( $_POST );
				break;
			case "resetpass":
				$return_array = reset_password ( $_POST );
				break;
			case "login":
				process_member_logout ();
				$return_array = process_member_login ( $_POST );
				break;
			case "cpass":
				$return_array = change_password ( $_POST );
				break;
			case "fpass":
				$return_array = forgot_password_step_1 ( $_POST );
				break;
			case "prof":
				$return_array = update_user_details ( $_POST );
				break;
			case "logout":
				$return_array = process_member_logout ();
				break;
			case "userdetails":
				$return_array = get_user_details_php ();
				break;
			default:
				$return_array['rc'] = "0";
				$return_array['errmsg'] = "Invalid Request! " . $_POST['action'];
				break;
		}
		
		$return_array['action'] = $_POST['action'];
		die ($return_data = json_encode($return_array));
		
		exit; // NEVER remove this EXIT!
	}

# end-of-ajax
#############


?>
<!doctype html>
<html>
<head>
	<title>index</title>
    <meta charset="utf-8">
<!--     <meta name="viewport" content="width=device-width, initial-scale=1">
 -->    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/> 
	<?php include("inc/css.inc"); ?>
<style>
	/* grid definitions */

.grid {
	display: grid;
	grid-template-columns: repeat( 15, 1fr );
	grid-template-rows: repeat( 1, 1fr );
	grid-gap: 1px;
}

/* logo */
.grid div:nth-child( 1 ) { 
	grid-column: 1 / 6;
	grid-row: 1;
}

/* top banner */
.grid div:nth-child( 2 ) {
	grid-column: 6 / -1;
	grid-row: 1;
}

/* menu bar */
.grid div:nth-child( 3 ) {
	grid-column: 1 / -1;
	grid-row: 2;
}

/* main */
.grid div:nth-child( 4 ) {
	grid-column: 1 / 11;
	grid-row: 3;
}
/* right hand ad pane */
.grid div:nth-child( 5 ) {
	grid-column: 11 / -1;
	grid-row: 3;
}

/* footer */
.grid div:nth-child( 6 ) {
	grid-column: 1 / -1;
	grid-row: 4;
}

/* @media( min-width: 640px ) {
	.grid {
	}
} */

	/* end of grid definitions */

.userlinks:hover {
	background-color: WhiteSmoke;
	color: red;
}

div.formfields {
	margin: 0 0 0 2em; 
}

.showpass {
	vertical-align: middle;
	cursor: pointer;
	opacity: 0.0;
}

#paralax {
	font-family: sans-serif;
	text-decoration: none; 
	font-size: 0.8em;
	color: black;
}

</style>
	<?php include("inc/js.inc"); ?>
<script type="text/javascript">
	
	var objAntiBot;
	var objOutput;
		
	$( document ).ready( function() {
//		$( '.submit-btn' ).prop( 'disabled', true );

		// init
		<?php if ( isset( $_COOKIE['session_token'] ) ) { ?>
			$( '.loggedout' ).hide();
			get_user_details();
		<?php } else { ?>
			$( '.loggedin' ).hide();
			$( '#userbizotitle' ).html( "User's Business" );
		<?php } ?>

		<?php if ( isset( $_GET['reg'] ) ) { ?>
			$( '#'+VisibleContentID ).css( 'display' , 'none' );
			VisibleContentID = 'userlogin-content';
			$( '#'+VisibleContentID ).css( 'display' , 'block' );
			alert ('Registration request confirmed.\nYou may Log In now.');
		<?php } ?>

		<?php if ( isset( $_GET['regmsg'] ) ) { ?>
			var regMsg = "<?php echo $_GET['regmsg']; ?>";
			switch ( regMsg ) {
				case "1":
					alert ('Security string missing.\nPlease use link as emailed to you');
					break;
				case "2":
					alert ('Security string cannot be matched.\nPlease use link as emailed to you');
					break;
				case "3":
					alert ('Security string expired.\nPlease send request again and confirm within 24hrs.');
					break;
				default:
					alert ('<?php echo $_GET['regmsg']; ?>');
					break;
			}
		<?php } ?>

		<?php if ( isset( $_GET['fps'] ) ) { ?>
			$( '#'+VisibleContentID ).css( 'display' , 'none' );
			VisibleContentID = 'resetpass-content';
			$( '#'+VisibleContentID ).css( 'display' , 'block' );
			alert ('Password Reset request confirmed.\nReset your Password now.');
		<?php } ?>

		<?php if ( isset( $_GET['fpsmsg'] ) ) { ?>
			var fpsMsg = "<?php echo $_GET['fpsmsg']; ?>";
			switch ( fpsMsg ) {
				case "1":
					alert ('Security string missing.\nPlease use link as emailed to you.');
					break;
				case "2":
					alert ('No active request can be found.\nPlease send a new request.');
					break;
				case "3":
					alert ('Security string expired.\nPlease send request again and confirm within 24hrs.');
					break;
				default:
					alert ('<?php echo $_GET['fpsmsg']; ?>');
					break;
			}
		<?php } ?>
		
		$( '.userlinks' ).on( 'click', function() {
			slide_up_topmenu ();
			menuToggledBy = 'dummymenuToggledBy';
			if ( this.id == 'userlogout' ) {
				var callData = "ajax=<?php echo $_SERVER['PHP_SELF'];?>&action=logout";
				$.ajax({ // ### AJAX this is to do database business ###
				    url:"<?php echo $_SERVER['PHP_SELF'];?>",
				    type:"POST",
				    data : callData,
				    success:function(msg){
					    msg = JSON.parse(msg);
						$( '.loggedout' ).show();
						$( '.loggedin' ).hide();
						$( '#fnameprof' ).val( '' );
						$( '#lnameprof' ).val( '' );
						$( '#emailprof' ).val( '' );
						$( '#usernameprof' ).val( '' );
						$( '#userbizotitle' ).html( "User's Business" );

						$( '#'+VisibleContentID ).css( 'display' , 'none' );
						VisibleContentID = 'homecontents';
						$( '#'+VisibleContentID ).css( 'display' , 'block' );
						if ( topMenuDown === true ) {
							slide_up_topmenu ();
						}
						
						alert(msg.errmsg);
				    },
				    error: function (jqXHR, textStatus, errorThrown) {
						alert("Network Error! "+msg.errmsg);
				    },
				});  // ### AJAX this is to do database business ###
			} else if ( this.id == 'userguest' ) {
				/* do nothing */
			} else {
				$( '#'+VisibleContentID ).css( 'display' , 'none' );
				VisibleContentID = this.id+'-content';
				$( '#'+VisibleContentID ).css( 'display' , 'block' );
			}
    	});
		
		$( '#fpq' ).on( 'click', function() {
			$( '#'+VisibleContentID ).css( 'display' , 'none' );
			VisibleContentID = 'userfpass-content';
			$( '#'+VisibleContentID ).css( 'display' , 'block' );
    	});
		
		/* show password to user */
		$( '.showpass' ).on( 'mousedown touchstart', function() {
		    $( this ).prev().attr( 'type', 'text' );
		}).on( 'mouseup touchend', function() {
		    $( this ).prev().attr( 'type', 'password' );
		}).on( 'mouseout touchmove',function(){
		    $( this ).prev().attr( 'type', 'password' );
		});

		/* validate password repeat */
		$( '.pass' ).on( 'input', function() {
			var PasswordID;
			var PasswordRepeatID;
			var SubmitButtonID;
			if ( $( this ).hasClass( 'join' ) ) {
				PasswordID = 'passwordjoin';
				PasswordRepeatID = 'passwordrepeatjoin';
				SubmitButtonID = 'submitjoin';
			} else if ( $( this ).hasClass( 'change' ) ) {
				PasswordID = 'passwordnew';
				PasswordRepeatID = 'passwordrepeatnew';
				SubmitButtonID = 'submitcpass';
			} else if ( $( this ).hasClass( 'reset' ) ) {
				PasswordID = 'resetpassnew';
				PasswordRepeatID = 'resetpassrepeatnew';
				SubmitButtonID = 'submitresetpass';
			} else if ( $( this ).hasClass( 'login' ) ) {
				if ( this.value > '' ) {
					$( '.showpass' ).css( 'opacity', '0.5' );
				} else {
					$( '.showpass' ).css( 'opacity', '0.0' );
				}
				return; // this is to skip execution below for repeat password comparison
			} else {
				$( '.showpass' ).css( 'opacity', '0.0' );
			}

			if ( $( '#'+PasswordRepeatID ).val() > '' ) {
				$( '.showpass' ).css( 'opacity', '0.5');
				if ( $( '#'+PasswordRepeatID ).val() != $( '#'+PasswordID ).val() ) {
					$( '#'+PasswordRepeatID ).css( 'color', 'red' );
					$( '#'+SubmitButtonID ).prop( 'disabled', true );
				} else {
					$( '#'+PasswordRepeatID ).css( 'color', 'RoyalBlue' );
					$( '#'+SubmitButtonID ).prop( 'disabled', false );
				}
			} else {
				$( '.showpass' ).css( 'opacity', '0.0' );
			}
		});
		
		/* submit form - AJAX call and functions */
		$( '.user-biznes-form' ).submit( function() {
			objAntiBot = document.getElementById(this.id).elements["antibot"]; // this gets an object by name attribute, here name="antibot"
			objOutput = document.getElementById(this.id).elements["output"]; // this gets an object by name attribute, here name="output"
			$('.submit-btn').hide(); //hide submit button
			$('.loading-img').show(); //show gif loader
			var msg;			
			var options = { 
				target: msg ,   // target element(s) to be updated with server response 
				beforeSubmit: beforeSubmit ,  // pre-submit callback 
				success: function( msg ) {
					msg = JSON.parse( msg );
					
			    	user_biznes_form_completion( msg );
					$('#'+objOutput.id).delay( 15000 ).fadeOut(); //hide output div
					$( '.showpass' ).css( 'opacity', '0.0');
					$( '.forminputs' ).each( function() {
						$( this ).val( '' ); // clear field
						placeholder_reinstate ( this );
					});
					
					if ( msg.rc == "1" && ( msg.action == "login" || msg.action == "prof" ) ) {
						$( '.loggedout' ).hide();
						$( '.loggedin' ).show();
						get_user_details();
					}	
					
					if ( msg.rc == "1" && msg.action == "resetpass" ) {
						$( '#'+VisibleContentID ).css( 'display' , 'none' );
						VisibleContentID = 'userlogin-content';
						$( '#'+VisibleContentID ).css( 'display' , 'block' );
						alert ('Password Reset Successful.\nYou may Log In now.');
					}	
				},
			    error: function ( jqXHR, textStatus, errorThrown ) {
				    msg.rc = "0";
				    msg.errmsg = "Network Error - Please try again";
			    	user_biznes_form_completion( msg );
			    },
//				resetForm: true        // reset the form after successful submit 
			}; 
			$( this ).ajaxSubmit( options );  			
			return false; // always return false to prevent standard browser submit and page navigation 
		});

		function beforeSubmit() {

			if ( objAntiBot.value > '' ) {
				$('.loading-img').hide(); //hide ajax spinner
				$('.submit-btn').show(); //show submit button
				$('#'+objOutput.id).css( 'color', 'red' );
				var errmsg = 'Data Security Violation! Unauthorized Data Alteration Detected.<br>Please contact Support if you think it is an error.';
				$('#'+objOutput.id).html( errmsg ); //set the message
				$('#'+objOutput.id).show(); // show  output div
				return false;
			}
			return true;
		}

		function user_biznes_form_completion( msg ) {
			$('.loading-img').hide(); //hide ajax spinner
			$('.submit-btn').show(); //show submit button
			if ( msg.rc == "0" ) {
				$('#'+objOutput.id).css( 'color', 'red' );
			} else {
				$('#'+objOutput.id).css( 'color', 'RoyalBlue' );
			}	
			$('#'+objOutput.id).html( msg.errmsg ); //set the message
			$('#'+objOutput.id).show(); // show  output div
			return true;
		}

		function get_user_details() {
			var callData = "ajax=<?php echo $_SERVER['PHP_SELF'];?>&action=userdetails";
			$.ajax({ // ### AJAX this is to do database business ###
			    url:"<?php echo $_SERVER['PHP_SELF'];?>",
			    type:"POST",
			    data : callData,
			    success:function(msg){
				    msg = JSON.parse(msg);
					if ( msg.rc == "1" ) {
						$( '#fnameprof' ).val( msg.result['first_name'] );
						$( '#lnameprof' ).val( msg.result['last_name'] );
						$( '#emailprof' ).val( msg.result['email'] );
						$( '#usernameprof' ).val( msg.result['username'] );
						$( '#userbizotitle' ).html( msg.result['first_name']+"'s Business" );
					} else {
						alert ( msg.errmsg );
					}
			    },
			    error: function (jqXHR, textStatus, errorThrown) {
					alert("Network Error! "+msg.errmsg);
			    },
			});  // ### AJAX this is to do database business ###
		}
		
	}); // document ready
	
</script>
</head>
<body id="thebody">
<!-- index-page -->
	<div data-role="page" id="index-page" class="grid">
		<div style="height: 95px;background: url('img/logo.jpg');background-size: cover;"></div>
		<div style="height: 95px;background: url('img/top-banner.jpg');background-size: cover;"></div>
		<!-- navbar menu -->
		<div id="navbar">
      	
			<div id="homeicon" class="divtopmenuicons inlineblock" title="Home">
				<img class="topmenuicon" src="img/home25x25.png" alt="">
			</div>

			<div id="wordcrossyicon" class="divtopmenuicons inlineblock" title="Word Crossy">
				<a href="word_crossy.php"><img class="topmenuicon" src="img/wc.png" alt=""></a>
			</div>

			<div id="paralax" class="divtopmenuicons inlineblock" title="Parallax">
				<a href="paralax.php">Parallax</a>
			</div>
	
			<ol id="topmenuolist" class="inlineblock">
				<!-- all menu titles must have ascending numeric values -->
				<li id="topmenutitle2" class="topmenutitles" value="2">2and Awayand Awayand Awayand Away</li>
				<li id="topmenutitle3" class="topmenutitles" value="3">3and Backand Backand Backand</li>
				<li id="topmenutitle4" class="topmenutitles" value="4">4and Backand Backand Backand</li>
				<li id="topmenutitle25" class="topmenutitles" value="25"><div id="userbizotitle"></div></li>
				
			</ol> <!-- topmenuolist -->
		</div> <!-- navbar -->
		<!-- main contents container - hidden divs - shown dynamically by javascript -->
		<div id="maincontentscontainer" class="mainbodycontainers">
			
			<div id="homecontents" class="contentscontainers">
				<p>
					Skip to main content Australian Government - Bureau of Meteorology Search Enter search terms HOMEABOUTMEDIACONTACTS NSW NSW Weather &amp; Warnings Warnings Summary Forecasts Sydney Forecast NSW Forecast Area Map Observations Sydney Observations All NSW Observations Rainfall &amp; River Conditions
				</p>
			</div> <!-- homecontents -->

			<div id="userjoin-content" class="contentscontainers" style="background: url('img/user-join.png');background-size: cover;">
				<div class="inlineblock"><img src="img/user-join.jpg" alt="Join us..."></div>
				<div class="inlineblock" style="vertical-align: top;height: 100%;font-size: 1.3em;line-height: 2.1em;">
					<div style="margin: 0 0 0 2em;"><p style="float:none; width: 100%;font-family: Arial;">Join Us</p></div>
					<form id="join-form" class="user-biznes-form" action="<?php echo $_SERVER['PHP_SELF'];?>" method="post" enctype="multipart/form-data">
						<input type="hidden" id="antibot-join" name="antibot" value="" />
						<input type="hidden" id="ajax-join" name="ajax" value="<?php echo $_SERVER['PHP_SELF']?>" />
						<input type="hidden" id="action-join" name="action" value="join" />
						<div class="formfields">
							<div class="inlineblock input-container">
								<span id="fnamejoin-label" class="textbox-label">First Name</span>
								<input id="fnamejoin" name="fnamejoin" class="forminputs" type="text" value="" placeholder="First Name" autocomplete="off" data-messageless="true" required>
								<img src="img/dummy30x30.png">
							</div>
							<div class="inlineblock input-container">
								<span id="lnamejoin-label" class="textbox-label">Last Name</span>
								<input id="lnamejoin" name="lnamejoin" class="forminputs" type="text" value="" placeholder="Last Name" autocomplete="off" data-messageless="true" required>
							</div>
							<br>
							<div class="inlineblock input-container">
								<span id="emailjoin-label" class="textbox-label">Email</span>
								<input id="emailjoin" name="emailjoin" class="forminputs" type="email" value="" placeholder="Email" autocomplete="off" data-messageless="true" required>
								<img src="img/dummy30x30.png">
							</div>
							<div class="inlineblock input-container">
								<span id="usernamejoin-label" class="textbox-label">Username, optional, for login</span>	
								<input id="usernamejoin" name="usernamejoin" class="forminputs" type="text" value="" placeholder="Username" autocomplete="off" data-messageless="true">
							</div>
							<br>
							<div class="inlineblock input-container">	
								<span id="passwordjoin-label" class="textbox-label">Password</span>	
								<input id="passwordjoin" name="passwordjoin" class="forminputs pass join" type="password" placeholder="Password" data-messageless="true" required>
								<img src="img/eye-30x30.png" class="showpass">
							</div>
							<div class="inlineblock input-container">	
								<span id="passwordrepeatjoin-label" class="textbox-label">Repeat password</span>	
								<input id="passwordrepeatjoin" name="passwordrepeatjoin" class="forminputs pass join" type="password" placeholder="Repeat password" data-messageless="true" required>
								<img src="img/eye-30x30.png" class="showpass">
							</div>
							
							<div class="input-container">	
								<input id="submitjoin" name="submitjoin" class="submit-btn" type="submit" style="margin: 25px 0 0 0;width: 92%;" value="Join!" />
							</div>
							<img src="img/ajax-loader.gif" class="loading-img" alt="Please Wait..."/>
							<output id="output-join" name="output" style="color: RoyalBlue;font-size: 0.7em;font-family: Arial;"></output>
						</div> <!-- formfields -->
					</form>
				</div>
			</div> <!-- userjoin -->

			<div id="userlogin-content" class="contentscontainers" style="background: url('img/user-login.png');background-size: cover;">
				<div class="inlineblock"><img src="img/user-login.jpg" alt="Log In..."></div>
				<div class="inlineblock" style="vertical-align: top;font-size: 1.3em;line-height: 2.1em;">
					<div style="margin: 0 0 0 2em;"><p id="log-in-title" style="float:none; width: 100%;font-family: Arial;">Log In</p></div>
					<form id="login-form" class="user-biznes-form" action="<?php echo $_SERVER['PHP_SELF'];?>" method="post" enctype="multipart/form-data">
						<input type="hidden" id="antibot-login" name="antibot" value="" />
						<input type="hidden" id="ajax-login" name="ajax" value="<?php echo $_SERVER['PHP_SELF']?>" />
						<input type="hidden" id="action-login" name="action" value="login" />
						<div class="formfields">
							<div class="input-container">
								<span id="login-label" class="textbox-label">Email or Username</span>	
								<input id="login" name="login" class="forminputs" type="text" value="" placeholder="Email or Username" autocomplete="off" data-messageless="true" required>
							</div>
							
							<div class="input-container">	
								<span id="passwordlogin-label" class="textbox-label">Password</span>	
								<input id="passwordlogin" name="passwordlogin" class="forminputs pass login" type="password" placeholder="Password" data-messageless="true" required>
								<img src="img/eye-30x30.png" class="showpass">
								<a href="#" id="fpq" style="font-size: 0.55em; font-family: Arial; color: RoyalBlue; text-decoration:none;">Forgot password? Click here...</a>
							</div>
							
							<div class="input-container">	
								<input id="submitlogin" name="submitlogin" class="submit-btn" type="submit" style="margin: 25px 0 0 0;width: 100%;" value="Log in" />
							</div>
							<img src="img/ajax-loader.gif" class="loading-img" alt="Please Wait..."/>
							<output id="output-login" name="output" style="color: RoyalBlue;font-size: 0.7em;font-family: Arial;"></output>
						</div> <!-- formfields -->
					</form>
				</div>
			</div> <!-- userlogin -->

			<div id="resetpass-content" class="contentscontainers" style="background: url('img/user-cpass.png');background-size: cover;">
				<div class="inlineblock"><img src="img/user-cpass.jpg" alt="Reset Password..."></div>
				<div class="inlineblock" style="vertical-align: top;font-size: 1.3em;line-height: 2.1em;">
					<div style="margin: 0 0 0 2em;"><p style="float:none; width: 100%;font-family: Arial;">Reset Password</p></div>
					<form id="resetpass-form" class="user-biznes-form" action="<?php echo $_SERVER['PHP_SELF'];?>" method="post" enctype="multipart/form-data">
						<input type="hidden" id="antibot-resetpass" name="antibot" value="" />
						<input type="hidden" id="ajax-resetpass" name="ajax" value="<?php echo $_SERVER['PHP_SELF']?>" />
						<input type="hidden" id="action-resetpass" name="action" value="resetpass" />
						<input type="hidden" id="resetpass-trs" name="resetpass-trs" value="<?php echo $_GET['trs']; ?>" />
						<div class="formfields">
							
							<div class="input-container">	
								<span id="resetpassnew-label" class="textbox-label">New Password</span>	
								<input id="resetpassnew" name="resetpassnew" class="forminputs pass reset" type="password" placeholder="New Password" data-messageless="true" required>
								<img src="img/eye-30x30.png" class="showpass">
							</div>
							
							<div class="input-container">	
								<span id="resetpassrepeatnew-label" class="textbox-label">Repeat New Password</span>	
								<input id="resetpassrepeatnew" name="resetpassrepeatnew" class="forminputs pass reset" type="password" placeholder="Repeat New Password" data-messageless="true" required>
								<img src="img/eye-30x30.png" class="showpass">
							</div>
							
							<div class="input-container">	
								<input id="submitresetpass" name="submitresetpass" class="submit-btn" type="submit" style="margin: 25px 0 0 0;width: 100%;" value="Reset password" />
							</div>
							<img src="img/ajax-loader.gif" class="loading-img" alt="Please Wait..."/>
							<output id="output-resetpass" name="output" style="color: RoyalBlue;font-size: 0.7em;font-family: Arial;"></output>
						</div> <!-- formfields -->
					</form>
				</div>
			</div> <!-- resetpass -->

			<div id="usercpass-content" class="contentscontainers" style="background: url('img/user-cpass.png');background-size: cover;">
				<div class="inlineblock"><img src="img/user-cpass.jpg" alt="Change Password..."></div>
				<div class="inlineblock" style="vertical-align: top;font-size: 1.3em;line-height: 2.1em;">
					<div style="margin: 0 0 0 2em;"><p style="float:none; width: 100%;font-family: Arial;">Change Password</p></div>
					<form id="cpass-form" class="user-biznes-form" action="<?php echo $_SERVER['PHP_SELF'];?>" method="post" enctype="multipart/form-data">
						<input type="hidden" id="antibot-cpass" name="antibot" value="" />
						<input type="hidden" id="ajax-cpass" name="ajax" value="<?php echo $_SERVER['PHP_SELF']?>" />
						<input type="hidden" id="action-cpass" name="action" value="cpass" />
						<div class="formfields">
							<div class="input-container">	
								<span id="passwordcpass-label" class="textbox-label">Current Password</span>	
								<input id="passwordcpass" name="passwordcpass" class="forminputs pass login" type="password" placeholder="Current Password" data-messageless="true" required>
								<img src="img/eye-30x30.png" class="showpass">
							</div>
							
							<div class="input-container">	
								<span id="passwordnew-label" class="textbox-label">New Password</span>	
								<input id="passwordnew" name="passwordnew" class="forminputs pass change" type="password" placeholder="New Password" data-messageless="true" required>
								<img src="img/eye-30x30.png" class="showpass">
							</div>
							
							<div class="input-container">	
								<span id="passwordrepeatnew-label" class="textbox-label">Repeat New Password</span>	
								<input id="passwordrepeatnew" name="passwordrepeatnew" class="forminputs pass change" type="password" placeholder="Repeat New Password" data-messageless="true" required>
								<img src="img/eye-30x30.png" class="showpass">
							</div>
							
							<div class="input-container">	
								<input id="submitcpass" name="submitcpass" class="submit-btn" type="submit" style="margin: 25px 0 0 0;width: 100%;" value="Change password" />
							</div>
							<img src="img/ajax-loader.gif" class="loading-img" alt="Please Wait..."/>
							<output id="output-cpass" name="output" style="color: RoyalBlue;font-size: 0.7em;font-family: Arial;"></output>
						</div> <!-- formfields -->
					</form>
				</div>
			</div> <!-- usercpass -->

			<div id="userfpass-content" class="contentscontainers" style="background: url('img/user-fpass.png');background-size: cover;">
				<div class="inlineblock"><img src="img/user-fpass.jpg" alt="Forgot Password..."></div>
				<div class="inlineblock" style="vertical-align: top;font-size: 1.3em;line-height: 2.1em;">
					<div style="margin: 0 0 0 2em;"><p style="float:none; width: 100%;font-family: Arial;">Forgot Password</p></div>
					<form id="fpass-form" class="user-biznes-form" action="<?php echo $_SERVER['PHP_SELF'];?>" method="post" enctype="multipart/form-data">
						<input type="hidden" id="antibot-fpass" name="antibot" value="" />
						<input type="hidden" id="ajax-fpass" name="ajax" value="<?php echo $_SERVER['PHP_SELF']?>" />
						<input type="hidden" id="action-fpass" name="action" value="fpass" />
						<div class="formfields">
							<div class="input-container">
								<span id="fpassemail-label" class="textbox-label">Email</span>	
								<input id="fpassemail" name="fpassemail" class="forminputs" type="email" value="" placeholder="Enter your Email" autocomplete="off" data-messageless="true" required>
							</div>
							
							<div class="input-container">	
								<input id="submitfpass" name="submitfpass" class="submit-btn" type="submit" style="margin: 25px 0 0 0;width: 100%;" value="Request Password Reset" />
							</div>
							<img src="img/ajax-loader.gif" class="loading-img" alt="Please Wait..."/>
							<output id="output-fpass" name="output" style="color: RoyalBlue;font-size: 0.7em;font-family: Arial;"></output>
						</div> <!-- formfields -->
					</form>
				</div>
			</div> <!-- userfpass -->

			<div id="userprof-content" class="contentscontainers" style="background: url('img/user-prof.png');background-size: cover;">
				<div class="inlineblock"><img src="img/user-prof.jpg" alt="Update your identity..."></div>
				<div class="inlineblock" style="vertical-align: top;font-size: 1.3em;line-height: 2.1em;">
					<div style="margin: 0 0 0 2em;"><p style="float:none; width: 100%;font-family: Arial;">Update Your Identity Record</p></div>
					<form id="prof-form" class="user-biznes-form" action="<?php echo $_SERVER['PHP_SELF'];?>" method="post" enctype="multipart/form-data">
						<input type="hidden" id="antibot-prof" name="antibot" value="" />
						<input type="hidden" id="ajax-prof" name="ajax" value="<?php echo $_SERVER['PHP_SELF']?>" />
						<input type="hidden" id="action-prof" name="action" value="prof" />
						<div class="formfields">
							<div class="inlineblock input-container">
								<span id="fnameprof-label" class="textbox-label">First Name</span>
								<input id="fnameprof" name="fnameprof" class="forminputs" type="text" value="" placeholder="First Name" autocomplete="off" data-messageless="true" >
								<img src="img/dummy30x30.png">
							</div>
							<div class="inlineblock input-container">
								<span id="lnameprof-label" class="textbox-label">Last Name</span>
								<input id="lnameprof" name="lnameprof" class="forminputs" type="text" value="" placeholder="Last Name" autocomplete="off" data-messageless="true" >
							</div>
							<br>
							<div class="inlineblock input-container">
								<span id="emailprof-label" class="textbox-label">Email</span>
								<input id="emailprof" name="emailprof" class="forminputs" type="email" value="" placeholder="Email" autocomplete="off" data-messageless="true" >
								<img src="img/dummy30x30.png">
							</div>
							<div class="inlineblock input-container">
								<span id="usernameprof-label" class="textbox-label">Username, optional, for login</span>	
								<input id="usernameprof" name="usernameprof" class="forminputs" type="text" value="" placeholder="Username" autocomplete="off" data-messageless="true">
							</div>
							
							<div class="input-container">	
								<input id="submitprof" name="submitprof" class="submit-btn" type="submit" style="margin: 25px 0 0 0;width: 100%;" value="Update" />
							</div>
							<img src="img/ajax-loader.gif" class="loading-img" alt="Please Wait..."/>
							<output id="output-prof" name="output" style="color: RoyalBlue;font-size: 0.7em;font-family: Arial;"></output>
						</div> <!-- formfields -->
					</form>
				</div>
			</div> <!-- userprof -->

		<!-- /Always hidden - contents shown via javascript driven thru top menu-->
		</div> <!-- maincontentscontainer -->
		<!-- pane to the right for ads -->
		<div id="rightpaneforads" class="mainbodycontainers floatright">
		AdHereAdHereAdHereAdHereAdHereAdHereAdHereAdHereAdHereAdHereAdHereAdHereAdHere
		</div>
		<div>Footer</div>
	</div> 
     	
	<!-- top drop menu items container - populated dynamically by javascript -->
		<div id="topdropmenu" tabindex="0"> <!-- tabindex="0" is to allow <div> receive focus, don't know more about it... -->
 
	    <!-- menu items displayed in container.id="topdropmenu" -->
	    <!-- Note! All IDs here must correspond to "topmenutitles' IDs"+"-menuitem", otherwise it will display crap -->
			<div id="topmenutitle1-menuitem" class="topmenuitems" >topmenutitle1-menuitem<br>topmenutitle1-menuitem<br>topmenutitle1-menuitem<br>topmenutitle1-menuitem<br>topmenutitle1-menuitem<br>topmenutitle1-menuitem<br>topmenutitle1-menuitem<br></div>
			<div id="topmenutitle2-menuitem" class="topmenuitems" >topmenutitle2-menuitem<br>topmenutitle2-menuitem<br>topmenutitle2-menuitem<br>topmenutitle2-menuitem<br>topmenutitle2-menuitem<br>topmenutitle2-menuitem<br>topmenutitle2-menuitem<br>topmenutitle2-menuitem<br>topmenutitle2-menuitemtopmenutitle2-menuitemtopmenutitle2-menuitemtopmenutitle2-menuitemtopmenutitle2-menuitemtopmenutitle2-menuitemtopmenutitle2-menuitemtopmenutitle2-menuitemtopmenutitle2-menuitemtopmenutitle2-menuitemtopmenutitle2-menuitemtopmenutitle2-menuitemtopmenutitle2-menuitemtopmenutitle2-menuitemtopmenutitle2-menuitemtopmenutitle2-menuitemtopmenutitle2-menuitem</div>
			<div id="topmenutitle3-menuitem" class="topmenuitems" >topmenutitle3-menuitem<br>topmenutitle3-menuitem<br><a href="https://peterlandlord.com.au">PL</a><br>topmenutitle3-menuitem<br>topmenutitle3-menuitem<br>topmenutitle3-menuitem<br>topmenutitle3-menuitemtopmenutitle3-menuitemtopmenutitle3-menuitemtopmenutitle3-men</div>
			<div id="topmenutitle4-menuitem" class="topmenuitems" >topmenutitle4-menuitem<br>topmenutitle4-menuitem<br>topmenutitle4-menuitem<br>topmenutitle4-menuitem<br>topmenutitle4-menuitem<br>topmenutitle4-menuitem<br></div>
	
			<!-- User's Business -->
			<div id="topmenutitle25-menuitem" class="topmenuitems" >
				<div class="inlineblock" style="width: 50%;list-style-type: none;font-size: 1.3em;line-height: 2.1em;">
					<!-- <span style="font-size: 1.0em;">All about youser...</span> -->
					<ol class="inlineblock" style="list-style-type: none;margin-top: 0;font-size: 1.3em;">
						<li id="userjoin" class="userlinks loggedout" class="userlinks" style="text-align: right;">Join Us<br><p style="margin-top: 0;text-align: left;font-size: 0.6em;line-height: 1.1em;">Why join? We will keep your work for you to access at anytime. You may continue as a guest but your work will be deleted at the end of your session. That's why. </p></li>
						<li id="userlogin" class="userlinks loggedout">Log In</li>
						<li id="userfpass" class="userlinks loggedout" style="text-align: right;font-size: 0.8em;">Forgot Passsword?</li>
						<li id="usercpass" class="userlinks loggedin" style="text-align: right;font-size: 0.8em;">Change Passsword</li>
						<li id="userprof" class="userlinks loggedin" style="text-align: right;font-size: 0.8em;">Update Your Profile</li>
						<li id="userlogout" class="userlinks loggedin" style="text-align: right;font-size: 0.8em;">Log Out</li>
						<li id="userguest" class="userlinks loggedout">Continue as Guest<br><p style="margin-top: 0;text-align: left;font-size: 0.6em;line-height: 1.1em;">As a guest, remember that any work done here will be erased as soon as you finish the session. Finish and take it with you or join and login to enjoy full benefits.</p></li>
					</ol>
				</div>
				<div class="inlineblock floatright"><img src="img/user-bizo-menu.jpg" alt="That's how you do it..."></div>
			</div> <!-- topmenutitle25-menuitem -->
		</div> 
	<!-- topdropmenu -->
	</div> 
<!-- /index-page -->
 
</body>
</html>