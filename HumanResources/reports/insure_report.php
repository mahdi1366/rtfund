<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	92.05
//---------------------------
require_once("../header.inc.php");

require_once 'insure_report.js.php';

if(isset($_GET['showRes']) && $_GET['showRes'] == 1 )
{	
	$where = " where (1=1)";   
	
//....شماره شناسایی..........
if(!empty($_POST['from_person_id'])){
	
	$where.= " AND  pd.PersonID >= ".$_POST['from_person_id']  ; 
	
}

if(!empty($_POST['to_person_id'])){
	
	$where.= " AND pd.PersonID <= ".$_POST['to_person_id']  ; 
	
}

$qry = " select  pd.*,
				 bi.Title ,
				 bi.TypeID

		 from  person_dependents pd		INNER JOIN Basic_Info bi 
											ON ( bi.InfoID = pd.dependency  and bi.TypeID = 1 ) ".$where ." 
												
		 order by  pd.personid  "  ; 

$temp = PdoDataAccess::runquery($qry) ; 

for($i=0 ; $i<count($temp);$i++)
{
	$query = "select 
						bi.Title     	insure_type , pds.from_date 
				from person_dependent_supports  pds inner join Basic_Info bi on pds.insure_type =  bi.InfoID and bi.TypeID = 30

				where PersonID = ".$temp[$i]['PersonID']." AND
					master_row_no = ".$temp[$i]['row_no']." AND
					(pds.from_date <='".date('Y-m-d')."' AND
					(pds.to_date >= '".date('Y-m-d')."' OR pds.to_date IS  NULL OR  pds.to_date = '0000-00-00' ) AND
					pds.status IN (1,2))  ";

	$tmp = PdoDataAccess::runquery($query);

	if( count($tmp) == 0 ){
		
		$temp[$i]['insure_type'] =""; 
		$temp[$i]['from_date'] =""; 
		}
	else     { 
		$temp[$i]['insure_type'] = $tmp[0]["insure_type"]; 
		$temp[$i]['from_date'] = $tmp[0]["from_date"]; 
		}
}
       
	
	// echo PdoDataAccess::GetLatestQueryString() ; die() ; 
?>

<html dir='rtl'>
	<head>
		<style>
		.reportGenerator {border-collapse: collapse;border: 1px solid black;font-family: tahoma;font-size: 8pt;
						  text-align: center;width: 80%;padding: 2px;}
		.reportGenerator .header {color: white;font-weight: bold;background-color:#3F5F96}
		.reportGenerator td {border: 1px solid #555555;height: 20px;}
		</style>
		<title>گزارش افراد  تحت تکفل </title>
		<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
		<link rel=stylesheet href="/HumanResources/css/writ.css">
	</head>
	<body>
		<center>
			<table width="50%" cellpadding="0" cellspacing="0">
				<tr>
					<td width="20%"><img src="/HumanResources/img/fum_symbol.jpg" ></td>
					<td align="center" style="font-family:'b titr'">گزارش  افراد  تحت تکفل</td>
					<td width="20%" align="left" style="font-family:tahoma;font-size:8pt">تاریخ : <?= DateModules::shNow() ?></td>
				</tr>
			</table>
			<table style="text-align: right;" class="reportGenerator" cellpadding="4" cellspacing="0" >
				<tr class="header">			
					<td width="4%">ردیف</td>					
					<td width="12%">نام خانوادگی</td>
					<td width="8%">نام</td>
					<td width="10%">وابستگی</td>
					<td width="21%">ش ش</td>
					<td width="17%">تاریخ تولد</td>		
					<td width="8%">نام پدر</td>		
					<td width="8%">نوع بیمه</td>	
					<td width="8%"> تاریخ شروع بیمه</td>											
				</tr>				
			</table>
			<table style="text-align: right;" class="reportGenerator" cellpadding="4" cellspacing="0" >
			<?			
			
			for($i=0 ; $i<count($temp);$i++)
			{					
				echo "	<tr>
							<td width='4%'>".($i+1)."</td>							
							<td width='12%'>".$temp[$i]["lname"]."</td>
							<td width='8%'>".$temp[$i]["fname"]."</td>
							<td width='10%'>".$temp[$i]["Title"]."</td>
							<td width='21%'>".$temp[$i]["idcard_no"]."</td>
							<td width='17%'>".DateModules::miladi_to_shamsi($temp[$i]["birth_date"])."</td>		
							<td width='8%'>".$temp[$i]["father_name"]."</td>		
							<td width='8%'>".$temp[$i]["insure_type"]."</td>		
							<td width='8%'>".DateModules::miladi_to_shamsi($temp[$i]["from_date"])."</td>".		
					"</tr>" ; 					
			}
			
			?>							
			</table>
		</center>
	</body>
</html>

<?

die() ; 
	
}

?>

<form id="form_SearchPost" >
<center>
<div>
<div id="AdvanceSearchDIV">
	<table style="width:100%" id="searchTBL">
		<tr>
			<td> شماره شناسایی از:</td>
			<td><input type="text" class="x-form-text x-form-field" style="width: 50%" id="from_person_id" name="from_person_id"></td>
			<td>تا :</td>
			<td><input type="text" class="x-form-text x-form-field" style="width: 50%" id="to_person_id" name="to_person_id"></td>
		</tr>
	</table>	
</div>
</div>
</center>
</form>