<?php
// utils.php

function elog($x) { $now = date("Y-m-d H:i:s"); error_log($now . ": ". $x);}

function gameIsOver ( $winner ) {
  require 'database.inc';
  $dbPlayerCount = -1;
  try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
  catch(PDOException $e){
    echo "Connection failed: " . $e->getMessage();
    exit;
  }
  $stmt = $conn->prepare("update game set winner = $winner");
  $stmt->execute();
  //?? we should check something
  $conn = null;
}

function isGameOver( &$winner ) {
  require 'database.inc';
  $dbPlayerCount = -1;
  try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
  catch(PDOException $e){
    echo "Connection failed: " . $e->getMessage();
    exit;
  }
  $stmt = $conn->prepare("SELECT winner from game where id = 1");
  $stmt->execute();

  $rc = $stmt->fetch(PDO::FETCH_ASSOC);
  $winnerId = $rc['winner'];

  if ( $winnerId > 0 ) {
    $rc = true;
    $winner = $winnerId;
  } else {
    $rc = false;
  }
  elog("game2pWin isGameOver returns [$rc] and winner is [$winnerId].");
  $conn = null;

  return $rc;  
}

//$w = -99;
//$rc = isGameOver( $w );
//print "winner: $w and return code is: $rc";

function resetGame($id = 0) {
  require_once "database.inc";
  try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
  catch(PDOException $e){
    echo "resetGame Connection failed: " . $e->getMessage();
    exit;
  }
  $stmt = $conn->prepare("update game set playerCount = 0, winner = -1 where id = $id");
  $stmt->execute();

  elog("game2p game $id was reset.");
  $conn = null;
}

function getPlayerCount($increment = false) {
  require_once "database.inc";
  $dbPlayerCount = -1;
  try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
  catch(PDOException $e){
    echo "Connection failed: " . $e->getMessage();
    exit;
  }
  $stmt = $conn->prepare("SELECT playerCount from game where id = 1");
  $stmt->execute();
  
  //  $result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
  
  $rc = $stmt->fetch(PDO::FETCH_ASSOC);
  elog("g2 " . print_r($rc, true));
  $dbPlayerCount = $rc['playerCount'];
  
  if ($increment)  {
    $stmt = $conn->prepare("update game set playerCount = playerCount + 1 where id = 1");
    $stmt->execute();
  }
  elog("game2p playercount from db is $dbPlayerCount.  Incremented? $increment");
  $conn = null;
  return $dbPlayerCount;
}

function distance($lat1, $lon1, $lat2, $lon2) {
  $latMid = ($lat1+$lat2 )/2.0;  // or just use Lat1 for slightly less accurate estimate

  $m_per_deg_lat = 111132.954 - 559.822 * cos( 2.0 * $latMid ) + 1.175 * cos( 4.0 * $latMid);
  $m_per_deg_lon = (3.14159265359/180 ) * 6367449 * cos ( $latMid );

  $deltaLat = abs($lat1 - $lat2);
  $deltaLon = abs($lon1 - $lon2);

  $dist_m = sqrt (  pow( $deltaLat * $m_per_deg_lat,2) + pow( $deltaLon * $m_per_deg_lon , 2) );
  elog ('distance '. $lat1. ',' .$lon1. ' to '. $lat2. ','. $lon2. ' is '. $dist_m. "\n");
  return $dist_m;
}

// Function to get the client ip address
function getRealIpAddr()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
    {
      $ip=$_SERVER['HTTP_CLIENT_IP'];
    }
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
    {
      $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    else
    {
      $ip=$_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

?>
