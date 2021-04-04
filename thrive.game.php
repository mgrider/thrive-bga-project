<?php
 /**
  *------
  * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
  * thrive implementation : © Philippe Dubrulle p.dubrulle@gmail.com
  *
  * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
  * See http://en.boardgamearena.com/#!doc/Studio for more information.
  * -----
  *
  * thrive.game.php
  *
  * This is the main file for your game logic.
  *
  * In this PHP file, you are going to defines the rules of the game.
  *
  */


require_once( APP_GAMEMODULE_PATH.'module/table/table.game.php' );

// Define game states.
define( 'STATE_GAME_SETUP', 1 );
define( 'STATE_PLAYER_TURN_START', 2 );
define( 'STATE_PLAYER_TURN_SELECT_PIECE', 20 );
define( 'STATE_PLAYER_TURN_SELECT_PIECE_LOCATION', 30 );
define( 'STATE_PLAYER_TURN_SELECT_PEG1_PIECE', 40 );
define( 'STATE_PLAYER_TURN_SELECT_PEG1_LOCATION', 50 );
define( 'STATE_PLAYER_TURN_SELECT_PEG2_PIECE', 60 );
define( 'STATE_PLAYER_TURN_SELECT_PEG2_LOCATION', 70 );
define( 'STATE_PLAYER_CONFIRM_TURN_END', 75 );
define( 'STATE_PLAYER_TURN_END', 80 );
define( 'STATE_GAME_END', 99 );


class thrive extends Table
{
	function __construct( )
	{
        // Your global variables labels:
        //  Here, you can assign labels to global variables you are using for this game.
        //  You can use any number of global variables with IDs between 10 and 99.
        //  If your game has options (variants), you also have to associate here a label to
        //  the corresponding ID in gameoptions.inc.php.
        // Note: afterwards, you can get/set the global variables with getGameStateValue/setGameStateInitialValue/setGameStateValue
        parent::__construct();

        self::initGameStateLabels( array(
            //    "my_first_global_variable" => 10,
            //    "my_second_global_variable" => 11,
            //      ...
            //    "my_first_game_variant" => 100,
            //    "my_second_game_variant" => 101,
            //      ...
			"selectedPieceID" => 10
        ) );
	}

    protected function getGameName( )
    {
		// Used for translations and stuff. Please do not modify.
        return "thrive";
    }

    /*
        setupNewGame:

        This method is called only once, when a new game is launched.
        In this method, you must setup the game according to the game rules, so that
        the game is ready to be played.
    */
    protected function setupNewGame( $players, $options = array() )
    {
        // Set the colors of the players with HTML color code
        // The default below is red/green/blue/orange/brown
        // The number of colors defined here must correspond to the maximum number of players allowed for the gams
        $gameinfos = self::getGameinfos();
        // $default_colors = $gameinfos['player_colors'];
		$default_colors = [ "4fb0bd", "ffffff" ];

        // Create players
        // Note: if you added some extra field on "player" table in the database (dbmodel.sql), you can initialize it there.
        $sql = "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar) VALUES ";
        $values = array();
        foreach( $players as $player_id => $player )
        {
            $color = array_shift( $default_colors );
            $values[] = "('".$player_id."','$color','".$player['player_canal']."','".addslashes( $player['player_name'] )."','".addslashes( $player['player_avatar'] )."')";
        }
        $sql .= implode( $values, ',' );
        self::DbQuery( $sql );
        //self::reattributeColorsBasedOnPreferences( $players, $gameinfos['player_colors'] );
        self::reloadPlayersBasicInfos();

        /************ Start the game initialization *****/

        // Init global values with their initial values
        //self::setGameStateInitialValue( 'my_first_global_variable', 0 );
        self::setGameStateInitialValue( 'selectedPieceID', -1 );

        // Init game statistics
        // (note: statistics used in this file must be defined in your stats.inc.php file)
        //self::initStat( 'table', 'table_teststat1', 0 );    // Init a table statistics
        //self::initStat( 'player', 'player_teststat1', 0 );  // Init a player statistics (for all players)

        // TODO: setup the initial game situation here

		/************ Start the game initialization *****/
		list( $blueplayer_id, $whiteplayer_id ) = array_keys( $players );

		$sql = "INSERT INTO piece (piece_id, player_id, piece_x, piece_y) VALUES ";
		$sql .= "(0 , $blueplayer_id, 0, 0),";
		$sql .= "(1 , $blueplayer_id, 1, 0),";
		$sql .= "(2 , $blueplayer_id, 2, 0),";
		$sql .= "(3 , $blueplayer_id, 3, 0),";
		$sql .= "(4 , $blueplayer_id, 4, 0),";
		$sql .= "(5 , $blueplayer_id, 5, 0),";
		$sql .= "(6 , $whiteplayer_id, 0, 5),";
		$sql .= "(7 , $whiteplayer_id, 1, 5),";
		$sql .= "(8 , $whiteplayer_id, 2, 5),";
		$sql .= "(9 , $whiteplayer_id, 3, 5),";
		$sql .= "(10 ,$whiteplayer_id, 4, 5),";
		$sql .= "(11 ,$whiteplayer_id, 5, 5)";
		self::DbQuery( $sql );

		$sql = "INSERT INTO peg (piece_id, peg_index) VALUES ";
		$sql_values = array();
		for( $p=0; $p<=5; $p++ ) {
			$sql_values[] = "('$p', 17)";
		}
		for ($p = 6; $p <= 11; $p++ ) {
			$sql_values[] = "('$p', 7)";
		}
		$sql .= implode( $sql_values, ',' );
		self::DbQuery( $sql );

        // Activate first player (which is in general a good idea :) )
        $this->activeNextPlayer();

        /************ End of the game initialization *****/
    }

