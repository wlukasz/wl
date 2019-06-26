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
			case "searchcity":
				$return_array = search_weather_city ( $_POST );
				break;
			case "searchlang":
				$return_array = search_weather_lang ( $_POST );
				break;
			case "findcity":
				$return_array = get_weather_data ( $_POST );
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
	<link rel="shortcut icon" href="favicon.ico" />
    <meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
 	<?php include("inc/css.inc"); ?>
   <link href="https://fonts.googleapis.com/css?family=Raleway:400,300,600,800,900" rel="stylesheet" type="text/css">
<style>

/* top level grid definitions */

.grid {
	display: grid;
	grid-template-columns: repeat( 15, 1fr );
	grid-template-rows: repeat( 1, 1fr );
	grid-gap: 1px;
}

@media( min-width: 640px ) {
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
} 
/* end of top level grid definitions */

/* OPENWEATHERMAP Grid Definitions */

/* city search grid definitions */
cpik { grid-area: cpik; }

.findcity-grid {
	display: grid;
	grid-gap: 1px;
	grid-template-areas: 
		"cpik"
}

/* weather page sub-grid definitions */
wnow { grid-area: wnow; }
cmap { grid-area: cmap; }
fcst { grid-area: fcst; }

.weather-grid {
	display: grid;
	grid-gap: 1px;
	grid-template-areas: 
		"wnow"
		"cmap"
		"fcst"
}

@media( min-width: 640px ) {
	.weather-grid {
		display: grid;
		grid-gap: 1px;
		grid-template-areas:
			"wnow wnow wnow cmap cmap cmap cmap"
			"fcst fcst fcst fcst fcst fcst fcst"
	}
}

/* end of weather page sub-grid definitions */

/* current weather sub-grid definitions */
city { grid-area: city; }
dnow { grid-area: dnow; }
temp { grid-area: temp; }
tmin { grid-area: tmin; }
tmax { grid-area: tmax; }
wdsc { grid-area: wdsc; }
icon { grid-area: icon; }
phpa { grid-area: phpa; }
humi { grid-area: humi; }
wind { grid-area: wind; }
ccvr { grid-area: ccvr; }
sunr { grid-area: sunr; }
suns { grid-area: suns; }

.weather-now-grid {
	display: grid;
	grid-gap: 1px;
	grid-template-areas: 
		"city"
		"dnow"
		"temp"
		"tmin"
		"tmax"
		"wdsc"
		"icon"
		"phpa"
		"humi"
		"wind"
		"ccvr"
		"sunr"
		"suns"
}

@media( min-width: 640px ) {
	.weather-now-grid {
		display: grid;
		grid-gap: 1px;
		grid-template-areas:
			"city city city" 
			"dnow dnow dnow" 
			"temp icon wdsc"
			"sunr phpa humi"
			"suns wind ccvr"
	}
}

/* end of current weather sub-grid definitions */

/* temp sub-grid definitions */
ticn { grid-area: ticn; }
tmax { grid-area: tmax; }
tnow { grid-area: tnow; }
tmin { grid-area: tmin; }

.temp-grid {
	display: grid;
	grid-gap: 1px;
	grid-template-areas: 
		"ticn"
		"tmax"
		"tnow"
		"tmin"
}

@media( min-width: 640px ) {
	.temp-grid {
		display: grid;
		grid-gap: 1px;
		grid-template-areas:
			"ticn tmax" 
			"ticn tnow"
			"ticn tmin"
	}
}

/* end of temp sub-grid definitions */

/* fcst sub-grid definitions */
d101 { grid-area: d101; }
d201 { grid-area: d201; }
d301 { grid-area: d301; }
d401 { grid-area: d401; }
d501 { grid-area: d501; }
d601 { grid-area: d601; }
d104 { grid-area: d104; }
d204 { grid-area: d204; }
d304 { grid-area: d304; }
d404 { grid-area: d404; }
d504 { grid-area: d504; }
d604 { grid-area: d604; }
d107 { grid-area: d107; }
d207 { grid-area: d207; }
d307 { grid-area: d307; }
d407 { grid-area: d407; }
d507 { grid-area: d507; }
d607 { grid-area: d607; }
d110 { grid-area: d110; }
d210 { grid-area: d210; }
d310 { grid-area: d310; }
d410 { grid-area: d410; }
d510 { grid-area: d510; }
d610 { grid-area: d610; }
d113 { grid-area: d113; }
d213 { grid-area: d213; }
d313 { grid-area: d313; }
d413 { grid-area: d413; }
d513 { grid-area: d513; }
d613 { grid-area: d613; }
d116 { grid-area: d116; }
d216 { grid-area: d216; }
d316 { grid-area: d316; }
d416 { grid-area: d416; }
d516 { grid-area: d516; }
d616 { grid-area: d616; }
d119 { grid-area: d119; }
d219 { grid-area: d219; }
d319 { grid-area: d319; }
d419 { grid-area: d419; }
d519 { grid-area: d519; }
d619 { grid-area: d619; }
d122 { grid-area: d122; }
d222 { grid-area: d222; }
d322 { grid-area: d322; }
d422 { grid-area: d422; }
d522 { grid-area: d522; }
d622 { grid-area: d622; }

