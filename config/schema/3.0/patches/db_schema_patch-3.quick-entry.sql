INSERT IGNORE INTO schemaversion (versionnumber) values ("quick-entry-patch");

-- Add missing columns to the omocurrences table where the quick entry data is stored
ALTER TABLE `omoccurrences` 
    ADD COLUMN `filedUnder` varchar(255) DEFAULT NULL,
    ADD COLUMN `geoWithin` varchar(255) DEFAULT NULL,
    ADD COLUMN `herbarium` varchar(4) NOT NULL,
    ADD COLUMN `accesNum` varchar(255) DEFAULT NULL,
    ADD COLUMN `currName` varchar(255) DEFAULT NULL,
    ADD COLUMN `idQualifier` varchar(16) DEFAULT NULL,
    ADD COLUMN `detText` text DEFAULT NULL,
    ADD COLUMN `provenance` text DEFAULT NULL,
    ADD COLUMN `container` varchar(255) DEFAULT NULL,
    ADD COLUMN `collTrip` varchar(255) DEFAULT NULL,
    ADD COLUMN `highGeo` varchar(255) DEFAULT NULL,
    ADD COLUMN `frequency` varchar(255) DEFAULT NULL,
    ADD COLUMN `prepMethod` varchar(255) DEFAULT NULL,
    ADD COLUMN `format` varchar(255) DEFAULT NULL,
    ADD COLUMN `verbLat` varchar(255) DEFAULT NULL,
    ADD COLUMN `verbLong` varchar(255) DEFAULT NULL,
    ADD COLUMN `method` varchar(255) DEFAULT NULL;