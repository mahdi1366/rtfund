<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	93.03
//---------------------------
require_once '../header.inc.php';
require_once(inc_response);
require_once inc_dataReader;
require_once inc_PDODataAccess;
require_once inc_QueryHelper;

class manage_salary_utils
{

//مقدار ماليات را براي گزارشات خزانه شبيه سازي مي كند 
	public static function simulate_tax($pay_year,$pay_month,$payment_type=NULL){
		
		$e_date = "31-".$pay_month."-".$pay_year ; 		
		$end_month_date = DateModules::shamsi_to_miladi($e_date) ; 
		
		$middle_date = "15-".$pay_month."-".$pay_year ; 	
		$middle_month_date = DateModules::shamsi_to_miladi($middle_date) ; 
		
			if($payment_type)
				$payment_type_where = ' AND pi.payment_type = '.$payment_type ;

			
			PdoDataAccess::runquery("DROP TABLE IF EXISTS temp_tax_table_items") ; 
		
			PdoDataAccess::runquery("DROP TABLE IF EXISTS temp_tax_include_sum") ; 
			
			PdoDataAccess::runquery("DROP TABLE IF EXISTS temp_tax_include_incremental_sum ;") ; 
			
			PdoDataAccess::runquery("DROP TABLE IF EXISTS temp_limit_staff ;") ; 
			 
			PdoDataAccess::runquery("DROP TABLE IF EXISTS temp_paied_tax ;" ) ; 

			PdoDataAccess::runquery(" CREATE  TEMPORARY TABLE temp_limit_staff AS
										SELECT DISTINCT s.staff_id
										FROM staff s
										INNER JOIN staff_include_history si
											ON (s.staff_id = si.staff_id AND si.start_date <= ('$end_month_date') AND (si.end_date IS NULL OR si.end_date >= ('$end_month_date')) ) 
										WHERE si.tax_include = 1;") ;


			PdoDataAccess::runquery("ALTER TABLE temp_limit_staff ADD INDEX (staff_id);");

			PdoDataAccess::runquery(" CREATE TEMPORARY TABLE temp_paied_tax AS
										SELECT pai.staff_id,pai.get_value tax_value
										FROM   payment_items pai
												INNER JOIN temp_limit_staff tls
													ON (pai.staff_id = tls.staff_id)
										WHERE  pai.pay_year = $pay_year AND	
												pai.pay_month = $pay_month AND	
												pai.payment_type = 1 AND
												pai.salary_item_type_id IN (".SIT_PROFESSOR_TAX.",".SIT_STAFF_TAX.",".SIT_WORKER_TAX.")");

			PdoDataAccess::runquery(" CREATE TEMPORARY TABLE temp_tax_include_sum AS
										SELECT s.staff_id ,
												0 value,
												CASE 
													WHEN sit.credit_topic = ".CREDIT_TOPIC_OTHER." THEN SUM(pi.pay_value+pi.diff_pay_value) 
													ELSE 0
												END param1 ,
												0 param2  ,
												0 param3
										FROM payment_items pi
										INNER JOIN salary_item_types sit
											ON (pi.salary_item_type_id = sit.salary_item_type_id)
										INNER JOIN temp_limit_staff s
											ON (s.staff_id = pi.staff_id)
										WHERE pi.pay_year = ($pay_year) AND pi.pay_month = ($pay_month) AND sit.tax_include = 1 
											$payment_type_where
										GROUP BY
										staff_id;");

			PdoDataAccess::runquery(" CREATE TEMPORARY table temp_tax_table_items
										SELECT s.staff_id , tti.*
										FROM temp_limit_staff s
										INNER JOIN staff_tax_history sth
											ON (s.staff_id = sth.staff_id AND 
												sth.start_date <= ('$middle_month_date') AND 
												(sth.end_date >= ('$middle_month_date') OR sth.end_date IS NULL))
										INNER JOIN tax_table_types ttt
											ON (ttt.tax_table_type_id = sth.tax_table_type_id)
										INNER JOIN tax_tables tt
											ON (tt.tax_table_type_id = ttt.tax_table_type_id AND 
												tt.from_date <= ('$middle_month_date') AND 
												(tt.to_date >= ('$middle_month_date') OR tt.to_date IS NULL))
										INNER JOIN tax_table_items tti
											ON (tti.tax_table_id = tt.tax_table_id) ");


			PdoDataAccess::runquery(" CREATE TEMPORARY TABLE temp_tax_include_incremental_sum AS
										SELECT ts.staff_id , SUM(((ti.to_value - ti.from_value) * ti.coeficient)) inc_sum
										FROM temp_tax_include_sum ts
											LEFT OUTER JOIN temp_tax_table_items ti
												ON (ts.staff_id = ti.staff_id AND 
													ts.param1 >= ti.to_value)
										GROUP BY staff_id; ");

			PdoDataAccess::runquery(" ALTER TABLE temp_tax_include_sum ADD INDEX (staff_id) ");

			PdoDataAccess::runquery(" UPDATE temp_tax_include_sum ts
										LEFT OUTER JOIN temp_tax_table_items ti
											ON (ts.staff_id = ti.staff_id AND 
												ti.from_value <= ts.param1 AND 
												(ti.to_value >= ts.param1))
										LEFT OUTER JOIN temp_tax_include_incremental_sum tis
											ON (ts.staff_id = tis.staff_id)
										SET value = CASE WHEN ((param1 - ti.from_value ) * ti.coeficient) IS NULL THEN 0 ELSE ((param1 - ti.from_value) * ti.coeficient) END + 
													CASE WHEN tis.inc_sum IS NULL THEN 0 ELSE tis.inc_sum END ");


			PdoDataAccess::runquery(" UPDATE temp_tax_include_sum ts
										LEFT OUTER JOIN temp_paied_tax tpt
											ON (ts.staff_id = tpt.staff_id)
										SET value = tpt.tax_value - value ") ; 
		
}

//مقدار بيمه را براي گزارشات خزانه شبيه سازي مي كند
public static function simulate_bime($pay_year,$pay_month,$payment_type=NULL){
	
	$e_date = "31-".$pay_month."-".$pay_year ; 
	$end_month_date = DateModules::shamsi_to_miladi($e_date) ; 
	
	$s_date = "1-".$pay_month."-".$pay_year ; 
	$start_month_date = DateModules::shamsi_to_miladi($s_date) ; 
	
	$worker_month_day = 30;
	$actual_month_day = DateModules::get_month_dayno($pay_year,$pay_month) ; 
	
	
	//...................................
	
	PdoDataAccess::runquery("DROP TABLE IF EXISTS temp_insure_include_sum ") ; 
	
	PdoDataAccess::runquery(" DROP TABLE IF EXISTS temp_limit_staff "); 
	
	PdoDataAccess::runquery(" CREATE TEMPORARY TABLE temp_limit_staff AS
								SELECT DISTINCT s.staff_id , s.person_type
								FROM staff s
									INNER JOIN staff_include_history si
										ON (s.staff_id = si.staff_id AND si.start_date <= ('$end_month_date') AND (si.end_date IS NULL OR si.end_date >= ('$end_month_date'))) 
								WHERE si.insure_include = 1 ");

	PdoDataAccess::runquery("ALTER TABLE temp_limit_staff ADD INDEX (staff_id)");
	
	PdoDataAccess::runquery(" CREATE TEMPORARY table temp_insure_include_sum AS
								SELECT s.staff_id ,
										0 value,
										sum(pi.pay_value) + CASE WHEN sum(pi.diff_pay_value * pi.diff_value_coef) < 0 THEN 0 ELSE sum(pi.diff_pay_value * pi.diff_value_coef) END param1 ,
										0 param2 ,
										0 param3 , 
										CASE WHEN s.person_type = ".HR_WORKER." THEN $worker_month_day ELSE $actual_month_day END month_days
								FROM payment_items pi
								INNER JOIN salary_item_types sit
									ON (pi.salary_item_type_id = sit.salary_item_type_id AND 
										sit.credit_topic = ".CREDIT_TOPIC_1." AND 
										sit.insure_include = 1)
								INNER JOIN temp_limit_staff s
									ON (s.staff_id = pi.staff_id)
								WHERE pi.pay_year = ($pay_year) AND 
									pi.pay_month = ($pay_month) AND 
									pi.payment_type = 1
									$payment_type_where
								GROUP BY
								staff_id; ");

	
	PdoDataAccess::runquery("ALTER TABLE temp_insure_include_sum ADD INDEX (staff_id)");
	
	$max_daily_salary_insure_include = manage_salary_params::get_salaryParam_value("", 100 , SPT_MAX_DAILY_SALARY_INSURE_INCLUDE, $start_month_date);
	
	PdoDataAccess::runquery(" UPDATE temp_insure_include_sum
							  SET param1 = $max_daily_salary_insure_include * $worker_month_day 
							  WHERE param1 > $max_daily_salary_insure_include * $worker_month_day ") ; 
	
		
	//استخراج درصد بیمه بیکاری سهم کارفرما
	
	$res = PdoDataAccess::runquery("SELECT value
									FROM salary_params
										WHERE from_date <= '".$start_month_date."' AND
											to_date >= '".$end_month_date."' AND person_type =100 AND 
											param_type = ".SPT_UNEMPLOYMENT_INSURANCE_VALUE);
	$unemployment_insurance_value = $res[0]['value'] ; 
	
	//استخراج درصد بیمه سهم شخص
	$res = PdoDataAccess::runquery("SELECT value
									FROM salary_params
									WHERE from_date <= '".$start_month_date."' AND
											to_date >= '".$end_month_date."' AND person_type =100 AND 
											param_type = ".SPT_SOCIAL_SUPPLY_INSURE_PERSON_VALUE);
	
	$person_value = $res[0]['value']; 
				
	//استخراج درصد بیمه سهم کارفرما
	
	$res = PdoDataAccess::runquery("SELECT value
									FROM salary_params
									WHERE from_date <= '".$start_month_date."' AND
											to_date >= '".$end_month_date."' AND person_type =100 AND 
											param_type = ".SPT_SOCIAL_SUPPLY_INSURE_EMPLOYER_VALUE);
	
	$employmer_value = $res[0]['value']; 
	
	PdoDataAccess::runquery(" UPDATE temp_insure_include_sum
								SET value =  param1 * $person_value ,
									param2 = param1 * $employmer_value ,
									param3 = param1 * $unemployment_insurance_value 
								WHERE (1=1)");
		
}

}
?>