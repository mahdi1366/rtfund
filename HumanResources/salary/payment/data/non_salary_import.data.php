<?php

//-----------------------------
//	Programmer	: b.Mahdipour
//	Date		: 92.09
//-----------------------------

require_once '../../../header.inc.php';
require_once '../../org_process_docs/class/group_pay_get_log.class.php';
require_once '../class/payment_calculation.class.php';
require_once '../class/payments.class.php';
require_once '../class/payment_items.class.php';
require_once 'phpExcelReader.php';
require_once(inc_response);

ini_set("display_errors","On") ;

function exe_param_sql($PayYear ,$PayMonth , &$salaryParam) {
	
	$SDate = $PayYear."/".$PayMonth."/01" ; 
	$SDate = DateModules::shamsi_to_miladi($SDate) ; 
	
	if($PayMonth < 7 ) $endDay = "31" ; 
	elseif($PayMonth > 6 &&  $PayMonth < 12 ) $endDay = "30 " ;
	elseif($PayMonth == 12 ) $endDay = "29" ;  

	if( DateModules::YearIsLeap($PayYear) )
		$endDay = 30;
	else
		$endDay = 29;

	$EDate = $PayYear."/".$PayMonth."/".$endDay ; 
	$EDate = DateModules::shamsi_to_miladi($EDate) ; 
	
	$tmpRes = PdoDataAccess::runquery('SELECT   param_type,
												dim1_id,
												value
										FROM salary_params
										WHERE from_date <= \''.$EDate.'\' AND to_date >= \''.$SDate.'\' AND
												param_type IN('.SPT_HANDSEL_VALUE.','.SPT_JOB_SALARY.')') ; 
	
	for($t=0;$t<count($tmpRes);$t++)
	{		
		$tmpRes[$t]['dim1_id']= ($tmpRes[$t]['dim1_id'] == NULL) ? 0 : $tmpRes[$t]['dim1_id']; 
		$salaryParam[$tmpRes[$t]['param_type']][$tmpRes[$t]['dim1_id']] = $tmpRes[$t]['value'] ; 		
	}
	
}

function exe_taxtable_sql($PayYear ,$PayMonth , &$taxTable) {
	
		$SDate = $PayYear."/".$PayMonth."/01" ; 

		if($PayMonth < 7 ) $endDay = "31" ; 
		elseif($PayMonth > 6 &&  $PayMonth < 12 ) $endDay = "30 " ;
		elseif($PayMonth == 12 ) $endDay = "29" ;  

if( DateModules::YearIsLeap($PayYear) )
			$endDay = 30;
		else
			$endDay = 29;

		$EDate = $PayYear."/".$PayMonth."/".$endDay ; 
		
		$tmp_rs = PdoDataAccess::runquery("
                        SELECT ttype.person_type,
                               ttype.tax_table_type_id,
                               ttable.from_date,
                               ttable.to_date,
                               titem.from_value,
                               titem.to_value,
                               titem.coeficient

                        FROM tax_table_types ttype
                             INNER JOIN tax_tables ttable
                                   ON(ttype.tax_table_type_id = ttable.tax_table_type_id AND from_date <= '".DateModules::shamsi_to_miladi($EDate)."' AND 
								                                                             to_date >= '".DateModules::shamsi_to_miladi($SDate)."')
                             INNER JOIN tax_table_items titem
                                   ON(ttable.tax_table_id = titem.tax_table_id)

                        ORDER BY ttype.person_type,ttype.tax_table_type_id,ttable.from_date,titem.from_value
                        ");
				
	//	echo 	PdoDataAccess::GetLatestQueryString(); die(); 
		
		for($i=0; $i<count($tmp_rs); $i++)
		{
			$taxTable[$tmp_rs[$i]['tax_table_type_id']][] = array(
															'from_date' => $tmp_rs[$i]['from_date'],
															'to_date' => $tmp_rs[$i]['to_date'],
															'from_value' => $tmp_rs[$i]['from_value'],
															'to_value' => $tmp_rs[$i]['to_value'],
															'coeficient'   => $tmp_rs[$i]['coeficient']);
		}
						
	}
	
	
//پردازش ماليات معمولي
function process_tax($staffID , $PayVal ,$PayYear ,$PayMonth ) {
	
	/*اين فرد مشمول ماليات نمي باشد*/
	
	$SDate = $PayYear."/".$PayMonth."/01" ; 
	
	if($PayMonth < 7 ) $endDay = "31" ; 
	elseif($PayMonth > 6 &&  $PayMonth < 12 ) $endDay = "30 " ;
	elseif($PayMonth == 12 ) $endDay = "29" ;  
	
	$EDate = $PayYear."/".$PayMonth."/".$endDay ; 
	$EDate = DateModules::shamsi_to_miladi($EDate);
	$SDate = DateModules::shamsi_to_miladi($SDate);
	
	$qry = " select tax_include
				from staff_include_history
					where staff_id = ".$staffID." and start_date <= '".$EDate."' AND
					  	 (end_date IS NULL OR end_date = '0000-00-00' OR
						  end_date >= '".$EDate."' OR end_date > '".$SDate."' ) " ; 
	$res = PdoDataAccess::runquery($qry) ; 
	
	if($res[0]['tax_include'] == 0 ) {
		return ;
	}
	
	//..........................................
	$qry = "
			SELECT
					pit.staff_id staff_id,
					SUM(pit2.get_value + (pit2.diff_get_value * pit2.diff_value_coef)) sum_tax,
					SUM(pit.param1 + pit.diff_param1) sum_tax_include

			FROM        payment_items pit								
						LEFT OUTER JOIN payment_items pit2
							ON(pit2.staff_id = pit.staff_id AND 
								pit2.pay_year = pit.pay_year AND
								pit2.pay_month = pit.pay_month AND
								pit2.payment_type = ".NORMAL_PAYMENT." AND
								pit2.salary_item_type_id = pit.salary_item_type_id)

			WHERE   pit.pay_year >= ".$PayYear." AND
					pit.pay_month >= ".$PayMonth." AND pit.payment_type in (1,3) AND
					pit.salary_item_type_id IN( ".SIT_PROFESSOR_TAX.",
												".SIT_STAFF_TAX.",
												".SIT_WORKER_TAX.",
												".SIT5_STAFF_TAX.")  AND
				    pit.staff_id = ". $staffID ; 
	
	 $taxRes = PdoDataAccess::runquery($qry) ;
 
	 //.........................................................................
	 
	 $qry2 =	"
				SELECT  sth.staff_id,
						sth.start_date,
						sth.end_date,
						sth.tax_table_type_id,
						sth.payed_tax_value

				FROM    staff_tax_history sth 

				WHERE NOT((start_date > '".$EDate."') OR 
						  (end_date IS NOT NULL  AND  end_date != '0000-00-00' AND 
					  	   end_date < '".$SDate."')) AND  sth.staff_id = ".$staffID." 
				ORDER BY sth.staff_id,sth.start_date			
				
			  " ; 		
	 $taxHisRes = PdoDataAccess::runquery($qry2) ;	 
	 $tax_table_type_id = $taxHisRes[0]['tax_table_type_id']  ; 
  
	 exe_taxtable_sql($PayYear ,$PayMonth , $taxTable) ;
	 
	 if(! key_exists($tax_table_type_id, $taxTable)) {
		return ;				
	 }
	 
	$sum_tax_include = $taxRes[0]['sum_tax_include'] + $PayVal ;  
	$tax = 0;  //متغيري جهت نگهداري ماليات
	reset($taxTable);
	
	foreach( $taxTable[$tax_table_type_id] as $tax_table_row ) {

		$pay_mid_month_date = DateModules::shamsi_to_miladi($PayYear."/".$PayMonth."/15") ; 			

		if( DateModules::CompareDate($pay_mid_month_date, $tax_table_row['from_date']) != -1 && 
			DateModules::CompareDate($pay_mid_month_date,$tax_table_row['to_date']) != 1 ) { 
			if( $sum_tax_include >= $tax_table_row['from_value'] && $sum_tax_include <= $tax_table_row['to_value'] ) {
				$tax += ( $sum_tax_include - $tax_table_row['from_value'] ) * $tax_table_row['coeficient'];
			}
			else if($sum_tax_include > $tax_table_row['to_value']){
				$tax += ( $tax_table_row['to_value'] - $tax_table_row['from_value'] ) * $tax_table_row['coeficient'];
			}
		}
	}
	$tax = $tax - $taxRes[0]['sum_tax'] ; 
	$PaymentItems = array(					
						'get_value' => $tax,						
						'param1' => $PayVal , 
						'param3' => $taxRes[0]['sum_tax'] + $tax ,						
						'param5' => $tax_table_type_id  	
						);
	
		
	return $PaymentItems ;
 
	
}

	//پردازش ماليات تعديل شده
    function process_tax_normalize($staffID , $PayVal ,$PayYear ,$PayMonth) {
		
		/*اين فرد مشمول ماليات نمي باشد*/
		
		if($PayMonth > 6 && $PayMonth < 11 )
		{
			$SPayMonth = 7 ;
			$EPayMonth = 10 ;
		}		
		elseif($PayMonth > 10 )
		{
			$SPayMonth = 11 ;
			$EPayMonth = 12 ;
		}
		elseif($PayMonth >= 1 && $PayMonth < 4 )
		{
			$SPayMonth = 1 ; 
			$EPayMonth = 3 ;
		}
		elseif($PayMonth > 3 && $PayMonth < 7 )
		{
			$SPayMonth = 4; 
			$EPayMonth = 6 ;
		}
		
		$SDate = $PayYear."/".$SPayMonth."/01" ; 
		
		if($EPayMonth < 7 ) $endDay = "31" ; 
		elseif($EPayMonth > 6 &&  $EPayMonth < 12 ) $endDay = "30 " ;
		elseif($EPayMonth == 12 ) $endDay = "29" ;  
		
		$EDate = $PayYear."/".$EPayMonth."/".$endDay ; 
		$EDate = DateModules::shamsi_to_miladi($EDate);
		$SDate = DateModules::shamsi_to_miladi($SDate);
		
		$qry = " select tax_include
					from staff_include_history
						where staff_id = ".$staffID." and start_date <= '".$EDate."' AND
							 (end_date IS NULL OR end_date = '0000-00-00' OR
							  end_date >= '".$EDate."' OR end_date > '".$SDate."' ) " ; 
		$res = PdoDataAccess::runquery($qry) ; 
		
		if($res[0]['tax_include'] == 0 ) {
			return ;
		}
				
	//..........................................
	$qry = "
			SELECT
				  pit.staff_id staff_id,
					SUM(pit2.get_value + if( pit3.get_value IS NULL , 0 , pit3.get_value) + 
					   (pit2.diff_get_value * pit2.diff_value_coef) + 
						if(pit3.diff_get_value is null , 0 , (pit3.diff_get_value * pit3.diff_value_coef)) ) sum_tax,
					SUM(pit.param1 + pit.diff_param1) sum_tax_include
					
			FROM     payment_items pit							
					 LEFT OUTER JOIN payment_items pit2
							ON(pit2.staff_id = pit.staff_id AND 
							   pit2.pay_year = pit.pay_year AND
							   pit2.pay_month = pit.pay_month AND
							   pit2.payment_type != 3 AND
							   pit2.salary_item_type_id = pit.salary_item_type_id)
							   
					 LEFT OUTER JOIN payment_items pit3
							ON(pit3.staff_id = pit.staff_id AND 
							   pit3.pay_year = pit.pay_year AND
							   pit3.pay_month = pit.pay_month AND
							   pit3.payment_type = 3 AND if( pit3.pay_year = 1393 , pit3.pay_month > 1 , (1=1) )  AND
							   pit3.salary_item_type_id = pit.salary_item_type_id)

			WHERE pit.pay_year >= ".$PayYear." AND
				  pit.pay_month >= ".$SPayMonth." AND  pit.pay_month <= ".$EPayMonth." AND
				  pit.salary_item_type_id IN(".SIT_PROFESSOR_TAX.",
											 ".SIT_STAFF_TAX.",
											 ".SIT_WORKER_TAX.",
											 ".SIT5_STAFF_TAX.") AND
	   		      pit.staff_id = ". $staffID ; 
    	
	$taxRes = PdoDataAccess::runquery($qry) ;
	 
	 //.........................................................................
	 
	 $qry2 =	"
				SELECT  sth.staff_id,
						sth.start_date,
						sth.end_date,
						sth.tax_table_type_id,
						sth.payed_tax_value

				FROM    staff_tax_history sth 

				WHERE end_date IS NULL OR end_date = '0000-00-00' OR  end_date > '".$SDate."' AND
					  start_date < '".$EDate."' AND  sth.staff_id = ".$staffID." 
				ORDER BY sth.staff_id,sth.start_date			
				
				" ; 		
	$taxHisRes = PdoDataAccess::runquery($qry2) ;	 
	$tax_table_type_id = $taxHisRes[0]['tax_table_type_id']  ; 
	
	exe_taxtable_sql($PayYear ,$PayMonth , $taxTable) ;
		//.........................................................................
	/* تعدیل مالیات با توجه به بازه مربوط به آن ترم  در نظر گرفته می شود */

	$year_avg_tax_include = ( $taxRes[0]['sum_tax_include'] + $PayVal + $taxHisRes[0]['payed_tax_value']) / ($EPayMonth - $SPayMonth + 1);
	$sum_normalized_tax = $tax_table_type_id = 0; //متغيري جهت نگهداري ماليات تعديل شده براي cur_staff در تمام طول سال

	reset($this->tax_tables);

	for($m = $SPayMonth; $m <= $EPayMonth; $m++ ) {
		$begin_month_date = DateModules::shamsi_to_miladi($this->__YEAR."/".$m."/1") ; 			
		$end_month_date = DateModules::shamsi_to_miladi($this->__YEAR."/".$m."/".DateModules::DaysOfMonth($this->__YEAR,$m)) ;	

		for($j=0;$j< count($taxHisRes);$j++) {
			
			if( ( $taxHisRes[$j]['end_date'] != null  && $taxHisRes[$j]['end_date'] != '0000-00-00' ) && 
					DateModules::CompareDate($taxHisRes[$j]['end_date'],$begin_month_date) == -1 ) { 															
				continue;
			}
			if(DateModules::CompareDate($taxHisRes[$j]['start_date'],$end_month_date) == 1 ) { 						
				break;
			}
			
			$tax_table_type_id = $taxHisRes[$j]['tax_table_type_id'];
			break;
		}
		if(!isset($tax_table_type_id) ||  $tax_table_type_id == NULL) 
		{
			continue ; 
		}
		if(! key_exists($tax_table_type_id, $taxTable)) {
			return ;				
		}
			
		foreach( $taxTable[ $tax_table_type_id ] as $tax_table_row ) {
			$pay_mid_month_date = DateModules::shamsi_to_miladi($this->__YEAR."/".$m."/15") ;				
			if( DateModules::CompareDate($pay_mid_month_date, $tax_table_row['from_date']) != -1 && 
				DateModules::CompareDate($pay_mid_month_date,$tax_table_row['to_date']) != 1 ) { 
									
				if( $year_avg_tax_include >= $tax_table_row['from_value'] && $year_avg_tax_include <= $tax_table_row['to_value'] ) {
					$sum_normalized_tax += ( $year_avg_tax_include - $tax_table_row['from_value'] ) * $tax_table_row['coeficient'];						
					
				}
				else if($year_avg_tax_include > $tax_table_row['to_value']){
					$sum_normalized_tax += ( $tax_table_row['to_value'] - $tax_table_row['from_value'] ) * $tax_table_row['coeficient'];											
				}
			}
			
		}
	}
		
	
	$normalized_tax = $sum_normalized_tax -$taxRes[0]['sum_tax'];
	if($normalized_tax < 0)
	$normalized_tax = 0;
	//انتصاب ماليات تعديل شده به  payment_items
	
	$PaymentItems = array(					
					'get_value' => $normalized_tax,						
					'param1' => $PayVal , 
					'param2' => $sum_normalized_tax ,
					'param3' => $taxRes[0]['sum_tax'] + $normalized_tax ,						
					'param5' => $tax_table_type_id  	
					);

	return $PaymentItems ; 
			
	}

$task = isset($_POST ["task"]) ? $_POST ["task"] : (isset($_GET ["task"]) ? $_GET ["task"] : "");

switch ($task) {
	
    case "InsertData":
          InsertData();
	
}


function InsertData(){
		
	if(!empty($_FILES['attach']['name'])){ 	
		$data = new Spreadsheet_Excel_Reader();
		$data->setOutputEncoding('utf-8');
		$data->setRowColOffset(0);
		$data->read($_FILES["attach"]["tmp_name"]);       	
	}       
	$log_obj = new manage_group_pay_get_log();	

	$FileType = $_POST["PayType"] ; 
	$PayYear = $_POST["pay_year"] ; 
	$PayMonth = $_POST["pay_month"] ;
	$SID = $_POST["sid"] ;
 	
	$success_count = 0;
	$unsuccess_count = 0;
	
	      //.......ماموریت.......................................................
        if($FileType == 8 )
        {	
				
			if(empty($_FILES['attach']['name'])){ 
				
				$SDate = $PayYear."/".$PayMonth."/01" ; 
				if($PayMonth < 7 ) $endDay = "31" ;  elseif($PayMonth > 6 &&  $PayMonth < 12 ) $endDay = "30 " ;
				elseif($PayMonth == 12 ) $endDay = "29" ;  
				$EDate = $PayYear."/".$PayMonth."/".$endDay ; 
										
			$pdo = PdoDataAccess::getPdoObject();
			$pdo->beginTransaction();
					
			$query = "	SELECT staff_id , duration , region_coef , salary_item_type_id , mli.list_id , mli.list_row_no 

							FROM pay_get_lists pgl inner join mission_list_items mli

															on pgl.list_id = mli.list_id

												where list_type = 9 and pgl.list_date >= '".DateModules::shamsi_to_miladi($SDate) ."' and
														pgl.list_date <= j2g($PayYear,$PayMonth,$endDay) and doc_state = 3 " ; //".DateModules::shamsi_to_miladi($EDate) ."
			
			$res = PdoDataAccess::runquery($query) ;	
									
			for($i = 0 ; $i < count($res); $i++){
				
				$PaymentObj = new manage_payments(); 
				$PayItmObj = new manage_payment_items();  
				
				$query = " select staff_id , bank_id , account_no , last_cost_center_id , person_type  
						                from hrmstotal.staff where staff_id =".$res[$i]['staff_id'] ; 
					
				$resStf = PdoDataAccess::runquery($query) ;
				
				if( !isset($resStf[0]['bank_id']) || !($resStf[0]['bank_id'] > 0))
					{  
						$log_obj->make_unsuccess_rows($res[$i]['staff_id'], "-" ,"بانک فرد جهت پرداخت مشخص نمی باشد.");
						$unsuccess_count++;
						continue ; 
					}
					
					if( !($resStf[0]['account_no'] > 0))
					{ 
						$log_obj->make_unsuccess_rows($res[$i]['staff_id'], "-" ,"شماره حساب فرد جهت پرداخت مشخص نمی باشد.");
						$unsuccess_count++;
						continue ; 
					}
					
					if( !($resStf[0]['last_cost_center_id'] > 0))
					{ 
						$log_obj->make_unsuccess_rows($res[$i]['staff_id'], "-" ,"آخرین مرکز هزینه فرد مشخص نشده است.");
						$unsuccess_count++;
						continue ; 
					}
			
						//......... محاسبه ماموریت............................
					$coef = (!empty($res[$i]['region_coef'])  ? $res[$i]['region_coef'] : 0 ) ; 
					$param1 = 0 ; 					 
					$missionValue = manage_payment_calculation::calculate_mission($res[$i]['staff_id'],$PayYear,$PayMonth,$res[$i]['duration'],$coef,$param1) ; 
/*if($res[$i]['staff_id'] == 882660 ) {
 echo "----".$missionValue ;  	 die() ;  }*/
					 	
					//....................................................					
					$PaymentObj->staff_id = $res[$i]['staff_id'] ; 
					$PaymentObj->pay_year = $PayYear ; 
					$PaymentObj->pay_month = $PayMonth ; 
					$PaymentObj->payment_type = $FileType ; 
					$PaymentObj->bank_id = $resStf[0]['bank_id'] ; 
					$PaymentObj->account_no = $resStf[0]['account_no'] ; 
					$PaymentObj->state = 2 ;
					unset($payRes) ; 
					
					/*if( $resStf[0]['person_type'] == 1 || $resStf[0]['person_type'] == 2 || $resStf[0]['person_type'] == 3 ) 
						$DB = "hrms.";
					else 
						$DB = "hrms_sherkati."; */ 
					
					$qry = " select count(*) cn  
									from payments 
											where pay_year = ".$PayYear." and pay_month = ".$PayMonth." and staff_id = ".$PaymentObj->staff_id." and payment_type = ".$FileType ; 
					
					$payRes = PdoDataAccess::runquery($qry) ; 
		
					if($payRes[0]['cn'] == 0 )
					{		
						
						if(  $PaymentObj->Add($pdo) === false )
						{ 
							$log_obj->make_unsuccess_rows($res[$i]['staff_id'], "-" ,"خطا در ثبت  فیش ماموریت");
							$unsuccess_count++; 

							continue ; 
						}
					
						if($resStf[0]['person_type'] == 1 )   $SID = 42 ; 
						if($resStf[0]['person_type'] == 2 )   $SID = 43 ; 
						if($resStf[0]['person_type'] == 3 )   $SID = 10315 ; 
						if($resStf[0]['person_type'] == 5 )   $SID = 643 ; 
						
						//$SID = $res[$i]['salary_item_type_id'] ; 
						//............ مرکز هزینه .....................

						$PayItmObj->pay_year = $PayYear ; 
						$PayItmObj->pay_month = $PayMonth ; 
						$PayItmObj->param1 = $param1 ; 
						$PayItmObj->staff_id = $res[$i]['staff_id'] ; 
						$PayItmObj->salary_item_type_id = $SID ; 
						$PayItmObj->pay_value =  $missionValue ; 
						$PayItmObj->cost_center_id = $resStf[0]['last_cost_center_id'] ; 
						$PayItmObj->payment_type = 8 ; 	
										
						if(  $PayItmObj->Add() === false )
						{ 							
							$log_obj->make_unsuccess_rows($res[$i]['staff_id'], "-" ," عدم ثبت رکورد");
							$unsuccess_count++; 
							continue ; 
						}
	
						$qry = " update hrmstotal.mission_list_items set PayValue =".$missionValue." 
										where  list_id= ".$res[$i]['list_id']." and list_row_no=".$res[$i]['list_row_no'] ; 
							
	//print_r(ExceptionHandler::PopAllExceptions()) ; 
	//echo PdoDataAccess::GetLatestQueryString() .'----<br>'; 
						if( PdoDataAccess::runquery($qry,array(),$pdo) === false ) 
						{
							$log_obj->make_unsuccess_rows($res[$i]['staff_id'], "-" ,"خطا در ثبت مبلغ در رکورد ماموریت");
							$unsuccess_count++; 
							continue ; 
							
						}
					
					}
					else if($payRes[0]['cn'] > 0 ) 
					{		
										
						$qry = " select pay_value 
									from payment_items 
										where pay_year = ".$PayYear." and pay_month = ".$PayMonth." and staff_id = ".$PaymentObj->staff_id." and payment_type = ".$FileType ; 
						$resItem = PdoDataAccess::runquery($qry) ; 
						
												
						//................................................	
						if(count($resItem) > 0 ) {
							
						$PayItmObj->pay_year = $PayYear ; 
						$PayItmObj->pay_month = $PayMonth ; 
						$PayItmObj->param1 = $param1 ; 
						$PayItmObj->staff_id = $PaymentObj->staff_id ; 						
						$PayItmObj->pay_value = $missionValue + $resItem[0]['pay_value'] ; 
						$PayItmObj->payment_type = 8 ; 
								
						if(  $PayItmObj->Edit($pdo) === false )
						{ 
							$log_obj->make_unsuccess_rows($PaymentObj->staff_id, "-" ," خطای بروز رسانی ");
							$unsuccess_count++; 
							continue ; 
						} 
				
						$qry = " update hrmstotal.mission_list_items set PayValue =".$missionValue." 
										where  list_id= ".$res[$i]['list_id']." and list_row_no=".$res[$i]['list_row_no'] ; 

	//PdoDataAccess::runquery($qry) ; 
	// echo "***we*".PdoDataAccess::AffectedRows()."---";
	//print_r(ExceptionHandler::PopAllExceptions()) ; echo PdoDataAccess::GetLatestQueryString() .'----<br>';     die() ; 
	
						if( PdoDataAccess::runquery($qry,array(),$pdo) === false ) 
						{
							$log_obj->make_unsuccess_rows($res[$i]['staff_id'], "-" ,"خطا در ثبت مبلغ در رکورد ماموریت");
							$unsuccess_count++; 
							continue ; 
							
						}
						
						}
						
					}
					
				
			}	//End for 
		
			$log_obj->finalize();
			$st = preg_replace('/\r\n/',"",$log_obj->make_result("UploadPayFilesObj.expand();")) ; 
	
			if($unsuccess_count > 0)
			{							

				$pdo->rollBack();
			}
			else {		
//echo "************" ; die() ; 
				$pdo->commit(); 			
			}
			
			echo "{success:true,data:'" . $st . "'}";
			die(); 		
			}
			//........................................................ از طریق فایل اکسل ....................................................
			else {  
				die() ; 
			$pdo = PdoDataAccess::getPdoObject();
			$pdo->beginTransaction();
			
            for ($i = 1; $i < $data->sheets[0]['numRows']; $i++) {
				
				$PaymentObj = new manage_payments(); 
				$PayItmObj = new manage_payment_items();  
						
				if(!isset($data->sheets[0]['cells'][$i][0]) && !isset($data->sheets[0]['cells'][$i][1]))
					break ; 
					
					$query = " select staff_id , bank_id , account_no , last_cost_center_id , person_type  
						                from staff where staff_id =".$data->sheets[0]['cells'][$i][0] ; 
					
					$resStf = PdoDataAccess::runquery($query) ;
					
					if(count($resStf) == 0 )
					{
						$log_obj->make_unsuccess_rows($data->sheets[0]['cells'][$i][0], "-" ," شماره شناسایی معتبر نمی باشد.");
						$unsuccess_count++;
						continue ; 
					}
					
					if( !($resStf[0]['bank_id'] > 0))
					{
						$log_obj->make_unsuccess_rows($data->sheets[0]['cells'][$i][0], "-" ,"بانک فرد جهت پرداخت مشخص نمی باشد.");
						$unsuccess_count++;
						continue ; 
					}
					
					if( !($resStf[0]['account_no'] > 0))
					{
						$log_obj->make_unsuccess_rows($data->sheets[0]['cells'][$i][0], "-" ,"شماره حساب فرد جهت پرداخت مشخص نمی باشد.");
						$unsuccess_count++;
						continue ; 
					}
					
					if( !($resStf[0]['last_cost_center_id'] > 0))
					{
						$log_obj->make_unsuccess_rows($data->sheets[0]['cells'][$i][0], "-" ,"آخرین مرکز هزینه فرد مشخص نشده است.");
						$unsuccess_count++;
						continue ; 
					}
					
					//......... محاسبه ماموریت............................
					$coef = (!empty($data->sheets[0]['cells'][$i][2]) ? $data->sheets[0]['cells'][$i][2] : 0 ) ; 
										 
					$missionValue = manage_payment_calculation::calculate_mission($data->sheets[0]['cells'][$i][0],$PayYear,$PayMonth,$data->sheets[0]['cells'][$i][1],$coef) ; 
					
					//....................................................					
					$PaymentObj->staff_id = $data->sheets[0]['cells'][$i][0] ; 
					$PaymentObj->pay_year = $PayYear ; 
					$PaymentObj->pay_month = $PayMonth ; 
					$PaymentObj->payment_type = $FileType ; 
					$PaymentObj->bank_id = $resStf[0]['bank_id'] ; 
					$PaymentObj->account_no = $resStf[0]['account_no'] ; 
					$PaymentObj->state = 2 ;
					
					$qry = " select count(*) cn  
									from hrms.payments 
											where pay_year = ".$PayYear." and pay_month = ".$PayMonth." and staff_id = ".$PaymentObj->staff_id." and payment_type = ".$FileType ; 
					
					$payRes = PdoDataAccess::runquery($qry) ; 
					
					if($payRes[0]['cn'] == 0 )
					{		
											 
						if(  $PaymentObj->Add() === false )
						{ 
							$log_obj->make_unsuccess_rows($data->sheets[0]['cells'][$i][0], "-" ," خطا ");
							$unsuccess_count++; 

							continue ; 
						}
					
						if($resStf[0]['person_type'] == 1 )   $SID = 42 ; 
						if($resStf[0]['person_type'] == 2 )   $SID = 43 ; 
						//............ مرکز هزینه .....................

						$PayItmObj->pay_year = $PayYear ; 
						$PayItmObj->pay_month = $PayMonth ; 
						$PayItmObj->staff_id = $data->sheets[0]['cells'][$i][0] ; 
						$PayItmObj->salary_item_type_id = $SID ; 
						$PayItmObj->pay_value =  $missionValue ; 
						$PayItmObj->cost_center_id = $resStf[0]['last_cost_center_id'] ; 
						$PayItmObj->payment_type = 8 ; 	
										
						if(  $PayItmObj->Add() === false )
						{ 
							$log_obj->make_unsuccess_rows($data->sheets[0]['cells'][$i][0], "-" ," خطا ");
							$unsuccess_count++; 
							continue ; 
						}
					
					}
					else if(count($payRes) > 0 ) 
					{		
									
						$qry = " select pay_value 
									from hrms.payment_items 
										where pay_year = ".$PayYear." and pay_month = ".$PayMonth." and staff_id = ".$PaymentObj->staff_id." and payment_type = ".$FileType ; 
						$res = PdoDataAccess::runquery($qry) ; 
						
						$PayItmObj->pay_year = $PayYear ; 
						$PayItmObj->pay_month = $PayMonth ; 
						$PayItmObj->staff_id = $data->sheets[0]['cells'][$i][0] ; 						
						$PayItmObj->pay_value = $missionValue + $res[0]['pay_value'] ; 
						$PayItmObj->payment_type = 8 ; 
								
						if(  $PayItmObj->Edit() === false )
						{ 
							$log_obj->make_unsuccess_rows($data->sheets[0]['cells'][$i][0], "-" ," خطای بروز رسانی ");
							$unsuccess_count++; 
							continue ; 
						}						

					}
				
			} // End for  
			
			$log_obj->finalize();
			$st = preg_replace('/\r\n/',"",$log_obj->make_result("UploadPayFilesObj.expand();")) ; 
			
			if($unsuccess_count > 0)
			{				
				$pdo->rollBack();
			}
			else {
				$pdo->commit(); 			
			}
			
			echo "{success:true,data:'" . $st . "'}";
			die(); 			
		
		} 
		
		}
		//...................... بن غیر نقدی شش ماهه ........................
		if($FileType == 4 ||  $FileType == 5 )
        {
		
		
			$pdo = PdoDataAccess::getPdoObject();
			$pdo->beginTransaction();
			
            for ($i = 1; $i < $data->sheets[0]['numRows']; $i++) {
	
				$PaymentObj = new manage_payments(); 
				$PayItmObj = new manage_payment_items();  
						
				if(!isset($data->sheets[0]['cells'][$i][0]) /*&& !isset($data->sheets[0]['cells'][$i][1])*/)
					break ; 
					
					$query = " select staff_id , bank_id , account_no ,person_type , last_cost_center_id
						                from staff where staff_id =".$data->sheets[0]['cells'][$i][0] ; 
					
					$resStf = PdoDataAccess::runquery($query) ;
					
					if(count($resStf) == 0 )
					{
						$log_obj->make_unsuccess_rows($data->sheets[0]['cells'][$i][0], "-" ," شماره شناسایی معتبر نمی باشد.");
						$unsuccess_count++;
						continue ; 
					}
					
					if( !($resStf[0]['bank_id'] > 0))
					{
						$log_obj->make_unsuccess_rows($data->sheets[0]['cells'][$i][0], "-" ,"بانک فرد جهت پرداخت مشخص نمی باشد.");
						$unsuccess_count++;
						continue ; 
					}
					
					if( !($resStf[0]['account_no'] > 0))
					{
						$log_obj->make_unsuccess_rows($data->sheets[0]['cells'][$i][0], "-" ,"شماره حساب فرد جهت پرداخت مشخص نمی باشد.");
						$unsuccess_count++;
						continue ; 
					}
					
                                        if( !($resStf[0]['last_cost_center_id'] > 0))
					{
						$log_obj->make_unsuccess_rows($data->sheets[0]['cells'][$i][0], "-" ,"مرکز هزینه برای فرد مشخص نشده است.");
						$unsuccess_count++;
						continue ; 
					}				
				
					//......... محاسبه بن نقدی............................
					$BonValue = (!empty($data->sheets[0]['cells'][$i][1]) ? $data->sheets[0]['cells'][$i][1] : 0 ) ;  
													
					$PaymentObj->staff_id = $data->sheets[0]['cells'][$i][0] ; 
					$PaymentObj->pay_year = $PayYear ; 
					$PaymentObj->pay_month = $PayMonth ; 
					$PaymentObj->payment_type = $FileType ; 
					$PaymentObj->bank_id = $resStf[0]['bank_id'] ; 
					$PaymentObj->account_no = $resStf[0]['account_no'] ; 
					$PaymentObj->state = 1 ;
					
					/*if($resStf[0]['person_type'] == 10) 
					{
						$DB = "hrmr." ;
					}
					else 
					{*/
						$DB = "hrmstotal." ; 
					//}
					
					//.....................................
					$qry = " select count(*) cn  
									from ".$DB."payments 
											where pay_year = ".$PayYear." and pay_month = ".$PayMonth." and staff_id = ".$PaymentObj->staff_id." and payment_type = ".$FileType ; 
					
					$payRes = PdoDataAccess::runquery($qry) ; 
			
					if($payRes[0]['cn'] == 0 )
					{		
												 
						if(  $PaymentObj->Add("",$DB) === false )
						{ 
							$log_obj->make_unsuccess_rows($data->sheets[0]['cells'][$i][0], "-" ," خطا 22");
							$unsuccess_count++; 

							continue ; 
						}
											  
						//............ مرکز هزینه .....................

						$PayItmObj->pay_year = $PayYear ; 
						$PayItmObj->pay_month = $PayMonth ; 
						$PayItmObj->staff_id = $data->sheets[0]['cells'][$i][0] ; 
						$PayItmObj->salary_item_type_id = 9941 ; 
						$PayItmObj->pay_value =  $BonValue ; 
						$PayItmObj->cost_center_id = $resStf[0]['last_cost_center_id'] ; 
						$PayItmObj->payment_type = $FileType ; 	
										
						if(  $PayItmObj->Add("",$DB) === false )
						{ 
							$log_obj->make_unsuccess_rows($data->sheets[0]['cells'][$i][0], "-" ," خطا 444");
							$unsuccess_count++; 
							continue ; 
						}
					
					}
					else if(count($payRes) > 0 ) 
					{		
						
						$qry = " select pay_value 
									from ".$DB."payment_items 
										where pay_year = ".$PayYear." and pay_month = ".$PayMonth." and staff_id = ".$PaymentObj->staff_id." and payment_type = ".$FileType ; 
						$res = PdoDataAccess::runquery($qry) ; 
						
						$PayItmObj->pay_year = $PayYear ; 
						$PayItmObj->pay_month = $PayMonth ; 
						$PayItmObj->staff_id = $data->sheets[0]['cells'][$i][0] ; 						
						$PayItmObj->pay_value = $BonValue + $res[0]['pay_value'] ; 
						$PayItmObj->payment_type = $FileType ; 
								
						if(  $PayItmObj->Edit("",$DB) === false )
						{ 
							$log_obj->make_unsuccess_rows($data->sheets[0]['cells'][$i][0], "-" ," خطای بروز رسانی ");
							$unsuccess_count++; 
							continue ; 
						}						

					}
					
					
				
			} // End for  
			
			$log_obj->finalize();
			$st = preg_replace('/\r\n/',"",$log_obj->make_result("UploadPayFilesObj.expand();")) ; 
			
			if($unsuccess_count > 0)
			{				
				$pdo->rollBack();
			}
			else {
				$pdo->commit(); 			
			}
			
			echo "{success:true,data:'" . $st . "'}";
			die(); 			
		
		
		}
		// محاسبه پرداخت تالیف و ویراستاری.............................
		if($FileType == 12 )
        {		
		
			$pdo = PdoDataAccess::getPdoObject();
			$pdo->beginTransaction();
				
	
            for ($i = 1; $i < $data->sheets[0]['numRows']; $i++) {
	
				$PaymentObj = new manage_payments(); 
				$PayItmObj = new manage_payment_items();  
				
                                unset($TaxRes);

				if(!isset($data->sheets[0]['cells'][$i][0]))
					break ; 
					
					$query = " select staff_id , bank_id , account_no ,person_type ,last_cost_center_id
						                from staff where staff_id =".$data->sheets[0]['cells'][$i][0] ; 
					
					$resStf = PdoDataAccess::runquery($query) ;
					
					if(count($resStf) == 0 )
					{
						$log_obj->make_unsuccess_rows($data->sheets[0]['cells'][$i][0], "-" ," شماره شناسایی معتبر نمی باشد.");
						$unsuccess_count++;
						continue ; 
					}
					
					if( !($resStf[0]['bank_id'] > 0))
					{
						$log_obj->make_unsuccess_rows($data->sheets[0]['cells'][$i][0], "-" ,"بانک فرد جهت پرداخت مشخص نمی باشد.");
						$unsuccess_count++;
						continue ; 
					}
					
					if( !($resStf[0]['account_no'] > 0))
					{
						$log_obj->make_unsuccess_rows($data->sheets[0]['cells'][$i][0], "-" ,"شماره حساب فرد جهت پرداخت مشخص نمی باشد.");
						$unsuccess_count++;
						continue ; 
					}
					
					if( !($resStf[0]['last_cost_center_id'] > 0))
					{
						$log_obj->make_unsuccess_rows($data->sheets[0]['cells'][$i][0], "-" ,"آخرین مرکز هزینه فرد مشخص نشده است.");
						$unsuccess_count++;
						continue ; 
					}
					
					//.........محاسبه تالیف و ویراستاری...........................
					$TValue = (!empty($data->sheets[0]['cells'][$i][1]) ? $data->sheets[0]['cells'][$i][1] : 0 ) ;  
																		
					$PaymentObj->staff_id = $data->sheets[0]['cells'][$i][0] ; 
					$PaymentObj->pay_year = $PayYear ; 
					$PaymentObj->pay_month = $PayMonth ; 
					$PaymentObj->payment_type = $FileType ; 
					$PaymentObj->bank_id = $resStf[0]['bank_id'] ; 
					$PaymentObj->account_no = $resStf[0]['account_no'] ; 
					$PaymentObj->message = (!empty($data->sheets[0]['cells'][$i][3])) ? $data->sheets[0]['cells'][$i][3] :  0 ;
					$PaymentObj->state = 1 ;
										
					//.....................................
					$qry = " select count(*) cn  
									from payments 
											where pay_year = ".$PayYear." and pay_month = ".$PayMonth." and 
												  staff_id = ".$PaymentObj->staff_id." and payment_type = ".$FileType ; 
					
					$payRes = PdoDataAccess::runquery($qry) ; 
		
					if($payRes[0]['cn'] == 0 )
					{		
						
						if(  $PaymentObj->Add() === false )
						{ 
							$log_obj->make_unsuccess_rows($data->sheets[0]['cells'][$i][0], "-" ," خطا 22");
							$unsuccess_count++; 

							continue ; 
						}
											  
						//............ مرکز هزینه .....................

						$PayItmObj->pay_year = $PayYear ; 
						$PayItmObj->pay_month = $PayMonth ; 
						$PayItmObj->staff_id = $data->sheets[0]['cells'][$i][0] ; 
						$PayItmObj->salary_item_type_id = 10389 ; 
						$PayItmObj->pay_value =  $TValue ; 
						$PayItmObj->get_value = 0 ; 
						$PayItmObj->cost_center_id = $resStf[0]['last_cost_center_id'] ; 
						$PayItmObj->payment_type = 12 ; 	
										
						if(  $PayItmObj->Add() === false )
						{ 
							$log_obj->make_unsuccess_rows($data->sheets[0]['cells'][$i][0], "-" ," خطا 444");
							$unsuccess_count++; 
							continue ; 
						}
						//.......................................... محاسبه مالیات ........................................
						
						if($resStf[0]['person_type'] == 1 || $resStf[0]['person_type'] == 10 ) 
							$TaxKey = 146 ; 
						
						elseif($resStf[0]['person_type'] == 2 ) 
							$TaxKey = 147 ; 
						
						elseif($resStf[0]['person_type'] == 3 ) 
							$TaxKey = 148 ; 
						
						elseif($resStf[0]['person_type'] == 5 ) 
							$TaxKey = 747  ; 
				
						if($resStf[0]['person_type'] == 10 )
							$TaxResVal = ($TValue /10 ) ; 
						else	
							$TaxRes = process_tax($data->sheets[0]['cells'][$i][0] , $TValue ,$PayYear ,$PayMonth ) ; 
						
						$PayItmObj->pay_year = $PayYear ; 
						$PayItmObj->pay_month = $PayMonth ; 
						$PayItmObj->staff_id = $data->sheets[0]['cells'][$i][0] ; 
						$PayItmObj->salary_item_type_id = $TaxKey ; 
						$PayItmObj->get_value = ($resStf[0]['person_type'] == 10 ) ? $TaxResVal : $TaxRes['get_value'] ;
						$PayItmObj->pay_value = 0 ; 
						$PayItmObj->cost_center_id = $resStf[0]['last_cost_center_id'] ; 
						$PayItmObj->payment_type = 12 ; 
						$PayItmObj->param1 = ($resStf[0]['person_type'] == 10 ) ? 0 : $TaxRes['param1'] ; 						
						$PayItmObj->param3 =  ($resStf[0]['person_type'] == 10 ) ? 0 : $TaxRes['param3'] ;
						$PayItmObj->param4 = 1 ; 
						$PayItmObj->param5 = ($resStf[0]['person_type'] == 10 ) ? 0 : $TaxRes['param5'] ;
						
						if( $PayItmObj->Add() === false )
						{ 
							$log_obj->make_unsuccess_rows($data->sheets[0]['cells'][$i][0], "-" ," خطا 444");
							$unsuccess_count++; 
							continue ; 
						}
						
						//...................افزودن مبلغ علی الحساب.....................
						
						if( !empty($data->sheets[0]['cells'][$i][2]) &&  $data->sheets[0]['cells'][$i][2] > 0 )
						{
						
							$PayItmObj->pay_year = $PayYear ; 
							$PayItmObj->pay_month = $PayMonth ; 
							$PayItmObj->staff_id = $data->sheets[0]['cells'][$i][0] ; 
							$PayItmObj->salary_item_type_id = 4600 ; 
							$PayItmObj->get_value = $data->sheets[0]['cells'][$i][2] ; 
							$PayItmObj->pay_value = 0 ; 
							$PayItmObj->cost_center_id = $resStf[0]['last_cost_center_id'] ; 
							$PayItmObj->payment_type = 12 ; 
							$PayItmObj->param1 = 0 ; 
							$PayItmObj->param2 = 0 ; 						
							$PayItmObj->param3 = 0 ;
							$PayItmObj->param4 = 2 ; 
							$PayItmObj->param5 = 0 ; 
							
							if( $PayItmObj->Add() === false )
							{ 
								$log_obj->make_unsuccess_rows($data->sheets[0]['cells'][$i][0], "-" ," خطا 444");
								$unsuccess_count++; 
								continue ; 
							}
						
						}
					
					}
					
				
			} // End for  
			
			$log_obj->finalize();
			$st = preg_replace('/\r\n/',"",$log_obj->make_result("UploadPayFilesObj.expand();")) ; 
			
			if($unsuccess_count > 0)
			{				
				$pdo->rollBack();
			}
			else {
				$pdo->commit(); 			
			} 
			
			echo "{success:true,data:'" . $st . "'}";
			die(); 			
		
		
		}
		//......... محاسبه حق التدریس..................................
		if($FileType == 14 )
        {		
		
			$pdo = PdoDataAccess::getPdoObject();
			$pdo->beginTransaction();
				
	
            for ($i = 1; $i < $data->sheets[0]['numRows']; $i++) {
	
				$PaymentObj = new manage_payments(); 
				$PayItmObj = new manage_payment_items();  
						
				if(!isset($data->sheets[0]['cells'][$i][0]))
					break ; 
					
					$query = " select staff_id , bank_id , account_no ,person_type ,last_cost_center_id
						                from staff where staff_id =".$data->sheets[0]['cells'][$i][0]." AND person_type in (1,10) " ; 
					
					$resStf = PdoDataAccess::runquery($query) ;
					
					if(count($resStf) == 0 )
					{
						$log_obj->make_unsuccess_rows($data->sheets[0]['cells'][$i][0], "-" ," شماره شناسایی معتبر نمی باشد.");
						$unsuccess_count++;
						continue ; 
					}
					
					if( !($resStf[0]['bank_id'] > 0))
					{
						$log_obj->make_unsuccess_rows($data->sheets[0]['cells'][$i][0], "-" ,"بانک فرد جهت پرداخت مشخص نمی باشد.");
						$unsuccess_count++;
						continue ; 
					}
					
					if( !($resStf[0]['account_no'] > 0))
					{
						$log_obj->make_unsuccess_rows($data->sheets[0]['cells'][$i][0], "-" ,"شماره حساب فرد جهت پرداخت مشخص نمی باشد.");
						$unsuccess_count++;
						continue ; 
					}
					
					if( !($resStf[0]['last_cost_center_id'] > 0))
					{
						$log_obj->make_unsuccess_rows($data->sheets[0]['cells'][$i][0], "-" ,"آخرین مرکز هزینه فرد مشخص نشده است.");
						$unsuccess_count++;
						continue ; 
					}
			
					//............................. محاسبه مبلغ حق التدریس................
					$TValue = (!empty($data->sheets[0]['cells'][$i][1]) ? $data->sheets[0]['cells'][$i][1] : 0 ) ;  
																		
					$PaymentObj->staff_id = $data->sheets[0]['cells'][$i][0] ; 
					$PaymentObj->pay_year = $PayYear ; 
					$PaymentObj->pay_month = $PayMonth ; 
					$PaymentObj->payment_type = $FileType ; 
					$PaymentObj->writ_id = (!empty($data->sheets[0]['cells'][$i][2])) ? $data->sheets[0]['cells'][$i][2] :  0 ;
					$PaymentObj->writ_ver = (!empty($data->sheets[0]['cells'][$i][3])) ? $data->sheets[0]['cells'][$i][3] :  0 ; 
					$PaymentObj->bank_id = $resStf[0]['bank_id'] ; 
					$PaymentObj->account_no = $resStf[0]['account_no'] ; 
					$PaymentObj->message = (!empty($data->sheets[0]['cells'][$i][5])) ? $data->sheets[0]['cells'][$i][5] :  0 ;
					$PaymentObj->state = 1 ;
										
					//.....................................
					$qry = " select count(*) cn  
									from payments 
											where pay_year = ".$PayYear." and pay_month = ".$PayMonth." and 
												  staff_id = ".$PaymentObj->staff_id." and payment_type = ".$FileType ; 
					
					$payRes = PdoDataAccess::runquery($qry) ; 
		
					if($payRes[0]['cn'] == 0 )
					{		
						
						if(  $PaymentObj->Add() === false )
						{ 
							$log_obj->make_unsuccess_rows($data->sheets[0]['cells'][$i][0], "-" ," خطا 22");
							$unsuccess_count++; 

							continue ; 
						}
											  
						//............ مرکز هزینه .....................

						$PayItmObj->pay_year = $PayYear ; 
						$PayItmObj->pay_month = $PayMonth ; 
						$PayItmObj->staff_id = $data->sheets[0]['cells'][$i][0] ; 
						$PayItmObj->salary_item_type_id = 40 ; // کد قلم مربوط به حق التدریس
						$PayItmObj->pay_value =  $TValue ; 
						$PayItmObj->cost_center_id = $resStf[0]['last_cost_center_id'] ; 
						$PayItmObj->payment_type = 14 ; 	
										
						if(  $PayItmObj->Add() === false )
						{ 
							$log_obj->make_unsuccess_rows($data->sheets[0]['cells'][$i][0], "-" ," خطا 444");
							$unsuccess_count++; 
							continue ; 
						}
						//.......................................... محاسبه مالیات ........................................
						
						if($resStf[0]['person_type'] == 1 ) 
							$TaxKey = 146 ; 						
						elseif($resStf[0]['person_type'] == 2 ) 
							$TaxKey = 147 ; 
						
						elseif($resStf[0]['person_type'] == 3 ) 
							$TaxKey = 148 ; 
						
						elseif($resStf[0]['person_type'] == 5 ) 
							$TaxKey = 747  ; 
						elseif($resStf[0]['person_type'] == 10 ) 
							$TaxKey = 146 ; 
							
						//.............. تعدیل مالیات با توجه به بازه مرتبط با ترم ................................
						
						// $TaxRes = process_tax_normalize($data->sheets[0]['cells'][$i][0] , $TValue ,$PayYear ,$PayMonth ) ; 
						
											
						/*اين فرد مشمول ماليات نمي باشد*/
						
						$SDate = $PayYear."/".$PayMonth."/01" ; 
						
						if($PayMonth < 7 ) $endDay = "31" ; 
						elseif($PayMonth > 6 &&  $PayMonth < 12 ) $endDay = "30 " ;
						elseif($PayMonth == 12 ) $endDay = "29" ;  
						
						$EDate = $PayYear."/".$PayMonth."/".$endDay ; 
						$EDate = DateModules::shamsi_to_miladi($EDate);
						$SDate = DateModules::shamsi_to_miladi($SDate);
						$staffID = $data->sheets[0]['cells'][$i][0] ; 

						$qry = " select tax_include
									from staff_include_history
										where staff_id = ".$staffID." and start_date <= '".$EDate."' AND
											 (end_date IS NULL OR end_date = '0000-00-00' OR
											  end_date >= '".$EDate."' OR end_date > '".$SDate."' ) " ; 
						$res = PdoDataAccess::runquery($qry) ; 
						
						if($res[0]['tax_include'] == 0 ) {
							$TaxRes = 0 ; 
						}
						else {
							$TaxRes = ( $TValue * 10) / 100 ; 
						}
							
						
						$PayItmObj->pay_year = $PayYear ; 
						$PayItmObj->pay_month = $PayMonth ; 
						$PayItmObj->staff_id = $data->sheets[0]['cells'][$i][0] ; 
						$PayItmObj->salary_item_type_id = $TaxKey ; 
						$PayItmObj->get_value = $TaxRes ; //$TaxRes['get_value'] ; 
						$PayItmObj->pay_value = 0 ; 
						$PayItmObj->cost_center_id = $resStf[0]['last_cost_center_id'] ; 
						$PayItmObj->payment_type = 14 ; 
						$PayItmObj->param1 = $TValue ; //$TaxRes['param1'] ; 	
						$PayItmObj->param2 = 0 ; //$TaxRes['param2'] ;						
						$PayItmObj->param3 = 0 ; //$TaxRes['param3'] ; 
						$PayItmObj->param4 = 2 ; 
						$PayItmObj->param5 = 0 ; //$TaxRes['param5'] ;  
						
						if($TaxRes > 0 ) {
							if( $PayItmObj->Add() === false )
							{ 
								$log_obj->make_unsuccess_rows($data->sheets[0]['cells'][$i][0], "-" ," خطا 444");
								$unsuccess_count++; 
								continue ; 
							}
						}
						//...................افزودن مبلغ علی الحساب.....................
						
						if( !empty($data->sheets[0]['cells'][$i][4]) &&  $data->sheets[0]['cells'][$i][4] > 0 )
						{
						
							$PayItmObj->pay_year = $PayYear ; 
							$PayItmObj->pay_month = $PayMonth ; 
							$PayItmObj->staff_id = $data->sheets[0]['cells'][$i][0] ; 
							$PayItmObj->salary_item_type_id = 4600 ; 
							$PayItmObj->get_value = $data->sheets[0]['cells'][$i][4] ; 
							$PayItmObj->pay_value = 0 ; 
							$PayItmObj->cost_center_id = $resStf[0]['last_cost_center_id'] ; 
							$PayItmObj->payment_type = 14 ; 
							$PayItmObj->param1 = 0 ; 
							$PayItmObj->param2 = 0 ; 						
							$PayItmObj->param3 = 0 ;
							$PayItmObj->param4 = 2 ; 
							$PayItmObj->param5 = 0 ; 
							
							if( $PayItmObj->Add() === false )
							{ 
								$log_obj->make_unsuccess_rows($data->sheets[0]['cells'][$i][0], "-" ," خطا 444");
								$unsuccess_count++; 
								continue ; 
							}
						
						}
						
						
					
					}
					
				
			} // End for  
			
			$log_obj->finalize();
			$st = preg_replace('/\r\n/',"",$log_obj->make_result("UploadPayFilesObj.expand();")) ; 
			
			if($unsuccess_count > 0)
			{				
				$pdo->rollBack();
			}
			else {
				$pdo->commit(); 			
			} 
			
			echo "{success:true,data:'" . $st . "'}";
			die(); 			
		
		
		}

	//.........محاسبه عیدی و پاداش..........................................
		if($FileType == 2 )
        {				
			
		$pdo = PdoDataAccess::getPdoObject();
		$pdo->beginTransaction();
		
		//.........................محاسبه کارکرد سالانه...................
		$year_fdate = DateModules::shamsi_to_miladi($PayYear."/01/01") ;  
		$year_edate = DateModules::shamsi_to_miladi(($PayYear+1)."/01/01") ;  
		
		$year_fdate = str_replace("/","-",$year_fdate);
		$year_edate = str_replace("/","-",$year_edate);
		
		PdoDataAccess::runquery('DROP TABLE IF EXISTS temp_work_writs;') ; 
		PdoDataAccess::runquery('
								CREATE TABLE temp_work_writs  AS
								SELECT w.staff_id,										
									CASE WHEN w.emp_mode IN (3,8,9,15,7,16,11,12,14,20,22,25,27,28,29) 
										THEN 0 
										WHEN  w.emp_mode IN ( '.EMP_MODE_LEAVE_WITH_SALARY.' ) THEN 1
										ELSE (CASE w.annual_effect
														WHEN 1 THEN 1
														WHEN 2 THEN 0.5
														WHEN 3 THEN 0
														WHEN 4 THEN 2
											END) END annual_coef,
									CASE 
										WHEN w.execute_date < \''.$year_fdate.'\' THEN \''.$year_fdate.'\'
										ELSE w.execute_date
									END execute_date,
									CASE
										WHEN ( SELECT MIN(w2.execute_date) execute_date
												FROM writs w2
												WHERE w2.execute_date <= \''.$year_edate.'\' AND
														w2.staff_id = w.staff_id AND
														w2.history_only = 0 AND
														w2.state = '.WRIT_SALARY.' AND
														(w2.execute_date > w.execute_date OR
														(w2.execute_date = w.execute_date AND w2.writ_id > w.writ_id) OR
														(w2.execute_date = w.execute_date AND w2.writ_id = w.writ_id AND w2.writ_ver > w.writ_ver))
												GROUP BY staff_id) IS NULL THEN \''.$year_edate.'\'
										ELSE ( SELECT MIN(w2.execute_date) execute_date
												FROM writs w2
												WHERE   w2.execute_date <= \''.$year_edate.'\' AND
														w2.staff_id = w.staff_id AND
														w2.history_only = 0 AND
														w2.state = '.WRIT_SALARY.' AND
														(w2.execute_date > w.execute_date OR
														(w2.execute_date = w.execute_date AND w2.writ_id > w.writ_id) OR
														(w2.execute_date = w.execute_date AND w2.writ_id = w.writ_id AND w2.writ_ver > w.writ_ver))
												GROUP BY staff_id)
											END end_date,
									w.person_type
								FROM writs w
								WHERE w.history_only = 0 AND
									w.state = '.WRIT_SALARY.' AND
									( \''.$year_edate.'\' >= w.execute_date OR w.execute_date IS NULL OR w.execute_date = \'0000-00-00\') 								
							'); 
			 

		PdoDataAccess::runquery('ALTER TABLE temp_work_writs ADD INDEX(staff_id)');
		
		PdoDataAccess::runquery('DROP TABLE IF EXISTS temp_last_salary_writs;'); 
		PdoDataAccess::runquery('CREATE  TABLE temp_last_salary_writs  AS
									SELECT w.staff_id,
										SUBSTRING_INDEX(SUBSTRING( MAX( CONCAT(w.execute_date,w.writ_id,\'.\',w.writ_ver) ),11) ,\'.\',1) writ_id,
										SUBSTRING_INDEX(MAX( CONCAT(w.execute_date,w.writ_id,\'.\',w.writ_ver) ) ,\'.\',-1) writ_ver
									FROM writs w
									WHERE w.state = '.WRIT_SALARY.' AND
										w.history_only = 0  AND if(w.person_type = 3 , w.emp_mode not in ( 3,8,9,15,7,16,11,12,14,20,22) , (1=1)) 										 
									GROUP BY w.staff_id;');
										
		PdoDataAccess::runquery('ALTER TABLE temp_last_salary_writs ADD INDEX(staff_id,writ_id,writ_ver);');

		PdoDataAccess::runquery("SET NAMES 'utf8'");
						
	$WritWrk_DT = PdoDataAccess::runquery_fetchMode(' SELECT  w.staff_id,
														p.plname,
														p.pfname,
														w.person_type,
														w.cost_center_id,
														tlw.writ_id  last_writ_id,
														tlw.writ_ver last_writ_ver,
														s.bank_id,
														s.account_no,														
														si.tax_include,
														pay.staff_id as before_calced,
														( SELECT tax_table_type_id
														FROM staff_tax_history sth
														WHERE sth.staff_id = w.staff_id
														ORDER BY start_date DESC
														LIMIT 1
														) as tax_table_type_id,
														( SELECT SUM(wsi.value)
														FROM writ_salary_items wsi
														WHERE wsi.writ_id = w.writ_id AND
																wsi.writ_ver = w.writ_ver AND
																wsi.salary_item_type_id IN('.SIT_WORKER_BASE_SALARY.','.SIT_WORKER_ANNUAL_INC.') AND
																w.person_type = '.HR_WORKER.' AND
																w.state = '.WRIT_SALARY.'
														) as worker_base_salary,
														SUM(DATEDIFF(tw.end_date,tw.execute_date) * tw.annual_coef) work_time
												FROM    temp_work_writs tw
														INNER JOIN staff s
															ON(tw.staff_id = s.staff_id)
														INNER JOIN staff_include_history si
															ON(s.staff_id = si.staff_id AND si.start_date <= \''.$year_edate.'\' AND (si.end_date IS NULL OR si.end_date = \'0000-00-00\' OR si.end_date >= \''.$year_edate.'\') )
														INNER JOIN persons p
															ON(s.PersonID = p.PersonID)
														INNER JOIN temp_last_salary_writs tlw
															ON(s.staff_id = tlw.staff_id)
														INNER JOIN writs w
															ON(tlw.staff_id = w.staff_id AND tlw.writ_id = w.writ_id AND tlw.writ_ver = w.writ_ver AND
															(w.person_type = '.HR_WORKER.' OR w.emp_mode <> '.EMP_MODE_RETIRE.') )
														LEFT OUTER JOIN payments pay
															ON(pay.pay_year = '.$PayYear.' AND pay.pay_month=12 AND pay.payment_type= '.HANDSEL_PAYMENT.' AND pay.staff_id = s.staff_id)
														
												WHERE   s.staff_id not in (1085919 , 25 , 29 , 1086493 , 1085766 , 1086203 , 1086272 , 1085025 ,2003012 ,1081975) AND tw.end_date > \''.$year_fdate.'\' 
												GROUP BY w.staff_id,
														p.plname,
														p.pfname,
														w.person_type,
														w.cost_center_id,
														tlw.writ_id,
														tlw.writ_ver,
														s.bank_id,
														s.account_no,
														s.tafsili_id,
														pay.staff_id     having work_time > 0  ');
	//	echo PdoDataAccess::GetLatestQueryString(); die();
 $count = $WritWrk_DT->rowCount();

//$WritWrkRes
		//.....................................................................
			
		if( DateModules::YearIsLeap($PayYear) )
			$Month12Leng = 30;
		else
			$Month12Leng = 29;
				
		//..............................انتقال پارامترهای حقوقی به یک آرایه .............		
		exe_param_sql($PayYear, 12, $salaryParam);		
		//........................................انتقال داده های جداول مالیاتی به یک آرایه ......................................		
		exe_taxtable_sql($PayYear ,12,$taxTable) ;		
		//......................................................................
		
		 for($i=0; $i< $count ;$i++)
		 {

$WritWrkRes = $WritWrk_DT->fetch();

			$PaymentObj = new manage_payments(); 
			$PayItmObj = new manage_payment_items();
			
			 //................... اعمال کنترل..................................
			if($WritWrkRes['cost_center_id'] == NULL)
			{
				$log_obj->make_unsuccess_rows($WritWrkRes['staff_id'], "-" ,"براي اين شخص مرکز هزينه مشخص نشده است.");
				$unsuccess_count++;
				continue ; 				
			}
			if($WritWrkRes['tax_table_type_id'] == NULL)
			{
				$log_obj->make_unsuccess_rows($WritWrkRes['staff_id'], "-" ,"براي اين شخص جدول مالياتي مشخص نشده است.");
				$unsuccess_count++;
				continue ; 				
			}
			if($WritWrkRes['before_calced'] > 0 )
			{
				$log_obj->make_unsuccess_rows($WritWrkRes['staff_id'], "-" ,"محاسبه عيدي و پاداش اين شخص قبلا انجام شده است.");
				$unsuccess_count++;
				continue ; 				
			}				
			if(empty($taxTable[$WritWrkRes['tax_table_type_id']]) )
			{
				$log_obj->make_unsuccess_rows($WritWrkRes['staff_id'], "-" ," جدول مالیاتی برای فرد ناقص تعریف شده است.");
				$unsuccess_count++;
				continue ; 				
			}
			if($WritWrkRes['work_time'] == 0)
			{
				continue ;
			}
//.....................


if($WritWrkRes['staff_id']  == 2002664 ) 
$WritWrkRes['work_time'] = 365 ;


if($WritWrkRes['staff_id']  == 2002612) 
$WritWrkRes['work_time'] = 365 ;

if($WritWrkRes['staff_id']  == 2002561) 
$WritWrkRes['work_time'] = 365 ;


if($WritWrkRes['staff_id']  == 2003053 || $WritWrkRes['staff_id']  == 2002691 ) 
$WritWrkRes['work_time'] = 365 ;
	

//..........................
			//................................محاسبه عیدی و پاداش با توجه به کارکرد فرد در طول سال................................
			$value = 0 ; 
			if(DateModules::YearIsLeap($PayYear)) 
				$year_length = 366;
			else
				$year_length = 365;

			if($WritWrkRes['person_type'] == HR_WORKER ) {
				if($WritWrkRes['worker_base_salary'] * 2 > $salaryParam[SPT_JOB_SALARY][1] * 30 * 3)
				$value = $salaryParam[SPT_JOB_SALARY][1] * 30 * 3;
				else
					$value = $WritWrkRes['worker_base_salary'] * 2;
				$value *= $WritWrkRes['work_time'] / $year_length;
				$param2 = $salaryParam[SPT_JOB_SALARY][1];
			}else {		   
				$value = ($WritWrkRes['work_time'] / $year_length) * $salaryParam[SPT_HANDSEL_VALUE][0];
				$param2 = $salaryParam[SPT_HANDSEL_VALUE][0];
			}

			if($WritWrkRes['person_type'] == HR_EMPLOYEE )
				$key = 164 ;
			elseif($WritWrkRes['person_type'] == HR_PROFESSOR)
				$key = 163 ;
			elseif($WritWrkRes['person_type'] == HR_WORKER )
				$key = 165 ;
			elseif($WritWrkRes['person_type'] == HR_CONTRACT )
				$key = 764 ; 

			$PaymentObj->staff_id = $WritWrkRes['staff_id'] ; 
			$PaymentObj->pay_year = $PayYear ; 
			$PaymentObj->pay_month = 12 ; 
			$PaymentObj->payment_type = $FileType ; 
			$PaymentObj->writ_id = $WritWrkRes['last_writ_id'];
			$PaymentObj->writ_ver = $WritWrkRes['last_writ_ver'];
			$PaymentObj->bank_id = $WritWrkRes['bank_id'] ; 
			$PaymentObj->account_no = $WritWrkRes['account_no'] ; 
			$PaymentObj->state = 1 ;

			if(  $PaymentObj->Add() === false )
			{ 
				$log_obj->make_unsuccess_rows($WritWrkRes['staff_id'], "-" ," خطا در ثبت جدول پرداخت");
				$unsuccess_count++; 
				continue ; 
			}
 
			$PayItmObj->pay_year = $PayYear ; 
			$PayItmObj->pay_month = 12 ; 
			$PayItmObj->staff_id = $WritWrkRes['staff_id'] ; 
			$PayItmObj->salary_item_type_id = $key ; 
			$PayItmObj->pay_value = $value ; 
			$PayItmObj->get_value = 0 ; 
			$PayItmObj->param1 = $WritWrkRes['work_time']  ; 
			$PayItmObj->param2 = $param2 ; 
			$PayItmObj->cost_center_id =$WritWrkRes['cost_center_id']  ;
			$PayItmObj->payment_type = HANDSEL_PAYMENT ; 

			if( $PayItmObj->Add() === false )
			{ 	
				$log_obj->make_unsuccess_rows($WritWrkRes['staff_id'], "-" ,"خطا در ثبت اقلام پرداختی");
				$unsuccess_count++; 
				continue ; 
			}

			//..........................محاسبه مالیات مربوط به عیدی و پاداش ...................
		
			if(empty($WritWrkRes['tax_include'])) {
				continue ;
			}
			
			$handsel_key = $key ;
			
			if($WritWrkRes['person_type'] == 1 ) 
				$TaxKey = 146 ; 						
			elseif($WritWrkRes['person_type'] == 2 ) 
				$TaxKey = 147 ; 

			elseif($WritWrkRes['person_type'] == 3 ) 
				$TaxKey = 148 ; 

			elseif($WritWrkRes['person_type'] == 5 ) 
				$TaxKey = 747  ; 
					
			$handsel_value = $value ;
			$tax = 0;  //متغيري جهت نگهداري ماليات 
		
			reset($taxTable);
		
			foreach( $taxTable[$WritWrkRes['tax_table_type_id']] as $tax_table_row ) {
				$pay_mid_month_date = DateModules::shamsi_to_miladi($PayYear."/12/15");

				if( DateModules::CompareDate($pay_mid_month_date, $tax_table_row['from_date']) != -1 && 
					DateModules::CompareDate($pay_mid_month_date,$tax_table_row['to_date']) != 1 ) {
					if( $handsel_value >= $tax_table_row['from_value'] && $handsel_value <= $tax_table_row['to_value'] ) {
						$tax += ( $handsel_value - $tax_table_row['from_value'] ) * $tax_table_row['coeficient'];
					}
					else if($handsel_value >$tax_table_row['to_value']) {
						$tax += ( $tax_table_row['to_value'] - $tax_table_row['from_value'] ) * $tax_table_row['coeficient'];
					}
				}
			}	
		
		if($tax > 0 )
		{
			$PayItmObj->pay_year = $PayYear ; 
			$PayItmObj->pay_month = 12 ; 
			$PayItmObj->staff_id = $WritWrkRes['staff_id'] ; 
			$PayItmObj->salary_item_type_id = $TaxKey ; 
			$PayItmObj->pay_value = 0 ; 
			$PayItmObj->get_value = $tax ; 
			$PayItmObj->param1 = $handsel_value  ; 
			$PayItmObj->param2 = $WritWrkRes['tax_table_type_id'] ; 
			$PayItmObj->cost_center_id =$WritWrkRes['cost_center_id']  ;
			$PayItmObj->payment_type = HANDSEL_PAYMENT ; 
		
		
			if( $PayItmObj->Add() === false )
			{ 
				$log_obj->make_unsuccess_rows($WritWrkRes['staff_id'], "-" ,"خطا در ثبت اقلام پرداختی");
				$unsuccess_count++; 
				continue ; 
			}



		}


		}//End For
	
		$log_obj->finalize();
		$st = preg_replace('/\r\n/',"",$log_obj->make_result("UploadPayFilesObj.expand();")) ; 

		if($unsuccess_count > 0)
		{				
			$pdo->rollBack();
		}
		else {
			$pdo->commit(); 			
		} 

		echo "{success:true,data:'" . $st . "'}";
		die(); 
		
		} 


if($FileType == 7 )
        {
	//echo "*ererer**" ; die();
$pdo = PdoDataAccess::getPdoObject();
$pdo->beginTransaction();

	for ($i=1; $i < $data->sheets[0]['numRows']; $i++) {

$query = " select staff_id , PersonID
											from staff where staff_id =".$data->sheets[0]['cells'][$i][0] ; 
						
		$resStf = PdoDataAccess::runquery($query) ;
		//echo PdoDataAccess::GetLatestQueryString() ; die();
		if(count($resStf) == 0 )
		{
			$log_obj->make_unsuccess_rows($data->sheets[0]['cells'][$i][0], "-" ," شماره شناسایی معتبر نمی باشد.");
			$unsuccess_count++;
			continue ; 
		}
	
	$query = " insert into person_subtracts (PersonID ,  subtract_type ,  first_value , instalment,
											 remainder ,  start_date ,  end_date ,  salary_item_type_id , 
											 reg_date )  values (".$resStf[0]['PersonID']." , 3 , 
												                 ".$data->sheets[0]['cells'][$i][1].",
																 ".$data->sheets[0]['cells'][$i][1].",
																 ".$data->sheets[0]['cells'][$i][1].",
																 '2015-02-20' ,'2015-03-20' , 9945 , '2015-03-15' ) " ;

//echo $query ; die();
	PdoDataAccess::runquery($query); 

//echo PdoDataAccess::GetLatestQueryString() ; die();

	}
		$log_obj->finalize();
			$st = preg_replace('/\r\n/',"",$log_obj->make_result("UploadPayFilesObj.expand();")) ; 
			
			if($unsuccess_count > 0)
			{				
				$pdo->rollBack();
			}
			else {
				$pdo->commit(); 			
			} 
			
			echo "{success:true,data:'" . $st . "'}";
			die();
	}
		
				
}
?>