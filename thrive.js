/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * thrive implementation : © Philippe Dubrulle p.dubrulle@gmail.com
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * thrive.js
 *
 * thrive user interface script
 *
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */

define([
    "dojo","dojo/_base/declare",
    "ebg/core/gamegui",
    "ebg/counter"
],
function (dojo, declare) {
    return declare("bgagame.thrive", ebg.core.gamegui, {
        constructor: function(){
            console.log('thrive constructor');

            // Here, you can init the global variables of your user interface
            // Example:
            // this.myGlobalValue = 0;

			this.xOfSelectedPiece = -1;
			this.yOfSelectedPiece = -1;
			this.idOfSelectedPiece = -1;

			this.idOfFirstPlayer = -1;

        },

        /*
            setup:

            This method must set up the game user interface according to current game situation specified
            in parameters.

            The method is called each time the game interface is displayed to a player, ie:
            _ when the game starts
            _ when a player refreshes the game page (F5)

            "gamedatas" argument contains all datas retrieved by your "getAllDatas" PHP method.
        */

        setup: function( gamedatas )
        {
            console.log( "Starting game setup" );

            // Setting up player boards
/*
            for( var player_id in gamedatas.players )
            {
                var player = gamedatas.players[player_id];

				// TODO: Setting up players boards if needed
            }
*/
			this.idOfFirstPlayer = Object.keys(gamedatas.players)[0];

            // TODO: Set up your game interface here, according to "gamedatas"

			for( var i in gamedatas.pieces ) {
				var piece = gamedatas.pieces[ i ];
				if ( piece.alive == 1 ) {
					this.addPieceOnBoard( piece.piece_id, piece.x, piece.y, piece.player_id );
				}
				if ( piece.selected == 1 ) {
					dojo.addClass( 'piece_' + piece.piece_id, 'selectedPiece' )
					this.xOfSelectedPiece = piece.x;
					this.yOfSelectedPiece = piece.y;
					this.idOfSelectedPiece = piece.piece_id;
				}
				for( var j in gamedatas.pegs ) {
					var peg = gamedatas.pegs[ j ];
					if ( peg.piece_id == piece.piece_id ) {
						this.addPegOnPiece( piece.piece_id, peg.peg_index );
					}
				}
			}

            // Setup game notifications to handle (see "setupNotifications" method below)
            this.setupNotifications();
//			if ( this.isCurrentPlayerActive() ) {
				dojo.query( '.piece' ).connect( 'onclick', this, 'onSelectedPieceToMove' );
				dojo.query( '.square' ).connect( 'onclick', this, 'onSelectedWhereToMove' );
				dojo.query( '.peg_placement' ).connect( 'onclick', this, 'onSelectedWhereToPlacePeg' );
//			}

			if (this.player_id != this.idOfFirstPlayer) {
				dojo.query('#game_play_area').addClass("rotated");
			}

			console.log( "Ending game setup" );
        },

        ///////////////////////////////////////////////////
        //// Game & client states

        // onEnteringState: this method is called each time we are entering into a new game state.
        //                  You can use this method to perform some user interface changes at this moment.
        //
        onEnteringState: function( stateName, args )
        {
            console.log( 'Entering state: '+stateName );

            switch( stateName )
            {

            /* Example:

            case 'myGameState':

                // Show some HTML block at this game state
                dojo.style( 'my_html_block_id', 'display', 'block' );

                break;
           */
			case 'playerTurnSelectPieceToMove':
				if ( this.isCurrentPlayerActive() ) {
					this.updatePossiblePiecesToMove( args.args.playerPieces );
					this.cleanPossibleMoves( );
				}
				break;

			case 'playerTurnSelectLocationToMove':
				if ( this.isCurrentPlayerActive() ) {
					this.updatePossibleMoves( args.args.possibleMoves );
				}
				break;

			case 'playerTurnSelectPieceForPeg1Add':
				if ( this.isCurrentPlayerActive() ) {
					this.updatePossiblePiecesToMove( args.args.playerPieces );
					this.cleanPossiblePegs( );
				}
				break;

			case 'playerTurnSelectPegLocationForPeg1Add':
				if ( this.isCurrentPlayerActive() ) {

					this.updatePossiblePegs( args.args.possiblePegs, args.args.player );
				}
				break;

			case 'playerTurnSelectPieceForPeg2Add':
				if ( this.isCurrentPlayerActive() ) {
					this.updatePossiblePiecesToMove( args.args.playerPieces );
					this.cleanPossiblePegs( );
				}
				break;

			case 'playerTurnSelectPegLocationForPeg2Add':
				if ( this.isCurrentPlayerActive() ) {
					this.updatePossiblePegs( args.args.possiblePegs, args.args.player );
				}
				break;

            case 'dummmy':
                break;
            }
        },

        // onLeavingState: this method is called each time we are leaving a game state.
        //                 You can use this method to perform some user interface changes at this moment.
        //
        onLeavingState: function( stateName )
        {
            console.log( 'Leaving state: '+stateName );

            switch( stateName )
            {

            /* Example:

            case 'myGameState':

                // Hide the HTML block we are displaying only during this game state
                dojo.style( 'my_html_block_id', 'display', 'none' );

                break;
           */
			case 'playerTurnSelectPieceToMove':
				if ( this.isCurrentPlayerActive() ) {
					this.cleanPossiblePiecesToMove( );
				}
				break;

			case 'playerTurnSelectLocationToMove':
				if ( this.isCurrentPlayerActive() ) {
					this.cleanPossibleMoves( );
				}
				break;

			case 'playerTurnSelectPieceForPeg1Add':
				if ( this.isCurrentPlayerActive() ) {
					this.cleanPossiblePiecesToMove( );
				}
				break;

			case 'playerTurnSelectPegLocationForPeg1Add':
				if ( this.isCurrentPlayerActive() ) {
					this.cleanPossiblePegs( );
				}
				break;

			case 'playerTurnSelectPieceForPeg2Add':
				if ( this.isCurrentPlayerActive() ) {
					this.cleanPossiblePiecesToMove( );
				}
				break;

			case 'playerTurnSelectPegLocationForPeg2Add':
				if ( this.isCurrentPlayerActive() ) {
					this.cleanPossiblePegs( );
				}
				break;

            case 'dummmy':
                break;
            }
        },

        // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
        //                        action status bar (ie: the HTML links in the status bar).
        //
        onUpdateActionButtons: function( stateName, args )
        {
            console.log( 'onUpdateActionButtons: '+stateName );

            if( this.isCurrentPlayerActive() )
            {
                switch( stateName )
                {
/*
                 Example:

                 case 'myGameState':

                    // Add 3 action buttons in the action status bar:

                    this.addActionButton( 'button_1_id', _('Button 1 label'), 'onMyMethodToCall1' );
                    this.addActionButton( 'button_2_id', _('Button 2 label'), 'onMyMethodToCall2' );
                    this.addActionButton( 'button_3_id', _('Button 3 label'), 'onMyMethodToCall3' );
                    break;
*/
				case 'playerTurnSelectLocationToMove':
				case 'playerTurnSelectPegLocationForPeg1Add':
				case 'playerTurnSelectPegLocationForPeg2Add':
					this.addActionButton('button_cancelSelection', _('Unselect'), 'onCancelPieceSelection');
					break;
					case 'playerTurnSelectPieceToMove':
					// @TODO No move is allowed only if there are no available moves. Implement!
					// this.addActionButton('button_cancelMove', _('No move'), 'onPassMovePiece');
					break;
				case 'playerTurnSelectPieceForPeg1Add':
					case 'playerTurnSelectPieceForPeg2Add':
					// @TODO Skip to next player automatically if there are no peg placement spaces available.
					// this.addActionButton('button_cancelPlacePeg', _('No peg'), 'onPassPlacePeg');
					break;
				case 'playerConfirmTurnEnd':
					this.addActionButton('button_undoMove', _('Undo'), 'onUndoResetTurn');
					this.addActionButton('button_turnEnd', _('End Turn'), 'onConfirmEndTurn');
					break;
                }
            }
        },

		onUndoResetTurn: function() {
			console.log('Undo it!');
		},

		onConfirmEndTurn: function() {
        	console.log('confirm it!');
		},

        ///////////////////////////////////////////////////
        //// Utility methods

        /*

            Here, you can defines some utility methods that you can use everywhere in your javascript
            script.

        */

		addPieceOnBoard: function( id, x, y, player ) {
			dojo.place( this.format_block( 'jstpl_piece', {
				id: id,
				color: this.gamedatas.players[ player ].color
			} ) , 'pieces' );

			this.placeOnObject( 'piece_' + id, 'overall_player_board_' + player );
			this.slideToObject( 'piece_' + id, 'square_' + x + '_' + y ).play();
		},

		addPegOnPiece: function( piece_id, peg_index ) {
			dojo.place( this.format_block( 'jstpl_peg', {
				id: piece_id + "_" + peg_index
			} ) , 'piece_' + piece_id );
			x=0;
			y=0;
			switch( peg_index ) {
				case "0": x = -2; y = -2 ; break;
				case "1": x = -1; y = -2 ; break;
				case "2": x =  0; y = -2 ; break;
				case "3": x =  1; y = -2 ; break;
				case "4": x =  2; y = -2 ; break;
				case "5": x = -2; y = -1 ; break;
				case "6": x = -1; y = -1 ; break;
				case "7": x =  0; y = -1 ; break;
				case "8": x =  1; y = -1 ; break;
				case "9": x =  2; y = -1 ; break;
				case "10": x = -2; y = 0 ; break;
				case "11": x = -1; y = 0 ; break;
				case "12": x =  0; y = 0 ; break;
				case "13": x =  1; y = 0 ; break;
				case "14": x =  2; y = 0 ; break;
				case "15": x = -2; y = 1 ; break;
				case "16": x = -1; y = 1 ; break;
				case "17": x =  0; y = 1 ; break;
				case "18": x =  1; y = 1 ; break;
				case "19": x =  2; y = 1 ; break;
				case "20": x = -2; y = 2 ; break;
				case "21": x = -1; y = 2 ; break;
				case "22": x =  0; y = 2 ; break;
				case "23": x =  1; y = 2 ; break;
				case "24": x =  2; y = 2 ; break;
			}
			hor_scale = 11.0;
			ver_scale = 11.0;
			posx = Math.round( x * hor_scale );
			posy = Math.round( y * ver_scale );
			this.placeOnObjectPos( 'peg_' + piece_id + "_" + peg_index, 'piece_' + piece_id, posx, posy );
		},

		updatePossiblePiecesToMove: function( playerPieces ) {
			dojo.query( '.square_clickable' ).removeClass( 'square_clickable' ).addClass('square');
			for( var id in playerPieces ) {
				x = playerPieces[ id ][ "x" ];
				y = playerPieces[ id ][ "y" ];
				// x,y is a possible move
				dojo.addClass( 'piece_' + id, 'possiblePieceToMove' );
			}
		},

		cleanPossiblePiecesToMove: function( ) {
			// Remove current selected pieces
            console.log( 'function cleanPossiblePiecesToMove called' );
			dojo.query( '.possiblePieceToMove' ).removeClass( 'possiblePieceToMove' );
		},

		updatePossibleMoves: function( possibleMoves ) {
			dojo.query( '.square' ).addClass( 'square_clickable' ).removeClass('square');

			for( var x in possibleMoves ) {
				for( var y in possibleMoves[ x ] ) {
					// x,y is a possible move
					dojo.addClass( 'square_' + x + '_' + y, 'possibleMove' );
				}
			}
		},

		cleanPossibleMoves: function( ) {
			// Remove current possible moves
            console.log( 'function cleanPossibleMoves called' );
			dojo.query( '.possibleMove' ).removeClass( 'possibleMove' );
		},

		updatePossiblePegs: function ( possiblePegs, player ) {
			dojo.style( "div-peg-placement", "display", "block" );
			dojo.query( '#piece_for_peg_placement' ).addClass( 'piece_' + this.gamedatas.players[ player ].color + '_for_peg_placement' );
			for ( var peg in possiblePegs ) {
				dojo.query( '#peg_placement_' + peg ).addClass( 'possible_peg_placement' );
			}
		},

		cleanPossiblePegs: function( ) {
			// Remove current possible peg placements
			dojo.style( "div-peg-placement", "display", "none" );
			for ( var playr in this.gamedatas.players )
				dojo.query( '#piece_for_peg_placement' ).removeClass( 'piece_' + this.gamedatas.players[ playr ].color + '_for_peg_placement' );
			dojo.query( '.possible_peg_placement' ).removeClass( 'possible_peg_placement' );
		},


        ///////////////////////////////////////////////////
        //// Player's action

        /*

            Here, you are defining methods to handle player's action (ex: results of mouse click on
            game objects).

            Most of the time, these methods:
            _ check the action is possible at this game state.
            _ make a call to the game server

        */

        /* Example:

        onMyMethodToCall1: function( evt )
        {
            console.log( 'onMyMethodToCall1' );

            // Preventing default browser reaction
            dojo.stopEvent( evt );

            // Check that this action is possible (see "possibleactions" in states.inc.php)
            if( ! this.checkAction( 'myAction' ) )
            {   return; }

            this.ajaxcall( "/thrive/thrive/myAction.html", {
                                                                    lock: true,
                                                                    myArgument1: arg1,
                                                                    myArgument2: arg2,
                                                                    ...
                                                                 },
                         this, function( result ) {

                            // What to do after the server call if it succeeded
                            // (most of the time: nothing)

                         }, function( is_error) {

                            // What to do after the server call in anyway (success or failure)
                            // (most of the time: nothing)

                         } );
        },

        */

		onSelectedPieceToMove: function( evt ) {
			var currentAction;
			var action;
			if( this.checkAction( 'selectPieceToMove', false ) ) {
				currentAction = 'selectPieceToMove';
				action = 'moving';
			}
			else if ( this.checkAction( 'selectPieceForPeg1', false ) ) {
				currentAction = 'selectPieceForPeg1';
				action = 'placing peg 1';
			}
			else if ( this.checkAction( 'selectPieceForPeg2', false ) ) {
				currentAction = 'selectPieceForPeg2';
				action = 'placing peg 2';
			} else return; // check that this action is possible at this moment

			console.log( 'function onSelectedPieceToMove called (for ' + action + ')' );

			// Stop this event propagation
			dojo.stopEvent( evt );

			// Get the clicked piece
			// Note: piece id format is "piece_ID"
			var coords = evt.currentTarget.id.split('_');
			var id = coords[1];
			this.idOfSelectedPiece = id;
			if( ! dojo.hasClass( 'piece_' + this.idOfSelectedPiece, 'possiblePieceToMove' ) ) {
				// this is not a possible move => the click does nothing
				console.log( 'Impossible move' );
				return;
			}

			if (currentAction == 'selectPieceToMove')
				this.ajaxcall( "/thrive/thrive/selectPieceToMove.html", {
									id: id,
									check: "selectPieceToMove"
								}, this, function( result ) { }, );
			else if (currentAction == 'selectPieceForPeg1')
				this.ajaxcall( "/thrive/thrive/selectPieceToMove.html", {
									id: id,
									check: "selectPieceForPeg1"
								}, this, function( result ) { }, );
			else if (currentAction == 'selectPieceForPeg2')
				this.ajaxcall( "/thrive/thrive/selectPieceToMove.html", {
									id: id,
									check: "selectPieceForPeg2"
								}, this, function( result ) { }, );
		},

		onCancelPieceSelection: function( evt ) {
			if( this.checkAction( 'selectMoveLocation', false ) || this.checkAction( 'selectPeg1Location', false ) || this.checkAction( 'selectPeg2Location', false ) ) { // check that this action is possible at this moment
				console.log( 'function onCancelPieceSelection called' );

				// Stop this event propagation
				dojo.stopEvent( evt );

				// unselect piece
				this.idOfSelectedPiece = -1;
//				this.cleanPossiblePiecesToMove( );
//				this.cleanPossibleMoves( );

				var currentAction;
				if( this.checkAction( 'selectMoveLocation', false ) )
					currentAction = 'selectMoveLocation';
				else if ( this.checkAction( 'selectPeg1Location', false ) )
					currentAction = 'selectPeg1Location';
				else if ( this.checkAction( 'selectPeg2Location', false ) )
					currentAction = 'selectPeg2Location';

				this.ajaxcall( "/thrive/thrive/cancelPieceSelection.html", {
									check: currentAction
								}, this, function( result ) { }, );
			}
		},

		onSelectedWhereToMove: function( evt ) {
			console.log( 'function onSelectedWhereToMove called' );

			// Stop this event propagation
			dojo.stopEvent( evt );

			// Get the cliqued square (X and Y) or piece (ID)
			// Note: square id format is "square_X_Y"
			// Note: piece id format is "piece_ID"
			var coords = evt.currentTarget.id.split('_');
			var x = coords[1];
			var y = coords[2];

			if( ! dojo.hasClass( 'square_' + x + '_' + y, 'possibleMove' ) ) {
				// this is not a possible move => the click does nothing
				console.log( 'Impossible move' );
				return;
			}

			if( this.checkAction( 'selectMoveLocation' ) ) { // check that this action is possible at this moment
				this.ajaxcall( "/thrive/thrive/selectMoveLocation.html", {
									x: x,
									y: y
								}, this, function( result ) { }, );
			}

		},

		onPassMovePiece: function( evt ) {
			if( this.checkAction( 'selectPieceToMove' ) ) { // check that this action is possible at this moment
				console.log( 'function onPassMovePiece called' );

				// Stop this event propagation
				dojo.stopEvent( evt );

				var currentAction = 'selectPieceToMove';

				this.ajaxcall( "/thrive/thrive/passMovePiece.html", {
									check: currentAction
								}, this, function( result ) { }, );
			}
		},

		onSelectedWhereToPlacePeg: function( evt ) {
			console.log( 'function onSelectedWhereToPlacePeg called' );

			// Stop this event propagation
			dojo.stopEvent( evt );

			// Get the clicked peg_placement (peg_index)
			// Note: peg_index is between 0 and 24
			var coords = evt.currentTarget.id.split('_');
			var peg_index = coords[2];


			if( ! dojo.hasClass( 'peg_placement_' + peg_index, 'possible_peg_placement' ) ) {
				// this is not a possible move => the click does nothing
				console.log( 'Impossible move' );
				return;
			}

			if( this.checkAction( 'selectPeg1Location', false ) ) { // check that this action is possible at this moment
				this.ajaxcall( "/thrive/thrive/selectPegLocation.html", {
									peg_index: peg_index,
									check: "selectPeg1Location"
								}, this, function( result ) { }, );
			} else if( this.checkAction( 'selectPeg2Location', false ) ) { // check that this action is possible at this moment
				this.ajaxcall( "/thrive/thrive/selectPegLocation.html", {
									peg_index: peg_index,
									check: "selectPeg2Location"
								}, this, function( result ) { }, );
			}

		},

		onPassPlacePeg: function( evt ) {
			if( this.checkAction( 'selectPieceForPeg1', false ) || this.checkAction( 'selectPieceForPeg2', false ) ) { // check that this action is possible at this moment
				console.log( 'function onPassPlacePeg called' );

				// Stop this event propagation
				dojo.stopEvent( evt );

				// unselect piece
				this.idOfSelectedPiece = -1;
//				this.cleanPossiblePiecesToMove( );
//				this.cleanPossibleMoves( );

				var currentAction;
				if( this.checkAction( 'selectPieceForPeg1', false ) )
					currentAction = 'selectPieceForPeg1';
				else if ( this.checkAction( 'selectPieceForPeg2', false ) )
					currentAction = 'selectPieceForPeg2';

				this.ajaxcall( "/thrive/thrive/passPlacePeg.html", {
									check: currentAction
								}, this, function( result ) { }, );
			}
		},


        ///////////////////////////////////////////////////
        //// Reaction to cometD notifications

        /*
            setupNotifications:

            In this method, you associate each of your game notifications with your local method to handle it.

            Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" calls in
                  your thrive.game.php file.

        */
        setupNotifications: function()
        {
            console.log( 'notifications subscriptions setup' );

            // TODO: here, associate your game notifications with local methods

            // Example 1: standard notification handling
            // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );

            // Example 2: standard notification handling + tell the user interface to wait
            //            during 3 seconds after calling the method in order to let the players
            //            see what is happening in the game.
            // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );
            // this.notifqueue.setSynchronous( 'cardPlayed', 3000 );
            //
			dojo.subscribe( 'selectedPieceToMove', this, "notif_selectedPieceToMove" );
			dojo.subscribe( 'cancelPieceSelection', this, "notif_cancelPieceSelection" );
			dojo.subscribe( 'selectedMoveLocation', this, "notif_selectedMoveLocation" );
			this.notifqueue.setSynchronous( 'selectedMoveLocation', 2000 );
			dojo.subscribe( 'capturedPiece', this, "notif_capturedPiece" );
			dojo.subscribe( 'selectedPegLocation', this, "notif_selectedPegLocation" );
        },

        // TODO: from this point and below, you can write your game notifications handling methods

        /*
        Example:

        notif_cardPlayed: function( notif )
        {
            console.log( 'notif_cardPlayed' );
            console.log( notif );

            // Note: notif.args contains the arguments specified during you "notifyAllPlayers" / "notifyPlayer" PHP call

            // TODO: play the card in the user interface.
        },

        */
/*
dump: function(obj) {
    var out = '';
    for (var i in obj) {
        out += i + ": " + obj[i] + "\n";
    }

    alert(out);

    // or, if you wanted to avoid alerts...

    var pre = document.createElement('pre');
    pre.innerHTML = out;
    document.body.appendChild(pre)
}
*/
		notif_selectedPieceToMove: function( notif ) {
			// Remove current possible moves
			dojo.query( '.possiblePieceToMove' ).removeClass( 'possiblePieceToMove' );
			// mark the selected piece
			dojo.addClass( 'piece_' + notif.args.id, 'selectedPiece');
			this.xOfSelectedPiece = notif.args.x;
			this.yOfSelectedPiece = notif.args.y;
			this.idOfSelectedPiece = notif.args.id;
		},

		notif_selectedMoveLocation: function( notif ) {
			// Turn game board for second player so that their pieces be at the bottom and move upwards.
			if (this.player_id != this.idOfFirstPlayer) {
				dojo.query('#game_play_area').removeClass("rotated");
			}

			this.slideToObject( 'piece_' + this.idOfSelectedPiece, 'square_' + notif.args.x + '_' + notif.args.y ).play();

			if (this.player_id != this.idOfFirstPlayer) {
				dojo.query('#game_play_area').addClass("rotated");
			}

			this.xOfSelectedPiece = -1;
			this.yOfSelectedPiece = -1;
			this.idOfSelectedPiece = -1;

			dojo.query( '.possibleMove' ).removeClass( 'possibleMove' );
			dojo.query( '.selectedPiece' ).removeClass( 'selectedPiece' );
		},

		notif_cancelPieceSelection: function( notif ) {
			dojo.query( '.pegPlacement' ).removeClass( 'pegPlacement' );
			dojo.query( '.possibleMove' ).removeClass( 'possibleMove' );
			dojo.query( '.selectedPiece' ).removeClass( 'selectedPiece' );
		},

		notif_capturedPiece: function( notif ) {
			this.fadeOutAndDestroy( "piece_" + notif.args.id );
		},

		notif_selectedPegLocation: function( notif ) {
			if (this.player_id != this.idOfFirstPlayer) {
				dojo.query('#game_play_area').removeClass("rotated");
			}

			this.addPegOnPiece( notif.args.piece_id, notif.args.peg_index );
			dojo.query( '.pegPlacement' ).removeClass( 'pegPlacement' );

			if (this.player_id != this.idOfFirstPlayer) {
				dojo.query('#game_play_area').addClass("rotated");
			}

			this.xOfSelectedPiece = -1;
			this.yOfSelectedPiece = -1;
			this.idOfSelectedPiece = -1;

			dojo.query( '.possible_peg_placement' ).removeClass( 'possible_peg_placement' );
			dojo.query( '.selectedPiece' ).removeClass( 'selectedPiece' );
		},
   });
});
