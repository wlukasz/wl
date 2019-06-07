/*
js/functions.js
	
	Custom JavaScript Functions and Code
*/
	// global variables
 	var objInputs = []; // array to hold arrays of IDs & placeholders
	objInputs.arrIDs = []; // array to hold IDs
	objInputs.arrPlaceholders = []; // array hold to placeholders ( with sindex corresponding to IDs )

	var titleColorInitial = 'white';
	var titleColorHover = 'WhiteSmoke';
	var titleColorClicked = 'Silver';
	var topMenuDown = false;
	var menuToggledBy = 'dummymenuToggledBy';
	var VisibleMenuContentID = 'dummyVisibleMenuContentID';
	var VisibleContentID = 'homecontents';
	
	// this will execute on every page that loads js/cr_functions.js - i.e. this file.
	$( document ).ready( function() {
		
// --- Handling screen size ----------------------------------------------------------------------------------------------------------------- 
/* 
		landscape-px	width	height
		------------	-----	------
		iPhone 4s		480		320
		iPad			1024	768	
		iPad mini		1024	768
		HP Folio 13		1366	768
 */		
 		var screenWidth = screen.width;
		var screenHeight = screen.height;
		var screenZoom;
		if ( screenWidth < 500 ) {
			screenZoom = 0.5;
		} else if ( screenWidth < 800 ) { 
			screenZoom = 0.8;
		} else if ( screenWidth < 1000 ) { 
			screenZoom = 0.9;
		} else { 
			screenZoom = 1.0;
		}
		$( 'body' ).css( 'zoom', screenZoom );

// --- /Handling screen size ----------------------------------------------------------------------------------------------------------------- 

// ------------------------------------------------------------------------------------------------------------------------------------------
// Handle display of placeholders in input type text 

		// collect IDs of all input elements on the page and their placeholder values 
 		$( 'input' ).each( function() {
			objInputs.arrIDs.push( this.id );
			objInputs.arrPlaceholders.push( $( this ).attr( 'placeholder' ) );
		}); // each

		$( 'input' ).on( 'focus', function() {
			placeholder_remove ( this );
		}); 
		
		$( 'input' ).on( 'focusout', function() {
			placeholder_reinstate ( this );	
		}); 
 		
// /Handle display of placeholders in input type text 
// ------------------------------------------------------------------------------------------------------------------------------------------

// ------------------------------------------------------------------------------------------------------------------------------------------
// --- Handling top menu 
		
		/* on document ready */
		$( '#'+VisibleContentID ).css( 'display' , 'block' );
		
		$( '#homeicon' ).click(  function() { 
			$( '#'+VisibleContentID ).css( 'display' , 'none' );
			VisibleContentID = 'homecontents';
			$( '#'+VisibleContentID ).css( 'display' , 'block' );
			if ( topMenuDown === true ) {
				slide_up_topmenu ();
			}
		});

		// set initial background color for manu titles
	    $( '.topmenutitles' ).css( 'background-color' , titleColorInitial );

		$( '.topmenutitles' ).hover( 
			function() {
				$( this ).css( 'background-color' , titleColorHover );
			}
			,
			function() {
	 	 	    if ( this.id == menuToggledBy ) { // this menu title was clicked before and #topdropmenu is displayed - return it to clicked-color 
					$( this ).css( 'background-color' , titleColorClicked );
	 	 	    } else { // this item was not clicked - return to initial color
					$( this ).css( 'background-color' , titleColorInitial );
	 	 	    }
			}
		);

 	    $( '.topmenutitles' ).click( function() {
 	 	    var clickedMenuTitle = this;
 	 	    if ( clickedMenuTitle.id == menuToggledBy && topMenuDown === true ) { 
				slide_up_topmenu ();
				menuToggledBy = 'dummymenuToggledBy';
 	 	    } else { // menu items will be displayed
 	 	    	if ( clickedMenuTitle.id == menuToggledBy ) { 
	 	 	    	show_menu_items ( clickedMenuTitle );
	 	 	    } else {
					setTimeout( function() { 
		 	 	    	show_menu_items ( clickedMenuTitle );
		 			}, 300);
	 	 	    } 
	 	    }
		}); // .topmenutitles click

		$( '#topdropmenu' ).on( "focusout", function() {
		//		this is causing menu slide up and down when clicking the same menu button - focusout and click is fired simultaneously
		//      so we set a timeout and that fixes it
			setTimeout( function() { 
				slide_up_topmenu (); 
 			}, 200);
    	});
	
    	$(document).on( 'scroll', function() {
			slide_up_topmenu ();
    	});
		
// --- /Handling top menu 
// ------------------------------------------------------------------------------------------------------------------------------------------

	}); // document ready
		
