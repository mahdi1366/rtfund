<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	91.08.07
//---------------------------
require_once("../../../header.inc.php");

if(!isset($_REQUEST["show"]))
    require_once '../js/PersonInfo.js.php';
//require_once "ReportGenerator.class.php";

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
	if(!empty($_POST["unitId"]))
	{
		$whr .= " AND ( s.UnitCode in(".$_POST["unitId"].") OR s.ouid in (".$_POST["unitId"].") ) ";   				
	}
		  
 $query = "	select  p.PersonID ,pfname , plname , o.ptitle title 
                       from persons p inner join staff s 
										on p.PersonID = s.PersonID and p.person_type = s.person_type 
									  inner join org_new_units o 
										on s.UnitCode = o.ouid 
									  inner join writs w 
										on s.last_writ_id = w.writ_id and s.last_writ_ver = w.writ_ver and s.staff_id = w.staff_id 
						 where  
					(1=1) ".$whr ;  
 $query .= ($WhereEmpstate !="" ) ? " AND  w.emp_state in (".$WhereEmpstate.") " : "" ;    
 $query .= ($WhereEmpMod !="" ) ? " AND  w.emp_mode in (".$WhereEmpMod.") " : "" ;    
 
 $query .=" order by s.UnitCode , p.plname , p.pfname  " ; 
 
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
			<td>نام</td>
			<td>نام خانوادگی</td>	
			<td>واحد محل خدمت</td>
			<td width="10%">عکس</td>
		</tr>' ; 
	
	for($i=0 ; $i < count($dataTable) ; $i++)
	    {	 
	  
	     echo " <tr>
			    <td>".( $i + 1 )."</td>
			   
			    <td>".$dataTable[$i]['pfname']."</td>
			    <td>".$dataTable[$i]['plname']."</td>			
				<td>".$dataTable[$i]['title']."</td>							
				<td><div style='background-image: url(showImage.php?PersonID=".$dataTable[$i]['PersonID']."); background-size: auto 80px; height: 80px; width: 80px; background-repeat: no-repeat;'></div></td>
			    
		    </tr>" ; 
		
	    }
	
	
}

?>
<form id="mainForm">
    <center>
        <div id="mainpanel"></div>
    </center>    
</form>