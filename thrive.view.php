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
 * thrive.view.php
 *
 * This is your "view" file.
 *
 * The method "build_page" below is called each time the game interface is displayed to a player, ie:
 * _ when the game starts
 * _ when a player refreshes the game page (F5)
 *
 * "build_page" method allows you to dynamically modify the HTML generated for the game interface. In
 * particular, you can set here the values of variables elements defined in thrive_thrive.tpl (elements
 * like {MY_VARIABLE_ELEMENT}), and insert HTML block elements (also defined in your HTML template file)
 *
 * Note: if the HTML of your game interface is always the same, you don't have to place anything here.
 *
 */
  
  require_once( APP_BASE_PATH."view/common/game.view.php" );
  
  class view_thrive_thrive extends game_view
  {
    function getGameName() {
        return "thrive";
    }
  	function build_page( $viewArgs )
  	{
  	    // Get players & players number
        $players = $this->game->loadPlayersBasicInfos();
        $players_nbr = count( $players );

        /*********** Place your code below:  ************/

		$this->page->begin_block( "thrive_thrive", "square" );
		$hor_scale = 99;
		$ver_scale = 99;
		for( $x=0; $x<6; $x++ ) {
			for( $y=0; $y<6; $y++ ) {
				$this->page->insert_block( "square", array(
					'X' => $x,
					'Y' => $y,
					'LEFT' => round( ($x)*$hor_scale + 10 ),
					'TOP' => round( ($y)*$ver_scale + 15 )
				) );
			}
		}

		$this->page->begin_block( "thrive_thrive", "peg_placement" );
		$hor_scale_peg = 21.4;
		$ver_scale_peg = 21.4;
		for( $n=0; $n<25; $n++ ) {
			if ( $n != 12 ) {
				switch( $n ) {
					case "0": $x = -2.1; $y = -2.1 ; break;
					case "1": $x = -1.1; $y = -2.1 ; break;
					case "2": $x =  -0.1; $y = -2.1 ; break;
					case "3": $x =  0.9; $y = -2.1 ; break;
					case "4": $x =  1.9; $y = -2.1 ; break;
					case "5": $x = -2.1; $y = -1.1; break;
					case "6": $x = -1.1; $y = -1.1; break;
					case "7": $x =  -0.1; $y = -1.1; break;
					case "8": $x =  0.9; $y = -1.1; break;
					case "9": $x =  1.9; $y = -1.1; break;
					case "10": $x = -2.1; $y = -0.1; break;
					case "11": $x = -1.1; $y = -0.1; break;
					case "12": $x =  -0.1; $y = -0.1; break;
					case "13": $x =  0.9; $y = -0.1; break;
					case "14": $x =  1.9; $y = -0.1; break;
					case "15": $x = -2.1; $y = 0.9; break;
					case "16": $x = -1.1; $y = 0.9; break;
					case "17": $x =  -0.1; $y = 0.9; break;
					case "18": $x =  0.9; $y = 0.9; break;
					case "19": $x =  1.9; $y = 0.9; break;
					case "20": $x = -2.1; $y = 1.9; break;
					case "21": $x = -1.1; $y = 1.9; break;
					case "22": $x =  -0.1; $y = 1.9; break;
					case "23": $x =  0.9; $y = 1.9; break;
					case "24": $x =  1.9; $y = 1.9; break;
				}

				$this->page->insert_block( "peg_placement", array(
					'num' => $n,
					'LEFT' => round( ( $x ) * $hor_scale_peg + 3 * $hor_scale + 0 ),
					'TOP' => round( ( $y ) * $ver_scale_peg + 3 * $ver_scale + 0 )
				) );
			}
		}

        /*
        
        // Examples: set the value of some element defined in your tpl file like this: {MY_VARIABLE_ELEMENT}

        // Display a specific number / string
        $this->tpl['MY_VARIABLE_ELEMENT'] = $number_to_display;

        // Display a string to be translated in all languages: 
        $this->tpl['MY_VARIABLE_ELEMENT'] = self::_("A string to be translated");

        // Display some HTML content of your own:
        $this->tpl['MY_VARIABLE_ELEMENT'] = self::raw( $some_html_code );
        
        */
        
        /*
        
        // Example: display a specific HTML block for each player in this game.
        // (note: the block is defined in your .tpl file like this:
        //      <!-- BEGIN myblock --> 
        //          ... my HTML code ...
        //      <!-- END myblock --> 
        

        $this->page->begin_block( "thrive_thrive", "myblock" );
        foreach( $players as $player )
        {
            $this->page->insert_block( "myblock", array( 
                                                    "PLAYER_NAME" => $player['player_name'],
                                                    "SOME_VARIABLE" => $some_value
                                                    ...
                                                     ) );
        }
        
        */



        /*********** Do not change anything below this line  ************/
  	}
  }
  

