-------------- on hrms database ---------------------

ALTER TABLE banks RENAME TO d_banks;
create view banks as select * from hrmstotal.banks;

ALTER TABLE Basic_Info RENAME TO d_Basic_Info;
create view Basic_Info as select * from hrmstotal.Basic_Info;

ALTER TABLE Basic_Type RENAME TO d_Basic_Type;
create view Basic_Type as select * from hrmstotal.Basic_Type;

ALTER TABLE cities RENAME TO d_cities;
create view cities as select * from hrmstotal.cities;

ALTER TABLE cost_centers RENAME TO d_cost_centers;
create view cost_centers as select * from hrmstotal.cost_centers;

ALTER TABLE countries RENAME TO d_countries;
create view countries as select * from hrmstotal.countries;

ALTER TABLE evaluation_lists RENAME TO d_evaluation_lists;
create view evaluation_lists as select * from hrmstotal.evaluation_lists where list_id<500;

ALTER TABLE evaluation_list_items RENAME TO d_evaluation_list_items;
create view evaluation_list_items as select * from hrmstotal.evaluation_list_items where list_id<500;

ALTER TABLE job_category RENAME TO d_job_category;
create view job_category as select * from hrmstotal.job_category;

ALTER TABLE job_fields RENAME TO d_job_fields;
create view job_fields as select * from hrmstotal.job_fields;

ALTER TABLE job_subcategory RENAME TO d_job_subcategory;
create view job_subcategory as select * from hrmstotal.job_subcategory;

ALTER TABLE jobs RENAME TO d_jobs;
create view jobs as select * from hrmstotal.jobs;

ALTER TABLE mobilization_list_items RENAME TO d_mobilization_list_items;
create view mobilization_list_items as select * from hrmstotal.mobilization_list_items where list_id<100;

ALTER TABLE mobilization_lists RENAME TO d_mobilization_lists;
create view mobilization_lists as select * from hrmstotal.mobilization_lists where list_id<100;

ALTER TABLE org_new_units RENAME TO d_org_new_units;
create view org_new_units as select * from hrmstotal.org_new_units;

ALTER TABLE person_dependent_supports RENAME TO d_person_dependent_supports;
create view person_dependent_supports as select * from hrmstotal.person_dependent_supports;

ALTER TABLE person_dependents RENAME TO d_person_dependents;
create view person_dependents as select * from hrmstotal.person_dependents;

ALTER TABLE person_devotions RENAME TO d_person_devotions;
create view person_devotions as select * from hrmstotal.person_devotions;

ALTER TABLE person_educations RENAME TO d_person_educations;
create view person_educations as select * from hrmstotal.person_educations;

ALTER TABLE person_employments RENAME TO d_person_employments;
create or replace view person_employments as select * from hrmstotal.person_employments;

ALTER TABLE person_misc_docs RENAME TO d_person_misc_docs;
create view person_misc_docs as select * from hrmstotal.person_misc_docs;

ALTER TABLE persons RENAME TO d_persons;
create view persons as select * from hrmstotal.persons where person_type<>5;

ALTER TABLE position RENAME TO d_position;
create view position as select * from hrmstotal.position;

ALTER TABLE salary_item_types RENAME TO d_salary_item_types;
create or replace view salary_item_types AS
  select salary_item_type_id
  ,person_type,user_defined,effect_type,full_title,print_title,compute_place,insure_include,tax_include
  ,retired_include,user_data_entry,salary_compute_type,work_time_unit,multiplicand,
  (case when substr(function_name,1,21)='compute_salary_item5_' then concat('compute_salary_item2_',substr(function_name,22))
        when substr(function_name,1,21)='compute_salary_item6_' then concat('compute_salary_item3_',substr(function_name,22))
		when person_type=3 and salary_item_type_id=10212 then 'compute_salary_other_premium2'
		when person_type=5 and salary_item_type_id=10211 then 'compute_salary_other_premium2'
        else function_name end) as function_name
  ,param1_title,param1_input
  ,param2_title,param2_input,param3_title,param3_input,param4_title,param4_input,remember_distance,remember_message
  ,print_order,validity_start_date,validity_end_date,total_id,ledger_id,tafsili_id,tafsili2_id,available_for
  ,backpay_include,month_length_effect,pension_include,credit_topic,editable_value,param5_title,param5_input
  ,param6_title,param6_input,param7_title,param7_input,ret_function_name from hrmstotal.salary_item_types;

ALTER TABLE salary_params RENAME TO d_salary_params;
create view hrms.salary_params as select param_id,param_type,from_date,to_date,dim1_id,dim2_id,dim3_id,value,used
from hrmstotal.salary_params where person_type<>5;

