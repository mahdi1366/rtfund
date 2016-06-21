<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	92.02
//---------------------------
require_once("../header.inc.php");

require_once 'professor_posts_report.js.php';
require_once inc_manage_unit;

if(isset($_GET['showRes']) && $_GET['showRes'] == 1 )
{	
	$where = " where (1=1)";   
	
		
//....شماره شناسایی..........
if(!empty($_POST['from_staff_id'])){
	
	$where.= " AND  s.staff_id >= ".$_POST['from_staff_id']  ; 
	
}

if(!empty($_POST['to_staff_id'])){
	
	$where.= " AND s.staff_id <= ".$_POST['to_staff_id']  ; 
	
}
//........واحد محل خدمت.....
if(!empty($_POST['ouid'])){
			
		$where .= " AND ( p.ouid = ".$_POST['ouid']." OR o.parent_path LIKE '%,".$_POST['ouid'].",%' OR  
						  o.parent_path LIKE '%".$_POST['ouid'].",%' OR o.parent_path LIKE '%,".$_POST['ouid']."%' OR o.parent_path LIKE '%".$_POST['ouid']."%'  ) ";
}

if(!empty($_POST['from_post_id'])){
	
	$where.= " AND  p.post_id >= ".$_POST['from_post_id']  ; 
	
}

if(!empty($_POST['to_post_id'])){
	
	$where.= " AND p.post_id <= ".$_POST['to_post_id']  ; 
	
}

//....... تاریخ -----

if(!empty($_POST['from_start_date'])){	
	$where.= " AND pep.from_date >= '".DateModules::shamsi_to_miladi($_POST['from_start_date'])."'" ; 
}
if(!empty($_POST['from_start_date'])){
	$where.= " AND pep.from_date <= '".DateModules::shamsi_to_miladi($_POST['from_start_date'])."'" ; 	
}

if(!empty($_POST['to_start_date'])){	
	$where.= " AND pep.to_date >= '".DateModules::shamsi_to_miladi($_POST['to_start_date'])."'" ; 
}
if(!empty($_POST['to_start_date'])){
	$where.= " AND pep.to_date <= '".DateModules::shamsi_to_miladi($_POST['to_start_date'])."'" ; 	
}

if(!empty($_POST['sl']) && $_POST['sl'] != -1 ){
	
	$where.= " AND p.post_type = ".$_POST['sl'] ; 
}

if( $_POST['included']!= "all" ){			
	$where.= " AND p.included = ".$_POST['included'] ; 
	
}

$qry = " " ; 
$selectStr = "" ; 

if(!empty($_POST['HavePost']) && $_POST['HavePost'] == 1 ){
	
	$qry = "  LEFT  JOIN managmnt_extra_bylaw_items bi
							ON(p.post_id = bi.post_id)
			  LEFT  JOIN management_extra_bylaw b 
							ON(bi.bylaw_id = b.bylaw_id) " ; 	
	
	$selectStr = ", bi.value mng_value " ; 
	
	$where.= " AND (((b.from_date <= '".DateModules::Now()."' AND ( b.to_date >= '".DateModules::Now()."' OR b.to_date IS NULL OR b.to_date = '0000-00-00')) OR bi.post_id IS NULL)) AND
				((pep.to_date IS NULL OR pep.to_date = '0000-00-00' OR pep.to_date >='".DateModules::Now()."')) " ; 		
}

$query = " SELECT s.staff_id , ps.PersonID, ps.pfname, ps.plname,
					p.post_no ,p.post_id , p.title, g2j(pep.from_date) from_date , pep.to_date to_date ,
					CASE p.included WHEN 1 THEN 'بلي' WHEN NULL THEN 'خير' END included_title,
					bi1.Title post_type_title, p.ouid /* , concat(o1.ptitle, ',', o.ptitle ) full_path */ $selectStr

				FROM professor_exe_posts pep

						INNER JOIN position p
								ON (pep.post_id = p.post_id )

						INNER JOIN staff s
								ON (pep.staff_id = s.staff_id)

						INNER JOIN Basic_Info bi1 
						        ON (bi1.InfoID = p.post_type and bi1.TypeID = 27 )

						INNER JOIN persons ps 
						        ON (s.PersonID = ps.PersonID)

						LEFT  JOIN org_new_units o 
						        ON (p.ouid = o.ouid)
						/*		
						LEFT OUTER JOIN org_new_units o1 
						        ON (o1.ouid = o.parent_ouid) */ ".$qry ; 

	$query .= $where ; 
	$query .=" order by s.staff_id " ; 
	
	$res = PdoDataAccess::runquery($query) ; 
	
	 for($i=0 ; $i < count($res) ; $i++ )
	 {
		$res[$i]['full_unit_title'] = manage_units::get_full_title($res[$i]['ouid']);
	 }
	
	 
?>

