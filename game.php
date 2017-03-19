<?php

// Function to get the client ip address
function getRealIpAddr()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
      $ip=$_SERVER['HTTP_CLIENT_IP'];
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) //to check ip is pass from proxy
      $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
    else
      $ip=$_SERVER['REMOTE_ADDR'];
    return $ip;
}

$ip = getRealIpAddr();
$msg = $_GET["msg"];

$target_lat = 32.4675765;
$target_lon = -99.70698;
$target_delta = 10.0;
$meter1 = .00001;


if (isset($_REQUEST['lon']))
{
	$lon = $_GET["lon"];
	$lat = $_GET["lat"];
}

function distance($lat1, $lon1, $lat2, $lon2) 
{
  $latMid = ($lat1+$lat2 )/2.0;

  $m_per_deg_lat = 111132.954 - 559.822 * cos( 2.0 * $latMid ) + 1.175 * cos( 4.0 * $latMid);
  $m_per_deg_lon = (3.14159265359/180 ) * 6367449 * cos ( $latMid );

  $deltaLat = abs($lat1 - $lat2);
  $deltaLon = abs($lon1 - $lon2);

  $dist_m = sqrt (  pow( $deltaLat * $m_per_deg_lat,2) + pow( $deltaLon * $m_per_deg_lon , 2) );
  return $dist_m;
}

function closeEnough ($lat, $lon) 
{
  GLOBAL $target_lat, $target_lon, $target_delta;
  return  distance($lat, $lon, $target_lat, $target_lon) < $target_delta;
}

switch ($msg) 
{
	case "start":
		error_log("start received", 0);
		$msg = 'welcome';
		$playerId = 0; //default

		$link = new mysqli("localhost","ec2-user", NULL,"mc2");
		if ($link->connect_errno) 
		{
		    printf("Connect failed: %s\n", $link->connect_error);
		    exit();
		}
		else //mysqli connection successful
		{
		    $result = $link->query("SELECT * FROM users WHERE ip='$ip'");
		    if(!$result)
		        die ('Can\'t query users because: ' . $link->error);
		    else //query successful
			{
				$num_rows = mysqli_num_rows($result);
		        if ($num_rows > 0) //existing playerId for that IP address
		        {
		        	$msg = "welcome back";
		        	$player = $result->fetch_assoc();
		        	$playerId = $player["playerId"];
		        }
		        else //no existing playerId for that IP
		        {
		        	$result = $link->query("INSERT INTO users (ip) VALUES ('$ip')");
					if(!$result)
		   				die ('Can\'t add user because: ' . $link->error);
		   			$result = $link->query("SELECT * FROM users WHERE ip='$ip'");
						if(!$result)
		    			die ('Can\'t query users because: ' . $link->error);
		    		$player = $result->fetch_assoc();
		        	$playerId = $player["playerId"];
		    	}
		    }
		}
		
		$things = array( 'msg'=> $msg, 'id' => $playerId, 'latitude'=>$target_lat, 'longitude'=>$target_lon, 'ip' => $ip);
	break;

	case "walking":
		error_log("walking received", 0);
		$distance = distance($lat, $lon, $target_lat, $target_lon);
		if ($distance < $target_delta) 
			$things = array( 'msg'=> 'win', 'latitude'=>$lat, 'longitude'=>$lon, 'ip' => $ip);
		else 
			$things = array( 'msg'=> 'keepwalking', 'latitude'=>$lat, 'longitude'=>$lon, 'distance'=>$distance, 'ip' => $ip);
	break;

	default:
	  $things = array( 'msg'=> 'speakbetter', 'ip' => $ip);
}

header("Content-type: application/json");
echo json_encode($things);
?>