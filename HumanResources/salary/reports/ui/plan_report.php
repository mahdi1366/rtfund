<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	91.08.07
//---------------------------
require_once("../../../header.inc.php");
if (!isset($_REQUEST["show"]))
	require_once '../js/plan_report.js.php';
require_once "ReportGenerator.class.php";

$whr = "";
$AccError = false;

if (isset($_REQUEST["show"])) {

	// <editor-fold defaultstate="collapsed" desc="register Account Doc">
	
	require_once '../../../../accountancy/import/salary/salary.class.php';
	
	if(isset($_POST["DeleteDoc"]))
	{
		$AccDocObj = new ImportSalary($_POST["pay_year"], $_POST["pay_month"]);
		if(!$AccDocObj->DeleteAccDoc())
			echo "<center><h2>" . ExceptionHandler::GetExceptionsToString ("<br>") . "</h2></center>";
		else
			echo "<center><h2>" . " پیش سند با موفقیت حذف شد" . "</h2></center>";
	}	
	
	$regAccDoc = !empty($_POST["registerDoc"]) ? true : false;		
	if($regAccDoc)
	{
		$AccDocObj = new ImportSalary($_POST["pay_year"], $_POST["pay_month"]);
		if($AccDocObj->InitialImportance() === false)
		{
			echo "<center><h2>" . ExceptionHandler::GetExceptionsToString ("<br>") . "</h2></center>";
			$regAccDoc = false;
		}
	}
	$CostCodesArray = array(
		ImportSalary::PERSON_TYPE_ConditionalProf => array(
			"salary" => 18581,//150010110,
			"overtime" => 0,
			"OrgInsurance" => 18582,//150060113,
			"GovInsurance" => 434, // 150060112
			"27" => 30,//150010213,
			"extra" => 19023//150010241			
		),
		ImportSalary::PERSON_TYPE_Prof => array(
			"salary" => 5,//150010101,
			"overtime" => 0,
			"OrgInsurance" => 413,//150060101,
			"GovInsurance" => 18583,//150060114,
			"27" => 30,//150010213,
			"extra" => 27,//150010210
		),
		ImportSalary::PERSON_TYPE_Staff => array(
			"salary" => 6,//150010102,
			"overtime" => 44,//150010227,
			"OrgInsurance" => 416,//150060103,
			"GovInsurance" => 18584,//150060115,
			"27" => 40,//150010223,
			"extra" => 19024,//150010242
		),
		ImportSalary::PERSON_TYPE_Worker => array(
			"salary" => 8,//150010104,
			"overtime" => 51,//150010234,
			"OrgInsurance" => 422,//150060106,
			"GovInsurance" => 0,
			"27" => 19023,//150010241
			"38" => 342, //150021327
			"extra" => 19025,//150010243
		),
		ImportSalary::PERSON_TYPE_Contract => array(
			"salary" => 345,//150021399,
			"overtime" => 345,//150021399,
			"OrgInsurance" => 345,//150021399,
			"GovInsurance" => 345,//150021399,
			"27" => 345,//150021399,
			"38" => 343,//150021328,
			"extra" => 345,//150021399
		)
	);
	
	// </editor-fold>
	
	
	$whereParam = array();

	if (!empty($_POST["pay_month"])) {
		$whr .= " AND pit.pay_month = :pm ";
		$whereParam[":pm"] = $_POST["pay_month"];
	}
	if (!empty($_POST["pay_year"])) {
		$whr .= " AND pit.pay_year=:py";
		$whereParam[":py"] = $_POST["pay_year"];
	}

	if (!empty($_POST["PersonType"])) 
	{
		if ($_POST["PersonType"] == 102) 
		{
			$whr .= " AND s.person_type in (1,2,3) ";
			$pt = "(1,2,3)";
		} 
		else 
		{
			$whr .= " AND s.person_type=:pt";
			$whereParam[":pt"] = $_POST["PersonType"];
		}

		if ($_POST["PersonType"] == 102 || $_POST["PersonType"] == 2 || $_POST["PersonType"] == 3 || $_POST["PersonType"] == 1) 
		{
			//$whr.= " AND pit.salary_item_type_id in (144,145,9920,38,143,149,150,146,147,148,399,242,9915,9911,243,242,399) " ; 
		}
	}
	if(!empty($_POST["PayType"]))
	{
		$whr .= " AND p.payment_type=".$_POST["PayType"];		
		$whereParam[":pty"] = $_POST["PayType"];			
	}

	if($_POST['pay_year'] >= 1393 &&  $_POST['pay_month']  >= 10 )	
		$coef = 1 ;
	elseif ($_POST["pay_year"] >= 1390 && $_POST["pay_month"] >= 5)
		$coef = 1.7 / 1.65;

	else if ($_POST["pay_year"] >= 1391)
		$coef = 1.7 / 1.65;

	else
		$coef = 3 / 2;
	

	if ($_POST['ReportType'] == 1) {
		$costCenterGroup = "c.CostCenterID,";
}
else if($_POST['ReportType'] == 2) {	
		$costCenterGroup = " ";
		$costCenterWhere = " ";
	}

	$month_start = $_POST["pay_year"] . "/" . $_POST["pay_month"] . "/01";
	$month_start = DateModules::shamsi_to_miladi($month_start);

	$query = "	select  c.CostCenterID ,c.Title, s.person_type,c.AccUnitID,
					SUM(if(pit.salary_item_type_id not in(524 ,506 , 518 , 9936 ,9931 ,1000,9978,9945 , 9931,1000,9978,9945 , 9931,1000,9978,9945 ,39 , 152 , 639 , 752, 10231 , 10232 ,10341, 10342 ,284 ,44 ) , (pit.pay_value + pit.diff_pay_value * pit.diff_value_coef) , 0 )) pay,
					SUM(if(s.person_type=1 and salary_item_type_id=44, pit.pay_value + pit.diff_pay_value * pit.diff_value_coef,0))as heatomana,
					SUM(if(s.person_type=1 and pit.salary_item_type_id= 143 , pit.param7 + pit.diff_param7 * pit.diff_param7_coef ,
					if(s.person_type=2 and pit.salary_item_type_id= 38 , pit.param7 + pit.diff_param7 * pit.diff_param7_coef ,0)) ) bimekhadamat ,
					round( SUM(if(s.person_type=1 and pit.salary_item_type_id= 143 , (pit.param7 + pit.diff_param7 * pit.diff_param7_coef) * " . $coef . " ,
							   if(s.person_type=2 and pit.salary_item_type_id= 38 , ( pit.param7 + pit.diff_param7 * pit.diff_param7_coef ) * " . $coef . " ,0)) )) bimekhadamatdolat ,

					SUM( if(s.person_type=1 and pit.salary_item_type_id= 9920 , ((pit.param2 + pit.diff_param2 * pit.diff_param2_coef)) ,
							if(s.person_type=2 and pit.salary_item_type_id= 144 , ((pit.param2 + pit.diff_param2 * pit.diff_param2_coef)) ,
							if(s.person_type=3 and pit.salary_item_type_id= 145 ,
							                           ((pit.param2 + pit.diff_param2 * pit.diff_param2_coef) + (pit.param3 + pit.diff_param3 * pit.diff_param3_coef)) ,
							if(s.person_type=5 and pit.salary_item_type_id= 744 ,
												(pit.param2 + pit.diff_param2 * pit.diff_param2_coef ) ,
							if(s.person_type=6 and pit.salary_item_type_id= 745 ,
												(pit.param2 + pit.diff_param2 * pit.diff_param2_coef +
													pit.param3 + pit.diff_param3 * pit.diff_param3_coef),0)
									))))) bimetamin ,
						SUM(if(s.person_type=1 and pit.salary_item_type_id= 149 , pit.param3 + pit.diff_param3 * pit.diff_param3_coef ,
							if(s.person_type=2 and pit.salary_item_type_id= 150 , pit.param3 + pit.diff_param3 * pit.diff_param3_coef ,
							if(s.person_type=3 and pit.salary_item_type_id= 151 , pit.param3 + pit.diff_param3 * pit.diff_param3_coef ,
							if(s.person_type=5 and pit.salary_item_type_id= 750 , pit.param3 + pit.diff_param3 * pit.diff_param3_coef ,
							if(s.person_type=6 and pit.salary_item_type_id= 151 , pit.param3 + pit.diff_param3 * pit.diff_param3_coef ,0))))) ) retire ,

						SUM(if(s.person_type=1 , 0 , if(s.person_type=2 and pit.salary_item_type_id= 39 ,pit.pay_value + pit.diff_pay_value * pit.diff_value_coef ,
							if(s.person_type=3 and pit.salary_item_type_id= 152 , pit.pay_value + pit.diff_pay_value * pit.diff_value_coef ,
							if(s.person_type=5 and pit.salary_item_type_id= 639 ,pit.pay_value + pit.diff_pay_value * pit.diff_value_coef ,
							if(s.person_type=6 and pit.salary_item_type_id= 752 , pit.pay_value + pit.diff_pay_value * pit.diff_value_coef ,0 ))))) ) ezafekar ,

						SUM(if(s.person_type=1 and ( pit.salary_item_type_id in( 518 , 9931 ,1000,9978,9945,9936) ), pit.pay_value + pit.diff_pay_value * pit.diff_value_coef,
							if(s.person_type=2 and pit.salary_item_type_id in( 9931,1000,9978,9945,9936 ),pit.pay_value + pit.diff_pay_value * pit.diff_value_coef ,
							if(s.person_type in (3,5,6) and pit.salary_item_type_id in( 1000,9978,9945,9936 ) ,
							pit.pay_value + pit.diff_pay_value * pit.diff_value_coef ,0))) ) mAdeh38fogholAdeModiriat37 ,							
						SUM(if(s.person_type in (3,5,6) and pit.salary_item_type_id in( 9931 ) ,pit.pay_value + pit.diff_pay_value * pit.diff_value_coef ,0 )) Madeh38 , 
						SUM(if (pit.salary_item_type_id in (282,10149) , pit.get_value + pit.diff_get_value * pit.diff_value_coef,0 )) bimeyeomr ,
						SUM(if (pit.salary_item_type_id in (524,506) , pit.pay_value + pit.diff_pay_value * pit.diff_value_coef,0 )) bimeJanbazan ,
						
						SUM( if( pit.salary_item_type_id in (  10231, 10232 ,10341, 10342,44,284,884 ) , pit.pay_value + pit.diff_pay_value * pit.diff_value_coef , 0 )) as haghebimeJanbaz ,
						SUM(if (pit.salary_item_type_id in (10341 , 10342 , 284 , 884 , 10231 , 10232) , pit.pay_value + pit.diff_pay_value * pit.diff_value_coef,0 )) TafavotOmana ,
						SUM( if( ( (s.last_retired_pay IS NULL OR s.last_retired_pay >='" . $month_start . "') AND (pit.salary_item_type_id in ( 149,150 )) ),(param3 + diff_param3 * diff_param3_coef ),0)) retired_for_org_dn30 , 
						SUM( if((s.last_retired_pay IS NOT NULL AND s.last_retired_pay < '" . $month_start . "' AND (pit.salary_item_type_id in ( 149,150 )) ),(param3 + diff_param3 * diff_param3_coef ),0)) retired_for_org_up30 
						
			FROM payments p INNER JOIN staff s
											ON s.staff_id = p.staff_id
							LEFT JOIN writs w

                                ON	(p.writ_id = w.writ_id AND
									 p.writ_ver = w.writ_ver AND
									 p.staff_id = w.staff_id AND w.state=3)

							INNER JOIN payment_items pit 
											ON(p.pay_year = pit.pay_year AND p.pay_month = pit.pay_month AND
											   p.staff_id = pit.staff_id AND p.payment_type = pit.payment_type AND p.state = 2 )

							INNER JOIN persons per 
											ON (per.personid = s.personid )
							INNER JOIN banks b 
											ON b.bank_id = p.bank_id
							INNER JOIN CostCenterPlan c 
											ON c.CostCenterID = w.CostCenterID
											
			 WHERE p.pay_year = :py and p.pay_month = :pm and p.payment_type = :pty and if(s.person_type = 1 , w.emp_state != 11 ,(1=1))  
			       AND pit.cost_center_id in ( " . manage_access::getValidCostCenters() . ") 

			 group by  " . $costCenterGroup . " s.person_type , p.pay_month,p.pay_year,p.payment_type

			 order by c.CostCenterID
								";

	$dataTable = PdoDataAccess::runquery($query, $whereParam);
	if($_SESSION['UserID'] == 'jafarkhani') {
		//echo PdoDataAccess::GetLatestQueryString() ;  die() ; 
		
	}
	$qry = " select bi.Title month_title 
					from  Basic_Info bi 
							where  bi.typeid = 41 AND InfoID = " . $_POST["pay_month"];

	$res = PdoDataAccess::runquery($qry);
	$month = $res[0]['month_title'];
	
	?>
<META http-equiv=Content-Type content="text/html; charset=UTF-8" ><body dir="rtl">
<style media="print">
	.noPrint {display:none;}
</style>
<center>
	<style>
		body {
			font-family: tahoma;
			font-size: 12px;
		}
		.reportGenerator {border-collapse: collapse;border: 1px solid black;font-family: tahoma;font-size: 8pt;
						  text-align: center;width: 50%;padding: 2px;}
		.reportGenerator .header {color: white;font-weight: bold;background-color:#3F5F96}
		.reportGenerator td {border: 1px solid #555555;height: 20px;}
	</style>
	<? if ($_POST['ReportType'] == 1) {?>
		<form class="noPrint" id="MainForm" method="post">
			<input type="hidden" name="pay_month" value="<?= $_POST["pay_month"]?>">
			<input type="hidden" name="pay_year" value="<?= $_POST["pay_year"]?>">
			<input type="hidden" name="ReportType" value="1">
			<input type="submit" name="registerDoc" value="صدور پیش سند حسابداری">
			<input type="submit" name="DeleteDoc"  value="حذف پیش سند حسابداری">
		</form>
	<?}?>
	
	<?
	ob_start(); 
	$costCenter = $dataTable[0]['CostCenterID'];
	$ACC_UnitID = $dataTable[0]['AccUnitID'];
	$costCenterTitle = $dataTable[0]['Title'];

	if($_POST['ReportType'] == 1) {	

		$costCenterWhere = " and  c.CostCenterID =".$costCenter ; 	

	}
			
	// هیئت علمی مشروط ...............
	$mp_hoghoogh = 0;
	$mp_ezafeKar = 0;
	$mp_bimeh_dastgah = 0;
	$mp_bimeh_dolat = 0;
	$mp_made_kharej_Az_shomool = 0;
	$mp_jazb_omana = 0;
	$mp_sum = 0;
	// هیئت علمی......................
	$p_hoghoogh = 0;
	$p_ezafeKar = 0;
	$p_bimeh_dastgah = 0;
	$p_bimeh_dolat = 0;
	$p_made_kharej_Az_shomool = 0;
	$p_jazb_omana = 0;
	$p_sum = 0;
	//;کارمند .......................
	$e_hoghoogh = 0;
	$e_ezafeKar = 0;
	$e_bimeh_dastgah = 0;
	$e_bimeh_dolat = 0;
	$e_made_kharej_Az_shomool = 0;
	$e_jazb_omana = 0;
	$e_sum = 0;

	// روزمزد بیمه ای ....................
	$r_hoghoogh = 0;
	$r_ezafeKar = 0;
	$r_bimeh_dastgah = 0;
	$r_bimeh_dolat = 0;
	$r_made_kharej_Az_shomool27 = 0;
	$r_made_kharej_Az_shomool38 = 0;
	$r_jazb_omana = 0;
	$r_sum = 0;

	//قراردادی ..........................

	$gh_hoghoogh = 0;
	$gh_ezafeKar = 0;
	$gh_bimeh_dastgah = 0;
	$gh_bimeh_dolat = 0;
	$gh_made_kharej_Az_shomool27 = 0;
	$gh_made_kharej_Az_shomool38 = 0; 
	$gh_jazb_omana = 0;
	$gh_sum = 0;

	//..........................

	$rowPlanItem = 0;
	//..........................ماده 27 و 38...............
	$ExtraEmp = 0;
	$ExtraProf = 0;
	$ExtraMProf = 0;
	$ExtraRoozMozd = 0;
	$ExtraGharardadi = 0;
	//................... اضافه کار ...........................
	$OverTimeEmp = 0;
	$OverTimeProf = 0;
	$OverTimeMProf = 0;
	$OverTimeRoozMozd = 0;
	$OverTimeGharardadi = 0;

	//, 10231, 10232 ,10341, 10342
	// هیئت علمی مشروط 
	$query = "	select  c.CostCenterID ,c.Title, s.person_type,	 c.AccUnitID,
					SUM(if(pit.salary_item_type_id not in(524 ,506 ,518 , 9931 ,9936 ,1000,9978,9945 , 9931,1000,9978,9945 , 9931,1000,9978,9945 ,39 ,152 ,639 ,752 , 10231, 10232 ,10341, 10342 ,284 ,44 ),(pit.pay_value + pit.diff_pay_value * pit.diff_value_coef) , 0 )) pay,
					SUM(if(s.person_type=1 and salary_item_type_id=44, pit.pay_value + pit.diff_pay_value * pit.diff_value_coef,0))as heatomana,
					SUM(if(s.person_type=1 and pit.salary_item_type_id= 143 , pit.param7 + pit.diff_param7 * pit.diff_param7_coef ,
					if(s.person_type=2 and pit.salary_item_type_id= 38 , pit.param7 + pit.diff_param7 * pit.diff_param7_coef ,0)) ) bimekhadamat ,
					round( SUM(if(s.person_type=1 and pit.salary_item_type_id= 143 , (pit.param7 + pit.diff_param7 * pit.diff_param7_coef) * " . $coef . " ,
							   if(s.person_type=2 and pit.salary_item_type_id= 38 , ( pit.param7 + pit.diff_param7 * pit.diff_param7_coef ) * " . $coef . " ,0)) )) bimekhadamatdolat ,

					SUM( if(s.person_type=1 and pit.salary_item_type_id= 9920 , ((pit.param2 + pit.diff_param2 * pit.diff_param2_coef)) ,
							if(s.person_type=2 and pit.salary_item_type_id= 144 , ((pit.param2 + pit.diff_param2 * pit.diff_param2_coef)) ,
							if(s.person_type=3 and pit.salary_item_type_id= 145 ,
							((pit.param2 + pit.diff_param2 * pit.diff_param2_coef) + (pit.param3 + pit.diff_param3 * pit.diff_param3_coef)) ,
							if(s.person_type=5 and pit.salary_item_type_id= 744 ,
												(pit.param2 + pit.diff_param2 * pit.diff_param2_coef +
													pit.param3 + pit.diff_param3 * pit.diff_param3_coef) ,
							if(s.person_type=6 and pit.salary_item_type_id= 745 ,
												(pit.param2 + pit.diff_param2 * pit.diff_param2_coef +
													pit.param3 + pit.diff_param3 * pit.diff_param3_coef),0)
									))))) bimetamin ,
						SUM(if(s.person_type=1 and pit.salary_item_type_id= 149 , pit.param3 + pit.diff_param3 * pit.diff_param3_coef ,
							if(s.person_type=2 and pit.salary_item_type_id= 150 , pit.param3 + pit.diff_param3 * pit.diff_param3_coef ,
							if(s.person_type=3 and pit.salary_item_type_id= 151 , pit.param3 + pit.diff_param3 * pit.diff_param3_coef ,
							if(s.person_type=5 and pit.salary_item_type_id= 750 , pit.param3 + pit.diff_param3 * pit.diff_param3_coef ,
							if(s.person_type=6 and pit.salary_item_type_id= 151 , pit.param3 + pit.diff_param3 * pit.diff_param3_coef ,0))))) ) retire ,

						SUM(if(s.person_type=1 , 0 , if(s.person_type=2 and pit.salary_item_type_id= 39 ,pit.pay_value + pit.diff_pay_value * pit.diff_value_coef ,
							if(s.person_type=3 and pit.salary_item_type_id= 152 , pit.pay_value + pit.diff_pay_value * pit.diff_value_coef ,
							if(s.person_type=5 and pit.salary_item_type_id= 639 ,pit.pay_value + pit.diff_pay_value * pit.diff_value_coef ,
							if(s.person_type=6 and pit.salary_item_type_id= 752 , pit.pay_value + pit.diff_pay_value * pit.diff_value_coef ,0 ))))) ) ezafekar ,

						SUM(if(s.person_type=1 and ( pit.salary_item_type_id in( 518 , 9931 ,1000,9978,9945 ,9936) ), pit.pay_value + pit.diff_pay_value * pit.diff_value_coef,
							if(s.person_type=2 and pit.salary_item_type_id in( 9931,1000,9978,9945,9936 ),pit.pay_value + pit.diff_pay_value * pit.diff_value_coef ,
							if(s.person_type in (3,5,6) and pit.salary_item_type_id in( 1000,9978,9945,9936 ) ,
							pit.pay_value + pit.diff_pay_value * pit.diff_value_coef ,0))) ) mAdeh38fogholAdeModiriat37 ,
						SUM(if(s.person_type in (3,5,6) and pit.salary_item_type_id in( 9931 ) ,pit.pay_value + pit.diff_pay_value * pit.diff_value_coef ,0 )) Madeh38 , 
						SUM(if (pit.salary_item_type_id in ( 282,10149 ) , pit.get_value + pit.diff_get_value * pit.diff_value_coef,0 )) bimeyeomr ,
						SUM(if (pit.salary_item_type_id in (524,506) , pit.pay_value + pit.diff_pay_value * pit.diff_value_coef,0 )) bimeJanbazan ,
						SUM(if((s.person_type = 2 and pit.salary_item_type_id in (284,10341)),pit.pay_value + pit.diff_pay_value * pit.diff_value_coef ,
							if((s.person_type = 3 and pit.salary_item_type_id in (10231)),pit.pay_value + pit.diff_pay_value * pit.diff_value_coef , 
							if((s.person_type = 5 and pit.salary_item_type_id in (884,10232,10342)),pit.pay_value + pit.diff_pay_value * pit.diff_value_coef , 0 )
								)
							))  as haghebimeJanbaz  , 

					
						SUM(if (pit.salary_item_type_id in (10341 , 10342 , 284 , 884 ) , pit.pay_value + pit.diff_pay_value * pit.diff_value_coef,0 )) TafavotOmana,

						SUM( if( ( (s.last_retired_pay IS NULL OR s.last_retired_pay >='" . $month_start . "') AND (pit.salary_item_type_id in ( 149,150 )) ),(param3 + diff_param3 * diff_param3_coef ),0)) retired_for_org_dn30 , 
						SUM( if((s.last_retired_pay IS NOT NULL AND s.last_retired_pay < '" . $month_start . "' AND (pit.salary_item_type_id in ( 149,150 )) ),(param3 + diff_param3 * diff_param3_coef ),0)) retired_for_org_up30 

			FROM payments p INNER JOIN staff s
											ON s.staff_id = p.staff_id
							
							LEFT JOIN writs w
                                ON(p.writ_id = w.writ_id AND
                                   p.writ_ver = w.writ_ver AND
                                   p.staff_id = w.staff_id AND w.state=3)

							INNER JOIN payment_items pit 
											ON(p.pay_year = pit.pay_year AND p.pay_month = pit.pay_month AND
											   p.staff_id = pit.staff_id AND p.payment_type = pit.payment_type)

							INNER JOIN persons per 
											ON (per.personid = s.personid )
							INNER JOIN banks b 
											ON b.bank_id = p.bank_id
							INNER JOIN CostCenterPlan c 
											ON c.CostCenterID = w.CostCenterID
											
			 WHERE p.pay_year = :py and p.pay_month = :pm and p.payment_type = :pty and w.emp_state = 11 and 
				   pit.cost_center_id in ( " . manage_access::getValidCostCenters() . ") and s.person_type = 1 " . $costCenterWhere . "

			 group by " . $costCenterGroup . " s.person_type , p.pay_month,p.pay_year,p.payment_type

			 order by c.CostCenterID	 
								";

	$dataMProf = PdoDataAccess::runquery($query, $whereParam);

	if ($_SESSION['UserID'] == 'jafarkhani') {
			//echo PdoDataAccess::GetLatestQueryString() ; die() ; 
	}

	for ($i = 0; $i < count($dataTable); $i++) {

		if ($costCenter != $dataTable[$i]['CostCenterID'] && $_POST['ReportType'] == 1) {

			//.........................
			if( $_POST["PayType"] == 1 ) {
			
				$qry = " select PayValue , RelatedItem , PersonType
										from PlanItemReport 
												where PayYear = " . $_POST["pay_year"] . " and 
													  PayMonth = " . $_POST["pay_month"] . " and 
													  CostCenterID =" . $costCenter;
				
				$ExtraRes = PdoDataAccess::runquery($qry);
				
			}
			else 
			{
				$ExtraRes = array() ; 
			}
			for ($t = 0; $t < count($ExtraRes); $t++) {

				if ($ExtraRes[$t]['PersonType'] == 1 && $ExtraRes[$t]['RelatedItem'] == 5)
					$ExtraProf += $ExtraRes[$t]['PayValue'];

				elseif ($ExtraRes[$t]['PersonType'] == 2 && $ExtraRes[$t]['RelatedItem'] == 5)
					$ExtraEmp += $ExtraRes[$t]['PayValue'];

				elseif ($ExtraRes[$t]['PersonType'] == 3 && $ExtraRes[$t]['RelatedItem'] == 5)
					$ExtraRoozMozd += $ExtraRes[$t]['PayValue'];

				elseif ($ExtraRes[$t]['PersonType'] == 5 && $ExtraRes[$t]['RelatedItem'] == 5)
					$ExtraGharardadi += $ExtraRes[$t]['PayValue'];
				elseif ($ExtraRes[$t]['RelatedItem'] == 5)
					$ExtraMProf += $ExtraRes[$t]['PayValue'];

				elseif ($ExtraRes[$t]['PersonType'] == 1 && $ExtraRes[$t]['RelatedItem'] == 2)
					$OverTimeProf += $ExtraRes[$t]['PayValue'];
				elseif ($ExtraRes[$t]['PersonType'] == 2 && $ExtraRes[$t]['RelatedItem'] == 2)
					$OverTimeEmp += $ExtraRes[$t]['PayValue'];
				elseif ($ExtraRes[$t]['PersonType'] == 3 && $ExtraRes[$t]['RelatedItem'] == 2)
					$OverTimeRoozMozd += $ExtraRes[$t]['PayValue'];
				elseif ($ExtraRes[$t]['PersonType'] == 5 && $ExtraRes[$t]['RelatedItem'] == 2)
					$OverTimeGharardadi += $ExtraRes[$t]['PayValue'];
				elseif ($ExtraRes[$t]['RelatedItem'] == 2)
					$OverTimeMProf += $ExtraRes[$t]['PayValue'];
			}

			//.........................

			echo '<table  class="reportGenerator" style="text-align: right;width:100%!important" cellpadding="4" cellspacing="0">
					<tr class="header">
					<td colspan="8"> مرکز هزینه : ' . $costCenter . '- 
					 
					' . $costCenterTitle . '</td>
					</tr>
					<tr class="header"  style="background-color:#4682B4" >					
						<td> &nbsp; </td>
						<td align="center" >حقوق</td>
						<td align="center" >اضافه کار</td>
						<td align="center" > بیمه سهم دستگاه </td>			
						<td align="center" >بیمه درمانی سهم دولت </td>					
						<td align="center" >ماده 27 و 38 و مدیریت خارج از شمول</td>
						<td align="center" >2% فوق العاده جذب هیئت امنا</td>
						<td align="center" >جمع</td>
					</tr>';

			// <editor-fold defaultstate="collapsed" desc="register Account Doc">
			if($regAccDoc)
			{
				$ACC_pt = ImportSalary::PERSON_TYPE_ConditionalProf;
				$AccDocObj->AddItem($ACC_UnitID, $CostCodesArray[$ACC_pt]["salary"], $mp_hoghoogh, $ACC_pt);
				$AccDocObj->AddItem($ACC_UnitID, $CostCodesArray[$ACC_pt]["overtime"], $mp_ezafeKar + $OverTimeMProf, $ACC_pt);
				$AccDocObj->AddItem($ACC_UnitID, $CostCodesArray[$ACC_pt]["OrgInsurance"], round($mp_bimeh_dastgah), $ACC_pt);
				$AccDocObj->AddItem($ACC_UnitID, $CostCodesArray[$ACC_pt]["GovInsurance"], $mp_bimeh_dolat, $ACC_pt);
				$AccDocObj->AddItem($ACC_UnitID, $CostCodesArray[$ACC_pt]["27"], ($mp_made_kharej_Az_shomool + $ExtraMProf), $ACC_pt);
				$AccDocObj->AddItem($ACC_UnitID, $CostCodesArray[$ACC_pt]["extra"], $mp_jazb_omana, $ACC_pt);

				$ACC_pt = ImportSalary::PERSON_TYPE_Prof;
				$AccDocObj->AddItem($ACC_UnitID, $CostCodesArray[$ACC_pt]["salary"], $p_hoghoogh, $ACC_pt);
				$AccDocObj->AddItem($ACC_UnitID, $CostCodesArray[$ACC_pt]["overtime"], $p_ezafeKar + $OverTimeProf, $ACC_pt);
				$AccDocObj->AddItem($ACC_UnitID, $CostCodesArray[$ACC_pt]["OrgInsurance"], round($p_bimeh_dastgah), $ACC_pt);
				$AccDocObj->AddItem($ACC_UnitID, $CostCodesArray[$ACC_pt]["GovInsurance"], $p_bimeh_dolat, $ACC_pt);
				$AccDocObj->AddItem($ACC_UnitID, $CostCodesArray[$ACC_pt]["27"], ($p_made_kharej_Az_shomool + $ExtraProf), $ACC_pt);
				$AccDocObj->AddItem($ACC_UnitID, $CostCodesArray[$ACC_pt]["extra"], $p_jazb_omana, $ACC_pt);

				$ACC_pt = ImportSalary::PERSON_TYPE_Staff;
				$AccDocObj->AddItem($ACC_UnitID, $CostCodesArray[$ACC_pt]["salary"], $e_hoghoogh, $ACC_pt);
				$AccDocObj->AddItem($ACC_UnitID, $CostCodesArray[$ACC_pt]["overtime"], $e_ezafeKar + $OverTimeEmp, $ACC_pt);
				$AccDocObj->AddItem($ACC_UnitID, $CostCodesArray[$ACC_pt]["OrgInsurance"], round($e_bimeh_dastgah), $ACC_pt);
				$AccDocObj->AddItem($ACC_UnitID, $CostCodesArray[$ACC_pt]["GovInsurance"], $e_bimeh_dolat, $ACC_pt);
				$AccDocObj->AddItem($ACC_UnitID, $CostCodesArray[$ACC_pt]["27"], ($e_made_kharej_Az_shomool + $ExtraEmp), $ACC_pt);
				$AccDocObj->AddItem($ACC_UnitID, $CostCodesArray[$ACC_pt]["extra"], $e_jazb_omana, $ACC_pt);

				$ACC_pt = ImportSalary::PERSON_TYPE_Worker;
				$AccDocObj->AddItem($ACC_UnitID, $CostCodesArray[$ACC_pt]["salary"], $r_hoghoogh, $ACC_pt);
				$AccDocObj->AddItem($ACC_UnitID, $CostCodesArray[$ACC_pt]["overtime"], $r_ezafeKar + $OverTimeRoozMozd, $ACC_pt);
				$AccDocObj->AddItem($ACC_UnitID, $CostCodesArray[$ACC_pt]["OrgInsurance"], round($r_bimeh_dastgah), $ACC_pt);
				$AccDocObj->AddItem($ACC_UnitID, $CostCodesArray[$ACC_pt]["GovInsurance"], $r_bimeh_dolat, $ACC_pt);
				$AccDocObj->AddItem($ACC_UnitID, $CostCodesArray[$ACC_pt]["27"], ($r_made_kharej_Az_shomool27 + $ExtraRoozMozd), $ACC_pt);
				$AccDocObj->AddItem($ACC_UnitID, $CostCodesArray[$ACC_pt]["38"], ($r_made_kharej_Az_shomool38 + $ExtraRoozMozd), $ACC_pt);
				$AccDocObj->AddItem($ACC_UnitID, $CostCodesArray[$ACC_pt]["extra"], $r_jazb_omana, $ACC_pt);

				$ACC_pt = ImportSalary::PERSON_TYPE_Contract;
				$AccDocObj->AddItem($ACC_UnitID, $CostCodesArray[$ACC_pt]["salary"], $gh_hoghoogh, $ACC_pt);
				$AccDocObj->AddItem($ACC_UnitID, $CostCodesArray[$ACC_pt]["overtime"], $gh_ezafeKar + $OverTimeGharardadi, $ACC_pt);
				$AccDocObj->AddItem($ACC_UnitID, $CostCodesArray[$ACC_pt]["OrgInsurance"], round($gh_bimeh_dastgah), $ACC_pt);
				$AccDocObj->AddItem($ACC_UnitID, $CostCodesArray[$ACC_pt]["GovInsurance"], $gh_bimeh_dolat, $ACC_pt);
				$AccDocObj->AddItem($ACC_UnitID, $CostCodesArray[$ACC_pt]["27"], ($gh_made_kharej_Az_shomool27 + $ExtraGharardadi), $ACC_pt);
				$AccDocObj->AddItem($ACC_UnitID, $CostCodesArray[$ACC_pt]["38"], ($gh_made_kharej_Az_shomool38 + $ExtraGharardadi), $ACC_pt);
				$AccDocObj->AddItem($ACC_UnitID, $CostCodesArray[$ACC_pt]["extra"], $gh_jazb_omana, $ACC_pt);

				if(ExceptionHandler::GetExceptionCount() > 0)
				{
					echo "مرکز هزینه : " . $title . "<br><hr><br>";
					echo "<span style=color:red><h3>" . ExceptionHandler::GetExceptionsToString ("<br>") . "</h3></span>";
					$AccError = true;
				}
				ExceptionHandler::PopAllExceptions();
				
			}
			//</editor-fold>
		
			echo '<tr>
				<td>علمی مشروط</td>
				<td>' . $mp_hoghoogh . '</td>
			<td>' . ($mp_ezafeKar + $OverTimeMProf ) . '</td>
				<td>' . round($mp_bimeh_dastgah) . '</td>
				<td>' . $mp_bimeh_dolat . '</td>
				<td>' . ($mp_made_kharej_Az_shomool + $ExtraMProf) . '</td>
				<td>' . $mp_jazb_omana . '</td>
			<td>' . ($mp_sum + $OverTimeMProf + $ExtraMProf ). '</td>			
			</tr>
			<tr>
				<td>هیات علمی</td>
				<td>' . $p_hoghoogh . '</td>
			 <td>' . ($p_ezafeKar + $OverTimeProf ) . '</td>
				<td>' . round($p_bimeh_dastgah) . '</td>
				<td>' . $p_bimeh_dolat . '</td>
				<td>' . ($p_made_kharej_Az_shomool + $ExtraProf) . '</td>
				<td>' . $p_jazb_omana . '</td>
			 <td>' . ($p_sum + $OverTimeProf + $ExtraProf ) . '</td>
			</tr>
			<tr>
				<td>کارمند</td>
				<td>' . $e_hoghoogh . '</td>
			 <td>' . ($e_ezafeKar + $OverTimeEmp ) . '</td>
				<td>' . round($e_bimeh_dastgah) . '</td>
				<td>' . $e_bimeh_dolat . '</td>
				<td>' . ($e_made_kharej_Az_shomool + $ExtraEmp) . '</td>
				<td>' . $e_jazb_omana . '</td>
			 <td>' . ($e_sum + $OverTimeEmp + $ExtraEmp ) . '</td>
			</tr>
			<tr>
				<td>روز مزدبیمه ای</td>
				<td>' . $r_hoghoogh . '</td>
			 <td>' . ($r_ezafeKar + $OverTimeRoozMozd) . '</td>
				<td>' . round($r_bimeh_dastgah) . '</td>
				<td>' . $r_bimeh_dolat . '</td>
				<td>' . ($r_made_kharej_Az_shomool27 + $r_made_kharej_Az_shomool38 + $ExtraRoozMozd) . '</td>
				<td>' . $r_jazb_omana . '</td>
			 <td>' . ($r_sum + $OverTimeRoozMozd + $ExtraRoozMozd) . '</td>
			</tr>
			<tr>
				<td>قرارداد کار معین و یکساله</td>
				<td>' . $gh_hoghoogh . '</td>
			 <td>' . ($gh_ezafeKar + $OverTimeGharardadi). '</td>
				<td>' . round($gh_bimeh_dastgah) . '</td>
				<td>' . $gh_bimeh_dolat . '</td>
				<td>' . ($gh_made_kharej_Az_shomool27 + $gh_made_kharej_Az_shomool38 + $ExtraGharardadi) . '</td>
				<td>' . $gh_jazb_omana . '</td>
			 <td>' . ($gh_sum + $OverTimeGharardadi + $ExtraGharardadi ). '</td>
			</tr>

			<tr style="background-color:#F0F8FF">
				<td style="font-family:b Titr"  >جمع</td>
				<td>' . ($mp_hoghoogh + $p_hoghoogh + $e_hoghoogh + $r_hoghoogh + $gh_hoghoogh ) . '</td>
			<td>' . ($mp_ezafeKar + $p_ezafeKar + $e_ezafeKar + $r_ezafeKar + $gh_ezafeKar + $OverTimeMProf + $OverTimeProf + $OverTimeEmp +
					 $OverTimeRoozMozd + $OverTimeGharardadi ) . '</td>
				<td>' . (round($mp_bimeh_dastgah) + round($p_bimeh_dastgah) + round($e_bimeh_dastgah) + round($r_bimeh_dastgah) + round($gh_bimeh_dastgah) ) . '</td>
				<td>' . ($mp_bimeh_dolat + $p_bimeh_dolat + $e_bimeh_dolat + $r_bimeh_dolat + $gh_bimeh_dolat ) . '</td>
				<td>' . ($mp_made_kharej_Az_shomool + $p_made_kharej_Az_shomool + $e_made_kharej_Az_shomool +
				$r_made_kharej_Az_shomool27 + $r_made_kharej_Az_shomool38 + $gh_made_kharej_Az_shomool27 + $gh_made_kharej_Az_shomool38 + $ExtraMProf + $ExtraProf + $ExtraEmp +
				$ExtraRoozMozd + $ExtraGharardadi ) . '</td>
				<td>' . ($mp_jazb_omana + $p_jazb_omana + $e_jazb_omana + $r_jazb_omana + $gh_jazb_omana) . '</td>
				<td>' . ($mp_sum + $p_sum + $e_sum + $r_sum + $gh_sum + $ExtraMProf + $ExtraProf + $ExtraEmp +
					 $ExtraRoozMozd + $ExtraGharardadi + $OverTimeMProf + $OverTimeProf + $OverTimeEmp +
			$OverTimeRoozMozd + $OverTimeGharardadi ) . '</td>
			</tr></table><br>';
			
			//.........................

			$costCenter = $dataTable[$i]['CostCenterID'];
			$ACC_UnitID = $dataTable[$i]['AccUnitID'];
			$costCenterTitle = $dataTable[$i]['Title'];


			// هیئت علمی مشروط 
			$query = "	select  c.CostCenterID ,c.Title, s.person_type,	c.AccUnitID,
					SUM(if(pit.salary_item_type_id not in(524 ,506 ,518 , 9931 ,9936,1000,9978,9945 , 9931,1000,9978,9945 , 9931,1000,9978,9945 , 39 , 152 , 639 , 752, 10231, 10232 ,10341, 10342 ,44 ) , (pit.pay_value + pit.diff_pay_value * pit.diff_value_coef) , 0 )) pay,
					SUM(if(s.person_type=1 and salary_item_type_id=44, pit.pay_value + pit.diff_pay_value * pit.diff_value_coef,0))as heatomana,
					
					SUM(if(s.person_type=1 and pit.salary_item_type_id= 143 , pit.param7 + pit.diff_param7 * pit.diff_param7_coef ,
					if(s.person_type=2 and pit.salary_item_type_id= 38 , pit.param7 + pit.diff_param7 * pit.diff_param7_coef ,0)) ) bimekhadamat ,
					round( SUM(if(s.person_type=1 and pit.salary_item_type_id= 143 , (pit.param7 + pit.diff_param7 * pit.diff_param7_coef) * " . $coef . " ,
							   if(s.person_type=2 and pit.salary_item_type_id= 38 , ( pit.param7 + pit.diff_param7 * pit.diff_param7_coef ) * " . $coef . " ,0)) )) bimekhadamatdolat ,

					SUM( if(s.person_type=1 and pit.salary_item_type_id= 9920 , ((pit.param2 + pit.diff_param2 * pit.diff_param2_coef)) ,
							if(s.person_type=2 and pit.salary_item_type_id= 144 , ((pit.param2 + pit.diff_param2 * pit.diff_param2_coef)) ,
							if(s.person_type=3 and pit.salary_item_type_id= 145 ,
							((pit.param2 + pit.diff_param2 * pit.diff_param2_coef) + (pit.param3 + pit.diff_param3 * pit.diff_param3_coef)) ,
							if(s.person_type=5 and pit.salary_item_type_id= 744 ,
												(pit.param2 + pit.diff_param2 * pit.diff_param2_coef ) ,
							if(s.person_type=6 and pit.salary_item_type_id= 745 ,
												(pit.param2 + pit.diff_param2 * pit.diff_param2_coef +
													pit.param3 + pit.diff_param3 * pit.diff_param3_coef),0)
									))))) bimetamin ,
						SUM(if(s.person_type=1 and pit.salary_item_type_id= 149 , pit.param3 + pit.diff_param3 * pit.diff_param3_coef ,
							if(s.person_type=2 and pit.salary_item_type_id= 150 , pit.param3 + pit.diff_param3 * pit.diff_param3_coef ,
							if(s.person_type=3 and pit.salary_item_type_id= 151 , pit.param3 + pit.diff_param3 * pit.diff_param3_coef ,
							if(s.person_type=5 and pit.salary_item_type_id= 750 , pit.param3 + pit.diff_param3 * pit.diff_param3_coef ,
							if(s.person_type=6 and pit.salary_item_type_id= 151 , pit.param3 + pit.diff_param3 * pit.diff_param3_coef ,0))))) ) retire ,

						SUM(if(s.person_type=1 , 0 , if(s.person_type=2 and pit.salary_item_type_id= 39 ,pit.pay_value + pit.diff_pay_value * pit.diff_value_coef ,
							if(s.person_type=3 and pit.salary_item_type_id= 152 , pit.pay_value + pit.diff_pay_value * pit.diff_value_coef ,
							if(s.person_type=5 and pit.salary_item_type_id= 639 ,pit.pay_value + pit.diff_pay_value * pit.diff_value_coef ,
							if(s.person_type=6 and pit.salary_item_type_id= 752 , pit.pay_value + pit.diff_pay_value * pit.diff_value_coef ,0 ))))) ) ezafekar ,

						SUM(if(s.person_type=1 and ( pit.salary_item_type_id in( 518 , 9931 ,1000,9978,9945 ,9936 ) ), pit.pay_value + pit.diff_pay_value * pit.diff_value_coef,
							if(s.person_type=2 and pit.salary_item_type_id in( 9931,1000,9978,9945 ,9936 ),pit.pay_value + pit.diff_pay_value * pit.diff_value_coef ,
							if(s.person_type in (3,5,6) and pit.salary_item_type_id in( 1000,9978,9945 ,9936 ) ,
							pit.pay_value + pit.diff_pay_value * pit.diff_value_coef ,0))) ) mAdeh38fogholAdeModiriat37 ,
						SUM(if(s.person_type in (3,5,6) and pit.salary_item_type_id in( 9931 ) ,pit.pay_value + pit.diff_pay_value * pit.diff_value_coef ,0 )) Madeh38 , 
						SUM(if (pit.salary_item_type_id in ( 282,10149 ) , pit.get_value + pit.diff_get_value * pit.diff_value_coef,0 )) bimeyeomr ,
						SUM(if (pit.salary_item_type_id in (524,506) , pit.pay_value + pit.diff_pay_value * pit.diff_value_coef,0 )) bimeJanbazan ,
						SUM( if (pit.salary_item_type_id in (44) , pit.pay_value + pit.diff_pay_value * pit.diff_value_coef , 0 )) as haghebimeJanbaz ,
						SUM(if (pit.salary_item_type_id in (10341 , 10342 , 284 , 884 , 10231, 10232 ,10341, 10342 ) , pit.pay_value + pit.diff_pay_value * pit.diff_value_coef,0 )) TafavotOmana,
						SUM( if( ( (s.last_retired_pay IS NULL OR s.last_retired_pay >='" . $month_start . "') AND (pit.salary_item_type_id in ( 149,150 )) ),(param3 + diff_param3 * diff_param3_coef ),0)) retired_for_org_dn30 , 
						SUM( if((s.last_retired_pay IS NOT NULL AND s.last_retired_pay < '" . $month_start . "' AND (pit.salary_item_type_id in ( 149,150 )) ),(param3 + diff_param3 * diff_param3_coef ),0)) retired_for_org_up30 


			FROM payments p INNER JOIN staff s
											ON s.staff_id = p.staff_id
							
							LEFT JOIN writs w
									ON(p.writ_id = w.writ_id AND
									   p.writ_ver = w.writ_ver AND
									   p.staff_id = w.staff_id AND w.state=3)

							INNER JOIN payment_items pit 
											ON(p.pay_year = pit.pay_year AND p.pay_month = pit.pay_month AND
											   p.staff_id = pit.staff_id AND p.payment_type = pit.payment_type)
 
							INNER JOIN persons per 
											ON (per.personid = s.personid )
							INNER JOIN banks b 
											ON b.bank_id = p.bank_id
							INNER JOIN CostCenterPlan c 
											ON c.CostCenterID = w.CostCenterID
											
			 WHERE p.pay_year = :py and p.pay_month = :pm and p.payment_type = :pty and w.emp_state = 11 and s.person_type = 1 and
				   pit.cost_center_id in ( " . manage_access::getValidCostCenters() . ") and c.CostCenterID = $costCenter

			 group by $costCenterGroup s.person_type , p.pay_month,p.pay_year,p.payment_type

			 order by c.CostCenterID
			 
								";

			$dataMProf = PdoDataAccess::runquery($query, $whereParam);

			// هیئت علمی مشروط ...............
			$mp_hoghoogh = 0;
			$mp_ezafeKar = 0;
			$mp_bimeh_dastgah = 0;
			$mp_bimeh_dolat = 0;
			$mp_made_kharej_Az_shomool = 0;
			$mp_jazb_omana = 0;
			$mp_sum = 0;
			// هیئت علمی......................
			$p_hoghoogh = 0;
			$p_ezafeKar = 0;
			$p_bimeh_dastgah = 0;
			$p_bimeh_dolat = 0;
			$p_made_kharej_Az_shomool = 0;
			$p_jazb_omana = 0;
			$p_sum = 0;
			//;کارمند .......................
			$e_hoghoogh = 0;
			$e_ezafeKar = 0;
			$e_bimeh_dastgah = 0;
			$e_bimeh_dolat = 0;
			$e_made_kharej_Az_shomool = 0;
			$e_jazb_omana = 0;
			$e_sum = 0;

			// روزمزد بیمه ای ....................
			$r_hoghoogh = 0;
			$r_ezafeKar = 0;
			$r_bimeh_dastgah = 0;
			$r_bimeh_dolat = 0;
			$r_made_kharej_Az_shomool27 = 0;
			$r_made_kharej_Az_shomool38 = 0;
			$r_jazb_omana = 0;
			$r_sum = 0;

			//قراردادی ..........................

			$gh_hoghoogh = 0;
			$gh_ezafeKar = 0;
			$gh_bimeh_dastgah = 0;
			$gh_bimeh_dolat = 0;
			$gh_made_kharej_Az_shomool27 = 0;
			$gh_made_kharej_Az_shomool38 = 0;
			$gh_jazb_omana = 0;
			$gh_sum = 0;
			//....................... ماده 27 و 38 ..........................	
			$ExtraEmp = 0;
			$ExtraProf = 0;
			$ExtraMProf = 0;
			$ExtraRoozMozd = 0;
			$ExtraGharardadi = 0;

			//................... اضافه کار ...........................
			$OverTimeEmp = 0;
			$OverTimeProf = 0;
			$OverTimeMProf = 0;
			$OverTimeRoozMozd = 0;
			$OverTimeGharardadi = 0;
		}

		if (count($dataMProf) > 0) {

			$mp_hoghoogh = $dataMProf[0]['pay'];
			$mp_ezafeKar = $dataMProf[0]['ezafekar'];
			$mp_bimeh_dastgah = $dataMProf[0]['bimekhadamat'] + $dataMProf[0]['bimetamin'] +
					$dataMProf[0]['retired_for_org_dn30'] + $dataMProf[0]['retired_for_org_up30'] + (($dataMProf[0]['bimeyeomr'] / 90000 ) * 108000) +
					$dataMProf[0]['bimeJanbazan'];
			$mp_bimeh_dolat = $dataMProf[0]['bimekhadamatdolat'];
			$mp_made_kharej_Az_shomool = $dataMProf[0]['mAdeh38fogholAdeModiriat37'];
			$mp_jazb_omana = $dataMProf[0]['haghebimeJanbaz'];
			$mp_sum = round($mp_hoghoogh + $mp_ezafeKar + $mp_bimeh_dastgah + $mp_bimeh_dolat + $mp_made_kharej_Az_shomool + $mp_jazb_omana + $ExtraMProf + $OverTimeMProf );
		}

		//...........................................

		if ($dataTable[$i]['person_type'] == 1) {

			$p_hoghoogh = $dataTable[$i]['pay'];
			$p_ezafeKar = $dataTable[$i]['ezafekar'];
			$p_bimeh_dastgah = $dataTable[$i]['bimekhadamat'] + $dataTable[$i]['bimetamin'] +
					$dataTable[$i]['retired_for_org_dn30'] + $dataTable[$i]['retired_for_org_up30'] + (($dataTable[$i]['bimeyeomr'] / 90000 ) * 108000) +
					$dataTable[$i]['bimeJanbazan'];		 
			$p_bimeh_dolat = $dataTable[$i]['bimekhadamatdolat'];
			$p_made_kharej_Az_shomool = $dataTable[$i]['mAdeh38fogholAdeModiriat37'];
			$p_jazb_omana = $dataTable[$i]['haghebimeJanbaz'];
			$p_sum = round($p_hoghoogh + $p_ezafeKar + $p_bimeh_dastgah + $p_bimeh_dolat + $p_made_kharej_Az_shomool + $p_jazb_omana + $ExtraProf + $OverTimeProf  );
		} else if ($dataTable[$i]['person_type'] == 2) {

			$e_hoghoogh = $dataTable[$i]['pay'];
			$e_ezafeKar = $dataTable[$i]['ezafekar'];
			$e_bimeh_dastgah = $dataTable[$i]['bimekhadamat'] + $dataTable[$i]['bimetamin'] +
					$dataTable[$i]['retired_for_org_dn30'] + $dataTable[$i]['retired_for_org_up30'] + (($dataTable[$i]['bimeyeomr'] / 90000 ) * 108000) +
					$dataTable[$i]['bimeJanbazan'];	        

			$e_bimeh_dolat = $dataTable[$i]['bimekhadamatdolat'];
			$e_made_kharej_Az_shomool = $dataTable[$i]['mAdeh38fogholAdeModiriat37'];
			$e_jazb_omana = $dataTable[$i]['haghebimeJanbaz'];
			$e_sum = round($e_hoghoogh + $e_ezafeKar + $e_bimeh_dastgah + $e_bimeh_dolat + $e_made_kharej_Az_shomool + $e_jazb_omana + $ExtraEmp + $OverTimeEmp );
		} else if ($dataTable[$i]['person_type'] == 3) {

			$r_hoghoogh = $dataTable[$i]['pay'];
			$r_ezafeKar = $dataTable[$i]['ezafekar'];
			$r_bimeh_dastgah = $dataTable[$i]['bimekhadamat'] + $dataTable[$i]['bimetamin'] +
					$dataTable[$i]['retired_for_org_dn30'] + $dataTable[$i]['retired_for_org_up30'] + (($dataTable[$i]['bimeyeomr'] / 90000 ) * 108000) +
					$dataTable[$i]['bimeJanbazan'];             

			$r_bimeh_dolat = $dataTable[$i]['bimekhadamatdolat'];
			$r_made_kharej_Az_shomool27 = $dataTable[$i]['mAdeh38fogholAdeModiriat37'] ;
			$r_made_kharej_Az_shomool38 = $dataTable[$i]['Madeh38'] ;
			$r_jazb_omana = $dataTable[$i]['haghebimeJanbaz'];
			$r_sum = round($r_hoghoogh + $r_ezafeKar + $r_bimeh_dastgah + $r_bimeh_dolat + $r_made_kharej_Az_shomool27 + $r_made_kharej_Az_shomool38 + $r_jazb_omana + $ExtraRoozMozd + $OverTimeRoozMozd );
		} else if ($dataTable[$i]['person_type'] == 5) {

			$gh_hoghoogh = $dataTable[$i]['pay'];
			$gh_ezafeKar = $dataTable[$i]['ezafekar'];
			$gh_bimeh_dastgah = $dataTable[$i]['bimekhadamat'] + $dataTable[$i]['bimetamin'] +
					$dataTable[$i]['retired_for_org_dn30'] + $dataTable[$i]['retired_for_org_up30'] + (($dataTable[$i]['bimeyeomr'] / 90000 ) * 108000) +
					$dataTable[$i]['bimeJanbazan'];

			$gh_bimeh_dolat = $dataTable[$i]['bimekhadamatdolat'];
			$gh_made_kharej_Az_shomool27 = $dataTable[$i]['mAdeh38fogholAdeModiriat37'] ;
			$gh_made_kharej_Az_shomool38 = $dataTable[$i]['Madeh38'] ;
			$gh_jazb_omana = $dataTable[$i]['haghebimeJanbaz'];
			$gh_sum = round($gh_hoghoogh + $gh_ezafeKar + $gh_bimeh_dastgah + $gh_bimeh_dolat + $gh_made_kharej_Az_shomool27 + $gh_made_kharej_Az_shomool38 + $gh_jazb_omana + $ExtraGharardadi + $OverTimeGharardadi );
		}
	}
	
	
	//....................... ماده 27 و 38 ..........................	
			$ExtraEmp = 0;
			$ExtraProf = 0;
			$ExtraMProf = 0;
			$ExtraRoozMozd = 0;
			$ExtraGharardadi = 0;

			//................... اضافه کار ...........................
			$OverTimeEmp = 0;
			$OverTimeProf = 0;
			$OverTimeMProf = 0;
			$OverTimeRoozMozd = 0;
			$OverTimeGharardadi = 0;
			
	//bahar
	if($_POST['ReportType'] == 1) {	

		$costCenterWhere = " and  c.CostCenterID =".$costCenter ; 	

	}
	if( $_POST["PayType"] == 1 ) {
		$qry = " select PayValue , RelatedItem , PersonType
										from PlanItemReport  c 
												where c.PayYear = " . $_POST["pay_year"] . " and 
													  c.PayMonth = " . $_POST["pay_month"] .$costCenterWhere;

		$ExtraRes = PdoDataAccess::runquery($qry);
	}
	else 
		$ExtraRes = array() ; 
	
	for ($t = 0; $t < count($ExtraRes); $t++) {

		if ($ExtraRes[$t]['PersonType'] == 1 && $ExtraRes[$t]['RelatedItem'] == 5)
			$ExtraProf += $ExtraRes[$t]['PayValue'];

		elseif ($ExtraRes[$t]['PersonType'] == 2 && $ExtraRes[$t]['RelatedItem'] == 5)
			$ExtraEmp += $ExtraRes[$t]['PayValue'];

		elseif ($ExtraRes[$t]['PersonType'] == 3 && $ExtraRes[$t]['RelatedItem'] == 5)
			$ExtraRoozMozd += $ExtraRes[$t]['PayValue'];

		elseif ($ExtraRes[$t]['PersonType'] == 5 && $ExtraRes[$t]['RelatedItem'] == 5)
			$ExtraGharardadi += $ExtraRes[$t]['PayValue'];
		elseif ($ExtraRes[$t]['RelatedItem'] == 5)
			$ExtraMProf += $ExtraRes[$t]['PayValue'];

		elseif ($ExtraRes[$t]['PersonType'] == 1 && $ExtraRes[$t]['RelatedItem'] == 2)
			$OverTimeProf += $ExtraRes[$t]['PayValue'];
		elseif ($ExtraRes[$t]['PersonType'] == 2 && $ExtraRes[$t]['RelatedItem'] == 2)
			$OverTimeEmp += $ExtraRes[$t]['PayValue'];
		elseif ($ExtraRes[$t]['PersonType'] == 3 && $ExtraRes[$t]['RelatedItem'] == 2)
			$OverTimeRoozMozd += $ExtraRes[$t]['PayValue'];
		elseif ($ExtraRes[$t]['PersonType'] == 5 && $ExtraRes[$t]['RelatedItem'] == 2)
			$OverTimeGharardadi += $ExtraRes[$t]['PayValue'];
		elseif ($ExtraRes[$t]['RelatedItem'] == 2)
			$OverTimeMProf += $ExtraRes[$t]['PayValue'];
	}

	//.............................................................
	if ($_POST['ReportType'] == 1) 
	{
		$title = '<td colspan="8"> مرکز هزینه :  ' . $costCenter . ' - ' . $costCenterTitle . '</td>';
	}
	else if ($_POST['ReportType'] == 2)
	{
		$title = '<td colspan="8"> کل دانشگاه</td>';
	}

	echo '<table  class="reportGenerator" style="text-align: right;width:70%!important" cellpadding="4" cellspacing="0">
			<tr class="header">
			' . $title . '
			</tr>
			<tr class="header" style="background-color:#4682B4"  >					
				<td> &nbsp; </td>
				<td align="center" >حقوق</td>
				<td align="center" >اضافه کار</td>
				<td align="center" > بیمه سهم دستگاه </td>			
				<td align="center" >بیمه درمانی سهم دولت </td>					
				<td align="center" >ماده 27 و 38 و مدیریت خارج از شمول</td>
				<td align="center" >2% فوق العاده جذب هیئت امنا</td>
				<td align="center" >جمع</td>
			</tr>';
	
	// <editor-fold defaultstate="collapsed" desc="register Account Doc">
	
	if($regAccDoc)
	{
		$ACC_pt = ImportSalary::PERSON_TYPE_ConditionalProf;
		$AccDocObj->AddItem($ACC_UnitID, $CostCodesArray[$ACC_pt]["salary"], $mp_hoghoogh, $ACC_pt);
		$AccDocObj->AddItem($ACC_UnitID, $CostCodesArray[$ACC_pt]["overtime"], $mp_ezafeKar + $OverTimeMProf, $ACC_pt);
		$AccDocObj->AddItem($ACC_UnitID, $CostCodesArray[$ACC_pt]["OrgInsurance"], round($mp_bimeh_dastgah), $ACC_pt);
		$AccDocObj->AddItem($ACC_UnitID, $CostCodesArray[$ACC_pt]["GovInsurance"], $mp_bimeh_dolat, $ACC_pt);
		$AccDocObj->AddItem($ACC_UnitID, $CostCodesArray[$ACC_pt]["27"], ($mp_made_kharej_Az_shomool + $ExtraMProf), $ACC_pt);
		$AccDocObj->AddItem($ACC_UnitID, $CostCodesArray[$ACC_pt]["extra"], $mp_jazb_omana, $ACC_pt);

		$ACC_pt = ImportSalary::PERSON_TYPE_Prof;
		$AccDocObj->AddItem($ACC_UnitID, $CostCodesArray[$ACC_pt]["salary"], $p_hoghoogh, $ACC_pt);
		$AccDocObj->AddItem($ACC_UnitID, $CostCodesArray[$ACC_pt]["overtime"], $p_ezafeKar + $OverTimeProf, $ACC_pt);
		$AccDocObj->AddItem($ACC_UnitID, $CostCodesArray[$ACC_pt]["OrgInsurance"], round($p_bimeh_dastgah), $ACC_pt);
		$AccDocObj->AddItem($ACC_UnitID, $CostCodesArray[$ACC_pt]["GovInsurance"], $p_bimeh_dolat, $ACC_pt);
		$AccDocObj->AddItem($ACC_UnitID, $CostCodesArray[$ACC_pt]["27"], ($p_made_kharej_Az_shomool + $ExtraProf), $ACC_pt);
		$AccDocObj->AddItem($ACC_UnitID, $CostCodesArray[$ACC_pt]["extra"], $p_jazb_omana, $ACC_pt);

		$ACC_pt = ImportSalary::PERSON_TYPE_Staff;
		$AccDocObj->AddItem($ACC_UnitID, $CostCodesArray[$ACC_pt]["salary"], $e_hoghoogh, $ACC_pt);
		$AccDocObj->AddItem($ACC_UnitID, $CostCodesArray[$ACC_pt]["overtime"], $e_ezafeKar + $OverTimeEmp, $ACC_pt);
		$AccDocObj->AddItem($ACC_UnitID, $CostCodesArray[$ACC_pt]["OrgInsurance"], round($e_bimeh_dastgah), $ACC_pt);
		$AccDocObj->AddItem($ACC_UnitID, $CostCodesArray[$ACC_pt]["GovInsurance"], $e_bimeh_dolat, $ACC_pt);
		$AccDocObj->AddItem($ACC_UnitID, $CostCodesArray[$ACC_pt]["27"], ($e_made_kharej_Az_shomool + $ExtraEmp), $ACC_pt);
		$AccDocObj->AddItem($ACC_UnitID, $CostCodesArray[$ACC_pt]["extra"], $e_jazb_omana, $ACC_pt);

		$ACC_pt = ImportSalary::PERSON_TYPE_Worker;
		$AccDocObj->AddItem($ACC_UnitID, $CostCodesArray[$ACC_pt]["salary"], $r_hoghoogh, $ACC_pt);
		$AccDocObj->AddItem($ACC_UnitID, $CostCodesArray[$ACC_pt]["overtime"], $r_ezafeKar + $OverTimeRoozMozd, $ACC_pt);
		$AccDocObj->AddItem($ACC_UnitID, $CostCodesArray[$ACC_pt]["OrgInsurance"], round($r_bimeh_dastgah), $ACC_pt);
		$AccDocObj->AddItem($ACC_UnitID, $CostCodesArray[$ACC_pt]["GovInsurance"], $r_bimeh_dolat, $ACC_pt);
		$AccDocObj->AddItem($ACC_UnitID, $CostCodesArray[$ACC_pt]["27"], ($r_made_kharej_Az_shomool27 + $ExtraRoozMozd), $ACC_pt);
		$AccDocObj->AddItem($ACC_UnitID, $CostCodesArray[$ACC_pt]["38"], ($r_made_kharej_Az_shomool38 + $ExtraRoozMozd), $ACC_pt);
		$AccDocObj->AddItem($ACC_UnitID, $CostCodesArray[$ACC_pt]["extra"], $r_jazb_omana, $ACC_pt);

		$ACC_pt = ImportSalary::PERSON_TYPE_Contract;
		$AccDocObj->AddItem($ACC_UnitID, $CostCodesArray[$ACC_pt]["salary"], $gh_hoghoogh, $ACC_pt);
		$AccDocObj->AddItem($ACC_UnitID, $CostCodesArray[$ACC_pt]["overtime"], $gh_ezafeKar + $OverTimeGharardadi, $ACC_pt);
		$AccDocObj->AddItem($ACC_UnitID, $CostCodesArray[$ACC_pt]["OrgInsurance"], round($gh_bimeh_dastgah), $ACC_pt);
		$AccDocObj->AddItem($ACC_UnitID, $CostCodesArray[$ACC_pt]["GovInsurance"], $gh_bimeh_dolat, $ACC_pt);
		$AccDocObj->AddItem($ACC_UnitID, $CostCodesArray[$ACC_pt]["27"], ($gh_made_kharej_Az_shomool27 + $ExtraGharardadi), $ACC_pt);
		$AccDocObj->AddItem($ACC_UnitID, $CostCodesArray[$ACC_pt]["38"], ($gh_made_kharej_Az_shomool38 + $ExtraGharardadi), $ACC_pt);
		$AccDocObj->AddItem($ACC_UnitID, $CostCodesArray[$ACC_pt]["extra"], $gh_jazb_omana, $ACC_pt);

		if(ExceptionHandler::GetExceptionCount() > 0)
		{
			echo "مرکز هزینه : " . $title . "<br><hr><br>";
			echo "<span style=color:red><h3>" . ExceptionHandler::GetExceptionsToString ("<br>") . "</h3></span>";
			$AccError = true;
		}
		ExceptionHandler::PopAllExceptions();
	}
	//</editor-fold>

	echo '<tr>
			<td>علمی مشروط</td>
			<td>' . $mp_hoghoogh . '</td>
			<td>' . ($mp_ezafeKar + $OverTimeMProf) . '</td>
			<td>' . round($mp_bimeh_dastgah) . '</td>
			<td>' . $mp_bimeh_dolat . '</td>
			<td>' . ($mp_made_kharej_Az_shomool + $ExtraMProf) . '</td>
			<td>' . $mp_jazb_omana . '</td>
			<td>' . ($mp_sum + $ExtraMProf + $OverTimeMProf) . '</td>			
		  </tr>
		  <tr>
			 <td>هیات علمی</td>
			 <td>' . $p_hoghoogh . '</td>
			 <td>' . ($p_ezafeKar + $OverTimeProf ) . '</td>
			 <td>' . round($p_bimeh_dastgah) . '</td>
			 <td>' . $p_bimeh_dolat . '</td>
			 <td>' . ($p_made_kharej_Az_shomool + $ExtraProf) . '</td>
			 <td>' . $p_jazb_omana . '</td>
			 <td>' . ($p_sum + $ExtraProf + $OverTimeProf) . '</td>
		  </tr>
		  <tr>
			<td>کارمند</td>
			 <td>' . $e_hoghoogh . '</td>
			 <td>' . ($e_ezafeKar + $OverTimeEmp) . '</td>
			 <td>' . round($e_bimeh_dastgah) . '</td>
			 <td>' . $e_bimeh_dolat . '</td>
			 <td>' . ($e_made_kharej_Az_shomool + $ExtraEmp) . '</td>
			 <td>' . $e_jazb_omana . '</td>
			 <td>' . ($e_sum + $ExtraEmp + $OverTimeEmp ) . '</td>
		  </tr>
		  <tr>
			<td>روز مزدبیمه ای</td>
			 <td>' . $r_hoghoogh . '</td>
			 <td>' . ($r_ezafeKar + $OverTimeRoozMozd ) . '</td>
			 <td>' . round($r_bimeh_dastgah) . '</td>
			 <td>' . $r_bimeh_dolat . '</td>
			 <td>' . ($r_made_kharej_Az_shomool27 + $r_made_kharej_Az_shomool38 + $ExtraRoozMozd) . '</td>
			 <td>' . $r_jazb_omana . '</td>
			 <td>' . ($r_sum + $ExtraRoozMozd + $OverTimeRoozMozd ) . '</td>
		  </tr>
		  <tr>
			<td>قرارداد کار معین و یکساله</td>
			 <td>' . $gh_hoghoogh . '</td>
			 <td>' . ($gh_ezafeKar + $OverTimeGharardadi ) . '</td>
			 <td>' . round($gh_bimeh_dastgah) . '</td>
			 <td>' . $gh_bimeh_dolat . '</td>
			 <td>' . ($gh_made_kharej_Az_shomool27 + $gh_made_kharej_Az_shomool38 + $ExtraGharardadi) . '</td>
			 <td>' . $gh_jazb_omana . '</td>
			 <td>' . ($gh_sum + $ExtraGharardadi + $OverTimeGharardadi ) . '</td>
		  </tr>';

	if ($_POST['ReportType'] == 2 ) {

		if( $_POST["PayType"] == 1 ) {
			$qry = " select PlanItemTitle , PayValue 
								from PlanItemReport 
									   where RelatedItem in ( 0 , -1 )  and PayYear = " . $_POST["pay_year"] . " and PayMonth =" . $_POST["pay_month"];

			$res = PdoDataAccess::runquery($qry);
		}
		else 
		{
			$res = array() ; 
		}
		for ($j = 0; $j < count($res); $j++) {

			echo '<tr>
							<td>' . $res[$j]['PlanItemTitle'] . '</td>
							<td></td>
							<td></td>
							<td>' . $res[$j]['PayValue'] . '</td>
							<td></td>
							<td></td>
							<td></td>
							<td>' . $res[$j]['PayValue'] . '</td>
						</tr> ';

			$rowPlanItem += $res[$j]['PayValue'];
		}
	}

	echo
	'<tr style="background-color:#F0F8FF">
			<td style="font-family:b Titr;" >جمع</td>
			<td>' . ($mp_hoghoogh + $p_hoghoogh + $e_hoghoogh + $r_hoghoogh + $gh_hoghoogh ) . '</td>
			<td>' . ($mp_ezafeKar + $p_ezafeKar + $e_ezafeKar + $r_ezafeKar + $gh_ezafeKar + $OverTimeMProf + $OverTimeProf + $OverTimeEmp +
	$OverTimeRoozMozd + $OverTimeGharardadi ) . '</td>
			<td>' . (round($mp_bimeh_dastgah) + round($p_bimeh_dastgah) + round($e_bimeh_dastgah) + round($r_bimeh_dastgah) + round($gh_bimeh_dastgah) + $rowPlanItem ) . '</td>
			<td>' . ($mp_bimeh_dolat + $p_bimeh_dolat + $e_bimeh_dolat + $r_bimeh_dolat + $gh_bimeh_dolat ) . '</td>
			<td>' . ($mp_made_kharej_Az_shomool + $p_made_kharej_Az_shomool + $e_made_kharej_Az_shomool +
	$r_made_kharej_Az_shomool27 + $r_made_kharej_Az_shomool38 + $gh_made_kharej_Az_shomool27 + $gh_made_kharej_Az_shomool38 + $ExtraMProf + $ExtraProf + $ExtraEmp +
	$ExtraRoozMozd + $ExtraGharardadi ) . '</td>		
			<td>' . ($mp_jazb_omana + $p_jazb_omana + $e_jazb_omana + $r_jazb_omana + $gh_jazb_omana) . '</td>
			<td>' . ($mp_sum + $p_sum + $e_sum + $r_sum + $gh_sum + $ExtraMProf + $ExtraProf + $ExtraEmp + $ExtraRoozMozd + $ExtraGharardadi +
	$OverTimeMProf + $OverTimeProf + $OverTimeEmp + $OverTimeRoozMozd + $OverTimeGharardadi + $rowPlanItem ) . '</td>
		  </tr></table><br>';

	echo "</center>";

	if($regAccDoc)
	{
		$AccDocObj->CommitImportance();
		?>
		<script>
			var elems = document.getElementsByClassName("reportGenerator");
			for(i=0; i < elems.length; i++)
				elems[i].style.visibility = "hidden";
		</script>
		<?
		if(!$AccError)
		{
			ob_get_clean();
			echo "<h3>" . "پیش سند حقوق مربوط به این ماه با موفقیت صادر گردید." . "</h3>";
		}
			
	}

	die();
}
?>
<form id="mainForm">
    <center>
        <div id="mainpanel"></div>
    </center>    
</form>