.fcst-grid {
	display: grid;
	grid-gap: 1px;
	grid-template-areas: 
		"d101"
		"d201"
		"d301"
		"d401"
		"d501"
		"d601"
		"d104"
		"d204"
		"d304"
		"d404"
		"d504"
		"d604"
		"d107"
		"d207"
		"d307"
		"d407"
		"d507"
		"d607"
		"d110"
		"d210"
		"d310"
		"d410"
		"d510"
		"d610"
		"d113"
		"d213"
		"d313"
		"d413"
		"d513"
		"d613"
		"d116"
		"d216"
		"d316"
		"d416"
		"d516"
		"d616"
		"d119"
		"d219"
		"d319"
		"d419"
		"d519"
		"d619"
		"d122"
		"d222"
		"d322"
		"d422"
		"d522"
		"d622"

}

@media( min-width: 640px ) {
	.fcst-grid {
		display: grid;
		grid-gap: 1px;
		grid-template-areas:

		"d101 d201 d301 d401 d501 d601"
		"d104 d204 d304 d404 d504 d604"
		"d107 d207 d307 d407 d507 d607"
		"d110 d210 d310 d410 d510 d610"
		"d113 d213 d313 d413 d513 d613"
		"d116 d216 d316 d416 d516 d616"
		"d119 d219 d319 d419 d519 d619"
		"d122 d222 d322 d422 d522 d622"

	}
}

/* end of fcst sub-grid definitions */
/* End of OPENWEATHERMAP Grid Definitions */

.weather-display {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 1.0em;
	color: white;
	background-color: dodgerblue;
	border-radius: 3px;
	padding: 5px;
}

/* DarkSky Grid Definitions */
/*  darksky-grid */
dscity { grid-area: dscity; }
dsarch { grid-area: dsarch; }
dssumm { grid-area: dssumm; }
dscurr { grid-area: dscurr; }
dsdly0 { grid-area: dsdly0; }
dsdly1 { grid-area: dsdly1; }
dsdly2 { grid-area: dsdly2; }
dsdly3 { grid-area: dsdly3; }
dsdly4 { grid-area: dsdly4; }
dsdly5 { grid-area: dsdly5; }
dsdly6 { grid-area: dsdly6; }
dsdly7 { grid-area: dsdly7; }


.darksky-grid {
	display: grid;
	grid-gap: 1px;
	grid-template-areas: 
		"dscity"
		"dsarch"
		"dssumm"
		"dscurr"
		"dsdly0"
		"dsdly1"
		"dsdly2"
		"dsdly3"
		"dsdly4"
		"dsdly5"
		"dsdly6"
		"dsdly7"
}

@media( min-width: 640px ) {
	.darksky-grid {
		display: grid;
		grid-gap: 1px;
		grid-template-areas:
		"dscity dscity dscurr dsarch"
		"dssumm dssumm dscurr dsarch"
		"dsdly0 dsdly0 dsdly0 dsdly0"
		"dsdly1 dsdly1 dsdly1 dsdly1"
		"dsdly2 dsdly2 dsdly2 dsdly2"
		"dsdly3 dsdly3 dsdly3 dsdly3"
		"dsdly4 dsdly4 dsdly4 dsdly4"
		"dsdly5 dsdly5 dsdly5 dsdly5"
		"dsdly6 dsdly6 dsdly6 dsdly6"
		"dsdly7 dsdly7 dsdly7 dsdly7"
	}
}
/* End of darksky-grid */

/*  darksky-hourly-grid */
dsvbar { grid-area: dsvbar; }
dshr02 { grid-area: dshr02; }
dshr04 { grid-area: dshr04; }
dshr06 { grid-area: dshr06; }
dshr08 { grid-area: dshr08; }
dshr10 { grid-area: dshr10; }
dshr12 { grid-area: dshr12; }
dshr14 { grid-area: dshr14; }
dshr16 { grid-area: dshr16; }
dshr18 { grid-area: dshr18; }
dshr20 { grid-area: dshr20; }
dshr22 { grid-area: dshr22; }
dshr24 { grid-area: dshr24; }

.darksky-hourly-grid {
	display: grid;
	grid-gap: 1px;
	grid-template-areas: 
	"dsvbar"
	"dshr02"
	"dshr04"
	"dshr06"
	"dshr08"
	"dshr10"
	"dshr12"
	"dshr14"
	"dshr16"
	"dshr18"
	"dshr20"
	"dshr22"
	"dshr24"
}

@media( min-width: 640px ) {
	.darksky-hourly-grid {
		display: grid;
		grid-gap: 1px;
		grid-template-areas:
		"dsvbar dshr02 dshr02 dshr02 dshr02 dshr02 dshr02 dshr02"
		"dsvbar dshr04 dshr04 dshr04 dshr04 dshr04 dshr04 dshr04"
		"dsvbar dshr06 dshr06 dshr06 dshr06 dshr06 dshr06 dshr06"
		"dsvbar dshr08 dshr08 dshr08 dshr08 dshr08 dshr08 dshr08"
		"dsvbar dshr10 dshr10 dshr10 dshr10 dshr10 dshr10 dshr10"
		"dsvbar dshr12 dshr12 dshr12 dshr12 dshr12 dshr12 dshr12"
		"dsvbar dshr14 dshr14 dshr14 dshr14 dshr14 dshr14 dshr14"
		"dsvbar dshr16 dshr16 dshr16 dshr16 dshr16 dshr16 dshr16"
		"dsvbar dshr18 dshr18 dshr18 dshr18 dshr18 dshr18 dshr18"
		"dsvbar dshr20 dshr20 dshr20 dshr20 dshr20 dshr20 dshr20"
		"dsvbar dshr22 dshr22 dshr22 dshr22 dshr22 dshr22 dshr22"
		"dsvbar dshr24 dshr24 dshr24 dshr24 dshr24 dshr24 dshr24"
	}
}
/* End of darksky-hourly-grid */

