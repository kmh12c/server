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
	$greeting = "welcome";
	$playerId = 1;
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