    /*
        getAllDatas:

        Gather all informations about current game situation (visible by the current player).

        The method is called each time the game interface is displayed to a player, ie:
        _ when the game starts
        _ when a player refreshes the game page (F5)
    */
    protected function getAllDatas()
    {
        $result = array();

        $current_player_id = self::getCurrentPlayerId();    // !! We must only return informations visible by this player !!

        // Get information about players
        // Note: you can retrieve some extra field you added for "player" table in "dbmodel.sql" if you need it.
        $sql = "SELECT player_id id, player_score score FROM player ";
        $result['players'] = self::getCollectionFromDb( $sql );

        // TODO: Gather all information about current game situation (visible by player $current_player_id).
		// Get pieces positions
		$result['pieces'] = self::getObjectListFromDb( "SELECT piece_id, player_id, piece_x x, piece_y y, alive, selected
														FROM piece
														WHERE alive = 1" );
		// Get pegs positions
		$result['pegs'] = self::getObjectListFromDb( "	SELECT piece_id, peg_index
														FROM peg" );
        return $result;
    }

    /*
        getGameProgression:

        Compute and return the current game progression.
        The number returned must be an integer beween 0 (=the game just started) and
        100 (= the game is finished or almost finished).

        This method is called each time we are in a game state with the "updateGameProgression" property set to true
        (see states.inc.php)
    */
    function getGameProgression()
    {
		// TODO: compute and return the game progression
		// count number of active player pieces
		$sql = "SELECT count(piece_id) AS num FROM piece WHERE alive = 1 AND player_id =" . self::getActivePlayerId();
		$number_of_player_pieces = self::getObjectListFromDB( $sql, true );
		// count number of active opposite player pieces
		$sql = "SELECT count(piece_id) AS num FROM piece WHERE alive = 1 AND player_id !=" . self::getActivePlayerId();
		$number_of_opposite_player_pieces = self::getObjectListFromDB( $sql, true );

		$number_of_pieces_active_player = self::getUniqueValueFromDB( "SELECT count(piece_id) AS num FROM piece WHERE alive = 1 AND player_id =" . self::getActivePlayerId() );
		$number_of_pieces_opposite_player = self::getUniqueValueFromDB( "SELECT count(piece_id) AS num FROM piece WHERE alive = 1 AND player_id !=" . self::getActivePlayerId() );

		return ( ( 6 - min( (int)$number_of_pieces_active_player, (int)$number_of_pieces_opposite_player ) ) * 20 );
    }


//////////////////////////////////////////////////////////////////////////////
//////////// Utility functions
////////////

    /*
        In this space, you can put any utility methods useful for your game logic
    */


    // Get the pieces positions with a double associative array
    function getPlayerPieces( $player_id ) {
		$sql = "SELECT piece_id id, piece_x x, piece_y y FROM piece WHERE alive = 1 AND player_id = " . $player_id;
        return self::getCollectionFromDB( $sql, true );
    }

    // Get the list of possible moves (x => y => true) of the piece in argument
    function getPossibleMoves( $IdOfPieceToMove ) { // if $IdOfPieceToMove == 99 : find if there is a possible move or not for the player
		$result = array();

		// get all the positions of the player pieces
		$sql = "SELECT piece_id, piece_x x, piece_y y, player_id FROM piece WHERE alive = 1 AND player_id = " . self::getCurrentPlayerId();
        $player_pieces = self::getCollectionFromDB( $sql );

		// get the id of the piece to move
//		$IdOfPieceToMove = self::getGameStateValue( 'selectedPieceID' );

		if ( $IdOfPieceToMove == 99 ) {
			foreach ($player_pieces as $player_piece ) {
				if ( count( self::getPossibleMoves( $player_piece[ "piece_id" ] ) ) >0 ) {
					return true;
				}
			}
			return false;
		} else if ( $IdOfPieceToMove != -1 ) {
			// get the peg positions of the piece to move
			$sql = "SELECT peg_index FROM peg WHERE piece_id = " . $IdOfPieceToMove;
			$pegs = self::getCollectionFromDB( $sql );

			$directions = array(
					array( -2, -2 ), array( -1, -2 ), array( 0, -2 ), array( 1, -2 ), array( 2, -2 ),
					array( -2, -1 ), array( -1, -1 ), array( 0, -1 ), array( 1, -1 ), array( 2, -1 ),
					array( -2,  0 ), array( -1,  0 ), array( 0,  0 ), array( 1,  0 ), array( 2,  0 ),
					array( -2,  1 ), array( -1,  1 ), array( 0,  1 ), array( 1,  1 ), array( 2,  1 ),
					array( -2,  2 ), array( -1,  2 ), array( 0,  2 ), array( 1,  2 ), array( 2,  2 )
			);
			foreach( $pegs as $peg ) {
				$test_x = $player_pieces[ $IdOfPieceToMove ][ "x" ] + $directions[ $peg['peg_index'] ][ 0 ];
				$test_y = $player_pieces[ $IdOfPieceToMove ][ "y" ] + $directions[ $peg['peg_index'] ][ 1 ];
				$test_same_player = false;
				foreach( $player_pieces as $p ) {
					$id_of_a_piece = $p[ "piece_id" ];
					if ( ( $player_pieces[ $id_of_a_piece ][ "x" ] == $test_x ) && ( $player_pieces[ $id_of_a_piece ][ "y" ] == $test_y ) )
						$test_same_player = true;
				}
				// if the new position is on the game board AND is not occupied by one of the current player piece
				if ( $test_x >= 0 && $test_x <= 5 && $test_y >=0 && $test_y <= 5 && !$test_same_player )
					$result[ $test_x ][ $test_y ] = true;
			}

		} else throw new feException( "No piece selected" );

        return $result;
    }

    // Get the list of possible peg places on the piece
    function getPossiblePegPositions( $IdOfSelectedPiece ) { // if $IdOfSelectedPiece == 99 : find if there is a possible peg placement the player
        $result = array();

		// get the id of the piece to move
//		$IdOfPieceToMove = self::getGameStateValue( 'selectedPieceID' );
		if ( $IdOfSelectedPiece == 99) {
			// get all the player pieces
			$sql = "SELECT piece_id, player_id FROM piece WHERE alive = 1 AND player_id = " . self::getCurrentPlayerId();
			$player_pieces = self::getCollectionFromDB( $sql );
			foreach ($player_pieces as $player_piece ) {
				if ( count( self::getPossiblePegPositions( $player_piece[ "piece_id" ] ) ) >0 ) {
					return true;
				}
			}
			return false;
		} else if ( $IdOfSelectedPiece != -1) {
			// get the selected piece
//			$sql = "SELECT piece_id, piece_x x, piece_y y, player_id FROM piece WHERE alive = 1 and piece_id = " .$IdOfSelectedPiece;
//			$selected_piece = self::getCollectionFromDB( $sql );

			$all_positions = range( 0, 24 );
			unset($all_positions[12]); // the central peg

			// get the peg positions of the piece to move
			$sql = "SELECT peg_index FROM peg WHERE piece_id = " . $IdOfSelectedPiece;
			$pegs = self::getCollectionFromDB( $sql );

			$result = array_diff( $all_positions, array_keys($pegs) );


		} else throw new feException( "No piece selected" );

        return $result;
	}

//////////////////////////////////////////////////////////////////////////////
//////////// Player actions
////////////

    /*
        Each time a player is doing some game action, one of the methods below is called.
        (note: each method below must match an input method in thrive.action.php)
    */

    /*

    Example:

    function playCard( $card_id )
    {
        // Check that this is the player's turn and that it is a "possible action" at this game state (see states.inc.php)
        self::checkAction( 'playCard' );

        $player_id = self::getActivePlayerId();

        // Add your game logic to play a card there
        ...

        // Notify all players about the card played
        self::notifyAllPlayers( "cardPlayed", clienttranslate( '${player_name} plays ${card_name}' ), array(
            'player_id' => $player_id,
            'player_name' => self::getActivePlayerName(),
            'card_name' => $card_name,
            'card_id' => $card_id
        ) );

    }

    */

	function selectPieceToMove( $id, $check ) { // $check is either selectPieceToMove or selectPieceForPeg1 or selectPieceForPeg2
		// Check that this is the player's turn and that it is a "possible action" at this game state (see states.inc.php)
		self::checkAction( $check );

		$player_id = self::getActivePlayerId();
//		$playerPieces = self::getPlayerPieces($player_id);
		self::setGameStateValue( 'selectedPieceID', $id );
		$sql = "UPDATE piece SET selected = 1 WHERE piece_id = " . $id;
		self::dbQuery( $sql );

		// Notify
		self::notifyAllPlayers( "selectedPieceToMove", clienttranslate( '${player_name} selected a piece' ), array(
			'player_id' => self::getActivePlayerId(),
			'player_name' => self::getActivePlayerName(),
			'id' => $id
		) );
		// Then go to the next state
		$this->gamestate->nextState( $check );
	}

	function cancelPieceSelection( $check ) { // $check is either selectMoveLocation or selectPeg1Location or selectPeg2Location
		// Check that this is the player's turn and that it is a "possible action" at this game state (see states.inc.php)
		self::checkAction( $check, false );

		$player_id = self::getActivePlayerId();
		self::setGameStateValue( 'selectedPieceID', -1 );
		$sql = "UPDATE piece SET selected = 0";
		self::dbQuery( $sql );

		// Notify
		self::notifyAllPlayers( "cancelPieceSelection", clienttranslate( '${player_name} unselected a piece' ), array(
			'player_id' => self::getActivePlayerId(),
			'player_name' => self::getActivePlayerName()
		) );
		// Then go to the next state
		$this->gamestate->nextState( 'cancelPieceSelection' );
	}

	function selectMoveLocation( $x, $y ) {
		// Check that this is the player's turn and that it is a "possible action" at this game state (see states.inc.php)
        self::checkAction( 'selectMoveLocation' );
/*
		// Now, check if this is a possible move
		$player_id = self::getActivePlayerId();
		$playerPieces = self::getPieces($player_id);
*/

		$IdOfPieceToMove = self::getGameStateValue( 'selectedPieceID' );
		if( $IdOfPieceToMove != -1 ) {
			$poss_moves = self::getPossibleMoves( $IdOfPieceToMove );

			if ( isset( $poss_moves[ $x ] ) && isset( $poss_moves[ $x ][ $y ] ) && $poss_moves[ $x ][ $y ] == true ) { // this is a possible move !
				// opposite player lost the game ?
				$opposite_player_has_lost = false;

				// Check if on other player piece
				$sql = "SELECT piece_id, piece_x x, piece_y y, player_id FROM piece WHERE alive = 1 AND piece_x = " . $x . " AND piece_y = " . $y;
				$pieces = self::getObjectListFromDB( $sql, true );
				if ( count( $pieces ) > 0 ) {
					$sql = "UPDATE piece SET piece_x = -1, piece_y = -1, selected = 0, alive = 0 WHERE piece_id = " . $pieces[ 0 ];
					self::dbQuery( $sql );
					// Notify
					self::notifyAllPlayers( "capturedPiece", clienttranslate( '${player_name} captured a piece' ), array(
						'player_name' => self::getActivePlayerName(),
						'id' => $pieces[ 0 ]
					) );
					// check if opposite player still has only one piece left
					$sql = "SELECT piece_id FROM piece WHERE alive = 1 AND player_id !=" . self::getActivePlayerId();
					$opposite_pieces = self::getObjectListFromDB( $sql, true );
					if ( count( $opposite_pieces ) == 1 )
						$opposite_player_has_lost = true;
				}

				// Move piece to new coordinates x,y and unselect it
				$sql = "UPDATE piece SET piece_x = " . $x . ", piece_y = " . $y . ", selected = 0 WHERE piece_id = " . $IdOfPieceToMove;
				self::dbQuery( $sql );
				self::setGameStateValue( 'selectedPieceID', -1 );

				// Notify
				self::notifyAllPlayers( "selectedMoveLocation", clienttranslate( '${player_name} moved' ), array(
					'player_id' => self::getActivePlayerId(),
					'player_name' => self::getActivePlayerName(),
					'x' => $x,
					'y' => $y,
					'id' => $IdOfPieceToMove
				) );

				// Then go to the next state
				if ( $opposite_player_has_lost == true ) {
					$sql = "UPDATE player SET player_score=1  WHERE player_id='" . self::getActivePlayerId() . "'";
					self::DbQuery($sql);
					self::notifyAllPlayers( "Game end", clienttranslate( '${player_name} has won' ), array(
						'player_id' => self::getActivePlayerId(),
						'player_name' => self::getActivePlayerName(),
						'x' => $x,
						'y' => $y,
						'id' => $IdOfPieceToMove
					) );

					$this->gamestate->nextState( 'endGame' );
				} else $this->gamestate->nextState( 'selectMoveLocation' );
			} else throw new feException( "This move is impossible" );
		} else throw new feException( "No piece selected" );
	}

	function passMovePiece( $check ) { // $check is selectPieceToMove
		// Check that this is the player's turn and that it is a "possible action" at this game state (see states.inc.php)
		self::checkAction( 'selectPieceToMove', false );
		if ( self::getPossibleMoves( 99 ) == false ) {
			// Notify
			self::notifyAllPlayers( "passMovePiece", clienttranslate( '${player_name} doesn\'t move' ), array(
				'player_id' => self::getActivePlayerId(),
				'player_name' => self::getActivePlayerName()
			) );
			// Then go to the next state
			$this->gamestate->nextState( 'passMovePiece' );
		} else throw new feException( "You must move a piece" );
	}

	function selectPegLocation( $peg_index, $check ) {
		// Check that this is the player's turn and that it is a "possible action" at this game state (see states.inc.php)

        if ( $check == "selectPeg1Location" )
			self::checkAction( 'selectPeg1Location' );
		else if ( $check == "selectPeg2Location" )
			self::checkAction( 'selectPeg2Location' );

		$IdOfSelectedPiece = self::getGameStateValue( 'selectedPieceID' );
		if( $IdOfSelectedPiece != -1 ) {

			$poss_positions = self::getPossiblePegPositions( $IdOfSelectedPiece );

			if ( isset( $poss_positions[ $peg_index ] ) && $poss_positions[ $peg_index ] >= 0 ) { // this is a possible placement !

				// Place peg on selected position
				$sql = "INSERT INTO peg (piece_id, peg_index) VALUES ( $IdOfSelectedPiece, $peg_index )";
				self::dbQuery( $sql );
				// Unselect piece
				$sql = "UPDATE piece SET selected = 0 WHERE piece_id = " . $IdOfSelectedPiece;
				self::dbQuery( $sql );
				self::setGameStateValue( 'selectedPieceID', -1 );

				// Notify
				self::notifyAllPlayers( "selectedPegLocation", clienttranslate( '${player_name} placed a peg' ), array(
					'player_id' => self::getActivePlayerId(),
					'player_name' => self::getActivePlayerName(),
					'peg_index' => $peg_index,
					'piece_id' => $IdOfSelectedPiece
				) );

				// Then go to the next state
				if ( $check == "selectPeg1Location" )
					$this->gamestate->nextState( 'selectPeg1Location' );
				else if ( $check == "selectPeg2Location" )
					$this->gamestate->nextState( 'selectPeg2Location' );
			} else throw new feException( "This move is impossible" );
		} else throw new feException( "No piece selected" );
	}

	function passPlacePeg( $check ) { // $check is either
		// Check that this is the player's turn and that it is a "possible action" at this game state (see states.inc.php)
		self::checkAction( $check, false );

		if ( self::getPossiblePegPositions( 99 ) == false ) {
			$player_id = self::getActivePlayerId();
			self::setGameStateValue( 'selectedPieceID', -1 );
			$sql = "UPDATE piece SET selected = 0";
			self::dbQuery( $sql );

			// Notify
			self::notifyAllPlayers( "passPlacePeg", clienttranslate( '${player_name} doesn\'t place a peg' ), array(
				'player_id' => self::getActivePlayerId(),
				'player_name' => self::getActivePlayerName()
			) );
			// Then go to the next state
			$this->gamestate->nextState( 'passPlacePeg' );
		} else throw new feException( "You must place a peg" );
	}


//////////////////////////////////////////////////////////////////////////////
//////////// Game state arguments
////////////

    /*
        Here, you can create methods defined as "game state arguments" (see "args" property in states.inc.php).
        These methods function is to return some additional information that is specific to the current
        game state.
    */

	function argPlayerPieces() {
		return array(
			'playerPieces' => self::getPlayerPieces( self::getActivePlayerId() )
		);
	}

	function argMovePiece() {
		return array(
			'possibleMoves' => self::getPossibleMoves( self::getGameStateValue( 'selectedPieceID' ) )
		);
	}

	function argPlacePeg() {
		return array(
			'possiblePegs' => self::getPossiblePegPositions( self::getGameStateValue( 'selectedPieceID' ) ),
			'player' => self::getActivePlayerId()
		);
	}

    /*

    Example for game state "MyGameState":

    function argMyGameState()
    {
        // Get some values from the current game situation in database...

        // return values:
        return array(
            'variable1' => $value1,
            'variable2' => $value2,
            ...
        );
    }
    */

//////////////////////////////////////////////////////////////////////////////
//////////// Game state actions
////////////

    /*
        Here, you can create methods defined as "game state actions" (see "action" property in states.inc.php).
        The action method of state X is called everytime the current game state is set to X.
    */

    /*

    Example for game state "MyGameState":

    function stMyGameState()
    {
        // Do some stuff ...

        // (very often) go to another gamestate
        $this->gamestate->nextState( 'some_gamestate_transition' );
    }
    */

    public function stTurnStart()
    {
        self::trace('stTurnStart');

        $this->undoSavePoint();

        $this->gamestate->nextState();
    }

	function stTurnEnd()
    {
        // Do some stuff ...

        $player_id = self::activeNextPlayer();
        // (very often) go to another gamestate
        $this->gamestate->nextState( 'nextPlayer' );
    }

    public function playerTurnUndo() {
        self::checkAction('playerTurnUndo');
        $this->undoRestorePoint();
        $this->gamestate->nextState('playerTurnSelectPieceToMove');
    }

    public function playerTurnConfirmEnd() {
        self::checkAction('playerTurnConfirmEnd');
        $this->gamestate->nextState('turnEnd');
    }

//////////////////////////////////////////////////////////////////////////////
//////////// Zombie
////////////

    /*
        zombieTurn:

        This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
        You can do whatever you want in order to make sure the turn of this player ends appropriately
        (ex: pass).

        Important: your zombie code will be called when the player leaves the game. This action is triggered
        from the main site and propagated to the gameserver from a server, not from a browser.
        As a consequence, there is no current player associated to this action. In your zombieTurn function,
        you must _never_ use getCurrentPlayerId() or getCurrentPlayerName(), otherwise it will fail with a "Not logged" error message.
    */

    function zombieTurn( $state, $active_player )
    {
    	$statename = $state['name'];

        if ($state['type'] === "activeplayer") {
            switch ($statename) {
                default:
                    $this->gamestate->nextState( "zombiePass" );
                	break;
            }

            return;
        }

        if ($state['type'] === "multipleactiveplayer") {
            // Make sure player is in a non blocking status for role turn
            $this->gamestate->setPlayerNonMultiactive( $active_player, '' );

            return;
        }

        throw new feException( "Zombie mode not supported at this game state: ".$statename );
    }

///////////////////////////////////////////////////////////////////////////////////:
////////// DB upgrade
//////////

    /*
        upgradeTableDb:

        You don't have to care about this until your game has been published on BGA.
        Once your game is on BGA, this method is called everytime the system detects a game running with your old
        Database scheme.
        In this case, if you change your Database scheme, you just have to apply the needed changes in order to
        update the game database and allow the game to continue to run with your new version.

    */

    function upgradeTableDb( $from_version )
    {
        // $from_version is the current version of this game database, in numerical form.
        // For example, if the game was running with a release of your game named "140430-1345",
        // $from_version is equal to 1404301345

        // Example:
//        if( $from_version <= 1404301345 )
//        {
//            // ! important ! Use DBPREFIX_<table_name> for all tables
//
//            $sql = "ALTER TABLE DBPREFIX_xxxxxxx ....";
//            self::applyDbUpgradeToAllDB( $sql );
//        }
//        if( $from_version <= 1405061421 )
//        {
//            // ! important ! Use DBPREFIX_<table_name> for all tables
//
//            $sql = "CREATE TABLE DBPREFIX_xxxxxxx ....";
//            self::applyDbUpgradeToAllDB( $sql );
//        }
//        // Please add your future database scheme changes here
//
//


    }
}