/* End of DarkSky Grid Definitions */

.dsdly {
	margin: 10px 100px 10px 100px;	
    border-style: solid;
    border-width: 1px;
	border-radius: 3px;
	border-color: dodgerblue;
}

.dsdly:hover {
	cursor: pointer;
}

.hourly {
	margin: 10px 10px 10px 10px;	
}

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

.matchedcities, .matchedlang {
	font-family: sans-serif;
	text-decoration: none; 
	font-size: 0.8em;
	color: white;
}

.matchedcities:hover, .matchedlang:hover {
	color: dodgerblue;
	background-color: white;
	cursor: pointer;
}

#citysearchoutput, #langsearchoutput {
    border-style: solid;
    border-width: 1px;
	border-radius: 3px;
	border-color: lightgrey;
}

/* ProgressBar definitions */
#dsarch {
  width: 306px;
  height: 153px;
}

/* #dsvbarpath {
  width: 100px;
  height: 400px;
} */

svg {
  /* width: 100%;
  height: 100%; */
  fill: none;
  stroke: dodgerblue;
  stroke-width: 5;
  stroke-linecap: round;
  /* -webkit-filter: drop-shadow( -3px -2px 5px gray );
  filter: drop-shadow( -3px -2px 5px gray ); */
  }
/* End of ProgressBar definitions */

</style>
	<?php include("inc/js.inc"); ?>
    <script src="js/progressbar.js"></script>
