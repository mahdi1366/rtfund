<?php
ALTER TABLE `main_rtfund`.`LON_installments` DROP COLUMN `InstallmentWage`;

ALTER TABLE `main_rtfund`.`LON_ReqParts` ADD COLUMN `FirstTotalWage` DECIMAL(13) NOT NULL DEFAULT 0 AFTER `BackPayCompute`;

