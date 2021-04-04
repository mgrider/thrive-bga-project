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
 * states.inc.php
 *
 * thrive game states description
 *
 */

/*
   Game state machine is a tool used to facilitate game developpement by doing common stuff that can be set up
   in a very easy way from this configuration file.

   Please check the BGA Studio presentation about game state to understand this, and associated documentation.

   Summary:

   States types:
   _ activeplayer: in this type of state, we expect some action from the active player.
   _ multipleactiveplayer: in this type of state, we expect some action from multiple players (the active players)
   _ game: this is an intermediary state where we don't expect any actions from players. Your game logic must decide what is the next game state.
   _ manager: special type for initial and final state

   Arguments of game states:
   _ name: the name of the GameState, in order you can recognize it on your own code.
   _ description: the description of the current game state is always displayed in the action status bar on
                  the top of the game. Most of the time this is useless for game state with "game" type.
   _ descriptionmyturn: the description of the current game state when it's your turn.
   _ type: defines the type of game states (activeplayer / multipleactiveplayer / game / manager)
   _ action: name of the method to call when this game state become the current game state. Usually, the
             action method is prefixed by "st" (ex: "stMyGameStateName").
   _ possibleactions: array that specify possible player actions on this step. It allows you to use "checkAction"
                      method on both client side (Javacript: this.checkAction) and server side (PHP: self::checkAction).
   _ transitions: the transitions are the possible paths to go from a game state to another. You must name
                  transitions in order to use transition names in "nextState" PHP method, and use IDs to
                  specify the next game state for each transition.
   _ args: name of the method to call to retrieve arguments for this gamestate. Arguments are sent to the
           client side to be used on "onEnteringState" or to set arguments in the gamestate description.
   _ updateGameProgression: when specified, the game progression is updated (=> call to your getGameProgression
                            method).
*/

//    !! It is not a good idea to modify this file when a game is running !!


$machinestates = array(

    // The initial state. Please do not modify.
	STATE_GAME_SETUP => array(
		"name"        => "gameSetup",
		"description" => "",
		"type"        => "manager",
		"action"      => "stGameSetup",
		"transitions" => array("" => 10),
	),

	10 => array(
		'name'        => 'turnStart',
		'description' => '',
		'type'        => 'game',
		'action'      => 'stTurnStart',
		'transitions' => array('playerTurnSelectPieceToMove' => STATE_PLAYER_TURN_SELECT_PIECE),
	),

	STATE_PLAYER_TURN_SELECT_PIECE => array(
		"name"              => "playerTurnSelectPieceToMove",
		"description"       => clienttranslate('${actplayer} must move a piece'),
		"descriptionmyturn" => clienttranslate('${you} must select the piece to move'),
		"type"              => "activeplayer",
		"args"              => "argPlayerPieces",
		"possibleactions"   => array("selectPieceToMove", "passMovePiece"),
		"transitions"       => array(
			"selectPieceToMove" => STATE_PLAYER_TURN_SELECT_LOCATION,
			"passMovePiece"     => STATE_PLAYER_TURN_SELECT_PEG1_LOCATION,
		),
	),

	STATE_PLAYER_TURN_SELECT_LOCATION => array(
		"name"              => "playerTurnSelectLocationToMove",
		"description"       => clienttranslate('${actplayer} must move a piece'),
		"descriptionmyturn" => clienttranslate('${you} must select where you move your piece'),
		"type"              => "activeplayer",
		"args"              => "argMovePiece",
		"possibleactions"   => array("cancelPieceSelection", "selectMoveLocation", "endGame"),
		"transitions"       => array(
			"cancelPieceSelection" => STATE_PLAYER_TURN_SELECT_PIECE,
			"selectMoveLocation"   => STATE_PLAYER_TURN_SELECT_LOCATION,
			"endGame"              => STATE_GAME_END,
		),
	),

	STATE_PLAYER_TURN_SELECT_PEG1_PIECE => array(
		"name"              => "playerTurnSelectPieceForPeg1Add",
		"description"       => clienttranslate('${actplayer} must add a peg'),
		"descriptionmyturn" => clienttranslate('${you} must select a piece to add your first peg'),
		"type"              => "activeplayer",
		"args"              => "argPlayerPieces",
		"possibleactions"   => array("selectPieceForPeg1", "passPlacePeg"),
		"transitions"       => array(
			"selectPieceForPeg1" => STATE_PLAYER_TURN_SELECT_PEG1_LOCATION,
			"passPlacePeg"       => STATE_PLAYER_TURN_END,
		),
	),

	STATE_PLAYER_TURN_SELECT_PEG1_LOCATION => array(
		"name"              => "playerTurnSelectPegLocationForPeg1Add",
		"description"       => clienttranslate('${actplayer} must add a peg'),
		"descriptionmyturn" => clienttranslate('${you} must add a peg to the selected piece'),
		"type"              => "activeplayer",
		"args"              => "argPlacePeg",
		"possibleactions"   => array("cancelPieceSelection", "selectPeg1Location"),
		"transitions"       => array(
			"cancelPieceSelection" => STATE_PLAYER_TURN_SELECT_PEG1_PIECE,
			"selectPeg1Location"   => STATE_PLAYER_TURN_SELECT_PEG2_PIECE,
		),
	),

	STATE_PLAYER_TURN_SELECT_PEG2_PIECE => array(
		"name"              => "playerTurnSelectPieceForPeg2Add",
		"description"       => clienttranslate('${actplayer} must add a peg'),
		"descriptionmyturn" => clienttranslate('${you} must select a piece to add your second peg'),
		"type"              => "activeplayer",
		"args"              => "argPlayerPieces",
		"possibleactions"   => array("selectPieceForPeg2", "passPlacePeg"),
		"transitions"       => array(
			"selectPieceForPeg2" => STATE_PLAYER_TURN_SELECT_PEG2_LOCATION,
			"passPlacePeg"       => 75,
		),
	),

	STATE_PLAYER_TURN_SELECT_PEG2_LOCATION => array(
		"name"              => "playerTurnSelectPegLocationForPeg2Add",
		"description"       => clienttranslate('${actplayer} must add a peg'),
		"descriptionmyturn" => clienttranslate('${you} must add a peg to the selected piece'),
		"type"              => "activeplayer",
		"args"              => "argPlacePeg",
		"possibleactions"   => array("cancelPieceSelection", "selectPeg2Location"),
		"transitions"       => array(
			"cancelPieceSelection" => STATE_PLAYER_TURN_SELECT_PEG2_PIECE,
			"selectPeg2Location"   => 75,
		),
	),

	75 => array(
		'name'              => 'playerConfirmTurnEnd',
		'description'       => clienttranslate('${actplayer} must confirm their turn.'),
		'descriptionmyturn' => clienttranslate('End turn?'),
		'type'              => 'activeplayer',
		'possibleactions'   => array('playerTurnUndo', 'playerTurnConfirmEnd'),
		'transitions'       => array('playerTurnSelectPieceToMove' => 20, 'turnEnd' => 80),
	),

	STATE_PLAYER_TURN_END => array(
		"name"                  => "turnEnd",
		"description"           => '',
		"type"                  => "game",
		"action"                => "stTurnEnd",
		"updateGameProgression" => true,
		"transitions"           => array("nextPlayer" => STATE_PLAYER_TURN_SELECT_PIECE),
	),

    // Final state.
    // Please do not modify (and do not overload action/args methods).
    99 => array(
        "name" => "gameEnd",
        "description" => clienttranslate("End of game"),
        "type" => "manager",
        "action" => "stGameEnd",
        "args" => "argGameEnd"
    )
);