<script type="text/javascript">
	
	var objAntiBot;
	var objOutput;
	var strWeatherSource = '';
	var darkskyData;
		
	$( document ).ready( function() {

		// init
		$( '#submitfindcity' ).hide();
		$( '#cpik' ).hide();
		$( '#darksky-options' ).hide();
		$( '.hourly' ).hide();
		<?php if ( isset( $_COOKIE['session_token'] ) ) { ?>
			$( '.loggedout' ).hide();
			get_user_details();
		<?php } else { ?>
			$( '.loggedin' ).hide();
			$( '#cpik' ).hide();
			$( '#userbizotitle' ).html( "User's Business" );
			$( '#logo' ).html( "" );
			$( '#logo' ).css( "background", "url('img/logo.jpg')" );
			$( '#logo' ).css( "background-size", "cover" );
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
						$( '#logo' ).html( "" );
						$( '#logo' ).css( "background", "url('img/logo.jpg')" );
						$( '#logo' ).css( "background-size", "cover" );

						$( '#'+VisibleContentID ).css( 'display' , 'none' );
						VisibleContentID = 'homecontents';
						$( '#'+VisibleContentID ).css( 'display' , 'block' );
						if ( topMenuDown === true ) {
							slide_up_topmenu ();
						}
						
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
				$( '.weather-display').hide();
				$( '#output-findcity').hide();
				$( '#darksky-results').hide();
				
				if ( this.id === 'openweathermap-accept' || this.id === 'darksky-accept' ) {
					$( '#cpik').fadeIn();
					$( '#darksky-options' ).fadeOut();
					$( '#findcitybox' ).val( '' );
					$( '#langbox' ).val( '' );
					strWeatherSource = '';
					if ( this.id === 'openweathermap-accept' ) {
						document.getElementById( 'poweredby' ).href = 'https://openweathermap.org';
						document.getElementById( 'poweredby' ).text = 'OpenWeatherMap';
						$( '#source').val( 'openweathermap' );
						strWeatherSource = 'openweathermap';
					} else if ( this.id === 'darksky-accept' ) {
						document.getElementById( 'poweredby' ).href = 'https://darksky.net';
						document.getElementById( 'poweredby' ).text = 'DarkSky API';
						$( '#source').val( 'darksky' );
						strWeatherSource = 'darksky';
					} else {
						document.getElementById( 'poweredby' ).href = '#';
						document.getElementById( 'poweredby' ).text = '';
					}
				}	
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
						if ( msg.action == "login" ) {
							setTimeout( function() { 
								$( '#'+VisibleContentID ).css( 'display' , 'none' );
								VisibleContentID = 'homecontents';
								$( '#'+VisibleContentID ).css( 'display' , 'block' );
				 			}, 3000);
						}
					}	
					
					if ( msg.rc == "1" && msg.action == "resetpass" ) {
						$( '#'+VisibleContentID ).css( 'display' , 'none' );
						VisibleContentID = 'userlogin-content';
						$( '#'+VisibleContentID ).css( 'display' , 'block' );
						alert ('Password Reset Successful.\nYou may Log In now.');
					}
					
				// weather results come here
					if ( msg.action == "findcity" ) {
						$( '#lang' ).val( '' );
						$( '#submitfindcity' ).fadeOut();

						if ( strWeatherSource === 'openweathermap' ) {
							if ( msg.rc == "1" )  {
								// set google map
								var lat = msg.coord.lat;
								var lon = msg.coord.lon;
								$( '#cmap' ).html( '<iframe width="100%" height="100%" frameborder="0" style="border:0" src="https://www.google.com/maps/embed/v1/view?zoom=15&center='+lat+','+lon+'&key=<?php echo API_KEY_GOOGLE_MAP_EMBED;?>" ></iframe>' )

								// populate current weather fields
								$.each( msg.curr, function( index, value ) {
										$( '#'+index ).html( value );
								});

								// populate forecast weather fields
								for(var i = 0; i < msg.fcst.length; i++) {
									var fcst = msg.fcst[i];
									$.each( fcst, function( index, value ) {
										$( '#'+index ).html( value );
									});
								}

								$( '#'+VisibleContentID ).css( 'display' , 'none' );
								VisibleContentID = 'openweathermap-accept-content';
								$( '#'+VisibleContentID ).css( 'display' , 'block' );
								$( '.weather-display').fadeIn();
							} else if  ( msg.rc == "0" )  {
								alert(msg.errmsg);
							} else {
								alert(msg.errmsg);
							}
// ##############################
// DarkSky Weather populate HERE:
// ##############################
						} else if ( strWeatherSource === 'darksky' ) {
							if ( msg.rc == "1" )  {

								darkskyData = msg.data;

								// clear any hourly dropdowns
								$( '.togglehourly' ).each( function() {
									$( this ).html( 'more...' );
								});
								$( '.hourly' ).hide();
								$( '.dshr' ).hide();

								$( '#dsarch' ).html( '' ); // clear arch container or it writes over itself on repeated searches

								// populate common weather fields
								$.each( darkskyData.common, function( index, value ) {
									$( '#'+index ).html( value );
								});


// ProgressBar.js definitions 
// SemiCircle
		var bar = new ProgressBar.SemiCircle(dsarch, {
            strokeWidth: 10,
            color: 'dodgerblue',
            trailColor: '#eee',
            trailWidth: 10,
            easing: 'linear',
            // easing: 'easeInOut',
            duration: 50000,
            svgStyle: null,
            text: {
                value: '',
                alignToBottom: true
            },
    
            // Set default step function for all animate calls
            step: (state, bar) => {
                bar.path.setAttribute('stroke', state.color);
				var archText = 'Now,<br>'+darkskyData.archdata[0].ddd+', '+darkskyData.archdata[0].hour+'<br>Feels like '+darkskyData.archdata[0].temp+'&#176C';

                for ( var i=0; i<darkskyData.archdata.length; i++ ) {
                    if ( i === Math.floor( bar.value() * 100 / (100 / 24) ) ) { // convert bar progress to hours
                        archText = darkskyData.archdata[i].ddd+', '+darkskyData.archdata[i].hour+'<br>'+darkskyData.archdata[i].temp+'&#176C';
                        break;
                    }
                }

				bar.setText( archText );
                bar.text.style.color = state.color;
                // bar.text.style.color = 'black';
            } // step
        }); // new Progressbar

        bar.text.style.fontFamily = '"Raleway", Helvetica, sans-serif';
        bar.text.style.fontSize = '1.4rem';

// End of ProgressBar.js definitions 

		bar.animate( 1 );  // Number from 0.0 to 1.0

								// populate daily summaries
								$.each( darkskyData.daily, function( index, value ) {
									$( '#'+index ).html( value );
								});

								$( '#'+VisibleContentID ).css( 'display' , 'none' );
								VisibleContentID = 'darksky-accept-content';
								$( '#'+VisibleContentID ).css( 'display' , 'block' );
								$( '#darksky-results').fadeIn();
								$( '#darksky-options').hide();
							} else if  ( msg.rc == "0" )  {
								alert(msg.errmsg);
							} else {
								alert(msg.errmsg);
							}
						} else {
							alert( 'Invalid weather source detected') ;
						}
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
			$('#'+objOutput.id).fadeIn(); // show  output div
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
						$( '#logo' ).html( "Welcome "+msg.result['first_name'] );
						$( '#logo' ).css( "background", "" );
						$( '#logo' ).css( "display", "flex" );
						$( '#logo' ).css( "align-items", "center" );

						setTimeout( function() { 
							$( '#logo' ).html( "" );
							$( '#logo' ).css( "background", "url('img/logo.jpg')" );
							$( '#logo' ).css( "background-size", "cover" );
						}, 3000);
					} else {
						alert ( msg.errmsg );
					}
			    },
			    error: function (jqXHR, textStatus, errorThrown) {
					alert("Network Error! "+msg.errmsg);
			    },
			});  // ### AJAX this is to do database business ###
		}

		/* retrieve city for weather */
		$( '#findcitybox' ).on( 'input', function() {
			if ( this.value.length > 2 ) {

				var cityPattern = this.value+'%';
				if ( this.value == '' ) {
					cityPattern = '';
				}
				var callData = "ajax=<?php echo $_SERVER['PHP_SELF'];?>&action=searchcity&cityname="+cityPattern;
					$.ajax({ // ### AJAX this is to do database business ###
					url:"<?php echo $_SERVER['PHP_SELF'];?>",
					type:"POST",
					data : callData,
					success:function(msg){
						msg = JSON.parse(msg);
						if ( msg.rc == "1" ) {
							var i = 0;
							var srText = '';
							for ( i==0; i<msg.result.length; i++ ) {
								srText += '<span id="'+msg.result[i].id+'" style="padding: 0 20px 0 10px;" class="matchedcities">'+msg.result[i].name+', '+msg.result[i].country_name+'</span><br>';
							}
							$( '#citysearchoutput').html( srText );
						} else if ( msg.rc == "2" ) {
							$( '#citysearchoutput').html( '' );
						} else if ( msg.rc == "0" ) {
							alert ( msg.errmsg );
						}
					},
					error: function (jqXHR, textStatus, errorThrown) {
						alert("Network Error! "+msg.errmsg);
					},
				});  // ### AJAX this is to do database business ###
			} else {
				$( '#submitfindcity' ).fadeOut();
				$( '#citysearchoutput').html( '' );
			}
		});
		
		$( document ).on( 'click touchstart', '.matchedcities', function() {
			$( '#findcitybox' ).val( $( this ).text() );
			$( '#cityid-findcity' ).val( this.id );
			$( '#citysearchoutput').html( '' );
			$( '#submitfindcity' ).fadeIn();
			if ( strWeatherSource === 'darksky' ) {
				$( '#darksky-options' ).fadeIn();
			}
		});
		
		$( '#findcitybox' ).focusin( function() {
			$( this ).val( '' );
			$( '#submitfindcity' ).fadeOut();
			$( '#darksky-options' ).fadeOut();
			$( '#langsearchoutput').html( '' );
			$( '#langbox').val( '' );
		});

		/* retrieve optional language for weather */
		$( '#langbox' ).on( 'input', function() {
			if ( this.value.length > 1 ) {

				var langPattern = this.value+'%';
				if ( this.value == '' ) {
					langPattern = '';
				}
				var callData = "ajax=<?php echo $_SERVER['PHP_SELF'];?>&action=searchlang&langname="+langPattern;
				$.ajax({ // ### AJAX this is to do database business ###
					url:"<?php echo $_SERVER['PHP_SELF'];?>",
					type:"POST",
					data : callData,
					success:function(msg){
						msg = JSON.parse(msg);
						if ( msg.rc == "1" ) {
							var i = 0;
							var srText = '';
							for ( i==0; i<msg.result.length; i++ ) {
								srText += '<span id="'+msg.result[i].lang_iso+'" style="padding: 0 20px 0 5px;" class="matchedlang">'+msg.result[i].language+'</span><br>';
							}
							$( '#langsearchoutput').html( srText );
						} else if ( msg.rc == "2" ) {
							$( '#langsearchoutput').html( '' );
						} else if ( msg.rc == "0" ) {
							alert ( msg.errmsg );
						}
					},
					error: function (jqXHR, textStatus, errorThrown) {
						alert("Network Error! "+msg.errmsg);
					},
				});  // ### AJAX this is to do database business ###
			} else {
				$( '#langsearchoutput').html( '' );
			}
		});
		
		$( document ).on( 'click touchstart', '.matchedlang', function() {
			$( '#langbox' ).val( $( this ).text() );
			$( '#lang' ).val( this.id );
			$( '#langsearchoutput').html( '' );
		});
		
		$( '#langbox' ).focusin( function() {
			$( this ).val( '' );
		});
		
		$( '#langbox' ).focusin( function() {
			$( this ).val( '' );
		});
		
		$( document ).on( 'click touchstart', '.dsdly', function() {
		// $( document ).on( 'click touchstart', '.togglehourly', function() {
			var loopCount = $( this ).attr( 'value' );
			$( '.hourly' ).each( function() {
				if ( $( this ).attr( 'value' ) === loopCount ) {
					$( this ).fadeToggle( 'slow' );
				} else {
					$( this ).hide( 'slow' );
				}
			});

			$( '.togglehourly' ).each( function() {
				if ( $( this ).attr( 'value' ) === loopCount ) {
					if ( $( this ).html() === 'more...' ) {
						$( this ).html( 'less...' );
						var hourly_items_count = populate_hourly_grid ( darkskyData, loopCount );
// The below 2 lines of code have been commented out disable vbar - it's not working correctly
// so we just show the hourly items and not plot the vbar
						// $( '.dshr' ).hide();
						// run_vbar_and_show( hourly_items_count );
					} else {
						$( this ).html( 'more...' );
					}
				} else {
					$( this ).html( 'more...' );
				}
			});
		});

