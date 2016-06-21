<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	91.5
//---------------------------
require_once("../header.inc.php");
require_once inc_manage_unit;

if(!empty($_REQUEST['Fdate'])){	
    $whereW .= " AND w.execute_date >= '".DateModules::shamsi_to_miladi($_REQUEST['Fdate'])."'  " ; 	
}

if(!empty($_REQUEST['Tdate'])){	
    $whereW .= " AND w.execute_date <= '".DateModules::shamsi_to_miladi($_REQUEST['Tdate'])."'" ; 
}

if(!empty($_REQUEST['UID']) && $_REQUEST['UID'] != 1 ){

	$whrUID = " AND s.UnitCode = ".$_POST['unitId'] ; 

}

if($_REQUEST['pt'] ==1 )
	$pt = " 1 " ; 
else 
	$pt = " 2,3,5 " ;


if( isset($_REQUEST['empMode']) && isset($_REQUEST['pt']) ) 
{
	$query = " select s.staff_id , p.PersonID , p.pfname , p.plname , w.ouid 

				 from persons p inner join staff s on p.personid = s.personid
								inner join (SELECT staff_id, SUBSTRING_INDEX(SUBSTRING(max_execute_date,11),'.',1) writ_id,
									   SUBSTRING_INDEX(max_execute_date,'.',-1) writ_ver FROM (SELECT w.staff_id,
									   max( CONCAT(w.execute_date,w.writ_id,'.',w.writ_ver) ) max_execute_date

									   FROM writs w INNER JOIN staff ls ON(w.staff_id = ls.staff_id)
									   WHERE w.history_only = 0 ".$whereW."
									   GROUP BY w.staff_id)tbl2) tbl1
					  on s.staff_id = tbl1.staff_id

					  inner join writs w
						   on tbl1.writ_id = w.writ_id and tbl1.writ_ver = w.writ_ver and tbl1.staff_id = w.staff_id

			where  w.emp_mode = ".$_REQUEST['empMode']." AND w.person_type in(".$pt.") ".$whereW." ".$whrUID."

			 " ; 
    $data = PdoDataAccess::runquery($query) ; 
	
	if($_SESSION['UserID'] == 'jafarkhani') 
	{	
		//echo $query ; die();
	}
	
}

?>
<html dir='rtl'>
	<head>
		<style>
		.reportGenerator {border-collapse: collapse;border: 1px solid black;font-family: tahoma;font-size: 8pt;
			text-align: center;width: 70%;padding: 2px;}
		.reportGenerator .header {color: white;font-weight: bold;background-color:#3F5F96}
		.reportGenerator td {border: 1px solid #555555;height: 20px;}
		</style>
		<title>لیست پرسنل </title>
		<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
		<link rel=stylesheet href="/HumanResources/css/writ.css">
	</head>
	<body>
		<center>
			<table width="70%" cellpadding="0" cellspacing="0">
				<tr class="header" >
					<td width="20%"><img src="/HumanResources/img/fum_symbol.jpg" ></td>
					<td align="center" style="font-family:b titr;font-size: 9pt;font-weight: bold;"><?=$title?></td>
					<td width="20%" align="left" style="font-family:tahoma;font-size:8pt">تاریخ :  <?= DateModules::shNow()?></td>
				</tr>
			</table>
			<table style="text-align: right;" class="reportGenerator" cellpadding="4" cellspacing="0">
			
				<tr class="header">					
					<td width="10%">شماره شناسایی</td>
					<td>نام</td>
					<td>نام خانوادگی</td>
					<td>عنوان کامل محل خدمت</td>
					<td>&nbsp;</td>
				</tr>
				<?
						for($i=0; $i< count($data) ; $i++)
						{
							echo " <tr>
									<td>".$data[$i]['staff_id']."</td><td>".$data[$i]['pfname']."</td><td>".$data[$i]['plname']."</td>
									<td>".manage_units::get_full_title($data[$i]['ouid'])."</td>
									<td><img src='../salary/reports/ui/showImage.php?PersonID=".$data[$i]['PersonID']."' style='height:100px;width:80px;'></td>				
								   </tr>";                                     
						}
					
							
				?>							
			</table>
			
		</center>
	</body>
</html>
