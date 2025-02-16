-- SQL script for `OctoTune`

CREATE TABLE `Benutzer` (
    `UUID` CHAR(50) NOT NULL,
    `username` TEXT,
    `password` TEXT,
    `registeredOn` DATE,
    PRIMARY KEY (`UUID`)
) ENGINE = InnoDB;

CREATE TABLE `Playlist` (
    `UPID` BIGINT NOT NULL AUTO_INCREMENT,
    `playlistName` TEXT,
    `createdOn` DATE,
    `isPublic` CHAR(1),
    `createdBy` CHAR(50) NOT Null,
    PRIMARY KEY (`UPID`),
    FOREIGN KEY (`createdBy`) REFERENCES benutzer (`UUID`)
) ENGINE = InnoDB;

CREATE TABLE `Lied` (
    `USID` BIGINT NOT NULL AUTO_INCREMENT,
    `songName` TEXT,
    `releaseDate` YEAR,
    `filePath` TEXT,
    `coverPath` TEXT,
    `genre` TEXT,
    `length` TEXT,
    `filetype` TEXT,
    PRIMARY KEY (`USID`)
) ENGINE = InnoDB;

CREATE TABLE `Kuenstler` (
    `UArtID` BIGINT NOT NULL AUTO_INCREMENT,
    `artistName` TEXT,
    `biografie` TEXT,
    `founded` DATE,
    PRIMARY KEY (`UArtID`)
) ENGINE = InnoDB;

CREATE TABLE `Album` (
    `UAlbID` BIGINT NOT NULL AUTO_INCREMENT,
    `albumName` TEXT,
    `releaseDate` DATE,
    `coverPath` TEXT,
    PRIMARY KEY (`UAlbID`)
) ENGINE = InnoDB;

CREATE TABLE `Wiedergabeverlauf` (
    `UWID` BIGINT NOT NULL AUTO_INCREMENT,
    `UUID` CHAR(50) NOT NULL,
    PRIMARY KEY (`UWID`),
    FOREIGN KEY (`UUID`) REFERENCES benutzer (`UUID`)
) ENGINE = InnoDB;

CREATE TABLE `erstellen` (
    `erstellt` BIGINT NOT NULL AUTO_INCREMENT,
	 `UUID` CHAR(50) NOT NULL,
    `UPID` BIGINT NOT NULL,
    PRIMARY KEY (`erstellt`),
    FOREIGN KEY (`UUID`) REFERENCES benutzer (`UUID`),
    FOREIGN KEY (`UPID`) REFERENCES playlist (`UPID`)
) ENGINE = InnoDB;

CREATE TABLE `beinhalten` (
	 `beinhaltet` BIGINT NOT NULL AUTO_INCREMENT,
    `USID` BIGINT NOT NULL,
    `UPID` BIGINT NOT NULL,
    `order` INT,
    PRIMARY KEY (`beinhaltet`),
    FOREIGN KEY (`USID`) REFERENCES lied (`USID`),
    FOREIGN KEY (`UPID`) REFERENCES playlist (`UPID`)
) ENGINE = InnoDB;

CREATE TABLE `komponieren` (
	 `komponiert` BIGINT NOT NULL AUTO_INCREMENT,
    `UArtID` BIGINT NOT NULL,
    `USID` BIGINT NOT NULL,
    PRIMARY KEY (`komponiert`),
    FOREIGN KEY (`UArtID`) REFERENCES kuenstler (`UArtID`),
    FOREIGN KEY (`USID`) REFERENCES lied (`USID`)
) ENGINE = InnoDB;

CREATE TABLE `veroeffentlichen` (
	 `veroeffentlicht` BIGINT NOT NULL AUTO_INCREMENT,
    `UArtID` BIGINT NOT NULL,
    `UAlbID` BIGINT NOT NULL,
    PRIMARY KEY (`veroeffentlicht`),
    FOREIGN KEY (`UArtID`) REFERENCES kuenstler (`UArtID`),
    FOREIGN KEY (`UAlbID`) REFERENCES album (`UAlbID`)
) ENGINE = InnoDB;

CREATE TABLE `enthalten` (
	 `enthaelt` BIGINT NOT NULL AUTO_INCREMENT,
    `UAlbID` BIGINT NOT NULL,
    `USID` BIGINT NOT NULL,
    `order` TINYINT,
    PRIMARY KEY (`enthaelt`),
    FOREIGN KEY (`UAlbID`) REFERENCES album (`UAlbID`),
    FOREIGN KEY (`USID`) REFERENCES lied (`USID`)
) ENGINE = InnoDB;


CREATE TABLE `speichern` (
	 `speichert` BIGINT NOT NULL AUTO_INCREMENT,
    `UWID` BIGINT NOT NULL,
    `USID` BIGINT NOT NULL,
    `listenedOn` TIMESTAMP,
    PRIMARY KEY (`speichert`),
    FOREIGN KEY (`UWID`) REFERENCES wiedergabeverlauf (`UWID`),
    FOREIGN KEY (`USID`) REFERENCES lied (`USID`)
) ENGINE = InnoDB;

ALTER TABLE `erstellen`
    ADD FOREIGN KEY (`UUID`) REFERENCES `Benutzer` (`UUID`) ;

ALTER TABLE `erstellen`
    ADD FOREIGN KEY (`UPID`) REFERENCES `Playlist` (`UPID`) ;

ALTER TABLE `beinhalten`
    ADD FOREIGN KEY (`USID`) REFERENCES `Lied` (`USID`) ;

ALTER TABLE `beinhalten`
    ADD FOREIGN KEY (`UPID`) REFERENCES `Playlist` (`UPID`) ;

ALTER TABLE `komponieren`
    ADD FOREIGN KEY (`UArtID`) REFERENCES `Kuenstler` (`UArtID`) ;

ALTER TABLE `komponieren`
    ADD FOREIGN KEY (`USID`) REFERENCES `Lied` (`USID`) ;

ALTER TABLE `veroeffentlichen`
    ADD FOREIGN KEY (`UArtID`) REFERENCES `Kuenstler` (`UArtID`) ;

ALTER TABLE `veroeffentlichen`
    ADD FOREIGN KEY (`UAlbID`) REFERENCES `Album` (`UAlbID`) ;

ALTER TABLE `enthalten`
    ADD FOREIGN KEY (`UAlbID`) REFERENCES `Album` (`UAlbID`) ;

ALTER TABLE `enthalten`
    ADD FOREIGN KEY (`USID`) REFERENCES `Lied` (`USID`) ;


ALTER TABLE `speichern`
    ADD FOREIGN KEY (`UWID`) REFERENCES `Wiedergabeverlauf` (`UWID`) ;

ALTER TABLE `speichern`
    ADD FOREIGN KEY (`USID`) REFERENCES `Lied` (`USID`) ;

ALTER TABLE `playlist`
    ADD FOREIGN KEY (`createdBy`) REFERENCES `benutzer` (`UUID`) ;

ALTER TABLE `wiedergabeverlauf`
    ADD FOREIGN KEY (`UUID`) REFERENCES `benutzer` (`UUID`) ;