<?php
//---------------------------
// programmer:	B.Mahdipour
// create Date:	93.06
//---------------------------

require_once("../../../header.inc.php");
ini_set("display_errors","On") ; 

function getSalaryItemValue(){

	switch ($_POST['ReportType']){
			case 1 :
				$value = "(pai.pay_value + pai.diff_pay_value * pai.diff_value_coef - pai.get_value - pai.diff_get_value * pai.diff_value_coef)";
			break ;
			case 2 :
				$value = "(pai.pay_value - pai.get_value)";
			break ;
			case 3 :
				$value = "(pai.diff_pay_value * pai.diff_value_coef - pai.diff_get_value * pai.diff_value_coef)";
			break ;
		}
   		return $value ;
	
	
}

function get_salary_items_select($in_clause , $type=1 , $emp_state = 'ALL'){
		$value = getSalaryItemValue() ;
   		if($emp_state != 'ALL')
   			$value = "( CASE WHEN emp_state IN($emp_state) THEN ".$value."  ELSE 0 END ) " ;

   		return "SUM(CASE WHEN pai.salary_item_type_id NOT IN ($in_clause) OR sit.credit_topic = ".CREDIT_TOPIC_OTHER." THEN 0 ELSE $type * $value END) ";
   }
   
function  get_salary_item_gov_share($in_clause , $type=1 ){
		$value = getSalaryItemParam(3) ;
   		return " ROUND(SUM(CASE WHEN pai.salary_item_type_id NOT IN ($in_clause) THEN 0 ELSE $type * $value END)) ";
   }
   
function  get_insure_gov_share($in_clause , $type=1 ){
		return "ROUND(SUM(CASE WHEN pai.salary_item_type_id NOT IN ($in_clause) THEN 0 ELSE ( if (pai.salary_item_type_id in(145,744) , $type * ".getSalaryItemValueOnly("(tis.param2 + tis.param3)")." , $type * ".getSalaryItemValueOnly("(tis.param2 )")." ) ) END))";

   }
   
function get_salary_items_select_insure($in_clause , $type=1){
		$value = getSalaryItemParam(7) ;
   		return "SUM(CASE WHEN pai.salary_item_type_id NOT IN ($in_clause) OR sit.credit_topic = ".CREDIT_TOPIC_OTHER." THEN 0 ELSE $type * $value END) ";
   }
   
function get_salary_items_select_insure_dolat($in_clause , $type=1){


		 if($_POST['pay_year'] >= '1390' && $_POST['pay_month'] >= '5' )
				$value = " ( ((pai.param7) * 1.7 / 1.65 ) + ((pai.diff_param7) * 1.7 / 1.65 ) ) " ;  
				
		  else if($_POST['pay_year'] >= '1391'  )
				$value = " ( ((pai.param7) * 1.7 / 1.65 ) + ((pai.diff_param7) * 1.7 / 1.65 ) ) " ; 
      	 else
          $value = "( (pai.param7) * 3 / 2 )";
          
        if($_POST['ReportType'] == 3 )
            return 0 ;
        else
   		return "SUM(CASE WHEN pai.salary_item_type_id NOT IN ($in_clause) OR sit.credit_topic = ".CREDIT_TOPIC_OTHER." THEN 0 ELSE $type * $value END) ";
   }
   
function get_tax($in_clause , $type =1 ){
		return " SUM(CASE WHEN pai.salary_item_type_id NOT IN ($in_clause) THEN 0 ELSE $type * ".getSalaryItemValueOnly("(tts.value)")." END) ";
   }
   
function  get_insure_staff_share($in_clause , $type =1 ){
		return " SUM(CASE WHEN pai.salary_item_type_id NOT IN ($in_clause) THEN 0 ELSE $type * ".getSalaryItemValueOnly("(tis.value)")." END) ";
   }
   
function getSalaryItemValueOnly($ivalue){
		switch ($_POST['ReportType']){
			case 1 :
			case 2 :
				$value = $ivalue;
			break ;
			case 3 :
				$value = "(0)" ;
			break ;
		}
   		return $value ;
   }
   
function getSalaryItemParam($i){
		switch ($_POST['ReportType']){
			case 1 :
				$value = "(pai.param$i + pai.diff_param$i * pai.diff_param$i"."_coef)" ;
			break ;
			case 2 :
				$value = "(pai.param$i)";
			break ;
			case 3 :
				$value = "(pai.diff_param$i * pai.diff_param$i"."_coef)" ;
			break ;
		}
   		return $value ;
   }
   
function getEduLevel($value){
	 	
	$qry = " SELECT InfoID ,param2 FROM Basic_Info where TypeID = 6 and InfoID =".$value ; 
	$educRes = PdoDataAccess::runquery($qry) ; 
	
	return $educRes[0]['param2'] ; 
		
   }

