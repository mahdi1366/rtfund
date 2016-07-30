<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	92.08
//---------------------------
require_once("../../../header.inc.php");
//ini_set("display_errors", "On");

if (!isset($_REQUEST["show"]))
	require_once '../js/loan_card_report.js.php';

$whr = " ";

if (isset($_REQUEST["show"])) {

	$whereParam = array();

	if (!empty($_POST["SID"])) {
		$whr .= " AND s.staff_id = :sid ";
		$whereParam[":sid"] = $_POST["SID"];
	}
	if (!empty($_POST["cost_center_id"])) {
		if ($_POST["cost_center_id"] != '-1') {
			$whr .= " AND c.cost_center_id = :cid ";
			$whereParam[":cid"] = $_POST["cost_center_id"];
		}
	}
	if (!empty($_POST["BankID"])  && $_POST["BankID"] != -1 ) {
		$whr .= " AND pa.bank_id = :bid ";
		$whereParam[":bid"] = $_POST["BankID"];
	}
	if (!empty($_POST["PTY"])) {
		if ($_POST["PTY"] == 102) {
			$whr .= " AND s.person_type in ( 1,2,3 ) ";
		} else {
			$whr .= " AND s.person_type = :pt ";
			$whereParam[":pt"] = $_POST["PTY"];
		}

		if ($_POST["PTY"] == 1 || $_POST["PTY"] == 2 || $_POST["PTY"] == 3 || $_POST["PTY"] == 102) {
			//$DB = "hrms.";
			if ($_POST["PTY"] == 1)
				$person_type = " هیئت علمی ";

			if ($_POST["PTY"] == 2)
				$person_type = " کارمند ";

			if ($_POST["PTY"] == 3)
				$person_type = " روزمزد بیمه ای ";

			if ($_POST["PTY"] == 102)
				$person_type = "هیئت علمی، کارمند، روزمزدبیمه ای";
		}
		elseif ($_POST["PTY"] == 5) {
			//$DB = "hrms_sherkati.";
			$person_type = " قراردادی ";
		} elseif ($_POST["PTY"] == 10){
			$DB = "hrmr.";
			$person_type = "بازنشستگان"  ;  
			
			}
			
			$DB = "hrmstotal." ; 
	}
	
	
	//..........................
	//.........................

	if ($_POST['RepFormat'] == 2) {

		$qry = "select SalaryItemTypeID from SalaryItemAccess 
							where UserID='" . $_SESSION["UserID"] . "' AND SalaryItemTypeID in (" . $_POST["SITID1"] . "," . $_POST["SITID2"] . ")";
		$res = PdoDataAccess::runquery($qry);

		if (count($res) != 2)
			$whr .= " AND c.cost_center_id in (" . manage_access::getValidCostCenters() . ") ";

		if ($_POST['RepType'] == 0)
			$orderBy = " order by cost_center_id,p.plname,p.pfname ";

		elseif ($_POST['RepType'] == 1)
			$orderBy = " order by p.plname,p.pfname ";

		$query = " select		c.cost_center_id,
								c.title cost_title,
								s.staff_id,								
								p.pfname,
								p.plname,
								pit.param1 param1 ,
								pit.param4 remainder,
								pit.get_value instalment ,
								pit.salary_item_type_id,
								ps.loan_no,ps.contract_no ,
								sit.print_title,
								pit.get_value,
								pa.account_no

					from " . $DB . "staff s
									INNER JOIN " . $DB . "persons p
										ON (s.PersonID = p.PersonID)
									INNER JOIN " . $DB . "payment_items pit
										ON(pit.staff_id = s.staff_id
											AND (pit.salary_item_type_id = " . $_POST["SITID1"] . "
												OR pit.salary_item_type_id = " . $_POST["SITID2"] . " ))
									INNER JOIN " . $DB . "payments pa
										ON(pa.pay_year = pit.pay_year AND pa.pay_month = pit.pay_month AND 
											pa.staff_id = pit.staff_id AND pa.payment_type = pit.payment_type)
									INNER JOIN " . $DB . "cost_centers c
										ON (pit.cost_center_id = c.cost_center_id)
									INNER JOIN " . $DB . "salary_item_types sit
										ON(pit.salary_item_type_id = sit.salary_item_type_id)
									LEFT OUTER JOIN " . $DB . "person_subtracts ps
										ON(pit.param2 = ps.subtract_id) 

				where pa.payment_type = ".$_POST['PayType']." AND  pit.pay_month= " . $_POST["pay_month"] . " AND
						pit.pay_year=" . $_POST["pay_year"] . " AND		
 pit.param2 not in (
270376904	,
270376908	,
270376909	,
270376911	,
270376912	,
270376914	,
270376915	,
270376916	,
270376917	,
270376929	,
270376930	,
270376931	,
270376933	,
270376934	,
270376935	,
270376936	,
270376937	,
270376938	,
270376939	,
270376940	,
270376941	,
270376942	,
270376944	,
270376945	,
270376946	,
270376948	,
270376950	,
270376958	,
270376960	,
270376961	,
270376962	,
270376963	,
270376965 , 178519943 , 270366680 , 270377067 , 180021184 , 270374973 , 270374972 , 270379013 

)						
						AND pit.payment_type = '1' " . $whr . "

					" . $orderBy;

		$dataTable = PdoDataAccess::runquery($query, $whereParam);
		
		if ($_SESSION['UserID'] == 'jafarkhani') {
			//echo PdoDataAccess::GetLatestQueryString() ; die() ; 
		}
		
		if(count($dataTable) == 0 )
		{
			echo "<center><br><font style='color:red;font-weight:bold:bold;font-size:20px' > .گزارش هیچ نتیجه ای در بر ندارد</font></center>" ; 
			die() ; 
		}
				
		?>
		<style>
			.reportGenerator {border-collapse: collapse;border: 1px solid black;font-family: tahoma;font-size: 8pt;
							  text-align: center;width: 50%;padding: 2px;}
			.reportGenerator .header {color: white;font-weight: bold;background-color:#3865A1} 
			.reportGenerator td {border: 1px solid #555555;height: 20px;}
		</style>
		<?
		$t = 1;
		$qry = " select * from salary_item_types where salary_item_type_id =" . $_POST['SITID1'];
		$titLoan = PdoDataAccess::runquery($qry);  

		$qry2 = " select * from salary_item_types where salary_item_type_id =" . $_POST['SITID2'];
		$titSub = PdoDataAccess::runquery($qry2);

		$qry = " select bi.Title month_title 
				from  Basic_Info bi 
							where  bi.typeid = 41 AND InfoID = " . $_POST["pay_month"];
		$res = PdoDataAccess::runquery($qry);
		$month = $res[0]['month_title'];

		echo '<META http-equiv=Content-Type content="text/html; charset=UTF-8" ><body dir="rtl"><center>';
		echo "<center><table style='border:2px groove #9BB1CD;border-collapse:collapse;width:100%'><tr>
				<td width=60px><img src='/HumanResources/img/fum_symbol.jpg'></td>
				<td align='center' style='font-family:b titr;font-size:15px'>گزارش پس انداز و اقساط وام &nbsp; ".$month." ماه ".
                                  $_POST['pay_year']."  </td>				
				<td width='200px' align='center' style='font-family:tahoma;font-size:11px'>تاریخ تهیه گزارش : " 
			. DateModules::shNow() . "<br>";		
		echo "</td></tr></table>";      

		echo '<table  class="reportGenerator" style="text-align: right;width:100%!important" cellpadding="4" cellspacing="0"> ';

		if ($_POST['RepType'] == 0) {

			echo '<tr class="header">		
					<td  colspan="6" >مرکز هزینه : &nbsp; ' . $dataTable[0]['cost_center_id'] . ' - ' . $dataTable[0]['cost_title'] . '
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						&nbsp;&nbsp;&nbsp;  نوع نیروی انسانی :
								&nbsp; ' . $person_type . '	
				    </td>
					<td colspan="5" > مربوط به :  ' . $month . ' &nbsp;
						ماه
					</td>								
			  </tr>';

			echo '<tr class="header">					
						<td colspan="6" >عنوان کسور: &nbsp;' . $_POST['SITID2'] . "-" . $titSub[0]['print_title'] . '</td>
						<td colspan="5" >عنوان وام: &nbsp;' . $_POST['SITID1'] . "-" . $titLoan[0]['print_title'] . '</td>
					</tr>
					<tr style="color: white;font-weight: bold;background-color:#7C99BF">
						<td>ردیف</td>
						<td>شماره شناسایی</td>						
						<td>نام خانوادگی</td>
						<td>نام</td>
						<td>مبلغ ماهانه</td>
						<td>پس انداز</td>
						<td>قسط وام</td>
						<td>مانده وام</td>
						<td>شماره وام</td>
						<td>شماره قرارداد</td>
						<td>شماره حساب</td>
					</tr>';

			$sum_loan_instalment = 0;
			$sum_loan_remainder = 0;
			$sum_fixfraction_instalment = 0;
			$sum_fixfraction_remainder = 0;
			$fixfraction_instalment = $loan_instalment = "";
			$fixfraction_remainder = $loan_remainder = "";
			$stid = "";
			$loan_no = ""; $contract_no = "" ; 
			$cid = $dataTable[0]['cost_center_id'];
			$j = 0;

			for ($i = 0; $i < count($dataTable); $i++) {
				if ($cid != $dataTable[$i]['cost_center_id']) {

					$t = 1;
					echo "<tr style='background-color:#F0F8FF;font-family:b Titr;font-size: 9pt' >
							<td >جمع :</td> 
							<td colspan='4'>&nbsp;</td>
							<td>" . number_format($sum_fixfraction_instalment, 0, '.', ',') . "</td>
							<td>" . number_format($sum_fixfraction_remainder, 0, '.', ',') . "</td>
							<td>" . number_format($sum_loan_instalment, 0, '.', ',') . "</td>
							<td>" . number_format($sum_loan_remainder, 0, '.', ',') . "</td>
							<td colspan='2'>&nbsp;</td>
						 </tr></table>";

					echo '<hr style="page-break-after:always; visibility: hidden"><br><br>';
					echo "<center><table style='border:2px groove #9BB1CD;border-collapse:collapse;width:100%'><tr>
				<td width=60px><img src='/HumanResources/img/fum_symbol.jpg'></td>
				<td align='center' style='font-family:b titr;font-size:15px'>گزارش پس انداز و اقساط وام &nbsp; ".$month." ماه ".
                                  $_POST['pay_year']."  </td>				
				<td width='200px' align='center' style='font-family:tahoma;font-size:11px'>تاریخ تهیه گزارش : " 
			. DateModules::shNow() . "<br>";		
		echo "</td></tr></table>";  
					echo '<table  class="reportGenerator" style="text-align: right;width:100%!important" cellpadding="4" cellspacing="0">';
					echo '<tr class="header">		
							<td colspan="6" >مرکز هزینه : &nbsp; ' . $dataTable[$i]['cost_center_id'] . ' - ' . $dataTable[$i]['cost_title'] . '
									&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								&nbsp;&nbsp;&nbsp;  نوع نیروی انسانی :
								&nbsp; ' . $person_type . '								
							</td>
							<td colspan="5" > مربوط به :  ' . $month . ' &nbsp;
								ماه
							</td>								
						  </tr>';
					echo '  <tr class="header">					
								<td colspan="6" >عنوان کسور: &nbsp;' . $_POST['SITID2'] . "-" . $titSub[0]['print_title'] . '</td>
								<td colspan="5" >عنوان وام: &nbsp;' . $_POST['SITID1'] . "-" . $titLoan[0]['print_title'] . '</td>
							</tr>
							<tr style="color: white;font-weight: bold;background-color:#7C99BF">
								<td>ردیف</td>
								<td>شماره شناسایی</td>								
								<td>نام خانوادگی</td>
								<td>نام</td>
								<td>مبلغ ماهانه</td>
								<td>پس انداز</td>
								<td>قسط وام</td>
								<td>مانده وام</td>
								<td>شماره وام</td>
								<td>شماره قرارداد</td>
								<td>شماره حساب</td>
							</tr>';

					$sum_loan_instalment = 0;
					$sum_loan_remainder = 0;
					$sum_fixfraction_instalment = 0;
					$sum_fixfraction_remainder = 0;
					$fixfraction_instalment = $loan_instalment = "";
					$fixfraction_remainder = $loan_remainder = "";
					$cid = $dataTable[$i]['cost_center_id'];
					$j = 0;
				}
				if ($stid != $dataTable[$i]['staff_id']) {
					$loan_instalment = "";
					$loan_remainder = "";
					$fixfraction_instalment = "";
					$fixfraction_remainder = "";
					$loan_no = ""; $contract_no = "" ; 
				}
				if ($dataTable[$i]['param1'] == 'LOAN') {
					$loan_instalment = $dataTable[$i]['instalment'];
					$loan_remainder = $dataTable[$i]['remainder'];
					$sum_loan_instalment += $dataTable[$i]['instalment'];
					$sum_loan_remainder += $dataTable[$i]['remainder'];
					$loan_no = $dataTable[$i]['loan_no'];
					$contract_no = $dataTable[$i]['contract_no'];
					$stid = $dataTable[$i]['staff_id'];
				} else {

					$fixfraction_instalment = $dataTable[$i]['instalment'];
					$fixfraction_remainder = $dataTable[$i]['remainder'];
					$sum_fixfraction_instalment += $dataTable[$i]['instalment'];
					$sum_fixfraction_remainder += $dataTable[$i]['remainder'];
					$stid = $dataTable[$i]['staff_id'];
				}

				if (($i + 1) < count($dataTable) && $dataTable[$i]['staff_id'] != $dataTable[$i + 1]['staff_id']) {

					echo " <tr>
									<td>" . $t . "</td>
									<td>" . $dataTable[$i]['staff_id'] . "</td>									
									<td>" . $dataTable[$i]['plname'] . "</td>
									<td>" . $dataTable[$i]['pfname'] . "</td>
									<td>" . number_format($fixfraction_instalment, 0, '.', ',') . "</td>
									<td>" . number_format($fixfraction_remainder, 0, '.', ',') . "</td>
									<td>" . number_format($loan_instalment, 0, '.', ',') . "</td>
									<td>" . number_format($loan_remainder, 0, '.', ',') . "</td>
									<td>" . $loan_no . "</td>
									<td>" . $contract_no . "</td>
									<td>" . $dataTable[$i]['account_no'] . "</td>
								</tr>";
					$j++;
					$t++;
				}

				if ($j > 0 && $j % 35 == 0 && $cid == $dataTable[$i]['cost_center_id']) {

					echo '</table><hr style="page-break-after:always; visibility: hidden"><br><br>';
					echo "<center><table style='border:2px groove #9BB1CD;border-collapse:collapse;width:100%'><tr>
				<td width=60px><img src='/HumanResources/img/fum_symbol.jpg'></td>
				<td align='center' style='font-family:b titr;font-size:15px'>گزارش پس انداز و اقساط وام &nbsp; ".$month." ماه ".
                                  $_POST['pay_year']."  </td>				
				<td width='200px' align='center' style='font-family:tahoma;font-size:11px'>تاریخ تهیه گزارش : " 
			. DateModules::shNow() . "<br>";		
		echo "</td></tr></table>"; 
					echo '<table  class="reportGenerator" style="text-align: right;width:100%!important" cellpadding="4" cellspacing="0">
								<tr class="header">		
									<td colspan="6" >مرکز هزینه : &nbsp; ' . $dataTable[$i]['cost_center_id'] . ' - ' . $dataTable[$i]['cost_title'] . '
											&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
										&nbsp;&nbsp;&nbsp;  نوع نیروی انسانی :
										&nbsp; ' . $person_type . '	
									</td>
									<td colspan="5" > مربوط به :  ' . $month . ' &nbsp;
										ماه
									</td>								
								</tr>
								<tr class="header">					
									<td colspan="6" >عنوان کسور: &nbsp;' . $_POST['SITID2'] . "-" . $titSub[0]['print_title'] . '</td>
									<td colspan="5" >عنوان وام: &nbsp;' . $_POST['SITID1'] . "-" . $titLoan[0]['print_title'] . '</td>
								</tr>
								<tr style="color: white;font-weight: bold;background-color:#7C99BF">
									<td>ردیف</td>
									<td>شماره شناسایی</td>									
									<td>نام خانوادگی</td>
									<td>نام</td>
									<td>مبلغ ماهانه</td>
									<td>پس انداز</td>
									<td>قسط وام</td>
									<td>مانده وام</td>
									<td>شماره وام</td>
									<td>شماره قرارداد</td>
									<td>شماره حساب</td>
								</tr>';
					$j = 0;
				}
			}


			if (count($dataTable) > 1) {

				echo " <tr>
								<td>" . $t . "</td>
								<td>" . $dataTable[$i - 1]['staff_id'] . "</td>								
								<td>" . $dataTable[$i - 1]['plname'] . "</td>
								<td>" . $dataTable[$i - 1]['pfname'] . "</td>
								<td>" . number_format($fixfraction_instalment, 0, '.', ',') . "</td>
								<td>" . number_format($fixfraction_remainder, 0, '.', ',') . "</td>
								<td>" . number_format($loan_instalment, 0, '.', ',') . "</td>
								<td>" . number_format($loan_remainder, 0, '.', ',') . "</td>
								<td>" . $loan_no . "</td><td>" . $contract_no . "</td>
								<td>" . $dataTable[$i - 1]['account_no'] . "</td>
							</tr>";
			}
			echo "<tr style='background-color:#F0F8FF;font-family:b Titr;font-size: 9pt' >
							<td >جمع :</td> 
							<td colspan='4'>&nbsp;</td>
							<td>" . number_format($sum_fixfraction_instalment, 0, '.', ',') . "</td>
							<td>" . number_format($sum_fixfraction_remainder, 0, '.', ',') . "</td>
							<td>" . number_format($sum_loan_instalment, 0, '.', ',') . "</td>
							<td>" . number_format($sum_loan_remainder, 0, '.', ',') . "</td>
							<td colspan='2'>&nbsp;</td>
						 </tr></table>";
		} elseif ($_POST['RepType'] == 1) {
			
			echo '<tr class="header">					
						<td colspan="11" > مربوط به :  ' . $month . ' &nbsp;
							ماه
								&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
&nbsp;&nbsp;&nbsp;  نوع نیروی انسانی :
										&nbsp; ' . $person_type . '	
						</td>								
				 </tr> ';

			echo '<tr class="header">					
						<td colspan="6" >عنوان کسور: &nbsp;' . $_POST['SITID2'] . "-" . $titSub[0]['print_title'] . '</td>
						<td colspan="5" >عنوان وام: &nbsp;' . $_POST['SITID1'] . "-" . $titLoan[0]['print_title'] . '</td>
					</tr>
					<tr style="color: white;font-weight: bold;background-color:#7C99BF">
						<td>ردیف</td>
						<td>شماره شناسایی</td>						
						<td>نام خانوادگی</td>
						<td>نام</td>
						<td>مبلغ ماهانه</td>
						<td>پس انداز</td>
						<td>قسط وام</td>
						<td>مانده وام</td>
						<td>شماره وام</td>
						<td>شماره قرارداد</td>
						<td>شماره حساب</td>
					</tr>';

			$sum_loan_instalment = 0;
			$sum_loan_remainder = 0;
			$sum_fixfraction_instalment = 0;
			$sum_fixfraction_remainder = 0;
			$fixfraction_instalment = $loan_instalment = "";
			$fixfraction_remainder = $loan_remainder = "";
			$stid = "";
			$loan_no = ""; $contract_no = "" ; 
			for ($i = 0; $i < count($dataTable); $i++) {

				if ($stid != $dataTable[$i]['staff_id']) {
					$loan_instalment = "";
					$loan_remainder = "";
					$fixfraction_instalment = "";
					$fixfraction_remainder = "";
					$loan_no = "";
					$contract_no = "" ; 
				}
				if ($dataTable[$i]['param1'] == 'LOAN') {
					$loan_instalment = $dataTable[$i]['instalment'];
					$loan_remainder = $dataTable[$i]['remainder'];
					$sum_loan_instalment += $dataTable[$i]['instalment'];
					$sum_loan_remainder += $dataTable[$i]['remainder'];
					$loan_no = $dataTable[$i]['loan_no'];
					$contract_no = $dataTable[$i]['loan_no'];
					$stid = $dataTable[$i]['staff_id'];
				} else {

					$fixfraction_instalment = $dataTable[$i]['instalment'];
					$fixfraction_remainder = $dataTable[$i]['remainder'];
					$sum_fixfraction_instalment += $dataTable[$i]['instalment'];
					$sum_fixfraction_remainder += $dataTable[$i]['remainder'];
					$stid = $dataTable[$i]['staff_id'];
				}

				if (($i + 1) < count($dataTable) && $dataTable[$i]['staff_id'] != $dataTable[$i + 1]['staff_id']) {

					echo " <tr>
							<td>" . $t . "</td>
							<td>" . $dataTable[$i]['staff_id'] . "</td>							
							<td>" . $dataTable[$i]['plname'] . "</td>
							<td>" . $dataTable[$i]['pfname'] . "</td>
							<td>" . number_format($fixfraction_instalment, 0, '.', ',') . "</td>
							<td>" . number_format($fixfraction_remainder, 0, '.', ',') . "</td>
							<td>" . number_format($loan_instalment, 0, '.', ',') . "</td>
							<td>" . number_format($loan_remainder, 0, '.', ',') . "</td>
							<td>" . $loan_no . "</td><td>" . $contract_no . "</td>
							<td>" . $dataTable[$i]['account_no'] . "</td>
						</tr>";
					$t++;
				}

				if ($i > 0 && $i % 50 == 0) {
					echo '</table><hr style="page-break-after:always; visibility: hidden"><br><br>';
					echo "<center><table style='border:2px groove #9BB1CD;border-collapse:collapse;width:100%'><tr>
				<td width=60px><img src='/HumanResources/img/fum_symbol.jpg'></td>
				<td align='center' style='font-family:b titr;font-size:15px'>گزارش پس انداز و اقساط وام &nbsp; ".$month." ماه ".
                                  $_POST['pay_year']."  </td>				
				<td width='200px' align='center' style='font-family:tahoma;font-size:11px'>تاریخ تهیه گزارش : " 
			. DateModules::shNow() . "<br>";		
		echo "</td></tr></table>"; 
					echo '<table  class="reportGenerator" style="text-align: right;width:100%!important" cellpadding="4" cellspacing="0">
								<tr class="header">					
									<td colspan="11" > مربوط به :  ' . $month . ' &nbsp;
										ماه
											&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
										&nbsp;&nbsp;&nbsp;  نوع نیروی انسانی :
										&nbsp; ' . $person_type . '	
									</td>								
								</tr>
								<tr class="header">					
									<td colspan="6" >عنوان کسور: &nbsp;' . $_POST['SITID2'] . "-" . $titSub[0]['print_title'] . '</td>
									<td colspan="5" >عنوان وام: &nbsp;' . $_POST['SITID1'] . "-" . $titLoan[0]['print_title'] . '</td>
								</tr>
								<tr style="color: white;font-weight: bold;background-color:#7C99BF">
									<td>ردیف</td>
									<td>شماره شناسایی</td>									
									<td>نام خانوادگی</td>
									<td>نام</td>
									<td>مبلغ ماهانه</td>
									<td>پس انداز</td>
									<td>قسط وام</td>
									<td>مانده وام</td>
									<td>شماره وام</td><td>شماره قرارداد</td>
									<td>شماره حساب</td>
								</tr>';
				}
			}


			if (count($dataTable) > 1) {

				echo " <tr>
								<td>" . $t . "</td>
								<td>" . $dataTable[$i - 1]['staff_id'] . "</td>								
								<td>" . $dataTable[$i - 1]['plname'] . "</td>
								<td>" . $dataTable[$i - 1]['pfname'] . "</td>
								<td>" . number_format($fixfraction_instalment, 0, '.', ',') . "</td>
								<td>" . number_format($fixfraction_remainder, 0, '.', ',') . "</td>
								<td>" . number_format($loan_instalment, 0, '.', ',') . "</td>
								<td>" . number_format($loan_remainder, 0, '.', ',') . "</td>
								<td>" . $loan_no . "</td><td>" . $contract_no . "</td>
								<td>" . $dataTable[$i - 1]['account_no'] . "</td>
							</tr>";
				$t++;
			}

			echo "<tr style='background-color:#F0F8FF;font-family:b Titr;font-size: 9pt' >
							<td >جمع :</td> 
							<td colspan='4'>&nbsp;</td>
							<td>" . number_format($sum_fixfraction_instalment, 0, '.', ',') . "</td>
							<td>" . number_format($sum_fixfraction_remainder, 0, '.', ',') . "</td>
							<td>" . number_format($sum_loan_instalment, 0, '.', ',') . "</td>
							<td>" . number_format($sum_loan_remainder, 0, '.', ',') . "</td>
							<td colspan='2'>&nbsp;</td>
						 </tr></table>";
		}
	} else if ($_POST['RepFormat'] == 0 || $_POST['RepFormat'] == 1) {
				
		if ($_POST['RepFormat'] == 0 ) {			
			$paramType = 'FIX_FRACTION';						
			if(isset($_POST["SITID2"])){
				$qry = "select count(*) cn  from SalaryItemAccess where UserID='" . $_SESSION["UserID"] . "' AND SalaryItemTypeID in (" . $_POST["SITID2"] . ")";
				$res = PdoDataAccess::runquery($qry);  
				$sid = $_POST["SITID2"];
			}
					
		}
		if ($_POST['RepFormat'] == 1 ) {
			$paramType = 'LOAN';
			if(isset($_POST["SITID1"])){
				$qry = "select count(*) cn from SalaryItemAccess where UserID='" . $_SESSION["UserID"] . "' AND SalaryItemTypeID in (" . $_POST["SITID1"] . ")";
				$res = PdoDataAccess::runquery($qry);  
				$sid = $_POST["SITID1"];
			}
		}
								
		if( $res[0]['cn'] == 0 )
		{
			$whr .= " AND c.cost_center_id in (" . manage_access::getValidCostCenters() . ") ";

		} 
		
		if ($_POST['RepType'] == 0)
			$orderBy = " order by cost_center_id,itm.salary_item_type_id,p.plname,p.pfname ";

		elseif ($_POST['RepType'] == 1)
			$orderBy = " order by itm.salary_item_type_id,p.plname,p.pfname ";
		
		$SITMWHRE = " " ; 
		
		if(( isset($_POST["SITID2"]) && $_POST["SITID2"] > 0 ) || (isset($_POST["SITID1"]) &&  $_POST["SITID1"] > 0 ))
		{
			$SITMWHRE = "AND itm.salary_item_type_id=" . $sid  ; 
			
		}

		$query = " select   c.cost_center_id,
							c.title cost_title ,
							s.staff_id,							
							p.pfname,
							p.plname,
							pss.loan_no,pss.contract_no , 
							itm.salary_item_type_id,
							itm.full_title,
							pit.get_value instalment,
							pit.param4 remainder,
							pa.account_no account_no ,
							pit.param1

					from " . $DB . "payment_items pit
												INNER JOIN " . $DB . "payments pa
													ON(pa.pay_year = pit.pay_year AND pa.pay_month = pit.pay_month AND 
													   pa.staff_id = pit.staff_id AND pa.payment_type = pit.payment_type)
												INNER JOIN " . $DB . "staff s
													ON pit.staff_id = s.staff_id
												INNER JOIN  " . $DB . "persons p
													ON (s.PersonID = p.PersonID)
												INNER JOIN  " . $DB . "cost_centers c
													ON (pit.cost_center_id = c.cost_center_id)
												INNER JOIN  " . $DB . "salary_item_types itm
													ON (itm.salary_item_type_id = pit.salary_item_type_id)
												LEFT OUTER JOIN  " . $DB . "person_subtracts pss
													ON (pit.param2 = pss.subtract_id)


					where pa.payment_type = ".$_POST['PayType']." AND pit.param1 = '" . $paramType . "' $SITMWHRE  AND 
						  pit.pay_month=" . $_POST["pay_month"] . " AND
						  pit.pay_year=" . $_POST["pay_year"] . " AND
	pit.param2 not in (
	
	270376904	,
270376908	,
270376909	,
270376911	,
270376912	,
270376914	,
270376915	,
270376916	,
270376917	,
270376929	,
270376930	,
270376931	,
270376933	,
270376934	,
270376935	,
270376936	,
270376937	,
270376938	,
270376939	,
270376940	,
270376941	,
270376942	,
270376944	,
270376945	,
270376946	,
270376948	,
270376950	,
270376958	,
270376960	,
270376961	,
270376962	,
270376963	,
270376965 , 178519943 , 270366680 , 270377067 , 180021184 , 270374973 , 270374972 , 270379013 
	
	) AND 
						  pit.payment_type = 1 " . $whr . " " . $orderBy;

		$dataTable = PdoDataAccess::runquery($query, $whereParam);
		
	//	echo PdoDataAccess::GetLatestQueryString() ; die();
		
		if(count($dataTable) == 0 )
		{
			echo "<center><br><font style='color:red;font-weight:bold:bold;font-size:20px' > .گزارش هیچ نتیجه ای در بر ندارد</font></center>" ; 
			die() ; 
		}
		if ($_SESSION['UserID'] == 'jafarkhani') {
			//echo PdoDataAccess::GetLatestQueryString() ; die() ; 
		}
		?>
		<style>
			.reportGenerator {border-collapse: collapse;border: 1px solid black;font-family: tahoma;font-size: 8pt;
							  text-align: center;width: 50%;padding: 2px;}
			.reportGenerator .header {color: white;font-weight: bold;background-color:#3865A1} 
			.reportGenerator td {border: 1px solid #555555;height: 20px;}
		</style>
		<?
		$t = 1;
		if ($_POST['RepFormat'] == 0 && isset($_POST['SITID2'])) {
			$qry2 = " select * from salary_item_types where salary_item_type_id =" . $_POST['SITID2'];
			$titSub = PdoDataAccess::runquery($qry2);
		} else if ($_POST['RepFormat'] == 1 && isset($_POST['SITID1'])) {
			$qry = " select * from salary_item_types where salary_item_type_id =" . $_POST['SITID1'];
			$titLoan = PdoDataAccess::runquery($qry);  // 	
		}
		$qry = " select bi.Title month_title 
				from  Basic_Info bi 
							where  bi.typeid = 41 AND InfoID = " . $_POST["pay_month"];
		$res = PdoDataAccess::runquery($qry);
		$month = $res[0]['month_title'];

		echo '<META http-equiv=Content-Type content="text/html; charset=UTF-8" ><body dir="rtl"><center>';
		echo "<center><table style='border:2px groove #9BB1CD;border-collapse:collapse;width:100%'><tr>
				<td width=60px><img src='/HumanResources/img/fum_symbol.jpg'></td>
				<td align='center' style='font-family:b titr;font-size:15px'>گزارش پس انداز و اقساط وام &nbsp; ".$month." ماه ".
                                  $_POST['pay_year']."  </td>				
				<td width='200px' align='center' style='font-family:tahoma;font-size:11px'>تاریخ تهیه گزارش : " 
			. DateModules::shNow() . "<br>";		
		echo "</td></tr></table>"; 
		echo '<table  class="reportGenerator" style="text-align: right;width:100%!important" cellpadding="4" cellspacing="0"> ';

		if ($_POST['RepType'] == 0) {
			echo '<tr class="header">		
					<td  colspan="6" >مرکز هزینه : &nbsp; ' . $dataTable[0]['cost_center_id'] . ' - ' . $dataTable[0]['cost_title'] . '
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						&nbsp;&nbsp;&nbsp;  نوع نیروی انسانی :
								&nbsp; ' . $person_type . '	
				    </td>
					<td colspan="4" > مربوط به :  ' . $month . ' &nbsp;
						ماه
					</td>								
			  </tr>';

			echo '<tr class="header">';
			if ($_POST['RepFormat'] == 0)  
				echo '<td colspan="10" >عنوان کسور: &nbsp;' . $dataTable[0]['salary_item_type_id'] . "-" . $dataTable[0]['full_title'] . '</td>';

			if ($_POST['RepFormat'] == 1)
				echo '<td colspan="10" >عنوان وام: &nbsp;' . $dataTable[0]['salary_item_type_id'] . "-" . $dataTable[0]['full_title'] . '</td>';

			echo '</tr>
					<tr style="color: white;font-weight: bold;background-color:#7C99BF">
						<td>ردیف</td>
						<td>شماره شناسایی</td>						
						<td>نام خانوادگی</td>
						<td>نام</td> ';
			if ($_POST['RepFormat'] == 0) {
				echo '<td>پس انداز</td>
							 <td>مبلغ ماهانه</td> ';
			}
			if ($_POST['RepFormat'] == 1) {
				echo '<td>قسط وام</td>
							 <td>مانده وام</td>
							 <td>شماره وام</td><td>شماره قرارداد</td> ';
			}
			echo '<td colspan="3">شماره حساب</td>
							</tr>';
			if ($_SESSION['UserID'] == 'jafarkhani') {
				// echo "iuiui" ; die() ; 
			}
			$sum_loan_instalment = 0;
			$sum_loan_remainder = 0;
			$sum_fixfraction_instalment = 0;
			$sum_fixfraction_remainder = 0;
			$fixfraction_instalment = $loan_instalment = "";
			$fixfraction_remainder = $loan_remainder = "";
			$stid = "";
			$loan_no = ""; $contract_no = "" ; 
			$cid = $dataTable[0]['cost_center_id'];			
			$sitem = $dataTable[0]['salary_item_type_id'];		 
			$j = 0;
			
			for ($i = 0; $i < count($dataTable); $i++) {
				if ($cid != $dataTable[$i]['cost_center_id'] || $sitem !=  $dataTable[$i]['salary_item_type_id'] ) {

					$t = 1;

					echo "<tr style='background-color:#F0F8FF;font-family:b Titr;font-size: 9pt' >
							<td >جمع :</td> 
							<td colspan='4'>&nbsp;</td>";
					if ($_POST['RepFormat'] == 0) {
						echo "<td>" . number_format($sum_fixfraction_remainder, 0, '.', ',') . "</td>
							<td>" . number_format($sum_fixfraction_instalment, 0, '.', ',') . "</td>";
					}
					if ($_POST['RepFormat'] == 1) {
						echo "<td>" . number_format($sum_loan_instalment, 0, '.', ',') . "</td>
							  <td>" . number_format($sum_loan_remainder, 0, '.', ',') . "</td>";
					}

					echo "<td colspan='3'>&nbsp;</td>
						 </tr></table>";

					echo '<hr style="page-break-after:always; visibility: hidden"><br><br>';
					echo "<center><table style='border:2px groove #9BB1CD;border-collapse:collapse;width:100%'><tr>
				<td width=60px><img src='/HumanResources/img/fum_symbol.jpg'></td>
				<td align='center' style='font-family:b titr;font-size:15px'>گزارش پس انداز و اقساط وام &nbsp; ".$month." ماه ".
                                  $_POST['pay_year']."  </td>				
				<td width='200px' align='center' style='font-family:tahoma;font-size:11px'>تاریخ تهیه گزارش : " 
			. DateModules::shNow() . "<br>";		
		echo "</td></tr></table>"; 
					echo '<table  class="reportGenerator" style="text-align: right;width:100%!important" cellpadding="4" cellspacing="0">';
					echo '<tr class="header">		
							<td colspan="6" >مرکز هزینه : &nbsp; ' . $dataTable[$i]['cost_center_id'] . ' - ' . $dataTable[$i]['cost_title'] . '
									&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								&nbsp;&nbsp;&nbsp;  نوع نیروی انسانی :
								&nbsp; ' . $person_type . '								
							</td>
							<td colspan="5" > مربوط به :  ' . $month . ' &nbsp;
								ماه
							</td>								
						  </tr>';

					echo '  <tr class="header">	';
										
					if ($_POST['RepFormat'] == 0)  
						echo '<td colspan="10" >عنوان کسور: &nbsp;' . $dataTable[$i]['salary_item_type_id'] . "-" . $dataTable[$i]['full_title'] . '</td>';

					if ($_POST['RepFormat'] == 1)
						echo '<td colspan="10" >عنوان وام: &nbsp;' . $dataTable[$i]['salary_item_type_id'] . "-" . $dataTable[$i]['full_title'] . '</td>';

					echo '</tr>
							<tr style="color: white;font-weight: bold;background-color:#7C99BF">
								<td>ردیف</td>
						<td>شماره شناسایی</td>						
						<td>نام خانوادگی</td>
						<td>نام</td> ';
					if ($_POST['RepFormat'] == 0) {
						echo '<td>پس انداز</td>
							 <td>مبلغ ماهانه</td> ';
					}
					if ($_POST['RepFormat'] == 1) {
						echo '<td>قسط وام</td>
							 <td>مانده وام</td>
							 <td>شماره وام</td><td>شماره قرارداد</td> ';
					}
					echo '<td colspan="3" >شماره حساب</td>
							</tr>';

					$sum_loan_instalment = 0;
					$sum_loan_remainder = 0;
					$sum_fixfraction_instalment = 0;
					$sum_fixfraction_remainder = 0;
					$fixfraction_instalment = $loan_instalment = "";
					$fixfraction_remainder = $loan_remainder = "";
					$cid = $dataTable[$i]['cost_center_id'];
					$sitem = $dataTable[$i]['salary_item_type_id'];
					$j = 0;
				}
				//......
				if ($stid != $dataTable[$i]['staff_id']) {
					$loan_instalment = "";
					$loan_remainder = "";
					$fixfraction_instalment = "";
					$fixfraction_remainder = "";
					$loan_no = ""; $contract_no = ""; 
				}
				if ($dataTable[$i]['param1'] == 'LOAN') {
					$loan_instalment = $dataTable[$i]['instalment'];
					$loan_remainder = $dataTable[$i]['remainder'];
					$sum_loan_instalment += $dataTable[$i]['instalment'];
					$sum_loan_remainder += $dataTable[$i]['remainder'];
					$loan_no = $dataTable[$i]['loan_no'];
					$contract_no = $dataTable[$i]['contract_no'];
					$stid = $dataTable[$i]['staff_id'];
				} else {

					$fixfraction_instalment = $dataTable[$i]['instalment']; //bahar
					$fixfraction_remainder = $dataTable[$i]['remainder'];
					$sum_fixfraction_instalment += $dataTable[$i]['instalment'];
					$sum_fixfraction_remainder += $dataTable[$i]['remainder'];
					$stid = $dataTable[$i]['staff_id'];
				}

				if ( count($dataTable) == 1 || (($i + 1) < count($dataTable) && ($dataTable[$i]['staff_id'] != $dataTable[$i + 1]['staff_id'] ||  
					($dataTable[$i]['staff_id'] == $dataTable[$i + 1]['staff_id'] && $dataTable[$i]['salary_item_type_id'] != $dataTable[$i + 1]['salary_item_type_id'] )))) {

					echo " <tr>
									<td>" . $t . "</td>
									<td>" . $dataTable[$i]['staff_id'] . "</td>									
									<td>" . $dataTable[$i]['plname'] . "</td>
									<td>" . $dataTable[$i]['pfname'] . "</td> ";
					if ($_POST['RepFormat'] == 0) {
						echo "<td>" . number_format($fixfraction_remainder, 0, '.', ',') . "</td>
									 <td>" . number_format($fixfraction_instalment, 0, '.', ',') . "</td>";
					}
					if ($_POST['RepFormat'] == 1) {
						echo "<td>" . number_format($loan_instalment, 0, '.', ',') . "</td>
									<td>" . number_format($loan_remainder, 0, '.', ',') . "</td>
									<td>" . $loan_no . "</td><td>" . $contract_no . "</td>";
					}
					echo "<td colspan='3' >" . $dataTable[$i]['account_no'] . "</td>
								</tr>";
					$j++;
					$t++;
				}

				if ($j > 0 && $j % 37 == 0 && $cid == $dataTable[$i]['cost_center_id'] && $sitem ==  $dataTable[$i]['salary_item_type_id'] ) {

					echo '</table><hr style="page-break-after:always; visibility: hidden"><br><br>';
					echo "<center><table style='border:2px groove #9BB1CD;border-collapse:collapse;width:100%'><tr>
				<td width=60px><img src='/HumanResources/img/fum_symbol.jpg'></td>
				<td align='center' style='font-family:b titr;font-size:15px'>گزارش پس انداز و اقساط وام &nbsp; ".$month." ماه ".
                                  $_POST['pay_year']."  </td>				
				<td width='200px' align='center' style='font-family:tahoma;font-size:11px'>تاریخ تهیه گزارش : " 
			. DateModules::shNow() . "<br>";		
		echo "</td></tr></table>"; 
					echo '<table  class="reportGenerator" style="text-align: right;width:100%!important" cellpadding="4" cellspacing="0">
								<tr class="header">		
									<td colspan="6" >مرکز هزینه : &nbsp; ' . $dataTable[$i]['cost_center_id'] . ' - ' . $dataTable[$i]['cost_title'] . '
											&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
										&nbsp;&nbsp;&nbsp;  نوع نیروی انسانی :
										&nbsp; ' . $person_type . '	
									</td>
									<td colspan="5" > مربوط به :  ' . $month . ' &nbsp;
										ماه
									</td>								
								</tr>
								<tr class="header">';
										
					if ($_POST['RepFormat'] == 0)  
						echo '<td colspan="10" >عنوان کسور: &nbsp;' . $dataTable[$i]['salary_item_type_id'] . "-" . $dataTable[$i]['full_title'] . '</td>';

					if ($_POST['RepFormat'] == 1)
						echo '<td colspan="10" >عنوان وام: &nbsp;' . $dataTable[$i]['salary_item_type_id'] . "-" . $dataTable[$i]['full_title'] . '</td>';

					
					echo '</tr>
								<tr style="color: white;font-weight: bold;background-color:#7C99BF">
									<td>ردیف</td>
									<td>شماره شناسایی</td>									
									<td>نام خانوادگی</td>
									<td>نام</td>';
					if ($_POST['RepFormat'] == 0) {
						echo '<td>پس انداز</td>
										<td>مبلغ ماهانه</td> ';
					}
					if ($_POST['RepFormat'] == 1) {
						echo '<td>قسط وام</td>
										<td>مانده وام</td>
										<td>شماره وام</td><td>شماره قرارداد</td> ';
					}
					echo '<td colspan="3">شماره حساب</td>									
									</tr>';
					$j = 0;
				}
				///
			}


			if (count($dataTable) > 1) {

				echo " <tr>
								<td>" . $t . "</td>
								<td>" . $dataTable[$i - 1]['staff_id'] . "</td>								
								<td>" . $dataTable[$i - 1]['plname'] . "</td>
								<td>" . $dataTable[$i - 1]['pfname'] . "</td>";
				if ($_POST['RepFormat'] == 0) {
					echo "<td>" . number_format($fixfraction_remainder, 0, '.', ',') . "</td>
									<td>" . number_format($fixfraction_instalment, 0, '.', ',') . "</td>";
				}
				if ($_POST['RepFormat'] == 1) {
					echo "<td>" . number_format($loan_instalment, 0, '.', ',') . "</td>
									 <td>" . number_format($loan_remainder, 0, '.', ',') . "</td>
									 <td>" . $loan_no . "</td><td>" . $contract_no . "</td>";
				}
				echo "<td colspan='3' >" . $dataTable[$i - 1]['account_no'] . "</td>
							  </tr>";
			}
			echo "<tr style='background-color:#F0F8FF;font-family:b Titr;font-size: 9pt' >
							<td >جمع :</td> 
							<td colspan='4'>&nbsp;</td>";
			if ($_POST['RepFormat'] == 0) {
				echo "<td>" . number_format($sum_fixfraction_remainder, 0, '.', ',') . "</td>
							<td>" . number_format($sum_fixfraction_instalment, 0, '.', ',') . "</td>";
			}
			if ($_POST['RepFormat'] == 1) {
				echo "<td>" . number_format($sum_loan_instalment, 0, '.', ',') . "</td>
					  <td>" . number_format($sum_loan_remainder, 0, '.', ',') . "</td>";
			}

			echo "<td colspan='3'>&nbsp;</td>
						 </tr></table>";
		} 
		
		elseif ($_POST['RepType'] == 1) {
						
			echo '<tr class="header">					
						<td colspan="11" > مربوط به :  ' . $month . ' &nbsp;
							ماه
								&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					&nbsp;&nbsp;&nbsp;  نوع نیروی انسانی :
										&nbsp; ' . $person_type . '	
						</td>								
				 </tr> ';

			echo '<tr class="header">' ;	
								
				if ($_POST['RepFormat'] == 0)  
						echo '<td colspan="10" >عنوان کسور: &nbsp;' . $dataTable[0]['salary_item_type_id'] . "-" . $dataTable[0]['full_title'] . '</td>';

				if ($_POST['RepFormat'] == 1)
						echo '<td colspan="10" >عنوان وام: &nbsp;' . $dataTable[0]['salary_item_type_id'] . "-" . $dataTable[0]['full_title'] . '</td>';
				
			echo '  </tr>
					<tr style="color: white;font-weight: bold;background-color:#7C99BF">
						<td>ردیف</td>
						<td>شماره شناسایی</td>						
						<td>نام خانوادگی</td> 
						<td>نام</td>' ;
			if ($_POST['RepFormat'] == 0) {
				echo '<td>پس انداز</td> 
					<td>مبلغ ماهانه</td>' ;
			}		
			if ($_POST['RepFormat'] == 1) {
				  echo '<td>قسط وام</td>
						<td>مانده وام</td>
						<td>شماره وام</td><td>شماره قرارداد</td>';
			}
			echo '<td>شماره حساب</td>
			  	  </tr>';

			$sum_loan_instalment = 0;
			$sum_loan_remainder = 0;
			$sum_fixfraction_instalment = 0;
			$sum_fixfraction_remainder = 0;
			$fixfraction_instalment = $loan_instalment = "";
			$fixfraction_remainder = $loan_remainder = "";
			$stid = "";
			$loan_no = ""; $contract_no = "" ; 
			$sitem = $dataTable[0]['salary_item_type_id'];	
			$j = 0;
			
			for ($i = 0; $i < count($dataTable); $i++) {
			
				
				if ($sitem !=  $dataTable[$i]['salary_item_type_id'] ) {

					$t = 1;

					echo "<tr style='background-color:#F0F8FF;font-family:b Titr;font-size: 9pt' >
							<td >جمع :</td> 
							<td colspan='4'>&nbsp;</td>";
					if ($_POST['RepFormat'] == 0) {
				
						echo "<td>" . number_format($sum_fixfraction_remainder, 0, '.', ',') . "</td>
							  <td>" . number_format($sum_fixfraction_instalment, 0, '.', ',') . "</td>";
					}
					if ($_POST['RepFormat'] == 1) {
						echo "<td>" . number_format($sum_loan_instalment, 0, '.', ',') . "</td>
							  <td>" . number_format($sum_loan_remainder, 0, '.', ',') . "</td>";
					}

					echo "<td colspan='3'>&nbsp;</td>
						 </tr></table>";

					echo '<hr style="page-break-after:always; visibility: hidden"><br><br>';
					echo "<center><table style='border:2px groove #9BB1CD;border-collapse:collapse;width:100%'><tr>
				<td width=60px><img src='/HumanResources/img/fum_symbol.jpg'></td>
				<td align='center' style='font-family:b titr;font-size:15px'>گزارش پس انداز و اقساط وام &nbsp; ".$month." ماه ".
                                  $_POST['pay_year']."  </td>				
				<td width='200px' align='center' style='font-family:tahoma;font-size:11px'>تاریخ تهیه گزارش : " 
			. DateModules::shNow() . "<br>";		
		echo "</td></tr></table>"; 
					echo '<table  class="reportGenerator" style="text-align: right;width:100%!important" cellpadding="4" cellspacing="0">
								<tr class="header">					
									<td colspan="11" > مربوط به :  ' . $month . ' &nbsp;
										ماه
											&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
										&nbsp;&nbsp;&nbsp;  نوع نیروی انسانی :
										&nbsp; ' . $person_type . '	
									</td>								
								</tr>
								<tr class="header">	';				
					if ($_POST['RepFormat'] == 0)  
						echo '<td colspan="10" >عنوان کسور: &nbsp;' . $dataTable[$i]['salary_item_type_id'] . "-" . $dataTable[$i]['full_title'] . '</td>';

					if ($_POST['RepFormat'] == 1)
						echo '<td colspan="10" >عنوان وام: &nbsp;' . $dataTable[$i]['salary_item_type_id'] . "-" . $dataTable[$i]['full_title'] . '</td>';

					echo '</tr>
							<tr style="color: white;font-weight: bold;background-color:#7C99BF">
								<td>ردیف</td>
						<td>شماره شناسایی</td>						
						<td>نام خانوادگی</td>
						<td>نام</td> ';
					if ($_POST['RepFormat'] == 0) {
						echo '<td>پس انداز</td>
							 <td>مبلغ ماهانه</td> ';
					}
					if ($_POST['RepFormat'] == 1) {
						echo '<td>قسط وام</td>
							 <td>مانده وام</td>
							 <td>شماره وام</td><td>شماره قرارداد</td> ';
					}
					echo '<td colspan="3" >شماره حساب</td>
							</tr>';

					$sum_loan_instalment = 0;
					$sum_loan_remainder = 0;
					$sum_fixfraction_instalment = 0;
					$sum_fixfraction_remainder = 0;
					$fixfraction_instalment = $loan_instalment = "";
					$fixfraction_remainder = $loan_remainder = "";					
					$sitem = $dataTable[$i]['salary_item_type_id'];
					$j = 0;
				}

				if ($stid != $dataTable[$i]['staff_id']) {
					$loan_instalment = "";
					$loan_remainder = "";
					$fixfraction_instalment = "";
					$fixfraction_remainder = "";
					$loan_no = ""; $contract_no = "" ; 
				}
				if ($dataTable[$i]['param1'] == 'LOAN') {
					$loan_instalment = $dataTable[$i]['instalment'];
					$loan_remainder = $dataTable[$i]['remainder'];
					$sum_loan_instalment += $dataTable[$i]['instalment'];
					$sum_loan_remainder += $dataTable[$i]['remainder'];
					$loan_no = $dataTable[$i]['loan_no'];
					$contract_no = $dataTable[$i]['contract_no'];
					$stid = $dataTable[$i]['staff_id'];
				} else {

					$fixfraction_instalment = $dataTable[$i]['instalment'];
					$fixfraction_remainder = $dataTable[$i]['remainder'];
					$sum_fixfraction_instalment += $dataTable[$i]['instalment'];
					$sum_fixfraction_remainder += $dataTable[$i]['remainder'];
					$stid = $dataTable[$i]['staff_id'];
				}

				if (count($dataTable) == 1 || (($i + 1) < count($dataTable) && ($dataTable[$i]['staff_id'] != $dataTable[$i + 1]['staff_id'] ||  
					($dataTable[$i]['staff_id'] == $dataTable[$i + 1]['staff_id'] && $dataTable[$i]['salary_item_type_id'] != $dataTable[$i + 1]['salary_item_type_id'] )))) {

					echo " <tr>
							<td>" . $t . "</td>
							<td>" . $dataTable[$i]['staff_id'] . "</td>							
							<td>" . $dataTable[$i]['plname'] . "</td>
							<td>" . $dataTable[$i]['pfname'] . "</td>";
					
					if ($_POST['RepFormat'] == 0) {
						 echo "<td>" .number_format($fixfraction_remainder, 0, '.', ','). "</td>
						       <td>" .number_format($fixfraction_instalment, 0, '.', ','). "</td>";
					}
					if ($_POST['RepFormat'] == 1) {
						echo "<td>" . number_format($loan_instalment, 0, '.', ',') . "</td>
							  <td>" . number_format($loan_remainder, 0, '.', ',') . "</td>
							  <td>" . $loan_no . "</td><td>" . $contract_no . "</td> " ; 
						
							 }
						echo  "<td>" . $dataTable[$i]['account_no'] . "</td>
						</tr>";
					$t++;
					$j++;
				}
				
				if ($j > 0 && $j % 38 == 0 &&  $sitem ==  $dataTable[$i]['salary_item_type_id'] ) {

					echo '</table><hr style="page-break-after:always; visibility: hidden"><br><br>';
					echo "<center><table style='border:2px groove #9BB1CD;border-collapse:collapse;width:100%'><tr>
				<td width=60px><img src='/HumanResources/img/fum_symbol.jpg'></td>
				<td align='center' style='font-family:b titr;font-size:15px'>گزارش پس انداز و اقساط وام &nbsp; ".$month." ماه ".
                                  $_POST['pay_year']."  </td>				
				<td width='200px' align='center' style='font-family:tahoma;font-size:11px'>تاریخ تهیه گزارش : " 
			. DateModules::shNow() . "<br>";		
		echo "</td></tr></table>"; 
					echo '<table  class="reportGenerator" style="text-align: right;width:100%!important" cellpadding="4" cellspacing="0">
								<tr class="header">					
									<td colspan="11" > مربوط به :  ' . $month . ' &nbsp;
										ماه
											&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
										&nbsp;&nbsp;&nbsp;  نوع نیروی انسانی :
										&nbsp; ' . $person_type . '	
									</td>								
								</tr>
								<tr class="header">	';				
					if ($_POST['RepFormat'] == 0)  
						echo '<td colspan="10" >عنوان کسور: &nbsp;' . $dataTable[$i]['salary_item_type_id'] . "-" . $dataTable[$i]['full_title'] . '</td>';

					if ($_POST['RepFormat'] == 1)
						echo '<td colspan="10" >عنوان وام: &nbsp;' . $dataTable[$i]['salary_item_type_id'] . "-" . $dataTable[$i]['full_title'] . '</td>';
					echo '</tr>
								<tr style="color: white;font-weight: bold;background-color:#7C99BF">
									<td>ردیف</td>
									<td>شماره شناسایی</td>									
									<td>نام خانوادگی</td>
									<td>نام</td>' ;
							if ($_POST['RepFormat'] == 0) {
								echo '<td>پس انداز</td> 
									<td>مبلغ ماهانه</td>' ;
							}		
							if ($_POST['RepFormat'] == 1) {
								echo '<td>قسط وام</td>
										<td>مانده وام</td>
										<td>شماره وام</td><td>شماره قرارداد</td>';
							}
							echo '<td>شماره حساب</td>
								</tr>';
					$j = 0;
				}
				
			}

			if (count($dataTable) > 1) {
				
				  echo " <tr>
								<td>" . $t . "</td>
								<td>" . $dataTable[$i - 1]['staff_id'] . "</td>								
								<td>" . $dataTable[$i - 1]['plname'] . "</td>
								<td>" . $dataTable[$i - 1]['pfname'] . "</td>";
					if ($_POST['RepFormat'] == 0) {
							echo
								"<td>" . number_format($fixfraction_remainder, 0, '.', ',') . "</td>
								 <td>" . number_format($fixfraction_instalment, 0, '.', ',') . "</td>"; 
							
								}
					if ($_POST['RepFormat'] == 1) {			
							echo "<td>" . number_format($loan_instalment, 0, '.', ',') . "</td>
								<td>" . number_format($loan_remainder, 0, '.', ',') . "</td>
								<td>" . $loan_no . "</td><td>" . $contract_no . "</td>"; 
							   
								}
					echo "<td>" . $dataTable[$i - 1]['account_no'] . "</td>
						  </tr>";
				$t++;
			}

			echo "<tr style='background-color:#F0F8FF;font-family:b Titr;font-size: 9pt' >
							<td >جمع :</td> 
							<td colspan='4'>&nbsp;</td>" ;
					if ($_POST['RepFormat'] == 0) {		
						echo   "<td>" . number_format($sum_fixfraction_remainder, 0, '.', ',') . "</td>
								<td>" . number_format($sum_fixfraction_instalment, 0, '.', ',') . "</td>" ; 
					}
					if ($_POST['RepFormat'] == 1) {			
						echo "<td>" . number_format($sum_loan_instalment, 0, '.', ',') . "</td>
					 		  <td>" . number_format($sum_loan_remainder, 0, '.', ',') . "</td>";
					}
					
					echo "<td colspan='2'>&nbsp;</td>
						  </tr></table>";
		}
		
	}
}
?>
<form id="mainForm">
    <center>
        <div id="mainpanel"></div>
    </center>    
</form>