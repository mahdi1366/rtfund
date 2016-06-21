<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	93.03
//---------------------------
require_once(inc_response);
require_once inc_dataReader;
require_once inc_PDODataAccess;
require_once $address_prefix . "/HumanResources/salary/salary_params/class/salary_params.class.php";

class manage_salary_utils
{

//مقدار ماليات را براي گزارشات خزانه شبيه سازي مي كند 
	public static function simulate_tax($pay_year,$pay_month,$payment_type=NULL){
		
		$e_date = "31/".$pay_month."/".$pay_year ; 		
		
		$end_month_date = DateModules::shamsi_to_miladi($e_date,'-') ; 
		
		$middle_date = "15/".$pay_month."/".$pay_year ; 	
		$middle_month_date = DateModules::shamsi_to_miladi($middle_date,'-') ; 
		$payment_type_where = '' ;		
			if($payment_type)
				$payment_type_where = ' AND pi.payment_type = '.$payment_type ;

			PdoDataAccess::runquery("DROP TABLE IF EXISTS temp_tax_table_items") ; 
		
			PdoDataAccess::runquery("DROP TABLE IF EXISTS temp_tax_include_sum") ; 
			
			PdoDataAccess::runquery("DROP TABLE IF EXISTS temp_tax_include_incremental_sum ;") ; 
			
			PdoDataAccess::runquery("DROP TABLE IF EXISTS temp_limit_staff ;") ; 
			 
			PdoDataAccess::runquery("DROP TABLE IF EXISTS temp_paied_tax ;" ) ; 
			
			$w1 = "" ; 
			if($pay_year>=1393 && $pay_month > 2 ) 
			{
				$w1 = " OR si.end_date ='0000-00-00'" ;
			}
	
			PdoDataAccess::runquery(" CREATE  TABLE temp_limit_staff AS
										SELECT DISTINCT s.staff_id
										FROM staff s
										INNER JOIN staff_include_history si
											ON (s.staff_id = si.staff_id AND si.start_date <= ('".$end_month_date."') AND 
											   (si.end_date IS NULL $w1 OR si.end_date >= ('".$end_month_date."')) ) 
										WHERE si.tax_include = 1;") ;
							
			PdoDataAccess::runquery("ALTER TABLE temp_limit_staff ADD INDEX (staff_id);");

			PdoDataAccess::runquery(" CREATE  TABLE temp_paied_tax AS
										SELECT pai.staff_id,pai.get_value tax_value
										FROM   payment_items pai
												INNER JOIN temp_limit_staff tls
													ON (pai.staff_id = tls.staff_id)
										WHERE  pai.pay_year = ".$pay_year." AND	
												pai.pay_month = ".$pay_month." AND	
												pai.payment_type = 1 AND
												pai.salary_item_type_id IN (".SIT_PROFESSOR_TAX.",".SIT_STAFF_TAX.",".SIT_WORKER_TAX.",747)");
										
			PdoDataAccess::runquery(" CREATE  TABLE temp_tax_include_sum AS
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
										WHERE pi.pay_year = ".$pay_year." AND pi.pay_month = ".$pay_month." AND sit.tax_include = 1 
															 ".$payment_type_where."
										GROUP BY
										staff_id;");
																			
										
			$w2 = $w3 = "" ; 
			if($pay_year>=1393 && $pay_month > 2 ) 
			{
				$w2 = " OR sth.end_date ='0000-00-00'" ; 
				$w3 = " OR tt.to_date ='0000-00-00'" ;
			}

			PdoDataAccess::runquery(" CREATE  table temp_tax_table_items
										SELECT s.staff_id , tti.*
										FROM temp_limit_staff s
										INNER JOIN staff_tax_history sth
											ON (s.staff_id = sth.staff_id AND 
												sth.start_date <= '".$middle_month_date."' AND 
												(sth.end_date >= '".$middle_month_date."' OR sth.end_date IS NULL $w2 ))
										INNER JOIN tax_table_types ttt
											ON (ttt.tax_table_type_id = sth.tax_table_type_id)
										INNER JOIN tax_tables tt
											ON (tt.tax_table_type_id = ttt.tax_table_type_id AND 
												tt.from_date <= '".$middle_month_date."' AND 
												(tt.to_date >= '".$middle_month_date."' OR tt.to_date IS NULL  $w3 ))
										INNER JOIN tax_table_items tti
											ON (tti.tax_table_id = tt.tax_table_id) ");
											

			PdoDataAccess::runquery(" CREATE  TABLE temp_tax_include_incremental_sum AS
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
	
	$e_date = "31/".$pay_month."/".$pay_year ; 
	$end_month_date = DateModules::shamsi_to_miladi($e_date,'-') ; 
	
	$s_date = "01/".$pay_month."/".$pay_year ; 
	
	$start_month_date = DateModules::shamsi_to_miladi($s_date,'-') ; 
	
	$worker_month_day = 30;
	$actual_month_day = DateModules::DaysOfMonth($pay_year,$pay_month) ; 
	
	if($payment_type)
	   $payment_type_where = ' AND pi.payment_type = '.$payment_type ;
	   
	//...................................
	
	PdoDataAccess::runquery("DROP TABLE IF EXISTS temp_insure_include_sum ") ; 
	
	PdoDataAccess::runquery(" DROP TABLE IF EXISTS temp_limit_staff "); 
	
	$w1 = "" ; 
	if($pay_year>=1393 && $pay_month > 2 ) 
	{
	   $w1 = " OR si.end_date ='0000-00-00'" ;
	}
	
	PdoDataAccess::runquery(" CREATE  TABLE temp_limit_staff AS
								SELECT DISTINCT s.staff_id , s.person_type
								FROM staff s
									INNER JOIN staff_include_history si
										ON (s.staff_id = si.staff_id AND si.start_date <= '".$end_month_date."' AND 
										   (si.end_date IS NULL $w1 OR si.end_date >= '".$end_month_date."')) 
								WHERE si.insure_include = 1 ");
														

	PdoDataAccess::runquery("ALTER TABLE temp_limit_staff ADD INDEX (staff_id)");
	
	PdoDataAccess::runquery(" CREATE  table temp_insure_include_sum AS
								SELECT s.staff_id ,
										0 value,
										sum(pi.pay_value) + CASE WHEN sum(pi.diff_pay_value * pi.diff_value_coef) < 0 THEN 0 ELSE sum(pi.diff_pay_value * pi.diff_value_coef) END param1 ,
										0 param2 ,
										0 param3 , 
										CASE WHEN s.person_type in (".HR_WORKER." , ".HR_CONTRACT.") THEN $worker_month_day ELSE ".$actual_month_day." END month_days
								FROM payment_items pi
								INNER JOIN salary_item_types sit
									ON (pi.salary_item_type_id = sit.salary_item_type_id AND 
										sit.credit_topic = ".CREDIT_TOPIC_1." AND 
										sit.insure_include = 1)
								INNER JOIN temp_limit_staff s
									ON (s.staff_id = pi.staff_id)
								WHERE pi.pay_year = ".$pay_year." AND 
									pi.pay_month = ".$pay_month." AND 
									pi.payment_type = 1
									".$payment_type_where."
								GROUP BY
								staff_id; ");
								
	
	PdoDataAccess::runquery("ALTER TABLE temp_insure_include_sum ADD INDEX (staff_id)");
	
	$max_daily_salary_insure_include = manage_salary_params::get_salaryParam_value("", 100 , SPT_MAX_DAILY_SALARY_INSURE_INCLUDE, $start_month_date);
	 
	PdoDataAccess::runquery(" UPDATE temp_insure_include_sum
							  SET param1 = ".($max_daily_salary_insure_include * $worker_month_day)." 
							  WHERE param1 > ".($max_daily_salary_insure_include * $worker_month_day)) ; 
			
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
								SET value =  param1 * ".$person_value." ,
									param2 = param1 * ".$employmer_value." ,
									param3 = param1 * ".$unemployment_insurance_value." 
								WHERE (1=1)");
								
}

	public static function get_arrear_document($staff_id,$pay_year,$pay_month){
	
		
		for ($t = $_POST['from_pay_year']; $t < ( $_POST['to_pay_year'] + 1 ); $t++) {
        $retCoef = 1;


        $qry = " select max(arrear_ver) MV
					from  corrective_payment_writs 
						where staff_id = " . $SID . " and pay_year = " . $t . "
		       ";
        $MaxVer = PdoDataAccess::runquery($qry);


        if (count($MaxVer) == 0 || $MaxVer[0]['MV'] == 0)
            continue;

		 $qry = " select  cpw.staff_id , cpw.writ_id ,  cpw.writ_ver , cpw.arrear_ver , w.execute_date, w.send_letter_no , w.salary_pay_proc
		
				 from corrective_payment_writs cpw inner join  writs w 
			                                         on cpw.staff_id = w.staff_id and 
														cpw.writ_id = w.writ_id and 
														cpw.writ_ver = w.writ_ver 

			 where cpw.pay_year = " . $t . " and cpw.staff_id = " . $SID . " and arrear_ver = " . $MaxVer[0]['MV'] . " and cpw.pay_month = 12 
			 order by  w.execute_date ";


        $res1 = PdoDataAccess::runquery($qry);
		
	
        //............................. پیدا کردن آخرین ماهی که فیش حقوقی در آن محاسبه شده است ................

        $qry = " select max(pay_month) PM from payment_writs where staff_id = " . $SID . " AND pay_year= " . $t;
        $MaxMonth = PdoDataAccess::runquery($qry);

        if ($MaxMonth[0]['PM'] > 0 && $MaxMonth[0]['PM'] < 7)
            $LastDate = $t . "/" . $MaxMonth[0]['PM'] . "/31";

        else if ($MaxMonth[0]['PM'] > 6 && $MaxMonth[0]['PM'] < 12)
            $LastDate = $t . "/" . $MaxMonth[0]['PM'] . "/30";

        else if ($MaxMonth[0]['PM'] == 12)
            $LastDate = $t . "/" . $MaxMonth[0]['PM'] . "/29";
        else
            $LastDate = '0000-00-00';
		
		$LAST_PAY_DATE = date('Y-m-d', strtotime(DateModules::shamsi_to_miladi($LastDate) . "+1 days"));
		$LAST_PAY_DATE = DateModules::miladi_to_shamsi($LAST_PAY_DATE);

        if ($res1[(count($res1) - 1)]['salary_pay_proc'] == 1) {

            $LastDate = DateModules::miladi_to_shamsi($res1[(count($res1) - 1)]['execute_date']);
            $arr = preg_split('/\//', $LastDate);
            $NewDate = date('Y-m-d', strtotime(DateModules::shamsi_to_miladi($LastDate) . "-1 days"));
            $LastDate = DateModules::miladi_to_shamsi($NewDate);
           
            $arr = preg_split('/\//', $LastDate);
        }

        
        $LD = array();
        //.................................................................................................

        for ($i = 0; $i < count($res1); $i++) {

            if ($LastDate != '0000-00-00' && DateModules::CompareDate($res1[$i]['execute_date'], str_replace("/", "-", DateModules::shamsi_to_miladi($LastDate))) >= 0) {

                $LD[$t]['lastDate'] = $LastDate;
                break;
            }

            $CDate = DateModules::miladi_to_shamsi($res1[$i]['execute_date']);

            $arr2 = preg_split('/\//', $CDate);


            if (intval($arr2[1]) <= intval($MaxMonth[0]['PM'])) {



                $qry = " select t.staff_id,max_execute_date ,SUBSTRING(max_execute_date,1,10) execute_date ,
						SUBSTRING_INDEX(SUBSTRING(max_execute_date,11),'.',1) writ_id,
						SUBSTRING_INDEX(max_execute_date,'.',-1) writ_ver

					from (
							select w.staff_id,max( CONCAT(w.execute_date,w.writ_id,'.',w.writ_ver) ) max_execute_date

							from payment_writs cpw inner join  writs w
																				on  cpw.staff_id = w.staff_id and
																					cpw.writ_id = w.writ_id and
																					cpw.writ_ver = w.writ_ver
							where cpw.pay_year = " . $t . " and cpw.staff_id = " . $SID . " and
							      /*cpw.pay_month = 12 and*/ execute_date <= '" . $res1[$i]['execute_date'] . "'

							group by w.staff_id
						) t
							";

                // پرس و جو بالا هم بایستی union شود با ورژن های قبلی پرداخت تا احکام آنها هم دیده شود .

                $res2 = PdoDataAccess::runquery($qry);

                
                if (count($res2) == 0) {

                    $res2[0]['execute_date'] = '0000-00-00';
                    $res2[0]['writ_id'] = 0;
                    $res2[0]['writ_ver'] = 0;
                }
            } else {

                $res2[0]['execute_date'] = '0000-00-00';
                $res2[0]['writ_id'] = 0;
                $res2[0]['writ_ver'] = 0;
            }

           /* PdoDataAccess::runquery(" insert compare_arrear_writs (staff_id ,current_execute_date , current_writ_id , current_writ_ver, 
															   prev_execute_date , prev_writ_id , prev_writ_ver , arrear_ver , pay_year ) values 
															  (" . $res1[$i]['staff_id'] . ",'" . $res1[$i]['execute_date'] . "'," . $res1[$i]['writ_id'] . "," .
                    $res1[$i]['writ_ver'] . ",'" . $res2[0]['execute_date'] . "'," . $res2[0]['writ_id'] . "," .
                    $res2[0]['writ_ver'] . "," . $res1[$i]['arrear_ver'] . "," . $t . " ) ");
			*/
            if ($res1[$i]['writ_id'] == $res2[0]['writ_id'] && $res1[$i]['writ_ver'] == $res2[0]['writ_ver'])
                continue;
            
            $writNo++;
        }

        //..................اگر برج 12 در دیون محاسبه می شود ولی در قبلی نبوده است بایستی به جدول compare افزوده شود..............................
    /*   if ($res1[(count($res1) - 1)]['salary_pay_proc'] != 1 && $MaxMonth[0]['PM'] < 12) {



            $qry = " select staff_id , execute_date , writ_id , writ_ver 
                from writs 
                 where  staff_id = " . $SID . " and salary_pay_proc = 1 and 
                        execute_date >= '" . DateModules::shamsi_to_miladi($t . '/01/01') . "' and 
                        execute_date <= '" . DateModules::shamsi_to_miladi($t . '/12/29') . "' and 
                        issue_date >= '" . $res1[$i - 1]['execute_date'] . "' and state = 3 ";

            $res3 = PdoDataAccess::runquery($qry);

            PdoDataAccess::runquery(" insert compare_arrear_writs ( staff_id ,current_execute_date , current_writ_id , current_writ_ver, 
                                                        prev_execute_date , prev_writ_id , prev_writ_ver , arrear_ver , pay_year ) values 
                                                      (" . $SID . ",'" . $res1[$i - 1]['execute_date'] . "'," . $res1[$i - 1]['writ_id'] . "," .
                    $res1[$i - 1]['writ_ver'] . ",'" . $res3[0]['execute_date'] . "'," . $res3[0]['writ_id'] . "," .
                    $res3[0]['writ_ver'] . "," . $res1[$i - 1]['arrear_ver'] . "," . $t . " ) ");
        } */

               
//....................we need this part......................
        $res = PdoDataAccess::runquery(" select * 
										from compare_arrear_writs 
											where staff_id = " . $SID . " and pay_year =" . $t . " and arrear_ver = " . $MaxVer[0]['MV']);



        for ($i = 0; $i < count($res); $i++) {
            $writsWhereClause.=' (wsi.writ_id=' . $res[$i]['current_writ_id'] . ' AND wsi.writ_ver=' . $res[$i]['current_writ_ver'] . ' AND wsi.staff_id=' . $res[$i]['staff_id'] . ') OR 
							 (wsi.writ_id=' . $res[$i]['prev_writ_id'] . ' AND wsi.writ_ver=' . $res[$i]['prev_writ_ver'] . ' AND wsi.staff_id=' . $res[$i]['staff_id'] . ' ) OR  ';

            $PrewritsWhereClause .= ' (wsi.writ_id=' . $res[$i]['prev_writ_id'] . ' AND wsi.writ_ver=' . $res[$i]['prev_writ_ver'] . ' AND wsi.staff_id=' . $res[$i]['staff_id'] . ' ) OR  ';

            $CurrwritsWhereClause .= ' (wsi.writ_id=' . $res[$i]['current_writ_id'] . ' AND wsi.writ_ver=' . $res[$i]['current_writ_ver'] . ' AND wsi.staff_id=' . $res[$i]['staff_id'] . ') OR ';
        }
		
//.............................................................
    } 
		
	$writsWhereClause = substr($writsWhereClause, 0, strlen($writsWhereClause) - 4);
    $PrewritsWhereClause = substr($PrewritsWhereClause, 0, strlen($PrewritsWhereClause) - 4);
    $CurrwritsWhereClause = substr($CurrwritsWhereClause, 0, strlen($CurrwritsWhereClause) - 4);

    $ResITM2 = PdoDataAccess::runquery(" select distinct wsi.salary_item_type_id  , sit.print_title ,sit.month_length_effect
											 from writ_salary_items wsi 
																inner join salary_item_types sit 
																		on  wsi.salary_item_type_id  = sit.salary_item_type_id
													where " . $writsWhereClause);


//..............................................................................
	$ResITM = array();
    $diffVal = 0;
    for ($j = 0; $j < count($ResITM2); $j++) {

        $res6 = PdoDataAccess::runquery(" select wsi.salary_item_type_id , sit.print_title , sum(wsi.value) CurrVal 
											 from writ_salary_items wsi 
																inner join salary_item_types sit 
																		on  wsi.salary_item_type_id  = sit.salary_item_type_id
													where wsi.must_pay = 1 and (" . $CurrwritsWhereClause . ") and wsi.salary_item_type_id =" . $ResITM2[$j]['salary_item_type_id']);


        $res7 = PdoDataAccess::runquery(" select wsi.salary_item_type_id , sit.print_title , sum(wsi.value) PreVal 
											 from writ_salary_items wsi 
																inner join salary_item_types sit 
																		on  wsi.salary_item_type_id  = sit.salary_item_type_id
													where  wsi.must_pay = 1 and (" . $PrewritsWhereClause . ") and wsi.salary_item_type_id =" . $ResITM2[$j]['salary_item_type_id']);

        $diffVal = $res6[0]['CurrVal'] - $res7[0]['PreVal'];

        if ($diffVal != 0) {

            $diffVal = 0;
            $ResITM[] = array('salary_item_type_id' => $ResITM2[$j]['salary_item_type_id'],
                'print_title' => $ResITM2[$j]['print_title'],
                'month_length_effect' => $ResITM2[$j]['month_length_effect']);
        } else {
            //.................................... چنانچه قلمی در حکم فعلی حذف شده باشد.................


            for ($i = 0; $i < count($res); $i++) {

                $res6 = PdoDataAccess::runquery(' select wsi.salary_item_type_id , sit.print_title , sum(wsi.value) CurrVal 
                                                        from writ_salary_items wsi 
                                                                    inner join salary_item_types sit 
                                                                                    on  wsi.salary_item_type_id  = sit.salary_item_type_id
                                                                    where wsi.must_pay = 1 and 
                                                                         (wsi.writ_id=' . $res[$i]['prev_writ_id'] . ' AND 
                                                                          wsi.writ_ver=' . $res[$i]['prev_writ_ver'] . ' AND
                                                                          wsi.staff_id=' . $res[$i]['staff_id'] . ' ) and 
                                                                          wsi.salary_item_type_id =' . $ResITM2[$j]['salary_item_type_id']);


                $res7 = PdoDataAccess::runquery(' select wsi.salary_item_type_id , sit.print_title , sum(wsi.value) PreVal 
                                                        from writ_salary_items wsi 
                                                                inner join salary_item_types sit 
                                                                        on  wsi.salary_item_type_id  = sit.salary_item_type_id
                                                        where  wsi.must_pay = 1 and 
                                                              (wsi.writ_id=' . $res[$i]['current_writ_id'] . ' AND 
                                                               wsi.writ_ver=' . $res[$i]['current_writ_ver'] . ' AND 
                                                               wsi.staff_id=' . $res[$i]['staff_id'] . ') and 
                                                               wsi.salary_item_type_id =' . $ResITM2[$j]['salary_item_type_id']);


                if (( count($res6) != count($res7) ) || $res6[0]['CurrVal'] != $res7[0]['PreVal']) {
                    if (!in_array($ResITM2[$j]['salary_item_type_id'], $ResITM)) {

                        $ResITM[] = array('salary_item_type_id' => $ResITM2[$j]['salary_item_type_id'],
                            'print_title' => $ResITM2[$j]['print_title'],
                            'month_length_effect' => $ResITM2[$j]['month_length_effect']);
                    }
                }
            }
        }
    }
	
	//..........................................................................
    $TotalsumDiff = $TotalMainVal = $TotalRowTax = $TotalRowIns = $TotalRowRet = $TotalPay = 0;

    for ($t = $_POST['from_pay_year']; $t < ( $_POST['to_pay_year'] + 1 ); $t++) {
        $retCoef = 1;
        $qry = " select max(arrear_ver) MV
					from  corrective_payment_writs 
						where staff_id = " . $SID . " and pay_year = " . $t . "
		       ";
        $MaxVer = PdoDataAccess::runquery($qry);

        if (count($MaxVer) == 0 || $MaxVer[0]['MV'] == 0)
            continue;

        $res = PdoDataAccess::runquery(" select * 
										from compare_arrear_writs 
											where staff_id = " . $SID . " and pay_year =" . $t . " and arrear_ver = " . $MaxVer[0]['MV']);



        $prior_execute_date = $current_execute_date = $prior_writ_type = $current_writ_type = '';
        $current_writ_items = $prior_writ_items = array();

       

        $sdate = "";
        for ($i = 0; $i < count($res); $i++) {
            if ($res[$i]['current_writ_id'] == $res[$i]['prev_writ_id'] && $res[$i]['current_writ_ver'] == $res[$i]['prev_writ_ver'])
                continue;

            
            if (!empty($LD[$t]['lastDate'])) {
                $toDate = $LD[$t]['lastDate'];
            } else {

                if ($LastDate == '0000-00-00')
                    $toDate = $t . "/12/30";
                else
                    $toDate = (( DateModules::CompareDate(str_replace("/", "-", DateModules::shamsi_to_miladi($t . "/12/30")), str_replace("/", "-", DateModules::shamsi_to_miladi($LastDate))) >= 0 ) ? $LastDate : $t . "/12/30" );
            }


           /* if ($sdate != "" && $sdate > '0000-00-00')
                $Row .= "<td>" . $sdate . "</td>";
            else
                $Row .= "<td>" .DateModules::miladi_to_shamsi($res[$i]['current_execute_date']). "</td>";
            */
            if ($res[$i]['current_execute_date'] == $res[$i + 1]['current_execute_date']) {
                //$Row .= "<td>" . DateModules::AddToJDate($toDate, 1) . "</td>";
                $sdate = DateModules::AddToJDate($toDate, 1);
            } /*else if ($sdate != "" && $sdate > '0000-00-00' && empty($res[$i + 1]['current_execute_date']))
                $Row .= "<td>" . $t . "/12/29" . "</td>";
            elseif($res[$i + 1]['current_execute_date'] !='0000-00-00' && DateModules::CompareDate(DateModules::miladi_to_shamsi($res[$i + 1]['current_execute_date']),$LAST_PAY_DATE) == 1  )
            {
                  $Row .= "<td>" . $LAST_PAY_DATE . "</td>";
                
            }
            else
                $Row .= "<td>" . ( empty($res[$i + 1]['current_execute_date']) ? $toDate : DateModules::miladi_to_shamsi($res[$i + 1]['current_execute_date']) ) . "</td>";
*/
            if (!empty($LD[$t]['lastDate'])) {
                $toDate2 = DateModules::shamsi_to_miladi(DateModules::AddToJDate($LD[$t]['lastDate'], 1));
            } else {
                if ($LastDate == '0000-00-00')
                    $toDate2 = DateModules::shamsi_to_miladi(($t + 1) . "/01/01");
                else
                    $toDate2 = ( ( DateModules::CompareDate(str_replace("/", "-", DateModules::shamsi_to_miladi($t . "/12/30")), str_replace("/", "-", DateModules::shamsi_to_miladi($LastDate))) >= 0 ) ? DateModules::AddToJDate($LastDate, 1) : DateModules::shamsi_to_miladi(($t + 1) . "/01/01") );
            }

            $endDate = (empty($res[$i + 1]['current_execute_date']) ? $toDate2 : $res[$i + 1]['current_execute_date'] );
            
            if ($res[$i]['current_execute_date'] == $res[$i + 1]['current_execute_date']) {
                $endDate = $toDate2;
            }



            if ($sdate != "" && $sdate > '0000-00-00' && empty($res[$i + 1]['current_execute_date'])) {

                $endDate = $t + 1 . "/1/01";

                if ($resPerInfo[0]['person_type'] == 3) {

                    $DayNo = round(DateModules::GDateMinusGDate(DateModules::shamsi_to_miladi($endDate), DateModules::shamsi_to_miladi($sdate)));
                    $DayNo2 = calculate_duration($sdate, $endDate);
                } else {

                    $DayNo = calculate_duration($sdate, $endDate);
                }
            } else {

                if ($resPerInfo[0]['person_type'] == 3) {
                    $DayNo = round(DateModules::GDateMinusGDate(DateModules::shamsi_to_miladi($endDate), $res[$i]['current_execute_date']));
                    $DayNo2 = calculate_duration($res[$i]['current_execute_date'], $endDate);
                } else {
                    $DayNo = calculate_duration($res[$i]['current_execute_date'], $endDate);
                }
            }

            if( $res[$i + 1]['current_execute_date'] !='0000-00-00' && 
                DateModules::CompareDate(DateModules::miladi_to_shamsi($res[$i + 1]['current_execute_date']),$LAST_PAY_DATE) == 1  )
            {              
                $DayNo = calculate_duration($res[$i]['current_execute_date'] ,DateModules::shamsi_to_miladi($LAST_PAY_DATE));                
            }

            // $Row .= "<td>" . $DayNo . "</td>";
            $sumDiff = $MainVal = 0;
            $RetInc = $TaxInc = $InsInc = 0;

            for ($j = 0; $j < count($ResITM); $j++) {

                $val1 = get_writSalaryItem_value($res[$i]["current_writ_id"], $res[$i]["current_writ_ver"], $res[$i]["staff_id"], $ResITM[$j]["salary_item_type_id"]);

                $val2 = get_writSalaryItem_value($res[$i]["prev_writ_id"], $res[$i]["prev_writ_ver"], $res[$i]["staff_id"], $ResITM[$j]["salary_item_type_id"]);

                $RetRes = PdoDataAccess::runquery(" select retired_include , tax_include , insure_include 
                                                      from salary_item_types where salary_item_type_id = " . $ResITM[$j]["salary_item_type_id"]);


              //  $Row .= "<td>" . CurrencyModulesclass::toCurrency($val1 - $val2) . (($val1 - $val2) < 0 ? "-&nbsp;" : "" ) . "</td>";
                $sumDiff += ($val1 - $val2);

                if (empty($Itm[$j]['sumVal'])) {
                    $Itm[$j]['sumVal'] = 0;
                    $Itm[$j]['sumCol'] = 0;
                }

                $Itm[$j]['sumVal'] += $val1 - $val2;

                if ($ResITM[$j]["month_length_effect"] == 0 && $resPerInfo[0]['person_type'] == 3) {
                    $Itm[$j]['sumCol'] += round((($val1 - $val2) * $DayNo2 ) / 30);
                    $MainVal+= round((($val1 - $val2) * $DayNo2 ) / 30);
                } elseif ($resPerInfo[0]['person_type'] == 5 && $_SESSION['UserID'] == 'jafarkhani') {

                    // echo $endDate . "---<br>" ; 
                    // echo DateModules::miladi_to_shamsi($res[$i]['current_execute_date'])."***<br>" ; die();

                    $arr3 = preg_split('/\//', DateModules::miladi_to_shamsi($res[$i]['current_execute_date']));
                    $arr4 = preg_split('/\//', DateModules::miladi_to_shamsi($endDate));

                    if (($arr4[1] * 1) == 1 && $arr4[0] == ($arr3[0] + 1 )) {

                        $EP = 12;
                    }
                    else
                        $EP = ($arr4[1] * 1);

                    //.........................

                    $std = $res[$i]['current_execute_date'];
                    $stMonth = $arr3[1] * 1;

                    if ($stMonth < 7)
                        $endt = $arr3[0] . "/" . $arr3[1] . "/31";

                    elseif (6 < $stMonth && $stMonth < 12)
                        $endt = $arr3[0] . "/" . $arr3[1] . "/30";

                    elseif ($stMonth == 12)
                        $endt = $arr3[0] . "/" . $arr3[1] . "/29";

                   
                    for ($k = $arr3[1] * 1; $k < ( $EP + 1 ); $k++) {

                      if (DateModules::CompareDate(DateModules::miladi_to_shamsi($endt),DateModules::miladi_to_shamsi($endDate)) < 1) {
                           
                            $DYNo = round(DateModules::GDateMinusGDate(DateModules::shamsi_to_miladi($endt), $std)) + 1;

                            if ($stMonth < 7)
                                $mNO = 31;
                            elseif ($stMonth > 6 && $stMonth < 12)
                                $mNO = 30;
                            elseif ($stMonth == 12)
                                $mNO = 29;

                            $Itm[$j]['sumCol'] += round((($val1 - $val2) * $DYNo ) / $mNO);

                            $MainVal+= round((($val1 - $val2) * $DYNo ) / $mNO);
$TaxInc += round((($val1 - $val2) * $DYNo ) / $mNO);
                            
                            //........................

                            $std = DateModules::shamsi_to_miladi($arr3[0] . "/" . ($k + 1) . "/01"); 
                          
                            $stMonth = $k+1 ; //$arr3[1] + 1;   
                           
                            if ($stMonth < 7)
                                $endt = $arr3[0] . "/" . $stMonth . "/31";

                            elseif (6 < $stMonth && $stMonth < 12)
                                $endt = $arr3[0] . "/" . $stMonth . "/30";

                            elseif ($stMonth == 12)
                                $endt = $arr3[0] . "/" . $stMonth . "/29";
                        }
                        else {

                            //echo "****";
                           // die();
                            $endt = $endDate;
                            $DYNo = round(DateModules::GDateMinusGDate(DateModules::shamsi_to_miladi($endt), $std));

                            if ($stMonth < 7)
                                $mNO = 31;
                            elseif ($stMonth > 6 && $stMonth < 12)
                                $mNO = 30;
                            elseif ($stMonth == 12)
                                $mNO = 29;

                            $Itm[$j]['sumCol'] += round((($val1 - $val2) * $DYNo ) / $mNO);
                            $MainVal+= round((($val1 - $val2) * $DYNo ) / $mNO);
$TaxInc += round((($val1 - $val2) * $DYNo ) / $mNO);
                            break;
                        }
                    }
                }
                else {
                    $Itm[$j]['sumCol'] += round((($val1 - $val2) * $DayNo ) / 30);
                    $MainVal+= round((($val1 - $val2) * $DayNo ) / 30);
                }

                //...............................			

                
                if ($RetRes[0]['retired_include'] == 1)
                    $RetInc += $val1 - $val2;

                if ($RetRes[0]['tax_include'] == 1 && $resPerInfo[0]['person_type'] != 5 ) {

                    if ($ResITM[$j]["month_length_effect"] == 0 && $resPerInfo[0]['person_type'] == 3)
                        $TaxInc += round((($val1 - $val2) * $DayNo2 ) / 30);

                    else
                        $TaxInc += round((($val1 - $val2) * $DayNo ) / 30);
                }

                if ($RetRes[0]['insure_include'] == 1)
                    $InsInc += $val1 - $val2;
            }
            //$MainVal = round(($sumDiff) * $DayNo / 30);
            //....................مالیات...........

            $qry = " select count(*) cn  
					from Arrear_payment_items 
						where staff_id = " . $SID . " and pay_year = " . $t . " and pay_month  = 12 and salary_item_type_id in (146,147,148,747)";
            $res4 = PdoDataAccess::runquery($qry);

            if ($res4[0]['cn'] > 0) {

//.........................................        

                $qry = " select count(*) cnp
            from payments 
               where staff_id = " . $SID . " and pay_year = " . $t;
                $resE = PdoDataAccess::runquery($qry);

                if ($resE[0]['cnp'] == 0) {

                    $sdatetax = DateModules::shamsi_to_miladi($t . "/12/29");

                    $qry = " SELECT tti.from_value
                FROM staff_tax_history sth
                        inner join tax_tables tt on sth.tax_table_type_id = tt.tax_table_type_id
                        inner join tax_table_items tti on tti.tax_table_id = tt.tax_table_id

                WHERE sth.staff_id = " . $SID . " and 
                    sth.start_date < '" . DateModules::Now() . "' and
                    ( sth.end_date = '0000-00-00' or sth.end_date is null or 
                    sth.end_date > '" . DateModules::Now() . "' ) and
                    tt.from_date < '" . $sdatetax . "' and
                    ( tt.to_date = '0000-00-00' or tt.to_date is null or tt.to_date >= '" . $sdatetax . "'  ) and coeficient = 0.1 ";

                    $resFval = PdoDataAccess::runquery($qry);
                    
                    $TaxInc -= $resFval[0]['from_value'];
                }
                //.........................................

                $RowTax = $TaxInc * 0.1; //round(($TaxInc * $DayNo / 30) * 0.1);
            } else {
                $RowTax = 0;
            }

            //..................................
            $qry = " select count(*) cn  
					from Arrear_payment_items 
						where staff_id = " . $SID . " and pay_year = " . $t . " and pay_month  = 12 and salary_item_type_id in (149 , 150)";
            $res3 = PdoDataAccess::runquery($qry);

            if ($res3[0]['cn'] > 0) {

                $RowRet = round((($RetInc * $DayNo / 30) * 0.09));
            } else {
                $RowRet = 0;
            }

            //.......................بیمه تامین اجتماعی...........
            $qry = " select count(*) cn  
					from Arrear_payment_items 
						where staff_id = " . $SID . " and pay_year = " . $t . " and pay_month  = 12 and salary_item_type_id in (144,145,744,9920)";
            $res5 = PdoDataAccess::runquery($qry);

            if ($res5[0]['cn'] > 0) {

                $RowInsure = round(($InsInc * $DayNo / 30 ) * 0.07);
            } else {
                $RowInsure = 0;
            }

            //.....................................

            if ($resPerInfo[0]['last_retired_pay'] != NULL && $resPerInfo[0]['last_retired_pay'] != '0000-00-00' &&
                    DateModules::CompareDate($resPerInfo[0]['last_retired_pay'], DateModules::shamsi_to_miladi($t . "/01/01")) == -1) {
                $RowRet = $retCoef = 0;
            }

            $TotalsumDiff += $sumDiff;
            $TotalMainVal += $MainVal;
            $TotalRowTax += $RowTax;
            $TotalRowRet += $RowRet;
            $TotalRowIns += $RowInsure;

            $TotalPay += $MainVal - ($RowTax + $RowRet + $RowInsure);

           /* $Row .= "<td>" . ( ($sumDiff < 0 ) ? CurrencyModulesclass::toCurrency($sumDiff) . "-" : CurrencyModulesclass::toCurrency($sumDiff)) . "</td>
				 <td>" . (($MainVal < 0 ) ? CurrencyModulesclass::toCurrency($MainVal) . "-" : CurrencyModulesclass::toCurrency($MainVal)) . "</td><td>" .
                    (($RowTax < 0 ) ? CurrencyModulesclass::toCurrency($RowTax) . "-" : CurrencyModulesclass::toCurrency($RowTax)) . "</td><td>" .
                    (($RowRet < 0 ) ? CurrencyModulesclass::toCurrency($RowRet) . "-" : CurrencyModulesclass::toCurrency($RowRet)) . "</td><td>" .
                    (($RowInsure < 0 ) ? CurrencyModulesclass::toCurrency($RowInsure) . "-" : CurrencyModulesclass::toCurrency($RowInsure)) . "</td><td>" .
                    ((($MainVal - ($RowTax + $RowRet + $RowInsure )) < 0 ) ? CurrencyModulesclass::toCurrency(($MainVal - ($RowTax + $RowRet + $RowInsure))) . "-" : CurrencyModulesclass::toCurrency(($MainVal - ($RowTax + $RowRet + $RowInsure)))) . "</td></tr>"; */
        }

     //   $Row .= "</tr>";
    }
	
	
								
	}


}
?>