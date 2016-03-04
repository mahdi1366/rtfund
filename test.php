<?php
require_once 'header.inc.php';

function get_data($url) {
    $ch = curl_init();
    $timeout = 5;
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

echo get_data("http://82.99.224.135:9080/palayeshreport/reportWS/report.wsdl");
die();

	require_once('../libtejarat/nusoap.php');
    $ns = "http://sabapardazesh/reportWS/definitions";
    $wsdl2 = "http://82.99.224.135:9080/palayeshreport/reportWS/report.wsdl";
	
    $soapclient = new nusoap_client($wsdl2, '', '5.9.11.86', '81');
    $param = array();
    $param['accountNumber'] = "425273566";
    $param["dateFrom"] = "13940501";
	$param["timeFrom"] = "000000";
    $param["dateTo"] = "13940504";
    $param["timeTo"] = "235959";
    
    $param["paymentTypeId"] = 1;
    $param["bankPayerId"] = "2";
    $param["bankBranchCode"] = "";
	
    $result = $soapclient->call('reportRequest', $param, $ns);
	
	print_r($result);
?>
ALTER TABLE `krrtfir_rtfund`.`BSC_persons` ADD COLUMN `RegNo` INTEGER UNSIGNED NOT NULL AFTER `IsGovermental`,
 ADD COLUMN `RegDate` DATE NOT NULL AFTER `RegNo`,
 ADD COLUMN `RegPlace` VARCHAR(500) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `RegDate`,
 ADD COLUMN `CompanyType` SMALLINT UNSIGNED NOT NULL AFTER `RegPlace`,
 ADD COLUMN `DomainID` INTEGER UNSIGNED NOT NULL AFTER `CompanyType`,
 ADD COLUMN `AccountNo` DECIMAL(20) NOT NULL AFTER `DomainID`,
 ADD COLUMN `WebSite` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `AccountNo`;

 
 CREATE TABLE `krrtfir_rtfund`.`BSC_ActDomain` (
  `DomainID` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `ParentID` INTEGER UNSIGNED NOT NULL,
  `DomainDesc` VARCHAR(500) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `IsActive` ENUM('YES','NO') NOT NULL DEFAULT 'YES',
  PRIMARY KEY (`DomainID`)
)
ENGINE = InnoDB;


ALTER TABLE `krrtfir_rtfund`.`BSC_persons` MODIFY COLUMN `RegNo` INTEGER UNSIGNED,
 MODIFY COLUMN `RegDate` DATE,
 MODIFY COLUMN `RegPlace` VARCHAR(500) CHARACTER SET utf8 COLLATE utf8_general_ci,
 MODIFY COLUMN `CompanyType` SMALLINT(5) UNSIGNED,
 MODIFY COLUMN `DomainID` INTEGER UNSIGNED,
 MODIFY COLUMN `AccountNo` DECIMAL(20,0),
 MODIFY COLUMN `WebSite` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci;

addd menu


ALTER TABLE `krrtfir_rtfund`.`BSC_persons` ADD COLUMN `FatherName` VARCHAR(200) CHARACTER SET utf8 COLLATE utf8_general_ci AFTER `WebSite`,
 ADD COLUMN `ShNo` INTEGER UNSIGNED AFTER `FatherName`;

 

CREATE TABLE  `krrtfir_rtfund`.`BSC_OrgSigners` (
  `RowID` int(10) unsigned NOT NULL auto_increment,
  `PersonID` int(10) unsigned NOT NULL,
  `fullname` varchar(500) NOT NULL,
  `sex` enum('MALE','FEMALE') NOT NULL default 'MALE',
  `NationalID` varchar(10) NOT NULL default '',
  `telephone` varchar(20) NOT NULL default '',
  `mobile` varchar(11) NOT NULL default '',
  `PostDesc` varchar(500) NOT NULL default '',
  `detail` varchar(500) NOT NULL default '',
  PRIMARY KEY  USING BTREE (`RowID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE  `krrtfir_rtfund`.`BSC_licenses` (
  `LicenseID` int(10) unsigned NOT NULL auto_increment,
  `PersonID` int(10) unsigned NOT NULL,
  `title` varchar(1000) NOT NULL,
  `LicenseNo` varchar(100) default NULL,
  `ExpDate` date default NULL,
  PRIMARY KEY  (`LicenseID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `krrtfir_rtfund`.`DMS_documents` MODIFY COLUMN `DocDesc` VARCHAR(200) 
CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'توضیحات کلی';

ALTER TABLE `krrtfir_rtfund`.`BSC_licenses` ADD COLUMN `IsConfirm` 
ENUM('YES','NO') NOT NULL DEFAULT 'NO' AFTER `ExpDate`;