function populate_hourly_grid ( darkskyData, loopCount ) {
	var hourlyHtml = '';
	var hourly_items_count = 0;

	$( '.hourly' ).each( function() {
		var hourlyID = this.id;
		$( this ).html( '' );

		$.each( darkskyData.hourly, function( index, value ) {
			if ( $( '#'+hourlyID ).attr( 'value' ) == loopCount && value.value_attr == loopCount ) {
				hourlyHtml += value.html;
				hourly_items_count++;
			}
		});

		if ( hourlyHtml.length > 0 ) {
			$( this ).html( '<dsvbar id="dsvbar" width="5px"><div id="dsvbarpath"></div></dsvbar>'+hourlyHtml );
		} else {
			$( this ).html( 'Hourly data unavailable' );
		}
		hourlyHtml = '';
	});

	return hourly_items_count;

} // populate_hourly_grid

function run_vbar_and_show( hourly_items_count ) {

// ProgressBar.js definitions 
// Line
	if (typeof dsvbarpath != "undefined") { // only run bar if hourly data exists
		var vbar = new ProgressBar.Line(dsvbarpath, {
			strokeWidth: 10,
			color: 'dodgerblue',
			trailColor: '#eee',
			trailWidth: 10,
			easing: 'linear',
			// easing: 'easeInOut',
			duration: 10000,
			// svgStyle: null,
			svgStyle: {
				transform: 'rotate(90deg)'
			},
			text: {
				value: '',
				alignToBottom: true
			},

			// Set default step function for all animate calls
			step: (state, vbar) => {
				vbar.path.setAttribute('stroke', 'dodgerblue');
				// var value = Math.round(vbar.value() * 100); // this would show percent
				var tempi;
				for ( var i=1; i<=hourly_items_count; i++ ) {
					if ( i === Math.floor( vbar.value() * 100 / (100 / hourly_items_count) ) ) {
						var temps = i * 2;
						var tempStr = temps.toString();
						tempi = tempStr.padStart(2, '0');
						break;
					// } else {
					// 	tempi = 12;
					}
				}
				$( '#dshr'+tempi).fadeIn( 'slow' );
				// if (tempi === 0) {
				// 	vbar.setText('');
				// } else {
				// 	vbar.setText( tempi );
				// }

				// vbar.text.style.color = state.color;
				// vbar.text.style.color = 'black';
			} // step
		}); // new Progressbar
		// vbar.text.style.fontFamily = '"Raleway", Helvetica, sans-serif';
		// vbar.text.style.fontSize = '1.0rem';

		vbar.animate( 1 );  // Number from 0.0 to 1.0
	} // if (typeof dsvbarpath != "undefined")

} // run_vbar_and_show

	}); // document ready
	
