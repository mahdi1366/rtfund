<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	93.03
//---------------------------

require_once("../../../header.inc.php");


if (isset($_REQUEST["show"]))
{
	require_once '../../../global/salary_utils.inc.php';
 	
	global $GroupCostCenter;
	global $GroupPersonType;
	
	global $where;
	global $param;
	
	$GroupCostCenter = isset($_POST["GroupCostCenter"]) ? true : false;
	$GroupPersonType = isset($_POST["GroupPersonType"]) ? true : false;

	ShowReport();	
	die();
}

function makeWhere(){
	
	global $where;
	global $param;
	
	$keys = array_keys($_POST);
	
	if(!empty($_POST["staff_id"]))
	{
		$where .= " AND s.staff_id=:staffid";
		$param[":staffid"] = $_POST["staff_id"];
	}
		
	//........................................
	
	$PersonTypes = array();
	$CostCenters = array();
	$EmpStates = array();
	
	for($i=0; $i < count($keys); $i++)
	{
		if(strpos($keys[$i],"PersonType_") !== false)
			$PersonTypes[] = str_replace ("PersonType_", "", $keys[$i]);
		
		if(strpos($keys[$i],"chkcostID_") !== false)
			$CostCenters[] = str_replace ("chkcostID_", "", $keys[$i]);
		
		if(strpos($keys[$i],"chkEmpState_") !== false)
			$EmpStates[] = str_replace ("chkEmpState_", "", $keys[$i]);
	}
	
	if(count($PersonTypes) > 0)
		$where .= " AND s.person_type in(" . implode(",", $PersonTypes) . ")";
	if(count($CostCenters) > 0)
		$where .= " AND pi.cost_center_id in(" . implode(",", $CostCenters) . ")";
	if(count($EmpStates) > 0)
		$where .= " AND w.emp_state in(" . implode(",", $EmpStates) . ")";
	
}

function makeGroup(){
	
	global $GroupCostCenter;
	global $GroupPersonType;
	
	$group = "";
	$group .= $GroupCostCenter ? ",pi.cost_center_id" : "";
	$group .= $GroupPersonType ? ",s.person_type" : "";
	
	return $group;
}