if (isset($_REQUEST["show"]))
{
	
	$keys = array_keys($_POST);
	$WhereCost = $WherePT = $WhereEmpstate = "" ;
	$arr = "" ;
	
	for($i=0; $i < count($_POST); $i++)
	{
		if(strpos($keys[$i],"chkcostID_") !== false)
		{			
			$arr = preg_split('/_/', $keys[$i]);	
			if(isset($arr[1]))
			$WhereCost .= ($WhereCost!="") ?  ",".$arr[1] : $arr[1] ; 
		}	
		
		
		if( strpos($keys[$i],"chkEmpState_") !== false)
		{		
			$arr = preg_split('/_/', $keys[$i]);		
			if(isset($arr[1]))
			$WhereEmpstate .= ($WhereEmpstate!="") ?  ",".$arr[1] : $arr[1] ;
		}	
		 
		
	}
	
	if(isset($_POST['PT_1']) && $_POST['PT_1']== 1) 
	   	$WherePT .= ($WherePT !="" ) ?  " ,1 " :  "1 " ; 
	
	if(isset($_POST['PT_2']) && $_POST['PT_2']== 1) 
	   	$WherePT .= ($WherePT !="" ) ?  " ,2 " :  "2 " ; 
	
	if(isset($_POST['PT_3']) && $_POST['PT_3']== 1) 
	   	$WherePT .= ($WherePT !="" ) ?  " ,3 " :  "3 " ; 
	
	if(isset($_POST['PT_5']) && $_POST['PT_5']== 1) 
	   	$WherePT .= ($WherePT !="" ) ?  " ,5 " :  "5" ; 

       
	
	$pament_type = $_POST['PayType'];
	$staffID = (isset($_POST['staff_id']) && $_POST['staff_id'] > 0) ? $_POST['staff_id'] : " " ; 
	
	if( $_POST['PayType']  != 2 ){
		manage_salary_utils::simulate_tax($_POST['pay_year'], $_POST['pay_month'] , $pament_type ) ;	 
		manage_salary_utils::simulate_bime($_POST['pay_year'], $_POST['pay_month'] , $pament_type) ; 
		
		$value = getSalaryItemValue(); 

	$treasure_items =
			SIT_PROFESSOR_RETIRED.','.SIT_STAFF_RETIRED.','.
			SIT_PROFESSOR_COLLECTIVE_SECURITY_INSURE.','.SIT_STAFF_COLLECTIVE_SECURITY_INSURE.','.SIT_WORKER_COLLECTIVE_SECURITY_INSURE.',744,'.
			SIT1_BASE_SALARY.',34,'.SIT2_BASE_SALARY.','.SIT2_BASE_SALARY.','.
			SIT_STAFF_ANNUAL_INC.',36,'.SIT_WORKER_ANNUAL_INC.','.
			SIT_STAFF_JOB_EXTRA.','.
			SIT_PROFESSOR_ADAPTION_DIFFERENCE.','.
			SIT_STAFF_EQUALITY_DIFFERENCE.',57,'.
			SIT_STAFF_HARD_WORK_EXTRA.',49,'.
			SIT_PROFESSOR_BAD_WEATHER_EXTRA.',46,'.SIT_STAFF_BAD_WEATHER_EXTRA.','.
			SIT_STAFF_FACILITIES_VITIOSITY_EXTRA.','.
			SIT_STAFF_DUTY_LOCATION_EXTRA.','.
			SIT_STAFF_SHIFT_EXTRA.','.SIT_WORKER_SHIFT_EXTRA.',55,'.
			SIT_EMPLOYEE_SPECIAL_EXTRA.','.SIT_PROFESSOR_PARTICULAR_EXTRA.','.
			SIT_STAFF_DOMINANT_JOB_EXTRA.','.
			SIT_STAFF_CHILD_RIGHT.',50,'.SIT_PROFESSOR_CHILD_RIGHT.','.SIT_WORKER_CHILD_RIGHT.','.
			SIT_PROFESSOR_CHILDREN_RIGHT.',51,'.SIT_STAFF_CHILDREN_RIGHT.','.
			SIT_STAFF_ABSOPPTION_EXTRA.','.SIT_PROFESSOR_ABSOPPTION_EXTRA.','.SIT_EMPLOYEE_SEVEN_PERCENT_ABSORB_EXTRA.','.SIT_STAFF_HEIAT_OMANA_SPECIAL_EXTRA.','.
			SIT_STAFF_MIN_PAY.',45,'.
			SIT_STAFF_ADJUST_EXTRA.','.SIT_STAFF_ABSORB_EXTRA_8_9.','.
			SIT_STAFF_DEPRIVED_REGIONS_ABSOPPTION_EXTRA.',54,'.
			SIT_PROFESSOR_SPECIAL_EXTRA.','.
			'507'.','.'9944'.','.'9969'.',56,47,'.SIT_PROFESSOR_DEVOTION_EXTRA.','.
			SIT_STAFF_ADAPTION_DIFFERENCE.','.
			SIT_STAFF_WORK_WITH_RAY_EXTRA.','.SIT_PROFESSOR_WORK_WITH_RAY_EXTRA.','.
			SIT_PROFESSOR_MANAGMENT_EXTRA.',35,'.
			SIT_PROFESSOR_DEVOTION_EXTRA.','.
			SIT_PROFESSOR_RETIRED.','.SIT_STAFF_RETIRED.','.
			SIT_RETURN_INSURE_AND_RETIRED_WOUNDED_PERSONS.','.RETURN_FIRST_MONTH_MOGHARARY.','.
			SIT_PROFESSOR_COLLECTIVE_SECURITY_INSURE.','.SIT_STAFF_COLLECTIVE_SECURITY_INSURE.','.SIT_WORKER_COLLECTIVE_SECURITY_INSURE.','.
			SIT_STAFF_REMEDY_SERVICES_INSURE.','.SIT_PROFESSOR_REMEDY_SERVICES_INSURE .','.
			SIT_PROFESSOR_TAX.','.SIT_STAFF_TAX.','.SIT_WORKER_TAX.',747,'.
			SIT_PROFESSOR_RETIRED.','.SIT_STAFF_RETIRED.','.
			SIT_PROFESSOR_COLLECTIVE_SECURITY_INSURE.','.SIT_STAFF_COLLECTIVE_SECURITY_INSURE.','.SIT_WORKER_COLLECTIVE_SECURITY_INSURE.','.
			SIT_STAFF_REMEDY_SERVICES_INSURE.','.SIT_PROFESSOR_REMEDY_SERVICES_INSURE.','.
			PROFESSOR_FIRST_MONTH_MOGHARARY.','.STAFF_FIRST_MONTH_MOGHARARY.
			',10264 , 10364 , 10265 ,10365, 10266 ,10366 , 10267 , 10367, 10327,10368 , 10328 ,
			  10369 , 10329 ,10370, 10330 ,10371 , 10331 ,10372, 10332 ,10377,10373 , 10333 ,10374, 10334,10375 , 10335 ,10376';
	
			$insure_and_tax = SIT_PROFESSOR_TAX.','.SIT_STAFF_TAX.','.SIT_WORKER_TAX.','.
							  SIT_PROFESSOR_COLLECTIVE_SECURITY_INSURE.','.SIT_STAFF_COLLECTIVE_SECURITY_INSURE.','.SIT_WORKER_COLLECTIVE_SECURITY_INSURE ;

			$other_subtracts = " pai.salary_item_type_id  NOT IN ($treasure_items) AND sit.effect_type = 2 AND sit.credit_topic = ".CREDIT_TOPIC_1 ;
			$other_payments = " pai.salary_item_type_id  NOT IN ($treasure_items) AND sit.effect_type = 1 AND sit.credit_topic = ".CREDIT_TOPIC_1 ;
			
			$query = " SELECT 
						'115500' item_1  ,
						prs.national_code item_2 ,
						s.staff_id item_3,
						prs.birth_date item_4,
						w.onduty_year item_5,
						prs.pfname item_6,
						prs.plname item_7,
						CASE prs.sex WHEN  1 THEN 2 ELSE 4 END item_8,
						CASE prs.marital_status WHEN 1 THEN 1 ELSE 3 END item_9,
						w.children_count item_10,
						CASE WHEN w.emp_state = 1 OR w.emp_state = 2 OR w.emp_state = 10 THEN 6 ELSE 5 END item_11  ,
						w.education_level item_12,
						CASE WHEN SUM(CASE WHEN pai.salary_item_type_id NOT IN (".SIT_PROFESSOR_RETIRED.','.SIT_STAFF_RETIRED.") THEN 0 ELSE 1 END)>0 THEN 7 ELSE 8 END item_13 ,
						CASE WHEN SUM(CASE WHEN pai.salary_item_type_id NOT IN (".SIT_PROFESSOR_COLLECTIVE_SECURITY_INSURE.','.SIT_STAFF_COLLECTIVE_SECURITY_INSURE.','.SIT_WORKER_COLLECTIVE_SECURITY_INSURE.",744) THEN 0 ELSE 1 END)>0 THEN 2 ELSE 1 END item_14 ,
						".
						get_salary_items_select(SIT1_BASE_SALARY.','.SIT2_BASE_SALARY.',34,10264, 10364,'.SIT2_BASE_SALARY)." item_15 ,". 						 
						get_salary_items_select(SIT_STAFF_ANNUAL_INC.',36,'.SIT_WORKER_ANNUAL_INC)." item_16,".
						get_salary_items_select(SIT_STAFF_JOB_EXTRA.',10267, 10367, 10367')." item_17 , ".
						get_salary_items_select(SIT_PROFESSOR_ADAPTION_DIFFERENCE.',56,10335,10376')." item_18 ,".
						get_salary_items_select(SIT_STAFF_EQUALITY_DIFFERENCE.',57')." item_19,".
						get_salary_items_select(SIT_STAFF_HARD_WORK_EXTRA.',49,10328,10369')." item_20,".
						get_salary_items_select(SIT_PROFESSOR_BAD_WEATHER_EXTRA.',46,10333,10374,'.SIT_STAFF_BAD_WEATHER_EXTRA)." item_21,".
						get_salary_items_select(SIT_STAFF_FACILITIES_VITIOSITY_EXTRA)." item_22,".
						get_salary_items_select(SIT_STAFF_DUTY_LOCATION_EXTRA)." item_23,".
						get_salary_items_select(SIT_STAFF_SHIFT_EXTRA.','.SIT_WORKER_SHIFT_EXTRA.','.'55,10331,10372')." item_24,".
						get_salary_items_select(SIT_EMPLOYEE_SPECIAL_EXTRA.',10265,10365,'.SIT_PROFESSOR_PARTICULAR_EXTRA)." item_25,".
						get_salary_items_select(SIT_STAFF_DOMINANT_JOB_EXTRA.',48')." item_26,".
						get_salary_items_select(SIT_STAFF_CHILD_RIGHT.',50,10330,10371,'.SIT_PROFESSOR_CHILD_RIGHT.','.SIT_WORKER_CHILD_RIGHT)." item_27,".
						get_salary_items_select(SIT_PROFESSOR_CHILDREN_RIGHT.',51,10329,10370,'.SIT_STAFF_CHILDREN_RIGHT)." item_28,".
						get_salary_items_select(SIT_STAFF_ABSOPPTION_EXTRA.','.SIT_PROFESSOR_ABSOPPTION_EXTRA.','.SIT_EMPLOYEE_SEVEN_PERCENT_ABSORB_EXTRA.',47,10266,10366,'.SIT_PROFESSOR_DEVOTION_EXTRA)." item_29 ,".
						get_salary_items_select(SIT_STAFF_MIN_PAY.',45,10327,10368')." item_30 ,".
						get_salary_items_select(SIT_STAFF_ADJUST_EXTRA.','.SIT_STAFF_ABSORB_EXTRA_8_9)." item_31 ,".
						"0  item_32 ,".
						get_salary_items_select(SIT_STAFF_DEPRIVED_REGIONS_ABSOPPTION_EXTRA.',54')." item_33 ,".
						get_salary_items_select(SIT_PROFESSOR_SPECIAL_EXTRA.',284')." item_34 ,".
						"0  item_35 ,".
						get_salary_items_select_insure(SIT_STAFF_REMEDY_SERVICES_INSURE.','.SIT_PROFESSOR_REMEDY_SERVICES_INSURE )." item_36 ,".
						"0  item_37 ,".
						get_salary_items_select(SIT_STAFF_ADAPTION_DIFFERENCE)."  item_38 ,".
						get_salary_items_select(SIT_STAFF_WORK_WITH_RAY_EXTRA.',10334,10375,'.SIT_PROFESSOR_WORK_WITH_RAY_EXTRA)." item_39 ,".
						"0  item_40 ,".
						"0  item_41 ,".
						get_salary_items_select(SIT_PROFESSOR_EXCLUDE_MANAGEMENT_EXTRA.',35,'.SIT_STAFF_EXCLUDE_MANAGEMENT_EXTRA.',10332,10377,10373,'.SIT_PROFESSOR_MANAGMENT_EXTRA)." item_42 ,".
						get_salary_items_select(SIT_RETURN_INSURE_AND_RETIRED_WOUNDED_PERSONS.','.RETURN_FIRST_MONTH_MOGHARARY)." item_43 ,".
						get_salary_item_gov_share(SIT_PROFESSOR_RETIRED.','.SIT_STAFF_RETIRED)." item_44 ,".
						get_insure_gov_share(SIT_PROFESSOR_COLLECTIVE_SECURITY_INSURE.','.SIT_STAFF_COLLECTIVE_SECURITY_INSURE.','.SIT_WORKER_COLLECTIVE_SECURITY_INSURE.',744')." item_45 ,".
						get_salary_items_select_insure_dolat(SIT_STAFF_REMEDY_SERVICES_INSURE.','.SIT_PROFESSOR_REMEDY_SERVICES_INSURE )."  item_46 ,".
						"0  item_47 ,".
						"SUM(CASE WHEN ".$other_payments." THEN 1*".$value." ELSE 0 END)  item_48 ,".
						get_tax(SIT_PROFESSOR_TAX.','.SIT_STAFF_TAX.','.SIT_WORKER_TAX.',747' , 1 )."  item_49  ,".
						get_salary_items_select(SIT_PROFESSOR_RETIRED.','.SIT_STAFF_RETIRED,-1)."  item_50 ,".
						get_insure_staff_share(SIT_PROFESSOR_COLLECTIVE_SECURITY_INSURE.','.SIT_STAFF_COLLECTIVE_SECURITY_INSURE.','.SIT_WORKER_COLLECTIVE_SECURITY_INSURE.',744' )."  item_51 ,".
						get_salary_items_select(SIT_STAFF_REMEDY_SERVICES_INSURE.','.SIT_PROFESSOR_REMEDY_SERVICES_INSURE,-1)."  item_52 ,".
						"0  item_53 ,".
						get_salary_items_select(PROFESSOR_FIRST_MONTH_MOGHARARY.','.STAFF_FIRST_MONTH_MOGHARARY.','.EMPLOYEE_FIRST_MONTH_MOGHARARY_DEBT , -1)."  item_54 ,".
						"SUM(CASE WHEN ".$other_subtracts." THEN -1*".$value." ELSE 0 END)  item_55 ,
						0 item_56 ,
						s.account_no  item_57 ,
						b.name  item_58 ,
						b.name  item_59 ,
						b.branch_code  item_60
					FROM
						payments p
						INNER JOIN payment_items pai
							ON(p.pay_year = pai.pay_year AND p.pay_month = pai.pay_month AND p.staff_id = pai.staff_id AND p.payment_type = pai.payment_type)
						INNER JOIN salary_item_types sit
							ON(pai.salary_item_type_id = sit.salary_item_type_id)
						INNER JOIN cost_centers c
							ON(pai.cost_center_id = c.cost_center_id)
						INNER JOIN staff s
							ON(p.staff_id = s.staff_id)
						INNER JOIN persons prs
							ON(s.PersonID = prs.PersonID)
						LEFT JOIN writs w
							ON(p.writ_id = w.writ_id AND p.writ_ver = w.writ_ver AND p.staff_id = w.staff_id )
						INNER JOIN banks b
							ON(p.bank_id = b.bank_id)
							
						LEFT OUTER JOIN temp_tax_include_sum tts
							ON (tts.staff_id = pai.staff_id AND pai.salary_item_type_id IN(146,147,148,747))
							
						LEFT OUTER JOIN temp_insure_include_sum tis
							ON (tis.staff_id = pai.staff_id AND pai.salary_item_type_id IN(9920,145,144,744))
					
					WHERE pai.pay_year = ".$_POST['pay_year']." AND
						  pai.pay_month = ".$_POST['pay_month']." AND
						  pai.payment_type = ".$pament_type	; 
						  
			$query .=  ($WhereCost !="") ? " AND pai.cost_center_id in (".$WhereCost.") " : " "  ; 
			$query .=  ($WhereEmpstate !="") ? " AND w.emp_state in (".$WhereEmpstate.") " : " "  ; 
			$query .=  ($WherePT !="") ? " AND s.person_type in (".$WherePT.") " : " "  ; 
			$query .=  ($staffID  !=" ") ? " AND s.staff_id in (".$staffID .") " : " "  ; 
						
			$query .= " GROUP BY '115500' ,
						prs.national_code ,
						s.staff_id ,
						prs.birth_date ,
						w.onduty_year ,
						prs.pfname,
						prs.plname,
						prs.sex ,
						prs.marital_status ,
						w.children_count,
						w.emp_mode ,
						w.education_level,
						s.account_no,
						b.name,
						b.branch_code" ;
										
		//echo $query ; die() ; 
		$res = PdoDataAccess::runquery($query) ; 
		$output ="" ; 
		
		for($i=0;$i<count($res);$i++)
		{						
			foreach ($res[$i] as $item) {
				if($item == NULL)
				   $item = 0 ;
			}
	
			$res[$i]['item_4'] = substr(DateModules::miladi_to_shamsi($res[$i]['item_4']) , 0 , 4 );			  
			$res[$i]['item_12'] = getEduLevel($res[$i]['item_12']);
			list($bank , $branch) = preg_split('/ /',$res[$i]['item_58']); 		 
			$res[$i]['item_58'] = $bank ;
			$res[$i]['item_59'] = $branch ;
			
			$pure_val =
		   		$res[$i]['item_15']+
		   		$res[$i]['item_16']+
		   		$res[$i]['item_17']+
		   		$res[$i]['item_18']+
		   		$res[$i]['item_19']+
		   		$res[$i]['item_20']+
		   		$res[$i]['item_21']+
		   		$res[$i]['item_22']+
		   		$res[$i]['item_23']+
		   		$res[$i]['item_24']+
		   		$res[$i]['item_25']+
		   		$res[$i]['item_26']+
		   		$res[$i]['item_27']+
		   		$res[$i]['item_28']+
		   		$res[$i]['item_29']+
		   		$res[$i]['item_30']+
		   		$res[$i]['item_31']+
		   		$res[$i]['item_32']+
		   		$res[$i]['item_33']+
		   		$res[$i]['item_34']+
		   		$res[$i]['item_35']+
		   		//$res[$i]['item_36']+
		   		$res[$i]['item_37']+
		   		$res[$i]['item_38']+
		   		$res[$i]['item_39']+
		   		$res[$i]['item_40']+
		   		$res[$i]['item_41']+
		   		$res[$i]['item_42']+
		   		$res[$i]['item_43']+
		   		$res[$i]['item_48']-
		   		$res[$i]['item_49']-
		   		$res[$i]['item_50']-
		   		$res[$i]['item_51']-
		   		$res[$i]['item_52']-
		   		$res[$i]['item_53']-
		   		$res[$i]['item_54']-
		   		$res[$i]['item_55'] ;
			
			if($pure_val<0 && -1*$pure_val<$res[$i]['item_55']){
				$res[$i]['item_55'] += $pure_val ;
			}
			
			$res[$i]['item_56'] =
		   		$res[$i]['item_15']+
		   		$res[$i]['item_16']+
		   		$res[$i]['item_17']+
		   		$res[$i]['item_18']+
		   		$res[$i]['item_19']+
		   		$res[$i]['item_20']+
		   		$res[$i]['item_21']+
		   		$res[$i]['item_22']+
		   		$res[$i]['item_23']+
		   		$res[$i]['item_24']+
		   		$res[$i]['item_25']+
		   		$res[$i]['item_26']+
		   		$res[$i]['item_27']+
		   		$res[$i]['item_28']+
		   		$res[$i]['item_29']+
		   		$res[$i]['item_30']+
		   		$res[$i]['item_31']+
		   		$res[$i]['item_32']+
		   		$res[$i]['item_33']+
		   		$res[$i]['item_34']+
		   		$res[$i]['item_35']+
		   		//$res[$i]['item_36']+
		   		$res[$i]['item_37']+
		   		$res[$i]['item_38']+
		   		$res[$i]['item_39']+
		   		$res[$i]['item_40']+
		   		$res[$i]['item_41']+
		   		$res[$i]['item_42']+
		   		$res[$i]['item_43']+
		   		$res[$i]['item_44']+
		   		$res[$i]['item_45']+		   		
		   		$res[$i]['item_36']+
		   		$res[$i]['item_47']+
		   		$res[$i]['item_48']+
		   		$res[$i]['item_49']+
		   		$res[$i]['item_50']+
		   		$res[$i]['item_51']+
		   		$res[$i]['item_52']+
		   		$res[$i]['item_53']+
		   		$res[$i]['item_54']+
		   		$res[$i]['item_55'] ;
				
			$sep = '' ;
			$record = '' ;
			for($k=1; $k<=60 ;$k++)
			{
				$record = $record.$sep.$res[$i]['item_'.$k];
				$sep = ',' ;		
			}
			
			$has_value = false ;
			for($k=15;$k<=55;$k++){
				if($res[$i]['item_'.$k]!=0){
					$has_value = true ;
					break ;
				}
			}
echo $record ; die(); 					
			if($has_value)
			   $output.=$record."\n";
			
		}
		
		
		
//................................................footer....................
		
		$file = "W".substr($_POST['pay_year'],2,2).str_pad($_POST['pay_month'], 2,"0",STR_PAD_LEFT).'001'.".DAT";
		$filename = "../../../HRProcess/".$file ;

		$fp=fopen($filename,'w');				
		fwrite($fp , $output);
		fclose($fp);
				
		header('Content-disposition: filename="'.$file.'"');
		header('Content-type: application/file');
		header('Pragma: no-cache');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');

		echo file_get_contents("../../../HRProcess/".$file);
		die() ; 	
		
		
	}// Kind of Type
	
	elseif ($_POST['PayType'] == 2 ) 
	{

		$_POST['ReportType'] =  1 ; 
	
		$query = " SELECT  '115500' item_1  ,
							prs.national_code item_2 ,
							s.staff_id item_3,
							SUM(CASE WHEN pai.salary_item_type_id IN (163,164,165,764) THEN pai.param1 ELSE 0 END) item_4 ,
							".get_salary_items_select('163,164,165,764')." handsel,
							prs.pfname item_6,
							prs.plname item_7,
							s.account_no  item_8 ,
							b.name  item_9 ,
							b.name  item_10 ,
							b.branch_code  item_11 ,
							".get_salary_items_select(SIT_PROFESSOR_TAX.','.SIT_STAFF_TAX.','.SIT_WORKER_TAX,-1)." item_12
					FROM payments p
							INNER JOIN payment_items pai
								ON(p.pay_year = pai.pay_year AND p.pay_month = pai.pay_month AND p.staff_id = pai.staff_id AND p.payment_type = pai.payment_type)
							INNER JOIN salary_item_types sit
								ON(pai.salary_item_type_id = sit.salary_item_type_id)
							INNER JOIN cost_centers c
								ON(pai.cost_center_id = c.cost_center_id)
							INNER JOIN staff s
								ON(p.staff_id = s.staff_id)
							INNER JOIN persons prs
								ON(s.PersonID = prs.PersonID)
							INNER JOIN writs w
								ON(p.writ_id = w.writ_id AND p.writ_ver = w.writ_ver AND p.staff_id = w.staff_id)
							INNER JOIN banks b 
								ON(p.bank_id = b.bank_id)
								
					WHERE pai.pay_year = ".$_POST['pay_year']." AND
						  pai.pay_month = 12 AND
						  pai.payment_type = 2  " ; 
			
					$query .=  ($WhereCost !="") ? " AND pai.cost_center_id in (".$WhereCost.") " : " "  ; 
					$query .=  ($WhereEmpstate !="") ? " AND w.emp_state in (".$WhereEmpstate.") " : " "  ; 
					$query .=  ($WherePT !="") ? " AND s.person_type in (".$WherePT.") " : " "  ; 
					$query .=  ($staffID  !=" ") ? " AND s.staff_id in (".$staffID .") " : " "  ; 
					
				    $query .=" GROUP BY '115500',
										prs.national_code,
										s.staff_id,
										prs.pfname,
										prs.plname,
										s.account_no,
										b.name,
										b.branch_code
							";
	
				
			$res = PdoDataAccess::runquery($query) ; 

			$output ="" ; 
		
		for($i=0;$i<count($res);$i++)
		{
		
			if($res[$i]['handsel'] > 0 )
			   $res[$i]['item_5'] = $res[$i]['handsel'] - $res[$i]['item_12'];
			   
			foreach ($res[$i] as $item) {
				if( $item == NULL)
					$item = 0 ;
			}

			list($bank , $branch) = preg_split('/ /', $res[$i]['item_9']);	
			$res[$i]['item_9'] = $bank ;
			$res[$i]['item_10'] = $branch ;
			
			//...........................
			
			$j = 0 ;
			$sep = '' ;
			$record = '' ;
			for($j=1 ; $j<=12 ; $j++){
				$record = $record.$sep.$res[$i]['item_'.$j] ;
				$sep = ',' ;
			}
			$has_value = true ;
			if($has_value)
				$output.=$record."\n";
			
			//..........................
		
		}
		
		
//................................................footer....................
		
		$file = "WE1".substr($_POST['pay_year'],2,2).'001'.".TXT";
		$filename = "../../../HRProcess/".$file ;

		$fp=fopen($filename,'w');				
		fwrite($fp , $output);
		fclose($fp);
				
		header('Content-disposition: filename="'.$file.'"');
		header('Content-type: application/file');
		header('Pragma: no-cache');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');

		echo file_get_contents("../../../HRProcess/".$file);
		die() ; 
	
	}
	
		
			 
}