// ------------------------------------------------------------------------------------------------------------------------------------------
// Global Functions
	

		function placeholder_remove ( objThis ) {
			$( objThis ).attr( 'placeholder', '' );	
			$( '#'+objThis.id+'-label' ).css( { opacity: 0.0 } ).animate( { opacity: 1.0 } );
		} // placeholder_remove
	
		function placeholder_reinstate ( objThis ) {
			if ( objThis.value.length == 0 ) {	
				for ( var i=0; i<objInputs.arrIDs.length; i++ ) {
					if ( objInputs.arrIDs[i] == objThis.id ) {
						$( objThis ).attr( 'placeholder', objInputs.arrPlaceholders[i] );
						$( '#'+objThis.id+'-label' ).css( { opacity: 1.0 } ).animate( { opacity: 0.0 } );
						break;
					}
				}
			}	
		} // placeholder_reinstate

 		function slide_up_topmenu () {
	 	    if ( topMenuDown === true ) {
				$( '#'+menuToggledBy ).css( 'background-color' , titleColorInitial );
				$( '#topdropmenu' ).slideUp( 'fast' );
				$( '#'+VisibleMenuContentID ).css( 'display' , 'none' );
				topMenuDown = false;
	 	    }
		} //slide_up_topmenu ()

		function show_menu_items ( clickedMenuTitle ) {
 			$( '#'+VisibleMenuContentID ).css( 'display' , 'none' );
			VisibleMenuContentID = clickedMenuTitle.id+'-menuitem';
			$( '#'+clickedMenuTitle.id+'-menuitem' ).css( 'display' , 'block' );
	 	    	$( '.topmenutitles' ).css( 'background-color' , titleColorInitial );
			$( clickedMenuTitle ).css( 'background-color' , titleColorClicked );
		
			// figure out position to display topdropmenu
//			var windowWidth = parseFloat( $( window ).width() );
//			var navBarWidth = parseFloat( $( '#navbar ').css( 'width' ).replace( 'px', '' ) ); // strip 'px' from width property
			var topMenuOlistWidth = parseFloat( $( '#topmenuolist' ).css( 'width' ).replace( 'px', '' ) ); // strip 'px' from width property
			var topMenuOlistHeight = parseFloat( $( '#topmenuolist' ).css( 'height' ).replace( 'px', '' ) ); // strip 'px' from height property

			// this is to handle window resizing and menutitles overflowing to next line(s)
			var adjuster;
			if ( topMenuOlistHeight < 50 ) { // menu is 1 line
				adjuster = 1;
			} else if ( topMenuOlistHeight < 100 ) { // menu is 2 lines
				adjuster = 2;
			} else if ( topMenuOlistHeight < 150 ) { // menu is 3 lines
				adjuster = 3;
	 	    	} else {
				adjuster = 4; // menu is >3 lines
	 	    	}
	 	    	
			var offset = $( clickedMenuTitle ).offset(); // object with properties .left & .top
			var leftPosition = offset.left;
			var scroll = $(window).scrollTop(); // top of scroll bar - scroll position
			var topPosition = offset.top + ( topMenuOlistHeight / adjuster ) - scroll;

			if ( leftPosition > ( topMenuOlistWidth / 2 ) ) { // element pos. more to the right of screen
				var titleWidth = parseFloat( $( clickedMenuTitle ).css( 'width' ).replace( 'px', '' ) );
				var menuWidth = parseFloat( $( '#topdropmenu' ).css( 'width' ).replace( 'px', '' ) );
				leftPosition = offset.left + titleWidth - menuWidth + 9;
			}

			$( '#topdropmenu' ).css( 'left' , ( leftPosition )+'px' );
			$( '#topdropmenu' ).css( 'top' , ( topPosition )+'px' );
			$( '#topdropmenu' ).slideDown( 'fast' );
			topMenuDown = true;
			menuToggledBy = clickedMenuTitle.id;
			$( '#topdropmenu' ).focus();
		} //show_menu_items ()
		
/*
		Toggles full screen view.
		elem can be ( document.body ) for full page or an id of any element i.e. ( document.getElementById( 'some-id' ) )
		use ( document.documentElement ) for full page - ( document.body ) works but produces no vertical scrollbar	
*/	
		function toggleFullScreen ( elem ) {
		  if ((document.fullScreenElement !== undefined && document.fullScreenElement === null) || (document.msFullscreenElement !== undefined && document.msFullscreenElement === null) || (document.mozFullScreen !== undefined && !document.mozFullScreen) || (document.webkitIsFullScreen !== undefined && !document.webkitIsFullScreen)) {
			  if (elem.requestFullScreen) {
				  elem.requestFullScreen();
			  } else if (elem.mozRequestFullScreen) {
		    	elem.mozRequestFullScreen();
			  } else if (elem.webkitRequestFullScreen) {
		    	elem.webkitRequestFullScreen(Element.ALLOW_KEYBOARD_INPUT);
			  } else if (elem.msRequestFullscreen) {
		    	elem.msRequestFullscreen();
			  }
		  } else {
			  if (document.cancelFullScreen) {
				  document.cancelFullScreen();
			  } else if (document.mozCancelFullScreen) {
				  document.mozCancelFullScreen();
			  } else if (document.webkitCancelFullScreen) {
				  document.webkitCancelFullScreen();
			  } else if (document.msExitFullscreen) {
				  document.msExitFullscreen();
			  }
		  }
		} // toggleFullScreen

// /Local Functions
// ------------------------------------------------------------------------------------------------------------------------------------------
		
