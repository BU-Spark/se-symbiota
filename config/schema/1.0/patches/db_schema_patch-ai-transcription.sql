INSERT IGNORE INTO schemaversion (versionnumber) values ("ai-transcription-patch");
-- NOTE: These tables are not currently in use by the frontend, but the frontend should be updated to use these tables for the ai transcription feature

-- Create ocr_results table
DROP TABLE IF EXISTS `ocr_results`;
CREATE TABLE `ocr_results` (
  `imgid` int(10) unsigned NOT NULL,
  `batchID` int(11) NOT NULL,
  `collID` int(11) NOT NULL,
  `results` JSON NOT NULL,
  `processed_date` timestamp NOT NULL,
  PRIMARY KEY (`imgid`,`batchID`),
  KEY `FK_ocr_results_img` (`imgid`),
  KEY `FK_ocr_results_batch` (`batchID`),
  CONSTRAINT `FK_ocr_results_img` FOREIGN KEY (`imgid`) REFERENCES `images` (`imgid`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_ocr_results_batch` FOREIGN KEY (`batchID`) REFERENCES `batch` (`batchID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Add columns to the omoccurrences to indicate whether the record was human or machine generated and its confidence value (if machine generated)
ALTER TABLE `omoccurrences` 
    ADD COLUMN `is_machine_generated` BOOL DEFAULT 0,
    ADD COLUMN `confidence_value` double DEFAULT NULL;