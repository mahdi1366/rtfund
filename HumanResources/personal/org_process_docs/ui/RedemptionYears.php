<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	94.02
//---------------------------
require_once("../../../header.inc.php");
require_once '../../../../accountancy/commitment/import/HrmsProcesses.class.php';


if(isset($_GET['task']) && $_GET['task'] == 'Compute' )
{
	$tempArr = array(); 		
	
	$SDate = DateModules::shamsi_to_miladi($_REQUEST['pay_year']."/01/01") ; 
	$EDate = DateModules::shamsi_to_miladi($_REQUEST['pay_year']."/12/29") ; 
	
	$query = " select s.PersonID , sum(wsi.value) sv

			   from staff s inner join (
                                            SELECT  w.staff_id,
                                                            SUBSTRING_INDEX(SUBSTRING(max(CONCAT(w.execute_date,w.writ_id,'.',w.writ_ver)),11),'.',1) writ_id,
                                                            SUBSTRING_INDEX(max(CONCAT(w.execute_date,w.writ_id,'.',w.writ_ver)),'.',-1) writ_ver

                                            FROM writs w
                                                            INNER JOIN staff ls ON(w.staff_id = ls.staff_id)

                                            WHERE w.person_type in (1,2,3,5) and w.execute_date <= '".$EDate."'

                                            GROUP BY w.staff_id
					
										) w	
									on s.staff_id = w.staff_id 
							inner join writs wr 
									on wr.staff_id = w.staff_id and wr.writ_id = w.writ_id and wr.writ_ver = w.writ_ver			
							inner join writ_salary_items wsi
									on w.staff_id = wsi.staff_id and w.writ_id = wsi.writ_id and w.writ_ver = wsi.writ_ver
							inner join salary_item_types sit 
									on sit.salary_item_type_id = wsi.salary_item_type_id
									

									where wr.person_type in (1,2,3,5) and if(( wr.person_type =2 or wr.person_type =1 ) ,sit.retired_include =1 , sit.insure_include =1 ) and 
										  wr.execute_date >= '".$SDate."' and wr.execute_date <= '".$EDate."' /* AND s.PersonID = 201424*/
				group by s.PersonID 
			 " ; 
	
	$res = PdoDataAccess::runquery($query) ; 



	for($i=0;$i<count($res);$i++){		
		
		$total_year = 0;$total_month = 0;$total_day = 0; 
		$Tyear = $Tmonth = $Tday =0 ; 
		$qry = " select execute_date , annual_effect , writ_id , writ_ver , w.onduty_year ,w.onduty_month ,
                                w.onduty_day  , w.person_type ,cc.AccUnitID
			
				 from writs w inner join staff s 
				                on w.staff_id =  s.staff_id 
							  inner join CostCenterPlan cc 
								on cc.CostCenterID = w.CostCenterID 
								
				 where s.PersonID = ".$res[$i]['PersonID']." and w.execute_date <= '".$EDate."'
				 order by execute_date " ;
		
		$res2 = PdoDataAccess::runquery($qry);
		$cn = count($res2) - 1 ; 

	if($res2[$cn]['person_type'] != 1)
	{
				 
                $DayToEnd = DateModules::CalculateDuration($res2[$cn]['execute_date'] , DateModules::shamsi_to_miladi($_REQUEST['pay_year']."/12/30")); 

		$TotalDay = $res2[$cn]['onduty_year'] * 360 + $res2[$cn]['onduty_month'] * 30 + $res2[$cn]['onduty_day'];

		$TotalDay += $DayToEnd ; 
		
		//................ ذخیره مقدارنهایی 
		
		$query = " insert into YearRedemption (PersonID,duration,value,year,writ_id,writ_ver) values 
			               (".$res[$i]['PersonID'].",".round(($TotalDay/360),2).",".$res[$i]['sv']." ,".$_REQUEST['pay_year'].",".
							  $res2[$cn]['writ_id'].",".$res2[$cn]['writ_ver']."); " ; 
		
	}	
	else {
		//.......................
		$j=0;
		for($j=0;$j<count($res2);$j++)
		{
			
			$first_date = $res2[$j]['execute_date'];
			$last_date = ($j+1 < count($res2)) ? $res2[$j+1]['execute_date'] : $EDate ;

			$diff = strtotime($last_date) - strtotime($first_date);
			$diff = floor($diff/(60*60*24));

			$year = (floor($diff/ 365.25));
			$month = (floor(($diff - floor($diff / 365.25)*365.25  ) / 30.4375 ));
			$day = floor($diff - floor($diff / 365.25)*365.25 -  floor(($diff - floor($diff / 365.25)*365.25  ) / 30.4375 )*30.4375);

			if($res2[$j]['annual_effect'] != "3")
			{
				$total_year += $year;
				$total_month += $month;
				$total_day += $day;
			}	
			
		} 
		
		//....................... سربازی .......................................
		$query = " select military_from_date , military_to_date , military_duration
						from persons where PersonID = ".$res[$i]['PersonID']  ; 
		$Mrow = PdoDataAccess::runquery($query) ; 
		
		if(isset($Mrow[0]["military_duration"]) && $Mrow[0]["military_duration"] > 0 ){
			$total_year += floor($Mrow[0]["military_duration"] / 12);
			$total_month += ($Mrow[0]["military_duration"] - (floor($Mrow[0]["military_duration"] / 12) * 12));
			$total_day += 0;
		}
		
		//.......................... سابقه کاری ......................
		
		require_once "../../../personal/persons/class/employment.class.php";
		$temp = manage_person_employment::GetAllEmp("PersonID=" . $res[$i]['PersonID']);
		
		for($k=0; $k < count($temp); $k++)
		{
			if( $temp[$k]["retired_duration_year"] != 0 || $temp[$k]["retired_duration_month"] != 0 ||
				$temp[$k]["retired_duration_day"] != 0)
			{				
				$total_year += $temp[$k]["retired_duration_year"];
				$total_month += $temp[$k]["retired_duration_month"];
				$total_day += $temp[$k]["retired_duration_day"];
			} 
			
		}
		//..........................................................
		
		$TotalDay = DateModules::ymd_to_days($total_year, $total_month,$total_day); 
				//DateModules::day_to_ymd($TotalDay, $Tyear, $Tmonth, $Tday);
		//................ ذخیره مقدارنهایی 
		
		$query = " insert into YearRedemption (PersonID,duration,value,year,writ_id,writ_ver) values 
			               (".$res[$i]['PersonID'].",".round(($TotalDay/360),2).",".$res[$i]['sv']." ,".$_REQUEST['pay_year'].",".
							  $res2[($j-1)]['writ_id'].",".$res2[($j-1)]['writ_ver']."); " ; 
		
		}
                
		
		PdoDataAccess::runquery($query); 
	
		//.....................................................................
		
		$qry = " select duration , value from YearRedemption
			           where PersonID = " .$res[$i]['PersonID'] ." and year = ".( $_REQUEST['pay_year'] - 1 ) ; 
		$res3 = PdoDataAccess::runquery($qry) ;
		
		$prev_Years = 0 ; 
		
		$prev_Years = (!empty($res3[0]['duration'])) ? ( $res3[0]['duration'] * $res3[0]['value']) : 0  ; 			
				
		$currentYear = ( round(($TotalDay/360),2) * $res[$i]['sv']  ) - $prev_Years ; 				

		$tempArr[$i]['AccUnitID'] = ($res2[$cn]['person_type'] != 1) ? $res2[$cn]['AccUnitID'] : $res2[($j-1)]['AccUnitID']; 
		$tempArr[$i]['person_type'] = ($res2[$cn]['person_type'] != 1) ? $res2[$cn]['person_type'] : $res2[($j-1)]['person_type']; 
		$tempArr[$i]['PersonID'] = $res[$i]['PersonID']; 
		$tempArr[$i]['amount'] = $currentYear; 				
		
	}
		
	$res = HrmsProcesses::DutyRedemtion(substr($_REQUEST['pay_year'],2,2), $tempArr );
	
	/*print_r(ExceptionHandler::PopAllExceptions()) ; 
		die();*/
	//.............Audit...............
	$daObj = new DataAudit();
	$daObj->ActionType = DataAudit::Action_add;
	$daObj->RelatedPersonType = NULL ;
	$daObj->RelatedPersonID = NULL ;
	$daObj->MainObjectID = NULL ;
	$daObj->TableName = "YearRedemption";
	$daObj->execute();
		
	die();
	
}
elseif(isset($_GET['task']) && $_GET['task'] == 'Cancle' )
{
	
	if(HrmsProcesses::ReturnDutyRedemtion(substr($_POST['pay_year'],2,2))) {
		
		$query = " delete
						from YearRedemption where year = ".$_POST['pay_year'] ; 
		PdoDataAccess::runquery($query) ;  
	
	}
	
	//.............Audit...............
	$daObj = new DataAudit();
	$daObj->ActionType = DataAudit::Action_delete;
	$daObj->RelatedPersonType = NULL ;
	$daObj->RelatedPersonID = NULL ;
	$daObj->MainObjectID = NULL ;
	$daObj->TableName = "YearRedemption";
	$daObj->execute();
	
	//................................
	
	die();
	
}
else{
       require_once '../js/RedemptionYears.js.php';
}

?>
<form id="YearsForm" >
<center>
	<div id="YearsFormDIV"></div>
	<br><br>
	<div id="result" style="width:800px"></div>
</center>
</form>