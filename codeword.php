<?php
# codeword.php

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
			case "go":
				$lettercount = $_POST['lettercount'];
				$allletters = $_POST['allletters'];
				$array = str_split ( strtoupper( $allletters ) );
				$knownletters = json_decode( strtoupper( $_POST['knownletters'] ), true );
				
				$dups = array();
				$collect = array();
				$list = array();
				depth_picker($array, "", $collect);
				$tot = 0;
				
				for ( $i=0; $i<sizeof( $collect );$i++ ) {
					if ( strlen($collect[$i]) == $lettercount) {
						
						$letter_matched = TRUE;
						if ( count( $knownletters ) > 0 ) {
							foreach ($knownletters as $key => $value) {
								foreach ( $value as $pos => $letter ) {
									if ( substr( $collect[$i], $pos, 1 ) != $letter ) {
										$letter_matched = FALSE;
										break;
									}
								}
							}
						}
						
						$dup = FALSE;
						if ( $letter_matched ) {
							foreach ( $dups as $key => $value ) {
								if ( $collect[$i] == $value ) {
									$dup = TRUE;
									break;
								}
							}
							
							if ( !$dup ) {
								$sql = "SELECT word FROM tbl_en_words WHERE word = ?";
								$params = array( $collect[$i] ); //if no params then must be "array()", otherwise values comma separated
								$pdo_return_data = pdo_db_call ( __FUNCTION__, 1, $db, $sql, $params, 'fetch', 2 );
								if ( $pdo_return_data['rc'] == '1' ) {
									if ( $pdo_result = $pdo_return_data['result'] ) { // word returned - match
										array_push($list, $collect[$i]);
										$tot++;
									}
								} else {
									$return_array['rc'] = '0';
									$return_array['errmsg'] = 'System Error.\nCould not retrieve word.';
									die ($return_data = json_encode($return_array));
								}
							}
							array_push($dups, $collect[$i]);
						}
					}
				}
				
				$return_array['list'] = $list;
				$return_array['tot'] = $tot;
				$return_array['rc'] = "1";
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

	function depth_picker($arr, $temp_string, &$collect) {
		if ($temp_string != "")
			$collect []= $temp_string;
	
		for ($i=0; $i<sizeof($arr);$i++) {
			$arrcopy = $arr;
			$elem = array_splice($arrcopy, $i, 1); // removes and returns the i'th element
			if (sizeof($arrcopy) > 0) {
				depth_picker($arrcopy, $temp_string  . $elem[0], $collect);
			} else {
				$collect []= $temp_string . $elem[0];
			}
		}
	}
	
# end-of-ajax
#############

?>
<!doctype html>
<html>
<head>
	<title>WordCrossy</title>
    <meta charset="utf-8">
<!--     <meta name="viewport" content="width=device-width, initial-scale=1">
 -->    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/> 
	<?php include("inc/css.inc"); ?>
<style>

#wc-page {
   background: white;
   padding: 5px;
   font-family: Verdana, sans-serif;
}   

#wordlengthdiv select {
   background: transparent;
   width: 500px;
   padding: 5px;
   font-size: 20px;
   line-height: 1;
   border: 0;
   border-radius: 0;
   height: 30px;
   float: right;
   -webkit-appearance: none;
}
   
input[type=text], input[type=submit] {
    border: 5px solid royal-blue; 
    -webkit-box-shadow: 
      inset 0 0 8px  rgba(0,0,0,0.1),
            0 0 16px rgba(0,0,0,0.1); 
    -moz-box-shadow: 
      inset 0 0 8px  rgba(0,0,0,0.1),
            0 0 16px rgba(0,0,0,0.1); 
    box-shadow: 
      inset 0 0 8px  rgba(0,0,0,0.1),
            0 0 16px rgba(0,0,0,0.1); 
    padding: 15px;
    background: rgba(255,255,255,0.5);
    margin: 0 0 10px 0;
    font-size: 20px;
    color: royal-blue;
	line-height: 1;
}
   
</style>
	<?php include("inc/js.inc"); ?>
