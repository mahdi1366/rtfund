<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	92.02
//---------------------------
require_once("../header.inc.php");

require_once 'not_preferment_report.js.php';

if(isset($_GET['showRes']) && $_GET['showRes'] == 1 )
{
	
	$where = "(1=1)"; 
  
	$qry = "select InfoID from Basic_Info where TypeID = 3 " ; 
	$res = PdoDataAccess::runquery($qry) ; 
	$emp_state = "" ; 

	for($i=0 ; $i < count($res); $i++)
	{
		if(!empty($_POST['emp_state'.$res[$i]['InfoID']])){
			$emp_state .= $res[$i]['InfoID']."," ; 
		}	
	}

	$qry = "select InfoID from Basic_Info where TypeID = 4 " ; 
	$res2 = PdoDataAccess::runquery($qry) ; 
	$emp_mode = "" ; 

	for($i=0 ; $i < count($res2); $i++)
	{				
		if(!empty($_POST['emp_mod'.$res2[$i]['InfoID']])){
			$emp_mode .= $res2[$i]['InfoID']."," ; 
		}	
	}	

	$emp_mode = substr($emp_mode,0,-1) ; 
	$emp_state = substr($emp_state,0,-1) ; 


	if($emp_mode != "")
	   $where.= " and w.emp_mode in ($emp_mode) "  ; 

	if($emp_state != "")
	   $where.= " and w.emp_state in ($emp_state) "  ; 
	
	
	
//....شماره شناسایی..........
if(!empty($_POST['from_staff_id'])){
	
	$where.= " AND  w.staff_id >= ".$_POST['from_staff_id']  ; 
	
}

if(!empty($_POST['to_staff_id'])){
	
	$where.= " AND w.staff_id <= ".$_POST['to_staff_id']  ; 
	
}
//....... تاریخ -----

if(!empty($_POST['from_execute_date'])){	
	$where.= " AND w.execute_date >= '".DateModules::shamsi_to_miladi($_POST['from_execute_date'])."'" ; 
}
if(!empty($_POST['to_execute_date'])){
	$where.= " AND w.execute_date <= '".DateModules::shamsi_to_miladi($_POST['to_execute_date'])."'" ; 	
}
//.......پایه .......
if(!empty($_POST['from_cur_group'])){
	
	$where.= " AND w.base >= ".$_POST['from_cur_group'] ; 
	
}
if(!empty($_POST['to_cur_group'])){
	
	$where.= " AND w.base <= ".$_POST['to_cur_group'] ; 
	
}
//........واحد محل خدمت.....
if(!empty($_POST['ouid'])){
	
		$where.= " AND s.UnitCode = ".$_POST['ouid'] ; 
	
}
//...........مرتبه علمی...
if(!empty($_POST['sl']) && $_POST['sl'] != -1 ){
	
	$where.= " AND w.science_level = ".$_POST['sl'] ; 
}

	$query = "	SELECT  w.staff_id, p.plname, p.pfname,
						SUBSTR(MAX(CONCAT(w.execute_date,o.ouid)),11) AS ouid, s.UnitCode , 
						MAX(w.science_level) m_science_level , bi.Title science_level_title , 
						MAX(w.base) m_base,concat(o.parent_path, ',', o.ouid) full_path

				FROM writs w

						INNER JOIN staff s
									ON(w.staff_id = s.staff_id)

						INNER JOIN persons p
									ON(s.PersonID = p.PersonID)

						LEFT  JOIN org_new_units o 
									ON(w.ouid = o.ouid)

						LEFT  JOIN Basic_Info bi
									ON bi.typeid = 8 and bi.InfoID = w.science_level

				WHERE (w.history_only = 0 AND s.person_type=1) AND $where

				GROUP BY w.staff_id, p.plname, p.pfname

				HAVING COUNT(DISTINCT(w.base)) = 1

				ORDER BY full_path,plname,pfname " ; 


	$res = PdoDataAccess::runquery($query) ; 
	
	echo PdoDataAccess::GetLatestQueryString() ; die() ; 

?>

<html dir='rtl'>
	<head>
		<style>
		.reportGenerator {border-collapse: collapse;border: 1px solid black;font-family: tahoma;font-size: 8pt;
						  text-align: center;width: 50%;padding: 2px;}
		.reportGenerator .header {color: white;font-weight: bold;background-color:#3F5F96}
		.reportGenerator td {border: 1px solid #555555;height: 20px;}
		</style>
		<title>گزارش افرادی که ترفیع نگرفته اند </title>
		<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
		<link rel=stylesheet href="/HumanResources/css/writ.css">
	</head>
	<body>
		<center>
			<table width="50%" cellpadding="0" cellspacing="0">
				<tr>
					<td width="20%"><img src="/HumanResources/img/fum_symbol.jpg" ></td>
					<td align="center" style="font-family:'b titr'">گزارش  افرادی که ترفیع نگرفته اند</td>
					<td width="20%" align="left" style="font-family:tahoma;font-size:8pt">تاریخ : <?= DateModules::shNow() ?></td>
				</tr>
			</table>
			<table style="text-align: right;" class="reportGenerator" cellpadding="4" cellspacing="0" >
				<tr class="header">			
					<td width="10%">ردیف</td>
					<td width="20%">شماره شناسایی</td>
					<td width="25%">نام خانوادگی</td>
					<td width="15%">نام</td>
					<td width="20%">مرتبه علمی</td>
					<td width="10%">پایه</td>					
				</tr>				
			</table>
			<table style="text-align: right;" class="reportGenerator" cellpadding="4" cellspacing="0" >
			<?			
			for($i=0 ; $i<count($res);$i++)
			{					
				echo "	<tr>
							<td width='10%'>".($i+1)."</td>
							<td width='20%'>".$res[$i]["staff_id"]."</td>
							<td width='25%'>".$res[$i]["plname"]."</td>
							<td width='15%'>".$res[$i]["pfname"]."</td>
							<td width='20%'>".$res[$i]["science_level_title"]."</td>
							<td width='10%'>".$res[$i]["m_base"]."</td>
						</tr>	" ; 					
			}
			?>							
			</table>
		</center>
	</body>
</html>

<?

die() ; 
	
}

$Drp_Science_Level = manage_domains::DRP_Science_Level("sl",""," ","width:100");
$chk_emp_state = manage_domains::CHK_employee_states("emp_state",array(),5);
$chk_emp_mod = manage_domains::CHK_employee_modes("emp_mod",array(),5);

?>

<form id="form_SearchPrefer" >
<center>
<div>
<div id="AdvanceSearchDIV">
	<table style="width:100%" id="searchTBL">
		<tr>
			<td> شماره شناسایی از:</td>
			<td><input type="text" class="x-form-text x-form-field" style="width: 50%" id="from_staff_id" name="from_staff_id"></td>
			<td>تا :</td>
			<td><input type="text" class="x-form-text x-form-field" style="width: 50%" id="to_staff_id" name="to_staff_id"></td>
		</tr>
		<tr>
			<td>از تاریخ :</td>
			<td><input type="text" class="x-form-text x-form-field" style="width: 80PX" id="from_execute_date" name="from_execute_date"></td>
			<td>تا :</td>
			<td><input type="text" class="x-form-text x-form-field" style="width: 80PX" id="to_execute_date" name="to_execute_date"></td>
		</tr>	
		<tr>
			<td> پایه از : </td>
			<td><input type="text" class="x-form-text x-form-field" style="width: 50%" id="from_base" name="from_cur_group"></td>
			<td> تا : </td>
			<td><input type="text" class="x-form-text x-form-field" style="width: 50%" id="to_base" name="to_cur_group"></td>
		</tr>	
		<tr>
			<td>واحد محل خدمت :</td>
			<td colspan="3">
				<input type="text" id="ouid">
			</td>
		</tr>
		<tr>
			<td> مرتبه علمی : </td>
			<td colspan="3"><?= $Drp_Science_Level?></td>
		</tr>
		<tr>
			<td colspan="4" valign="top" align="center">
				<div id="FS_emp_state" style="width:98%"><div id="FS_emp_state2">
					<?= $chk_emp_state ?>
				</div></div>
			</td>
		</tr>
		<tr>
			<td colspan="4" valign="top" align="center">
				<div id="FS_emp_mod" style="width:98%"><div id="FS_emp_mod2">
					<?= $chk_emp_mod ?>
				</div></div>
			</td>
		</tr>	
	</table>	
</div>
</div>
</center>
</form>