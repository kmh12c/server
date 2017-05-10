
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

drop table if exists `game`;
create table `game` (id int, playerCount int, maxPlayers int, winner int, description text, 
 primary key(id));
insert into `game` (id, playerCount, maxPlayers, winner, description)
 values (1, 0, 2, -1, "1st Game"), (2, 0, 2, -1, "2nd Game");

create table if not exists `spot` (id int, lat float, lon float, description text, 
 primary key(id));
INSERT INTO `spot` (`id`, `lat`, `lon`, `description`) VALUES
(1, 32.4677, -99.7071, 'elevator'), (2, 32.4677, -99.7071, 'stairwell'), (3, 32.4678, -99.7072, 'elevator 2nd floor'), 
(4, 32.4676, -99.7072, 'bridge'), (5, 32.4679, -99.7069, 'north entrance light'), (6, 32.4684, -99.7065, 'Jacob''s Dream');

create table if not exists `player` (id int, name text);

create table if not exists `path` (gameId int, sequenceId int, spotId int);
insert into `path` (gameId, sequenceId, spotId) values (1,1,5), (1,2,6), (2,1,2), (2,2,3), (2,3,4);

create table if not exists `playing` (playerId int, gameId int, started timestamp);

create table if not exists `arrival` (playerId int, gameId int, sequenceId int, at timestamp);
