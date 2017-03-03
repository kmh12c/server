<?php
// 17S CS 316 Mobile II
// Brent Reeves
// get1.php
// respond to GET
//

$ip = $_SERVER['REMOTE_ADDR'];
$msg = $_GET["msg"];

if (isset($_REQUEST['long']))
{
	$long = $_GET["long"];
	$lat = $_GET["lat"];
}

if ($msg == "start")
{
	$greeting = "Welcome";
	$playerId = 0; //default

	//$link = new mysqli("localhost","ec2-user", NULL,"mc2");
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
	        	$greeting = "Welcome Back";
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

	$info = ["blee", "blah"];
	$things = array($greeting, $playerId, $info);
}
elseif ($msg == "walking")
{
	$greeting = "walking";
	$things = array($greeting, $long, $lat);
}
else
{
	$greeting = "win";
	$info = ["", ""];
	$things = array($greeting, $info);
}

header("Content-type: application/json");
echo json_encode($things);
?>