</script>
</head>
<body id="thebody">
<!-- index-page -->
	<div data-role="page" id="index-page" class="grid">
		<div id="logo" style="height: 95px;font-family:Arial, Helvetica, sans-serif;font-size: 1.5em;color: black;padding-left: 15px;"></div>
		<div style="height: 95px;background: url('img/top-banner.jpg');background-size: cover;"></div>
		<!-- navbar menu -->
		<div id="navbar">
      	
			<div id="homeicon" class="divtopmenuicons inlineblock" title="Home">
				<!-- <img class="topmenuicon" src="img/home25x25.png" alt=""> -->
				Home
			</div>
	
			<ol id="topmenuolist" class="inlineblock">
				<!-- all menu titles must have ascending numeric values -->
				<li id="topmenutitle1" class="topmenutitles" value="1">Reserved 1</li>
				<li id="topmenutitle2" class="topmenutitles" value="2">Weather</li>
				<li id="topmenutitle3" class="topmenutitles" value="3">Reserved 3</li>
				<li id="topmenutitle4" class="topmenutitles" value="4">Misc</li>
				<li id="topmenutitle25" class="topmenutitles" value="25"><div id="userbizotitle"></div></li>
				
			</ol> <!-- topmenuolist -->
		</div> <!-- navbar -->
		<!-- main contents container - hidden divs - shown dynamically by javascript -->
		<div id="maincontentscontainer" class="mainbodycontainers">
			
			<div id="homecontents" class="contentscontainers" style="font-family:'Raleway', Helvetica, sans-serif";>
				<div style="margin: 20px 50px 0px 50px;">
					<p>This is an experimental project. Its primary purpose is to keep my sanity intact. I think I'm successful in that.</p>
					<p>Beginnings go back to 2018 and I add sections as ideas come to mind. Don't hold your breath for anything "Wow!" but some sections might be useful.</p>
				</div>
			</div> <!-- homecontents -->

					<cpik id="cpik">
						<div class="findcity-grid" style="background-color: dodgerblue;">
							<form id="findcity-form" class="user-biznes-form" action="<?php echo $_SERVER['PHP_SELF'];?>" method="post" enctype="multipart/form-data">
								<output id="output-findcity" name="output" style="float: right;font-family: Arial;font-size: 0.7em;background-color: white;border-radius: 0 0 3px 3px;padding: 5px;"></output>
								<input type="hidden" name="antibot" value="" />
								<input type="hidden" name="ajax" value="<?php echo $_SERVER['PHP_SELF']?>" />
								<input type="hidden" name="action" value="findcity" />
								<input type="hidden" id="source" name="source" value="" />
								<input type="hidden" id="lang" name="lang" value="" />
								<input type="hidden" id="cityid-findcity" name="cityid" value="" />

								<div class="input-container inlineblock">
									<span id="findcitybox-label" class="textbox-label" style="color: white;">Find City</span>	
									<input id="findcitybox" name="findcitybox" class="forminputs" type="text" size="40" value="" title="Type city and it will show in a list below. Click on city of your choice." placeholder="Find City" autocomplete="off" required>
								</div>
								<div id="darksky-options" class="input-container inlineblock">
									<span id="langbox-label" class="textbox-label" style="color: white;">Select Language</span>	
									<input id="langbox" name="langbox" class="forminputs" type="text" size="20" value="" title="Type language name, it will show in box below. If not showing, laguage is unavailable, it will default to English. Weather summaries will show in chosen language, all other labels are in English" placeholder="Language (optional)" autocomplete="off">
								</div>
								<div class="input-container inlineblock">	
									<input id="submitfindcity" name="submitfindcity" class="submit-btn" type="submit" style="margin: 0 0 0 0;width: 100%;" value="Display Weather Info" />
								</div>
								<img src="img/ajax-loader.gif" class="loading-img inlineblock" alt="Please Wait..."/>
								<br>
								<div id="citysearchoutput" style="display: inline-block;margin-left: 5px;color: white;" class="forminputs"></div>
								<div id="langsearchoutput" style="display: inline-block;margin-left: 300px;color: white;" class="forminputs"></div>
								<span style="float: right;font-family: Arial;font-size: 0.8em;color: white;padding: 3px;">Powered by <a id="poweredby" href="" target="_blank" style="color: white;"></a></span>
							</form>
						</div>
					</cpik>

			<div id="darksky-accept-content" class="contentscontainers" style="font-family: Raleway, Helvetica, sans-serif;padding: 3px;">
				<div id="darksky-results" class="darksky-grid" style="background-color: white;padding: 0;">
					<dscity id="dscity"></dscity>
					<dsarch id="dsarch"></dsarch>
					<dssumm id="dssumm" style="text-align: center;font-size: 1.3em;padding: 3px 5px 3px 5px;"></dssumm>
					<dscurr id="dscurr" style="text-align: right;font-size: 0.8em;padding: 0 10px 0 0"></dscurr>
					<dsdly0 id="dsdly0" class="dsdly" value="0">
						<span id="dlysum0" value="0"></span>
						<div id="byhour0" class="darksky-hourly-grid hourly" value="0">
						</div>
					</dsdly0>
					<dsdly1 id="dsdly1" class="dsdly" value="1">
						<span id="dlysum1" value="1"></span>
						<div id="byhour1" class="darksky-hourly-grid hourly" value="1">
						</div>
					</dsdly1>
					<dsdly2 id="dsdly2" class="dsdly" value="2">
						<span id="dlysum2" value="2"></span>
						<div id="byhour2" class="darksky-hourly-grid hourly" value="2">
						</div>
					</dsdly2>
					<dsdly3 id="dsdly3" class="dsdly" value="3">
						<span id="dlysum3" value="3"></span>
						<div id="byhour3" class="darksky-hourly-grid hourly" value="3">
						</div>
					</dsdly3>
					<dsdly4 id="dsdly4" class="dsdly" value="4">
						<span id="dlysum4" value="4"></span>
						<div id="byhour4" class="darksky-hourly-grid hourly" value="4">
						</div>
					</dsdly4>
					<dsdly5 id="dsdly5" class="dsdly" value="5">
						<span id="dlysum5" value="5"></span>
						<div id="byhour5" class="darksky-hourly-grid hourly" value="5">
						</div>
					</dsdly5>
					<dsdly6 id="dsdly6" class="dsdly" value="6">
						<span id="dlysum6" value="6"></span>
						<div id="byhour6" class="darksky-hourly-grid hourly" value="6">
						</div>
					</dsdly6>
					<dsdly7 id="dsdly7" class="dsdly" value="7">
						<span id="dlysum7" value="7"></span>
						<div id="byhour7" class="darksky-hourly-grid hourly" value="7">
						</div>
					</dsdly7>
				</div>
			</div> <!-- darksky-accept-content -->

			<div id="openweathermap-accept-content" class="contentscontainers">

				<!-- weather-results -->
				<div id="weather-results" class="weather-grid" style="background-color: white;padding: 0;">
					<wnow class="weather-display">
						<div class="weather-now-grid">
							<city><span id="city" style="padding: 3px;font-size: 1.8em;"></span></city>
							<dnow><span id="dnow" style="padding: 3px;font-size: 1.2em;float: right;"></span></dnow>
							<temp class="temp-grid">
								<ticn><span id="ticn"></ticn>
								<tmin><span id="tmin" style="font-size: 0.8em;float: left;"></tmin>
								<tnow><span id="tnow" style="font-size: 1.8em;"></span></tnow>
								<tmax><span id="tmax" style="font-size: 0.8em;float: left;"></tmax>
							</temp>
							<wdsc><span id="wdsc" style="padding: 39px 3px 3px 3px;font-size: 1.0em;float: left;"></wdsc>
							<icon><span id="icon" style="padding: 3px;font-size: 1.0em;float: right;"></icon>
							<phpa><span id="phpa" style="padding: 3px;font-size: 1.0em;float: left;"></span></phpa>
							<humi><span id="humi" style="padding: 3px;font-size: 1.0em;float: right;"></span></humi>
							<wind><span id="wind" style="padding: 3px;font-size: 1.0em;float: left;"></span></wind>
							<ccvr><span id="ccvr" style="padding: 3px;font-size: 1.0em;float: right;"></span></ccvr>
							<sunr><span id="sunr" style="padding: 3px;font-size: 1.0em;float: left;"></span></sunr>
							<suns><span id="suns" style="padding: 3px;font-size: 1.0em;float: left;"></span></suns>
						</div>
					</wnow>
					<cmap id="cmap" class="weather-display"></cmap>
					<fcst class="fcst-grid weather-display" style="background-color: white;padding: 0;font-size: 0.8em;">

						<d101 id="d101" class="weather-display"></d101>
						<d201 id="d201" class="weather-display"></d201>
						<d301 id="d301" class="weather-display"></d301>
						<d401 id="d401" class="weather-display"></d401>
						<d501 id="d501" class="weather-display"></d501>
						<d601 id="d601" class="weather-display"></d601>

						<d104 id="d104" class="weather-display"></d104>
						<d204 id="d204" class="weather-display"></d204>
						<d304 id="d304" class="weather-display"></d304>
						<d404 id="d404" class="weather-display"></d404>
						<d504 id="d504" class="weather-display"></d504>
						<d604 id="d604" class="weather-display"></d604>

						<d107 id="d107" class="weather-display"></d107>
						<d207 id="d207" class="weather-display"></d207>
						<d307 id="d307" class="weather-display"></d307>
						<d407 id="d407" class="weather-display"></d407>
						<d507 id="d507" class="weather-display"></d507>
						<d607 id="d607" class="weather-display"></d607>

						<d110 id="d110" class="weather-display"></d110>
						<d210 id="d210" class="weather-display"></d210>
						<d310 id="d310" class="weather-display"></d310>
						<d410 id="d410" class="weather-display"></d410>
						<d510 id="d510" class="weather-display"></d510>
						<d610 id="d610" class="weather-display"></d610>

						<d113 id="d113" class="weather-display"></d113>
						<d213 id="d213" class="weather-display"></d213>
						<d313 id="d313" class="weather-display"></d313>
						<d413 id="d413" class="weather-display"></d413>
						<d513 id="d513" class="weather-display"></d513>
						<d613 id="d613" class="weather-display"></d613>
						
						<d116 id="d116" class="weather-display"></d116>
						<d216 id="d216" class="weather-display"></d216>
						<d316 id="d316" class="weather-display"></d316>
						<d416 id="d416" class="weather-display"></d416>
						<d516 id="d516" class="weather-display"></d516>
						<d616 id="d616" class="weather-display"></d616>

						<d119 id="d119" class="weather-display"></d119>
						<d219 id="d219" class="weather-display"></d219>
						<d319 id="d319" class="weather-display"></d319>
						<d419 id="d419" class="weather-display"></d419>
						<d519 id="d519" class="weather-display"></d519>
						<d619 id="d619" class="weather-display"></d619>

						<d122 id="d122" class="weather-display"></d122>
						<d222 id="d222" class="weather-display"></d222>
						<d322 id="d322" class="weather-display"></d322>
						<d422 id="d422" class="weather-display"></d422>
						<d522 id="d522" class="weather-display"></d522>
						<d622 id="d622" class="weather-display"></d622>
					</fcst>
				</div><!-- weather-results -->
			</div> <!-- openweathermap-accept-content -->

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
		AdHereAdHereAdHereAdHereAdHereAdHereAdHere
		</div>
		<div class="divtopmenuicons" style="margin-top: 50px;">This is Page Footer. I'm not sure what to put in here so it stays as is..</div>
	</div> 
     	
	<!-- top drop menu items container - populated dynamically by javascript -->
		<div id="topdropmenu" tabindex="0"> <!-- tabindex="0" is to allow <div> receive focus, don't know more about it... -->
 
	    <!-- menu items displayed in container.id="topdropmenu" -->
	    <!-- Note! All IDs here must correspond to "topmenutitles' IDs"+"-menuitem", otherwise it will display crap -->
			<div id="topmenutitle1-menuitem" class="topmenuitems" >This item is reserved for future use</div>

			<!-- Weather API -->
			<div id="topmenutitle2-menuitem" class="topmenuitems" >
				<div style="font-size: 1.0em;">This section is a result of my learning APIs in general and particularly weather APIs. I've made two separate implementations, OpenWeatherMap and DarkSky. I prefer the latter. You can access current weather and forecast data for any location on Earth including over 200,000 cities!<br>Current weather is frequently updated based on global models and data from more than 40,000 weather stations.<br><br></div>
				<input type="button" id="openweathermap-accept" class="userlinks" style="width: 200px;font-size: 1em;" value="Click for Open Weather Map" />
				<span style="display: inline-block;font-size: 1.0em;">Powered by <a href="https://openweathermap.org" target="_blank">OpenWeatherMap</a></span>
				<br><br>
				<input type="button" id="darksky-accept" class="userlinks" style="width: 200px;font-size: 1em;" value="Click for Dark Sky" />
				<span style="display: inline-block;font-size: 1.0em;">Powered by <a href="https://https://darksky.net" target="_blank">DarkSky API</a></span>
			</div>

			<div id="topmenutitle3-menuitem" class="topmenuitems" >This item is reserved for future use</div>

			<!-- Miscellaneous items -->
			<div id="topmenutitle4-menuitem" class="topmenuitems" >
				<div id="wordcrossyicon" class="divtopmenuicons inlineblock" title="Word Crossy">
					<a href="word_crossy.php"><img class="topmenuicon" src="img/wc.png" alt=""></a>
				</div>
				<div id="paralax" class="divtopmenuicons inlineblock" title="Parallax">
					<a href="paralax.php">Parallax</a>
				</div>
			</div>
	
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