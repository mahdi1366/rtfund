<?php
ALTER TABLE `krrtfir_rtfund`.`VOT_FilledItems`
CHANGE COLUMN `ItemValue` `FilledValue` VARCHAR(500) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
