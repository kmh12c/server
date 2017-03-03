<?php
// 17S CS 316 Mobile II
// Brent Reeves
// get1.php
// respond to GET
//
$long = $_GET["long"];
$lat = $_GET["lat"];
$ip = $_SERVER['REMOTE_ADDR'];

$greeting = "hello";
if ( $long > 68 )
  $greeting = "Move Faster";

$things = array( $ip, $greeting, $long, $lat);

header("Content-type: application/json");
echo json_encode($things);
?>