?>

<script>
	TreasureDisketteSummary.prototype = {
		TabID : '<?= $_REQUEST["ExtTabID"] ?>',
		address_prefix : "<?= $js_prefix_address ?>",		
		get : function(elementID){
			return findChild(this.TabID, elementID);
		}
	};
	
	TreasureDisketteSummary.prototype.showReport = function(btn, e)
	{
		if(!this.filterPanel.getForm().isValid())
			return;
			
		this.form = this.get("mainForm");
		this.form.target = "_blank";
		this.form.method = "POST";
		this.form.action =  this.address_prefix + "treasure_diskette_report.php?show=true";
	
		this.form.submit();
		this.get("excel").value = "";
		return;
	}
	
	function TreasureDisketteSummary()
	{
		var types = Ext.create('Ext.data.ArrayStore', {
			fields: ['val', 'title'],
			data : [[1,'براساس مبالغ و تفاوت ها'],
					[2,'براساس مبالغ'],
					[3,'براساس تفاوت ها']]	
					});     
		this.filterPanel = new Ext.form.Panel({
			renderTo : this.get('DivInfo'),
			width : 780,
			titleCollapse : true,
			frame : true,
			collapsible : true,
			bodyStyle : "padding:5px",
			title :"تنظیم گزارش تهیه دیسکت خزانه",
			fieldDefaults: {
				labelWidth: 60
			},
			layout: {
				type: 'table',
				columns: 3
			},
			items :[{
					xtype : "numberfield",
					hideTrigger : true,
					fieldLabel : "سال",
					name : "pay_year",
					allowBlank : false,
					width : 150
				},{
					xtype : "numberfield",
					hideTrigger : true,
					fieldLabel : "ماه",
					name : "pay_month",
					allowBlank : false,
					width : 150
				},
				{
					xtype : "numberfield",
					hideTrigger : true,
					width : 180,
					labelWidth: 110,
					fieldLabel : "شماره شناسایی",
					name : "staff_id"
				},
				{
						colspan:3,										
						xtype: 'container',  
						style : "padding:5px",
						html:"نوع فرد : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+
					
								"<input type=checkbox id='PT_1' name='PT_1' value=1 checked>&nbsp; هیئت علمی"+
								"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+
								"<input type=checkbox id='PT_2' name='PT_2' value=1 checked>&nbsp;  کارمند  " +
								"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+
								"<input type=checkbox id='PT_3' name='PT_3' value=1 checked>&nbsp;  روزمزدبیمه ای " +
								"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+
								"<input type=checkbox id='PT_5' name='PT_5' value=1 >&nbsp; قراردادی " 
                                                                
					},						
					{
						xtype: 'fieldset',
						title : "مراکز هزینه",
						colspan : 3,		
						style:'background-color:#DFEAF7',					
						width : 700,						
						fieldLabel: 'Auto Layout',
						itemId : "chkgroup",
						collapsible: true,
						collapsed: true,
						layout : {
							type : "table",
							columns : 4,
							tableAttrs : {
								width : "100%",
								align : "center"
							},
							tdAttrs : {							
								align:'right',
								width : "۱6%"
							}
						},
						items : [{
							xtype : "checkbox",
							boxLabel : "همه",
							checked : true,
							listeners : {
								change : function(){
									parentNode = TreasureDisketteSummaryObject.filterPanel.down("[itemId=chkgroup]").getEl().dom;
									elems = parentNode.getElementsByTagName("input");
									for(i=0; i<elems.length; i++)
									{
										if(elems[i].id.indexOf("chkcostID_") != -1)
											elems[i].checked = this.getValue();
									}
								}
							}
						}]
					},					
					{
						xtype: 'fieldset',
						title : "وضعیت استخدامی",
						colspan : 3,
						style:'background-color:#DFEAF7',					
						width : 700,						
						fieldLabel: 'Auto Layout',
						itemId : "chkgroup2",	
						collapsible: true,
						collapsed: true,
						layout : {
							type : "table",
							columns : 4,
							tableAttrs : {
								width : "100%",
								align : "center"
							},
							tdAttrs : {							
								align:'right',
								width : "۱6%"
							}
						},
						items : [{
							xtype : "checkbox",
							boxLabel : "همه",
							checked : true,							
							listeners : {
								change : function(){
									parentNode = TreasureDisketteSummaryObject.filterPanel.down("[itemId=chkgroup2]").getEl().dom;
									elems = parentNode.getElementsByTagName("input");
									for(i=0; i<elems.length; i++)
									{
										if(elems[i].id.indexOf("chkEmpState_") != -1)
											elems[i].checked = this.getValue();
									}
								}
							}
						}]
					},					
					{
						xtype : "combo",
						colspan:3,
						store :  new Ext.data.Store({
							fields : ["InfoID","Title"],
							proxy : {
										type: 'jsonp',
										url : this.address_prefix + "../../../global/domain.data.php?task=searchPayType",
										reader: {
											root: 'rows',
											totalProperty: 'totalCount'
										}
									}
									,
								autoLoad : true,
								listeners:{
									load : function(){
											TreasureDisketteSummaryObject.filterPanel.down("[itemId=PayType]").setValue("1");										
									}
								}
									
													}),
						valueField : "InfoID",
						displayField : "Title",
						hiddenName : "PayType",
						itemId : "PayType",
						fieldLabel : "نوع پرداخت",						
						listConfig: {
							loadingText: 'در حال جستجو...',
							emptyText: 'فاقد اطلاعات',
							itemCls : "search-item"
						},
						width:300
					},
					{
						xtype : "combo",
						hiddenName:"ReportType",                                    
						fieldLabel : "نوع گزارش",
						value : 1 ,
						store: types,
						valueField: 'val',
						displayField: 'title',
						width:250
					}					
					
			],
			buttons :  [ {
							text : "تهیه دیسکت ",
							handler :  Ext.bind(this.showReport,this),
							iconCls : "save"                                
						},{
						iconCls : "clear",
						text : "پاک کردن فرم",
						handler : function(){
						this.up("form").getForm().reset();
						TreasureDisketteSummaryObject.get("mainForm").reset();
					}
				}]
		});
		
		new Ext.data.Store({
			fields : ["cost_center_id","title"],
			proxy : {
				type: 'jsonp',
				url : this.address_prefix + "../../../global/domain.data.php?task=searchCostCenter",
				reader: {
					root: 'rows',
					totalProperty: 'totalCount'
				}
			},
			autoLoad : true,
			listeners:{
				load : function(){
					this.each(function (record) {
						TreasureDisketteSummaryObject.filterPanel.down("[itemId=chkgroup]").add({
							xtype : "container",
							html : "<input type=checkbox name=chkcostID_" + record.data.cost_center_id + " id=chkcostID_" + record.data.cost_center_id + " checked > " + record.data.title
						});
						
					});
										
				}}
			
		});
		
		new Ext.data.Store({
			fields : ["InfoID","Title"],
			proxy : {
				type: 'jsonp',
				url : this.address_prefix + "../../../global/domain.data.php?task=searchEmpState",
				reader: {
					root: 'rows',
					totalProperty: 'totalCount'
				}
			},
			autoLoad : true,
			listeners:{
				load : function(){
					this.each(function (record) {
						TreasureDisketteSummaryObject.filterPanel.down("[itemId=chkgroup2]").add({
							xtype : "container",
							html : "<input type=checkbox name=chkEmpState_" + record.data.InfoID + " id=chkEmpState_" + record.data.InfoID + " checked > " + record.data.Title
						});
						
					});
										
				}}
			
		});		
		
		
	}
	
	var TreasureDisketteSummaryObject = new TreasureDisketteSummary();
	
	
		
</script>
<center>
	<form id="mainForm">
		<br>
		<div id="DivInfo"></div><br>
		<input type="hidden" name="excel" id="excel">
	</form>
</center>
