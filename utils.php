<?php
// utils.php

function elog($x) { 
  $now = date("Y-m-d H:i:s"); 
  error_log($now . ": ". $x);
}

function connectDB () {
  require 'database.inc';
  $conn="";
  $dbPlayerCount = -1;
  try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
  catch(PDOException $e){
    echo "Connection failed: " . $e->getMessage();
    exit;
  }
  return $conn;
}

function thisGameIsOver ($game, $winner ) {
  $conn = connectDB();
  $stmt = $conn->prepare("update game set winner = ? where id = ?");
  $stmt->execute(array($winner, $game));
  //?? we should check something
  $conn = null;
}

function countSpots($gameId) {
  $conn = connectDB();
  $stmt = $conn->prepare("select count(id) spots from spot where gameId = ?");
  $stmt->execute( array($gameId) );
  $rc = $stmt->fetch(PDO::FETCH_ASSOC);
  if ($rc) {
    return $rc['spots'];
  }
  // should really complain about bogus game, but to keep the peace I'll just answer 0
  return 0;
}

function findNextTarget ($gameId, $playerId) {
  $conn = connectDB();
  $stmt = $conn->prepare("SELECT max(sequenceId) spot from arrival where playerId = ? and gameId = ?");
  $stmt->execute( array( $playerId, $gameId));
  $rc = $stmt->fetch(PDO::FETCH_ASSOC);
  var_dump($rc);
  if ($rc['spot'] != NULL) {
    $spot = $rc['spot'];
    $stmt = $conn->prepare("SELECT sequenceId sequence, spotId spot from path where gameId = ? and sequenceId = ? + 1");
    $stmt->execute( array( $gameId, $spot ));
    $rc2 = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($rc2['spot'] != NULL) {
      $nextSpot = $rc2['spot'];
      $stmt = $conn->prepare("SELECT lat, lon, id from spot where gameId = ? and id = ?");
      $stmt->execute( array( $gameId, $nextSpot));
      $rc3 = $stmt->fetch(PDO::FETCH_ASSOC);
      return array('lat' => $rc3['lat'],'lon' => $rc3['lon'],'id' => $rc3['id']);
    }
    else {
      thisGameIsOver($gameId, $playerId);
      return array('lat' => 0,'lon' => 0,'id' => 0);
    }
  }
  else { //first spot
    $stmt = $conn->prepare("SELECT sequenceId sequence, spotId spot from path where gameId = ? and sequenceId = 1");
    $stmt->execute( array( $gameId ));
    $rc2 = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($rc2) {
      $nextSpot = $rc2['spot'];
      $stmt = $conn->prepare("SELECT lat, lon from spot where gameId = ? and id = ?");
      $stmt->execute( array( $gameId, $nextSpot));
      $rc3 = $stmt->fetch(PDO::FETCH_ASSOC);
      return array('lat' => $rc3['lat'],'lon' => $rc3['lon'],'id' => $rc3['id']);
    }
    else {
      thisGameIsOver($gameId, $playerId);
      return array('lat' => 0,'lon' => 0,'id' => 0);
    }
  }
}

function isGameOver( $game, &$winner ) {
  $conn = connectDB();
  $stmt = $conn->prepare("SELECT winner from game where id = ?");
  $stmt->execute( array($game));

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

function resetGame($id = 0) {
  $conn = connectDB();
  $stmt = $conn->prepare("update game set playerCount = 0, winner = -1 where id = ?");
  $stmt->execute( array($id));

  elog("game2p game $id was reset.");
  $conn = null;
}

function newGame($id = 0) {
  $conn = connectDB();
  $stmt = $conn->prepare("select max(id) max from game where id = ?");
  $stmt->execute(array($id));
  $rc = $stmt->fetch(PDO::FETCH_ASSOC);
  elog("newGame " . print_r($rc, true));
  $maxId = $rc['max'];

  $stmt = $conn->prepare("insert into game (id, playerCount, maxPlayers, winner, description) values (?,?,?,?,?)");
  $stmt->bind_param("iiiis", 1,2,3,4,5); //does this need to change?
  $stmt->execute();

  elog("game2p game $id was reset.");
  $conn = null;
}

function getPlayerCount($increment = false, $id) {
  $conn = connectDB();
  $stmt = $conn->prepare("SELECT playerCount from game where id = $id");
  $stmt->execute();
  
  //  $result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
  
  $rc = $stmt->fetch(PDO::FETCH_ASSOC);
  elog("g2 " . print_r($rc, true));
  $dbPlayerCount = $rc['playerCount'];
  
  if ($increment)  {
    $stmt = $conn->prepare("update game set playerCount = playerCount + 1 where id = ?");
    $stmt->execute(array($id));
  }
  elog("game2p playercount from db is $dbPlayerCount.  Incremented? $increment");
  $conn = null;
  return $dbPlayerCount;
}

function playerArrivedAtSpot($gameId, $playerId, $spotId) {
  $conn = connectDB();
  $nowish  = new \DateTime( 'now',  new \DateTimeZone( 'UTC' ) );
  $stmt = $conn->prepare("insert into arrival (gameId, playerId, sequenceId, at) values (?,?,?,?)");
  $stmt->execute( array($gameId, $playerId, $spotId, $nowish->format('Y-m-d H:i:s'))) ;
  
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
