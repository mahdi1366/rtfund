SELECT * FROM WFM_FlowSteps Where flowID=4;
ALTER TABLE `krrtfir_rtfund`.`WAR_requests` ADD COLUMN `CancelDate` DATE NOT NULL AFTER `RegisterAmount`;
SELECT * FROM BaseInfo where typeID=9;
ALTER TABLE `krrtfir_rtfund`.`BSC_branches` MODIFY COLUMN `IsActive` ENUM('YES','NO') NOT NULL DEFAULT 'YES',
 ADD COLUMN `WarrentyAllowed` ENUM('YES','NO') NOT NULL DEFAULT 'YES' AFTER `DefaultAccountTafsiliID`;

SELECT * FROM DMS_DocParams where DocType=2;

ALTER TABLE `krrtfir_rtfund`.`DMS_DocParams` ADD COLUMN `ParamValues` VARCHAR(45) AFTER `KeyTitle`;

ALTER TABLE `krrtfir_rtfund`.`DMS_DocParams` ADD COLUMN `IsActive` ENUM('YES','NO') NOT NULL DEFAULT 'YES' AFTER `ParamValues`;
