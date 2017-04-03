<?php
date_default_timezone_set('America/Chicago');
require "utils.php";

if ( isset($_GET["msg"]))
  $msg = $_GET["msg"];

$playerNum=0;
if ( isset($_GET["playerNum"]))
  $playerNum = $_GET["playerNum"];

if ( isset($_GET["game"]))
  $resetGame = $_GET["game"];

$g_ip = getRealIpAddr();
$ip = '';

$target_lat = 32.4675787;
$target_lon = -99.70723875;
$target_delta = 10.0;
$meter1 = .00001;
$playerCount = 0;
$playerList = array();

switch ($msg) 
{
	case "reset":
		resetGame ( $resetGame );
		$things = array( 'msg'=>'reset', 'game'=>$resetGame);
		break;
	case "gotGame":
		// return the list of games available
		break;
	case "start":
		$playerCount = getPlayerCount(true);
  		$playerCount ++;
  		elog("game2p start blee received.  Playercount now: " . $playerCount . " ip: [$g_ip]", 0);

  		$things = array( 'msg'=> 'welcome', 'latitude'=>$target_lat, 'longitude'=>$target_lon,
		   'ip' => $g_ip, 'playerNum' => $playerCount);
  		break;

	// 	$link = new mysqli("localhost","ec2-user", NULL,"mc2");
	// 	if ($link->connect_errno) 
	// 	{
	// 	    printf("Connect failed: %s\n", $link->connect_error);
	// 	    exit();
	// 	}
	// 	else //mysqli connection successful
	// 	{
	// 	    $result = $link->query("SELECT * FROM users WHERE ip='$ip'");
	// 	    if(!$result)
	// 	        die ('Can\'t query users because: ' . $link->error);
	// 	    else //query successful
	// 		{
	// 			$num_rows = mysqli_num_rows($result);
	// 	        if ($num_rows > 0) //existing playerId for that IP address
	// 	        {
	// 	        	$msg = "welcome back";
	// 	        	$player = $result->fetch_assoc();
	// 	        	$playerId = $player["playerId"];
	// 	        }
	// 	        else //no existing playerId for that IP
	// 	        {
	// 	        	$result = $link->query("INSERT INTO users (ip) VALUES ('$ip')");
	// 				if(!$result)
	// 	   				die ('Can\'t add user because: ' . $link->error);
	// 	   			$result = $link->query("SELECT * FROM users WHERE ip='$ip'");
	// 					if(!$result)
	// 	    			die ('Can\'t query users because: ' . $link->error);
	// 	    		$player = $result->fetch_assoc();
	// 	        	$playerId = $player["playerId"];
	// 	    	}
	// 	    }
	// 	}
		
	// 	$things = array( 'msg'=> $msg, 'id' => $playerId, 'latitude'=>$target_lat, 'longitude'=>$target_lon, 'ip' => $ip);
	// break;

	case "walking":
		// we might have lost the game - see if it is already over
		  $aWinner = -99;
		    if (isGameOver( $aWinner )) {
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
		        $things = array( 'msg'=> 'win', 'latitude'=>$lat, 'longitude'=>$lon, 'ip' => $g_ip);
		        gameIsOver($playerNum);
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