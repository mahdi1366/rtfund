<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	94.02
//---------------------------
require_once("../header.inc.php");

require_once 'person_gradation_report.js.php';

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

//........واحد محل خدمت.....
if(!empty($_POST['ouid'])){
	
		$where.= " AND s.UnitCode = ".$_POST['ouid'] ; 
	
}
//...........مرتبه علمی...
if(!empty($_POST['sl']) && $_POST['sl'] != -1 ){
	
	$where.= " AND w.science_level = ".$_POST['sl'] ; 
}

	$query = "	SELECT  s.staff_id,
						w.ouid,
						w.writ_id,
						w.emp_mode,
						ps.pfname,
						ps.plname,
						w.science_level,
						w.execute_date,
						w.science_level,
						o.ptitle unit_title, 
                                                bi.Title science_title

				FROM staff s
						LEFT  JOIN writs w
							ON (s.last_writ_id = w.writ_id AND s.last_writ_ver = w.writ_ver )
						LEFT  JOIN persons ps
							ON (s.PersonID = ps.PersonID)
						LEFT  JOIN org_new_units o
							ON (o.ouid = w.ouid)
                                                LEFT JOIN Basic_Info bi 
							ON   bi.InfoID = w.science_level AND bi.TypeID = 8
				WHERE s.person_type = 1 AND ".$where."

				ORDER BY w.ouid,ps.plname,pfname " ; 
   $res = PdoDataAccess::runquery($query) ; 
   
echo $query ; die();
//.......................................................
	
	$key = 0;
	foreach ($res as $rec) {

		$science_level=$rec['science_level']-1;

		switch ($science_level){
				case 1:
					$tp='مربي آموزشيار';
					break;
				case 2:
					$tp='مربي';
					break;
				case 3:
					$tp=('استاديار');
					break;
				case 4:
					$tp=('دانشيار');
					break;
				case 5:
					$tp=('استاد');
					break;
				default:
						$tp=('--');
						break;
			}
			
			//..................
			
			$qry = " select count(*) cn 
						from writs 
							where science_level = ".$science_level." and  staff_id =".$rec['staff_id'] ; 
			$resItm = PdoDataAccess::runquery($qry) ; 
			 
			if($resItm[0]['cn'] > 0 ) 
			{
				$qry = " select execute_date 
							from writs 
								where science_level= ".$rec['science_level']." and  staff_id = ".$rec['staff_id']." 
									order by execute_date limit 1 " ;
				$resExe = PdoDataAccess::runquery($qry) ; 
				
				$date = $resExe[0]['execute_date'] ; 
				$result [$key]['staff_id']		 	 =$rec['staff_id'];
				$result [$key]['writ_id']			 =$rec['writ_id'];
				$result [$key]['emp_mode']			 =$rec['emp_mode'];
				$result [$key]['pfname']			 =$rec['pfname'];
				$result [$key]['plname']			 =$rec['plname'];
				$result [$key]['unit_title']		 =$rec['unit_title'];
				$result [$key]['sub_title']			 =$rec['sub_title'];
				$result [$key]['ptitle']			 =$rec['ptitle'];
				$result [$key]['pre_sience_level']   =$tp;
				$result [$key]['ouid']			     =$rec['ouid'];
				$result [$key]['science_title']		 =$rec['science_level_title'];
				$result [$key]['execute_date']		 =DateModules::miladi_to_shamsi($date);
				$key++;
			}
						
		}


		
?>

<html dir='rtl'>
	<head>
		<style>
		.reportGenerator {border-collapse: collapse;border: 1px solid black;font-family: tahoma;font-size: 8pt;
						  text-align: center;width: 50%;padding: 2px;}
		.reportGenerator .header {color: white;font-weight: bold;background-color:#3F5F96}
		.reportGenerator td {border: 1px solid #555555;height: 20px;}
		</style>
		<title>گزارش ارتقاء مرتبه</title>
		<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
		<link rel=stylesheet href="/HumanResources/css/writ.css">
	</head>
	<body>
		<center>
			<table width="50%" cellpadding="0" cellspacing="0">
				<tr>
					<td width="20%"><img src="/HumanResources/img/fum_symbol.jpg" ></td>
					<td align="center" style="font-family:'b titr'">گزارش ارتقاء مرتبه</td>
					<td width="20%" align="left" style="font-family:tahoma;font-size:8pt">تاریخ : <?= DateModules::shNow() ?></td>
				</tr>
			</table>
			<table style="text-align: right;" class="reportGenerator" cellpadding="4" cellspacing="0" >
				<tr class="header">			
					<td width="10%">ردیف</td>
					<td width="20%">شماره شناسایی</td>
					<td width="25%">نام خانوادگی</td>
					<td width="15%">نام</td>
					<td width="20%"> مرتبه علمی قبلی</td>
					<td width="10%">مرتبه علمی فعلی</td>	
					<td width="10%"> تاریخ ارتقاء</td>	
				</tr>				
			</table>
			<table style="text-align: right;" class="reportGenerator" cellpadding="4" cellspacing="0" >
			<?			
			for($i=0 ; $i<count($result);$i++)
			{					
				echo "	<tr>
							<td width='10%'>".($i+1)."</td>
							<td width='20%'>".$result[$i]["staff_id"]."</td>
							<td width='25%'>".$result[$i]["plname"]."</td>
							<td width='15%'>".$result[$i]["pfname"]."</td>
							<td width='20%'>".$result[$i]["pre_sience_level"]."</td>
							<td width='10%'>".$result[$i]["science_title"]."</td>
							<td width='10%'>".$result[$i]["execute_date"]."</td>
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

<form id="form_SearchGrad" >
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