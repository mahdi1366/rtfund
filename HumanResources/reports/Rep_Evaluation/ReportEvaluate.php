<?php
//---------------------------
// programmer:	Gholami
// create Date:	93.07
//---------------------------
ini_set("display_errors","on"); 
require_once("../../header.inc.php");

if(!isset($_REQUEST["showRes"]))
require_once 'ReportEvaluate.js.php';

 
if(isset($_GET['showRes']) && $_GET['showRes'] == 1 )
{	
	$where = " 1=1 ";
	$param = array();

	if(!empty($_POST["FName"]))
	{
		$where .= " AND pfname like :fn ";   		
		$param[":fn"] = "%".$_POST["FName"]."%";
	}
	if(!empty($_POST["LName"]))
	{
		$where .= " AND plname like :pl ";   		
		$param[":pl"] = "%".$_POST["LName"]."%";
	}

	$query="SELECT (SELECT count(*) as ba 
				FROM ease.SEVL_EvlForms where s.SupervisorID=SupervisorID) as TotalCount ,

	(select count(*) as b FROM ease.SEVL_EvlForms 
		LEFT JOIN hrmstotal.persons persons1 on (persons1.PersonID=SEVL_EvlForms.SupervisorID) 
	where   (colleague1ID!='0' or colleague2ID!='0' or colleague3ID!='0') and s.SupervisorID=SupervisorID
	group by SupervisorID
	) as Count,


	(select count(*) as bop FROM ease.SEVL_EvlForms 
	where   FormStatus=4 and s.SupervisorID=SupervisorID
	group by SupervisorID) as TotalCo ,



	o.ptitle,(u.ptitle) as p1,u.ouid,concat(persons1.pfname, ' ', persons1.plname) as persons1_FullName,persons1.PersonID,SupervisorID,
	persons1.pfname, persons1.plname,concat(persons2.pfname, ' ', persons2.plname) as persons2_FullName
	,group_concat(distinct u.ptitle) as p2




	FROM ease.SEVL_EvlForms s
	LEFT JOIN hrmstotal.persons persons1 on (persons1.PersonID=s.SupervisorID)
 	LEFT JOIN hrmstotal.persons persons2 on (persons2.PersonID=s.PersonID)

	LEFT JOIN hrmstotal.staff st on (persons1.PersonID=st.PersonID and st.person_type=persons1.person_type)
 	LEFT JOIN hrmstotal.staff stt on (persons2.PersonID=stt.PersonID and stt.person_type=persons2.person_type)

	LEFT JOIN hrmstotal.org_new_units  o on (o.ouid=st.UnitCode)
	LEFT JOIN hrmstotal.org_new_units  u on (u.ouid=stt.UnitCode)

	where persons1.PersonID!=''  and $where 
	group by s.SupervisorID
	order by u.ouid


	 ";
                     
	$dataTable = PdoDataAccess::runquery($query,$param);
	      
	   
	if(isset($_GET['excel']) && $_GET['excel'] == 'true')
	{
		ini_set("display_errors","On") ; 
	
		require_once 'excel.php';
		require_once "php_writeexcel-0.3.0/class.writeexcel_workbook.inc.php";
		require_once "php_writeexcel-0.3.0/class.writeexcel_worksheet.inc.php";
		
		$workbook = &new writeexcel_workbook("/tmp/temp.xls");
		$worksheet =& $workbook->addworksheet("Sheet1");
		$heading =& $workbook->addformat(array('align' => 'center', 'bold' => 1, 'bg_color' => 'blue', 'color' => 'white'));

		$worksheet->write(0, 0, "ردیف", $heading);
		$header = array("نام ",
						"نام خانوادگی",
						"واحد محل خدمت" ,
						"تعداد کل" ,
						"تعدادی که تعیین شاخص شده" ,
						"تعدادی که توسط مدیر تایید شده " ,
						) ;
		
			for ($i = 0; $i< count($header); $i++)
			{
				$worksheet->write(0, $i+1 , $header[$i], $heading);			
			}
			              
			$content = array( "pfname" , "plname" , "p2" , "TotalCount" , "Count" ,"TotalCo" ); 
			
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
		echo "<center><table style='border:2px groove #9BB1CD;border-collapse:collapse;width:70%'><tr>
				<td width=60px><img src='/HumanResources/img/fum_symbol.jpg'></td>";
					
	echo "</tr></table>";      
	
        echo '<table  class="reportGenerator" style="text-align: right;width:70%!important" cellpadding="4" cellspacing="0">
		 <tr class="header">					
			<td>ردیف </td>
			<td>نام </td>
			<td> نام خانوادگی </td>
			<td> واحد محل خدمت </td>								
			<td> تعداد کل </td>
			<td>تعدادی که تعیین شاخص شده</td>
			<td>تعدادی که توسط مدیر تایید شده</td>
			
		</tr>' ; 
	$sum = 0 ; 
	for($i=0 ; $i < count($dataTable) ; $i++)
	    {
		if($i> 0 && $i%35 == 0 )
		{
				echo '</table><hr style="page-break-after:always; visibility: hidden"><br><br>';
				echo '<table  class="reportGenerator" style="text-align: right;width:70%!important" cellpadding="4" cellspacing="0">
						<tr class="header">					
							<td>ردیف </td>
							<td>نام </td>
							<td> نام خانوادگی </td>	
							<td> واحد محل خدمت </td>					
							<td> تعداد کل </td>
							<td>تعدادی که تعیین شاخص شده</td>
							<td>تعدادی که توسط مدیر تایید شده</td>
						</tr>' ; 
		}
	     echo " <tr>
			    <td>".( $i + 1 )."</td>
			    <td>".$dataTable[$i]['pfname']."</td>
			    <td>".$dataTable[$i]['plname']."</td>
				<td>".$dataTable[$i]['p2']."</td>
				<td>".$dataTable[$i]['TotalCount']."</td>
				<td>".$dataTable[$i]['Count']."</td>
				<td>".$dataTable[$i]['TotalCo']."</td>
		    </tr>" ; 
		 
		// $sum += $dataTable[$i]['PayValue']; 
		
	    }
		//echo "<tr style='font-weight:bold' ><td colspan='8' align='left' >جمع: </td><td colspan='2' >".number_format($sum, 0, '.', ',')."</td></tr>" ;
	   
	    echo "</table></center>" ; 
   
}



?>
<form id="mainForm">
    <center>
        <div id="mainpanel"></div>
    </center>    
</form>
