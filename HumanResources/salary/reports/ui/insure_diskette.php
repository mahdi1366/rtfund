<?php
//---------------------------
// programmer:	B.Mahdipour
// create Date:	93.06
//---------------------------

require_once("../../../header.inc.php");
// این گزارش مورد بررسی مجدد قرار گیرد چون در صفحه مربوطه تغییراتی اعمال شده است .................
die();
 ini_set("display_errors", "On");
if(!isset($_REQUEST["task"]))   
	require_once '../js/insure_diskette.js.php';
	
if( isset($_REQUEST["task"]) /*&& $_REQUEST["task"] != 'GetDisk' */ ) {

	?>
	<style>
		.reportGenerator {border-collapse: collapse;border: 1px solid black;font-family:tahoma;font-size: 8pt;
			text-align: center;width: 50%;padding: 2px;}
		.reportGenerator .header {color: white;font-weight: bold;background-color:#4D7094}
		.reportGenerator .header1 {color: white;font-weight: bold;background-color:#465E86}		
		.reportGenerator td {border: 1px solid #555555;height: 20px;}
	</style>
	<?
	
	}
/*اولین حکم بعد از این حکم را استخراج می کند*/
	function get_next_writ($execute_date,$staff_id)
	{

	     $WRes = PdoDataAccess::runquery("
										select writ_id, writ_ver 
										from writs
										where staff_id = ? and execute_date > ? and
											  history_only != 1 and emp_mode not in (3,9,15)
										order by execute_date , writ_id DESC , writ_ver DESC
										limit 1 ");
										
		if( count($WRes) > 0 )
			return $WRes[0]['writ_id'] ; 
		else 
		return 0 ; 
	}
	
function onCalcField(&$rec)
{
	if(empty($rec['work_sheet'])) {
		$DT = PdoDataAccess::runquery('SELECT MAX(DISTINCT pai.param4) work_days,
									   s.person_type
								 FROM  payment_items pai
									  INNER JOIN salary_item_types sit
											ON (pai.salary_item_type_id = sit.salary_item_type_id)
									  INNER JOIN staff s	
											ON (pai.staff_id = s.staff_id)
								 WHERE pai.pay_year = '.$_POST['pay_year'].' AND
									  pai.pay_month = '.$_POST['pay_month'].' AND
									  pai.payment_type = '.$_POST['PayType'].' AND
									  pai.staff_id = '.$rec['staff_id'].' AND
									  sit.compute_place = '.SALARY_ITEM_COMPUTE_PLACE_WRIT.'
								 GROUP BY s.staff_id') ; 	
								 
	    if( $DT[0]['work_days'] > 1 ) 
			$DT[0]['work_days'] = 1 ; 
			 
	    $work_days = $DT[0]['work_days'];
		$person_type = $DT[0]['person_type'];
		
		$work_days *= DateModules::DaysOfMonth($rec['pay_month'],$rec['pay_year']);		       	
		$rec['work_sheet'] = $work_days;			 
	    
	}
	if(!empty($rec['work_sheet']))
			$rec['work_sheet'] = round($rec['work_sheet']) ;

		if ($rec['person_type'] == HR_WORKER) {
			$rec['daily_fee'] = $rec['monthly_fee'] / $rec['work_sheet'];
        } else {
        	$rec['daily_fee'] = $rec['monthly_fee'] / DateModules::DaysOfMonth($rec['pay_month'],$rec['pay_year']);
        }

		$rec['monthly_premium'] = $rec['monthly_insure_include'] - $rec['monthly_fee'];
		$rec['other_gets'] = $rec['gets'] - $rec['worker_insure_include'];
		return true;
}			

function copyDbfFiles() {

	$from_path = "/var/www/sadaf/HumanResources/upload/dbf/" ;
	$to_path = "../../../HRProcess/" ;
	$this_path = getcwd();
	

	if(!is_dir($to_path)) {
		mkdir($to_path, 0775);
	}

	if (is_dir($from_path)) {
		chdir($from_path);
		$handle=opendir('.');
		while (($file = readdir($handle))!==false) {
				
			if (($file != ".") && ($file != "..")) {
				if (is_file($file)) {
					chdir($this_path);							
					copy($from_path.$file, $to_path.$file);
					chdir($from_path);								
				}
			}
		}
		closedir($handle);
	}
	chdir($this_path);
	
	
}

if(isset($_REQUEST["task"]))
{
	
	//........................... where .....................................
	$keys = array_keys($_POST);
	$WhereCost = $WherePT = $WhereDetec = "" ;
	$arr = "" ;
	
	for($i=0; $i < count($_POST); $i++)
	{
		if(strpos($keys[$i],"chkcostID_") !== false)
		{			
			$arr = preg_split('/_/', $keys[$i]);	
			if(isset($arr[1]))
			$WhereCost .= ($WhereCost!="") ?  ",".$arr[1] : $arr[1] ; 
		}	
		
		
		if(strpos($keys[$i],"chkDetect_") !== false)
		{		
			$arr = preg_split('/_/', $keys[$i]);		
			if(isset($arr[1]))
			$WhereDetec .= ($WhereDetec!="") ?  ",".$arr[1] : $arr[1] ;
		}			 		
	}
	
	$WhereDetec = ($WhereDetec !="") ? " AND  daily_work_place_no in (".$WhereDetec.")" : "" ;
	$PT = ( $_POST['PersonType'] == 102 ) ? '1,2,3' :  $_POST['PersonType']  ; 
		
	//............................ Query ...........................................
	
	$query = " DROP TABLE IF EXISTS temp_insure_include " ;
	PdoDataAccess::runquery($query) ; 
	
	if($_POST['pay_month'] >= 1 && $_POST['pay_month'] < 7 )
		$EndDay = 31 ;
	else if($_POST['pay_month'] > 6 && $_POST['pay_month'] < 12 )
		$EndDay = 30 ;
	else if($_POST['pay_month'] == 12 )
		$EndDay = 29 ;
		
	$month_start = DateModules::shamsi_to_miladi($_POST['pay_year']."/".$_POST['pay_month']."/01") ;
	$month_end = DateModules::shamsi_to_miladi($_POST['pay_year']."/".$_POST['pay_month']."/".$EndDay) ;
	$next_month_start = DateModules::shamsi_to_miladi($_POST['pay_year']."/".($_POST['pay_month']+1)."/01") ;
	
	$query = " CREATE TABLE temp_insure_include  AS
			    SELECT DISTINCT staff_id
				FROM payment_items
				WHERE pay_year = ".$_POST['pay_year']." AND
					  pay_month = ".$_POST['pay_month']." AND
					  salary_item_type_id IN(".SIT_WORKER_COLLECTIVE_SECURITY_INSURE.",
											 ".SIT_PROFESSOR_COLLECTIVE_SECURITY_INSURE.",
											 ".SIT_STAFF_COLLECTIVE_SECURITY_INSURE.") AND 
											  get_value <> 0  " ; 	
	PdoDataAccess::runquery($query) ; 	
	PdoDataAccess::runquery("ALTER TABLE temp_insure_include ADD INDEX(staff_id)") ; 		

	PdoDataAccess::runquery("DROP TABLE IF EXISTS temp_work_sheet") ; 		
	PdoDataAccess::runquery("CREATE TABLE temp_work_sheet AS
							  SELECT pgli.staff_id , SUM(pgli.approved_amount) work_sheet , MAX(pgli.comments) description
							  FROM pay_get_list_items pgli
							  INNER JOIN pay_get_lists pgl
									ON pgl.list_id = pgli.list_id
							  WHERE list_date >= '".$month_start."' AND list_date<='".$month_end."' AND
								  list_type = ".WORK_SHEET_LIST."  AND (doc_state=".CENTER_CONFIRM." OR doc_state=".COMPUTED.")
							  GROUP BY pgli.staff_id") ; 
	
	PdoDataAccess::runquery("DROP TABLE IF EXISTS temp_insure_list") ; 							  
	PdoDataAccess::runquery(" CREATE TABLE temp_insure_list AS 
						 select s.staff_id,
        						s.work_start_date,
                                s.account_no,
                                ps.pfname,
                                ps.plname,
                                ps.idcard_no,
                                ps.sex,
                                CASE WHEN ps.sex = 1 THEN 1 ELSE 0 END man_counter ,
                                CASE WHEN ps.sex = 2 THEN 1 ELSE 0 END woman_counter ,
        						ps.father_name,
        						ps.insure_no,
        						ps.country_id,
        						ps.national_code ,
                                CASE s.person_type
                                	WHEN ".HR_WORKER." THEN  j.title
                                	WHEN ".HR_EMPLOYEE." THEN  po.title
                                	WHEN ".HR_PROFESSOR." THEN  po.title
		                        END job_title,
                                w.contract_start_date,
        						w.contract_end_date,
								w.issue_date,
                                w.ouid,
                                w.salary_pay_proc,
                                w.person_type,
								c.ptitle country_title , 
                                pa.pay_year,
                                pa.pay_month,
                                pa.start_date,
                                pa.end_date,
                                tc.daily_work_place_no,tc.cost_center_id ,
                                tc.detective_name,
                                tc.detective_address,
                                tc.collective_security_branch,
                                tc.employer_name,
                                tws.work_sheet ,
                                tws.description ,
                                CASE s.person_type
                                	WHEN ".HR_WORKER." THEN
		                                SUM(CASE
		                                    WHEN (pai.salary_item_type_id = ".SIT_WORKER_BASE_SALARY."  OR
		                                          pai.salary_item_type_id = ".SIT_WORKER_ANNUAL_INC.")
		                                    THEN (pai.pay_value + pai.diff_pay_value * pai.diff_value_coef)
		                                    END)
                                	WHEN ".HR_EMPLOYEE." THEN
		                                SUM(CASE
		                                    WHEN (pai.salary_item_type_id IN(".SIT_STAFF_BASE_SALARY.",".SIT_STAFF_ANNUAL_INC
        									.",".SIT_STAFF_MIN_PAY.",".SIT_STAFF_ADAPTION_DIFFERENCE.",".SIT_STAFF_ABSOPPTION_EXTRA
        									.",".SIT_STAFF_DOMINANT_JOB_EXTRA.",".SIT_STAFF_JOB_EXTRA.",
										    34,36,10264 , 10267 , 10364 , 10367 , 10366 ))
		                                    THEN (pai.pay_value + pai.diff_pay_value * pai.diff_value_coef)
		                                    END)
                                	WHEN ".HR_PROFESSOR." THEN
		                                SUM(CASE
		                                    WHEN (pai.salary_item_type_id IN(".SIT_PROFESSOR_BASE_SALARY.",".SIT_PROFESSOR_SPECIAL_EXTRA.") )
		                                    THEN (pai.pay_value + pai.diff_pay_value * pai.diff_value_coef)
		                                    END)
		                        END monthly_fee,
                                SUM(pai.pay_value + pai.diff_pay_value * pai.diff_value_coef) pay,
                                SUM(CASE
                                    WHEN (pai.salary_item_type_id = ".SIT_WORKER_COLLECTIVE_SECURITY_INSURE." OR
                                    	 pai.salary_item_type_id = ".SIT_STAFF_COLLECTIVE_SECURITY_INSURE." OR
                                    	 pai.salary_item_type_id = ".SIT_PROFESSOR_COLLECTIVE_SECURITY_INSURE.")
                                    THEN (pai.param1 + pai.diff_param1 * pai.diff_param1_coef)
                                    END) monthly_insure_include,
                                ROUND(SUM(CASE
                                    WHEN (pai.salary_item_type_id = ".SIT_WORKER_COLLECTIVE_SECURITY_INSURE." OR
                                    	 pai.salary_item_type_id = ".SIT_STAFF_COLLECTIVE_SECURITY_INSURE." OR
                                    	 pai.salary_item_type_id = ".SIT_PROFESSOR_COLLECTIVE_SECURITY_INSURE.")
                                    THEN (pai.get_value + pai.diff_get_value * pai.diff_value_coef)
                                    END)) worker_insure_include,
                                ROUND(SUM(CASE
                                    WHEN (pai.salary_item_type_id = ".SIT_WORKER_COLLECTIVE_SECURITY_INSURE." OR
                                    	 pai.salary_item_type_id = ".SIT_STAFF_COLLECTIVE_SECURITY_INSURE." OR
                                    	 pai.salary_item_type_id = ".SIT_PROFESSOR_COLLECTIVE_SECURITY_INSURE.")
                                    THEN (pai.get_value)
                                    END)) worker_insure_include_val,
                                  ROUND(SUM(CASE
                                    WHEN (pai.salary_item_type_id = ".SIT_WORKER_COLLECTIVE_SECURITY_INSURE." OR
                                    	 pai.salary_item_type_id = ".SIT_STAFF_COLLECTIVE_SECURITY_INSURE." OR
                                    	 pai.salary_item_type_id = ".SIT_PROFESSOR_COLLECTIVE_SECURITY_INSURE.")
                                    THEN ( pai.diff_get_value * pai.diff_value_coef)
                                    END)) worker_insure_include_diffval,    
                                ROUND(SUM(CASE
                                    WHEN (pai.salary_item_type_id = ".SIT_WORKER_COLLECTIVE_SECURITY_INSURE." OR
                                    	 pai.salary_item_type_id = ".SIT_STAFF_COLLECTIVE_SECURITY_INSURE." OR
                                    	 pai.salary_item_type_id = ".SIT_PROFESSOR_COLLECTIVE_SECURITY_INSURE.")
                                    THEN (pai.param2 + pai.diff_param2 * diff_param2_coef)
                                    END)) employer_insure_value,
                                ROUND(SUM(CASE
                                    WHEN (pai.salary_item_type_id = ".SIT_WORKER_COLLECTIVE_SECURITY_INSURE." )
                                    THEN (pai.param3 + pai.diff_param3 * diff_param3_coef)
                                    END)) unemployment_insure_value,
                                SUM(pai.get_value + pai.diff_get_value * pai.diff_value_coef) gets,
                                SUM( (pai.pay_value + pai.diff_pay_value * pai.diff_value_coef) - (pai.get_value - pai.diff_get_value * pai.diff_value_coef)) pure_pay
                                
                 from temp_insure_include ti
        					  INNER JOIN payments pa
        					  	   ON(ti.staff_id = pa.staff_id)
                              LEFT OUTER JOIN payment_items pai
                                   ON (pai.pay_year = pa.pay_year AND
                                   	   pai.pay_month = pa.pay_month AND
                                   	   pai.payment_type = pa.payment_type AND
                                   	   pai.staff_id = pa.staff_id)
                              LEFT OUTER JOIN writs w
                                   ON (pa.writ_id = w.writ_id AND pa.writ_ver=w.writ_ver AND pa.staff_id = w.staff_id AND w.state=".WRIT_SALARY.")
                              LEFT OUTER JOIN position po
                                   ON (w.post_id = po.post_id)
                              LEFT OUTER JOIN jobs j
                                   ON (w.job_id = j.job_id)
                              LEFT OUTER JOIN staff s
                                   ON (w.staff_id = s.staff_id)
                              LEFT OUTER JOIN cost_centers  tc
                                   ON (w.cost_center_id = tc.cost_center_id)
                              LEFT OUTER JOIN persons ps
                                   ON (ps.PersonID = s.PersonID)
						      LEFT OUTER JOIN countries c 
								   ON (ps.country_id = c.country_id)
                              LEFT OUTER JOIN salary_item_types sit
                                   ON(pai.salary_item_type_id = sit.salary_item_type_id)
                              LEFT OUTER JOIN temp_work_sheet tws
                              	   ON tws.staff_id = s.staff_id 
                              	   
                   where  (pa.pay_year = ".$_POST['pay_year'].") AND
						   (pa.pay_month = ".$_POST['pay_month'].") AND	       						
							pa.payment_type=".$_POST['PayType']." ".(($WhereCost !="" ) ? " AND  
							w.cost_center_id in (".$WhereCost.") " : "" )." ".$WhereDetec." AND 
							s.person_type in (".$PT.")
       			   group by s.staff_id,
                                     pa.pay_year,
                                     pa.pay_month,
                                     s.work_start_date,
                                     ps.pfname,
                                     ps.plname,
                                     ps.idcard_no,
                                     ps.sex,
                                     ps.father_name,
                                     ps.insure_no,
                                     ps.country_id,
                                     j.title,
                                     w.contract_start_date,
                                     w.contract_end_date,
                                     w.ouid,
                                     w.salary_pay_proc,
                                     pa.start_date,
                                     pa.end_date,
                                     tc.cost_center_id,
                                     tc.daily_work_place_no,
                                     tc.detective_name,
                                     tc.detective_address,
                                     tc.collective_security_branch,
                                     tc.employer_name ");
									 
								//	 echo PdoDataAccess::GetLatestQueryString() ; die() ;
	 
	 
					PdoDataAccess::runquery("insert into temp_insure_list (
					                                staff_id,
					        						work_start_date,
					                                account_no,
					                                pfname,
					                                plname,
					                                idcard_no,  sex,man_counter ,woman_counter ,father_name,insure_no,
					        						country_id,	national_code , job_title,
					                                contract_start_date,contract_end_date,
					                                ouid,
					                                salary_pay_proc,
					                                person_type,
					                                pay_year,
					                                pay_month,
					                                start_date,
					                                end_date,
					                                daily_work_place_no,cost_center_id,
					                                detective_name,
					                                detective_address,
					                                collective_security_branch,
					                                employer_name,
					                                work_sheet ,
					                                description ,
					                                monthly_fee,
					                                pay,
					                                monthly_insure_include,
					                                worker_insure_include,
					                                employer_insure_value,
					                                unemployment_insure_value,
					                                gets,
					                                pure_pay  ) (

                                        SELECT distinct s.staff_id,
                                                        s.work_start_date,
                                                        s.account_no,
                                                        ps.pfname,
                                                        ps.plname,
                                                        ps.idcard_no,
                                                        ps.sex,
                                                        CASE ps.sex
                                                            WHEN 1 THEN 1
                                                            ELSE 0
                                                        END man_counter,
                                                        CASE ps.sex
                                                            WHEN 2 THEN 1
                                                            ELSE 0
                                                        END woman_counter,
                                                        ps.father_name,
                                                        ps.insure_no,
                                                        ps.country_id,ps.national_code,
                                                        CASE s.person_type
                                                            WHEN ".HR_WORKER." THEN  po.title
                                                            WHEN ".HR_EMPLOYEE." THEN  po.title
                                                            WHEN ".HR_PROFESSOR." THEN  po.title
                                                        END job_title,
                                                        w.contract_start_date,
                                                        w.contract_end_date,
                                                        w.ouid, w.salary_pay_proc,
                                                        w.person_type,
                                ".$_POST['pay_year']." pay_year,
                                ".$_POST['pay_month']." pay_month,
                                '".$month_start."' start_date,
                                '".$month_end."' end_date,
                                c.daily_work_place_no,c.cost_center_id,
                                c.detective_name,
                                c.detective_address,
                                c.collective_security_branch,
                                c.employer_name,
                                0 work_sheet,
                                '' description ,
					            0  monthly_fee,
                                0 pay,
                                0 monthly_insure_include,
                                0 worker_insure_include,
                                0 employer_insure_value,
                                0 unemployment_insure_value,
                                0 gets,
                                0 pure_pay

                                        FROM staff s INNER JOIN writs w
                                                          ON s.last_writ_id = w.writ_id and
                                                             s.last_writ_ver = w.writ_ver AND s.staff_id = w.staff_id AND  w.state=".WRIT_SALARY."
                                                     INNER JOIN  staff_include_history sih
                                                                      ON s.staff_id = sih.staff_id


                              LEFT OUTER JOIN position  po
                                   ON (w.post_id = po.post_id)
                              LEFT OUTER JOIN jobs j
                                   ON (w.job_id = j.job_id)
                             
                              LEFT OUTER JOIN cost_centers c
                                   ON (w.cost_center_id = c.cost_center_id)
                              LEFT OUTER JOIN persons ps
                                   ON (ps.PersonID = s.PersonID)
                              
                              WHERE w.emp_mode = ".EMP_MODE_LEAVE_WITH_SALARY." AND
                                                          sih.start_date <= '".$month_start."' AND
                                                         (sih.end_date IS NULL OR sih.end_date >= '".$month_end."') AND sih.insure_include = 1 AND
                                                                                    s.staff_id not in ( select staff_id from  temp_insure_list )
                                          )");
										  
										  
	PdoDataAccess::runquery('ALTER TABLE temp_insure_list ADD INDEX(staff_id);');
	
	$query = "	select 
					staff_id,
					work_start_date,
					account_no,
					pfname,
					plname,
					idcard_no,  sex,man_counter ,woman_counter ,father_name,insure_no,
					country_id,	national_code , job_title,country_title,
					contract_start_date,contract_end_date,
					ouid,
					salary_pay_proc,
					person_type,
					pay_year,
					pay_month,
					start_date,
					end_date,
					daily_work_place_no,cost_center_id,
					detective_name,
					detective_address,
					collective_security_branch,
					employer_name,
					work_sheet ,issue_date,
					description ,
					monthly_fee,
					pay,
					monthly_insure_include,
					worker_insure_include,
					employer_insure_value,
					unemployment_insure_value,
					gets,
					pure_pay 
				
				from temp_insure_list 
				where (pay_year = ".$_POST['pay_year'].") AND
					  (pay_month = ".$_POST['pay_month'].") "." ".(($WhereCost !="" ) ? " AND  cost_center_id in (".$WhereCost.") " : "" )." ".$WhereDetec."  AND person_type in (".$PT.")
					  
				order by pay_year,pay_month,daily_work_place_no,plname,pfname " ; 
									
		$res = PdoDataAccess::runquery($query) ; 	

//echo PdoDataAccess::GetLatestQueryString(); die() ; 

		$qry = " select bi.Title month_title 
                        from  Basic_Info bi 
                                where  bi.typeid = 41 AND InfoID = ".$_POST["pay_month"] ; 
		$MRes = PdoDataAccess::runquery($qry) ; 
		$monthTitle = $MRes[0]['month_title'] ;
	
			
	if( $_REQUEST["task"] == 'GetDisk' ) {		
	
	
		copyDbfFiles();		
				
		$counter =$work_sheet = $daily_fee = $monthly_fee = 0 ; 
		$monthly_premium = $monthly_insure_include = $pay = $worker_insure_include =$other_gets = 0 ; 
		$pure_pay = $employer_insure_value = $unemployment_insure_value = 0  ; 
		
		$cnv = new manage_W2DFormatConvertor() ; 
		
		for($i=0;$i<count($res); $i++)
		{
			
			onCalcField($res[$i]) ;
					
			while(list($key, $value) = each($res[$i]) ) 
					$res[$i][$key] = str_replace(array("ی","ی","ک","ك","ؤ"),
												 array("ي","ي","ك","ك","و"), $res[$i][$key]);
											 
			if($res[$i]['sex'] == 1 )
				$sex = 'مرد';
			else
				$sex = 'زن';
		
			if($res[$i]['work_start_date'] != NULL && $res[$i]['work_start_date'] !='0000-00-00' )
			{
				$arr  = preg_split('/\//',DateModules::Miladi_to_Shamsi($res[$i]['work_start_date'])); 
				
				$year = $arr[0];
				$month = $arr[1]; 
				$day = $arr[2]; 
						
				if ($year * $month == $_POST['pay_year'] * $_POST['pay_month'] )
					$contract_start_date = DateModules::Miladi_to_Shamsi($res[$i]['work_start_date']);
				else
					$contract_start_date = NULL;
					
			}
			else $contract_start_date = NULL;
		 	
			unset($arr);
			if($res[$i]['contract_end_date'] != '0000-00-00')
			{
				$arr = preg_split('/\//',DateModules::Miladi_to_Shamsi($res[$i]['contract_end_date']));  	
				$year = $arr[0];
				$month = $arr[1]; 
				$day = $arr[2];						
				
				if ($year * $month == $_POST['pay_year'] * $_POST['pay_month']) {
					$next_writ = get_next_writ($res[$i]['contract_end_date'],$res[$i]['staff_id']);
					
					if ($next_writ == 0 && $res[$i]['salary_pay_proc'] == 1) {
						$contract_end_date = DateModules::Miladi_to_Shamsi($res[$i]['contract_end_date']);
					}				
				}
			
			} else
			$contract_end_date = NULL;
			
			$res[$i]['city_title'] = 'مشهد' ;
							/* $res[$i]['plname'] = 'ئ' ; 
							echo $cnv->convertStringMs2Dos(iconv('UTF-8','WINDOWS-1256',$res[$i]['plname'])) ; 
							die()  ; */
			$record = array($res[$i]['daily_work_place_no'],
							substr($_POST['pay_year'],2,2),
	    					$_POST['pay_month'],
	    					NULL, /* شماره ليست*/
	    					$cnv->correctDigitDir($res[$i]['insure_no']),
	    					$cnv->convertStringMs2Dos(iconv('UTF-8','WINDOWS-1256',$res[$i]['pfname'])),
	    					$cnv->convertStringMs2Dos(iconv('UTF-8','WINDOWS-1256',$res[$i]['plname'])),
	    					$cnv->convertStringMs2Dos(iconv('UTF-8','WINDOWS-1256',$res[$i]['father_name'])),
	    					$cnv->convertDigitDosEnToFa($cnv->correctDigitDir($res[$i]['idcard_no'])),
	    					$cnv->convertStringMs2Dos(iconv('UTF-8','WINDOWS-1256',$res[$i]['city_title'])),
	    					substr(DateModules::Miladi_to_Shamsi($res[$i]['issue_date']),2),
	    					NULL, /*DSW_BDATE*/
	    					$cnv->convertStringMs2Dos(iconv('UTF-8','WINDOWS-1256',$sex)),
	    					$cnv->convertStringMs2Dos(iconv('UTF-8','WINDOWS-1256',$res[$i]['country_title'])),
	    					$cnv->convertStringMs2Dos(iconv('UTF-8','WINDOWS-1256',$res[$i]['job_title'])),
	    					substr($contract_start_date,2),
	    					substr($contract_end_date,2),
	    					round($res[$i]['work_sheet']),
	    					round($res[$i]['daily_fee']),
	    					round($res[$i]['monthly_fee']),
	    					round($res[$i]['monthly_premium']),
	    					round($res[$i]['monthly_insure_include']),
	    					round($res[$i]['pay']),
	    					round($res[$i]['worker_insure_include']),							                          
	    					NULL, /* نرخ پورسانتاژ */
	    					NULL,  /* DSW_JOB */
							$res[$i]['national_code']
    					);
						
			
			$counter++;
			$work_sheet += $res[$i]['work_sheet'];
			$daily_fee += $res[$i]['daily_fee']; 
			$monthly_fee += $res[$i]['monthly_fee']; 
			$monthly_premium += $res[$i]['monthly_premium'];  
			$monthly_insure_include += $res[$i]['monthly_insure_include']; 
			$pay += $res[$i]['pay'];
			$worker_insure_include += $res[$i]['worker_insure_include'];
			$employer_insure_value += $res[$i]['employer_insure_value']; 
			$unemployment_insure_value += $res[$i]['unemployment_insure_value']; 
						
			$file = "DSKWOR00.DBF" ;
			$db_path = "../../../HRProcess/".$file ;
			$dbi = dbase_open($db_path,2);
			dbase_add_record($dbi, $record);
			dbase_close($dbi);
									
		}	
 
		if (file_exists("../../../HRProcess/"."DSKWOR".$res[0]['cost_center_id'].".DBF")) {			
			unlink("../../../HRProcess/"."DSKWOR".$res[0]['cost_center_id'].".DBF");				
			}

		//.............rename a file ............................
		$directory ="../../../HRProcess/" ;
		foreach (glob($directory."*.DBF") as $filename) {
			$file = realpath($filename);
			rename($file, str_replace("DSKWOR00","DSKWOR".$res[0]['cost_center_id'],$file));
		}
			
	
		//..........................................................
	
		$record2 = array($res[0]['daily_work_place_no'],
    					$cnv->convertStringMs2Dos(iconv('UTF-8','WINDOWS-1256',$res[0]['detective_name'])),
    					$cnv->convertStringMs2Dos(iconv('UTF-8','WINDOWS-1256',$res[0]['employer_name'])),
    					$cnv->convertDigitDosEnToFa($cnv->convertStringMs2Dos(iconv('UTF-8','WINDOWS-1256',$res[0]['detective_address']))),
    					1,
    					substr($_POST['pay_year'],2,2),
    					$_POST['pay_month'],
    					NULL,
    					$cnv->convertStringMs2Dos(iconv('UTF-8','WINDOWS-1256','ليست اصلي مشمول بيمه')),
    					$counter,
    					round($work_sheet),
    					round($daily_fee),
    					round($monthly_fee),
    					round($monthly_premium),
    					round($monthly_insure_include),
    					round($pay),
    					round($worker_insure_include),                     
    					round($employer_insure_value),
    					round($unemployment_insure_value),
    					'27',
    					NULL, 
    					NULL  
    					);
			
		$file = "DSKKAR00.DBF" ;
		$db_path = "../../../HRProcess/".$file ;    	
    	$dbi = dbase_open($db_path,2);
		dbase_add_record($dbi, $record2);
		dbase_close($dbi);
		
		if (file_exists("../../../HRProcess/"."DSKKAR".$res[0]['cost_center_id'].".DBF")) {			
			unlink("../../../HRProcess/"."DSKKAR".$res[0]['cost_center_id'].".DBF");				
			}

		//.............rename a file ............................
		$directory ="../../../HRProcess/" ;
		foreach (glob($directory."*.DBF") as $filename) {
			$file = realpath($filename);
			rename($file, str_replace("DSKKAR00","DSKKAR".$res[0]['cost_center_id'],$file));
		}
		
		
		//...........................................................
		echo '<META http-equiv=Content-Type content="text/html; charset=UTF-8" ><body dir="rtl">';
		echo "<center><table style='border:0px groove #9BB1CD;border-collapse:collapse;width:50%'><tr>
				<td width=60px><img src='/HumanResources/img/fum_symbol.jpg'></td>
				<td align='center' style='font-family:b titr;font-size:15px'>گزارش دیسکت بیمه "." </td>				
				<td width='200px' align='center' style='font-family:tahoma;font-size:11px'>تاریخ تهیه گزارش : " 
				. DateModules::shNow() . "<br>";		
		echo "</td></tr></table>";      
	
		echo '<table  class="reportGenerator" style="text-align: right;width:70%!important" cellpadding="4" cellspacing="0">
						
				<tr class="header">					
					<td >ردیف </td>
					<td> مرکز هزینه </td>
					<td align="center" >دریافت فایل کارفرما</td>
					<td align="center" >دریافت فایل کارکنان </td>				
				</tr>	' ;
				
		
		echo "<tr>
				<td>1</td>				
				<td>".$res[0]['detective_name']."</td>
				<td><a href=''>فایل کارفرما -  ".$res[0]['detective_name']."</td>
				<td><a href=''> فایل کارکنان - ".$res[0]['detective_name']."</td>
			  </tr>"; 
				
			
		
		//...........................................................
	/*	header('Content-disposition: filename="'."DSKWOR".$res[0]['cost_center_id'].'.DBF"');
		header('Content-type: application/file');
		header('Pragma: no-cache');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');

		echo file_get_contents("../../../HRProcess/"."DSKWOR".$res[0]['cost_center_id'].'.DBF'); */ 
		die() ; 		  		
	}		
	elseif( $_REQUEST["task"] == 'ShowList' ) 
	{

	 
	
	echo '<META http-equiv=Content-Type content="text/html; charset=UTF-8" ><body dir="rtl">';
		echo "<center><table style='border:0px groove #9BB1CD;border-collapse:collapse;width:90%'><tr>
				<td width=60px><img src='/HumanResources/img/fum_symbol.jpg'></td>
				<td align='center' style='font-family:b titr;font-size:15px'>گزارش لیست بیمه"." </td>				
				<td width='200px' align='center' style='font-family:tahoma;font-size:11px'>تاریخ تهیه گزارش : " 
		     . DateModules::shNow() . "<br>";		
	echo "</td></tr></table>";      
	
	echo '<table  class="reportGenerator" style="text-align: right;width:90%!important" cellpadding="4" cellspacing="0">
			 <tr class="header1">					
				<td colspan="24">صورت دستمزد/حقوق ومزایای&nbsp;&nbsp;&nbsp;'. $monthTitle.'&nbsp;'.$_POST['pay_year'].'</td>
			 </tr>
			 <tr class="header1">
				<td colspan="4" >شماره کارگاه : &nbsp; '.$res[0]['daily_work_place_no'].'</td>
				<td colspan="4">نام گارگاه : &nbsp; '.$res[0]['detective_name'].'</td>
				<td colspan="5">نام کارفرما : &nbsp; '.$res[0]['employer_name'].'</td>
				<td colspan="6">نشانی کارگاه :&nbsp; '.$res[0]['detective_address'].'</td>
				<td colspan="5">شعبه تامین اجتماعی :&nbsp; '.$res[0]['collective_security_branch'].'</td>			
			 </tr>
			 <tr class="header">					
				<td rowspan="2">ردیف </td>
				<td colspan="9" align="center" >مشخصات بیمه شده</td>
				<td colspan="3" align="center" >روزهای کارکردماه</td>
				<td colspan="5" align="center" >دستمزد/حقوق مزایا(ریال)</td>
				<td rowspan="2" >حق بیمه <br>سهم بیمه شده</td>
				<td rowspan="2" >سایر کسور</td>		
				<td rowspan="2" >مانده قابل پرداخت</td>
				<td rowspan="2" >امضا/اثرانگشت</td>
				<td rowspan="2" >کدملی</td>
			</tr>
			<tr class="header">
				<td>نام</td>
				<td>نام خانوادگی</td>
				<td>شماره شناسنامه/گذرنامه</td>
				<td>نام پدر</td>
				<td>شماره بیمه شده</td>
				<td>شغل</td>
				<td>مرد</td>
				<td>زن</td>
				<td>غیرایرانی</td>
				<td>تاریخ آغاز به کار</td>
				<td>تاریخ ترک کار</td>
				<td>روزهای کارکرد</td>
				<td>دستمزد روزانه</td>
				<td>دستمزد ماهانه</td>
				<td>مزایای ماهانه مشمول</td>
				<td>جمع دستمزد و مزاياي ماهانه مشمول</td>
				<td>جمع دستمزد و مزایای ماهانه مشمول و غیر مشمول</td>							
			</tr>' ; 
		$sumWorkSheet = $sumDailyFee = $sumMonthlyFee = $sumMonthlyPremium = $UEInsurevalue =  0 ; 
		$sumMInsureInc = $sumPay = $sumWInsureInc = $sumOtherPay = $sumPurePay = $EInsurevalue = 0 ;	
		for($i=0;$i< count($res) ;$i++)
		{
			onCalcField($res[$i]) ;						
			
			echo " <tr>
					<td>".( $i + 1 )."</td>
					<td>".$res[$i]['pfname']."</td> 
					<td>".$res[$i]['plname']."</td>
					<td>".$res[$i]['idcard_no']."</td>
					<td>".$res[$i]['father_name']."</td>
					<td>".$res[$i]['insure_no']."</td> " ;
			mb_internal_encoding('utf-8');
			if(mb_strlen($res[$i]['job_title'])<=14)				
				echo "<td>".$res[$i]['job_title']."</td>" ; 
			else 
				echo "<td>".mb_substr($res[$i]['job_title'],0,12).'...'."</td>" ;  
			
			if($res[$i]['sex'] == 1 )
				echo "<td> * </td>" ; 
			else 
				echo "<td>&nbsp;</td>" ; 
			
			if($res[$i]['sex'] == 2 )
				echo "<td> * </td>" ; 
			else 
				echo "<td>&nbsp;</td>" ; 
				
			if($res[$i]['country_id'] != 1111 )
				echo "<td> * </td>" ; 
			else 
				echo "<td>&nbsp;</td>" ; 
			
			if(!empty($res[$i]['work_start_date']) && $res[$i]['work_start_date'] !='0000-00-00'  ){
				list($year,$month,$day) = preg_split('/[\/]/',DateModules::miladi_to_shamsi($res[$i]['work_start_date']));	

				if ($year * $month == $res[$i]['pay_year'] * $res[$i]['pay_month'])
					echo "<td> ".DateModules::miladi_to_shamsi($res[$i]['work_start_date'])." </td>" ; 
				else echo "<td>&nbsp;</td>" ; 
			}	
			else echo "<td>&nbsp;</td>" ; 
                   
			if(!empty($res[$i]['contract_end_date']) && $res[$i]['contract_end_date'] !='0000-00-00' ){
				list($year,$month,$day) = preg_split('/[\/]/',DateModules::miladi_to_shamsi($res[$i]['contract_end_date']));		
				if ($year * $month == $res[$i]['pay_year'] * $res[$i]['pay_month']){
					
					$qry = " select  count(*) cn
								from writs
									where execute_date > '".$res[$i]['contract_end_date']."' and staff_id = ".$res[$i]['staff_id']."
							 order by execute_date
							 limit 1 " ; 
					$NWRes = PdoDataAccess::runquery($qry) ; 
					
						if($NWRes[0]['cn'] == 0 && $res[$i]['salary_pay_proc'] == 1 )									
							echo "<td> ".DateModules::miladi_to_shamsi($res[$i]['contract_end_date'])." </td>" ; 
				}
				else echo "<td>&nbsp;</td>" ; 
			}
			else echo "<td>&nbsp;</td>" ; 
			
			echo "<td>".$res[$i]['work_sheet']."</td>
				  <td>".number_format(round($res[$i]['daily_fee']), 0, '.', ',')."</td> 
				  <td>".number_format(round($res[$i]['monthly_fee']), 0, '.', ',')."</td>
				  <td>".number_format(round($res[$i]['monthly_premium']), 0, '.', ',')."</td>
				  <td>".number_format(round($res[$i]['monthly_insure_include']), 0, '.', ',')."</td>
				  <td>".number_format(round($res[$i]['pay']), 0, '.', ',')."</td>
				  <td>".number_format(round($res[$i]['worker_insure_include']), 0, '.', ',')."</td>
				  <td>".number_format(round($res[$i]['other_gets']), 0, '.', ',')."</td>
				  <td>".number_format(round($res[$i]['pure_pay']), 0, '.', ',')."</td>
				  <td>".$res[$i]['account_no']."</td>
				  <td>".$res[$i]['national_code']."</td></tr>" ;
						
			$sumWorkSheet +=  $res[$i]['work_sheet'];
			$sumDailyFee += $res[$i]['daily_fee'] ; 
			$sumMonthlyFee += $res[$i]['monthly_fee'] ;
			$sumMonthlyPremium += $res[$i]['monthly_premium'] ;
			$sumMInsureInc +=  $res[$i]['monthly_insure_include'] ;
			$sumPay += $res[$i]['pay'] ;
			$sumWInsureInc +=  $res[$i]['worker_insure_include'] ; 
			$sumOtherPay += $res[$i]['other_gets'] ; 
			$sumPurePay += $res[$i]['pure_pay'] ; 
			$EInsurevalue += $res[$i]['employer_insure_value'] ; 
			$UEInsurevalue += $res[$i]['unemployment_insure_value']	 ;  
		}
	

		echo "<tr style='font-weight: bold' ><td colspan='12'>جمع :&nbsp;</td>
				  <td>".number_format(round($sumWorkSheet), 0, '.', ',')."</td>
				  <td>".number_format(round($sumDailyFee), 0, '.', ',')."</td>
				  <td>".number_format(round($sumMonthlyFee), 0, '.', ',')."</td>
				  <td>".number_format(round($sumMonthlyPremium), 0, '.', ',')."</td>
				  <td>".number_format(round($sumMInsureInc), 0, '.', ',')."</td>
				  <td>".number_format(round($sumPay), 0, '.', ',')."</td>
				  <td>".number_format(round($sumWInsureInc), 0, '.', ',')."</td>
				  <td>".number_format(round($sumOtherPay), 0, '.', ',')."</td>
				  <td>".number_format(round($sumPurePay), 0, '.', ',')."</td>
				  <td colspan=2></td></tr>" ; 
				  
		echo "</table><table  width='90%' style='font-weight: bold;font-size: 11pt;' cellpadding= '3' cellspacing='0'><tr>
				  <td colspan='13' width='51%' >&nbsp;</td>
				  <td colspan='4' style='border:1px solid black;border-collapse:collapse;' width='17%' >جمع حق بیمه سهم کارفرما </td>
				  <td colspan='2' style='border:1px solid black;border-collapse:collapse;' width='11%' >".number_format(round($EInsurevalue), 0, '.', ',')."</td>
				  <td colspan='4' width='21%' >&nbsp;</td></tr>" ;
		echo "<tr><td colspan='13'>&nbsp;</td><td colspan='4' style='border:1px solid black;border-collapse:collapse;' >حق 3% بیمه بیکاری </td>
				  <td colspan='2' style='border:1px solid black;border-collapse:collapse;' >".number_format(round($UEInsurevalue), 0, '.', ',')."</td><td colspan='4'>&nbsp;</td></tr>" ;

		echo "<tr><td colspan='13'>&nbsp;</td><td colspan='4' style='border:1px solid black;border-collapse:collapse;' >جمع کل</td>
				  <td colspan='2' style='border:1px solid black;border-collapse:collapse;' >".number_format(round($sumWInsureInc + $EInsurevalue + $UEInsurevalue), 0, '.', ',')."</td>
				  <td colspan='4'>&nbsp;</td></tr></table></center>" ;
	
	}
	elseif( $_REQUEST["task"] == 'ApprovedForm' ) 
	{
		
		$man_counter = $woman_counter =$work_sheet = $daily_fee = $monthly_fee = 0 ; 
		$monthly_premium = $monthly_insure_include = $pay = $worker_insure_include =$other_gets = 0 ; 
		$pure_pay = $employer_insure_value = $unemployment_insure_value = 0  ; 
		
		for($i=0;$i< count($res) ;$i++)
		{
			if($res[$i]['sex'] == 1 )
				$man_counter++;
				
			if($res[$i]['sex'] == 2 )
				$woman_counter++;
				
			$pay += $res[$i]['pay'] ; 
			$monthly_insure_include += $res[$i]['monthly_insure_include'] ; 
			$worker_insure_include += $res[$i]['worker_insure_include'] ;
			$employer_insure_value += $res[$i]['employer_insure_value'] ;
			$unemployment_insure_value += $res[$i]['unemployment_insure_value'] ;			
						
			
		}
	
		echo '<META http-equiv=Content-Type content="text/html; charset=UTF-8" ><body dir="rtl">';		     
	

		echo '<center><table  class="reportGenerator" style="text-align: right;width:85%!important;font-weight:bold;" cellpadding="4" cellspacing="0">
			 <tr>					
				<td align="center" colspan="2" style="height: 500; vertical-align: top;font-size:14px"><div style="margin-top: 10px;">رسید شعبه</div>
				  <div style="position: relative; top: 80%; width: 280px; left: -15%;">نام و امضاء متصدي دريافت و مهر شعبه</div>
				</td>
			 </tr> 
			 <tr>					
				<td colspan="2" style="vertical-align: top;">
					خلاصه وضعيت صورت دستمزد و مزاياي کارکنان کارگاه &nbsp; '.$res[0]['detective_name'].'<br><br> شماره کارگاه &nbsp; '.$res[0]['daily_work_place_no'].'
					<br><br> به پيوست  1  حلقه ديسکت مربوط به دستمزد، حقوق و مزاياي ماهانه جاري&nbsp;
					<img src="/HumanResources/img/checkbox_on.gif"> معوق 
					<img src="/HumanResources/img/checkbox_off.gif">متمم 
					<img src="/HumanResources/img/checkbox_off.gif"> پورسانتاژ 
					<img src="/HumanResources/img/checkbox_off.gif"> جانباز 
					<img src="/HumanResources/img/checkbox_off.gif"> معلول 
					<img src="/HumanResources/img/checkbox_off.gif"><br><br>
					کارکنان اين کارگاه در ماه &nbsp; '.$monthTitle.' &nbsp; سال &nbsp;'.$_POST['pay_year'].'که خلاصه وضعيت آن به شرح زير است : <br><br>
					جهت منظور نمودن در سيستم مکانيزه و دریافت رسيد تحويل مي گردد : <br><br>
					تعداد بيمه شدگان : مرد &nbsp; '.$man_counter.' نفر و زن  &nbsp;'.$woman_counter.'&nbsp;نفر، جمعا '.($man_counter + $woman_counter) .' &nbsp; نفر <br><br>
					
				</td>
			 </tr>
			 <tr>
				 <td> جمع دستمزد و مزاياي مشمول و غير مشمول کسر حق بيمه ماه </td><td align="left">'.number_format(round($pay), 0, '.', ',').'&nbsp; ریال &nbsp;</td>
				 <tr>
				 <td> جمع دستمزد و مزاياي مشمول کسر حق بيمه ماه </td><td align="left">'.number_format(round($monthly_insure_include), 0, '.', ',').'&nbsp; ریال &nbsp;</td>
			 </tr>
			 <tr>
				 <td> جمع حق بيمه سهم بيمه شده </td><td align="left" >'.number_format(round($worker_insure_include), 0, '.', ',').'&nbsp; ریال &nbsp;</td>
			 </tr>
			 <tr>
				 <td> جمع حق بيمه سهم کارفرما </td><td align="left" >'.number_format(round($employer_insure_value), 0, '.', ',').'&nbsp; ریال &nbsp;</td>
			 </tr>
			 <tr>
				 <td> جمع حق بيمه بيکاري </td><td align="left" >'.number_format(round($unemployment_insure_value), 0, '.', ',').'&nbsp; ریال &nbsp;</td>
			 </tr>
			 <tr>
				 <td> 4% حق بيمه مشاغل سخت </td><td align="left" >&nbsp;</td>
			 </tr>
			 <tr>
				 <td>جمع کل حق بيمه و بيمه بيکاري</td><td align="left" >'.number_format(round($worker_insure_include + $employer_insure_value + $unemployment_insure_value ), 0, '.', ',').'&nbsp; ریال &nbsp;</td>				 
			 </tr>
			 <tr>
				<td colspan="2" style="height: 200px" >
				<div style="position: relative; top: -30%; width:600px;">ضمنا متعهد مي گردد اطلاعات ذخيره شده در ديسکتهاي فوق عينا مربوط به مندرجات بشرح فوق مي باشد.</div>
				<div style="position: relative; top: -15%; width: 280px; left: -25%;"> مهر و امضاء کارفرما/نماينده قانوني کارفرما </div>
				</td>
			 </tr>
			 </tr>
			 ' ; 
		 die() ; 
				
	}	
		
}
?>
	
<form id="mainForm">
    <center>
        <div id="mainpanel"></div>
		<input type="hidden" name="Type" id="Type">
    </center>    
</form>