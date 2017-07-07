<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	95.10
//---------------------------
require_once '../../../header.inc.php';

function salary_receipt_list()
{
	$query = " select	ps.pfname, ps.plname,
        s.person_type, sit.effect_type,
        sit.print_title salary_item_title,
        pai.pay_value,
        pai.get_value, (pai.diff_pay_value * diff_value_coef) diff_pay_value,
       (pai.diff_get_value * diff_value_coef) diff_get_value,
        pai.salary_item_type_id, pai.param3, pai.param2, pa.account_no,
        s.staff_id, b.bank_id, b.name, pa.message, pa.pay_year, pa.pay_month, BI.InfoDesc month_title ,
        CASE pai.param1 WHEN 'LOAN' THEN pai.param4 END loan_remainder,
        CASE pai.param1 WHEN 'FRACTION' THEN pai.param4 END frac_remainder

		from HRM_payment_items pai

     INNER JOIN HRM_payments pa ON (pa.pay_year = pai.pay_year AND pa.pay_month = pai.pay_month AND
                                          pa.staff_id = pai.staff_id AND pa.payment_type = pai.payment_type )

     INNER JOIN HRM_salary_item_types sit ON (pai.salary_item_type_id = sit.salary_item_type_id)
     LEFT JOIN HRM_writs w ON ((pa.writ_id = w.writ_id) AND (pa.writ_ver = w.writ_ver) AND (pa.staff_id = w.staff_id) )
     INNER JOIN HRM_staff s ON (pa.staff_id = s.staff_id)
     INNER JOIN HRM_persons ps ON (s.PersonID = ps.PersonID)
     LEFT OUTER JOIN HRM_banks b ON (s.bank_id = b.bank_id)
     INNER JOIN BaseInfo BI ON BI.typeid = 78 and BI.infoid = pa.pay_month

			where pa.pay_year = :py and pa.pay_month =:pm and pa.staff_id =:st and pa.payment_type = :pt

			order by 
					 pa.pay_year,
					 pa.pay_month,
					 ps.plname,
					 ps.pfname,
					 sit.print_order

		";

	$whereParam[":py"] = $_GET['pay_year'] ;
	$whereParam[":pm"] = $_GET['pay_month'] ;
	$whereParam[":st"] = $_GET['staff_id'] ;
	$whereParam[":pt"] = $_GET['payment_type'] ;

	$dt = PdoDataAccess::runquery($query, $whereParam);

//	echo PdoDataAccess::GetLatestQueryString() ; die() ; 

	return $dt ; 
	
}

