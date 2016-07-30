<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	91.08.07
//---------------------------
require_once("../../../header.inc.php");

if(!isset($_REQUEST["show"]))
    require_once '../js/PersonInfo2.js.php';

 $whr = " " ; 
 
 
if(isset($_REQUEST["show"]))
{
				
	$whereParam = array();
	
	//.........................................
	$keys = array_keys($_POST);
	$WhereEmpstate = "" ; $WhereEmpMod = "" ;
	$arr = "" ;	
	//...................... وضعیت استخدامی................
	
	for($i=0; $i < count($_POST); $i++)
	{				
		if(strpos($keys[$i],"chkEmpState_") !== false)
		{		
			$arr = preg_split('/_/', $keys[$i]);		
			if(isset($arr[1]))
			$WhereEmpstate .= ($WhereEmpstate!="") ?  ",".$arr[1] : $arr[1] ;
		}			 		
		
		if(strpos($keys[$i],"chkEmpMod_") !== false)
		{		
			$arr = preg_split('/_/', $keys[$i]);		
			if(isset($arr[1]))
			$WhereEmpMod .= ($WhereEmpMod!="") ?  ",".$arr[1] : $arr[1] ;
		}	
		
	}
	//.........................................
    	
	if(!empty($_POST["unitId"]) && $_POST["unitId"] != 1 )
	{
		$whr .= " AND ouid in (".$_POST["unitId"].") ";   				
	}
	
		  
 $query = " select ouid , ptitle  
				from org_new_units 
					where parent_ouid is null and ouid != 1 ". $whr ; 
  
 $query .=" order by  ouid " ; 
 
 $dataTable = PdoDataAccess::runquery($query, $whereParam);
//echo PdoDataAccess::GetLatestQueryString() ; die();
if($_SESSION['UserID'] == 'jafarkhani'){
	// echo PdoDataAccess::GetLatestQueryString() ; die() ; 
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
			<td>واحد سازمانی</td>
			<td>تعداد کارکنان</td>				
		</tr>' ; 
	
	for($i=0 ; $i < count($dataTable) ; $i++)
	{	 
	
		$qry = " 
				select count(*) cn

				   from staff s inner join writs w
											   on s.staff_id = w.staff_id and
												  s.last_writ_id = w.writ_id and
												  s.last_writ_ver = w.writ_ver


					  where w.execute_date > '2014-03-20' and w.person_type in ( 1,2,3,5 )
							 and w.ouid in (
											select ouid
											 from org_new_units

											 
											where  ( ouid = ".$dataTable[$i]['ouid']."  OR parent_ouid = ".$dataTable[$i]['ouid']." OR
													 parent_path LIKE '%,".$dataTable[$i]['ouid'].",%' OR
													 substring(parent_path ,1,( length(".$dataTable[$i]['ouid'].") + 1 ) ) = '".$dataTable[$i]['ouid'].",' OR
													 substring(parent_path ,-( length(".$dataTable[$i]['ouid'].") + 1 ),( length(".$dataTable[$i]['ouid'].") + 1 ) ) = ',".$dataTable[$i]['ouid']."' )
										   )		 
				 " ; 

	   $dt = PdoDataAccess::runquery($qry) ; 
  //echo PdoDataAccess::GetLatestQueryString() .'****' ; die() ;
       echo " <tr>
				<td>".( $i + 1 )."</td>			   
				<td>".$dataTable[$i]['ptitle']."</td> " ; 
				if($dt[0]['cn'] > 0 )
					echo "<td><a href='/HumanResources/salary/reports/ui/SubUnitDetail1.php?ouid=".$dataTable[$i]['ouid']."' target='_blank'>".$dt[0]['cn']."</td> " ; 
				else echo "<td>".$dt[0]['cn']."</td>"	; 
	   echo "</tr>" ; 
	
	}

	
}

?>
<form id="mainForm">
    <center>
        <div id="mainpanel"></div>
    </center>    
</form>