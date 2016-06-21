
update persons set person_type=5 where person_type=6;
update salary_item_types set function_name=concat('compute_salary_item5_',substr(function_name,22)) where person_type=5 AND substr(function_name,1,21)='compute_salary_item2_';
update salary_item_types set function_name=concat('compute_salary_item6_',substr(function_name,22)) where person_type=6 AND substr(function_name,1,21)='compute_salary_item3_';

-------------------------------------------------------------
-------------------------------------------------------------

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

-- ----------------------------------------------------------------------

insert into writ_types values(5,4,'ﬁ—«—œ«œ «‰Ã«„ ò«— Ìò”«·Â'),(5,5,'ﬁ—«— œ«œ «‰Ã«„ ò«— „‘Œ’');

update writ_subtypes set writ_type_id=4 where writ_type_id=1 and person_type=5;
update writ_subtypes set writ_type_id=5 where writ_type_id=2 and person_type=5;

update writs set writ_type_id=4 where person_type=5 and writ_type_id=1;
update writs set writ_type_id=5 where person_type=5 and writ_type_id=2;
delete from writ_types where person_type=5 and writ_type_id in(1,2);

insert into writ_types select 5,writ_type_id,title from writ_types where person_type=6;
update writ_subtypes set person_type=5 where person_type=6;
update writs set person_type=5 where person_type=6;
delete from writ_types where person_type=6;

CREATE TABLE `map_writ_subtypes` (
  `writ_type_3` INTEGER UNSIGNED NOT NULL DEFAULT NULL AUTO_INCREMENT,
  `writ_type_5` INTEGER UNSIGNED NOT NULL,
  PRIMARY KEY (`writ_type_3`, `writ_type_5`)
)
ENGINE = InnoDB;
insert into map_writ_subtypes values
(1,1),(2,5),(4,4),(5,7),(8,3),(11,11),(12,6),(15,18),(16,10),(17,12),(18,13),(19,14),(21,16),(22,17),(23,8),
(25,19),(26,20),(28,22);

select writ_type_3,w1.title,writ_type_5,w2.title from map_writ_subtypes
join writ_subtypes w1 on(w1.person_type=5 AND w1.writ_type_id=3 and w1.writ_subtype_id=writ_type_3)
join writ_subtypes w2 on(w2.person_type=5 AND w2.writ_type_id=5 and w2.writ_subtype_id=writ_type_5);

update writs set writ_subtype_id=2,writ_type_id=3 where person_type=5 and writ_type_id=5 and writ_subtype_id=5;
update writs set writ_subtype_id=5,writ_type_id=3 where person_type=5 and writ_type_id=5 and writ_subtype_id=7;
update writs set writ_subtype_id=8,writ_type_id=3 where person_type=5 and writ_type_id=5 and writ_subtype_id=3;
update writs set writ_subtype_id=12,writ_type_id=3 where person_type=5 and writ_type_id=5 and writ_subtype_id=6;
update writs set writ_subtype_id=15,writ_type_id=3 where person_type=5 and writ_type_id=5 and writ_subtype_id=18;
update writs set writ_subtype_id=16,writ_type_id=3 where person_type=5 and writ_type_id=5 and writ_subtype_id=10;
update writs set writ_subtype_id=17,writ_type_id=3 where person_type=5 and writ_type_id=5 and writ_subtype_id=12;
update writs set writ_subtype_id=18,writ_type_id=3 where person_type=5 and writ_type_id=5 and writ_subtype_id=13;
update writs set writ_subtype_id=19,writ_type_id=3 where person_type=5 and writ_type_id=5 and writ_subtype_id=14;
update writs set writ_subtype_id=21,writ_type_id=3 where person_type=5 and writ_type_id=5 and writ_subtype_id=16;
update writs set writ_subtype_id=22,writ_type_id=3 where person_type=5 and writ_type_id=5 and writ_subtype_id=17;
update writs set writ_subtype_id=23,writ_type_id=3 where person_type=5 and writ_type_id=5 and writ_subtype_id=8;

update writs set writ_subtype_id=25,writ_type_id=3 where person_type=5 and writ_type_id=5 and writ_subtype_id=19;
update writs set writ_subtype_id=26,writ_type_id=3 where person_type=5 and writ_type_id=5 and writ_subtype_id=20;
update writs set writ_subtype_id=28,writ_type_id=3 where person_type=5 and writ_type_id=5 and writ_subtype_id=22;

