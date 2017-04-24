<?php
date_default_timezone_set('America/Chicago');
require "utils.php";

if ( isset($_GET["msg"]))
  $msg = $_GET["msg"];

$playerNum=0;
if ( isset($_GET["playerNum"]))
  $playerNum = $_GET["playerNum"];

if ( isset($_GET["game"]))
  $game = $_GET["game"];
else
  $game = 1;

$g_ip = getRealIpAddr();
$ip = '';

$target_lat = 32.4675787;
$target_lon = -99.70723875;
$target_delta = 10.0;
$meter1 = .00001;
$playerCount = 0;
$playerList = array();
$spot = 0;

switch ($msg) 
{
	case "reset":
		resetGame ( $game );
		$things = array( 'msg'=>'reset', 'game'=>$game);
		break;
	case "gotGame":
		// return the list of games available
		break;
	case "start":
		$playerCount = getPlayerCount(true, $game);
  		$playerCount ++;
  		elog($game, "start blee received.  Playercount now: " . $playerCount . " ip: [$g_ip]", 0);
  		//get first target
  		$latlong = findNextTarget ($gameId, $playerId);
  		$target_lat = $latlon(0);
  		$target_lon = $latlon(1);
  		$spot = $latlon(2);
  		$things = array( 'msg'=> 'welcome', 'game'=> $game, 'latitude'=>$target_lat, 'longitude'=>$target_lon,
		   'ip' => $g_ip, 'playerNum' => $playerCount);
  		break;
	case "walking":
		// we might have lost the game - see if it is already over
		  $aWinner = -99;
		    if (isGameOver( $game, $aWinner )) {
		      elog("evidently the game is over.");
		      $things = array( 'msg' => 'gameover', 'winner' => $aWinner, 'PlayerNum' => $playerNum );
		  } else {
		      //
		      if ( isset($_GET["lat"]))
		      $lat = $_GET["lat"];
		      else
		      elog("oops lat missing");      
		      
		      if ( isset($_GET["lon"]))
		      $lon = $_GET["lon"];
		      else
		      elog("oops lon missing");      
		      
		      elog("game2p walking received by playerNum: $playerNum", 0);
		      $distance = distance($lat, $lon, $target_lat, $target_lon);
		      $playerList[$playerNum] = $distance;
		      
		      if ($distance < $target_delta) {
		        playerArrivedAtSpot($game, $playerNum, $spot);
		        if (isGameOver( $game, $aWinner )) {
		    	  elog("evidently the game is over.");
		     	 $things = array( 'msg' => 'gameover', 'winner' => $aWinner, 'PlayerNum' => $playerNum );
		     	}
		     	else {
		     		$latlong = findNextTarget ($gameId, $playerId);
  					$target_lat = $latlon(0);
  					$target_lon = $latlon(1);
  					$spot = $latlon(2);
		     		$things = array( 'msg'=> 'arrived', 'latitude'=>$lat, 'longitude'=>$lon, 'ip' => $g_ip);
		     	}
		     	
		    } else {
		        $things = array( 'msg'=> 'keepwalking', 'latitude'=>$lat, 'longitude'=>$lon, 'distance'=>$distance, 'ip' => $g_ip, 'playerNum' => $playerNum);
		    }
		  }
		  break;

	default:
	  $things = array( 'msg'=> 'speakbetter', 'ip' => $g_ip);
}

header("Content-type: application/json");
echo json_encode($things);
?>