ALTER TABLE staff RENAME TO d_staff;
create view staff as select * from hrmstotal.staff where (person_type <> 5);

ALTER TABLE staff_group_members RENAME TO d_staff_group_members;
create view staff_group_members as select * from hrmstotal.staff_group_members;

ALTER TABLE staff_groups RENAME TO d_staff_groups;
create view staff_groups as select * from hrmstotal.staff_groups;

ALTER TABLE states RENAME TO d_states;
create view states as select * from hrmstotal.states;

ALTER TABLE study_branchs RENAME TO d_study_branchs;
create view study_branchs as select * from hrmstotal.study_branchs;

ALTER TABLE study_fields RENAME TO d_study_fields;
create view study_fields as select * from hrmstotal.study_fields;

ALTER TABLE universities RENAME TO d_universities;
create view universities as select * from hrmstotal.universities;

ALTER TABLE writ_subtypes RENAME TO d_writ_subtypes;
create view writ_subtypes as select * from hrmstotal.writ_subtypes;

ALTER TABLE writ_types RENAME TO d_writ_types;
create view writ_types as select * from hrmstotal.writ_types;

ALTER TABLE writs RENAME TO d_writs;
CREATE OR REPLACE VIEW hrms.writs AS
select * from hrmstotal.writs where hrmstotal.writs.person_type <> 5;

ALTER TABLE writ_salary_items RENAME TO d_writ_salary_items;
create or replace view hrms.writ_salary_items as select wsi.* from hrmstotal.writ_salary_items wsi join hrms.staff using(staff_id) where person_type <> 5;

ALTER TABLE professor_exe_posts RENAME TO d_professor_exe_posts;
create view professor_exe_posts as select * from hrmstotal.professor_exe_posts;



-- ***************************************************
-------------- on hrms_sherkati ----------------------
-- ***************************************************
;
create or replace view banks as select * from hrmstotal.banks;
create or replace view Basic_Info as select * from hrmstotal.Basic_Info;
create or replace view Basic_Type as select * from hrmstotal.Basic_Type;
create or replace view cities as select * from hrmstotal.cities;
create or replace view job_category as select * from hrmstotal.job_category;

ALTER TABLE cost_centers RENAME TO d_cost_centers;
create view cost_centers as select * from hrmstotal.cost_centers;

ALTER TABLE countries RENAME TO d_countries;
create view countries as select * from hrmstotal.countries;

ALTER TABLE evaluation_list_items RENAME TO d_evaluation_list_items;
create view evaluation_list_items as select * from hrmstotal.evaluation_list_items where list_id>500;

ALTER TABLE evaluation_lists RENAME TO d_evaluation_lists;
create view evaluation_lists as select * from hrmstotal.evaluation_lists where list_id>500;

ALTER TABLE jobs RENAME TO d_jobs;
create view jobs as select * from hrmstotal.jobs;

ALTER TABLE mobilization_list_items RENAME TO d_mobilization_list_items;
create view mobilization_list_items as select * from hrmstotal.mobilization_list_items where list_id>100;

ALTER TABLE mobilization_lists RENAME TO d_mobilization_lists;
create view mobilization_lists as select * from hrmstotal.mobilization_lists where list_id>100;

ALTER TABLE person_educations RENAME TO d_person_educations;
create view person_educations as select * from hrmstotal.person_educations;

ALTER TABLE person_misc_docs RENAME TO d_person_misc_docs;
create view person_misc_docs as select * from hrmstotal.person_misc_docs;

ALTER TABLE position RENAME TO d_position;
create view position as select * from hrmstotal.position;

ALTER TABLE salary_params RENAME TO d_salary_params;
create view hrms_sherkati.salary_params as select param_id,param_type,from_date,to_date,dim1_id,dim2_id,dim3_id,value,used from hrmstotal.salary_params where person_type=5;

ALTER TABLE staff RENAME TO d_staff;
create view staff as select * from hrmstotal.staff where (person_type = 5);

ALTER TABLE staff_group_members RENAME TO d_staff_group_members;
create view staff_group_members as select * from hrmstotal.staff_group_members;

ALTER TABLE staff_groups RENAME TO d_staff_groups;
create view staff_groups as select * from hrmstotal.staff_groups;

ALTER TABLE universities RENAME TO d_universities;
create view universities as select * from hrmstotal.universities;

ALTER TABLE writs RENAME TO d_writs;
CREATE OR REPLACE VIEW hrms_sherkati.writs AS
select * from hrmstotal.writs where hrmstotal.writs.person_type = 5;

ALTER TABLE writ_salary_items RENAME TO d_writ_salary_items;
create or replace view hrms_sherkati.writ_salary_items as select wsi.* from hrmstotal.writ_salary_items wsi join hrms_sherkati.staff using(staff_id) where person_type=5;