function generateReport()
{
	
	$dt = salary_receipt_list();
	if(count($dt) == 0)
	{
		echo "گزارش هیچ نتیجه ایی در بر ندارد.";
		return;
	}

		$pays['value']          = array();
    	$gets['value']          = array();
    	$pays['diff_value']     = array();
    	$gets['diff_value']     = array();
    	$pays['title']          = array();
    	$gets['title']          = array();
    	$pays['param3']         = array();
		$gets['loan_remainder'] = array();
    	$gets['frac_remainder'] = array();

	$query = " select person_type from staff where staff_id = :sid " ;
	$whereParam[':sid'] = $_GET['staff_id'];
	$pt = PdoDataAccess::runquery($query, $whereParam);
	
	
	if($pt[0]['person_type'] != HR_CONTRACT ){
		$SIT_STAFF_EXTRA_WORK = SIT_EXTRA_WORK ;
		$SIT_STAFF_HORTATIVE_EXTRA_WORK = SIT_STAFF_HORTATIVE_EXTRA_WORK;
		$SIT_WORKER_EXTRA_WORK = SIT_WORKER_EXTRA_WORK;
		$SIT_WORKER_HORTATIVE_EXTRA_WORK  = SIT_WORKER_HORTATIVE_EXTRA_WORK;

	}
	else {
		$SIT_STAFF_EXTRA_WORK = SIT5_STAFF_EXTRA_WORK;
		$SIT_STAFF_HORTATIVE_EXTRA_WORK = SIT5_STAFF_HORTATIVE_EXTRA_WORK;
		$SIT_WORKER_EXTRA_WORK = SIT5_WORKER_EXTRA_WORK;
		$SIT_WORKER_HORTATIVE_EXTRA_WORK  = SIT5_WORKER_HORTATIVE_EXTRA_WORK;
	}
	
	
	//..........................
	for($i=0;$i<count($dt);$i++){
		if( $dt[$i]['effect_type'] == BENEFIT &&
		   ($dt[$i]['pay_value']!= 0 || $dt[$i]['effect_type']!= 0 )){
		   
				array_push($pays['value'], $dt[$i]['pay_value']);
				array_push($pays['diff_value'], $dt[$i]['diff_pay_value']);
				array_push($pays['title'], $dt[$i]['salary_item_title']);
				
				if( $dt[$i]['salary_item_type_id'] == $SIT_STAFF_EXTRA_WORK ||
					$dt[$i]['salary_item_type_id'] == $SIT_STAFF_HORTATIVE_EXTRA_WORK) {
					array_push($pays['param3'], $dt[$i]['param3']);
				} elseif ( $dt[$i]['salary_item_type_id'] == $SIT_WORKER_EXTRA_WORK ||
						   $dt[$i]['salary_item_type_id'] == $SIT_WORKER_HORTATIVE_EXTRA_WORK ) {
					array_push($pays['param3'], $dt[$i]['param2']);
				} else {
					array_push($pays['param3'], NULL);
				}

		}else if($dt[$i]['get_value'] != 0 || $dt[$i]['diff_get_value'] != 0){

				 array_push($gets['value'], $dt[$i]['get_value']);
	    		 array_push($gets['diff_value'],$dt[$i]['diff_get_value']);
	    		 array_push($gets['title'], $dt[$i]['salary_item_title']);
	    		 array_push($gets['loan_remainder'],$dt[$i]['loan_remainder']);
	    		 array_push($gets['frac_remainder'], $dt[$i]['frac_remainder']);
				 
    	}
	}
	$loop_limit = max(17, count($pays['title']), count($gets['title']));

	$pay_sum = 0 ;
	$pay_diff_sum = 0 ;
	$get_sum = 0 ;
	$get_diff_sum = 0 ;
	$report = "";

	for($i=0; $i < $loop_limit; $i++) {
		$report .= "<tr>";
		    if( $i < count($pays['title']) ){
			    $report .=  "<td   class='payment_report_data_custom_noborder' width=5% >".($i + 1)."</td>";
			    $report .=  "<td   class='payment_report_data_custom_noborder' width=20% >".$pays['title'][$i]."</td>";
			    $report .=  "<td   class='payment_report_data_custom_noborder' width=10% >".$pays['value'][$i]."</td>";
			    $pay_sum += $pays['value'][$i];
			    $report .= "<td    class='payment_report_data_custom_noborder' width=10% >".$pays['diff_value'][$i]."</td>";
			    $pay_diff_sum += $pays['diff_value'][$i];
			    if($pays['param3'][$i] != null)
					  $report .= "<td  class='payment_report_data_custom_noborder'   width=3% >".$pays['param3'][$i]."</td>";
			    else  $report .= "<td  class='payment_report_data_custom_noborder'  width=3% >"."&nbsp;"."</td>";
		    }
		    else {
			    $report .=  "<td   class='payment_report_data_custom_noborder' width=5% >"."&nbsp;"."</td>";
			    $report .=  "<td   class='payment_report_data_custom_noborder' width=20% >"."&nbsp;"."</td>";
			    $report .=  "<td   class='payment_report_data_custom_noborder' width=10% >"."&nbsp;"."</td>";
			    $report .=  "<td   class='payment_report_data_custom_noborder' width=10% >"."&nbsp;"."</td>";
			    $report .=  "<td   class='payment_report_data_custom_noborder' width=3% >"."&nbsp;"."</td>";
		    }
		    if( $i < count($gets['title']) ){
			    $report .= "<td   class='payment_report_data_custom_noborder' width=20% >".$gets['title'][$i]."</td>";
			    $report .= "<td   class='payment_report_data_custom_noborder' width=10% >".$gets['value'][$i]."</td>";
			    $get_sum += $gets['value'][$i];
			    $report .= "<td   class='payment_report_data_custom_noborder' width=10% >".$gets['diff_value'][$i]."</td>";
			    $get_diff_sum += $gets['diff_value'][$i];
			    if($gets['loan_remainder'][$i] != null)
					$report .=  "<td   class='payment_report_data_custom_noborder' width=10% >".$gets['loan_remainder'][$i]."</td>";
			    else $report .= "<td   class='payment_report_data_custom_noborder' width=10% >"."&nbsp"."</td>";
					$report .=  "<td   class='payment_report_data_custom_noborder' width=7% >"."&nbsp;"."</td>";
		    }
		    else {
		    	$report .= "<td   class='payment_report_data_custom_noborder' width=20% >"."&nbsp;"."</td>";
			    $report .= "<td   class='payment_report_data_custom_noborder' width=10% >"."&nbsp;"."</td>";
			    $report .= "<td   class='payment_report_data_custom_noborder' width=10% >"."&nbsp;"."</td>";
			    $report .= "<td   class='payment_report_data_custom_noborder' width=10% >"."&nbsp;"."</td>";
			    $report .= "<td   class='payment_report_data_custom_noborder' width=7% >"."&nbsp;"."</td>";
		    }
	     $report .= "</tr>";
	}	
	
	$tags =  array('<!--report-->' => $report ,
				   '<!--plname-->' => $dt[0]['plname'] ,
	               '<!--account_no-->' => $dt[0]['account_no'] ,
				   '<!--staff_id-->' => $dt[0]['staff_id'] ,
				   '<!--pfname-->' => $dt[0]['pfname']  ,
				   '<!--name-->' => $dt[0]['name'] ,
				   '<!--month_title-->' => $dt[0]['month_title'],
				   '<!--pay_year-->' => $dt[0]['pay_year'],
				   '<!--total_pay_diffpay-->' => CurrencyModulesclass::toCurrency(($pay_sum + $pay_diff_sum)),
				   '<!--total_pay-->' => CurrencyModulesclass::toCurrency($pay_sum) ,
				   '<!--total_diffpay-->' => CurrencyModulesclass::toCurrency($pay_diff_sum) ,
				   '<!--total_get_diff-->' => CurrencyModulesclass::toCurrency(($get_sum + $get_diff_sum)),
				   '<!--total_get-->' => CurrencyModulesclass::toCurrency($get_sum) ,
				   '<!--total_diffget-->' => CurrencyModulesclass::toCurrency($get_diff_sum ),
				   '<!--total-->'	=> CurrencyModulesclass::toCurrency(($pay_sum + $pay_diff_sum - $get_sum - $get_diff_sum)),
				   '<!--str_total-->' =>	CurrencyModulesclass::CurrencyToString(($pay_sum + $pay_diff_sum - $get_sum - $get_diff_sum)),
				   '<!--message-->'	=>  $dt[0]['message'] );

	$content = file_get_contents("salary_receipt_print.htm");
	$content = str_replace(array_keys($tags), array_values($tags), $content);

	echo $content;
	
}

?>
<html dir='rtl'>
	<head>
		<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
		<link rel=stylesheet href="/HumanResources/css/payment.css">
	</head>
	<body style="margin-top:0">
		<center>
		<br><br>
		<? generateReport();?>
		</center>
	</body>
</html>
	