update writs set writ_type_id=3 where person_type=5 and writ_type_id=5 and writ_subtype_id in(1,4,11);

-- --------------------------------------------------

insert into writ_subtypes select 5,3,24,title,description,emp_state,emp_mode,worktime_type,edit_fields,time_limited,req_staff_signature,automatic,
salary_pay_proc,items_effect,post_effect,annual_effect,remember_distance,remember_message,
print_title,comments,show_in_summary_doc,force_writ_issue
from writ_subtypes where person_type=5 AND writ_type_id=5 AND writ_subtype_id=2;

insert into writ_subtypes select 5,3,30,title,description,emp_state,emp_mode,worktime_type,edit_fields,time_limited,req_staff_signature,automatic,
salary_pay_proc,items_effect,post_effect,annual_effect,remember_distance,remember_message,
print_title,comments,show_in_summary_doc,force_writ_issue
from writ_subtypes where person_type=5 AND writ_type_id=5 AND writ_subtype_id=9;

insert into writ_subtypes select 5,3,31,title,description,emp_state,emp_mode,worktime_type,edit_fields,time_limited,req_staff_signature,automatic,
salary_pay_proc,items_effect,post_effect,annual_effect,remember_distance,remember_message,
print_title,comments,show_in_summary_doc,force_writ_issue
from writ_subtypes where person_type=5 AND writ_type_id=5 AND writ_subtype_id=15;

insert into writ_subtypes select 5,3,32,title,description,emp_state,emp_mode,worktime_type,edit_fields,time_limited,req_staff_signature,automatic,
salary_pay_proc,items_effect,post_effect,annual_effect,remember_distance,remember_message,
print_title,comments,show_in_summary_doc,force_writ_issue
from writ_subtypes where person_type=5 AND writ_type_id=5 AND writ_subtype_id=21;

-- --------------------------------------------------

update writs set writ_subtype_id=24,writ_type_id=3 where person_type=5 and writ_type_id=5 and writ_subtype_id=2;
update writs set writ_subtype_id=30,writ_type_id=3 where person_type=5 and writ_type_id=5 and writ_subtype_id=9;
update writs set writ_subtype_id=31,writ_type_id=3 where person_type=5 and writ_type_id=5 and writ_subtype_id=15;
update writs set writ_subtype_id=32,writ_type_id=3 where person_type=5 and writ_type_id=5 and writ_subtype_id=21;
-- --------------------------------------------------

delete from writ_subtypes where person_type=5 AND writ_type_id=5;
delete from writ_types where person_type=5 AND writ_type_id=5;

----------------------------------------------------------------------------------------
----------------------------------------------------------------------------------------

update writ_salary_items set salary_item_type_id=633 where salary_item_type_id=631;
update writ_salary_items set salary_item_type_id=609 where salary_item_type_id=608;
update writ_salary_items set salary_item_type_id=632 where salary_item_type_id=630;
update writ_salary_items set salary_item_type_id=10202 where salary_item_type_id=10201;
update writ_salary_items set salary_item_type_id=10211 where salary_item_type_id=10213;
update writ_salary_items set salary_item_type_id=10209 where salary_item_type_id=10208;
update writ_salary_items set salary_item_type_id=628 where salary_item_type_id=627;
update writ_salary_items set salary_item_type_id=885 where salary_item_type_id=883;
update writ_salary_items set salary_item_type_id=10221 where salary_item_type_id=10222;
update writ_salary_items set salary_item_type_id=605 where salary_item_type_id=603;

delete from  salary_item_types where salary_item_type_id in (631,608,630,10201,10213,10208,627,883,10222,603);
update salary_item_types set person_type=5 where person_type=6;

----------------------------------------------------------------------------------------
----------------------------------------------------------------------------------------
;
update salary_item_types set function_name = 'compute_salary_other_premium3' where person_type=3 and salary_item_type_id=10212;
update salary_item_types set function_name = 'compute_salary_other_premium5' where person_type=5 and salary_item_type_id=10211;

----------------------------------------------------------------------------------------
update payment_items set salary_item_type_id=747 where salary_item_type_id=748;
update pay_get_lists set salary_item_type_id=747 where salary_item_type_id=748;
update pay_get_list_items set salary_item_type_id=747 where salary_item_type_id=748;
update writ_salary_items set salary_item_type_id=747 where salary_item_type_id=748;