CREATE OR REPLACE VIEW hrms_sherkati.persons AS
select * from hrmstotal.persons where person_type=5;

create or replace view hrms_sherkati.person_employments as select * from hrmstotal.person_employments;
create or replace view org_new_units as select * from hrmstotal.org_new_units;
create or replace view job_fields as select * from hrmstotal.job_fields;
create or replace view job_subcategory as select * from hrmstotal.job_subcategory;
create or replace view person_dependent_supports as select * from hrmstotal.person_dependent_supports;
create or replace view person_dependents as select * from hrmstotal.person_dependents;
create or replace view person_devotions as select * from hrmstotal.person_devotions;

create or replace view salary_item_types AS
  select salary_item_type_id
  ,person_type,user_defined,effect_type,full_title,print_title,compute_place,insure_include,tax_include
  ,retired_include,user_data_entry,salary_compute_type,work_time_unit,multiplicand,
  (case when substr(function_name,1,21)='compute_salary_item5_' then concat('compute_salary_item2_',substr(function_name,22))
        when substr(function_name,1,21)='compute_salary_item6_' then concat('compute_salary_item3_',substr(function_name,22))
		when person_type=3 and salary_item_type_id=10212 then 'compute_salary_other_premium2'
		when person_type=5 and salary_item_type_id=10211 then 'compute_salary_other_premium2'
        else function_name end) as function_name
  ,param1_title,param1_input
  ,param2_title,param2_input,param3_title,param3_input,param4_title,param4_input,remember_distance,remember_message
  ,print_order,validity_start_date,validity_end_date,total_id,ledger_id,tafsili_id,tafsili2_id,available_for
  ,backpay_include,month_length_effect,pension_include,credit_topic,editable_value,param5_title,param5_input
  ,param6_title,param6_input,param7_title,param7_input,ret_function_name from hrmstotal.salary_item_types;

create or replace view states as select * from hrmstotal.states;
create or replace view study_branchs as select * from hrmstotal.study_branchs;
create or replace view study_fields as select * from hrmstotal.study_fields;
create or replace view writ_subtypes as select * from hrmstotal.writ_subtypes;
create or replace view writ_types as select * from hrmstotal.writ_types;


update payment_items set salary_item_type_id=633 where salary_item_type_id=631;
update payment_items set salary_item_type_id=609 where salary_item_type_id=608;
update payment_items set salary_item_type_id=632 where salary_item_type_id=630;
update payment_items set salary_item_type_id=10202 where salary_item_type_id=10201;
update payment_items set salary_item_type_id=10211 where salary_item_type_id=10213;
update payment_items set salary_item_type_id=10209 where salary_item_type_id=10208;
update payment_items set salary_item_type_id=628 where salary_item_type_id=627;
update payment_items set salary_item_type_id=885 where salary_item_type_id=883;
update payment_items set salary_item_type_id=10221 where salary_item_type_id=10222;
update payment_items set salary_item_type_id=605 where salary_item_type_id=603;

update pay_get_lists set salary_item_type_id=633 where salary_item_type_id=631;
update pay_get_lists set salary_item_type_id=609 where salary_item_type_id=608;
update pay_get_lists set salary_item_type_id=632 where salary_item_type_id=630;
update pay_get_lists set salary_item_type_id=10202 where salary_item_type_id=10201;
update pay_get_lists set salary_item_type_id=10211 where salary_item_type_id=10213;
update pay_get_lists set salary_item_type_id=10209 where salary_item_type_id=10208;
update pay_get_lists set salary_item_type_id=628 where salary_item_type_id=627;
update pay_get_lists set salary_item_type_id=885 where salary_item_type_id=883;
update pay_get_lists set salary_item_type_id=10221 where salary_item_type_id=10222;
update pay_get_lists set salary_item_type_id=605 where salary_item_type_id=603;


update pay_get_list_items set salary_item_type_id=633 where salary_item_type_id=631;
update pay_get_list_items set salary_item_type_id=609 where salary_item_type_id=608;
update pay_get_list_items set salary_item_type_id=632 where salary_item_type_id=630;
update pay_get_list_items set salary_item_type_id=10202 where salary_item_type_id=10201;
update pay_get_list_items set salary_item_type_id=10211 where salary_item_type_id=10213;
update pay_get_list_items set salary_item_type_id=10209 where salary_item_type_id=10208;
update pay_get_list_items set salary_item_type_id=628 where salary_item_type_id=627;
update pay_get_list_items set salary_item_type_id=885 where salary_item_type_id=883;
update pay_get_list_items set salary_item_type_id=10221 where salary_item_type_id=10222;
update pay_get_list_items set salary_item_type_id=605 where salary_item_type_id=603;


