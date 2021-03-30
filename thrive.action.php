<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * thrive implementation : © Philippe Dubrulle p.dubrulle@gmail.com
 *
 * This code has been produced on the BGA studio platform for use on https://boardgamearena.com.
 * See http://en.doc.boardgamearena.com/Studio for more information.
 * -----
 * 
 * thrive.action.php
 *
 * thrive main action entry point
 *
 *
 * In this file, you are describing all the methods that can be called from your
 * user interface logic (javascript).
 *       
 * If you define a method "myAction" here, then you can call it from your javascript code with:
 * this.ajaxcall( "/thrive/thrive/myAction.html", ...)
 *
 */
  
  
  class action_thrive extends APP_GameAction
  { 
    // Constructor: please do not modify
   	public function __default()
  	{
  	    if( self::isArg( 'notifwindow') )
  	    {
            $this->view = "common_notifwindow";
  	        $this->viewArgs['table'] = self::getArg( "table", AT_posint, true );
  	    }
  	    else
  	    {
            $this->view = "thrive_thrive";
            self::trace( "Complete reinitialization of board game" );
      }
  	} 
  	
  	// TODO: defines your action entry points there


    /*
    
    Example:
  	
    public function myAction()
    {
        self::setAjaxMode();     

        // Retrieve arguments
        // Note: these arguments correspond to what has been sent through the javascript "ajaxcall" method
        $arg1 = self::getArg( "myArgument1", AT_posint, true );
        $arg2 = self::getArg( "myArgument2", AT_posint, true );

        // Then, call the appropriate method in your game logic, like "playCard" or "myAction"
        $this->game->myAction( $arg1, $arg2 );

        self::ajaxResponse( );
    }
    
    */

	public function selectPieceToMove() {
		self::setAjaxMode();
/*
		$x = self::getArg( "x", AT_posint, true );
		$y = self::getArg( "y", AT_posint, true );
*/
		$id = self::getArg( "id", AT_posint, true );
		$check = self::getArg( "check", AT_alphanum, true );
		$result = $this->game->selectPieceToMove( $id, $check );
		self::ajaxResponse();
	}

	public function cancelPieceSelection() {
		self::setAjaxMode();
		$check = self::getArg( "check", AT_alphanum, true );
		$result = $this->game->cancelPieceSelection( $check );
		self::ajaxResponse();
	}

	public function passMovePiece() {
		self::setAjaxMode();
		$check = self::getArg( "check", AT_alphanum, true );
		$result = $this->game->passMovePiece( $check );
		self::ajaxResponse();
	}

	public function passPlacePeg() {
		self::setAjaxMode();
		$check = self::getArg( "check", AT_alphanum, true );
		$result = $this->game->passPlacePeg( $check );
		self::ajaxResponse();
	}

	public function selectMoveLocation() {
		self::setAjaxMode();
		$x = self::getArg( "x", AT_posint, true );
		$y = self::getArg( "y", AT_posint, true );
		$result = $this->game->selectMoveLocation( $x, $y );
		self::ajaxResponse();
	}
	
	public function selectPegLocation() {
		self::setAjaxMode();
		$peg_index = self::getArg( "peg_index", AT_posint, true );
		$check = self::getArg( "check", AT_alphanum, true );
		$result = $this->game->selectPegLocation( $peg_index, $check );
		self::ajaxResponse();
	}

      /**
       * Handle the playerTurnUndo action.
       *
       * This method gets called when the player clicks the 'Undo' button to restart their turn.
       */
	public function playerTurnUndo() {
	    self::setAjaxMode();
        $this->game->playerTurnUndo();
	    self::ajaxResponse();
    }

      /**
       * Handle the playerTurnConfirmEnd action.
       *
       * This method gets called when the player clicks the 'End Turn' button to confirm their move.
       */
    public function playerTurnConfirmEnd() {
	    self::setAjaxMode();
	    $this->game->playerTurnConfirmEnd();
	    self::ajaxResponse();
    }
  }


