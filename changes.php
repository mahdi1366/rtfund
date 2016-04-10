<?php

/*










CREATE TABLE  `krrtfir_rtfund`.`ATN_traffic` (
  `TrafficID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `PersonID` int(10) unsigned NOT NULL,
  `TrafficDate` date NOT NULL,
  `TrafficTime` time NOT NULL,
  `IsSystemic` enum('YES','NO') NOT NULL DEFAULT 'YES',
  `IsActive` enum('YES','NO') NOT NULL DEFAULT 'YES',
  PRIMARY KEY (`TrafficID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='تردد پرسنل';
	
CREATE TABLE  `krrtfir_rtfund`.`ATN_shifts` (
  `ShiftID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(500) NOT NULL,
  `FromTime` time NOT NULL,
  `ToTime` time NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL,
  PRIMARY KEY (`ShiftID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='شیفت های کاری';
	
CREATE TABLE  `krrtfir_rtfund`.`ATN_PersonShifts` (
  `RowID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `PersonID` int(10) unsigned NOT NULL,
  `ShiftID` int(10) unsigned NOT NULL,
  `FromDate` date NOT NULL,
  `ToDate` date NOT NULL,
  `IsActive` enum('YES','NO') NOT NULL,
  PRIMARY KEY (`RowID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='شیفت های پرسنل';
 * 
 * 
 * 

 * 
 */
?>
