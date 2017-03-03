<?php
// 17S CS 316 Mobile II
// Brent Reeves
// post1.php
// respond to POST
//
$long = $_POST["long"];
$lat = $_POST["lat"];
$nope = $_POST["nope"];

$things = array($long, $lat, $nope);

header("Content-type: application/json");
echo json_encode($things);
?>