<html dir='rtl'>
	<head>
		<style>
		.reportGenerator {border-collapse: collapse;border: 1px solid black;font-family: tahoma;font-size: 8pt;
						  text-align: center;width: 80%;padding: 2px;}
		.reportGenerator .header {color: white;font-weight: bold;background-color:#3F5F96}
		.reportGenerator td {border: 1px solid #555555;height: 20px;}
		</style>
		<title>گزارش افراد دارای سمت </title>
		<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
		<link rel=stylesheet href="/HumanResources/css/writ.css">
	</head>
	<body>
		<center>
			<table width="50%" cellpadding="0" cellspacing="0">
				<tr>
					<td width="20%"><img src="/HumanResources/img/fum_symbol.jpg" ></td>
					<td align="center" style="font-family:'b titr'">گزارش  افراد دارای سمت </td>
					<td width="20%" align="left" style="font-family:tahoma;font-size:8pt">تاریخ : <?= DateModules::shNow() ?></td>
				</tr>
			</table>
			<table style="text-align: right;" class="reportGenerator" cellpadding="4" cellspacing="0" >
				<tr class="header">			
					<td width="4%">ردیف</td>
					<td width="10%">شماره شناسایی</td>
					<td width="12%">نام خانوادگی</td>
					<td width="8%">نام</td>
					<td width="21%">واحد محل خدمت</td>
					<td width="17%">عنوان</td>		
					<td width="8%">تاریخ شروع</td>		
					<td width="8%">تاریخ پایان</td>	
					<? if ( $_POST['HavePost'] == 1 ){ ?> <td width="12%">مبلغ </td>	<? }?>
					
				</tr>				
			</table>
			<table style="text-align: right;" class="reportGenerator" cellpadding="4" cellspacing="0" >
			<?			
			
			for($i=0 ; $i<count($res);$i++)
			{					
				echo "	<tr>
							<td width='4%'>".($i+1)."</td>
							<td width='10%'>".$res[$i]["staff_id"]."</td>
							<td width='12%'>".$res[$i]["plname"]."</td>
							<td width='8%'>".$res[$i]["pfname"]."</td>
							<td width='21%'>".$res[$i]["full_unit_title"]."</td>
							<td width='17%'>".$res[$i]["title"]."</td>		
							<td width='8%'>".$res[$i]["from_date"]."</td>		
							<td width='8%'>".DateModules::miladi_to_shamsi($res[$i]["to_date"])."</td>" ;
				
				if ($_POST['HavePost'] == 1)
					echo	"<td width='12%'>".$res[$i]["mng_value"]."</td></tr>" ; 					
			}
			
			?>							
			</table>
		</center>
	</body>
</html>

<?

die() ; 
	
}

$Drp_POST_TYPE = manage_domains::DRP_Post_Type("sl",""," ","width:150");
?>

<form id="form_SearchPost" >
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
			<td>واحد محل خدمت :</td>
			<td colspan="3">
				<input type="text" id="ouid">
			</td>
		</tr>
		<tr>
			<td> شناسه پست از : </td>
			<td><input type="text" class="x-form-text x-form-field" style="width: 50%" id="from_post_id" name="from_post_id"></td>
			<td>تا :</td>
			<td><input type="text" class="x-form-text x-form-field" style="width: 50%" id="to_post_id" name="to_post_id"></td>
		</tr>		
		<tr>
			<td>تاریخ شروع از : </td>
			<td><input type="text" class="x-form-text x-form-field" style="width: 80PX" id="from_start_date" name="from_start_date"></td>
			<td>تا :</td>
			<td><input type="text" class="x-form-text x-form-field" style="width: 80PX" id="to_start_date" name="to_start_date"></td>
		</tr>	
		<tr>
			<td>تاریخ پایان از : </td>
			<td><input type="text" class="x-form-text x-form-field" style="width: 80PX" id="from_end_date" name="from_end_date"></td>
			<td>تا :</td>
			<td><input type="text" class="x-form-text x-form-field" style="width: 80PX" id="to_end_date" name="to_end_date"></td>
		</tr>	
		<tr>
			<td> نوع پست :</td>
			<td colspan="3"><?= $Drp_POST_TYPE?></td>
		</tr>
		<tr>
			<td> داخل شمول ؟ : </td>
			<td>
				<input type="radio" id="included" name="included" value="all"   style="width:5%" size="10" >همه
				<input type="radio" id="included" name="included" value="1"   style="width:5%" size="10" > بلی
				<input type="radio" id="included" name="included" value="0"   style="width:5%" size="10" > خیر
			</td>
		</tr>
		<tr>
			<td colspan="4"> افرادی که هم اکنون دارای سمت هستند ؟  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="checkbox" value="1" id="HavePost"  name="HavePost"> </td>
		</tr>
				
	</table>	
</div>
</div>
</center>
</form>