<script type="text/javascript">
	var objKL = [];
	var strAllLetters;	
	var strLetterToReturn;	
	var strAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$( document ).ready( function() {

		// init
		$( '#gotlettersdiv' ).hide();
		$( '#wordlengthdiv' ).hide();
		$( '#donebutton' ).hide();

		$( '#word-length' ).focusin( function() {
			$( '#allletters' ).val( strAllLetters );
		});	

		$( '#word-length' ).change( function() {
			$( '#wordlist' ).html('');
			$( '#gotlettersdiv' ).show();
			$( '#gotlettersdiv' ).html("");
			// create 1 box per letter and ask for inserting known letters
			$( '#gotlettersdiv' ).html('Enter known letters in their positions in boxes below<br><div id="knownlettersdiv"></div>');
			objKL = [];

			for ( var i=0; i<$( '#word-length' ).val(); i++ ) {
				$( '#knownlettersdiv' ).append( '<input type="text" maxlength="1" size="2" id="'+i+'" class="knownletters" value=""/>' );
				objKL.push( {
					pos: i,
					val: ""
				});
			}
		
			$( '#knownlettersdiv' ).append( '<input type="submit" style="padding: 5px;" id="gobutton" value="GO!"/>' );
		});	
		
		 $( document ).on("focusin", '#allletters', function() {
			$( '#wordlist' ).html('');
		});	

		// need to bind dynamic textinput '#allletters' to parent, hence 'document' is used as selector
		 $( document ).on("input", '#allletters', function() {
			objKL = [];
			$( '#allletters' ).val( $( '#allletters' ).val().toUpperCase() );
			$( '#gotlettersdiv' ).hide();
			$( '#wordlengthdiv' ).hide();
			$( '#word-length' ).val('');
			$( '#wordlist' ).html('');
			
			if ( $( '#allletters' ).val().length > 2 ) {
				$( '#donebutton' ).fadeIn();
			} else {
				$( '#donebutton' ).fadeOut();
			}
		});	
			
		$( document ).on("click", '#alphabet', function() {
			$( '#allletters' ).val( strAlphabet );
			$( '#donebutton' ).fadeIn();
		});	
			
		$( document ).on("click", '#donebutton', function() {
			strAllLetters =  $( '#allletters' ).val();
			$('#word-length').find('option').remove().end();
			var options = [];
	        options.push('<option value="" disabled selected>How many letters in the word? Pick a number here!</option>');
			for ( var i=3; i<=$( '#allletters' ).val().length; i++ ) {
		        options.push('<option value="'+i+'">'+i+'</option>');
			}
			// append options to select menu 
			$( '#word-length' ).append(options.join(""));
			// set initial val="" (placeholder) - no options selected
			$( "#word-length" ).val("");
			
			$( '#wordlengthdiv' ).show();
			$( '#donebutton' ).fadeOut();
		});	

		$( document ).on("input", '.knownletters', function() {
			$( '#wordlist' ).html('');
			if ( this.value  == "" ) {
	        	$( '#allletters' ).val( $( '#allletters' ).val()+objKL[this.id] );
				objKL[this.id] = "";
			} else {
				if ( strLetterToReturn > "" ) {
		        	$( '#allletters' ).val( $( '#allletters' ).val()+strLetterToReturn );
				}
				var letterFound = false;
				this.value = this.value.toUpperCase();
				for ( var i=0; i<$( '#allletters' ).val().length; i++ ) {
			        if ( $( '#allletters' ).val().charAt(i) == this.value ) {
			        	letterFound = true;
			        	objKL[this.id] = $( '#allletters' ).val().charAt(i); 
			        	$( '#allletters' ).val( $( '#allletters' ).val().replace( $( '#allletters' ).val().charAt(i), '' ) );
			        	break;
			        } 
				}
				if ( this.value > "" && letterFound == false ) {
					alert ('Letter "'+this.value+'" is not available to choose');
					this.value = "";
				}
			}
		});	

		$( document ).on("dblclick", '.knownletters', function() {
			if ( this.value  > "" ) {
				strLetterToReturn = this.value;
			}
		});	

		$( document ).on("mousedown", '.knownletters', function() {
			strLetterToReturn = "";
		});	

		$( document ).on("click", '#gobutton', function() {
			var arr_knownletters = $(".knownletters").map(function() {
				if ( this.value > '' ) {
				    return '{"'+this.id+'" : "'+this.value+'"}';
				}
			}).get().join(",");
			arr_knownletters = "["+arr_knownletters+"]";

			document.getElementById("logo").src="img/word-loader.gif";
			var callData = "ajax=<?php echo $_SERVER['PHP_SELF'];?>&action=go&lettercount="+$( '#word-length' ).val()+"&allletters="+strAllLetters+"&knownletters="+arr_knownletters;

			$.ajax({ // ### AJAX this is to do database business ###
			    url:"<?php echo $_SERVER['PHP_SELF'];?>",
			    type:"POST",
			    data : callData,
			    success:function(msg){
				    msg = JSON.parse(msg);
					if ( msg.rc == "1" ) {
						$( '#wordlist' ).html('');
						$( '#wordlist' ).html( $( '#wordlist' ).html()+msg.tot+" possible solutions<br>" );
						for ( var i=0; i<msg.tot; i++ ) {
							$( '#wordlist' ).html( $( '#wordlist' ).html()+'<input type="text" readonly="readonly" value="'+msg.list[i]+'"/><br>' );
						}
					} else {
						alert ( msg.errmsg );
					}
					document.getElementById("logo").src="img/wc_big.png";
			    },
			    error: function (jqXHR, textStatus, errorThrown) {
					alert("Network Error! "+msg.errmsg);
					document.getElementById("logo").src="img/wc_big.png";
			    },
			});  // ### AJAX this is to do database business ###
		});
		
	}); // document ready
	
</script>
</head>
<body id="thebody">
<!-- wc-page -->
    <div data-role="page" id="wc-page">
  		<div id="wordcrossylogo" class="inlineblock" title="Back Home">
 			<a href="index.php"><img id="logo" src="img/wc_big.png" alt=""></a>
 		</div>
  		<div id="user-inputs" style="vertical-align: top;" class="inlineblock">
	  		<div style="top: 5px;" id="alllettersdiv">
				<input type="text" style="width: 600px;" maxlength="40" id="allletters" placeholder="type available letters here or select ALPHABET" value=""/>
				<input type="submit" style="padding: 5px;" id="donebutton" value="DONE!"/>
				<input type="submit" style="padding: 5px;" id="alphabet" value="ALPHABET"/>
	 		</div>
	  		<div id="wordlengthdiv">
	 			<select id="word-length"></select>
	 		</div>
	  		<div id="gotlettersdiv"></div>
 		</div>
		<div id="wordlist"></div>
	</div> <!-- /wc-page -->
 
</body>
</html>