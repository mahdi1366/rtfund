<?php

/*
 ALTER TABLE `krrtfir_rtfund`.`LON_requests`
MODIFY COLUMN `ReqPersonID` INTEGER UNSIGNED COMMENT 'معرفی کننده ';

update LON_requests set ReqPersonID=null where ReqPersonID=LoanPersonID;

ALTER TABLE `krrtfir_rtfund`.`LON_loans`
ADD COLUMN `IsCustomer` ENUM('YES','NO') NOT NULL DEFAULT 'NO' AFTER `BlockID`;


*/
?>
