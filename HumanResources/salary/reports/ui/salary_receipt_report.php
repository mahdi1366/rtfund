<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	92.07
//---------------------------
require_once("../../../header.inc.php");
 //ini_set("display_errors", "On");
if(!isset($_REQUEST["show"]))   
	require_once '../js/salary_receipt_report.js.php';
	require_once "ReportGenerator.class.php"; 
	
	
 $whr = " (1=1) " ;  

if(isset($_REQUEST["show"]))
{

	?>
	<html dir='rtl'>
		<head>
			<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
			<link rel=stylesheet href="/HumanResources/css/payment.css">
		</head>
		<body style="margin-top:0">
			<br><br>
		</body>
	</html>
	<?

	$whereParam = array();
       
	if(!empty($_POST["from_pay_year"]))
	{
		$whr .= " AND pa.pay_year >= :fpy ";   		
		$whereParam[":fpy"] = $_POST["from_pay_year"];
	}
	if(!empty($_POST["to_pay_year"]))
	{
		$whr .= " AND pa.pay_year <=:tpy";             
		$whereParam[":tpy"] = $_POST["to_pay_year"];
	}
	
	if(!empty($_POST["from_pay_month"]))
	{
		$whr .= " AND pa.pay_month >= :fpm ";   		
		$whereParam[":fpm"] = $_POST["from_pay_month"];
	}
	if(!empty($_POST["to_pay_month"]))
	{
		$whr .= " AND pa.pay_month<=:tpm";             
		$whereParam[":tpm"] = $_POST["to_pay_month"];
	}

	if(!empty($_POST["cost_center_id"]))
	{
		$whr .= " AND pai.cost_center_id = :ccid ";   		
		$whereParam[":ccid"] = $_POST["cost_center_id"] ;
	}
	if(!empty($_POST["PersonType"]))
	{
		if($_POST["PersonType"]==102){
			$whr .= " AND s.person_type in ( 1,2,3 ) ";   					
		}
		else{
			$whr .= " AND s.person_type = :pt ";   		
			$whereParam[":pt"] = $_POST["PersonType"] ; 
		
		}
	}

	if(!empty($_POST["ouid"]))
	{
		$whr .= " AND ( w.ouid=:ouid OR s.UnitCode=:ouid ) ";
		$whereParam[":ouid"] = $_POST["ouid"]; 		
	}
	
	if(!empty($_POST["SID"]))
	{
		$whr .= " AND pa.staff_id=:sid";
		$whereParam[":sid"] = $_POST["SID"]; 		
	}
	
	if(!empty($_POST["PayType"]))
	{
		$whr .= " AND pa.payment_type=:payty";
		$whereParam[":payty"] = $_POST["PayType"]; 
                
		if($_POST["PayType"] == 9) {
			$pw = "Arrear_payments";
			$pwi = "Arrear_payment_items"; 
		}
		else {
			$pw = "payments";
			$pwi = "payment_items"; 			
			}
	}
		

//..................................................................................

$query = " select	    ps.pfname,
						ps.plname,
						s.person_type,
						sit.effect_type,
						sit.print_title salary_item_title,
						c.cost_center_id,
						c.title cost_center_title,
						pai.pay_value,
						pai.get_value,
						(pai.diff_pay_value * diff_value_coef) diff_pay_value,
						(pai.diff_get_value * diff_value_coef) diff_get_value,
						pai.salary_item_type_id,
						pai.param3,
						pai.param2,
						pa.account_no,
						o.ptitle,
						s.tafsili_id,
						s.staff_id,
						b.bank_id,
						b.name,
						pa.message,
						pa.pay_year,
						pa.pay_month,
						pa.payment_type,
						BI.title month_title ,
						CASE pai.param1
							WHEN 'LOAN' THEN  pai.param4
						END loan_remainder,
						CASE pai.param1
							WHEN 'FRACTION' THEN  pai.param4
						END frac_remainder

			from hrmstotal.".$pwi." pai
										  INNER JOIN hrmstotal.".$pw." pa
												ON (pa.pay_year = pai.pay_year AND
													pa.pay_month = pai.pay_month AND
													pa.staff_id = pai.staff_id AND
													pa.payment_type = pai.payment_type )
										  INNER JOIN salary_item_types sit
												ON (pai.salary_item_type_id = sit.salary_item_type_id)
										  LEFT JOIN writs w
												ON ((pa.writ_id = w.writ_id) AND (pa.writ_ver = w.writ_ver) AND (pa.staff_id = w.staff_id) )
										  LEFT JOIN org_new_units o
												ON (w.ouid = o.ouid)
										  INNER JOIN staff s
												ON (pa.staff_id = s.staff_id)
										  INNER JOIN persons ps
												ON (s.PersonID = ps.PersonID)
										  INNER JOIN cost_centers c
												ON (pai.cost_center_id = c.cost_center_id)
										  LEFT OUTER JOIN banks b
												ON (s.bank_id = b.bank_id)
										  INNER JOIN hrmstotal.Basic_Info BI
												ON BI.typeid = 41 and BI.infoid = pa.pay_month

			where $whr  AND s.person_type in(" . manage_access::getValidPersonTypes() . ")

			order by c.cost_center_id,
					 pa.staff_id,
					 pa.pay_year,
					 pa.pay_month,					 
					 pa.payment_type,
					 ps.plname,
					 ps.pfname,
					 sit.print_order

		";

	
	$dt = PdoDataAccess::runquery($query, $whereParam);
	
	if(count($dt) == 0 /*|| $_SESSION['UserID'] == 'jafarkhani' */)
	{	//echo count($dt) ; die(); PdoDataAccess::GetLatestQueryString() ; die() ;
		echo "<center><br><font style='color:red;font-weight:bold:bold;font-size:20px' > گزارش هیچ نتیجه ای در بر ندارد.</font></center>" ; 
		die() ; 
	}
	
	$staffID = "";
	$payYear = "";
	$payMonth = "";
	$paymentType = "";
	
	
	for($i=0;$i<count($dt);$i++){
	
	if($staffID != $dt[$i]['staff_id'] || 
	   $payYear!= $dt[$i]['pay_year'] || 
	   $payMonth!= $dt[$i]['pay_month'] ||  
	   $paymentType!= $dt[$i]['payment_type']) {
		
		//................... چاپ فیش حقوقی ................................
		if($i > 0){
			
			$loop_limit = max(17, count($pays['title']), count($gets['title']));

			$pay_sum = 0 ;
			$pay_diff_sum = 0 ;
			$get_sum = 0 ;
			$get_diff_sum = 0 ;
			$report = "";
			
			for($j=0; $j < $loop_limit; $j++) {
					$report .= "<tr>";
					if( $j < count($pays['title']) ){
						$report .=  "<td   class='payment_report_data_custom_noborder' width=5% >".($j + 1)."</td>";
						$report .=  "<td   class='payment_report_data_custom_noborder' width=20% >".$pays['title'][$j]."</td>";
						$report .=  "<td   class='payment_report_data_custom_noborder' width=10% >".CurrencyModulesclass::toCurrency($pays['value'][$j])."</td>";
						$pay_sum += $pays['value'][$j];
						$report .= "<td    class='payment_report_data_custom_noborder' width=10% >".CurrencyModulesclass::toCurrency($pays['diff_value'][$j]) ;
						$report .=  ($pays['diff_value'][$j] < 0 ) ? "- </td>" : "</td>" ; 
						$pay_diff_sum += $pays['diff_value'][$j];
						if($pays['param3'][$j] != null)
							$report .= "<td  class='payment_report_data_custom_noborder'   width=3% >".$pays['param3'][$j]."</td>";
						else  $report .= "<td  class='payment_report_data_custom_noborder'  width=3% >"."&nbsp;"."</td>";
					}
					else {
						$report .=  "<td   class='payment_report_data_custom_noborder' width=5% >"."&nbsp;"."</td>";
						$report .=  "<td   class='payment_report_data_custom_noborder' width=20% >"."&nbsp;"."</td>";
						$report .=  "<td   class='payment_report_data_custom_noborder' width=10% >"."&nbsp;"."</td>";
						$report .=  "<td   class='payment_report_data_custom_noborder' width=10% >"."&nbsp;"."</td>";
						$report .=  "<td   class='payment_report_data_custom_noborder' width=3% >"."&nbsp;"."</td>";
					}
					if( $j < count($gets['title']) ){
						$report .= "<td   class='payment_report_data_custom_noborder' width=20% >".$gets['title'][$j]."</td>";						
						$report .= "<td   class='payment_report_data_custom_noborder' width=10% >".CurrencyModulesclass::toCurrency($gets['value'][$j]) ;
						$report .=  ($gets['value'][$j] < 0 ) ? "- </td>" : "</td>" ; 
						$get_sum += $gets['value'][$j];
						$report .= "<td   class='payment_report_data_custom_noborder' width=10% >" . CurrencyModulesclass::toCurrency($gets['diff_value'][$j]); 
						$report .=  ($gets['diff_value'][$j] < 0 ) ? "- </td>" : "</td>";
						
						$get_diff_sum += $gets['diff_value'][$j];
						if($gets['loan_remainder'][$j] != null)
							$report .=  "<td   class='payment_report_data_custom_noborder' width=10% >".CurrencyModulesclass::toCurrency($gets['loan_remainder'][$j])."</td>";
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
					'<!--plname-->' => $dt[$i-1]['plname'] ,
					'<!--account_no-->' => $dt[$i-1]['account_no'] ,
					'<!--staff_id-->' => $dt[$i-1]['staff_id'] ,
					'<!--pfname-->' => $dt[$i-1]['pfname']  ,
					'<!--name-->' => $dt[$i-1]['name'] ,
					'<!--cost_center_title-->' => $dt[$i-1]['cost_center_title'],
					'<!--month_title-->' => $dt[$i-1]['month_title'],
					'<!--pay_year-->' => $dt[$i-1]['pay_year'],
					'<!--total_pay_diffpay-->' => CurrencyModulesclass::toCurrency(($pay_sum + $pay_diff_sum)),
					'<!--total_pay-->' => CurrencyModulesclass::toCurrency($pay_sum) ,
					'<!--total_diffpay-->' => ($pay_diff_sum < 0 ) ? CurrencyModulesclass::toCurrency($pay_diff_sum)."-&nbsp;" : CurrencyModulesclass::toCurrency($pay_diff_sum) ,
					'<!--total_get_diff-->' => (($get_sum + $get_diff_sum) < 0 ) ? CurrencyModulesclass::toCurrency(($get_sum + $get_diff_sum))."-&nbsp;" : CurrencyModulesclass::toCurrency(($get_sum + $get_diff_sum)) ,
					'<!--total_get-->' => CurrencyModulesclass::toCurrency($get_sum) ,
					'<!--total_diffget-->' => ($get_diff_sum < 0) ? CurrencyModulesclass::toCurrency($get_diff_sum )."-&nbsp;" : CurrencyModulesclass::toCurrency($get_diff_sum )  ,
					'<!--total-->'	=> (($pay_sum + $pay_diff_sum - $get_sum - $get_diff_sum) < 0 )  ? CurrencyModulesclass::toCurrency(($pay_sum + $pay_diff_sum - $get_sum - $get_diff_sum))."-&nbsp;" : CurrencyModulesclass::toCurrency(($pay_sum + $pay_diff_sum - $get_sum - $get_diff_sum)) ,
					'<!--str_total-->' =>	CurrencyModulesclass::CurrencyToString(($pay_sum + $pay_diff_sum - $get_sum - $get_diff_sum)),
					'<!--message-->'	=>  $dt[$i-1]['message'] );

			$content = file_get_contents("../../payment/ui/salary_receipt_print.htm");
			$content = str_replace(array_keys($tags), array_values($tags), $content);

			echo $content;	
			echo '<br><br>' ; 
		}
						
		$staffID = $dt[$i]['staff_id'];
		$payYear = $dt[$i]['pay_year'];
		$payMonth = $dt[$i]['pay_month'];
		$paymentType = $dt[$i]['payment_type'];
	
		$pays['value']          = array();
    	$gets['value']          = array();
    	$pays['diff_value']     = array();
    	$gets['diff_value']     = array();
    	$pays['title']          = array();
    	$gets['title']          = array();
    	$pays['param3']         = array();
		$gets['loan_remainder'] = array();
    	$gets['frac_remainder'] = array();

		$query = " select person_type from hrmstotal.staff where staff_id = :sid " ;
		$WP[":sid"] = $_POST["SID"]; 
		$pt = PdoDataAccess::runquery($query, $WP);

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
						
	   }
	
	//..........................
	//for($i=0;$i<count($dt);$i++){
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
	//}
	
	}
	
	if($i== count($dt)){
		
			$loop_limit = max(17, count($pays['title']), count($gets['title']));

			$pay_sum = 0 ;
			$pay_diff_sum = 0 ;
			$get_sum = 0 ;
			$get_diff_sum = 0 ;
			$report = "";
			
			for($j=0; $j < $loop_limit; $j++) {
					$report .= "<tr>";
					if( $j < count($pays['title']) ){
						$report .=  "<td   class='payment_report_data_custom_noborder' width=5% >".($j + 1)."</td>";
						$report .=  "<td   class='payment_report_data_custom_noborder' width=20% >".$pays['title'][$j]."</td>";
						$report .=  "<td   class='payment_report_data_custom_noborder' width=10% >".CurrencyModulesclass::toCurrency($pays['value'][$j])."</td>";
						$pay_sum += $pays['value'][$j];
						$report .= "<td    class='payment_report_data_custom_noborder' width=10% >".CurrencyModulesclass::toCurrency($pays['diff_value'][$j]);
						$report .=  ($pays['diff_value'][$j] < 0 ) ? "- </td>" : "</td>" ; 
						$pay_diff_sum += $pays['diff_value'][$j];
						if($pays['param3'][$j] != null)
							$report .= "<td  class='payment_report_data_custom_noborder'   width=3% >".$pays['param3'][$j]."</td>";
						else  $report .= "<td  class='payment_report_data_custom_noborder'  width=3% >"."&nbsp;"."</td>";
					}
					else {
						$report .=  "<td   class='payment_report_data_custom_noborder' width=5% >"."&nbsp;"."</td>";
						$report .=  "<td   class='payment_report_data_custom_noborder' width=20% >"."&nbsp;"."</td>";
						$report .=  "<td   class='payment_report_data_custom_noborder' width=10% >"."&nbsp;"."</td>";
						$report .=  "<td   class='payment_report_data_custom_noborder' width=10% >"."&nbsp;"."</td>";
						$report .=  "<td   class='payment_report_data_custom_noborder' width=3% >"."&nbsp;"."</td>";
					}
					if( $j < count($gets['title']) ){
						$report .= "<td   class='payment_report_data_custom_noborder' width=20% >".$gets['title'][$j]."</td>";
						$report .= "<td   class='payment_report_data_custom_noborder' width=10% >".CurrencyModulesclass::toCurrency($gets['value'][$j])."</td>";
						$get_sum += $gets['value'][$j];
						$report .= "<td   class='payment_report_data_custom_noborder' width=10% >".CurrencyModulesclass::toCurrency($gets['diff_value'][$j]);
						$report .=  ($gets['diff_value'][$j] < 0 ) ? "- </td>" : "</td>" ; 
						$get_diff_sum += $gets['diff_value'][$j];
						if($gets['loan_remainder'][$j] != null)
							$report .=  "<td   class='payment_report_data_custom_noborder' width=10% >".CurrencyModulesclass::toCurrency($gets['loan_remainder'][$j])."</td>";
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
					'<!--plname-->' => $dt[$i-1]['plname'] ,
					'<!--account_no-->' => $dt[$i-1]['account_no'] ,
					'<!--staff_id-->' => $dt[$i-1]['staff_id'] ,
					'<!--pfname-->' => $dt[$i-1]['pfname']  ,
					'<!--name-->' => $dt[$i-1]['name'] ,
					'<!--cost_center_title-->' => $dt[$i-1]['cost_center_title'],
					'<!--month_title-->' => $dt[$i-1]['month_title'],
					'<!--pay_year-->' => $dt[$i-1]['pay_year'],
					'<!--total_pay_diffpay-->' => CurrencyModulesclass::toCurrency(($pay_sum + $pay_diff_sum)),
					'<!--total_pay-->' => CurrencyModulesclass::toCurrency($pay_sum) ,
					'<!--total_diffpay-->' =>  ($pay_diff_sum < 0 ) ? CurrencyModulesclass::toCurrency($pay_diff_sum)."-&nbsp;" : CurrencyModulesclass::toCurrency($pay_diff_sum) ,
					'<!--total_get_diff-->' => (($get_sum + $get_diff_sum) < 0 ) ? CurrencyModulesclass::toCurrency(($get_sum + $get_diff_sum))."-&nbsp;" : CurrencyModulesclass::toCurrency(($get_sum + $get_diff_sum)) ,
					'<!--total_get-->' => CurrencyModulesclass::toCurrency($get_sum) ,
					'<!--total_diffget-->' => ($get_diff_sum < 0) ? CurrencyModulesclass::toCurrency($get_diff_sum )."-&nbsp;" : CurrencyModulesclass::toCurrency($get_diff_sum )  ,					
					'<!--total-->'	=> (($pay_sum + $pay_diff_sum - $get_sum - $get_diff_sum) < 0 )  ? CurrencyModulesclass::toCurrency(($pay_sum + $pay_diff_sum - $get_sum - $get_diff_sum))."-&nbsp;" : CurrencyModulesclass::toCurrency(($pay_sum + $pay_diff_sum - $get_sum - $get_diff_sum)) ,
					'<!--str_total-->' =>	CurrencyModulesclass::CurrencyToString(($pay_sum + $pay_diff_sum - $get_sum - $get_diff_sum)),
					'<!--message-->'	=>  $dt[$i-1]['message'] );

			$content = file_get_contents("../../payment/ui/salary_receipt_print.htm");
			$content = str_replace(array_keys($tags), array_values($tags), $content);

			echo $content;	
			
			}
?>

<style>
		.reportGenerator {border-collapse: collapse;border: 1px solid black;font-family: tahoma;font-size: 8pt;
						  text-align: center;width: 50%;padding: 2px;}
		.reportGenerator .header {color: white;font-weight: bold;background-color:#3F5F96}
		.reportGenerator td {border: 1px solid #555555;height: 20px;}
</style>
<?		
			
}

?>
<form id="mainForm">
    <center>
        <div id="mainpanel"></div>
    </center>    
</form>