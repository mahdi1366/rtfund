<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	92.11
//---------------------------
require_once("../../../header.inc.php");
if(!isset($_REQUEST["showRes"]))
    require_once '../js/mission_report.js.php';
require_once "ReportGenerator.class.php";
 
if(isset($_GET['showRes']) && $_GET['showRes'] == 1 )
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
	
		if($_POST["pay_month"] < 7 )
			$dayNo = 31; 
		elseif( $_POST["pay_month"] > 6 &&  $_POST["pay_month"] < 12  )
			$dayNo = 30; 
		else 
			$dayNo = 29 ; 
		
        $month_start = DateModules::shamsi_to_miladi($_POST["pay_year"]."/".$_POST["pay_month"]."/01")  ;
        $month_end = DateModules::shamsi_to_miladi($_POST["pay_year"]."/".$_POST["pay_month"]."/".$dayNo)  ;       
		
        $query = " select pit.staff_id , p.pfname , p.plname , mli.duration , mli.region_coef , mli.destination , mli.PayValue ,pit.param1 , s.account_no

					from payment_items pit  inner join staff s
												on pit.staff_id = s.staff_id
											inner join persons p
												on s.personid = p.personid
											inner join hrmstotal.mission_list_items mli
												on mli.staff_id = pit.staff_id
											inner join hrmstotal.pay_get_lists pgl
												on mli.list_id = pgl.list_id and
												   pgl.list_date >= '".$month_start."' and pgl.list_date <= '".$month_end."'

					where pay_year = ".$_POST['pay_year']." and pay_month = ".$_POST['pay_month']." and payment_type = 8 " ;
					/*	
					UNION ALL 
					
					select pit.staff_id , p.pfname , p.plname , mli.duration , mli.region_coef , mli.destination , mli.PayValue ,pit.param1 , s.account_no

					from payment_items pit  inner join staff s
												on pit.staff_id = s.staff_id
											inner join persons p
												on s.personid = p.personid
											inner join hrmstotal.mission_list_items mli
												on mli.staff_id = pit.staff_id
											inner join hrmstotal.pay_get_lists pgl
												on mli.list_id = pgl.list_id and
												   pgl.list_date >= '".$month_start."' and pgl.list_date <= '".$month_end."'

					where pay_year = ".$_POST['pay_year']." and pay_month = ".$_POST['pay_month']." and payment_type = 8" ;  */
                     
		$dataTable = PdoDataAccess::runquery($query, $whereParam);
        
	 if($_SESSION["UserID"] == "bmahdipour" ) {
		 
		//  echo PdoDataAccess::GetLatestQueryString() ; die(); 
	}
	
       $qry = " select bi.Title month_title 
                        from  Basic_Info bi 
                                where  bi.typeid = 41 AND InfoID = ".$_POST["pay_month"] ; 
       $res = PdoDataAccess::runquery($qry) ; 
       $month = $res[0]['month_title'] ; 
      
	   
	if($_GET['excel'] == 'true')
	{
		ini_set("display_errors","On") ; 
	
		require_once 'excel.php';
		require_once "php_writeexcel-0.3.0/class.writeexcel_workbook.inc.php";
		require_once "php_writeexcel-0.3.0/class.writeexcel_worksheet.inc.php";
		
		$workbook = &new writeexcel_workbook("/tmp/temp.xls");
		$worksheet =& $workbook->addworksheet("Sheet1");
		$heading =& $workbook->addformat(array('align' => 'center', 'bold' => 1, 'bg_color' => 'blue', 'color' => 'white'));

		$worksheet->write(0, 0, "ردیف", $heading);
		$header = array("شماره شناسایی ",
						"نام",
						"نام خانوادگی" ,
						"مقصد" ,
						"مدت" ,
						"ضریب منطقه" ,
  					"حقوق و مزایای مشمول " ,    
				           "مبلغ " , 
						"شماره حساب" 
						) ;
		
			for ($i = 0; $i< count($header); $i++)
			{
				$worksheet->write(0, $i+1 , $header[$i], $heading);			
			}
			              
			$content = array( "staff_id" , "pfname" , "plname" , "destination" , "duration" , "region_coef" , "param1" , "PayValue" , "account_no" ); 
			
			for($index=0; $index < count($dataTable); $index++)
			{
				$row = $dataTable[$index];
			
				$worksheet->write($index+1, 0, ($index+1));
				
				for ($i = 0; $i < count($content); $i++)
				{
					$val = "";
					/*if(!empty($this->columns[$i]->renderFunction))
						eval("\$val = " . $this->columns[$i]->renderFunction . "(\$row,\$row[\$this->columns[\$i]->field]);");
					else*/
					$val = $row[$content[$i]];					
                    $val = ( is_int($val) ) ? round($val) : $val ; 
					$worksheet->write($index+1, $i+1, $val);
				}
			}
		
			$workbook->close();
			
			header("Content-type: application/ms-excel");
			header("Content-disposition: inline; filename=excel.xls");
			
			echo file_get_contents("/tmp/temp.xls");
			unlink("/tmp/temp.xls");
			die();			
	}
		   
    ?>