function PrepareData(&$person_count,$type){
	
	global $GroupCostCenter;
	global $GroupPersonType;
	
	global $where;
	global $param;
	
	$payment_type = $_POST["PayType"] ;
	$pay_year = $_POST["pay_year"];
	$pay_month = $_POST["pay_month"];

	$where = "";
	$param = array(
		":year" => $pay_year,
		":month" => $pay_month,
		":ptype" => $payment_type
	);
	
	makeWhere();
	$group = makeGroup();
	
	$month_start = DateModules::shamsi_to_miladi($pay_year . "-" . $pay_month . "-1");
	
	if($type == 'TSummary') {
		manage_salary_utils::simulate_tax($pay_year, $pay_month, $payment_type);
		manage_salary_utils::simulate_bime($pay_year, $pay_month, $payment_type);
			
	$MainRows = PdoDataAccess::runquery("
		SELECT pi.salary_item_type_id,
			sit.full_title sit_title,
			sit.effect_type,
			max(pi.pay_year) pay_year,
			max(pi.pay_month) pay_month,
			sum(pay_value) pay_sum,
			sum(diff_pay_value * diff_value_coef) diff_pay_sum,
			sum(CASE WHEN pi.salary_item_type_id IN(146,147,148,747) THEN tts.value WHEN pi.salary_item_type_id IN(9920,145,144) THEN tis.value ELSE get_value END) get_sum,
			sum(CASE WHEN pi.salary_item_type_id IN(146,147,148,747,9920,145,144,744) THEN 0 ELSE diff_get_value * diff_value_coef END) diff_get_sum,
			sum(CASE WHEN pi.salary_item_type_id IN(146,147,148,747) THEN tts.param2 WHEN pi.salary_item_type_id IN(9920,145,144) THEN tis.param2 ELSE pi.param2 END) param2,
			sum(CASE WHEN pi.salary_item_type_id IN(146,147,148,747,9920,145,144,744) THEN 0 ELSE diff_param2 * diff_param2_coef END) diff_param2,
			sum(CASE WHEN pi.salary_item_type_id IN(146,147,148,747) THEN tts.param3 WHEN pi.salary_item_type_id IN(9920,145,144) THEN tis.param3 ELSE pi.param3 END) param3,
			sum(CASE WHEN pi.salary_item_type_id IN(146,147,148,747,9920,145,144,744) THEN 0 ELSE diff_param3 * diff_param3_coef END) diff_param3,
			sum(CASE
					WHEN (s.last_retired_pay IS NOT NULL
						AND s.last_retired_pay < '$month_start') THEN pi.param3
				END) retired_for_org_up30,
			sum(CASE
					WHEN (s.last_retired_pay IS NOT NULL
						AND s.last_retired_pay < '$month_start') THEN diff_param3 * diff_param3_coef
				END) diff_retired_for_org_up30,

			sum(CASE
					WHEN (s.last_retired_pay IS NULL
						OR s.last_retired_pay >= '$month_start') THEN pi.param3
				END) retired_for_org_dn30,
			sum(CASE
					WHEN (s.last_retired_pay IS NULL
						OR s.last_retired_pay >= '$month_start') THEN diff_param3 * diff_param3_coef
				END) diff_retired_for_org_dn30,
			sum(pi.param7) param7,
			sum(pi.diff_param7 * pi.diff_value_coef) diff_param7,								  
			max(s.person_type) person_type,
			max(pi.cost_center_id) cost_center_id,
			max(cc.title) cost_center_title,
			max(ou.ptitle) unit_title,
			max(s.person_type) person_type,
			max(w.ouid) ouid

		FROM payment_items pi
			INNER JOIN payments p
				ON(pi.pay_year = p.pay_year AND pi.pay_month = p.pay_month AND pi.staff_id = p.staff_id AND pi.payment_type = p.payment_type 
					AND pi.pay_year = :year AND pi.pay_month = :month)
			INNER JOIN writs w
				ON(p.writ_id = w.writ_id AND p.writ_ver = w.writ_ver AND p.staff_id = w.staff_id AND w.state=" . WRIT_SALARY . ")
			LEFT OUTER JOIN org_new_units ou
				ON(w.ouid = ou.ouid)
			LEFT OUTER JOIN cost_centers cc
				ON (pi.cost_center_id = cc.cost_center_id)
			LEFT OUTER JOIN salary_item_types sit
				ON (sit.salary_item_type_id = pi.salary_item_type_id AND sit.credit_topic = 1)
			LEFT OUTER JOIN staff s
				ON (pi.staff_id = s.staff_id)
			LEFT OUTER JOIN temp_tax_include_sum tts
				ON (tts.staff_id = pi.staff_id AND pi.salary_item_type_id IN(146,147,148,747))
			LEFT OUTER JOIN temp_insure_include_sum tis
				ON (tis.staff_id = pi.staff_id AND pi.salary_item_type_id IN(9920,145,144,744)) 
				
		WHERE pi.payment_type = :ptype AND pi.pay_year=:year AND pi.pay_month=:month $where
				
		GROUP BY pi.salary_item_type_id,sit.effect_type,sit.full_title $group
		ORDER BY pi.pay_year,pi.pay_month" . $group . ",pi.salary_item_type_id", $param);
	
	//echo PdoDataAccess::GetLatestQueryString();
	}
	elseif($type == 'Summary') {
	
		$MainRows = PdoDataAccess::runquery(" SELECT   pi.salary_item_type_id,
													   sit.full_title sit_title,
													   sit.effect_type,
													   max(pi.pay_year) pay_year,
													   max(pi.pay_month) pay_month,
													   sum(pay_value) pay_sum,
													   sum(diff_pay_value * diff_value_coef) diff_pay_sum,
													   sum(get_value) get_sum,
													   sum(diff_get_value * diff_value_coef) diff_get_sum,
													   sum(param2) param2,
													   sum(diff_param2 * diff_param2_coef) diff_param2,
													   sum(param3) param3,
													   sum(diff_param3 * diff_param3_coef) diff_param3,
													   sum(CASE
															  WHEN (s.last_retired_pay IS NOT NULL
																   AND s.last_retired_pay < '".$month_start."' ) THEN param3
														   END) retired_for_org_up30,
													   sum(CASE
															  WHEN (s.last_retired_pay IS NOT NULL
																   AND s.last_retired_pay < '".$month_start."' ) THEN diff_param3 * diff_param3_coef
														   END) diff_retired_for_org_up30,
														  
													   sum(CASE
															  WHEN (s.last_retired_pay IS NULL
																   OR s.last_retired_pay >= '".$month_start."' ) THEN param3
														  END) retired_for_org_dn30,
													   sum(CASE
															  WHEN (s.last_retired_pay IS NULL
																   OR s.last_retired_pay >= '".$month_start."' ) THEN diff_param3 * diff_param3_coef
														  END) diff_retired_for_org_dn30,
													   sum(pi.param7) param7,
													   sum(pi.diff_param7 * pi.diff_value_coef) diff_param7,								  
													   max(s.person_type) person_type,
													   max(pi.cost_center_id) cost_center_id,
													   max(cc.title) cost_center_title,
													   max(ou.ptitle) unit_title,
													   max(s.person_type) person_type,
													   max(w.ouid) ouid 
										    FROM payment_items pi
												  INNER JOIN payments p
													   ON(pi.pay_year = p.pay_year AND 
														  pi.pay_month = p.pay_month AND 
														  pi.staff_id = p.staff_id AND 
														  pi.payment_type = p.payment_type )
												  LEFT JOIN writs w
													   ON(p.writ_id = w.writ_id AND p.writ_ver = w.writ_ver AND p.staff_id = w.staff_id AND w.state=" . WRIT_SALARY . ")
												  LEFT OUTER JOIN org_new_units ou
													   ON(w.ouid = ou.ouid)
												  LEFT OUTER JOIN cost_centers cc
													   ON (pi.cost_center_id = cc.cost_center_id)
												  LEFT OUTER JOIN salary_item_types sit
													   ON (sit.salary_item_type_id = pi.salary_item_type_id)
												  LEFT OUTER JOIN staff s
													   ON (pi.staff_id = s.staff_id) 
										    WHERE pi.payment_type = :ptype AND pi.pay_year=:year AND pi.pay_month=:month  $where   
											GROUP BY pi.salary_item_type_id,sit.effect_type,sit.full_title $group
											ORDER BY pi.pay_year,pi.pay_month" . $group . ",pi.salary_item_type_id", $param );
											
					
																					
	}
	global $MainQuery;
	$MainQuery = PdoDataAccess::GetLatestQueryString();
	
	//............... counting persons number .................
	
	$dt = PdoDataAccess::runquery("
		select COUNT(DISTINCT pi.staff_id) person_count $group
		from payment_items pi
			INNER JOIN payments p 
				ON(pi.pay_year = p.pay_year AND pi.pay_month = p.pay_month AND pi.staff_id = p.staff_id AND pi.payment_type = p.payment_type)
			LEFT JOIN writs w
				ON(p.writ_id = w.writ_id AND p.writ_ver = w.writ_ver AND p.staff_id = w.staff_id AND w.state=" . WRIT_SALARY . ")
			LEFT OUTER JOIN staff s
				ON pi.staff_id=s.staff_id
		WHERE pi.payment_type = :ptype AND pi.pay_year=:year AND pi.pay_month=:month $where " .
		($group != "" ? " group by " . substr($group,1) : "") , $param);
		
		//echo PdoDataAccess::GetLatestQueryString();
	
	$person_count = array();
	foreach($dt as $row)
	{
		$group = "";
		$group = $GroupCostCenter ? $row["cost_center_id"] . "_" : "ALL_";
		$group .= $GroupPersonType ? $row["person_type"] : "ALL";

		$person_count[$group] = $row['person_count'];		
	}
	
	return $MainRows;
}

function PersonTypeDesc($personType){
	
	switch ($personType){
		case "1" : return "هیئت علمی";
		case "2" : 
		case "5" :	return "کارمند";
		case "3" : return "روزمزد";
	}
}

function getSum($indexs, &$salaryItems, $kind = 'pay'){

	$indexs = split(',',$indexs);
	
	$sum = 0;
	for($i=0; $i<count($indexs); $i++)
	{
		$sum += isset($salaryItems[ $indexs[$i] ]) ? $salaryItems[ $indexs[$i] ][ ($kind == 'pay') ? 'value' : 'diff_value' ] : 0;
		unset($salaryItems[ $indexs[$i] ][ ($kind == 'pay') ? 'value' : 'diff_value' ]);
	}
	return $sum;
}

function vals_sum($salaryItems, $effect_type, $kind = 'get'){
	$sum = 0;
	reset($salaryItems);
	foreach($salaryItems as $row){
		if($row['effect_type'] == $effect_type)
			$sum += $kind == 'get' ? $row['value'] : $row['diff_value'];
	}

	return $sum;
}

function print_remained($salaryItems, $effect_type){
	
	foreach ($salaryItems as $row) {
		if($row['effect_type'] == $effect_type && 
				((isset($row['value']) && $row['value'] > 0)  || (isset($row['diff_value']) && $row['diff_value'] > 0))){
			
			if($row["person_type"] == "1")
				$ptName = '(هيات علمي)';
			if($row["person_type"] == "2" || $row["person_type"] == "5")
				$ptName = '(کارمندان)';
			if($row["person_type"] == "3")
				$ptName = '(روزمزد)';
			
			if($_SESSION["UserID"] == "jafarkhani")
				$ptName .= "[" . $row["salary_item_type_id"] . "]";
			?>
			<tr>
				<td><?= $row["sit_title"] . $ptName?></td>
				<td><?= number_format($row["value"], 0, '.', ',') ?></td>
				<td><?= number_format($row["diff_value"], 0, '.', ',') ?></td>
				<td><?= number_format($row["value"] + $row["diff_value"], 0, '.', ',') ?></td>
			</tr>
			<?		
		}
	}
}

function ComputePurePay(){
	
	global $where;
	global $param;
	
	$get_value =" CASE WHEN pi.salary_item_type_id IN(146,147,148,747) THEN tts.value WHEN pi.salary_item_type_id IN(9920,145,144,744) THEN tis.value ELSE get_value END ";
	$diff_get_value = "CASE WHEN pi.salary_item_type_id IN(146,147,148,747,9920,145,144,744) THEN 0 ELSE diff_get_value END ";
	
	if($_GET['RepType'] == 'TSummary' )			  
	{
		$TSelect = " sum(pi.pay_value - $get_value) pure_pay,
					 sum((pi.diff_pay_value - $diff_get_value) * pi.diff_value_coef) diff_pure_pay " ; 
		$TJoin = " AND sit.credit_topic = 1 " ; 
		
	}
	elseif ($_GET['RepType'] == 'Summary') {
		$TJoin = " " ;
		$TSelect = " sum(pi.pay_value - pi.get_value) pure_pay,
					 sum((pi.diff_pay_value - pi.diff_get_value) * pi.diff_value_coef) diff_pure_pay " ;	
	}
	$query = "SELECT b.bank_id,
					 b.name bank_title,
					 $TSelect
			FROM  payment_items pi
				INNER JOIN salary_item_types sit
					ON sit.salary_item_type_id = pi.salary_item_type_id $TJoin
				INNER JOIN payments p
					ON(pi.pay_year = p.pay_year AND pi.pay_month = p.pay_month AND pi.staff_id = p.staff_id AND pi.payment_type = p.payment_type 
						AND pi.pay_year = :year AND pi.pay_month = :month)
				LEFT JOIN writs w
					ON(p.writ_id = w.writ_id AND p.writ_ver = w.writ_ver AND p.staff_id = w.staff_id AND  w.state=" . WRIT_SALARY . ")
				LEFT OUTER JOIN temp_tax_include_sum tts
					ON (tts.staff_id = pi.staff_id AND pi.salary_item_type_id IN(146,147,148,747))
				LEFT OUTER JOIN temp_insure_include_sum tis
					ON (tis.staff_id = pi.staff_id AND pi.salary_item_type_id IN(9920,145,144,744))
				INNER JOIN staff s
					ON(pi.staff_id = s.staff_id AND pi.pay_year = :year AND pi.pay_month = :month)
				LEFT OUTER JOIN banks b
					ON(p.bank_id = b.bank_id)
			WHERE  pi.payment_type = :ptype $where
			GROUP BY b.bank_id,b.name";
	$dt = PdoDataAccess::runquery($query, $param);
	
	//echo PdoDataAccess::GetLatestQueryString() ; die() ;
	
	foreach($dt as $row)
	{
		echo "<tr>
				<td>قابل پرداخت (" . $row['bank_title'] . ")</td>
				<td>" . number_format($row["pure_pay"], 0, '.', ',') . "</td>
				<td>" . number_format($row["diff_pure_pay"], 0, '.', ',')  . "</td>
				<td>" . number_format(($row["pure_pay"] + $row["diff_pure_pay"]), 0, '.', ',') . "</td>
			</tr>";
	}
}

function makeHeader($row, $person_count){
	
	global $GroupCostCenter;
	global $GroupPersonType;
	
	$group = "";
	$group = $GroupCostCenter ? $row["cost_center_id"] . "_" : "ALL_";
	$group .= $GroupPersonType ? $row["person_type"] : "ALL";
	$HedearTitle = '' ; 
	
	if($_GET['RepType'] == 'TSummary' )
		$HedearTitle = 'خلاصه لیست حقوق خزانه'; 
		
	if($_GET['RepType'] ==  'Summary' )
		$HedearTitle = 'خلاصه لیست حقوق '; 	
		
	echo "<table width=100% border=0 style='font-family:b nazanin;'>
			<tr>
				<td width=120px><img src='/HumanResources/img/fum_symbol.jpg'></td>
				<td align=center style='font-weight:bold'>".$HedearTitle."</td>
				<td width=120px>
					شماره : 
					<br>
					تاریخ : 
					" . DateModules::shNow() . "
				</td>
			</tr>
		</table>";
	
	echo "<table id=outer width=100% cellpadding=0 cellspacing=0>
		<tr>
			<td colspan=2 class=header>
				<table width=100%>
				<tr class=header>
					<td>مرکز هزینه : " . ($GroupCostCenter ? "[ " . $row["cost_center_id"] . " ] " . $row["cost_center_title"] : "همه") . "</td>
					<td>نوع پرسنل : " . ($GroupPersonType ? PersonTypeDesc($row["person_type"]) : "همه" ) . "</td>
					<td>تعداد افراد : " . $person_count[$group] . "</td>
					<td>مربوط به : " . $_POST["pay_year"] . "/" . $_POST["pay_month"] . "</td>
				</tr>
				</table>
			</td>
		</tr>";	
}

function makeFooter(){
	
	echo "<table width=100% border=0 style='font-family:b nazanin;'>
			<tr>
				<td>
					حسابدار
					<br><br>
					رئیس واحد
					<br><br>
					مدیر مالی دانشگاه
				</td>
				<td>
					اداره رسیدگی
				</td>
				<td>
					اداره اعتبارات	
				</td>
			</tr>
		</table>";
}

function makeBody($salaryItems){
	
	global $GroupCostCenter;
	global $GroupPersonType;
	
	$pay_val_sum = vals_sum($salaryItems,1);
	$diff_pay_val_sum = vals_sum($salaryItems,1,'diff_get');
	$get_val_sum = vals_sum($salaryItems,2);
	
//echo $get_val_sum .'---------' ;  die() ; 
	
	$diff_get_val_sum = vals_sum($salaryItems,2,'diff_get');
	$i149 = isset($salaryItems[149]) ? $salaryItems[149] : 0;
	$i150 = isset($salaryItems[150]) ? $salaryItems[150] : 0;
	$i144 = isset($salaryItems[144]) ? $salaryItems[144] : 0;
	$i145 = isset($salaryItems[145]) ? $salaryItems[145] : 0;
	$i744 = isset($salaryItems[744]) ? $salaryItems[744] : 0;
	$i9920 = isset($salaryItems[9920]) ? $salaryItems[9920] : 0;
	$i38   = isset($salaryItems[38]) ? $salaryItems[38] : 0;
	$i143  = isset($salaryItems[143]) ? $salaryItems[143] : 0;
	//+items+
	$hoghogh = getSum('1,2,34,3,4,36,283,10264,10364',$salaryItems);//'حقوق '
	$diff_hoghogh = getSum('1,2,34,3,4,36,283,10264 , 10364 ',$salaryItems,'diff_pay');//'تفاوت حقوق '
	$foghaladeh_shoghl = getSum('17,6,10267, 10367',$salaryItems);//'فوق العاده شغل '
	$diff_foghaladeh_shoghl = getSum('17,6,10267, 10367',$salaryItems,'diff_pay');//'تفاوت فوق العاده شغل '
	$foghaladeh_jazb = getSum('15,21,22,167,10266,10366',$salaryItems);//'فوق العاده جذب '
	$diff_foghaladeh_jazb = getSum('15,21,22,167,10266,10366',$salaryItems,'diff_pay');//'تفاوت فوق العاده جذب '
	$aelemandi_oulad = getSum('29,30,7,8,9,10,32,50,51,10330,10371,10329,10370',$salaryItems);//'عائله مندي و حق اولاد و مسكن'
	$diff_aelemandi_oulad = getSum('29,30,7,8,9,10,32,50,51,10330,10371,10329,10370',$salaryItems,'diff_pay');//'تفاوت عائله مندي و حق اولاد و مسكن'
	$sakhtikar_noubatkari = getSum('26,27,23,49,55,10328,10369,10331,10372,10334,10375',$salaryItems);//'سختي کار و يا نوبت کاري '
	$diff_sakhtikar_noubatkari = getSum('26,27,23,49,55,10328,10369,10331,10372,10334,10375',$salaryItems,'diff_pay');//'تفاوت سختي کار و يا نوبت کاري '
	$badi_ab_hava = getSum('24,25,14,46,10333,10374',$salaryItems);//'بدي آب و هوا '
	$diff_badi_ab_hava = getSum('24,25,14,46,10333,10374',$salaryItems,'diff_pay');//'تفاوت بدي آب و هوا '
	$tafavot_tatbigh = getSum('33,168,12,183,9969,56,47,10327,10368,10335,10376',$salaryItems);//'تفاوت تطبيق '
	$diff_tafavot_tatbigh = getSum('33,168,12,183,9969,56,47,10327,10368,10335,10376',$salaryItems,'diff_pay');//'تفاوت تفاوت تطبيق '
	$ezafekar_haghotadris = getSum('39,152,9922,9921',$salaryItems);//'اضافه کار يا حق التدريس '
	$diff_ezafekar_haghotadris = getSum('39,152,9922,9921',$salaryItems,'diff_pay');//'تفاوت اضافه کار يا حق التدريس '
	$mazaya1 = getSum('16,510,41',$salaryItems);//'ساير مزايا 1 '
	$diff_mazaya1 = getSum('16,510,41',$salaryItems,'diff_pay');//'تفاوت ساير مزايا 1 '
	$mazaya2 = getSum('518,35,166,284,9901,10265,10365,10332,10377,10373',$salaryItems);//'ساير مزايا 2 '
	$diff_mazaya2 = getSum('518,35,166,284,9901,10265,10365,10332,10377,10373',$salaryItems,'diff_pay');//'تفاوت ساير مزايا 2 '
	$mazaya3 = getSum('42,43',$salaryItems);//'ساير مزايا 3 '
	$diff_mazaya3 = getSum('42,43',$salaryItems,'diff_pay');//'تفاوت ساير مزايا 3 '
	$mazaya4 = getSum('513,509',$salaryItems);//'ساير مزايا 4 '
	$diff_mazaya4 = getSum('513,509',$salaryItems,'diff_pay');//'تفاوت ساير مزايا 4 '
	$mazaya5 = getSum('18',$salaryItems);//'ساير مزايا 5 '
	$diff_mazaya5 = getSum('18',$salaryItems,'diff_pay');//'تفاوت ساير مزايا 5 '
	$haghbime_janbaz = getSum('506,524',$salaryItems);//'حق بيمه درماني جانبازان '
	$diff_haghbime_janbaz = getSum('506,524',$salaryItems,'diff_pay');//'تفاوت حق بيمه درماني جانبازان '
	//-items-
	$bime_taminejtemaie = getSum('9920,145,144,744',$salaryItems);//'بيمه تامين اجتماعي '
	$diff_bime_taminejtemaie = getSum('9920,145,144,744',$salaryItems,'diff_pay');//'تفاوت بيمه تامين اجتماعي '
	$bazneshastegi_rasmi = getSum('149,150',$salaryItems);//'بازنشستگي پرسنل رسمي '
	$diff_bazneshastegi_rasmi = getSum('149,150',$salaryItems,'diff_pay');//'تفاوت بازنشستگي پرسنل رسمي '
	$bime_rasmi = getSum('38,143',$salaryItems);//'بيمه درماني پرسنل رسمي'
	$diff_bime_rasmi = getSum('38,143',$salaryItems,'diff_pay');//'بيمه درماني پرسنل رسمي'
	$maliat = getSum('146,147,148,747',$salaryItems);//'ماليات'
	$diff_maliat = getSum('146,147,148,747',$salaryItems,'diff_pay');//'ماليات'
	$saham_sandogh_edare = getSum('9903,244',$salaryItems);//'سهام صندوق اداره'
	$diff_saham_sandogh_edare = getSum('9903,244',$salaryItems,'diff_pay');//'تفاوت سهام صندوق اداره'
	$mogharari_maheaval = getSum('9915,9911,9933',$salaryItems);//'مقرري ماه اول پرسنل رسمي'
	$diff_mogharari_maheaval = getSum('9915,9911,9933',$salaryItems,'diff_pay');//'تفاوت مقرري ماه اول پرسنل رسمي'
	$bedehi_motafareghe1 = getSum('245,9929',$salaryItems);//'بدهي متفرقه 1'
	$diff_bedehi_motafareghe1 = getSum('245,9929',$salaryItems,'diff_pay');//'تفاوت بدهي متفرقه 1'
	$bedehi_motafareghe2 = getSum('248',$salaryItems);//'بدهي متفرقه 2'
	$diff_bedehi_motafareghe2 = getSum('248',$salaryItems,'diff_pay');//'تفاوت بدهي متفرقه 2'
	$bedehi_motafareghe3 = getSum('436',$salaryItems);//'بدهي متفرقه 3'
	$diff_bedehi_motafareghe3 = getSum('436',$salaryItems,'diff_pay');//'تفاوت بدهي متفرقه 3'
	$bedehi_motafareghe4 = getSum('250,395',$salaryItems);//'بدهي متفرقه 4'
	$diff_bedehi_motafareghe4 = getSum('250,395',$salaryItems,'diff_pay');//'تفاوت بدهي متفرقه 4'
	$pasanadz_sandogh_alghadir = getSum('251,397',$salaryItems);//'پس انداز صندوق الغدير'
	$diff_pasanadz_sandogh_alghadir = getSum('251,397',$salaryItems,'diff_pay');//'تفاوت پس انداز صندوق الغدير'
	$bime_takmili_iran = getSum('9919',$salaryItems);//'بيمه تکميلي ايران'
	$diff_bime_takmili_iran = getSum('9919',$salaryItems,'diff_pay');//'تفاوت بيمه تکميلي ايران'
	$saham_sandogh_emamali = getSum('276',$salaryItems);//'سهام صندوق امام علي(ع)'
	$diff_saham_sandogh_emamali = getSum('276',$salaryItems,'diff_pay');//'تفاوت سهام صندوق امام علي(ع)'
	$bedehi_yekmahe = getSum('279',$salaryItems);//'بدهي يکماهه'
	$diff_bedehi_yekmahe = getSum('279',$salaryItems,'diff_pay');//'تفاوت بدهي يکماهه'
	$bedehi_yekmahe = getSum('282',$salaryItems);//'بيمه عمر و حوادث'
	$diff_bedehi_yekmahe = getSum('282',$salaryItems,'diff_pay');//'تفاوت بيمه عمر و حوادث'
	$ezafe_daryafti = getSum('254',$salaryItems);//'اضافه دريافتي'
	$diff_ezafe_daryafti = getSum('254',$salaryItems,'diff_pay');//'تفاوت اضافه دريافتي'
	
	$bime_omr = 0;
	$diff_bime_omr = 0;
//echo $get_val_sum.'****'  ; die() ; 
	$bedehihaye_aghsati = $get_val_sum -
						( $bime_taminejtemaie +
							$bazneshastegi_rasmi +
							$bime_rasmi +
							$maliat +
							$saham_sandogh_edare +
							$mogharari_maheaval +
							$bedehi_motafareghe1 +
							$bedehi_motafareghe2 +
							$bedehi_motafareghe3 +
							$bedehi_motafareghe4 +
							$pasanadz_sandogh_alghadir +
							$bime_takmili_iran +
							$saham_sandogh_emamali +
							$bedehi_yekmahe +
							$ezafe_daryafti);//'بديهاي اقساطي'
	$diff_bedehihaye_aghsati = $diff_get_val_sum -
								( $diff_bime_taminejtemaie +
								$diff_bazneshastegi_rasmi +
								$diff_bime_rasmi +
								$diff_maliat +
								$diff_saham_sandogh_edare +
								$diff_mogharari_maheaval +
								$diff_bedehi_motafareghe1 +
								$diff_bedehi_motafareghe2 +
								$diff_bedehi_motafareghe3 +
								$diff_bedehi_motafareghe4 +
								$diff_pasanadz_sandogh_alghadir +
								$diff_bime_takmili_iran +
								$diff_saham_sandogh_emamali +
								$diff_bedehi_yekmahe +
								$diff_ezafe_daryafti);//'تفاوت بديهاي اقساطي'
		
	?>
	<tr>
		<td style="vertical-align: top;">
			<table id="inner" cellpadding=0 cellspacing=0>
				<tr class="header">
					<td width="40%">قلم حقوقي</td>
					<td width="20%">مبلغ</td>
					<td width="20%">تفاوت</td>
					<td width="20%">جمع</td>
				</tr>
				<tr>
					<td>حقوق <?= $_SESSION["UserID"] == "jafarkhani" ? "[1,2,34,3,4,36,283,10264,10364]" : ""?></td>
					<td><?= number_format($hoghogh, 0, '.', ',') ?></td>
					<td><?= number_format($diff_hoghogh, 0, '.', ',') ?></td>
					<td><?= number_format($hoghogh + $diff_hoghogh, 0, '.', ',') ?></td>
				</tr>
				<tr>
					<td>فوق العاده شغل <?= $_SESSION["UserID"] == "jafarkhani" ? "[17,6,10267, 10367]" : ""?></td>
					<td><?= number_format($foghaladeh_shoghl, 0, '.', ',') ?></td>
					<td><?= number_format($diff_foghaladeh_shoghl, 0, '.', ',') ?></td>
					<td><?= number_format($foghaladeh_shoghl + $diff_foghaladeh_shoghl, 0, '.', ',') ?></td>
				</tr>
				<tr>
					<td>فوق العاده جذب <?= $_SESSION["UserID"] == "jafarkhani" ? "[15,21,22,167,10266,10366]" : ""?></td>
					<td><?= number_format($foghaladeh_jazb, 0, '.', ',') ?></td>
					<td><?= number_format($diff_foghaladeh_jazb, 0, '.', ',') ?></td>
					<td><?= number_format($diff_foghaladeh_jazb + $foghaladeh_jazb, 0, '.', ',') ?></td>
				</tr>
				<tr>
					<td>حق اولاد ، عائله مندي و مسكن <?= $_SESSION["UserID"] == "jafarkhani" ? "[29,30,7,8,9,10,32,50,51,10330,10371,10329,10370]" : ""?></td>
					<td><?= number_format($aelemandi_oulad, 0, '.', ',') ?></td>
					<td><?= number_format($diff_aelemandi_oulad, 0, '.', ',') ?></td>
					<td><?= number_format($diff_aelemandi_oulad + $aelemandi_oulad, 0, '.', ',') ?></td>
				</tr>
				<tr>
					<td>سختي کار و يا نوبت کاري <?= $_SESSION["UserID"] == "jafarkhani" ? "[26,27,23,49,55,10328,10369,10331,10372,10334,10375]" : ""?></td>
					<td><?= number_format($sakhtikar_noubatkari, 0, '.', ',') ?></td>
					<td><?= number_format($diff_sakhtikar_noubatkari, 0, '.', ',') ?></td>
					<td><?= number_format($diff_sakhtikar_noubatkari + $sakhtikar_noubatkari, 0, '.', ',') ?></td>
				</tr>
				<tr>
					<td>بدي آب و هوا <?= $_SESSION["UserID"] == "jafarkhani" ? "[24,25,14,46,10333,10374]" : ""?></td>
					<td><?= number_format($badi_ab_hava, 0, '.', ',') ?></td>
					<td><?= number_format($diff_badi_ab_hava, 0, '.', ',') ?></td>
					<td><?= number_format($diff_badi_ab_hava + $badi_ab_hava, 0, '.', ',') ?></td>
				</tr>
				<tr>
					<td>تفاوت تطبيق <?= $_SESSION["UserID"] == "jafarkhani" ? "[33,168,12,183,9969,56,47,10327,10368,10335,10376]" : ""?></td>
					<td><?= number_format($tafavot_tatbigh, 0, '.', ',') ?></td>
					<td><?= number_format($diff_tafavot_tatbigh, 0, '.', ',') ?></td>
					<td><?= number_format($diff_tafavot_tatbigh + $tafavot_tatbigh, 0, '.', ',') ?></td>
				</tr>
				<tr>
					<td>ساير مزايا 1 <?= $_SESSION["UserID"] == "jafarkhani" ? "[16,510,41]" : ""?></td>
					<td><?= number_format($mazaya1, 0, '.', ',') ?></td>
					<td><?= number_format($diff_mazaya1, 0, '.', ',') ?></td>
					<td><?= number_format($diff_mazaya1 + $mazaya1, 0, '.', ',') ?></td>
				</tr>
				<tr>
					<td>ساير مزايا 2 <?= $_SESSION["UserID"] == "jafarkhani" ? "[518,35,166,284,9901,10265,10365,10332,10377,10373]" : ""?></td>
					<td><?= number_format($mazaya2, 0, '.', ',') ?></td>
					<td><?= number_format($diff_mazaya2, 0, '.', ',') ?></td>
					<td><?= number_format($diff_mazaya2 + $mazaya2, 0, '.', ',') ?></td>
				</tr>
				<tr>
					<td>ساير مزايا 3 <?= $_SESSION["UserID"] == "jafarkhani" ? "[42,43]" : ""?></td>
					<td><?= number_format($mazaya3, 0, '.', ',') ?></td>
					<td><?= number_format($diff_mazaya3, 0, '.', ',') ?></td>
					<td><?= number_format($diff_mazaya3 + $mazaya3, 0, '.', ',') ?></td>
				</tr>
				<tr>
					<td>ساير مزايا 4 <?= $_SESSION["UserID"] == "jafarkhani" ? "[513,509]" : ""?></td>
					<td><?= number_format($mazaya4, 0, '.', ',') ?></td>
					<td><?= number_format($diff_mazaya4, 0, '.', ',') ?></td>
					<td><?= number_format($diff_mazaya4 + $mazaya4, 0, '.', ',') ?></td>
				</tr>
				<tr>
					<td>ساير مزايا 5 <?= $_SESSION["UserID"] == "jafarkhani" ? "[18]" : ""?></td>
					<td><?= number_format($mazaya5, 0, '.', ',') ?></td>
					<td><?= number_format($diff_mazaya5, 0, '.', ',') ?></td>
					<td><?= number_format($diff_mazaya5 + $mazaya5, 0, '.', ',') ?></td>
				</tr>
				<tr>
					<td>حق بيمه درماني جانبازان <?= $_SESSION["UserID"] == "jafarkhani" ? "[506,524]" : ""?></td>
					<td><?= number_format($haghbime_janbaz, 0, '.', ',') ?></td>
					<td><?= number_format($diff_haghbime_janbaz, 0, '.', ',') ?></td>
					<td><?= number_format($diff_haghbime_janbaz + $haghbime_janbaz, 0, '.', ',') ?></td>
				</tr>
				<? print_remained($salaryItems, 1); ?>
				<tr>
					<td>اضافه کار يا حق التدريس <?= $_SESSION["UserID"] == "jafarkhani" ? "[39,152,9922,9921]" : ""?></td>
					<td><?= number_format($ezafekar_haghotadris, 0, '.', ',') ?></td>
					<td><?= number_format($diff_ezafekar_haghotadris, 0, '.', ',') ?></td>
					<td><?= number_format($diff_ezafekar_haghotadris + $ezafekar_haghotadris, 0, '.', ',') ?></td>
				</tr>
				<tr>
					<td>جمع</td>
					<td><?= number_format($pay_val_sum, 0, '.', ',') ?></td>
					<td><?= number_format($diff_pay_val_sum, 0, '.', ',') ?></td>
					<td><?= number_format($diff_pay_val_sum + $pay_val_sum, 0, '.', ',') ?></td>
				</tr>
			</table>
		</td>
		<td style="vertical-align: top;">
			<table id="inner" cellpadding=0 cellspacing=0>
				<tr class="header">
					<td width="40%">قلم حقوقي</td>
					<td width="20%">مبلغ</td>
					<td width="20%">تفاوت</td>
					<td width="20%">جمع</td>
				</tr>
				<tr>
					<td>ماليات</td>
					<td><?= number_format($maliat, 0, '.', ',') ?></td>
					<td><?= number_format($diff_maliat, 0, '.', ',') ?></td>
					<td><?= number_format($diff_maliat + $maliat, 0, '.', ',') ?></td>
				</tr>
				<tr>
					<td>بازنشستگي پرسنل رسمي</td>
					<td><?= number_format($bazneshastegi_rasmi, 0, '.', ',') ?></td>
					<td><?= number_format($diff_bazneshastegi_rasmi, 0, '.', ',') ?></td>
					<td><?= number_format($diff_bazneshastegi_rasmi + $bazneshastegi_rasmi, 0, '.', ',') ?></td>
				</tr>
				<tr>
					<td>مقرري ماه اول پرسنل رسمي</td>
					<td><?= number_format($mogharari_maheaval, 0, '.', ',') ?></td>
					<td><?= number_format($diff_mogharari_maheaval, 0, '.', ',') ?></td>
					<td><?= number_format($diff_mogharari_maheaval + $mogharari_maheaval, 0, '.', ',') ?></td>
				</tr>
				<tr>
					<td>بيمه تامين اجتماعي</td>
					<td><?= number_format($bime_taminejtemaie, 0, '.', ',') ?></td>
					<td><?= number_format($diff_bime_taminejtemaie, 0, '.', ',') ?></td>
					<td><?= number_format($diff_bime_taminejtemaie + $bime_taminejtemaie, 0, '.', ',') ?></td>
				</tr>
				<tr>
					<td>بيمه درماني پرسنل رسمي</td>
					<td><?= number_format($bime_rasmi, 0, '.', ',') ?></td>
					<td><?= number_format($diff_bime_rasmi, 0, '.', ',') ?></td>
					<td><?= number_format($diff_bime_rasmi + $bime_rasmi, 0, '.', ',') ?></td>
				</tr>
				<tr>
					<td>بيمه تکميلي ايران</td>
					<td><?= number_format($bime_takmili_iran, 0, '.', ',') ?></td>
					<td><?= number_format($diff_bime_takmili_iran, 0, '.', ',') ?></td>
					<td><?= number_format($bime_takmili_iran + $diff_bime_takmili_iran, 0, '.', ',') ?></td>
				</tr>
				<tr>
					<td>اضافه دريافتي</td>
					<td><?= number_format($ezafe_daryafti, 0, '.', ',') ?></td>
					<td><?= number_format($diff_ezafe_daryafti, 0, '.', ',') ?></td>
					<td><?= number_format($ezafe_daryafti + $diff_ezafe_daryafti, 0, '.', ',') ?></td>
				</tr>
				<tr>
					<td>بدهي يکماهه</td>
					<td><?= number_format($bedehi_yekmahe, 0, '.', ',') ?></td>
					<td><?= number_format($diff_bedehi_yekmahe, 0, '.', ',') ?></td>
					<td><?= number_format($bedehi_yekmahe + $diff_bedehi_yekmahe, 0, '.', ',') ?></td>
				</tr>
				<tr>
					<td>سهام صندوق اداره</td>
					<td><?= number_format($saham_sandogh_edare, 0, '.', ',') ?></td>
					<td><?= number_format($diff_saham_sandogh_edare, 0, '.', ',') ?></td>
					<td><?= number_format($saham_sandogh_edare + $diff_saham_sandogh_edare, 0, '.', ',') ?></td>
				</tr>
				<tr>
					<td>بديهاي اقساطي</td>
					<td><?= number_format($bedehihaye_aghsati, 0, '.', ',') ?></td>
					<td><?= number_format($diff_bedehihaye_aghsati, 0, '.', ',') ?></td>
					<td><?= number_format($bedehihaye_aghsati + $diff_bedehihaye_aghsati, 0, '.', ',') ?></td>
				</tr>
				<tr>
					<td>بدهي متفرقه 1</td>
					<td><?= number_format($bedehi_motafareghe1, 0, '.', ',') ?></td>
					<td><?= number_format($diff_bedehi_motafareghe1, 0, '.', ',') ?></td>
					<td><?= number_format($bedehi_motafareghe1 + $diff_bedehi_motafareghe1, 0, '.', ',') ?></td>
				</tr>
				<tr>
					<td>بدهي متفرقه 2</td>
					<td><?= number_format($bedehi_motafareghe2, 0, '.', ',') ?></td>
					<td><?= number_format($diff_bedehi_motafareghe2, 0, '.', ',') ?></td>
					<td><?= number_format($bedehi_motafareghe2 + $diff_bedehi_motafareghe2, 0, '.', ',') ?></td>
				</tr>
				<tr>
					<td>بدهي متفرقه 3</td>
					<td><?= number_format($bedehi_motafareghe3, 0, '.', ',') ?></td>
					<td><?= number_format($diff_bedehi_motafareghe3, 0, '.', ',') ?></td>
					<td><?= number_format($bedehi_motafareghe3 + $diff_bedehi_motafareghe3, 0, '.', ',') ?></td>
				</tr>
				<tr>
					<td>بدهي متفرقه 4</td>
					<td><?= number_format($bedehi_motafareghe4, 0, '.', ',') ?></td>
					<td><?= number_format($diff_bedehi_motafareghe4, 0, '.', ',') ?></td>
					<td><?= number_format($bedehi_motafareghe4 + $diff_bedehi_motafareghe4, 0, '.', ',') ?></td>
				</tr>
				<tr>
					<td>پس انداز صندوق الغدير</td>
					<td><?= number_format($pasanadz_sandogh_alghadir, 0, '.', ',') ?></td>
					<td><?= number_format($diff_pasanadz_sandogh_alghadir, 0, '.', ',') ?></td>
					<td><?= number_format($pasanadz_sandogh_alghadir + $diff_pasanadz_sandogh_alghadir, 0, '.', ',') ?></td>
				</tr>
				<tr>
					<td>سهام صندوق امام علي(ع)</td>
					<td><?= number_format($saham_sandogh_emamali, 0, '.', ',') ?></td>
					<td><?= number_format($diff_saham_sandogh_emamali, 0, '.', ',') ?></td>
					<td><?= number_format($saham_sandogh_emamali + $diff_saham_sandogh_emamali, 0, '.', ',') ?></td>
				</tr>
				<tr>
					<td>جمع کل کسورات</td>
					<td><?= number_format($get_val_sum, 0, '.', ',') ?></td>
					<td><?= number_format($diff_get_val_sum, 0, '.', ',') ?></td>
					<td><?= number_format($get_val_sum + $diff_get_val_sum, 0, '.', ',') ?></td>
				</tr>
				<? 
				if(!$GroupCostCenter && !$GroupPersonType){				
					ComputePurePay();
					}
				else{
				?>
				<tr>
					<td>قابل پرداخت</td>
					<td><?= number_format($pay_val_sum - $get_val_sum, 0, '.', ',') ?></td>
					<td><?= number_format($diff_pay_val_sum - $diff_get_val_sum, 0, '.', ',') ?></td>
					<td><?= number_format($diff_pay_val_sum + $pay_val_sum - $get_val_sum - $diff_get_val_sum, 0, '.', ',') ?></td>
				</tr>
				<?}?>
			</table>
		</td>
	</tr>
	<tr>
		<td style="vertical-align: top;">
			<table id="inner" cellpadding=0 cellspacing=0>
				<tr>
					<td width="40%">فصل اول </td>
					<td width="20%"><?= number_format($pay_val_sum - $mazaya3, 0, '.', ',')?></td>
					<td width="20%"><?= number_format($diff_pay_val_sum - $diff_mazaya3, 0, '.', ',')?></td>
					<td width="20%"><?= number_format($pay_val_sum + $diff_pay_val_sum - $mazaya3 - $diff_mazaya3, 0, '.', ',')?></td>
				</tr>
				<tr>
					<td>بازنشستگي سهم سازمان(زير سي سال) <?= $_SESSION["UserID"] == "jafarkhani" ? "[149,150]" : ""?></td>
					<td><?= number_format($i149['retired_for_org_dn30'] + $i150['retired_for_org_dn30'], 0, '.', ',')?></td>
					<td><?= number_format($i149['diff_retired_for_org_dn30'] + $i150['diff_retired_for_org_dn30'], 0, '.', ',')?></td>
					<td><?= number_format($i149['diff_retired_for_org_dn30'] + $i150['diff_retired_for_org_dn30'] + 
							$i149['retired_for_org_dn30'] + $i150['retired_for_org_dn30'], 0, '.', ',')?></td>
				</tr>
				<tr>
					<td>بازنشستگي سهم سازمان(بالاي سي سال) <?= $_SESSION["UserID"] == "jafarkhani" ? "[149,150]" : ""?></td>
					<td><?= number_format($i149['retired_for_org_up30'] + $i150['retired_for_org_up30'], 0, '.', ',')?></td>
					<td><?= number_format($i149['diff_retired_for_org_up30'] + $i150['diff_retired_for_org_up30'], 0, '.', ',')?></td>
					<td><?= number_format($i149['diff_retired_for_org_up30'] + $i150['diff_retired_for_org_up30'] + 
							$i149['retired_for_org_up30'] + $i150['retired_for_org_up30'], 0, '.', ',')?></td>
				</tr>
				<tr>
					<td>بيمه خدمات درماني (سهم سازمان) <?= $_SESSION["UserID"] == "jafarkhani" ? "[38,143]" : ""?></td>
					<td><?= number_format($i38["param7"] + $i143["param7"], 0, '.', ',')?></td>
					<td><?= number_format($i38["diff_param7"] + $i143["diff_param7"], 0, '.', ',')?></td>
					<td><?= number_format($i38["param7"] + $i143["param7"] + $i38["diff_param7"] + $i143["diff_param7"], 0, '.', ',')?></td>
				</tr>
				<tr>
					<?
					if($_POST['pay_year'] >= '1393' &&  $_POST['pay_month']  >= '10' )	
						$coef_dolat = 1 ;
					elseif($_POST['pay_year'] >= '1390' && $_POST['pay_month']  >= '5' )
						$coef_dolat = 1.7 / 1.65 ;
					else if ($_POST['pay_year'] >= '1391')	
						$coef_dolat = 1.7 / 1.65 ;
                    else
						$coef_dolat = 3/2 ;
					?>
					<td>بیمه خدمات درمانی (سهم دولت) <?= $_SESSION["UserID"] == "jafarkhani" ? "[38,143 coaf:".$coef_dolat ."]" : ""?></td>
					<td><?= number_format($i38["param7"] *  $coef_dolat + $i143["param7"] * $coef_dolat, 0, '.', ',')?></td>
					<td><?= number_format($i38["diff_param7"] *  $coef_dolat + $i143["diff_param7"] * $coef_dolat, 0, '.', ',')?></td>
					<td><?= number_format($i38["param7"] *  $coef_dolat + $i143["param7"] * $coef_dolat  + $i38["diff_param7"] * $coef_dolat + 
							$i143["diff_param7"] * $coef_dolat, 0, '.', ',')?></td>
				</tr>
				<tr>
					<td>بيمه عمر و حوادث سهم سازمان <?= $_SESSION["UserID"] == "jafarkhani" ? "[0]" : ""?></td>
					<td><?= number_format($bime_omr, 0, '.', ',')?></td>
					<td><?= number_format($diff_bime_omr, 0, '.', ',')?></td>
					<td><?= number_format($diff_bime_omr + $bime_omr, 0, '.', ',')?></td>
				</tr>
				<tr>
					<?
				   
  				    // $i744["param3"]  ;  //  از sum1 حذف شد چون بچه های قراردادی این پارامتر بیمه بیکاری سهم سازمان را ندارند
					// +  + $i744["diff_param3"]  
					
					$sum1 = ($pay_val_sum) +
						($i149['retired_for_org_up30'] + $i149['retired_for_org_dn30']) +
						($i150['retired_for_org_up30'] + $i150['retired_for_org_dn30']) +
						($i144["param2"]+$i9920["param2"])+ 						
						($i145["param2"] + $i744["param2"] ) +						
						($i145["param3"])+
						$bime_omr +
						($i38["param7"] + $i143["param7"]) + ($i38["param7"] * $coef_dolat + $i143["param7"] * $coef_dolat) ;
						
						
						
					$sum2 = ($diff_pay_val_sum) +
							($i149['diff_retired_for_org_up30'] + $i149['diff_retired_for_org_dn30']) +
							($i150['diff_retired_for_org_up30'] + $i150['diff_retired_for_org_dn30']) +
							($i144["diff_param2"]+$i9920["diff_param2"]) + 
							($i145["diff_param2"] + $i744["diff_param2"] ) +
							($i145["diff_param3"] )+
							 $diff_bime_omr +
							($i38["diff_param7"] + $i143["diff_param7"]  ) +
							($i38["diff_param7"] * $coef_dolat + $i143["diff_param7"] * $coef_dolat);
					?>
					<td>جمع کل</td>
					<td><?= number_format($sum1, 0, '.', ',')?></td>
					<td><?= number_format($sum2, 0, '.', ',')?></td>
					<td><?= number_format($sum1 + $sum2, 0, '.', ',')?></td>
				</tr>
			</table>
		</td>
		<td style="vertical-align: top;">
			<table id="inner" cellpadding=0 cellspacing=0>
				<tr>
					<td width="40%">ساير فصول</td>
					<td width="20%"><?= number_format($mazaya3, 0, '.', ',')?></td>
					<td width="20%"><?= number_format($diff_mazaya3, 0, '.', ',')?></td>
					<td width="20%"><?= number_format($mazaya3 + $diff_mazaya3, 0, '.', ',')?></td>
				</tr>
				<tr>
					<td>بيمه اجتماعي سهم سازمان (پيماني و خريد خدمت) <?= $_SESSION["UserID"] == "jafarkhani" ? "" : ""?></td>
					<td><?= number_format($i144["param2"]+$i9920["param2"], 0, '.', ',')?></td>
					<td><?= number_format($i144["diff_param2"]+$i9920["diff_param2"], 0, '.', ',')?></td>
					<td><?= number_format($i144["param2"]+$i9920["param2"]+$i144["diff_param2"]+$i9920["diff_param2"], 0, '.', ',')?></td>
				</tr>
				<tr>
					<td>بيمه بيکاري سهم سازمان (پيماني و خريد خدمت) <?= $_SESSION["UserID"] == "jafarkhani" ? "" : ""?></td>
					<td><?= number_format(0, 0, '.', ',')?></td>
					<td><?= number_format(0, 0, '.', ',')?></td>
					<td><?= number_format(0, 0, '.', ',')?></td>
				</tr>
				<tr>
					<td>بیمه اجتماعی سهم سازمان(روزمزد/قراردادی) <?= $_SESSION["UserID"] == "jafarkhani" ? "" : ""?></td>
					<td><?= number_format(( $i145["param2"] + $i744["param2"] ), 0, '.', ',')?></td>
					<td><?= number_format(($i145["diff_param2"] + $i744["diff_param2"]), 0, '.', ',')?></td>
					<td><?= number_format($i145["param2"] + $i145["diff_param2"] + $i744["param2"] + $i744["diff_param2"] , 0, '.', ',')?></td>
				</tr>
				<? if( /*empty($_POST['PersonType_1']) && empty($_POST['PersonType_2']) &&*/ empty($_POST['PersonType_3']) /*&& $_POST['PersonType_5'] == true*/ ){ ?>
				<tr>
					<td>بیمه بیکاری سهم سازمان(روزمزد/قراردادی) <?= $_SESSION["UserID"] == "jafarkhani" ? "" : ""?></td> 
					<td>0</td>
					<td>0</td>
					<td>0</td>
				</tr>
				<? } elseif(!empty($_POST['PersonType_3'])) {
				?>
				<tr>
					<td>بیمه بیکاری سهم سازمان(روزمزد/قراردادی) <?= $_SESSION["UserID"] == "jafarkhani" ? "" : ""?></td> 
					<td><?= number_format($i145["param3"] /*+ $i744["param3"] */ , 0, '.', ',')?></td>
					<td><?= number_format($i145["diff_param3"] /*+ $i744["diff_param3"]*/ , 0, '.', ',')?></td>
					<td><?= number_format($i145["param3"] + $i145["diff_param3"] /*+ $i744["param3"] + $i744["diff_param3"]*/, 0, '.', ',')?></td>
				</tr>
				<?
				}?>
			</table>			
		</td>
	</tr>
	<?
	
	echo "</table><br><div class=pageBreak></div>";
}

function ShowReport(){
	
	global $GroupCostCenter;
	global $GroupPersonType;
	
	?>
	<html>
	<head>
		<META http-equiv=Content-Type content="text/html; charset=UTF-8" >
		<style>
			.header {
				background-color: #D9EBFF;
				font-weight: bold;
				font-size: 10px !important;
			}
			.header td{
				font-size: 11px !important;
			}
			/*-----------------------*/
			#outer table {
				border : 0px;
				border-collapse: collapse;
				width : 100%;
			}
			#outer tr {
				border : 1px solid black;
				border-bottom: 0px;
			}
			#outer td {
				font-family: tahoma;
				font-size: 10px;
				height : 21px;
			}
			/*-----------------------*/
			#inner table {
				border : 0px;
				border-collapse: collapse;
				width : 100%;
			}
			#inner td {
				font-family: tahoma;
				padding-right : 3px;
				font-size: 10px;
				height : 21px;
				border-collapse: collapse;
				border : 1px solid black;
			}
		</style>
	</head>
		<body dir=rtl>
	<?	
	
	$person_count;
	$salaryItems = array();
	global $MainQuery;
	$MainRows = PrepareData($person_count,$_GET['RepType']);
				
	$currentGroup = "";
	for($i=0; $i < count($MainRows); $i++)
	{
		$row = $MainRows[$i];
		
		$group = $GroupCostCenter ? $row["cost_center_id"] . "_" : "ALL_";
		$group .= $GroupPersonType ? $row["person_type"] : "ALL";
		
		if($currentGroup != $group)
		{
			if($currentGroup != "")
			{				
				makeBody($salaryItems);
				makeFooter();
			}
			makeHeader($row, $person_count);
			$currentGroup = $group;
			$i--;
			$salaryItems = array();
			continue;			
		}
		
		$salaryItems[ $row["salary_item_type_id"] ] = $row;
		$salaryItems[ $row["salary_item_type_id"] ]["value"] = ($row["effect_type"] == "1") ? $row["pay_sum"] : $row["get_sum"];
		
		$salaryItems[ $row["salary_item_type_id"] ]["diff_value"] = ($row["effect_type"] == "1") ? $row["diff_pay_sum"] : $row["diff_get_sum"];
	}	
	
	//--------- for last costcenter --------------
	makeBody($salaryItems);
	makeFooter();
