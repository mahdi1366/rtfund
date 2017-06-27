ALTER TABLE `krrtfir_rtfund`.`ATN_requests` MODIFY COLUMN `ConfirmExtra`
INTEGER UNSIGNED NOT NULL DEFAULT 0,
 ADD COLUMN `RealExtra` INTEGER UNSIGNED NOT NULL AFTER `IsArchive`,
 ADD COLUMN `LegalExtra` INTEGER UNSIGNED NOT NULL AFTER `RealExtra`;

 ALTER TABLE `krrtfir_rtfund`.`ATN_requests` MODIFY COLUMN `RealExtra` INTEGER UNSIGNED NOT NULL DEFAULT 0,
 MODIFY COLUMN `LegalExtra` INTEGER UNSIGNED NOT NULL DEFAULT 0;

 
 
 ALTER TABLE `krrtfir_rtfund`.`DMS_documents` ADD INDEX `Index_2`(`ObjectType`, `ObjectID`, `ObjectID2`);

 OFC_SendComments
 OFC_RefLetters
 
 ALTER TABLE `krrtfir_rtfund`.`OFC_send` ADD COLUMN `SeenTime` DATETIME AFTER `FollowUpDate`;

 OFC_receivers
 
 OFC_DailyTips