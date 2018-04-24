<?php
//---------------------------
// programmer:	B.Mahdipour
// create Date:	93.11
//---------------------------

require_once("../../../header.inc.php");

function getSum($inds,&$vals,$kind = 1) {//kind = 1 means pay and 2 means diff_pay

	reset($vals);

	$inds=preg_split('/,/',$inds);	 

	$sum=0;
	for($t=0;$t<count($inds);$t++){
		if($kind == 1) {
			$sum+=(!empty($vals[$inds[$t]]['value'])) ?  $vals[$inds[$t]]['value'] : 0 ;
		} else {
			$sum+= (!empty($vals[$inds[$t]]['diff_value'])) ? $vals[$inds[$t]]['diff_value'] : 0 ;
		}
	}


	return $sum;
}

function vals_sum($vals,$type,$kind = 1){ //kind = 1 means pay and 2 means diff_pay
	$sum=0;
	reset($vals);
	foreach($vals as $row){
		if($row['type']==$type && $kind == 1)
			$sum+=$row['value'];
		elseif ($row['type']==$type)
			$sum+=(!empty($row['diff_value']) ? $row['diff_value'] : 0 );
	}
	return $sum;
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
		
		
		if(strpos($keys[$i],"chkEmpState_") !== false)
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
	
	if(isset($_POST['PT_10']) && $_POST['PT_10']== 1) 
	   	$WherePT .= ($WherePT !="" ) ?  " ,10 " :  "10" ; 
	
	
	$pament_type = $_POST['PayType'];
	$staffID = (isset($_POST['staff_id']) && $_POST['staff_id'] > 0) ? $_POST['staff_id'] : " " ; 
	
	//..........................................................................
	$query = " DROP TABLE IF EXISTS temp_pure " ; 
	PdoDataAccess::runquery($query) ; 
	
	$query = " CREATE TABLE temp_pure 
			   SELECT  pay_year,
					   SUM(pay_value + diff_pay_value * diff_value_coef) pay_sum,
					   pay_month,
					   staff_id  
			   FROM payment_items 
			   WHERE pay_year = ".$_POST['pay_year']." AND pay_month= ".$_POST['pay_month']."
			   GROUP BY pay_year,pay_month,staff_id " ; 
    PdoDataAccess::runquery($query) ; 
	PdoDataAccess::runquery('ALTER TABLE temp_pure ADD INDEX(staff_id)') ; 
	
	$query = ' SELECT   s.staff_id,						
						pi.salary_item_type_id ,
						sit.effect_type ,
						w.cur_group,
						p.pfname,
						p.plname,
						pi.pay_year,
						pi.pay_month,
						SUM(pi.pay_value) pay_sum,
						SUM(pi.diff_pay_value  * pi.diff_value_coef) diff_pay_sum,
						SUM(pi.get_value + pi.diff_get_value * pi.diff_value_coef) get_sum,
						SUM(pi.param2 + pi.diff_param2) param2,
						SUM(pi.param3 + pi.diff_param3) param3,
						MAX(s.person_type) person_type,
						pay.account_no account_no,
						pi.cost_center_id,
						cc.title cost_center_title,
						sit.full_title sit_title,
						if(sit.effect_type =1 ,  pi.param1  , " " ) param1 
						
			   FROM temp_pure tp
					  INNER JOIN payment_items pi
						   ON(tp.pay_year = pi.pay_year AND tp.pay_month = pi.pay_month AND tp.staff_id = pi.staff_id)
					  LEFT OUTER JOIN payments pay
						   ON (pay.staff_id = pi.staff_id AND
							   pay.pay_year = pi.pay_year AND
							   pay.pay_month = pi.pay_month AND
							   pay.payment_type = pi.payment_type)
					  LEFT OUTER JOIN cost_centers cc
						   ON (pi.cost_center_id = cc.cost_center_id)
					  LEFT OUTER JOIN salary_item_types sit
						   ON (sit.salary_item_type_id = pi.salary_item_type_id)
					  LEFT OUTER JOIN staff s
						   ON (pi.staff_id = s.staff_id)
					  LEFT OUTER JOIN persons p
						   ON (s.PersonID = p.PersonID)
					  LEFT OUTER JOIN writs w
						   ON (w.writ_id = pay.writ_id AND w.writ_ver = pay.writ_ver)
					  LEFT OUTER JOIN hrms.org_new_units o
						   ON (w.ouid=o.ouid) 
			   WHERE   pay.pay_year = '.$_POST['pay_year'].' AND
					   pay.pay_month = '.$_POST['pay_month'].' AND
					   pay.payment_type = '.$pament_type  ; 
			   
		    $query .=  ($WhereCost !="") ? " AND pi.cost_center_id in (".$WhereCost.") " : " "  ; 
			$query .=  ($WhereEmpstate !="") ? " AND w.emp_state in (".$WhereEmpstate.") " : " "  ; 
			$query .=  ($WherePT !="") ? " AND s.person_type in (".$WherePT.") " : " "  ; 
			$query .=  ($staffID  !=" ") ? " AND s.staff_id in (".$staffID .") " : " "  ;  
			
			$query .= ' GROUP BY pi.salary_item_type_id,s.staff_id,p.plname,p.pfname,pay.account_no  ORDER BY p.plname,p.pfname ';
			
			$dt = PdoDataAccess::runquery($query) ; 


//......................................................................................
?>
<style>
		.reportGenerator {border-collapse: collapse;border: 1px solid black;font-family: tahoma;font-size: 8pt;
						  text-align: center;width: 50%;padding: 2px;}
		.reportGenerator .header {color: white;font-weight: bold;background-color:#3865A1} 
		.reportGenerator td {border: 1px solid #555555;height: 20px;}
</style>

<?

$qry = " select bi.Title month_title 
				from  Basic_Info bi 
							where  bi.typeid = 41 AND InfoID = " . $_POST["pay_month"];
		$res = PdoDataAccess::runquery($qry);
		$month = $res[0]['month_title'];

$qry = " select bi.Title pay_title 
				from  Basic_Info bi 
							where  bi.typeid = 50 AND InfoID = " . $pament_type;
$res = PdoDataAccess::runquery($qry);
$PayTitle = $res[0]['pay_title'];

echo '<META http-equiv=Content-Type content="text/html; charset=UTF-8" ><body dir="rtl"><center>';
echo "<center><table style='border:2px groove #9BB1CD;border-collapse:collapse;width:100%'><tr>
		<td width=60px><img src='/HumanResources/img/fum_symbol.jpg'></td>
		<td align='center' style='font-family:b titr;font-size:15px'>گزارش لیست پرداختی &nbsp; ".$PayTitle."&nbsp;".$month." ماه ".
						  $_POST['pay_year']."  </td>				
		<td width='200px' align='center' style='font-family:tahoma;font-size:11px'>تاریخ تهیه گزارش : " 
	. DateModules::shNow() . "<br>";		
echo "</td></tr></table>"; 
echo '<table  class="reportGenerator" style="text-align: right;width:100%!important" cellpadding="4" cellspacing="0">
	  <tr class="header">
		  <td colspan="3">&nbsp;</td> 
		  <td colspan="10" align="center" >حقوق و مزایا</td> 
		  <td colspan="6" align="center" >کسورات</td> 
		  <td colspan="3">&nbsp;</td>
	  </tr>';

echo ' <tr class="header">
		  <td  align="center" rowspan="2" >رديف</td> 
		  <td  align="center" >نام خانوادگي</td> 
		  <td  align="center" >نام</td> 
		  <td align="center">حقوق</td>
		  <td align="center">شغل</td>
		  <td align="center">مطب</td>
		  <td align="center">جذب</td>
		  <td align="center">حق اشعه</td>
		  <td align="center">کارانه</td>
		  <td align="center">عائله</td>
		  <td align="center">سختي کاري</td>
		  <td align="center">تسهيلات</td>
		  <td align="center">تطبيق</td>
		  <td align="center">مبلغ بيمه</td>
		  <td align="center">ماليات</td>
		  <td align="center">بازنشستگي</td>
		  <td align="center">سهام</td>
		  <td align="center">يک ماهه</td>
		  <td align="center">اقساطي</td>
		  <td align="center">جمع حقوق</td>
		  <td align="center">حساب بانکي</td>
		  <td align="center" rowspan="2" >کارکرد</td>		  
	   </tr> '; 	 

echo ' <tr class="header">
		  <td  align="center">شماره شناسایی</td> 
		  <td  align="center" >گروه</td> 
		  <td  align="center" > مديريت</td> 
		  <td align="center">اضافه/تدريس</td>		  
		  <td align="center">مزايا1</td>
		  <td align="center">مزايا2</td>
		  <td align="center">مزايا3</td>
		  <td align="center">مزايا4</td>
		  <td align="center">مزايا5</td>
		  <td align="center">10%</td>
		  <td align="center">مسکن</td>
		  <td align="center">تفاوت حقوق</td>
		  <td align="center">مقرري</td>
		  <td align="center">دريافتي</td>
		  <td align="center">بدهي1</td>
		  <td align="center">بدهي2</td>
		  <td align="center">بدهي3</td>
		  <td align="center">بدهي4</td>
		  <td align="center">جمع کسور</td>
		  <td align="center">مانده</td>		    
	   </tr> '; 

    $SumHoghoogh_1 = $Shoghl_1 = $Matab_1 = $Jazb_1 = $Ashae_1 = $Karane_1 = 0 ;
	$Aele_1 = $Sakhti_1 =$Tashilat_1 = $Tatbigh_1 = $Bime_1 = $Maliat_1 = 0 ;  
	$Bazneshast_1 = $Saham_1 = $Yekmahe_1 = $Aghsat_1 = 0 ; 
	$Jam = 0 ; 
	$SumHoghoogh_2 = $Shoghl_2 = $Matab_2 = $Jazb_2 = $Ashae_2 = $Karane_2 = $Aele_2 = $Sakhti_2 =$Tashilat_2 = $Tatbigh_2 = 0 ; 

	$Modir_1 = $Ezafe_1 = $Mazaya1_1 = $Mazaya2_1 = $Mazaya3_1 = $Mazaya4_1 = $Mazaya5_1 = $IT10_1 = $Maskan_1 = $Bedehi4_1 =$Jamkosoor_1 = 0  ;
	$Tafavot_1 = $Mogharari_1 = $Daryafti_1 = $Bedehi1_1 = $Bedehi2_1 = $Bedehi3_1 = $Mande_1 = 0 ; 
	$Modir_2 = $Ezafe_2 = $Mazaya1_2 = $Mazaya2_2 = $Mazaya3_2 = $Mazaya4_2 = $Mazaya5_2 = $IT10_2 = $Maskan_2 = 0 ; 	

    $SalaryItem = array();
	$SID = $dt[0]['staff_id'] ;
	$rent_debt_all = 0 ; 	
        $no = 0 ;

for($i=0;$i<count($dt);$i++)
{

if($dt[$i]['staff_id'] != $SID){
		 
		//.........................................................نمایش 
		echo '<tr>
				<td align="center" rowspan="2" >'.($no+1).'</td>
				<td align="center" >'.$dt[$i-1]['plname'].'</td>
				<td align="center" >'.$dt[$i-1]['pfname'].'</td>';
				
				$v = getSum('1,2,34,3,4,36,283,10389,164,163,165,764,10364,10367,10025,10028',$SalaryItem); //حقوق			
		echo	'<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$SumHoghoogh_1 += $v ; 
				$SumHoghoogh_2 += getSum('1,2,34,3,4,36,283,10389,164,163,165,764,10364,10367,10025,10028',$SalaryItem,2); 
			
				$v = getSum('6,17',$SalaryItem);//شغل
		echo	'<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Shoghl_1 += $v;
				$Shoghl_2 += getSum('6,17',$SalaryItem,2); 
				
				$v = getSum('507',$SalaryItem); //مطب
		echo   '<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Matab_1 += $v; 
				$Matab_2 += getSum('507',$SalaryItem,2);
				
				$v = getSum('21,22,167,10366',$SalaryItem); //جذب
		echo   '<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Jazb_1 += $v; 
				$Jazb_2 += getSum('21,22,167,10366',$SalaryItem,2); 
				
				$v = getSum('19,20,186,10375',$SalaryItem); //حق اشعه
		echo   '<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Ashae_1 += $v; 
				$Ashae_2 += getSum('19,20,186,10375',$SalaryItem,2);
				
				$v = 0; //کارانه
		echo   '<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Karane_1 += $v ; 
				$Karane_2 += 0 ; 
					
				$v = getSum('7,8,9,10,32,50,51,10371,10370,10026,10027',$SalaryItem); //عائله
		echo   '<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Aele_1 += $v ;   
				$Aele_2 += getSum('7,8,9,10,32,50,51,10371,10370,10026,10027',$SalaryItem,2);
				
				$v = getSum('26,27,23,49,55,10369,10372',$SalaryItem); //سختي کار
		echo   '<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Sakhti_1 += $v ;
				$Sakhti_2 += getSum('26,27,23,49,55,10369,10372',$SalaryItem,2);
		
				$v = getSum('15,24,25,14,46',$SalaryItem); //تسهيلات
		echo   '<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Tashilat_1 += $v;
				$Tashilat_2 += getSum('15,24,25,14,46',$SalaryItem,2);			
				
				$v = getSum('33,168,12,183,9969,37,47',$SalaryItem); //تطبيق
		echo   '<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Tatbigh_1 += $v ;
				$Tatbigh_2 += getSum('33,168,12,183,9969,37,47',$SalaryItem,2);
				
				$v = getSum('38,143,144,145,9920,10032',$SalaryItem);//مبلغ بيمه
		echo   '<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Bime_1 += $v ;			
				
				$v = getSum('146,147,148',$SalaryItem); //ماليات
		echo   '<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Maliat_1 += $v;
				
				$v = getSum('149,150',$SalaryItem); //بازنشستگي
		echo   '<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Bazneshast_1 += $v;
				
				$v = getSum('9903',$SalaryItem);	//سهام
		echo   '<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Saham_1 += $v;
				
				$v = getSum('279,282',$SalaryItem);//يک ماهه
		echo   '<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Yekmahe_1 += $v;
				
				$v = vals_sum($SalaryItem,2) - $rent_debt_all;//اقساطي
		echo   '<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Aghsat_1 += $v;
				
				$v = vals_sum($SalaryItem,1) + vals_sum($SalaryItem,1,2);//جمع حقوق
		echo   '<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Jam += $v;
				
		echo '<td align="center" >'.$dt[$i-1]['account_no'].'</td>
			  <td align="center" >'.$dt[$i-1]['param1'].'</td>';		
				
		echo    '</tr>'; 
		
		echo '<tr>				
				<td align="center" >'.$dt[$i-1]['staff_id'].'</td>
				<td align="center" >'.$dt[$i-1]['cost_center_id'].'</td>';
				
				$v = getSum('28',$SalaryItem); //مديريت	
		echo	'<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Modir_1 += $v ; 
				$Modir_2 += getSum('28',$SalaryItem,2); 
			
				$v = getSum('39,152,9921,9922,40',$SalaryItem); //اضافه / تدريس
		echo	'<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Ezafe_1 += $v ; 
				$Ezafe_2 += getSum('39,152,9921,9922,40',$SalaryItem,2);
				
				$v = getSum('510,16,41',$SalaryItem);  //مزايا1
		echo	'<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Mazaya1_1 += $v ; 
				$Mazaya1_2 += getSum('510,16,41',$SalaryItem,2);
		
				$v = getSum('284,518,35,166,9901,10365,10373,10377',$SalaryItem);  //مزايا2
		echo	'<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Mazaya2_1 += $v ; 
				$Mazaya2_2 += getSum('284,518,35,166,9901,10365,10373,10377',$SalaryItem,2);
			
				$v = getSum('42,43,643,10315',$SalaryItem);  //مزايا3
		echo	'<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Mazaya3_1 += $v ; 
				$Mazaya3_2 += getSum('42,43,643,10315',$SalaryItem,2);
				
				$v = getSum('513,509',$SalaryItem);  //مزايا4
		echo	'<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Mazaya4_1 += $v ; 
				$Mazaya4_2 += getSum('513,509',$SalaryItem,2);
				
				$v = getSum('18',$SalaryItem);  //مزايا5
		echo	'<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Mazaya5_1 += $v ; 
				$Mazaya5_2 += getSum('18',$SalaryItem,2);
						
				$v = getSum('506,524',$SalaryItem);  //10%
		echo	'<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$IT10_1 += $v ; 
				$IT10_2 += getSum('506,524',$SalaryItem,2);
				
				$v = getSum('29,30,31',$SalaryItem);  //مسکن
		echo	'<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Maskan_1 += $v ; 
				$Maskan_2 += getSum('29,30,31',$SalaryItem,2);
				
				$v = vals_sum($SalaryItem,1,2); //تفاوت حقوق
		echo	'<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Tafavot_1 += $v ; 
				
				$v = getSum('9911,9915,9933',$SalaryItem);  //مقرري
		echo	'<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Mogharari_1 += $v ; 
				
				$v = getSum('254',$SalaryItem);  //دريافتي
		echo	'<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Daryafti_1 += $v ; 
		
				$v = getSum('245',$SalaryItem);  //بدهي1
		echo	'<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Bedehi1_1 += $v ; 
								
				$v = getSum('248',$SalaryItem);  //بدهي2
		echo	'<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Bedehi2_1 += $v ;
				
				$v = getSum('436',$SalaryItem);  //بدهي3
		echo	'<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Bedehi3_1 += $v ;
							
				$v = getSum('250',$SalaryItem);  //بدهي4
		echo	'<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Bedehi4_1 += $v ;
			
				$v = vals_sum($SalaryItem,2);//جمع کسور
		echo	'<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Jamkosoor_1 += $v;
				
				$v = vals_sum($SalaryItem,1) + vals_sum($SalaryItem,1,2)-
					 vals_sum($SalaryItem,2) + vals_sum($SalaryItem,2,2);//مانده	
		echo	'<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Mande_1 += $v;
				
		echo    '</tr>'; 

		unset($SalaryItem) ; 
		$rent_debt_all = 0 ; 	
		$SID = $dt[$i]['staff_id'] ;
                $no++; 
		
	/*$SumHoghoogh_1 = $Shoghl_1 = $Matab_1 = $Jazb_1 = $Ashae_1 = $Karane_1 = 0 ;
	$Aele_1 = $Sakhti_1 =$Tashilat_1 = $Tatbigh_1 = $Bime_1 = $Maliat_1 = 0 ;  
	$Bazneshast_1 = $Saham_1 = $Yekmahe_1 = $Aghsat_1 = 0 ; 
	$Jam = 0 ; 
	$SumHoghoogh_2 = $Shoghl_2 = $Matab_2 = $Jazb_2 = $Ashae_2 = $Karane_2 = $Aele_2 = $Sakhti_2 =$Tashilat_2 = $Tatbigh_2 = 0 ; 

	$Modir_1 = $Ezafe_1 = $Mazaya1_1 = $Mazaya2_1 = $Mazaya3_1 = $Mazaya4_1 = $Mazaya5_1 = $IT10_1 = $Maskan_1 = $Bedehi4_1 =$Jamkosoor_1 = 0  ;
	$Tafavot_1 = $Mogharari_1 = $Daryafti_1 = $Bedehi1_1 = $Bedehi2_1 = $Bedehi3_1 = $Mande_1 = 0 ; 
	$Modir_2 = $Ezafe_2 = $Mazaya1_2 = $Mazaya2_2 = $Mazaya3_2 = $Mazaya4_2 = $Mazaya5_2 = $IT10_2 = $Maskan_2 = 0 ; 	
*/
	} //End if

        $SalaryItem[$dt[$i]['salary_item_type_id']]['type'] = $dt[$i]['effect_type'] ;
	
	if( $dt[$i]['effect_type'] == 1 ) {
		$SalaryItem[$dt[$i]['salary_item_type_id']]['value'] = $dt[$i]['pay_sum'] ;
		$SalaryItem[$dt[$i]['salary_item_type_id']]['diff_value'] = $dt[$i]['diff_pay_sum'] ;
	}
	else{
		$SalaryItem[$dt[$i]['salary_item_type_id']]['value'] = $dt[$i]['get_sum'] ;		
	}	
 
                $rent_debt_all = (!empty($SalaryItem[250]['value']) ? $SalaryItem[250]['value'] : 0)  +
					(!empty($SalaryItem[282]['value']) ? $SalaryItem[282]['value'] : 0)  +
					(!empty($SalaryItem[436]['value']) ? $SalaryItem[436]['value'] : 0)  + 
					(!empty($SalaryItem[248]['value']) ? $SalaryItem[248]['value'] : 0) +  
					(!empty($SalaryItem[245]['value']) ? $SalaryItem[245]['value'] : 0)  + 
					(!empty($SalaryItem[254]['value']) ? $SalaryItem[254]['value'] : 0 ) + 
					(!empty($SalaryItem[9911]['value']) ? $SalaryItem[9911]['value'] : 0)  + 
					(!empty($SalaryItem[9933]['value']) ? $SalaryItem[9933]['value'] : 0)   +  
					(!empty($SalaryItem[9915]['value']) ? $SalaryItem[9915]['value'] : 0 )+   
					(!empty($SalaryItem[279]['value']) ? $SalaryItem[279]['value'] : 0  ) +   
					(!empty($SalaryItem[9903]['value']) ? $SalaryItem[9903]['value'] : 0 )  +   
					(!empty($SalaryItem[149]['value']) ? $SalaryItem[149]['value'] : 0  ) +   
					(!empty($SalaryItem[150]['value']) ? $SalaryItem[150]['value'] : 0  ) +    
					(!empty($SalaryItem[144]['value']) ? $SalaryItem[144]['value'] : 0  )+  
				        (!empty($SalaryItem[145]['value']) ? $SalaryItem[145]['value'] : 0  ) + 

				        (!empty($SalaryItem[9920]['value']) ? $SalaryItem[9920]['value'] : 0) +
   				        (!empty($SalaryItem[146]['value']) ? $SalaryItem[146]['value'] : 0 )+
				        (!empty($SalaryItem[147]['value']) ? $SalaryItem[147]['value'] : 0 ) + 
					(!empty($SalaryItem[148]['value']) ? $SalaryItem[148]['value'] : 0 ) +  
					(!empty($SalaryItem[38]['value']) ? $SalaryItem[38]['value'] : 0 )+ 
					(!empty($SalaryItem[143]['value']) ? $SalaryItem[143]['value']  : 0 );

} //End For


//.........................................................نمایش 
		echo '<tr>
				<td align="center" rowspan="2" >'.($no).'</td>
				<td align="center" >'.$dt[$i-1]['plname'].'</td>
				<td align="center" >'.$dt[$i-1]['pfname'].'</td>';
				
				$v = getSum('1,2,34,3,4,36,283,10389,164,163,165,764,10364,10367,10025,10028',$SalaryItem); //حقوق			
		echo	'<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$SumHoghoogh_1 += $v ; 
				$SumHoghoogh_2 += getSum('1,2,34,3,4,36,283,10389,164,163,165,764,10364,10367,10025,10028',$SalaryItem,2); 
			
				$v = getSum('6,17',$SalaryItem);//شغل
		echo	'<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Shoghl_1 += $v;
				$Shoghl_2 += getSum('6,17',$SalaryItem,2); 
				
				$v = getSum('507',$SalaryItem); //مطب
		echo   '<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Matab_1 += $v; 
				$Matab_2 += getSum('507',$SalaryItem,2);
				
				$v = getSum('21,22,167,10366',$SalaryItem); //جذب
		echo   '<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Jazb_1 += $v; 
				$Jazb_2 += getSum('21,22,167,10366',$SalaryItem,2); 
				
				$v = getSum('19,20,186,10375',$SalaryItem); //حق اشعه
		echo   '<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Ashae_1 += $v; 
				$Ashae_2 += getSum('19,20,186,10375',$SalaryItem,2);
				
				$v = 0; //کارانه
		echo   '<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Karane_1 += $v ; 
				$Karane_2 += 0 ; 
					
				$v = getSum('7,8,9,10,32,50,51,10371,10370,10026,10027',$SalaryItem); //عائله
		echo   '<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Aele_1 += $v ;   
				$Aele_2 += getSum('7,8,9,10,32,50,51,10371,10370,10026,10027',$SalaryItem,2);
				
				$v = getSum('26,27,23,49,55,10369,10372',$SalaryItem); //سختي کار
		echo   '<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Sakhti_1 += $v ;
				$Sakhti_2 += getSum('26,27,23,49,55,10369,10372',$SalaryItem,2);
		
				$v = getSum('15,24,25,14,46',$SalaryItem); //تسهيلات
		echo   '<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Tashilat_1 += $v;
				$Tashilat_2 += getSum('15,24,25,14,46',$SalaryItem,2);			
				
				$v = getSum('33,168,12,183,9969,37,47',$SalaryItem); //تطبيق
		echo   '<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Tatbigh_1 += $v ;
				$Tatbigh_2 += getSum('33,168,12,183,9969,37,47',$SalaryItem,2);
				
				$v = getSum('38,143,144,145,9920,10032',$SalaryItem);//مبلغ بيمه
		echo   '<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Bime_1 += $v ;			
				
				$v = getSum('146,147,148',$SalaryItem); //ماليات
		echo   '<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Maliat_1 += $v;
				
				$v = getSum('149,150',$SalaryItem); //بازنشستگي
		echo   '<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Bazneshast_1 += $v;
				
				$v = getSum('9903',$SalaryItem);	//سهام
		echo   '<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Saham_1 += $v;
				
				$v = getSum('279,282',$SalaryItem);//يک ماهه
		echo   '<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Yekmahe_1 += $v;
				
				$v = vals_sum($SalaryItem,2) - $rent_debt_all;//اقساطي
		echo   '<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Aghsat_1 += $v;
				
				$v = vals_sum($SalaryItem,1) + vals_sum($SalaryItem,1,2);//جمع حقوق
		echo   '<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Jam += $v;
				
		echo '<td align="center" >'.$dt[$i-1]['account_no'].'</td>
			  <td align="center" >'.$dt[$i-1]['param1'].'</td>';		
				
		echo    '</tr>'; 
		
		echo '<tr>				
				<td align="center" >'.$dt[$i-1]['staff_id'].'</td>
				<td align="center" >'.$dt[$i-1]['cost_center_id'].'</td>';
				
				$v = getSum('28',$SalaryItem); //مديريت	
		echo	'<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Modir_1 += $v ; 
				$Modir_2 += getSum('28',$SalaryItem,2); 
			
				$v = getSum('39,152,9921,9922,40',$SalaryItem); //اضافه / تدريس
		echo	'<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Ezafe_1 += $v ; 
				$Ezafe_2 += getSum('39,152,9921,9922,40',$SalaryItem,2);
				
				$v = getSum('510,16,41',$SalaryItem);  //مزايا1
		echo	'<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Mazaya1_1 += $v ; 
				$Mazaya1_2 += getSum('510,16,41',$SalaryItem,2);
		
				$v = getSum('284,518,35,166,9901,10365,10373,10377',$SalaryItem);  //مزايا2
		echo	'<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Mazaya2_1 += $v ; 
				$Mazaya2_2 += getSum('284,518,35,166,9901,10365,10373,10377',$SalaryItem,2);
			
				$v = getSum('42,43,643,10315',$SalaryItem);  //مزايا3
		echo	'<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Mazaya3_1 += $v ; 
				$Mazaya3_2 += getSum('42,43,643,10315',$SalaryItem,2);
				
				$v = getSum('513,509',$SalaryItem);  //مزايا4
		echo	'<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Mazaya4_1 += $v ; 
				$Mazaya4_2 += getSum('513,509',$SalaryItem,2);
				
				$v = getSum('18',$SalaryItem);  //مزايا5
		echo	'<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Mazaya5_1 += $v ; 
				$Mazaya5_2 += getSum('18',$SalaryItem,2);
						
				$v = getSum('506,524',$SalaryItem);  //10%
		echo	'<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$IT10_1 += $v ; 
				$IT10_2 += getSum('506,524',$SalaryItem,2);
				
				$v = getSum('29,30,31',$SalaryItem);  //مسکن
		echo	'<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Maskan_1 += $v ; 
				$Maskan_2 += getSum('29,30,31',$SalaryItem,2);
				
				$v = vals_sum($SalaryItem,1,2); //تفاوت حقوق
		echo	'<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Tafavot_1 += $v ; 
				
				$v = getSum('9911,9915,9933',$SalaryItem);  //مقرري
		echo	'<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Mogharari_1 += $v ; 
				
				$v = getSum('254',$SalaryItem);  //دريافتي
		echo	'<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Daryafti_1 += $v ; 
		
				$v = getSum('245',$SalaryItem);  //بدهي1
		echo	'<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Bedehi1_1 += $v ; 
								
				$v = getSum('248',$SalaryItem);  //بدهي2
		echo	'<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Bedehi2_1 += $v ;
				
				$v = getSum('436',$SalaryItem);  //بدهي3
		echo	'<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Bedehi3_1 += $v ;
							
				$v = getSum('250',$SalaryItem);  //بدهي4
		echo	'<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Bedehi4_1 += $v ;
			
				$v = vals_sum($SalaryItem,2);//جمع کسور
		echo	'<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Jamkosoor_1 += $v;
				
				$v = vals_sum($SalaryItem,1) + vals_sum($SalaryItem,1,2)-
					 vals_sum($SalaryItem,2) + vals_sum($SalaryItem,2,2);//مانده	
		echo	'<td align="center" >'.number_format($v, 0, '.', ',').'</td>';
				$Mande_1 += $v;
				
		echo    '</tr>'; 
				
//......................................................................................
echo "<tr style='background-color:#E2F0FF'>
			<td colspan=3 rowspan=2 style='font-weight:bold'>جمع :</td>
			<td>".number_format($SumHoghoogh_1, 0, '.', ',')."</td><td>".number_format($Shoghl_1, 0, '.', ',')."</td><td>".number_format($Matab_1, 0, '.', ',')."</td>
			<td>".number_format($Jazb_1, 0, '.', ',')."</td><td>".number_format($Ashae_1, 0, '.', ',')."</td><td>".number_format($Karane_1, 0, '.', ',')."</td>
			<td>".number_format($Aele_1, 0, '.', ',')."</td><td>".number_format($Sakhti_1, 0, '.', ',')."</td>
		    <td>".number_format($Tashilat_1, 0, '.', ',')."</td><td>".number_format($Tatbigh_1, 0, '.', ',')."</td><td>".number_format($Bime_1, 0, '.', ',')."</td>
			<td>".number_format($Maliat_1, 0, '.', ',')."</td><td>".number_format($Bazneshast_1, 0, '.', ',')."</td><td>".number_format($Saham_1, 0, '.', ',')."</td>
			<td>".number_format($Yekmahe_1, 0, '.', ',')."</td><td>".number_format($Aghsat_1, 0, '.', ',')."</td><td>".number_format($Jam, 0, '.', ',')."</td><td>&nbsp;</td><td>&nbsp;</td>
	  </tr>" ; 

echo "<tr style='background-color:#E2F0FF'>			
			<td>".number_format($Modir_1, 0, '.', ',')."</td><td>".number_format($Ezafe_1, 0, '.', ',')."</td><td>".number_format($Mazaya1_1, 0, '.', ',')."</td>
			<td>".number_format($Mazaya2_1, 0, '.', ',')."</td><td>".number_format($Mazaya3_1, 0, '.', ',')."</td><td>".number_format($Mazaya4_1, 0, '.', ',')."</td>
			<td>".number_format($Mazaya5_1, 0, '.', ',')."</td><td>".number_format($IT10_1, 0, '.', ',')."</td><td>".number_format($Maskan_1, 0, '.', ',')."</td>
			<td>".number_format($Tafavot_1, 0, '.', ',')."</td><td>".number_format($Mogharari_1, 0, '.', ',')."</td><td>".number_format($Daryafti_1, 0, '.', ',')."</td>
			<td>".number_format($Bedehi1_1, 0, '.', ',')."</td><td>".number_format($Bedehi2_1, 0, '.', ',')."</td>
			<td>".number_format($Bedehi3_1, 0, '.', ',')."</td><td>".number_format($Bedehi4_1, 0, '.', ',')."</td>
			<td>".number_format($Jamkosoor_1, 0, '.', ',')."</td><td>".number_format($Mande_1, 0, '.', ',')."</td><td>&nbsp;</td>
	  </tr>" ; 	  
} //End ShowIf




?>

<script>
	PersonSalary.prototype = {
		TabID : '<?= $_REQUEST["ExtTabID"] ?>',
		address_prefix : "<?= $js_prefix_address ?>",		
		get : function(elementID){
			return findChild(this.TabID, elementID);
		}
	};
	
	PersonSalary.prototype.showReport = function(btn, e)
	{
		if(!this.filterPanel.getForm().isValid())
			return;
			
		this.form = this.get("mainForm");
		this.form.target = "_blank";
		this.form.method = "POST";
		this.form.action =  this.address_prefix + "PersonSalaryReport.php?show=true";
	
		this.form.submit();
		this.get("excel").value = "";
		return;
	}
	
	function PersonSalary()
	{
		   
		this.filterPanel = new Ext.form.Panel({
			renderTo : this.get('DivInfo'),
			width : 780,
			titleCollapse : true,
			frame : true,
			collapsible : true,
			bodyStyle : "padding:5px",
			title :"تنظیم گزارش حقوق و مزایای کارکنان",
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
								"<input type=checkbox id='PT_5' name='PT_5' value=1 >&nbsp; قراردادی " +
								"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+
								"<input type=checkbox id='PT_10' name='PT_10' value=1 >&nbsp; بازنشسته "
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
									parentNode = PersonSalaryObject.filterPanel.down("[itemId=chkgroup]").getEl().dom;
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
									parentNode = PersonSalaryObject.filterPanel.down("[itemId=chkgroup2]").getEl().dom;
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
											PersonSalaryObject.filterPanel.down("[itemId=PayType]").setValue("1");										
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
					}					
					
			],
			buttons :  [ {
							text : "مشاهده گزارش ",
							handler :  Ext.bind(this.showReport,this),
							iconCls : "report"                                
						},{
						iconCls : "clear",
						text : "پاک کردن فرم",
						handler : function(){
						this.up("form").getForm().reset();
						PersonSalaryObject.get("mainForm").reset();
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
						PersonSalaryObject.filterPanel.down("[itemId=chkgroup]").add({
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
						PersonSalaryObject.filterPanel.down("[itemId=chkgroup2]").add({
							xtype : "container",
							html : "<input type=checkbox name=chkEmpState_" + record.data.InfoID + " id=chkEmpState_" + record.data.InfoID + " checked > " + record.data.Title
						});
						
					});
										
				}}
			
		});		
		
		
	}
	
	var PersonSalaryObject = new PersonSalary();
	
	
		
</script>
<center>
	<form id="mainForm">
		<br>
		<div id="DivInfo"></div><br>
		<input type="hidden" name="excel" id="excel">
	</form>
</center>
