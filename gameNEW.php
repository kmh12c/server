<?php

function connectDB ()
{

	return conn;	
}


function thisGameIsOver (game, winner)
{
	$conn = connectDB();
	$stmt = $conn->prepare("update game set winner = $winner where /////");
	////?? check something
	$conn = null;
}

function countSpots ($game)
{
	$conn = ////
	$stmt = $conn->prepare("select count(id) spots from spot where gameID = $game");
	$stmt->execute(array($game));
	$rc = $stmt->fetch(PDO:///);
	if($rc) {
		return $rc['spots'];
	}
	return 0;
}

function findNextTarget ($game, $playerId)
{
	echo "findNextTarget...";
	$conn = ////
	$stmt = $conn->prepare("select max(spotID) spot from arrival where playerID = $playerId and gameId = $game");
	$stmt->execute(array($playerId, $game));
	$rc = $stmt->fetch(PDO:///);
	var////
}

function getPlayerCount (same)
{

}

function playerArrivedAtSpot

?>

//posted to files