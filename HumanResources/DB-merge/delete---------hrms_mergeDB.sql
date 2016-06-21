create view UsersSystems as SELECT * FROM framework.UsersSystems;
create view AccountSpecs as SELECT PersonID,WebUserID as UserID,PersonType FROM framework.AccountSpecs;
-- --------------------------------------------------------
CREATE TABLE  `banks` (
  `bank_id` smallint(6) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) COLLATE utf8_persian_ci NOT NULL DEFAULT '',
  `branch_code` int(11) DEFAULT NULL,
  `type` smallint(1) DEFAULT NULL,
  PRIMARY KEY (`bank_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2010 DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;
insert into banks select * from tmp_hrms.banks;
-- --------------------------------------------------------
CREATE TABLE  `Basic_Type` (
  `TypeID` int(11) NOT NULL AUTO_INCREMENT,
  `Title` varchar(45) COLLATE utf8_persian_ci NOT NULL DEFAULT '0',
  `FieldName` varchar(45) COLLATE utf8_persian_ci DEFAULT NULL,
  PRIMARY KEY (`TypeID`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;
insert into Basic_Type select * from Basic_Type;
-- --------------------------------------------------------
CREATE TABLE  `Basic_Info` (
  `TypeID` int(10) unsigned NOT NULL,
  `InfoID` int(10) unsigned NOT NULL,
  `Title` varchar(200) COLLATE utf8_persian_ci NOT NULL,
  `MasterID` int(11) NOT NULL DEFAULT 0,
  `MasterType` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`TypeID`,`InfoID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;
insert into Basic_Info select * from Basic_Info;
-- --------------------------------------------------------
CREATE TABLE  `countries` (
  `country_id` smallint(6) NOT NULL AUTO_INCREMENT,
  `ptitle` varchar(50) COLLATE utf8_persian_ci NOT NULL DEFAULT '',
  `etitle` varchar(50) COLLATE utf8_persian_ci DEFAULT NULL,
  PRIMARY KEY (`country_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1152 DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;
insert into countries select * from tmp_hrms.countries;
-- --------------------------------------------------------
CREATE TABLE  `states` (
  `state_id` smallint(6) NOT NULL AUTO_INCREMENT,
  `ptitle` varchar(100) COLLATE utf8_persian_ci NOT NULL DEFAULT '',
  `etitle` varchar(100) COLLATE utf8_persian_ci DEFAULT NULL,
  PRIMARY KEY (`state_id`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;

insert into states select * from tmp_hrms.states;
-- --------------------------------------------------------
CREATE TABLE `cities` (
  `city_id` smallint(6) NOT NULL AUTO_INCREMENT,
  `state_id` smallint(6) NOT NULL,
  `ptitle` varchar(100) COLLATE utf8_persian_ci NOT NULL DEFAULT '',
  `etitle` varchar(100) COLLATE utf8_persian_ci DEFAULT NULL,
  PRIMARY KEY (`city_id`),
  KEY `FK_CITIES__STATES` (`state_id`),
  CONSTRAINT `FK_CITIES__STATES` FOREIGN KEY (`state_id`) REFERENCES `states` (`state_id`)
) ENGINE=InnoDB AUTO_INCREMENT=381 DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;
insert into cities select * from tmp_hrms.cities;
-- --------------------------------------------------------
CREATE TABLE  `cost_centers` (
  `title` varchar(50) COLLATE utf8_persian_ci NOT NULL DEFAULT '',
  `cost_center_id` smallint(6) NOT NULL AUTO_INCREMENT,
  `daily_work_place_no` varchar(20) COLLATE utf8_persian_ci DEFAULT NULL,
  `detective_name` varchar(100) COLLATE utf8_persian_ci DEFAULT NULL,
  `employer_name` varchar(50) COLLATE utf8_persian_ci DEFAULT NULL,
  `detective_address` varchar(255) COLLATE utf8_persian_ci DEFAULT NULL,
  `collective_security_branch` varchar(50) COLLATE utf8_persian_ci DEFAULT NULL,
  PRIMARY KEY (`cost_center_id`)
) ENGINE=InnoDB AUTO_INCREMENT=91 DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;
insert into cost_centers select * from tmp_hrms_sherkati.cost_centers;
insert into cost_centers(title,cost_center_id) select c.title,c.cost_center_id from tmp_hrms.cost_centers as c on duplicate key UPDATE title=c.title;

-- --------------------------------------------------------
CREATE TABLE  `job_category` (
  `jcid` smallint(6) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) COLLATE utf8_persian_ci DEFAULT NULL,
  PRIMARY KEY (`jcid`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;
insert into job_category select * from tmp_hrms.job_category;
-- --------------------------------------------------------
CREATE TABLE  `job_subcategory` (
  `jcid` smallint(6) NOT NULL,
  `jsid` smallint(6) NOT NULL,
  `title` varchar(50) COLLATE utf8_persian_ci DEFAULT NULL,
  PRIMARY KEY (`jcid`,`jsid`),
  CONSTRAINT `FK_JSUB_CAT__JOB_CATEGORY` FOREIGN KEY (`jcid`) REFERENCES `job_category` (`jcid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;
insert into job_subcategory select * from tmp_hrms.job_subcategory;
-- --------------------------------------------------------
CREATE TABLE  `job_fields` (
  `jfid` int(11) NOT NULL,
  `jcid` smallint(6) NOT NULL,
  `jsid` smallint(6) NOT NULL,
  `grade` smallint(6) NOT NULL DEFAULT '0',
  `start_group` smallint(6) NOT NULL DEFAULT '0',
  `title` varchar(50) COLLATE utf8_persian_ci NOT NULL DEFAULT '',
  `conditions` text COLLATE utf8_persian_ci,
  `duties` text COLLATE utf8_persian_ci,
  `educ_research` tinyint(1) DEFAULT '0',
  `job_type` smallint(5) unsigned NOT NULL DEFAULT '1',
  `job_level` smallint(6) NOT NULL DEFAULT '1',
  PRIMARY KEY (`jfid`),
  KEY `FK_JOB_FIELDS__JOB_SUBCATEGORY` (`jcid`,`jsid`),
  CONSTRAINT `FK_JFIELDS__JOB_CATEGORY` FOREIGN KEY (`jcid`) REFERENCES `job_category` (`jcid`),
  CONSTRAINT `FK_JOB_FIELDS__JOB_SUBCATEGORY` FOREIGN KEY (`jcid`, `jsid`) REFERENCES `job_subcategory` (`jcid`, `jsid`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;
insert into job_fields select * from tmp_hrms.job_fields;

-- --------------------------------------------------------
CREATE TABLE  `org_new_units` (
  `ouid` smallint(6) NOT NULL AUTO_INCREMENT,
  `org_unit_type` smallint(6) DEFAULT '0',
  `ptitle` varchar(100) COLLATE utf8_persian_ci NOT NULL DEFAULT '',
  `etitle` varchar(100) COLLATE utf8_persian_ci DEFAULT NULL,
  `daily_work_place_no` varchar(20) COLLATE utf8_persian_ci DEFAULT NULL,
  `contract_work_place_no` varchar(20) COLLATE utf8_persian_ci DEFAULT NULL,
  `state_id` smallint(6) DEFAULT '0',
  `ctid` int(11) DEFAULT '0',
  `tcid` smallint(4) DEFAULT '0',
  `ccid` smallint(4) DEFAULT '0',
  `detective_name` varchar(100) COLLATE utf8_persian_ci DEFAULT NULL,
  `employer_name` varchar(50) COLLATE utf8_persian_ci DEFAULT NULL,
  `detective_address` varchar(255) COLLATE utf8_persian_ci DEFAULT NULL,
  `collective_security_branch` varchar(50) COLLATE utf8_persian_ci DEFAULT NULL,
  `parent_path` varchar(255) COLLATE utf8_persian_ci DEFAULT NULL,
  `parent_ouid` smallint(6) DEFAULT NULL,
  `manager_post_id` bigint(20) DEFAULT NULL,
  `unicef_code` smallint(6) DEFAULT '0',
  `ouid_old` int(11) DEFAULT NULL,
  `sub_ouid_old` int(11) DEFAULT NULL,
  `RegDate` date DEFAULT NULL COMMENT 'تاريخ ثبت',
  `UnitType` smallint(5) unsigned DEFAULT NULL COMMENT 'نوع واحد',
  `LevelType` smallint(5) unsigned DEFAULT NULL COMMENT 'نوع سطح',
  PRIMARY KEY (`ouid`),
  KEY `manager_post_id` (`manager_post_id`),
  KEY `parent_ouid` (`parent_ouid`)
) ENGINE=InnoDB AUTO_INCREMENT=565 DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;
insert into org_new_units select * from tmp_hrms.org_new_units;
-- --------------------------------------------------------
CREATE TABLE  `persons` (
  `PersonID` int(11) NOT NULL auto_increment,
  `pfname` varchar(30) collate utf8_persian_ci default NULL COMMENT 'نام',
  `plname` varchar(30) collate utf8_persian_ci default NULL COMMENT 'نام خانوادگی',
  `efname` varchar(30) collate utf8_persian_ci default NULL COMMENT 'نام انگلیسی',
  `elname` varchar(30) collate utf8_persian_ci default NULL COMMENT 'نام خانوادگی انگلیسی',
  `father_name` varchar(30) collate utf8_persian_ci default NULL COMMENT 'نام پدر',
  `idcard_no` varchar(30) collate utf8_persian_ci default NULL COMMENT 'شماره شناسنامه',
  `birth_date` date default NULL COMMENT 'تاریخ تولد',
  `birth_city_id` smallint(6) default NULL COMMENT 'شهر محل تولد - کلید به جدول cities',
  `birth_place` varchar(30) collate utf8_persian_ci default NULL COMMENT 'محل تولد',
  `issue_date` date default NULL,
  `issue_city_id` smallint(6) default NULL COMMENT 'شهر صدور شناسنامه - کلید به جدول cities',
  `issue_place` varchar(30) collate utf8_persian_ci default NULL COMMENT 'محل صدور شناسنامه',
  `country_id` smallint(6) default NULL COMMENT 'کد کشور - کلید به جدول countries',
  `national_code` varchar(30) collate utf8_persian_ci default NULL COMMENT 'کد ملی',
  `sex` smallint(6) default NULL COMMENT 'جنسیت',
  `marital_status` smallint(6) default NULL COMMENT 'وضعیت تاهل',
  `locality_type` smallint(1) default NULL COMMENT 'بومی؟',
  `address1` varchar(255) collate utf8_persian_ci default NULL COMMENT 'آدرس',
  `postal_code1` varchar(10) collate utf8_persian_ci default NULL COMMENT 'کد پستی',
  `home_phone1` varchar(14) collate utf8_persian_ci default NULL COMMENT 'تلفن منزل',
  `address2` varchar(255) collate utf8_persian_ci default NULL COMMENT 'آدرس ۲',
  `postal_code2` varchar(10) collate utf8_persian_ci default NULL COMMENT 'کد پستی ۲',
  `home_phone2` varchar(14) collate utf8_persian_ci default NULL COMMENT 'تلفن منزل ۲',
  `work_phone` varchar(14) collate utf8_persian_ci default NULL COMMENT 'تلفن محل کار',
  `work_int_phone` varchar(14) collate utf8_persian_ci default NULL COMMENT 'شماره داخلی',
  `mobile_phone` varchar(14) collate utf8_persian_ci default NULL COMMENT 'تلفن همراه',
  `religion` smallint(6) default NULL COMMENT 'دین',
  `subreligion` smallint(6) default NULL COMMENT 'مذهب',
  `nationality` smallint(6) default NULL COMMENT 'ملیت',
  `insure_no` varchar(20) collate utf8_persian_ci default NULL COMMENT 'شماره بیمه',
  `military_status` smallint(1) default NULL COMMENT 'وضعیت سربازی',
  `military_type` smallint(1) default NULL COMMENT 'نوع خدمت نظام وظیفه',
  `military_from_date` date default NULL COMMENT 'تاریخ شروع سربازی',
  `military_to_date` date default NULL COMMENT 'تاریخ پایان سربازی',
  `military_duration` smallint(2) default NULL COMMENT 'طول مدت سربازی',
  `military_comment` varchar(255) collate utf8_persian_ci default NULL COMMENT 'توضیح در مورد نظام وظیفه',
  `email` varchar(50) collate utf8_persian_ci default NULL COMMENT 'پست الکترونیک',
  `family_protector` tinyint(1) default NULL COMMENT 'سرپرست خانوار است؟',
  `role_student` smallint(1) default NULL COMMENT 'نقش دانشجو',
  `role_staff` smallint(1) default NULL COMMENT 'نقش کارمند',
  `role_part_time_teacher` smallint(1) default NULL COMMENT 'نقش استاد پاره وقت',
  `role_burse` smallint(1) default NULL COMMENT 'نقش بورسیه',
  `role_other` smallint(1) default NULL COMMENT 'نقش متفرقه',
  `birth_state_id` smallint(6) default NULL COMMENT 'استان محل تولد - کلید به جدول states',
  `issue_state_id` smallint(6) default NULL COMMENT 'استان محل صدور شناسنامه - کلید به جدول states',
  `HomePage` varchar(100) collate utf8_persian_ci default NULL COMMENT 'آدرس صفحه خانگی',
  `person_type` smallint(6) default NULL COMMENT 'نوع استخدام',
  `idcard_serial` varchar(15) collate utf8_persian_ci default NULL COMMENT 'شماره سریال شناسنامه',
  `military_duration_day` smallint(2) default NULL COMMENT 'طول مدت سربازی (روز)',
  `comment` varchar(255) collate utf8_persian_ci default NULL COMMENT 'توضیحات',
  PRIMARY KEY  (`PersonID`),
  KEY `national_code` (`national_code`),
  KEY `FK_PERSONS__CITIES` (`birth_city_id`),
  KEY `FK_PERSONS__STATES__1` (`birth_state_id`),
  KEY `FK_PERSONS__STATES__2` (`issue_state_id`),
  KEY `FK_PERSONS__CITIES__2` (`issue_city_id`),
  KEY `FK_PERSONS__COUNTRIES` (`country_id`),
  CONSTRAINT `FK_PERSONS__CITIES` FOREIGN KEY (`birth_city_id`) REFERENCES `cities` (`city_id`),
  CONSTRAINT `FK_PERSONS__CITIES__2` FOREIGN KEY (`issue_city_id`) REFERENCES `cities` (`city_id`),
  CONSTRAINT `FK_PERSONS__COUNTRIES` FOREIGN KEY (`country_id`) REFERENCES `countries` (`country_id`),
  CONSTRAINT `FK_PERSONS__STATES__1` FOREIGN KEY (`birth_state_id`) REFERENCES `states` (`state_id`),
  CONSTRAINT `FK_PERSONS__STATES__2` FOREIGN KEY (`issue_state_id`) REFERENCES `states` (`state_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;
insert into persons select * from tmp_hrms.persons;
update persons set person_type=5 where person_type=6
-- --------------------------------------------------------
CREATE TABLE  `staff` (
  `staff_id` int(11) NOT NULL COMMENT 'شماره شناسایی',
  `PersonID` int(11) NOT NULL COMMENT 'شناسه شخص',
  `personel_no` varchar(15) collate utf8_persian_ci default NULL COMMENT 'شماره مستخدم',
  `person_type` smallint(6) unsigned NOT NULL default '0' COMMENT 'نوع شخص 1 - هیات علمی 2 - کارمند 3 - روزمزد بیمه ای 4 - بورسیه 5 - قرارداد یکساله 6 - قرارداد معین 10 - بازنشسته',
  `bank_id` smallint(6) default NULL COMMENT 'کد بانک',
  `account_no` varchar(20) collate utf8_persian_ci default NULL COMMENT 'شماره حساب',
  `last_writ_id` int(11) default NULL COMMENT 'شماره آخرین حکم شخص',
  `last_writ_ver` smallint(2) default NULL COMMENT 'نگارش آخرین حکم شخص',
  `last_cost_center_id` int default NULL comment 'مرکز هزینه فرد بر اساس آخرین حکم',
  `last_retired_pay` date default NULL COMMENT 'آخرین تاریخ پرداخت کسور بازنشستگی',
  `barcode` varchar(12) collate utf8_persian_ci default NULL COMMENT 'بار کد',
  `work_start_date` date default NULL COMMENT 'تاریخ شروع به کار',
  `card_no` smallint(6) default NULL COMMENT 'شماره کارت شناسایی',
  `tafsili_id` int(11) default NULL COMMENT 'کد تفصیلی',
  `UnitCode` int(11) unsigned default NULL COMMENT 'کد واحد محل خدمت',
  `FacCode` smallint(5) unsigned COMMENT 'کد دانشکده ',
  `EduGrpCode` int(11) unsigned default NULL COMMENT 'کد گروه آموزشی',
  `ProCode` int(11) unsigned default NULL COMMENT 'کد استاد',
  `DutyUnit` int(11) default NULL COMMENT 'واحد موظف',
  `extra_work_coef` decimal(5,2) default NULL COMMENT 'ضریب اضافه کار',
  `sum_paied_pension` decimal(15,0) NOT NULL default '0' COMMENT 'مقرری پرداخت شده',
  `retired_date` date default NULL COMMENT 'تاریخ بازنشستگی',
  `retired_state` smallint(6) default NULL COMMENT 'وضعیت بازنشستگی',
  `job_id` int(11) default NULL COMMENT 'کد شغل',
  `post_id` bigint(20) default NULL COMMENT 'کد پست',
  `ouid` smallint(6) default NULL COMMENT 'کد واحد سازمانی',
  `last_person_type` smallint(6) default NULL COMMENT 'نوع فرد در سیستم قبلی برای نوع فرد 5 و 6',
  PRIMARY KEY  (`staff_id`),
  KEY `PersonID` (`PersonID`),
  KEY `last_writ_id` (`last_writ_id`,`last_writ_ver`),
  KEY `idx_procode` (`ProCode`),
  KEY `FK_STAFF__BANKS` (`bank_id`),
  CONSTRAINT `FK_STAFF__BANKS` FOREIGN KEY (`bank_id`) REFERENCES `banks` (`bank_id`),
  CONSTRAINT `FK_STAFF__PERSONS` FOREIGN KEY (`PersonID`) REFERENCES `persons` (`PersonID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;

insert into staff
select staff_id,PersonID,personel_no,person_type,bank_id,account_no,last_writ_id,last_writ_ver,NULL,last_retired_pay,barcode,work_start_date,card_no,
tafsili_id,UnitCode,FacCode,EduGrpCode,ProCode,DutyUnit,extra_work_coef,sum_paied_pension,retired_date,retired_state,job_id,post_id,ouid,NULL from tmp_hrms.staff;

insert into staff
select s.staff_id,s.PersonID,s.personel_no,s.person_type,s.bank_id,s.account_no,s.last_writ_id,s.last_writ_ver,NULL,s.last_retired_pay,s.barcode,s.work_start_date,s.card_no,
s.tafsili_id,s.UnitCode,s.FacCode,s.EduGrpCode,s.ProCode,s.DutyUnit,s.extra_work_coef,s.sum_paied_pension,s.retired_date,s.retired_state,
s.job_id,s.post_id,s.ouid,s.person_type from tmp_hrms_sherkati.staff as s join tmp_hrms.persons as p using(PersonID);

update staff set person_type = 5 where person_type=6;

update staff s join writs w on(s.last_writ_id=w.writ_id AND s.last_writ_ver=w.writ_ver AND s.staff_id=w.staff_id)
set s.last_cost_center_id=w.cost_center_id
where s.last_writ_id=w.writ_id AND s.last_writ_ver=w.writ_ver AND s.staff_id=w.staff_id;

CREATE  TABLE last_payment as
	select distinct p.staff_id,p.cost_center_id from tmp_hrms.payment_items p join
    (select  s.staff_id,max(concat(pay_year,pay_month)) pay_date
      from staff s join tmp_hrms.payment_items p
      on(p.staff_id=s.staff_id)
      where last_cost_center_id is null
      group by s.staff_id) tbl
   on(p.staff_id=tbl.staff_id AND p.pay_year=substr(pay_date,1,4) AND p.pay_month=substr(pay_date,5))
;
update staff s join last_payment p  on(s.staff_id=p.staff_id)
set s.last_cost_center_id=p.cost_center_id
where s.last_cost_center_id is null AND s.staff_id=p.staff_id
;
drop table last_payment;


CREATE  TABLE last_payment as
select distinct p.staff_id,p.cost_center_id from tmp_hrms_sherkati.payment_items p join
    (select  s.staff_id,max(concat(pay_year,pay_month)) pay_date
      from staff s join tmp_hrms.payment_items p
      on(p.staff_id=s.staff_id)
      where last_cost_center_id is null
      group by s.staff_id) tbl
   on(p.staff_id=tbl.staff_id AND p.pay_year=substr(pay_date,1,4) AND p.pay_month=substr(pay_date,5))
;
update staff s join last_payment p  on(s.staff_id=p.staff_id)
set s.last_cost_center_id=p.cost_center_id
where s.last_cost_center_id is null AND s.staff_id=p.staff_id
;
drop table last_payment;
-- --------------------------------------------------------
CREATE TABLE  `staff_groups` (
  `staff_group_id` smallint(6) NOT NULL auto_increment,
  `title` varchar(50) collate utf8_persian_ci NOT NULL default '',
  PRIMARY KEY  (`staff_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;

insert into staff_groups select * from tmp_hrms.staff_groups;
insert into staff_groups select * from tmp_hrms_sherkati.staff_groups;

-- ------------------------------------------------------------

CREATE TABLE  `staff_group_members` (
  `staff_group_id` smallint(6) NOT NULL,
  `staff_id` int(11) NOT NULL,
  PRIMARY KEY  (`staff_group_id`,`staff_id`),
  KEY `FK_STAFF_GROUP_MEMBERS__STAFF` (`staff_id`),
  CONSTRAINT `FK_STAFF_GROUP_MEMBERS__STAFF` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`staff_id`),
  CONSTRAINT `FK_STF_GROUP_MEMBERS__STAFF_GROUPS` FOREIGN KEY (`staff_group_id`) REFERENCES `staff_groups` (`staff_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;

-- --------------------------------------------------------
CREATE TABLE  `position` (
  `post_id` bigint(20) NOT NULL DEFAULT '0',
  `ouid` smallint(6) NOT NULL DEFAULT '0',
  `post_rowno` smallint(6) NOT NULL DEFAULT '0',
  `post_no` varchar(10) COLLATE utf8_persian_ci DEFAULT '0',
  `post_type` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `title` varchar(100) COLLATE utf8_persian_ci NOT NULL DEFAULT '',
  `included` tinyint(1) DEFAULT '0',
  `jfid` int(11) DEFAULT NULL,
  `validity_start` date DEFAULT '0000-00-00',
  `validity_end` date DEFAULT NULL,
  `description` varchar(255) COLLATE utf8_persian_ci DEFAULT NULL,
  `parent_post_id` bigint(20) DEFAULT NULL,
  `parent_path` varchar(255) COLLATE utf8_persian_ci DEFAULT NULL,
  `is_dummy_post` smallint(6) NOT NULL DEFAULT '0',
  `staff_id` int(11) unsigned DEFAULT NULL,
  `person_type` smallint(6) unsigned DEFAULT NULL,
  `RegDate` date DEFAULT NULL COMMENT 'تاريخ ثبت',
  `RoleID` int DEFAULT 0 COMMENT 'کد سمت از domain',
  PRIMARY KEY (`post_id`),
  KEY `ouid` (`ouid`),
  KEY `ouid_2` (`ouid`),
  KEY `parent_post_id` (`parent_post_id`),
  KEY `staff_id` (`staff_id`,`person_type`),
  KEY `person_type` (`person_type`,`staff_id`),
  KEY `jfid` (`jfid`),
  CONSTRAINT `jfid` FOREIGN KEY (`jfid`) REFERENCES `job_fields` (`jfid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;

insert into position select * from tmp_hrms.position;
insert into position select p.post_id,p.ouid,p.post_rowno,p.post_no,p.post_type,p.title,p.included,p.jfid,p.validity_start,p.validity_end,p.description,p.parent_post_id,
p.parent_path,p.is_dummy_post,p.staff_id,5,'0000-00-00',0 from tmp_hrms_sherkati.position as p join job_fields using(jfid) ;
-- --------------------------------------------------------

CREATE TABLE  `person_dependents` (
  `PersonID` int(11) NOT NULL,
  `row_no` smallint(6) NOT NULL,
  `dependency` smallint(1) NOT NULL DEFAULT '0',
  `fname` varchar(30) COLLATE utf8_persian_ci NOT NULL DEFAULT '',
  `lname` varchar(30) COLLATE utf8_persian_ci NOT NULL DEFAULT '',
  `birth_date` date NOT NULL DEFAULT '0000-00-00',
  `idcard_no` varchar(30) COLLATE utf8_persian_ci DEFAULT NULL,
  `idcard_location` varchar(30) COLLATE utf8_persian_ci DEFAULT NULL,
  `father_name` varchar(30) COLLATE utf8_persian_ci DEFAULT NULL,
  `marriage_date` date DEFAULT NULL,
  `separation_date` date DEFAULT NULL,
  `insure_no` varchar(20) COLLATE utf8_persian_ci DEFAULT NULL,
  `comments` varchar(255) COLLATE utf8_persian_ci DEFAULT NULL,
  `dep_person_id` int(10) unsigned DEFAULT NULL COMMENT 'شماره شناسه موظفین',
  `is_heir` smallint(1) NOT NULL DEFAULT '0' COMMENT 'وارث است؟',
  PRIMARY KEY (`PersonID`,`row_no`),
  CONSTRAINT `FK_PERSON_DEPENDENTS__PERSONS` FOREIGN KEY (`PersonID`) REFERENCES `persons` (`PersonID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;

insert into person_dependents select pd.* from tmp_hrms.person_dependents as pd join tmp_hrms.persons using(PersonID);
-- --------------------------------------------------------

CREATE TABLE  `person_dependent_supports` (
  `PersonID` int(11) NOT NULL,
  `master_row_no` smallint(6) NOT NULL,
  `row_no` smallint(6) NOT NULL,
  `from_date` date NOT NULL,
  `to_date` date DEFAULT NULL,
  `support_cause` smallint(1) NOT NULL DEFAULT '0',
  `insure_type` smallint(1) unsigned DEFAULT NULL,
  `status` smallint(6) DEFAULT '1',
  `calc_year_from` smallint(6) DEFAULT NULL,
  `calc_year_to` smallint(6) DEFAULT NULL,
  `calc_month_from` smallint(6) DEFAULT NULL,
  `calc_month_to` smallint(6) DEFAULT NULL,
  `dana_include` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`PersonID`,`master_row_no`,`row_no`),
  CONSTRAINT `FK_PREON_DEP_SUPPORTS__PERSON_DEP` FOREIGN KEY (`PersonID`, `master_row_no`) REFERENCES `person_dependents` (`PersonID`, `row_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;

insert into person_dependent_supports select * from tmp_hrms.person_dependent_supports;
-- --------------------------------------------------------
CREATE TABLE  `person_devotions` (
  `PersonID` int(11) NOT NULL,
  `devotion_row` smallint(6) NOT NULL,
  `devotion_type` smallint(1) default NULL,
  `personel_relation` smallint(1) default NULL,
  `enlisted` smallint(1) default NULL,
  `amount` smallint(6) NOT NULL default '0',
  `from_date` date default NULL,
  `to_date` date default NULL,
  `continous` smallint(1) default NULL,
  `war_place` varchar(30) collate utf8_persian_ci default NULL,
  `letter_no` varchar(20) collate utf8_persian_ci default NULL,
  `letter_date` date default NULL,
  `comments` varchar(255) collate utf8_persian_ci default NULL,
  `duration_include_paied_retired_fraction` smallint(6) default '0' COMMENT 'مدت قابل قبول بازنشستگی که کسور آن  پرداخت شده',
  PRIMARY KEY  (`PersonID`,`devotion_row`),
  CONSTRAINT `FK_PERSON_DEVOTIONS__PERSONS` FOREIGN KEY (`PersonID`) REFERENCES `persons` (`PersonID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;

insert into person_devotions select * from tmp_hrms.person_devotions;
-- --------------------------------------------------------
CREATE TABLE  `person_employments` (
  `PersonID` int(11) NOT NULL,
  `row_no` smallint(6) NOT NULL,
  `from_date` date NOT NULL DEFAULT '0000-00-00',
  `to_date` date NOT NULL DEFAULT '0000-00-00',
  `organization` varchar(30) COLLATE utf8_persian_ci NOT NULL DEFAULT '',
  `unit` varchar(30) COLLATE utf8_persian_ci DEFAULT NULL,
  `org_type` smallint(1) NOT NULL DEFAULT '0',
  `person_type` smallint(6) NOT NULL DEFAULT '0',
  `emp_state` tinyint(3) unsigned DEFAULT '0',
  `emp_mode` smallint(6) NOT NULL DEFAULT '0',
  `title` varchar(50) COLLATE utf8_persian_ci NOT NULL DEFAULT '',
  `unemp_cause` smallint(6) NOT NULL DEFAULT '0',
  `duration_year` smallint(6) NOT NULL DEFAULT '0',
  `duration_month` smallint(6) NOT NULL DEFAULT '0',
  `duration_day` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `retired_duration_year` smallint(6) NOT NULL DEFAULT '0',
  `retired_duration_month` smallint(6) NOT NULL DEFAULT '0',
  `retired_duration_day` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `group_duration_year` smallint(6) NOT NULL DEFAULT '0',
  `group_duration_month` smallint(6) NOT NULL DEFAULT '0',
  `group_duration_day` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `comments` varchar(255) COLLATE utf8_persian_ci DEFAULT NULL,
  PRIMARY KEY (`PersonID`,`row_no`),
  CONSTRAINT `FK_PERSON_EMPLOYMENTS__PERSONS` FOREIGN KEY (`PersonID`) REFERENCES `persons` (`PersonID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;

insert into person_employments select * from tmp_hrms.person_employments;

-- --------------------------------------------------------

CREATE TABLE  `study_fields` (
  `sfid` smallint(6) NOT NULL AUTO_INCREMENT,
  `ptitle` varchar(100) COLLATE utf8_persian_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`sfid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;

insert into study_fields select * from tmp_hrms.study_fields;
-- --------------------------------------------------------

CREATE TABLE  `study_branchs` (
  `sfid` smallint(6) NOT NULL,
  `sbid` smallint(6) NOT NULL,
  `ptitle` varchar(100) COLLATE utf8_persian_ci NOT NULL DEFAULT '',
  `etitle` varchar(100) COLLATE utf8_persian_ci DEFAULT NULL,
  PRIMARY KEY (`sfid`,`sbid`),
  CONSTRAINT `FK_STUDY_BRANCHS__STUDY_FIELDS` FOREIGN KEY (`sfid`) REFERENCES `study_fields` (`sfid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;

insert into study_branchs select * from tmp_hrms.study_branchs;
-- --------------------------------------------------------
CREATE TABLE  `universities` (
  `country_id` smallint(6) NOT NULL default '0',
  `university_id` smallint(6) NOT NULL default '0',
  `university_category` smallint(6) default '0',
  `ptitle` varchar(100) collate utf8_persian_ci NOT NULL default '',
  `etitle` varchar(100) collate utf8_persian_ci default NULL,
  PRIMARY KEY  (`country_id`,`university_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;

insert into universities select * from tmp_hrms.universities;
insert into universities select * from tmp_hrms_sherkati.universities on duplicate key
	update universities.country_id=tmp_hrms_sherkati.universities.country_id;

-- --------------------------------------------------------

CREATE TABLE  `person_educations` (
  `PersonID` int(11) NOT NULL,
  `row_no` smallint(6) NOT NULL,
  `education_level` smallint(1) NOT NULL DEFAULT '0',
  `sfid` smallint(6) DEFAULT NULL,
  `sbid` smallint(6) DEFAULT NULL,
  `doc_date` date DEFAULT '0000-00-00',
  `country_id` smallint(6) DEFAULT NULL,
  `university_id` smallint(6) DEFAULT NULL,
  `grade` decimal(5,2) DEFAULT NULL,
  `thesis_ptitle` varchar(100) COLLATE utf8_persian_ci DEFAULT NULL,
  `thesis_etitle` varchar(100) COLLATE utf8_persian_ci DEFAULT NULL,
  `burse` tinyint(1) DEFAULT '0',
  `certificated` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`PersonID`,`row_no`),
  KEY `university_id` (`university_id`),
  KEY `FK_PERSON_EDU__STUDY_BRANCHES` (`sfid`,`sbid`),
  KEY `FK_PERSON_EDU__UNIVERSITIES` (`country_id`,`university_id`),
  CONSTRAINT `FK_PERSON_EDUCATIONS__COUNTRIES` FOREIGN KEY (`country_id`) REFERENCES `countries` (`country_id`),
  CONSTRAINT `FK_PERSON_EDUCATIONS__PERSONS` FOREIGN KEY (`PersonID`) REFERENCES `persons` (`PersonID`),
  CONSTRAINT `FK_PERSON_EDU__STUDY_BRANCHES` FOREIGN KEY (`sfid`, `sbid`) REFERENCES `study_branchs` (`sfid`, `sbid`),
  CONSTRAINT `FK_PERSON_EDU__STUDY_FIELDS` FOREIGN KEY (`sfid`) REFERENCES `study_fields` (`sfid`),
  CONSTRAINT `FK_PERSON_EDU__UNIVERSITIES` FOREIGN KEY (`country_id`, `university_id`) REFERENCES `universities` (`country_id`, `university_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;

insert into person_educations select * from tmp_hrms.person_educations;

insert into study_branchs
	select pe.sfid,pe.sbid,'-','-' from tmp_hrms_sherkati.person_educations pe left join study_branchs s on(s.sfid=pe.sfid and s.sbid=pe.sbid)
	where pe.sfid is not null and pe.sbid is not null and s.sfid is null
	group by pe.sfid,pe.sbid;
	
insert into person_educations
	select pe.* from tmp_hrms_sherkati.person_educations as pe join tmp_hrms.persons p using(PersonID)
	left join person_educations hpe on(hpe.PersonID=pe.PersonID and pe.row_no=hpe.row_no)
	where hpe.PersonID is null;
-- --------------------------------------------------------
CREATE TABLE  `person_misc_docs` (
  `PersonID` int(11) NOT NULL,
  `row_no` smallint(6) NOT NULL,
  `doc_no` varchar(10) COLLATE utf8_persian_ci NOT NULL DEFAULT '',
  `doc_date` date NOT NULL DEFAULT '0000-00-00',
  `title` varchar(100) COLLATE utf8_persian_ci NOT NULL DEFAULT '',
  `attachments` text COLLATE utf8_persian_ci,
  PRIMARY KEY (`PersonID`,`row_no`),
  CONSTRAINT `FK_PERSON_MISC_DOCS__PERSONS` FOREIGN KEY (`PersonID`) REFERENCES `persons` (`PersonID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;

insert into person_misc_docs select * from tmp_hrms.person_misc_docs;
insert into person_misc_docs select * from tmp_hrms_sherkati.person_misc_docs;

-- --------------------------------------------------------
CREATE TABLE  `writ_types` (
  `person_type` smallint(6) NOT NULL default '0',
  `writ_type_id` smallint(6) NOT NULL,
  `title` varchar(100) collate utf8_persian_ci NOT NULL default '',
  PRIMARY KEY  (`person_type`,`writ_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;

insert into writ_types select * from tmp_hrms.writ_types;

-- --------------------------------------------------------

CREATE TABLE `writ_subtypes` (
  `person_type` smallint(6) NOT NULL default '0',
  `writ_type_id` smallint(6) NOT NULL,
  `writ_subtype_id` smallint(6) NOT NULL,
  `title` varchar(50) collate utf8_persian_ci NOT NULL default '',
  `description` text collate utf8_persian_ci,
  `emp_state` tinyint(3) unsigned default NULL,
  `emp_mode` smallint(6) default NULL,
  `worktime_type` smallint(1) default NULL,
  `edit_fields` tinyint(1) NOT NULL default '0',
  `time_limited` tinyint(1) NOT NULL default '0',
  `req_staff_signature` tinyint(1) NOT NULL default '0',
  `automatic` tinyint(1) NOT NULL default '0',
  `salary_pay_proc` tinyint(3) unsigned default '0',
  `items_effect` tinyint(3) unsigned default '0',
  `post_effect` tinyint(3) unsigned default '0',
  `annual_effect` tinyint(3) unsigned default NULL,
  `remember_distance` tinyint(4) default NULL,
  `remember_message` varchar(50) collate utf8_persian_ci default NULL,
  `print_title` varchar(50) collate utf8_persian_ci NOT NULL default '',
  `comments` text collate utf8_persian_ci,
  `show_in_summary_doc` smallint(6) NOT NULL default '0',
  `force_writ_issue` smallint(6) default NULL,
  PRIMARY KEY  (`person_type`,`writ_type_id`,`writ_subtype_id`),
  CONSTRAINT `FK_WRIT_SUBTYPES__WRIT_TYPES` FOREIGN KEY (`person_type`, `writ_type_id`) REFERENCES `writ_types` (`person_type`, `writ_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;

insert into writ_subtypes select * from tmp_hrms.writ_subtypes;

-- --------------------------------------------------------
CREATE TABLE  `salary_item_types` (
  `salary_item_type_id` smallint(6) NOT NULL,
  `person_type` smallint(6) NOT NULL default '0',
  `user_defined` tinyint(1) NOT NULL default '0',
  `effect_type` tinyint(3) unsigned NOT NULL default '0',
  `full_title` char(50) collate utf8_persian_ci NOT NULL default '',
  `print_title` char(50) collate utf8_persian_ci NOT NULL default '',
  `compute_place` tinyint(3) unsigned NOT NULL default '0',
  `insure_include` tinyint(1) NOT NULL default '0',
  `tax_include` tinyint(1) NOT NULL default '0',
  `retired_include` tinyint(1) NOT NULL default '0',
  `user_data_entry` tinyint(1) NOT NULL default '0',
  `salary_compute_type` tinyint(3) unsigned NOT NULL default '0',
  `work_time_unit` tinyint(4) default NULL,
  `multiplicand` tinyint(3) unsigned default NULL,
  `function_name` char(100) collate utf8_persian_ci default NULL,
  `param1_title` char(50) collate utf8_persian_ci default NULL,
  `param1_input` smallint(1) NOT NULL default '0',
  `param2_title` char(50) collate utf8_persian_ci default NULL,
  `param2_input` smallint(1) NOT NULL default '0',
  `param3_title` char(50) collate utf8_persian_ci default NULL,
  `param3_input` smallint(1) NOT NULL default '0',
  `param4_title` varchar(50) collate utf8_persian_ci default NULL,
  `param4_input` smallint(5) unsigned NOT NULL,
  `remember_distance` tinyint(4) default NULL,
  `remember_message` char(50) collate utf8_persian_ci default NULL,
  `print_order` smallint(1) default NULL,
  `validity_start_date` date NOT NULL default '0000-00-00',
  `validity_end_date` date default NULL,
  `total_id` int(11) default NULL,
  `ledger_id` int(11) default NULL,
  `tafsili_id` int(11) default NULL,
  `tafsili2_id` int(11) default NULL,
  `available_for` smallint(6) NOT NULL default '5',
  `backpay_include` smallint(6) NOT NULL default '0',
  `month_length_effect` smallint(6) NOT NULL default '0',
  `pension_include` tinyint(1) NOT NULL default '0',
  `credit_topic` smallint(6) NOT NULL default '1',
  `editable_value` smallint(5) unsigned NOT NULL default '0',
  `param5_title` char(50) collate utf8_persian_ci default NULL,
  `param5_input` smallint(1) NOT NULL default '0',
  `param6_title` char(50) collate utf8_persian_ci default NULL,
  `param6_input` smallint(1) NOT NULL default '0',
  `param7_title` char(50) collate utf8_persian_ci default NULL,
  `param7_input` smallint(1) NOT NULL default '0',
  `ret_function_name` char(100) collate utf8_persian_ci default NULL,
  PRIMARY KEY  (`salary_item_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;

insert into salary_item_types select * from tmp_hrms.salary_item_types;

update salary_item_types set function_name=concat('compute_salary_item5_',substr(function_name,22)) where person_type=5 AND substr(function_name,1,21)='compute_salary_item2_';
update salary_item_types set function_name=concat('compute_salary_item6_',substr(function_name,22)) where person_type=6 AND substr(function_name,1,21)='compute_salary_item3_';
-- --------------------------------------------------------
CREATE TABLE `jobs` (
  `job_id` smallint(6) NOT NULL auto_increment,
  `title` varchar(50) collate utf8_persian_ci NOT NULL default '',
  `job_group` smallint(6) NOT NULL default '0',
  `conditions` text collate utf8_persian_ci,
  `duties` text collate utf8_persian_ci,
  PRIMARY KEY  (`job_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5012 DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;

insert into jobs select * from tmp_hrms.jobs;
insert into jobs select * from tmp_hrms_sherkati.jobs on duplicate key update jobs.job_id=tmp_hrms_sherkati.jobs.job_id;

-- --------------------------------------------------------
CREATE TABLE  `writs` (
  `writ_id` int(11) NOT NULL COMMENT 'شماره حکم',
  `writ_ver` smallint(6) NOT NULL COMMENT 'نگارش حکم',
  `corrective` tinyint(1) NOT NULL default '0' COMMENT 'حکم اصلاحی است؟',
  `corrective_writ_id` int(11) default NULL COMMENT 'شماره حکم اصلاحی',
  `corrective_writ_ver` smallint(6) default NULL COMMENT 'نگارش حکم اصلاحی',
  `corrective_date` date default NULL COMMENT 'تاریخ اصلاح',
  `staff_id` int(15) NOT NULL COMMENT 'شماره شناسایی',
  `post_id` bigint(20) default NULL COMMENT 'کد پست',
  `job_id` smallint(6) default NULL COMMENT 'کد شغل',
  `cost_center_id` smallint(6) default NULL COMMENT 'کد مرکز هزینه',
  `ouid` smallint(6) default NULL COMMENT 'واحد اصلی محل خدمت',
  `sub_ouid` smallint(6) default NULL COMMENT 'واحد فرعی محل خدمت',
  `writ_type_id` smallint(6) NOT NULL COMMENT 'کد نوع اصلی حکم',
  `writ_subtype_id` smallint(6) NOT NULL COMMENT 'کد نوع فرعی حکم',
  `person_type` smallint(6) NOT NULL default '0' COMMENT 'نوع شخص 1 - هیات علمی 2 - کارمند 3 - روزمزد بیمه ای 4 - بورسیه 5 - قرارداد یکساله 6 - قرارداد معین 10 - بازنشسته',
  `emp_state` tinyint(3) unsigned NOT NULL default '0' COMMENT 'حالت استخدامی',
  `emp_mode` smallint(6) NOT NULL default '0' COMMENT 'وضعیت استخدامی',
  `worktime_type` smallint(1) NOT NULL default '0' COMMENT 'نوع زمان کاری',
  `ref_letter_no` varchar(10) collate utf8_persian_ci default NULL COMMENT 'شماره نامه مرجع',
  `ref_letter_date` date default NULL COMMENT 'تاریخ نامه مرجع',
  `send_letter_no` varchar(10) collate utf8_persian_ci default NULL COMMENT 'شماره نامه دبیرخانه',
  `send_letter_date` date default NULL COMMENT 'تاریخ نامه دبیرخانه',
  `issue_date` date NOT NULL default '0000-00-00' COMMENT 'تاریخ صدور',
  `execute_date` date NOT NULL default '0000-00-00' COMMENT 'تاریخ اجرا',
  `pay_date` date NOT NULL default '0000-00-00' COMMENT 'تاریخ پرداخت',
  `contract_start_date` date default NULL COMMENT 'تاریخ شروع قرارداد',
  `contract_end_date` date default NULL COMMENT 'تاریخ پایان قرارداد',
  `education_level` smallint(1) NOT NULL default '0' COMMENT 'مدرک تحصیلی',
  `science_level` smallint(6) default NULL COMMENT 'مرتبه علمی',
  `cur_group` smallint(6) default NULL COMMENT 'گروه',
  `base` smallint(6) default NULL COMMENT 'پایه',
  `description` text collate utf8_persian_ci COMMENT 'شرح حکم',
  `children_count` smallint(6) NOT NULL default '0' COMMENT 'تعداد فرزندان',
  `included_children_count` smallint(6) NOT NULL default '0' COMMENT 'تعداد فرزندان مشمول حق اولاد',
  `marital_status` smallint(6) default NULL COMMENT 'وضعیت تاهل ',
  `family_responsible` tinyint(1) NOT NULL default '0' COMMENT 'سرپرست خانوار؟',
  `onduty_year` smallint(4) NOT NULL default '0' COMMENT 'مدت خدمت (سال)',
  `onduty_day` smallint(2) NOT NULL default '0' COMMENT 'مدت خدمت (روز)',
  `onduty_month` smallint(2) NOT NULL default '0' COMMENT 'مدت خدمت (ماه)',
  `military_type` smallint(1) NOT NULL default '0' COMMENT 'وضعیت خدمت وظیفه ',
  `sbid` smallint(6) default NULL COMMENT 'کد رشته تحصیلی',
  `sfid` smallint(6) default NULL COMMENT 'کد گرایش تحصیلی',
  `annual_effect` smallint(1) default NULL COMMENT 'اثر سنواتی',
  `military_status` smallint(1) NOT NULL default '0' COMMENT 'وضعیت خدمت وظیفه',
  `joined_professor` tinyint(1) NOT NULL default '0' COMMENT 'استاد مهمان',
  `joined_professor_comment` varchar(50) collate utf8_persian_ci default NULL COMMENT 'توضیحات استاد مهمان',
  `automatic` tinyint(1) NOT NULL default '0' COMMENT 'صدور خودکار؟',
  `history_only` tinyint(1) default '0' COMMENT 'فقط ثبت سابقه',
  `warning_date` date default NULL COMMENT 'تاریخ هشدار',
  `warning_message` varchar(50) collate utf8_persian_ci default NULL COMMENT 'پیام هشدار',
  `work_city_id` smallint(6) default NULL COMMENT 'شهر محل خدمت',
  `salary_pay_proc` smallint(2) default NULL COMMENT 'روال پرداخت حقوق ',
  `work_state_id` smallint(6) default NULL COMMENT 'استان محل خدمت',
  `remembered` smallint(1) default NULL COMMENT 'یادآوری شد؟',
  `notes` text collate utf8_persian_ci COMMENT 'توضیحات',
  `state` smallint(2) NOT NULL default '1' COMMENT 'وضعیت حکم 1 - درکارگزینی 2 - وضعیت میانی 3 - در حقوق ',
  `writ_signature_post_title` varchar(50) collate utf8_persian_ci default NULL COMMENT 'عنوان پست امضا کنند ه حکم',
  `writ_signature_post_owner` varchar(50) collate utf8_persian_ci default NULL COMMENT 'دارنده پست امضا کننده حکم',
  `dont_transfer` smallint(6) default '0' COMMENT 'به حقوق منتقل شود؟',
  `correct_completed` smallint(6) NOT NULL default '0' COMMENT 'اصلاح تکمیل شده است؟',
  `hortative_group` smallint(6) NOT NULL default '0' COMMENT 'گروه تشویقی',
  `ouid_old` int(11) default NULL COMMENT 'کد قبلی واحد محل خدمت اصلی',
  `sub_ouid_old` int(11) default NULL COMMENT 'کد قبلی واحد فرعی محل خدمت',
  `related_onduty_year` smallint(4) NOT NULL default '0' COMMENT 'سنوات خدمت مربوط و مشابه (سال)',
  `related_onduty_month` smallint(2) NOT NULL default '0' COMMENT 'سنوات خدمت مربوط و مشابه (ماه)',
  `related_onduty_day` smallint(2) NOT NULL default '0' COMMENT 'سنوات خدمت مربوط و مشابه (روز)',
  `writ_transfer_date` datetime default NULL COMMENT 'تاریخ انتقال حکم به حقوق ',
  `writ_recieve_date` datetime default NULL,
  PRIMARY KEY  (`writ_id`,`writ_ver`, `staff_id`),
  KEY `ouid` (`ouid`,`sub_ouid`),
  KEY `staff_id` (`staff_id`),
  KEY `writ_type_id` (`person_type`,`writ_type_id`,`writ_subtype_id`),
  KEY `post_id` (`post_id`),
  KEY `FK_WRITS__CITIES` (`work_city_id`),
  KEY `FK_WRITS__COST_CENTERS` (`cost_center_id`),
  KEY `FK_WRITS__JOBS` (`job_id`),
  KEY `FK_WRITS__STATES` (`work_state_id`),
  KEY `FK_WRITS__STUDEY_BRANCHS` (`sfid`,`sbid`),
  CONSTRAINT `FK_WRITS__CITIES` FOREIGN KEY (`work_city_id`) REFERENCES `cities` (`city_id`),
  CONSTRAINT `FK_WRITS__COST_CENTERS` FOREIGN KEY (`cost_center_id`) REFERENCES `cost_centers` (`cost_center_id`),
  CONSTRAINT `FK_WRITS__JOBS` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`job_id`),
  CONSTRAINT `FK_WRITS__ORG_UNITS` FOREIGN KEY (`ouid`) REFERENCES `org_new_units` (`ouid`),
  CONSTRAINT `FK_WRITS__POSITION` FOREIGN KEY (`post_id`) REFERENCES `position` (`post_id`),
  CONSTRAINT `FK_WRITS__STAFF` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`staff_id`),
  CONSTRAINT `FK_WRITS__STATES` FOREIGN KEY (`work_state_id`) REFERENCES `states` (`state_id`),
  CONSTRAINT `FK_WRITS__STUDEY_BRANCHS` FOREIGN KEY (`sfid`, `sbid`) REFERENCES `study_branchs` (`sfid`, `sbid`),
  CONSTRAINT `FK_WRITS__STUDY_FIELDS` FOREIGN KEY (`sfid`) REFERENCES `study_fields` (`sfid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;

insert into writs select * from tmp_hrms.writs;

insert into writs select w.writ_id,w.writ_ver,w.corrective,w.corrective_writ_id,w.corrective_writ_ver,w.corrective_date,w.staff_id,
  p.post_id,w.job_id,w.cost_center_id,w.ouid,w.sub_ouid,w.writ_type_id,w.writ_subtype_id,w.person_type,w.emp_state,w.emp_mode,w.worktime_type,w.ref_letter_no,
  w.ref_letter_date,w.send_letter_no,w.send_letter_date,w.issue_date,w.execute_date,w.pay_date,w.contract_start_date,w.contract_end_date,w.education_level,
  w.science_level,w.cur_group,w.base,w.description,w.children_count,w.included_children_count,w.marital_status,w.family_responsible,w.onduty_year,w.onduty_day,
  w.onduty_month,w.military_type,
  b.sbid,b.sfid,
  w.annual_effect,w.military_status,w.joined_professor,w.joined_professor_comment,w.automatic,w.history_only,w.warning_date,w.warning_message,w.work_city_id,
  w.salary_pay_proc,w.work_state_id,w.remembered,w.notes,w.state,w.writ_signature_post_title,w.writ_signature_post_owner,w.dont_transfer,w.correct_completed,
  w.hortative_group,w.ouid_old,w.sub_ouid_old,w.related_onduty_year,w.related_onduty_month,w.related_onduty_day,w.writ_transfer_date,w.writ_recieve_date
   from tmp_hrms_sherkati.writs as w
  left join tmp_hrms.study_branchs as b on(w.sfid=b.sfid and w.sbid=b.sbid)
  left join position as p on(w.post_id=p.post_id);

-- --------------------------------------------------------
CREATE TABLE  `writ_salary_items` (
  `writ_id` int(11) NOT NULL,
  `writ_ver` smallint(6) NOT NULL,
  `staff_id` int(11) NOT NULL default '0',
  `salary_item_type_id` smallint(6) NOT NULL,
  `param1` varchar(100) collate utf8_persian_ci default NULL,
  `param2` varchar(255) collate utf8_persian_ci default NULL,
  `param3` decimal(10,3) default NULL,
  `param4` varchar(255) collate utf8_persian_ci default NULL,
  `value` decimal(15,0) NOT NULL default '0',
  `automatic` tinyint(1) NOT NULL default '0',
  `remember_date` date default NULL,
  `remember_message` varchar(50) collate utf8_persian_ci default NULL,
  `remembered` smallint(1) default NULL,
  `total_id` int(11) default NULL,
  `ledger_id` int(11) default NULL,
  `tafsili_id` int(11) default NULL,
  `tafsili2_id` int(11) default NULL,
  `must_pay` smallint(1) default '1',
  `base_value` decimal(15,0) default '0',
  `edit_reason` varchar(100) collate utf8_persian_ci default NULL,
  `param5` decimal(10,2) default NULL,
  `param6` decimal(10,2) default NULL,
  `param7` varchar(255) collate utf8_persian_ci default NULL,
  PRIMARY KEY  (`writ_id`,`writ_ver`,`salary_item_type_id`,`staff_id`),
  KEY `salary_item_type_id` (`salary_item_type_id`),
  KEY `writ_id` (`writ_id`,`writ_ver`),
  CONSTRAINT `FK_WRIT_SALARY_ITEMS__WRITS` FOREIGN KEY (`writ_id`, `writ_ver`, `staff_id`) REFERENCES `writs` (`writ_id`, `writ_ver`, `staff_id`),
  CONSTRAINT `FK_WRIT_SAL_ITEMS__SAL_ITEM_TYPES` FOREIGN KEY (`salary_item_type_id`) REFERENCES `salary_item_types` (`salary_item_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci ROW_FORMAT=DYNAMIC;

insert into writ_salary_items
select s.writ_id,s.writ_ver,w.staff_id,s.salary_item_type_id,s.param1,s.param2,s.param3,s.param4,s.value,s.automatic,
s.remember_date,s.remember_message,s.remembered,s.total_id,s.ledger_id,s.tafsili_id,s.tafsili2_id,s.must_pay,
s.base_value,s.edit_reason,s.param5,s.param6,s.param7
from tmp_hrms.writ_salary_items as s join tmp_hrms.writs as w on(s.writ_id=w.writ_id and s.writ_ver=w.writ_ver);

insert into writ_salary_items
select s.writ_id,s.writ_ver,w.staff_id,s.salary_item_type_id,s.param1,s.param2,s.param3,s.param4,s.value,s.automatic,
s.remember_date,s.remember_message,s.remembered,s.total_id,s.ledger_id,s.tafsili_id,s.tafsili2_id,s.must_pay,
s.base_value,s.edit_reason,s.param5,s.param6,s.param7
from tmp_hrms_sherkati.writ_salary_items as s join tmp_hrms_sherkati.writs as w on(s.writ_id=w.writ_id and s.writ_ver=w.writ_ver);

-- --------------------------------------------------------

CREATE TABLE  `salary_params` (
  `param_id` int(11) NOT NULL auto_increment,
  `person_type` smallint(6) NOT NULL,
  `param_type` smallint(4) unsigned NOT NULL default '0',
  `from_date` date NOT NULL default '0000-00-00',
  `to_date` date NOT NULL default '0000-00-00',
  `dim1_id` smallint(6) default NULL,
  `dim2_id` smallint(6) default NULL,
  `dim3_id` smallint(6) default NULL,
  `value` decimal(15,2) NOT NULL default '0.00',
  `used` smallint(1) default NULL,
  PRIMARY KEY  (`param_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;

insert into salary_params(person_type,param_type,from_date,to_date,dim1_id,dim2_id,dim3_id
,value,used) select 1,param_type,from_date,to_date,dim1_id,dim2_id,dim3_id
,value,used from tmp_hrms.salary_params;

insert into salary_params(person_type,param_type,from_date,to_date,dim1_id,dim2_id,dim3_id
,value,used) select 5,param_type,from_date,to_date,dim1_id,dim2_id,dim3_id
,value,used from tmp_hrms_sherkati.salary_params;

update salary_params set person_type = 2 where param_type in( 1 , 3 , 4 ,5,10 , 11 ) and person_type = 1;
update salary_params set person_type = 3 where param_type in( 6 , 62 , 8 , 9 ) and person_type = 1;
-- ------------------------------------------------------------

CREATE TABLE  `evaluation_lists` (
  `list_id` int(11) NOT NULL auto_increment,
  `list_date` date NOT NULL default '0000-00-00',
  `ouid` smallint(6) NOT NULL,
  `doc_state` smallint(1) default NULL,
  PRIMARY KEY  (`list_id`),
  KEY `FK_EVAL_LIST__ORG_UNITS` (`ouid`),
  CONSTRAINT `fk_evlist_orgunits` FOREIGN KEY (`ouid`) REFERENCES `org_new_units` (`ouid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci
;
CREATE TABLE  `evaluation_list_items` (
  `list_id` int(11) NOT NULL auto_increment,
  `staff_id` int(11) NOT NULL,
  `functional_score` decimal(4,2) default NULL,
  `job_behaviour_score` decimal(4,2) default NULL,
  `social_behaviour_score` decimal(4,2) default NULL,
  `annual_coef` decimal(5,2) default NULL,
  `high_job_coef` decimal(5,2) default NULL,
  `comments` varchar(255) collate utf8_persian_ci default NULL,
  `scores_sum` decimal(4,2) default NULL,
  PRIMARY KEY  (`list_id`,`staff_id`),
  KEY `FK_EVALUATION_LIST_ITEMS__STAFF` (`staff_id`),
  CONSTRAINT `FK_EVALUATION_LIST_ITEMS__STAFF` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`staff_id`),
  CONSTRAINT `FK_EVAL_LIST_ITEMS__EVAL_LISTS` FOREIGN KEY (`list_id`) REFERENCES `evaluation_lists` (`list_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci
;
insert into evaluation_lists select * from tmp_hrms.evaluation_lists;
insert into evaluation_list_items select * from tmp_hrms.evaluation_list_items;

insert into evaluation_lists select list_id+500,list_date,if(o.ouid is null,1,e.ouid) ,doc_state
from tmp_hrms_sherkati.evaluation_lists e left join org_new_units o using(ouid);

insert into evaluation_list_items select list_id+500,staff_id,functional_score,job_behaviour_score,social_behaviour_score
	,annual_coef,high_job_coef,comments,scores_sum from tmp_hrms_sherkati.evaluation_list_items;

-- ------------------------------------------------------------

CREATE TABLE  `mobilization_lists` (
  `list_id` int(11) NOT NULL,
  `list_date` date NOT NULL default '0000-00-00',
  `ouid` smallint(6) NOT NULL,
  `doc_state` smallint(1) default NULL,
  PRIMARY KEY  (`list_id`),
  KEY `FK_MOBILIZATION_LISTS__ORG_UNITS` (`ouid`),
  CONSTRAINT `FK_MOBILIZATION_LISTS__ORG_UNITS` FOREIGN KEY (`ouid`) REFERENCES `org_new_units` (`ouid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;
-- ------------------------------------------------------------

CREATE TABLE  `mobilization_list_items` (
  `list_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `mobilization_coef` decimal(5,2) default NULL,
  `comments` varchar(255) collate utf8_persian_ci default NULL,
  PRIMARY KEY  (`list_id`,`staff_id`),
  KEY `FK_MOBILIZATION_LIST_ITEMS__STAFF` (`staff_id`),
  CONSTRAINT `FK_MOBILIZATION_LIST_ITEMS__STAFF` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`staff_id`),
  CONSTRAINT `FK_MOB_LIST_ITEMS__MOBILIZATION_LISTS` FOREIGN KEY (`list_id`) REFERENCES `mobilization_lists` (`list_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;

insert into mobilization_lists select * from tmp_hrms.mobilization_lists;
insert into mobilization_list_items select * from tmp_hrms.mobilization_list_items;
-- ------------------------------------------------------------

CREATE TABLE  `cost_center_access` (
  `UserID` varchar(100) collate utf8_persian_ci NOT NULL default '',
  `cost_center_id` smallint(6) NOT NULL default '0',
  PRIMARY KEY  (`UserID`,`cost_center_id`),
  KEY `FK_cost_center_access_cost_centers` (`cost_center_id`),
  CONSTRAINT `FK_cost_center_access_cost_centers` FOREIGN KEY (`cost_center_id`) REFERENCES `cost_centers` (`cost_center_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;
-- ------------------------------------------------------------
CREATE TABLE  `person_type_access` (
  `UserID` varchar(100) collate utf8_persian_ci NOT NULL default '',
  `person_type` smallint(6) NOT NULL default '0',
  PRIMARY KEY  (`UserID`,`person_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;
-- -----------------------------------------------------------
CREATE TABLE  `professor_exe_posts` (
  `staff_id` int(11) NOT NULL,
  `row_no` smallint(6) NOT NULL,
  `post_id` bigint(20) default NULL,
  `letter_no` varchar(10) collate utf8_persian_ci default NULL,
  `letter_date` date default NULL,
  `from_date` date NOT NULL default '0000-00-00',
  `to_date` date default NULL,
  `description` varchar(255) collate utf8_persian_ci default NULL,
  PRIMARY KEY  (`staff_id`,`row_no`),
  KEY `FK_PROFESSOR_EXE_POSTS__POSITION` (`post_id`),
  CONSTRAINT `FK_PROFESSOR_EXE_POSTS__POSITION` FOREIGN KEY (`post_id`) REFERENCES `position` (`post_id`),
  CONSTRAINT `FK_PROFESSOR_EXE_POSTS__STAFF` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`staff_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;

insert into professor_exe_posts select * from tmp_hrms.professor_exe_posts;

-- -----------------------------------------------------------
create view payment_items as select * from tmp_hrms.payment_items union ALL select * from tmp_hrms_sherkati.payment_items;
create view payment_writs as select * from tmp_hrms.payment_writs union ALL select * from tmp_hrms_sherkati.payment_writs;

CREATE OR REPLACE VIEW person_courses AS select `r`.`PersonID` AS `PersonID`,`p`.`from_date` AS `from_date`,`p`.`to_date` AS `to_date`,`p`.`hours` AS `total_hours`,`p`.`p_id` AS `p_id`,`p`.`group_id` AS `group_id`,`r`.`related` AS `related`,`p`.`internal` AS `internal`,`r`.`get_award` AS `get_award`,`r`.`award_date` AS `award_date`,`r`.`get_group` AS `get_group`,`r`.`group_date` AS `group_date`,`r`.`message` AS `comments`,`p`.`lesson_id` AS `course_id`,`p`.`group_id` AS `group_course_id`,`p`.`deliver_id` AS `deliver_id`,`l`.`lesson_name` AS `title`,`r`.`state` AS `state`,`r`.`register_no` AS `register_no` from ((`ease`.`SED_presentation` `p` join `ease`.`SED_registeration` `r` on(((`p`.`p_id` = `r`.`p_id`) and (`p`.`group_id` = `r`.`group_id`)))) left join `ease`.`SED_lesson` `l` on((`l`.`lesson_id` = `p`.`lesson_id`))) where ((`r`.`status_hrms` = _utf8'YES') and (`r`.`state` = 2));
-- -----------------------------------------------------------

CREATE TABLE  `DataAudit` (
  `DataAuditID` int(10) unsigned NOT NULL auto_increment,
  `PersonID` int(11) NOT NULL COMMENT 'کد شخصی عمل کننده',
  `RoleID` int(11) NOT NULL COMMENT 'کد نقش فرد',
  `ouid` int(11) NOT NULL COMMENT 'واحد سازمانی فرد عمل کننده',
  `EduGrpCode` int(11) default NULL COMMENT 'کد گروه آموزشی فرد عمل کننده',
  `RelatedPersonType` enum('STAFF','PROF','STUDENT','NONE') collate utf8_persian_ci default 'NONE' COMMENT 'نوع فردی که داده دستکاری شده مربوط به اوست',
  `RelatedPersonID` int(11) default NULL COMMENT 'کد شخصی فردی که روی داده او عمل شده',
  `RelatedStNo` varchar(10) collate utf8_persian_ci default NULL COMMENT 'شماره دانشجویی که روی داده های او عمل شده',
  `TableName` varchar(100) collate utf8_persian_ci NOT NULL COMMENT 'نام جدول',
  `MainObjectID` int(11) NOT NULL COMMENT 'کد داده اصلی دستکاری شده',
  `SubObjectID` int(11) default NULL COMMENT 'کد داده فرعی دستکاری شده',
  `ActionType` enum('ADD','DELETE','UPDATE','VIEW','SEARCH','SEND','RETURN','CONFIRM','REJECT','OTHER') character set utf8 default NULL COMMENT 'نوع عمل',
  `SysCode` int(11) NOT NULL COMMENT 'کد سیستم جاری',
  `PageName` varchar(100) collate utf8_persian_ci NOT NULL COMMENT 'نام صفحه ای این دستکاری توسط آن انجام شده',
  `description` varchar(200) collate utf8_persian_ci default NULL COMMENT 'توضیحات بیشتر',
  `IPAddress` bigint(20) NOT NULL COMMENT 'آدرس آی پی کامپیوتر عمل کننده',
  `ActionTime` datetime NOT NULL COMMENT 'زمان انجام عمل',
  `IsSecure` enum('YES','NO') collate utf8_persian_ci NOT NULL COMMENT 'در صورتیکه در زمان اجرای عمل قفل سخت افزاری وجود داشته باشد بلی در غیر اینصورت خیر',
  PRIMARY KEY  (`DataAuditID`)
) ENGINE=MyISAM AUTO_INCREMENT=7907 DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci COMMENT='اطلاعات ممیزی ';

---------------------------------------------------------------
-- ************************************************************
-- ------------------------------------------------------------
						SALARY QUERIES
-- ------------------------------------------------------------
-- ************************************************************
---------------------------------------------------------------

CREATE TABLE  `payment_runs` (
  `run_id` int(11) NOT NULL auto_increment,
  `time_stamp` int(11) NOT NULL default '0',
  `uname` varchar(25) collate utf8_persian_ci NOT NULL default '',
  PRIMARY KEY  (`run_id`)
) ENGINE=InnoDB AUTO_INCREMENT=295 DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;
-- --------------------------------------------------------
CREATE TABLE  `payments` (
  `staff_id` int(11) NOT NULL,
  `pay_year` smallint(4) NOT NULL DEFAULT '0',
  `pay_month` smallint(2) NOT NULL DEFAULT '0',
  `writ_id` int(11) DEFAULT NULL,
  `writ_ver` smallint(6) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `payment_type` smallint(1) NOT NULL DEFAULT '0',
  `message` varchar(255) COLLATE utf8_persian_ci DEFAULT NULL,
  `bank_id` smallint(6) DEFAULT NULL,
  `account_no` varchar(20) COLLATE utf8_persian_ci DEFAULT NULL,
  `state` smallint(6) NOT NULL DEFAULT '1',
  PRIMARY KEY (`staff_id`,`pay_year`,`pay_month`,`payment_type`),
  KEY `FK_PAYMENTS__BANKS` (`bank_id`),
  CONSTRAINT `FK_PAYMENTS__BANKS` FOREIGN KEY (`bank_id`) REFERENCES `banks` (`bank_id`),
  CONSTRAINT `FK_PAYMENTS__STAFF` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`staff_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;

insert into payments select * from tmp_hrms.payments;
insert into payments select p.* from tmp_hrms_sherkati.payments as p join staff as s using(staff_id);
-- ------------------------------------------------------------

CREATE TABLE  `payment_items` (
  `row_id` int(11) NOT NULL auto_increment,
  `pay_year` smallint(6) NOT NULL default '0',
  `pay_month` smallint(6) NOT NULL default '0',
  `staff_id` int(11) NOT NULL default '0',
  `salary_item_type_id` smallint(4) NOT NULL default '0',
  `pay_value` decimal(15,0) NOT NULL default '0',
  `get_value` decimal(15,0) NOT NULL default '0',
  `param1` varchar(30) collate utf8_persian_ci NOT NULL default '0',
  `param2` varchar(30) collate utf8_persian_ci NOT NULL default '0',
  `param3` varchar(30) collate utf8_persian_ci NOT NULL default '0',
  `param4` varchar(30) collate utf8_persian_ci NOT NULL default '0',
  `param5` varchar(30) collate utf8_persian_ci NOT NULL default '0',
  `param6` varchar(30) collate utf8_persian_ci NOT NULL default '0',
  `cost_center_id` smallint(4) NOT NULL default '0',
  `payment_type` smallint(1) NOT NULL default '0',
  `debt_total_id` int(11) default NULL,
  `debt_ledger_id` int(11) default NULL,
  `debt_tafsili_id` int(11) default NULL,
  `debt_tafsili2_id` int(11) default NULL,
  `cred_total_id` int(11) default NULL,
  `cred_ledger_id` int(11) default NULL,
  `cred_tafsili_id` int(11) default NULL,
  `cred_tafsili2_id` int(11) default NULL,
  `diff_get_value` decimal(15,0) default '0',
  `diff_pay_value` decimal(15,0) default '0',
  `diff_param1` varchar(30) collate utf8_persian_ci NOT NULL default '0',
  `diff_param2` varchar(30) collate utf8_persian_ci NOT NULL default '0',
  `diff_param3` varchar(30) collate utf8_persian_ci NOT NULL default '0',
  `diff_param4` varchar(30) collate utf8_persian_ci NOT NULL default '0',
  `diff_param5` varchar(30) collate utf8_persian_ci NOT NULL default '0',
  `diff_param6` varchar(30) collate utf8_persian_ci NOT NULL default '0',
  `diff_value_coef` smallint(6) NOT NULL default '1',
  `param7` varchar(30) collate utf8_persian_ci NOT NULL default '0',
  `diff_param7` varchar(30) collate utf8_persian_ci NOT NULL default '0',
  `diff_param1_coef` smallint(6) NOT NULL default '1',
  `diff_param2_coef` smallint(6) NOT NULL default '1',
  `diff_param3_coef` smallint(6) NOT NULL default '1',
  `diff_param4_coef` smallint(6) NOT NULL default '1',
  `diff_param5_coef` smallint(6) NOT NULL default '1',
  `diff_param6_coef` smallint(6) NOT NULL default '1',
  `diff_param7_coef` smallint(6) NOT NULL default '1',
  `param8` varchar(30) collate utf8_persian_ci default NULL,
  `param9` varchar(30) collate utf8_persian_ci default NULL,
  `diff_param8` varchar(30) collate utf8_persian_ci default NULL,
  `diff_param9` varchar(30) collate utf8_persian_ci default NULL,
  `diff_param8_coef` smallint(6) default NULL,
  `diff_param9_coef` smallint(6) default NULL,
  PRIMARY KEY  (`row_id`),
  KEY `salary_item_type_id` (`salary_item_type_id`),
  KEY `pay_year` (`pay_year`,`pay_month`),
  KEY `staff_id` (`staff_id`,`payment_type`),
  KEY `pay_year_2` (`pay_year`,`pay_month`,`staff_id`,`payment_type`),
  KEY `cost_center_id` (`cost_center_id`),
  KEY `FK_PAYMENT_ITEMS__PAYMENTS` (`staff_id`,`pay_year`,`pay_month`,`payment_type`),
  CONSTRAINT `FK_PAYMENT_ITEMS__COST_CENTERS` FOREIGN KEY (`cost_center_id`) REFERENCES `cost_centers` (`cost_center_id`),
  CONSTRAINT `FK_PAYMENT_ITEMS__PAYMENTS` FOREIGN KEY (`staff_id`, `pay_year`, `pay_month`, `payment_type`) REFERENCES `payments` (`staff_id`, `pay_year`, `pay_month`, `payment_type`),
  CONSTRAINT `FK_PAYMENT_ITEMS__SAL_ITEM_TYPES` FOREIGN KEY (`salary_item_type_id`) REFERENCES `salary_item_types` (`salary_item_type_id`),
  CONSTRAINT `FK_PAYMENT_ITEMS__STAFF` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`staff_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;

insert into payment_items select * from tmp_hrms.payment_items;
insert into payment_items(pay_year ,pay_month ,staff_id ,salary_item_type_id ,pay_value ,get_value ,param1 ,param2 ,param3 ,param4 ,param5 ,param6 ,cost_center_id ,payment_type ,debt_total_id ,debt_ledger_id ,debt_tafsili_id ,debt_tafsili2_id ,cred_total_id ,cred_ledger_id ,cred_tafsili_id ,cred_tafsili2_id ,diff_get_value ,diff_pay_value ,diff_param1 ,diff_param2 ,diff_param3 ,diff_param4 ,diff_param5 ,diff_param6 ,diff_value_coef ,param7 ,diff_param7 ,diff_param1_coef ,diff_param2_coef ,diff_param3_coef ,diff_param4_coef ,diff_param5_coef ,diff_param6_coef ,diff_param7_coef ,param8 ,param9 ,diff_param8 ,diff_param9 ,diff_param8_coef,diff_param9_coef)
select pay_year ,pay_month ,staff_id ,salary_item_type_id ,pay_value ,get_value ,param1 ,param2 ,param3 ,param4 ,param5 ,param6 ,cost_center_id ,payment_type ,debt_total_id ,debt_ledger_id ,debt_tafsili_id ,debt_tafsili2_id ,cred_total_id ,cred_ledger_id ,cred_tafsili_id ,cred_tafsili2_id ,diff_get_value ,diff_pay_value ,diff_param1 ,diff_param2 ,diff_param3 ,diff_param4 ,diff_param5 ,diff_param6 ,diff_value_coef ,param7 ,diff_param7 ,diff_param1_coef ,diff_param2_coef ,diff_param3_coef ,diff_param4_coef ,diff_param5_coef ,diff_param6_coef ,diff_param7_coef ,param8 ,param9 ,diff_param8 ,diff_param9 ,diff_param8_coef,diff_param9_coef
from tmp_hrms_sherkati.payment_items;
-- ------------------------------------------------------------

CREATE TABLE  `payment_writs` (
  `pay_writ_id` int(11) NOT NULL AUTO_INCREMENT,
  `writ_id` int(11) NOT NULL,
  `writ_ver` smallint(6) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `pay_year` smallint(4) NOT NULL DEFAULT '0',
  `pay_month` smallint(2) NOT NULL DEFAULT '0',
  `payment_type` smallint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`pay_writ_id`),
  UNIQUE KEY `unique_key` (`writ_id`,`writ_ver`,`staff_id`,`pay_year`,`pay_month`,`payment_type`),
  KEY `writ_id` (`writ_id`,`writ_ver`),
  KEY `staff_id` (`staff_id`),
  KEY `FK_PAYMENT_WRITS__PAYMENTS` (`staff_id`,`pay_year`,`pay_month`,`payment_type`),
  CONSTRAINT `FK_PAYMENT_WRITS__PAYMENTS` FOREIGN KEY (`staff_id`, `pay_year`, `pay_month`, `payment_type`) REFERENCES `payments` (`staff_id`, `pay_year`, `pay_month`, `payment_type`),
  CONSTRAINT `FK_PAYMENT_WRITS__STAFF` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`staff_id`),
  CONSTRAINT `FK_PAYMENT_WRITS__WRITS` FOREIGN KEY (`writ_id`, `writ_ver`,`staff_id`) REFERENCES `writs` (`writ_id`, `writ_ver`,`staff_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;

insert into payment_writs(writ_id,writ_ver,staff_id,pay_year,pay_month,payment_type)
  select p.writ_id,p.writ_ver,p.staff_id,p.pay_year,p.pay_month,p.payment_type from tmp_hrms.payment_writs as p
  join staff using(staff_id)
  join writs as w on(w.writ_id=p.writ_id and w.writ_ver=p.writ_ver and w.staff_id=p.staff_id);
  
insert into payment_writs(writ_id,writ_ver,staff_id,pay_year,pay_month,payment_type)
  select p.writ_id,p.writ_ver,p.staff_id,p.pay_year,p.pay_month,p.payment_type
from tmp_hrms_sherkati.payment_writs as p
  join tmp_hrms_sherkati.staff using(staff_id)
  join tmp_hrms_sherkati.writs as w on(w.writ_id=p.writ_id and w.writ_ver=p.writ_ver and w.staff_id=p.staff_id)
  join payments py on(py.staff_id=p.staff_id AND py.pay_year=p.pay_year AND py.pay_month=p.pay_month AND py.payment_type=p.payment_type);
-- ------------------------------------------------------------

CREATE TABLE  `pay_get_lists` (
  `list_id` int(11) NOT NULL AUTO_INCREMENT,
  `list_date` date NOT NULL default '0000-00-00',
  `salary_item_type_id` smallint(6) default NULL,
  `doc_state` int(1) NOT NULL,
  `list_type` smallint(1) default NULL,
  `total_id` int(11) default NULL,
  `ledger_id` int(11) default NULL,
  `tafsili_id` int(11) default NULL,
  `tafsili2_id` int(11) default NULL,
  `cost_center_id` smallint(6) NOT NULL,
  PRIMARY KEY  (`list_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;

ALTER TABLE `tmp_hrmstotal`.`pay_get_lists` ADD CONSTRAINT `FK_pay_get_lists_1` FOREIGN KEY `FK_pay_get_lists_1` (`cost_center_id`)
    REFERENCES `cost_centers` (`cost_center_id`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT,
 ADD CONSTRAINT `FK_pay_get_lists_2` FOREIGN KEY `FK_pay_get_lists_2` (`salary_item_type_id`)
    REFERENCES `salary_item_types` (`salary_item_type_id`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT;
	
insert into pay_get_lists select * from tmp_hrms.pay_get_lists where list_id<>0
insert into pay_get_lists select list_id+414200,list_date,salary_item_type_id,doc_state,list_type,total_id,ledger_id,tafsili_id,tafsili2_id,cost_center_id
	from tmp_hrms_sherkati.pay_get_lists
-- ------------------------------------------------------------

CREATE TABLE  `pay_get_list_items` (
  `list_id` int(11) NOT NULL,
  `list_row_no` smallint(6) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `salary_item_type_id` smallint(6) default NULL,
  `initial_amount` decimal(15,2) default NULL,
  `approved_amount` decimal(15,2) NOT NULL default '0.00',
  `value` decimal(15,0) NOT NULL default '0',
  `comments` varchar(255) collate utf8_persian_ci default NULL,
  `total_id` int(11) default NULL,
  `ledger_id` int(11) default NULL,
  `tafsili_id` int(11) default NULL,
  `tafsili2_id` int(11) default NULL,
  PRIMARY KEY  (`list_id`,`list_row_no`),
  KEY `FK_PAY_GET_LIST_ITEMS__SAL_ITEM_TYPES` (`salary_item_type_id`),
  KEY `FK_PAY_GET_LIST_ITEMS__STAFF` (`staff_id`),
  CONSTRAINT `FK_PAY_GET_LIST_ITEMS__SAL_ITEM_TYPES` FOREIGN KEY (`salary_item_type_id`) REFERENCES `salary_item_types` (`salary_item_type_id`),
  CONSTRAINT `FK_PAY_GET_LIST_ITEMS__STAFF` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`staff_id`),
  CONSTRAINT `FK_PG_LIST_ITEMS__PAY_GET_LISTS` FOREIGN KEY (`list_id`) REFERENCES `pay_get_lists` (`list_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;

insert into pay_get_list_items select * from tmp_hrms.pay_get_list_items

insert into pay_get_list_items select p.list_id+414200,p.list_row_no,p.staff_id,p.salary_item_type_id,p.initial_amount,p.approved_amount,p.value,
p.comments,p.total_id,p.ledger_id,p.tafsili_id,p.tafsili2_id from tmp_hrms_sherkati.pay_get_list_items p join tmp_hrms_sherkati.salary_item_types s using(salary_item_type_id)

