?>
	<div style="display:none"><?= $MainQuery ?></div>
	</body>		
</html>		
<?
}

?>

<script>
	TreasureSummary.prototype = {
		TabID : '<?= $_REQUEST["ExtTabID"] ?>',
		address_prefix : "<?= $js_prefix_address ?>",
	
		get : function(elementID){
			return findChild(this.TabID, elementID);
		}
	};

	function TreasureSummary()
	{
		this.filterPanel = new Ext.form.Panel({
			renderTo : this.get('DivInfo'),
			width : 780,
			titleCollapse : true,
			frame : true,
			collapsible : true,
		 
			title :"تنظیمات گزارش ",
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
				width : 150,
				allowBlank : false,
				name : "pay_year"
			},{
				xtype : "numberfield",
				hideTrigger : true,
				width : 150,
				fieldLabel : "ماه",
				allowBlank : false,
				name : "pay_month"
			},{
				xtype : "numberfield",
				hideTrigger : true,
				width : 180,
				labelWidth: 110,
				fieldLabel : "شماره شناسایی",
				name : "staff_id"
			},
			{
				xtype : "combo",
				colspan : 4,
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
											}),
				valueField : "InfoID",
				displayField : "Title",
				hiddenName : "PayType",
				allowBlank : false,
				fieldLabel : "نوع پرداخت",
				listConfig: {
					loadingText: 'در حال جستجو...',
					emptyText: 'فاقد اطلاعات',
					itemCls : "search-item"
				},
				width:300
			},
			{
				xtype : "fieldset",
				colspan : 2,
				style: "margin-left:4px",
				title : "نوع فرد",
				html :  "<input type=checkbox name='PersonType_1' checked>  هیئت علمی &nbsp;&nbsp;&nbsp;&nbsp;" +
						"<input type=checkbox name='PersonType_2' checked>  کارمند &nbsp;&nbsp;&nbsp;&nbsp;" +
						"<input type=checkbox name='PersonType_3' checked>  روزمزد &nbsp;&nbsp;&nbsp;&nbsp;" +
						"<input type=checkbox name='PersonType_5' checked>  قراردادی &nbsp;&nbsp;&nbsp;&nbsp;" + 
						"<input type=checkbox name='PersonType_10' checked>  بازنشسته &nbsp;&nbsp;&nbsp;&nbsp;"
			},{
				xtype : "fieldset",
				title : "تفکیک گزارش",
				
				html :  "<input type=checkbox name='GroupCostCenter'> مرکز هزینه &nbsp;&nbsp;&nbsp;&nbsp;" +
						"<input type=checkbox name='GroupPersonType'> نوع شخص "						
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
							parentNode = TreasureSummaryObject.filterPanel.down("[itemId=chkgroup]").getEl().dom;
							elems = parentNode.getElementsByTagName("input");
							for(i=0; i<elems.length; i++)
							{
								if(elems[i].id.indexOf("chkcostID_") != -1)
									elems[i].checked = this.getValue();
							}
						}
					}
				}]
			},{
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
							parentNode = TreasureSummaryObject.filterPanel.down("[itemId=chkgroup2]").getEl().dom;
							elems = parentNode.getElementsByTagName("input");
							for(i=0; i<elems.length; i++)
							{
								if(elems[i].id.indexOf("chkEmpState_") != -1)
									elems[i].checked = this.getValue();
							}
						}
					}
				}]
			}],
			buttons :  [{
				text : "خلاصه لیست خزانه",
				handler : function(){TreasureSummaryObject.showReport()},
				listeners : {
					click : function(){
						TreasureSummaryObject.get('TSummary').value = "true";
						TreasureSummaryObject.get('Summary').value = "false";
					}
				},
				iconCls : "report"
			},/*{
				text : "خروجی excel",
				handler : function(){TreasureSummaryObject.showReport()},
				listeners : {
					click : function(){
						TreasureSummaryObject.get('excel').value = "true";
					}
				},
				iconCls : "excel"
			}*/
			{
				text : "خلاصه لیست حقوق",
				handler : function(){TreasureSummaryObject.showReport()},
				listeners : {
					click : function(){
						TreasureSummaryObject.get('Summary').value = "true";
						TreasureSummaryObject.get('TSummary').value = "false";
					}
				},
				iconCls : "report"
			},
			{
				iconCls : "clear",
				text : "پاک کردن فرم",
				handler : function(){
					this.up("form").getForm().reset();
					TreasureSummaryObject.get("mainForm").reset();
				}
			}]
		});
		
		//..........................
		
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
						TreasureSummaryObject.filterPanel.down("[itemId=chkgroup]").add({
							xtype : "container",
							html : "<input checked type=checkbox name=chkcostID_" + record.data.cost_center_id + " id=chkcostID_" + record.data.cost_center_id + " > " + record.data.title
						});
					});
				}}
		});
		
		//............................
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
						TreasureSummaryObject.filterPanel.down("[itemId=chkgroup2]").add({
							xtype : "container",
							html : "<input checked type=checkbox name=chkEmpState_" + record.data.InfoID + " id=chkEmpState_" + record.data.InfoID + " > " + 
								record.data.Title
						});
					});
				}}
		});
		
	}
	
	var TreasureSummaryObject = new TreasureSummary();
	
	TreasureSummary.prototype.showReport = function(btn, e)
	{
		if(!this.filterPanel.getForm().isValid())
			return;
		this.form = this.get("mainForm");
		this.form.target = "_blank";
		this.form.method = "POST";
	
		if(this.get("TSummary").value == 'true' )		
			this.form.action =  this.address_prefix + "treasure_summary.php?show=true&RepType=TSummary";	
			
		else if (this.get("Summary").value == 'true')
			this.form.action =  this.address_prefix + "treasure_summary.php?show=true&RepType=Summary";	
		
		this.form.submit();
		this.get("excel").value = "";
		
		
		return;
	}

</script>
<center>
	<form id="mainForm">
		<br>
		<div id="DivInfo"></div><br>
		<input type="hidden" name="excel" id="excel">
		<input type="hidden" name="TSummary" id="TSummary">
		<input type="hidden" name="Summary" id="Summary"> 
	</form>
</center>
