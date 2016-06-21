<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	94.02
//---------------------------
ini_set('display_errors','On');
require_once("../../../header.inc.php");
require_once '../../../../accountancy/commitment/import/HrmsProcesses.class.php';
ini_set('max_execution_time', 3000);

if(isset($_GET['task']) && $_GET['task'] == 'Compute' )
{
        if(!empty($_POST['PID']))
           $PersonID = $_POST['PID'] ; 
        else {
           $PersonID = "";
        }

	$query = "	SELECT  r.PersonID , remain, CurrentYearRemain , `Year` ,
						SUM(m.DailyOfficialLeaves)  as UsedOfficialLeave ,
						SUM(m.AbsentTime + m.HasteTime +m.TardinessTime)  as AbsentTime ,
						SUM(m.LeaveTime)  as LeaveTime

				FROM pas.RemainLeaves r
							JOIN pas.MonthlyCalculationSummary m
									ON (m.PersonID = r.PersonID AND m.CalculatedYear = ".$_REQUEST['pay_year']." )
									
				WHERE Year = ".$_REQUEST['pay_year']."
				Group by m.PersonID 
			" ; 
	
	$res = PdoDataAccess::runquery($query) ; 
//echo PdoDataAccess::GetLatestQueryString() ;  die() ; 
	for($i=0;$i<count($res);$i++) 
	{
		
		$currentRedemp = $res[$i]['CurrentYearRemain'] ; 
		$DailyUsed = $res[$i]['UsedOfficialLeave'] ;
		$AbsentTime = round($res[$i]['AbsentTime'] / 450 ,2);  ; 
		$HourlyUsed = round($res[$i]['LeaveTime'] / 450,2) ; 

		//........... مانده مرخصی در سال.................
		
		$PayedDay = 0 ; 

		$query2 = " SELECT PersonID , year , sum(RedeemLeaveDayCount)  RedeemLeaveDayCount 
						FROM pas.LeaveRedeem WHERE PersonID =".$res[$i]['PersonID'] ; 
		
		$res2 = PdoDataAccess::runquery($query2) ;
		if(count($res2) > 0 )
			$PayedDay = $res2[0]['RedeemLeaveDayCount'] ; 
		
		$Remain = round(($res[$i]['CurrentYearRemain'] - ( $DailyUsed + $AbsentTime + $HourlyUsed + $PayedDay )),2) ;
		
                $qry = " select person_type from persons where personID = ".$res[$i]['PersonID'] ;
		$resPT = PdoDataAccess::runquery($qry);

		if( $_REQUEST['pay_year'] == 1393 ) 
		{
                        if( $Remain > 0 && $resPT[0]["person_type"] == 5  )
			{
				$Remain = 0 ; 
			}
                        if( $resPT[0]["person_type"] == 2 && $Remain > 15 )
			{
				$Remain = 15 ;
			}
			else if ( $resPT[0]["person_type"] == 3 && $Remain > 9 ) 
			{
				$Remain = 9 ;
			}
 
		 	$total = $Remain + $res[$i]['remain'] ; 
		}
		else if( $_REQUEST['pay_year'] > 1393 ) {
                        if( $resPT[0]["person_type"] == 5  )
			{
				$Remain = 0 ; 
			} 
                        if( $resPT[0]["person_type"] == 2 && $Remain > 15 )
			{
				$Remain = 15 ;
			}
			else if ( $resPT[0]["person_type"] == 3 && $Remain > 9 ) 
			{
				$Remain = 9 ;
			}
  
			$total = $Remain  ; 
                }
		
		//.............آخرین حکم در سال تعیین شده ................
			
		$query = "  select t2.writ_id , t2.writ_ver ,cc.AccUnitID , st.person_type , sum(wsi.value) sv
					from (
					select t1.staff_id,
						SUBSTRING_INDEX(SUBSTRING(max_execute_date,11),'.',1) writ_id,
						SUBSTRING_INDEX(max_execute_date,'.',-1) writ_ver

					from (

					select w.staff_id,max( CONCAT(execute_date,writ_id,'.',writ_ver) ) max_execute_date
										from writs w inner join staff s on w.staff_id = s. staff_id
												inner join persons p on s.PersonID = p. PersonID
										where s.person_type in( 2 ,5 ) and execute_date > '".DateModules::shamsi_to_miladi($_REQUEST['pay_year']."/01/01")."' and
												execute_date < '".DateModules::shamsi_to_miladi($_REQUEST['pay_year']."/12/29")."'  and p.PersonID = ".$res[$i]['PersonID']."

					) t1
					) t2 inner join writ_salary_items wsi
									on  t2.staff_id = wsi.staff_id and
										t2.writ_id = wsi.writ_id and
										t2.writ_ver = wsi.writ_ver
						 inner join writs wr 
									on wsi.staff_id = wr.staff_id and 
									   wsi.writ_id = wr.writ_id and 
									   wsi.writ_ver = wr.writ_ver
						 inner join CostCenterPlan cc on cc.CostCenterID = wr.CostCenterID 
						 inner join staff st on  st.staff_id = wr.staff_id

					" ;
		$res2 = PdoDataAccess::runquery($query); 

                if($res2[0]['writ_id'] == NULL /*|| round($total) < 0 */) {			
			continue;
		}
		
		$qry = " select duration , value from LeaveRedemption
			           where PersonID = " .$res[$i]['PersonID'] ." and year < ".$_REQUEST['pay_year']; 
		$res3 = PdoDataAccess::runquery($qry) ;
		
		$prev_Years = 0 ; 
		$prev_Days = 0 ;
               
		
		for($k=0;$k <count($res3) ; $k++) 
		{
			$prev_Years += $res3[$k]['duration'] * $res3[$k]['value'] ; 
			$prev_Days += $res3[$k]['duration'] ;		
			
		}

		$currentYear = ( ( $prev_Days +round($total)  ) * round($res2[0]['sv']/30)  ) - $prev_Years ; 
		
		//......................................................................................

		$query = " insert into LeaveRedemption (PersonID,duration,value,year,writ_id,writ_ver,PayValue) values 
			               (".$res[$i]['PersonID'].",".round($total).",".round($res2[0]['sv']/30)." ,".$_REQUEST['pay_year'].",".
							  $res2[0]['writ_id'].",".$res2[0]['writ_ver'].",".$currentYear."); " ; 
		
		PdoDataAccess::runquery($query) ; 
	
		//.....................................................................
		
		
				
		
		$tempArr[$i]['AccUnitID'] = $res2[0]['AccUnitID']; 
		$tempArr[$i]['person_type'] = $res2[0]['person_type']; 
		$tempArr[$i]['PersonID'] = $res[$i]['PersonID']; 
		$tempArr[$i]['amount'] = $currentYear; 

					
		
		
	}


	//.................................... فراخوانی ماژول شبنم .................
	$res = HrmsProcesses::LeaveRedemtion(substr($_REQUEST['pay_year'],2,2), $tempArr );
	
	/*if($res == false)
		echo ExceptionHandler::GetExceptionsToString();
	else
		echo "SUCCUSS :)";
	die(); */
	
	//.............Audit...............
	$daObj = new DataAudit();
	$daObj->ActionType = DataAudit::Action_add;
	$daObj->RelatedPersonType = NULL ;
	$daObj->RelatedPersonID = NULL ;
	$daObj->MainObjectID = NULL ;
	$daObj->TableName = "LeaveRedemption";
	$daObj->execute();
	
	//................................
	
	die();
	
}
elseif(isset($_GET['task']) && $_GET['task'] == 'Cancle' )
{
	
	if(HrmsProcesses::ReturnLeaveRedemtion(substr($_POST['pay_year'],2,2))) {
		

		$query = " delete
			         from LeaveRedemption where year = ".$_POST['pay_year'] ; 
		PdoDataAccess::runquery($query) ;  
	
	}
	
	//.............Audit...............
	$daObj = new DataAudit();
	$daObj->ActionType = DataAudit::Action_delete;
	$daObj->RelatedPersonType = NULL ;
	$daObj->RelatedPersonID = NULL ;
	$daObj->MainObjectID = NULL ;
	$daObj->TableName = "LeaveRedemption";
	$daObj->execute();
	
	//................................
	
	die();
	
}
else{
       require_once '../js/RedemptionLeave.js.php';
}

?>
<form id="leaveForm" >
<center>
	<div id="leaveFormDIV"></div>
	<br><br>
	<div id="result" style="width:800px"></div>
</center>
</form>