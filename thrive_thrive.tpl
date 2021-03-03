{OVERALL_GAME_HEADER}

<!-- 
--------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- thrive implementation : © Philippe Dubrulle p.dubrulle@gmail.com
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-------

    thrive_thrive.tpl
    
    This is the HTML template of your game.
    
    Everything you are writing in this file will be displayed in the HTML page of your game user interface,
    in the "main game zone" of the screen.
    
    You can use in this template:
    _ variables, with the format {MY_VARIABLE_ELEMENT}.
    _ HTML block, with the BEGIN/END format
    
    See your "view" PHP file to check how to set variables and control blocks
    
    Please REMOVE this comment before publishing your game on BGA
-->


<div id="board">
  <div id="div-square">
    <!-- BEGIN square -->
        <div id="square_{X}_{Y}" class="square" style="left: {LEFT}px; top: {TOP}px;"></div>
    <!-- END square -->
  </div>
  
  <div id="div-peg-placement">
    <!-- BEGIN peg_placement -->
        <div id="peg_placement_{num}" class="peg_placement" style="left: {LEFT}px; top: {TOP}px;"></div>
    <!-- END peg_placement -->
  </div>

    <div id="pieces">
    </div>
	
	<div id="piece_for_peg_placement">
	</div>

</div>

<script type="text/javascript">

// Javascript HTML templates

var jstpl_piece='<div class="piece piececolor_${color}" id="piece_${id}"></div>';
var jstpl_peg='<div class="peg" id="peg_${id}"></div>';

/*
// Example:
var jstpl_some_game_item='<div class="my_game_item" id="my_game_item_${MY_ITEM_ID}"></div>';

*/

</script>  

{OVERALL_GAME_FOOTER}
