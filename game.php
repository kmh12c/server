<?php
date_default_timezone_set('America/Chicago');
require "utils.php";

if ( isset($_GET["msg"]))
  $msg = $_GET["msg"];

$playerNum=0;
if ( isset($_GET["playerNum"]))
  $playerNum = $_GET["playerNum"];
else
	$playerNum = 1; //change this

if ( isset($_GET["game"]))
  $game = $_GET["game"];
else
  $game = 1;

if ( isset($_GET["lat"]))
  $lat = $_GET["lat"];

if ( isset($_GET["lon"]))
	$lon = $_GET["lon"];

$g_ip = getRealIpAddr();
$target_lat = 0;
$target_lon = 0;
$target_delta = .0001;
$meter1 = .00001;
$playerCount = 1;
$playerList = array();
$latlon = array();
$description = "";
$lat = 0;
$lon = 0;

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
  		$playerNum = $playerCount++;
  		//get first target
  		$latlon = findNextTarget($game, $playerNum);
  		$target_lat = $latlon['lat'];
  		$target_lon = $latlon['lon'];
  		$description = $latlon['description'];
  		$things = array( 'msg'=> 'welcome', 'game'=> $game, 'lat'=>$target_lat, 'lon'=>$target_lon, 'description' => $description, 'ip' => $g_ip, 'playerNum' => $playerNum);
  		break;
	case "walking":
		// we might have lost the game - see if it is already over

		$aWinner = -99;
	    $aWinner = isGameOver($game, $aWinner);
		if ($aWinner >= 1) {
		    	if ($aWinner == $playerNum)
					$things = array( 'msg' => 'win', 'winner' => $aWinner, 'PlayerNum' => $playerNum );
		    	else
		      		$things = array( 'msg' => 'gameover', 'winner' => $aWinner, 'PlayerNum' => $playerNum );
		  } else {
		      if ( isset($_GET["lat"]))
		      $lat = $_GET["lat"];
		      
		      if ( isset($_GET["lon"]))
		      $lon = $_GET["lon"];

		  	  if ( isset($_GET["targetlat"]))
		      $target_lat = $_GET["targetlat"];
		      
		      if ( isset($_GET["targetlon"]))
		      $target_lon = $_GET["targetlon"];
		      
		      $distance = distance($lat, $lon, $target_lat, $target_lon);
		      $playerList[$playerNum] = $distance;
		      
		      if ($distance < $target_delta) {
		        playerArrivedAtSpot($game, $playerNum);
		        $aWinner = isGameOver( $game, $aWinner );
		        if ($aWinner == $playerNum) {
		     	 $things = array( 'msg' => 'win', 'winner' => $aWinner, 'PlayerNum' => $playerNum );
		     	}
		     	elseif ($aWinner > 0) {
		     		$things = array( 'msg' => 'gameover', 'winner' => $aWinner, 'PlayerNum' => $playerNum );
		     	}
		     	else {
		     		$latlon = findNextTarget ($game, $playerNum);
  					$target_lat = $latlon['lat'];
  					$target_lon = $latlon['lon'];
  					$description = $latlon['description'];
		     		$things = array( 'msg'=> 'checkpoint', 'lat'=>$target_lat, 'lon'=>$target_lon, 'description' => $description, 'ip' => $g_ip);
		     	}
		     	
		    } else {
		        $things = array( 'msg'=> 'keepwalking', 'lat'=>$lat, 'lon'=>$lon, 'target lat'=>$target_lat, 'target lon'=>$target_lon, 'distance'=>$distance, 'target delta' => $target_delta, 'ip' => $g_ip, 'playerNum' => $playerNum);
		    }
		  }
		  break;

		$things = array( 'msg'=> 'keepwalking', 'game'=> $game, 'lat'=>$target_lat, 'lon'=>$target_lon, 'description' => $description, 'ip' => $g_ip, 'playerNum' => $playerNum);
		break;

	default:
	  $things = array( 'msg'=> 'speakbetter', 'ip' => $g_ip);
}

header("Content-type: application/json");
echo json_encode($things);
?>