<style>
		.reportGenerator {border-collapse: collapse;border: 1px solid black;font-family: tahoma;font-size: 8pt;
			text-align: center;width: 50%;padding: 2px;}
		.reportGenerator .header {color: white;font-weight: bold;background-color:#3F5F96}
		.reportGenerator td {border: 1px solid #555555;height: 20px;}
</style>
       	
    <?    
		echo '<META http-equiv=Content-Type content="text/html; charset=UTF-8" ><body dir="rtl">';
		echo "<center><table style='border:2px groove #9BB1CD;border-collapse:collapse;width:100%'><tr>
				<td width=60px><img src='/HumanResources/img/fum_symbol.jpg'></td>
				<td align='center' style='font-family:b titr;font-size:15px'>گزارش پرداخت ماموریت  ".$month." ماه ".
                                  $_POST['pay_year']." </td>				
				<td width='200px' align='center' style='font-family:tahoma;font-size:11px'>تاریخ تهیه گزارش : " 
		     . DateModules::shNow() . "<br>";		
	echo "</td></tr></table>";      
	
        echo '<table  class="reportGenerator" style="text-align: right;width:100%!important" cellpadding="4" cellspacing="0">
		 <tr class="header">					
			<td>ردیف </td>
			<td>شماره شناسایی</td>
			<td>نام </td>
			<td> نام خانوادگی </td>			
			<td> مقصد </td>
			<td>مدت</td>
			<td>ضریب منطقه</td>
			<td>حقوق و مزایا مشمول</td>					
			<td>مبلغ</td>
			<td>شماره حساب</td>
		</tr>' ; 
	$sum = 0 ; 
	for($i=0 ; $i < count($dataTable) ; $i++)
	    {
		if($i> 0 && $i%35 == 0 )
		{
				echo '</table><hr style="page-break-after:always; visibility: hidden"><br><br>';
				echo '<table  class="reportGenerator" style="text-align: right;width:100%!important" cellpadding="4" cellspacing="0">
						<tr class="header">					
							<td>ردیف </td>
							<td>شماره شناسایی</td>
							<td>نام </td>
							<td> نام خانوادگی </td>			
							<td> مقصد </td>
							<td>مدت</td>
							<td>ضریب منطقه</td>
							<td>حقوق و مزایا مشمول</td>					
							<td>مبلغ</td>
							<td>شماره حساب</td>
						</tr>' ; 
		}
	     echo " <tr>
			    <td>".( $i + 1 )."</td>
			    <td>".$dataTable[$i]['staff_id']."</td> 
			    <td>".$dataTable[$i]['pfname']."</td>
			    <td>".$dataTable[$i]['plname']."</td>
				<td>".$dataTable[$i]['destination']."</td>
				<td>".$dataTable[$i]['duration']."</td>
				<td>".$dataTable[$i]['region_coef']."</td>
				<td>".number_format($dataTable[$i]['param1'], 0, '.', ',')."</td>
				<td>".number_format($dataTable[$i]['PayValue'], 0, '.', ',')."</td>
			    <td>".$dataTable[$i]['account_no']."</td>
		    </tr>" ; 
		 
		 $sum += $dataTable[$i]['PayValue']; 
		
	    }
		echo "<tr style='font-weight:bold' ><td colspan='8' align='left' >جمع: </td><td colspan='2' >".number_format($sum, 0, '.', ',')."</td></tr>" ;
	   
	    echo "</table></center>" ; 
   
}



?>
<form id="mainForm">
    <center>
        <div id="mainpanel"></div>
    </center>    
</form>