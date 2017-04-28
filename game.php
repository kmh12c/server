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

$g_ip = getRealIpAddr();
$ip = '';

$target_lat = 32.6;
$target_lon = -99.4;
$target_delta = 10.0;
$meter1 = .00001;
$playerCount = 1;
$playerList = array();
$spot = 0;
$latlon = array();

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
		//$playerCount = getPlayerCount(true, $game);
  		//$playerNum = $playerCount++;
  		echo "PlayerId:[$playerCount], GameId:[$game]";
  		elog($game, "start blee received.  Playercount now: " . $playerCount . " ip: [$g_ip]", 0);
  		//get first target
  		$latlon = findNextTarget($game, $playerNum);
  		$target_lat = 32.4679134; //$latlon['lat'];
  		$target_lon = -99.70692044; //$latlon['lon'];
  		$spot = $latlon['id'];
  		$things = array( 'msg'=> 'welcome', 'game'=> $game, 'latitude'=>$target_lat, 'longitude'=>$target_lon,
		   'ip' => $g_ip, 'playerNum' => $playerNum);
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
		     		//$latlon = findNextTarget ($game, $playerNum);
  					$target_lat = 32.4684332;//$latlon['lat'];
  					$target_lon = -99.7064923;
//$latlon['lon'];
  					$spot = $latlon['id'];
		     		$things = array( 'msg'=> 'checkpoint', 'latitude'=>$target_lat, 'longitude'=>$target_lon, 'ip' => $g_ip);
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