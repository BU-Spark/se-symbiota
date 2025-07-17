INSERT IGNORE INTO schemaversion (versionnumber) values ("image-batching-patch");

-- Create batch table
DROP TABLE IF EXISTS `batch`;
CREATE TABLE `batch` (
  `batchID` int(11) NOT NULL AUTO_INCREMENT,
  `ingest_date` timestamp NOT NULL,
  `completed_date` timestamp NULL,
  `batch_name` varchar(100) NOT NULL,
  `image_batch_path` varchar(100) NOT NULL,
  `initialtimestamp` TIMESTAMP NULL DEFAULT current_timestamp,
  `last_edited` int(11) NULL,
  `collID` int(11) NOT NULL,
  PRIMARY KEY (`batchID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Create cross-reference table between the batch table and media table
DROP TABLE IF EXISTS `batch_XREF`;
CREATE TABLE `batch_XREF` (
  `mediaID` int(10) unsigned NOT NULL,
  `batchID` int(11) NOT NULL,
  `ordinal` INT(10) NOT NULL,
  `initialtimestamp` TIMESTAMP NULL DEFAULT current_timestamp,
  PRIMARY KEY (`mediaID`,`batchID`),
  KEY `FK_batch_XREF_media` (`mediaID`),
  KEY `FK_batch_XREF_batch` (`batchID`),
  CONSTRAINT `FK_batch_XREF_media` FOREIGN KEY (`mediaID`) REFERENCES `media` (`mediaID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_batch_XREF_batch` FOREIGN KEY (`batchID`) REFERENCES `batch` (`batchID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Create batch user table
DROP TABLE IF EXISTS `batch_user`;
CREATE TABLE `batch_user` (
  `batch_userID` int(10) NOT NULL AUTO_INCREMENT,
  `batchID` int(10) NOT NULL,
  `uid` int(10) unsigned NOT NULL,
  `last_position` int(10) NOT NULL,
  `initialtimestamp` TIMESTAMP NULL DEFAULT current_timestamp,
  PRIMARY KEY (`batch_userID`),
  KEY `FK_batch_user_batch` (`batchID`),
  KEY `FK_batch_user_user` (`uid`),
  CONSTRAINT `FK_batch_user_batch` FOREIGN KEY (`batchID`) REFERENCES `batch` (`batchID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_batch_user_user` FOREIGN KEY (`uid`) REFERENCES `users` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `images_barcode`;
CREATE TABLE `images_barcode` (
  `mediaID` int(10) unsigned NOT NULL,
  `barcode` varchar(255) NOT NULL,
  `occid` int unsigned NOT NULL,
  PRIMARY KEY (`barcode`),
  KEY `FK_images_barcode_media` (`mediaID`),
  KEY `FK_images_barcode_omoccurrences` (`occid`),
  CONSTRAINT `FK_images_barcode_media` FOREIGN KEY (`mediaID`) REFERENCES `media` (`mediaID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_images_barcode_omoccurrences` FOREIGN KEY (`occid`) REFERENCES `omoccurrences` (`occid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
