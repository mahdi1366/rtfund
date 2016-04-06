<?php

/*
 ALTER TABLE `krrtfir_rtfund`.`LON_requests`
MODIFY COLUMN `ReqPersonID` INTEGER UNSIGNED COMMENT 'معرفی کننده ';

update LON_requests set ReqPersonID=null where ReqPersonID=LoanPersonID;

ALTER TABLE `krrtfir_rtfund`.`LON_loans`
ADD COLUMN `IsCustomer` ENUM('YES','NO') NOT NULL DEFAULT 'NO' AFTER `BlockID`;



ALTER TABLE `krrtfir_rtfund`.`DataAudit` MODIFY COLUMN `TableName` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'نام جدول';
*/
?>
