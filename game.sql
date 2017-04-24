
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

drop table if exists `game`;
create table `game` (id int, playerCount int, maxPlayers int, winner int, description text, 
 primary key(id));
insert into `game` (id, playerCount, maxPlayers, winner, description)
 values (1, 0, 2, -1, "Best 2-Player Game Ever"), (2, 0, 2, -1, "SteepleChase");

create table if not exists `spot` (id int, gameId int, lat float, lon float, description text, 
 primary key(id, gameId));
insert into `spot` (id, gameId, lat, lon, description) 
   values (1,1,32.3,-99.1,"Jacob's Dream"), (2,2,32.5,-99.3,"Da Bean"), (3,1,32.6,-99.4,"SITC");

create table if not exists `player` (id int, name text);
insert into `player` (id, name) values (1, "Blee"), (2, "Blah");

create table if not exists `path` (gameId int, sequenceId int, spotId int);
insert into `path` (gameId, sequenceId, spotId) values (1,1,2), (1,2,3);

create table if not exists `playing` (playerId int, gameId int, started timestamp);
insert into `playing` (playerId, gameId, started) values (1,1,'2017/04/04 13:00'), (3,1,'2017/04/01 17:06');

create table if not exists `arrival` (playerId int, gameId int, sequenceId int, at timestamp);
insert into `arrival` (playerId, gameId, sequenceId, at) values (1,1,1,'2017/04/04 13:00');