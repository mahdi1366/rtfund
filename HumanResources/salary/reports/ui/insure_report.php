<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	91.08.07
//---------------------------
require_once("../../../header.inc.php");
if(!isset($_REQUEST["show"]))
    require_once '../js/insure_report.js.php';
require_once "ReportGenerator.class.php";

 $whr = " " ; 
 
 
if(isset($_REQUEST["show"]))
{
			
	$whereParam = array();
       
	if(!empty($_POST["pay_month"]))
	{
		$whr .= " AND pit.pay_month = :pm ";   		
		$whereParam[":pm"] = $_POST["pay_month"];
	}
	if(!empty($_POST["pay_year"]))
	{
		$whr .= " AND pit.pay_year=:py";             
		$whereParam[":py"] = $_POST["pay_year"];
	}

	if(!empty($_POST["FName"]))
	{
		$whr .= " AND p.pfname like :fn ";   		
		$whereParam[":fn"] = "%".$_POST["FName"]."%";
	}
	if(!empty($_POST["LName"]))
	{
		$whr .= " AND p.plname like :pl ";   		
		$whereParam[":pl"] = "%".$_POST["LName"]."%";
	}

	if(!empty($_POST["PersonType"]))
	{
		$whr .= " AND p.person_type=:pt";
		$whereParam[":pt"] = $_POST["PersonType"]; 		
	}

		    		  
 $query = "	select  s.staff_id , p.pfname , p.plname , pit.pay_year , pit.pay_month , 
					case p.person_type when 2 then 'کارمند' when 10 then 'بازنشسته' end pt ,
					get_value , diff_get_value

				from payment_items pit inner join staff s
												on pit.staff_id = s.staff_id
									   inner join persons p
												on s.personid = p.personid

 					where  salary_item_type_id in (38,10032) ".$whr ;
                      
 $dataTable = PdoDataAccess::runquery($query, $whereParam);

if($_SESSION['UserID'] == 'jafarkhani'){
	//echo PdoDataAccess::GetLatestQueryString() ; die() ; 
}
	
	
?>
<style>
		.reportGenerator {border-collapse: collapse;border: 1px solid black;font-family: tahoma;font-size: 8pt;
						  text-align: center;width: 50%;padding: 2px;}
		.reportGenerator .header {color: white;font-weight: bold;background-color:#3F5F96}
		.reportGenerator td {border: 1px solid #555555;height: 20px;}
</style>
<?		
	
	echo '<META http-equiv=Content-Type content="text/html; charset=UTF-8" ><body dir="rtl"><center>';
	
	 echo '<table  class="reportGenerator" style="text-align: right;width:50%!important" cellpadding="4" cellspacing="0">
		 <tr class="header">					
			<td>ردیف </td>
			<td>شماره شناسایی</td>
			<td>نام</td>
			<td>نام خانوادگی</td>			
			<td>نوع شخص</td>	
			<td>سال</td>
			<td>ماه</td>
			<td>مبلغ بیمه خدمات درمانی</td>			
		</tr>' ; 
	
	for($i=0 ; $i < count($dataTable) ; $i++)
	    {	 
	  
	     echo " <tr>
			    <td>".( $i + 1 )."</td>
			    <td>".$dataTable[$i]['staff_id']."</td>
			    <td>".$dataTable[$i]['pfname']."</td>
			    <td>".$dataTable[$i]['plname']."</td>
				<td>".$dataTable[$i]['pt']."</td>
				<td>".$dataTable[$i]['pay_year']."</td>
			    <td>".$dataTable[$i]['pay_month']."</td>
			    <td>".number_format($dataTable[$i]['get_value'], 0, '.', ',')."</td>
		    </tr>" ; 
		
	    }
	
	
}

?>
<form id="mainForm">
    <center>
        <div id="mainpanel"></div>
    </center>    
</form>