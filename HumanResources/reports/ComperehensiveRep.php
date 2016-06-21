<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	92.05
//---------------------------
require_once("../header.inc.php");

if(!isset($_REQUEST["show"]))
{
	require_once 'ComperehensiveRep.js.php';
}

if(isset($_REQUEST['show']) && $_REQUEST['show'] == true )
{	

$whereW ="" ; 
if(!empty($_POST['from_execute_date'])){	
  $whereW .= " AND w.execute_date >= '".DateModules::shamsi_to_miladi($_POST['from_execute_date'])."'  " ; 	
}

if(!empty($_POST['to_execute_date'])){	
  $whereW .= " AND w.execute_date <= '".DateModules::shamsi_to_miladi($_POST['to_execute_date'])."'" ; 
}

if(!empty($_POST['unitId']) && $_POST['unitId'] != 1 ){

	$whrUID = " AND s.UnitCode = ".$_POST['unitId'] ; 

}

//.................. تفکيک حالت استخدامي......................
$query = "     

		select sum(cn) cn2 , emp_mode , pt

		from (
		select count(*) cn , w.emp_mode , s.person_type , if(s.person_type = 1 , 1 , 2 ) pt

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

		where s.person_type in (1,2,3,5) ".$whereW." ".$whrUID."

		group by s.person_type , w.emp_mode
		)t1
		group by pt ,emp_mode
		order by person_type , emp_mode
				  
		 ";

$temp = PdoDataAccess::runquery($query) ; 
 
//..............................................تفکيک وضعيت استخدامي................................

$query2 = "     

		select sum(cn) cn2 , emp_state , pt

		from (
		select count(*) cn , w.emp_state , s.person_type , if(s.person_type = 1 , 1 , 2 ) pt

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

		where s.person_type in (1,2,3,5) ".$whereW." ".$whrUID."

		group by s.person_type , w.emp_state
		)t1
		group by pt ,emp_state
		order by person_type , emp_state
				  
		 ";

$temp2 = PdoDataAccess::runquery($query2) ; 

//.................... آمار نيروي انساني ....................................
 

for($j=0; $j< 12; $j++)
{
	if($j == 0 ){
		$whrDate = " and w.execute_date < '".DateModules::shamsi_to_miladi(substr($_POST['from_execute_date'],0,4)."/02/01")."' " ; 
		$whrDate92 = " and w.execute_date < '".DateModules::shamsi_to_miladi((substr($_POST['from_execute_date'],0,4)-1)."/02/01")."' " ; 
		
		}
	elseif($j == 1) {
		$whrDate = " and w.execute_date < '".DateModules::shamsi_to_miladi(substr($_POST['from_execute_date'],0,4)."/03/01")."' " ; 
		$whrDate92 = " and w.execute_date < '".DateModules::shamsi_to_miladi( (substr($_POST['from_execute_date'],0,4)-1)."/03/01")."' " ; 
		}
	elseif($j == 2) {
		$whrDate = " and w.execute_date < '".DateModules::shamsi_to_miladi(substr($_POST['from_execute_date'],0,4)."/04/01")."' " ; 
		$whrDate92 = " and w.execute_date < '".DateModules::shamsi_to_miladi( (substr($_POST['from_execute_date'],0,4)-1)."/04/01")."' " ; 
		}
	elseif($j == 3) {
		$whrDate = " and w.execute_date < '".DateModules::shamsi_to_miladi(substr($_POST['from_execute_date'],0,4)."/05/01")."' " ; 
		$whrDate92 = " and w.execute_date < '".DateModules::shamsi_to_miladi( (substr($_POST['from_execute_date'],0,4)-1)."/05/01")."' " ; 
		}
	elseif($j == 4) {
		$whrDate = " and w.execute_date < '".DateModules::shamsi_to_miladi(substr($_POST['from_execute_date'],0,4)."/06/01")."' " ; 
		$whrDate92 = " and w.execute_date < '".DateModules::shamsi_to_miladi( (substr($_POST['from_execute_date'],0,4)-1)."/06/01")."' " ; 
		}
	elseif($j == 5) {
		$whrDate = " and w.execute_date < '".DateModules::shamsi_to_miladi(substr($_POST['from_execute_date'],0,4)."/07/01")."' " ; 
		$whrDate92 = " and w.execute_date < '".DateModules::shamsi_to_miladi( (substr($_POST['from_execute_date'],0,4)-1)."/07/01")."' " ; 
		}
	elseif($j == 6) {
		$whrDate = " and w.execute_date < '".DateModules::shamsi_to_miladi(substr($_POST['from_execute_date'],0,4)."/08/01")."' " ; 
		$whrDate92 = " and w.execute_date < '".DateModules::shamsi_to_miladi( (substr($_POST['from_execute_date'],0,4)-1)."/08/01")."' " ; 
		}
	elseif($j == 7) {
		$whrDate = " and w.execute_date < '".DateModules::shamsi_to_miladi(substr($_POST['from_execute_date'],0,4)."/09/01")."' " ; 
		$whrDate92 = " and w.execute_date < '".DateModules::shamsi_to_miladi( (substr($_POST['from_execute_date'],0,4)-1)."/09/01")."' " ; 
		}
	elseif($j == 8) {
		$whrDate = " and w.execute_date < '".DateModules::shamsi_to_miladi(substr($_POST['from_execute_date'],0,4)."/10/01")."' " ; 
		$whrDate92 = " and w.execute_date < '".DateModules::shamsi_to_miladi( (substr($_POST['from_execute_date'],0,4)-1)."/10/01")."' " ; 
		}
	elseif($j == 9) {
		$whrDate = " and w.execute_date < '".DateModules::shamsi_to_miladi(substr($_POST['from_execute_date'],0,4)."/11/01")."' " ; 
		$whrDate92 = " and w.execute_date < '".DateModules::shamsi_to_miladi( (substr($_POST['from_execute_date'],0,4)-1)."/11/01")."' " ; 
		}
	elseif($j == 10) {
		$whrDate = " and w.execute_date < '".DateModules::shamsi_to_miladi(substr($_POST['from_execute_date'],0,4)."/12/01")."' " ; 
		$whrDate92 = " and w.execute_date < '".DateModules::shamsi_to_miladi( (substr($_POST['from_execute_date'],0,4)-1)."/12/01")."' " ; 
		}
	elseif($j == 11)  {
		$whrDate = " and w.execute_date <= '".DateModules::shamsi_to_miladi(substr($_POST['from_execute_date'],0,4)."/12/29")."' " ; 
		$whrDate92 = " and w.execute_date <= '".DateModules::shamsi_to_miladi( (substr($_POST['from_execute_date'],0,4)-1)."/12/29")."' " ; 
		}
		
		$query3 = " select count(*) cn
						from staff s inner join (SELECT staff_id, SUBSTRING_INDEX(SUBSTRING(max_execute_date,11),'.',1) writ_id,
								   SUBSTRING_INDEX(max_execute_date,'.',-1) writ_ver FROM (SELECT w.staff_id,
								   max( CONCAT(w.execute_date,w.writ_id,'.',w.writ_ver) ) max_execute_date

								   FROM writs w INNER JOIN staff ls ON(w.staff_id = ls.staff_id)
								   WHERE w.history_only = 0 and w.execute_date >= '".DateModules::shamsi_to_miladi(substr($_POST['from_execute_date'],0,4)."/1/1")."' ".$whrDate."
								   GROUP BY w.staff_id)tbl2) tbl1
											on s.staff_id = tbl1.staff_id

							  inner join writs w
								   on tbl1.writ_id = w.writ_id and tbl1.writ_ver = w.writ_ver and tbl1.staff_id = w.staff_id

						where
							  w.execute_date >= '".DateModules::shamsi_to_miladi(substr($_POST['from_execute_date'],0,4)."/1/1")."'  and s.person_type in (1,2,3,5) and
							  w.emp_mode not in (7,9,11,12,13,14,20,22,24,25,26,27,28) ".$whrDate." ". $whrUID ; 
				
	    $temp3 = PdoDataAccess::runquery($query3); 
		//.....................................کل کارکنان ................
		$query10 = " select count(*) cn
						from staff s inner join (SELECT staff_id, SUBSTRING_INDEX(SUBSTRING(max_execute_date,11),'.',1) writ_id,
								   SUBSTRING_INDEX(max_execute_date,'.',-1) writ_ver FROM (SELECT w.staff_id,
								   max( CONCAT(w.execute_date,w.writ_id,'.',w.writ_ver) ) max_execute_date

								   FROM writs w INNER JOIN staff ls ON(w.staff_id = ls.staff_id)
								   WHERE w.history_only = 0 ".$whereW."
								   GROUP BY w.staff_id)tbl2) tbl1
											on s.staff_id = tbl1.staff_id

							  inner join writs w
								   on tbl1.writ_id = w.writ_id and tbl1.writ_ver = w.writ_ver and tbl1.staff_id = w.staff_id

						where
							  s.person_type in (1,2,3,5) and
							  w.emp_mode not in (7,9,11,12,13,14,20,22,24,25,26,27,28) ".$whereW." ". $whrUID ; 
				
	    $tmp = PdoDataAccess::runquery($query10); 
		//.......................درصد کارمندان...............
		$query10 = " select count(*) cn
						from staff s inner join (SELECT staff_id, SUBSTRING_INDEX(SUBSTRING(max_execute_date,11),'.',1) writ_id,
								   SUBSTRING_INDEX(max_execute_date,'.',-1) writ_ver FROM (SELECT w.staff_id,
								   max( CONCAT(w.execute_date,w.writ_id,'.',w.writ_ver) ) max_execute_date

								   FROM writs w INNER JOIN staff ls ON(w.staff_id = ls.staff_id)
								   WHERE w.history_only = 0 ".$whereW."
								   GROUP BY w.staff_id)tbl2) tbl1
											on s.staff_id = tbl1.staff_id

							  inner join writs w
								   on tbl1.writ_id = w.writ_id and tbl1.writ_ver = w.writ_ver and tbl1.staff_id = w.staff_id

						where
							  s.person_type in (2,3,5) and
							  w.emp_mode not in (7,9,11,12,13,14,20,22,24,25,26,27,28) ".$whereW." ". $whrUID ; 
				
	    $temp10 = PdoDataAccess::runquery($query10); 
		
	
		//......................درصد هيئت علمي...............
		$query11 =  " select count(*) cn
						from staff s inner join (SELECT staff_id, SUBSTRING_INDEX(SUBSTRING(max_execute_date,11),'.',1) writ_id,
								   SUBSTRING_INDEX(max_execute_date,'.',-1) writ_ver FROM (SELECT w.staff_id,
								   max( CONCAT(w.execute_date,w.writ_id,'.',w.writ_ver) ) max_execute_date

								   FROM writs w INNER JOIN staff ls ON(w.staff_id = ls.staff_id)
								   WHERE w.history_only = 0 ".$whereW."
								   GROUP BY w.staff_id)tbl2) tbl1
											on s.staff_id = tbl1.staff_id

							  inner join writs w
								   on tbl1.writ_id = w.writ_id and tbl1.writ_ver = w.writ_ver and tbl1.staff_id = w.staff_id

						where
							  s.person_type in (1) and
							  w.emp_mode not in (7,9,11,12,13,14,20,22,24,25,26,27,28) ".$whereW ." ". $whrUID ; 
				
	    $temp11 = PdoDataAccess::runquery($query11); 
		//..............................افراد خارج شده ............
		$query12 = " select count(*) cn
						from staff s inner join (SELECT staff_id, SUBSTRING_INDEX(SUBSTRING(max_execute_date,11),'.',1) writ_id,
								   SUBSTRING_INDEX(max_execute_date,'.',-1) writ_ver FROM (SELECT w.staff_id,
								   max( CONCAT(w.execute_date,w.writ_id,'.',w.writ_ver) ) max_execute_date

								   FROM writs w INNER JOIN staff ls ON(w.staff_id = ls.staff_id)
								   WHERE w.history_only = 0 ".$whereW."
								   GROUP BY w.staff_id)tbl2) tbl1
											on s.staff_id = tbl1.staff_id

							  inner join writs w
								   on tbl1.writ_id = w.writ_id and tbl1.writ_ver = w.writ_ver and tbl1.staff_id = w.staff_id

						where
							  s.person_type in (1,2,3,5) and
							  w.emp_mode in (7,9,11,12,13,14,20,22,24,25,26,27,28) ".$whereW ." ". $whrUID ; 
				
	    $temp12 = PdoDataAccess::runquery($query12);  
		
		//...............................افراد وارد شده ..........................
		$query13 = " select count(*) cn
						from staff s inner join (
								SELECT staff_id, SUBSTRING_INDEX(SUBSTRING(max_execute_date,11),'.',1) writ_id,
								       SUBSTRING_INDEX(max_execute_date,'.',-1) writ_ver 
								FROM (SELECT w.staff_id,
								       min( CONCAT(w.execute_date,w.writ_id,'.',w.writ_ver) ) max_execute_date

								   FROM writs w INNER JOIN staff ls ON(w.staff_id = ls.staff_id)
								   WHERE w.history_only = 0 
								   GROUP BY w.staff_id)tbl2) tbl1
											on s.staff_id = tbl1.staff_id

							  inner join writs w
								   on tbl1.writ_id = w.writ_id and tbl1.writ_ver = w.writ_ver and tbl1.staff_id = w.staff_id

						where
							  s.person_type in (1,2,3,5) and
							  w.emp_mode not in (7,9,11,12,13,14,20,22,24,25,26,27,28) ".$whereW ." ". $whrUID ; 
				
	    $temp13 = PdoDataAccess::runquery($query13); 
 
		//...........................................کارکنان داراي مدرک فوق العاده مديريت ............
		$query14 = " select count(*) cn
						from staff s inner join (  SELECT staff_id, SUBSTRING_INDEX(SUBSTRING(max_execute_date,11),'.',1) writ_id,
													      SUBSTRING_INDEX(max_execute_date,'.',-1) writ_ver FROM (SELECT w.staff_id,
													      max( CONCAT(w.execute_date,w.writ_id,'.',w.writ_ver) ) max_execute_date

												   FROM writs w INNER JOIN staff ls ON (w.staff_id = ls.staff_id)
												   WHERE w.history_only = 0 ".$whereW."
												   GROUP BY w.staff_id)tbl2) tbl1
																on s.staff_id = tbl1.staff_id

									  inner join writs w
										   on tbl1.writ_id = w.writ_id and tbl1.writ_ver = w.writ_ver and tbl1.staff_id = w.staff_id
										   
									  inner join writ_salary_items wsi 
										   on w.staff_id = wsi. staff_id and 
											  w.writ_id = wsi.writ_id and 
											  w.writ_ver = wsi.writ_ver and wsi.salary_item_type_id in (10373,10377,28)

						where
							  s.person_type in (1,2,3,5) and
							  w.emp_mode not in (7,9,11,12,13,14,20,22,24,25,26,27,28) ".$whereW." ". $whrUID ; 
				
	    $temp14 = PdoDataAccess::runquery($query14);   
		//............................................................................................	
		$query4 = " select count(*) cn
						from staff s inner join (SELECT staff_id, SUBSTRING_INDEX(SUBSTRING(max_execute_date,11),'.',1) writ_id,
								   SUBSTRING_INDEX(max_execute_date,'.',-1) writ_ver FROM (SELECT w.staff_id,
								   max( CONCAT(w.execute_date,w.writ_id,'.',w.writ_ver) ) max_execute_date

								   FROM writs w INNER JOIN staff ls ON(w.staff_id = ls.staff_id)
								   WHERE w.history_only = 0 and w.execute_date >= '".DateModules::shamsi_to_miladi((substr($_POST['from_execute_date'],0,4)-1)."/1/1")."' ".$whrDate92."
								   GROUP BY w.staff_id)tbl2) tbl1
											on s.staff_id = tbl1.staff_id

							  inner join writs w
								   on tbl1.writ_id = w.writ_id and tbl1.writ_ver = w.writ_ver and tbl1.staff_id = w.staff_id

						where
							  w.execute_date >= '".DateModules::shamsi_to_miladi((substr($_POST['from_execute_date'],0,4)-1)."/1/1")."'  and s.person_type in (1,2,3,5) and
							  w.emp_mode not in (7,9,11,12,13,14,20,22,24,25,26,27,28) ".$whrDate92." ". $whrUID ; 
	    $temp4 = PdoDataAccess::runquery($query4); 
		
		if($j == 0 ){
			$month1_92 = $temp4[0]['cn'] ; 
			$month1_93 = $temp3[0]['cn']; 
		}
		elseif($j == 1) {
			$month2_92 = $temp4[0]['cn'] ; 
			$month2_93 = $temp3[0]['cn']; 
			}
		elseif($j == 2) {
			$month3_92 = $temp4[0]['cn'] ; 
			$month3_93 = $temp3[0]['cn']; 
			}
		elseif($j == 3) {
			$month4_92 = $temp4[0]['cn'] ; 
			$month4_93 = $temp3[0]['cn']; 
			}
		elseif($j == 4) {
			$month5_92 = $temp4[0]['cn'] ; 
			$month5_93 = $temp3[0]['cn']; 
			}
		elseif($j == 5) {
			$month6_92 = $temp4[0]['cn'] ; 
			$month6_93 = $temp3[0]['cn']; 
			}
		elseif($j == 6) {
			$month7_92 = $temp4[0]['cn'] ; 
			$month7_93 = $temp3[0]['cn']; 
		}
		elseif($j == 7) {
			$month8_92 = $temp4[0]['cn'] ; 
			$month8_93 = $temp3[0]['cn']; 
			}
		elseif($j == 8) {
			$month9_92 = $temp4[0]['cn'] ; 
			$month9_93 = $temp3[0]['cn']; 
			}
		elseif($j == 9) {
			$month10_92 = $temp4[0]['cn'] ; 
			$month10_93 = $temp3[0]['cn']; 
			}
		elseif($j == 10) {
			$month11_92 = $temp4[0]['cn'] ; 
			$month11_93 = $temp3[0]['cn']; 
			}
		elseif($j == 11)  {
			$month12_92 = $temp4[0]['cn'] ; 
			$month12_93 = $temp3[0]['cn']; 
			}	   
	   
		
}

//.......................... اطلاعات کارکنان .........................

$year = substr($_POST['from_execute_date'], 0, 4);
$Smonth = substr($_POST['from_execute_date'], 5, 2);
$Tmonth = substr($_POST['to_execute_date'], 5, 2);

//  در سال 93 
	$query5 = " select s.staff_id ,s.person_type , sum(value)  sv  
				from

						  staff s
								inner join (SELECT staff_id, SUBSTRING_INDEX(SUBSTRING(max_execute_date,11),'.',1) writ_id,
									   SUBSTRING_INDEX(max_execute_date,'.',-1) writ_ver FROM (SELECT w.staff_id,
									   max( CONCAT(w.execute_date,w.writ_id,'.',w.writ_ver) ) max_execute_date

									   FROM writs w INNER JOIN staff ls ON(w.staff_id = ls.staff_id)
									   WHERE w.history_only = 0 ".$whereW."
									   GROUP BY w.staff_id)tbl2) tbl1
									on s.staff_id = tbl1.staff_id

							    inner join writs w
								    on tbl1.writ_id = w.writ_id and tbl1.writ_ver = w.writ_ver and tbl1.staff_id = w.staff_id

						  inner join writ_salary_items wsi
							 on w.staff_id = wsi.staff_id and
								w.writ_id = wsi.writ_id and
								w.writ_ver = wsi.writ_ver

				where wsi.salary_item_type_id in (10364,10366,10367,3,283,9969)  ".$whrUID."
				group by s.staff_id ,s.person_type  " ; 
		
	$temp5 = PdoDataAccess::runquery($query5); 
	$AVeExtraWork = 0 ; 
	for($j=0;$j<count($temp5);$j++)
	{
		if($temp5[$j]['person_type'] == 3 ) 
		{
			$AVeExtraWork += ( $temp5[$j]['sv'] /30.5 ) * 1.4/7.33 ; 
		}
		else 
		{
			$AVeExtraWork += $temp5[$j]['sv'] /176 ;
		}
		
	}
	
	$AVeExtraWork = round($AVeExtraWork / count($temp5)) ; 
	
	
	//...................
	$query6 = " select sum(pay_value + diff_pay_value) sv ,sum(param3) hrs , s.person_type

				  from payment_items pit inner join staff s
											  on  pit.staff_id = s.staff_id

					   where pay_year = ".$year." and pay_month >= ".$Smonth." and
							 pay_month < ".$Tmonth." and salary_item_type_id in (39,152,639,752) and  s.person_type in (2,5) ". $whrUID ; 
    $temp6 = PdoDataAccess::runquery($query6); 
	
	$query7 = " select sum(pay_value + diff_pay_value) sv ,sum(param2) hrs , s.person_type

				  from payment_items pit inner join staff s
											  on  pit.staff_id = s.staff_id

					   where pay_year = ".$year." and pay_month >= ".$Smonth." and
							 pay_month < ".$Tmonth." and salary_item_type_id in (39,152,639,752) and  s.person_type in (3) ". $whrUID ; 
							 
    $temp7 = PdoDataAccess::runquery($query7);
	
	//.........................ميانگين سنوات خدمت....................
				
	$qry = " select    round(sum(onduty_year) / count(*)) AveYear ,
					   round(sum(onduty_day) /count(*)) AveDay ,
					   round(sum(onduty_month) / count(*)) AveMonth ,
					   Max(onduty_year) MaxYear ,
					   Min(onduty_year) MinYear
	   
				from staff s inner join (SELECT staff_id, SUBSTRING_INDEX(SUBSTRING(max_execute_date,11),'.',1) writ_id,
						   SUBSTRING_INDEX(max_execute_date,'.',-1) writ_ver FROM (SELECT w.staff_id,
						   max( CONCAT(w.execute_date,w.writ_id,'.',w.writ_ver) ) max_execute_date

						   FROM writs w INNER JOIN staff ls ON(w.staff_id = ls.staff_id)
						   WHERE w.history_only = 0 ".$whereW."
						   GROUP BY w.staff_id)tbl2) tbl1
									on s.staff_id = tbl1.staff_id

					  inner join writs w
						   on tbl1.writ_id = w.writ_id and tbl1.writ_ver = w.writ_ver and tbl1.staff_id = w.staff_id

				where
					  s.person_type in (1,2,3,5) and
					  w.emp_mode not in (7,9,11,12,13,14,20,22,24,25,26,27,28) ".$whereW." ". $whrUID ; 
					 		
					
	$temp9 = PdoDataAccess::runquery($qry);
	
?>

<html dir='rtl'>
	<head>
		<style>
		.reportGenerator {border-collapse: collapse;border: 1px solid black;font-family: tahoma;font-size: 8pt;
						  text-align: center;width: 95%;padding: 2px;}
		.reportGenerator .header {color: black;font-weight: bold;background-color: #85A3C2 }
		.reportGenerator td {border: 1px solid #555555;height: 20px;text-align: center;}
		</style>
		<title>خلاصه پرونده نيروي انساني</title>
		<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
		<link rel=stylesheet href="/HumanResources/css/writ.css">
	</head>
	<body>
		<center>
			<table width="50%" cellpadding="0" cellspacing="0">
				<tr>
					<td width="20%"><img src="/HumanResources/img/fum_symbol.jpg" ></td>
					<td align="center" style="font-family:'b titr'">خلاصه پرونده نيروي انساني </td>
					<td width="20%" align="left" style="font-family:tahoma;font-size:8pt">تاريخ : <?= DateModules::shNow() ?></td>
				</tr>
			</table>
			
			<table style="text-align: right;"  width="50%" class="reportGenerator" cellpadding="4" cellspacing="0" >
				<tr class="header">	
					<td>حالت استخدامي</td>
					<td>شاغل</td><td>اشتغال</td><td>مرخصي بدون حقوق</td><td>ماموريت تحصيلي</td><td>ماموريت به خدمت</td><td>مامور به سازمان ديگر</td>
					<td>انتقال</td><td>انفصال موقت</td><td>انفصال دائم</td><td>آماده به خدمت</td><td>استعفا</td><td>اخراج</td><td>بازنشسته</td>
					<td>بازخريد</td><td>به دلايلي حقوق نمي گيرد</td><td>مامور از سازمان ديگر</td><td>ماموريت فرصت مطالعاتي خارجي </td><td>ماموريت تحصيلي داخل </td>
					<td>ماموريت تحصيلي خارج </td><td>لغو قرارداد</td><td>مرخصي استعلاجي</td>	<td>اتمام طرح</td><td>ماموريت فرصت مطالعاتي داخلي </td><td>خاتمه ماموريت</td>
					<td>قطع همکاري</td><td>تبديل وضعيت استخدامي</td><td>متوفي</td><td>شهادت</td>	<td>غيبت</td>											
				</tr>
				<?
				$EmpItm_1_1 = $EmpItm_1_2 =	$EmpItm_1_3 = $EmpItm_1_4 = $EmpItm_1_5 =$EmpItm_1_6 =$EmpItm_1_7= $EmpItm_1_8 =$EmpItm_1_9 =$EmpItm_1_10 =0 ; 
				$EmpItm_1_11 = $EmpItm_1_12 =	$EmpItm_1_13 = $EmpItm_1_14 = $EmpItm_1_15 =$EmpItm_1_16 =$EmpItm_1_17= $EmpItm_1_18 =$EmpItm_1_19 =$EmpItm_1_20 =0 ; 
				$EmpItm_1_21 = $EmpItm_1_22 =	$EmpItm_1_23 = $EmpItm_1_24 = $EmpItm_1_25 =$EmpItm_1_26 =$EmpItm_1_27= $EmpItm_1_28 =$EmpItm_1_29 =0 ; 
				
				$EmpItm_2_1 = $EmpItm_2_2 =	$EmpItm_2_3 = $EmpItm_2_4 = $EmpItm_2_5 =$EmpItm_2_6 =$EmpItm_2_7= $EmpItm_1_8 =$EmpItm_2_9 =$EmpItm_2_10 =0 ; 
				$EmpItm_2_11 = $EmpItm_2_12 =	$EmpItm_2_13 = $EmpItm_2_14 = $EmpItm_2_15 =$EmpItm_2_16 =$EmpItm_2_17= $EmpItm_2_18 =$EmpItm_2_19 =$EmpItm_2_20 =0 ; 
				$EmpItm_2_21 = $EmpItm_2_22 =	$EmpItm_2_23 = $EmpItm_2_24 = $EmpItm_2_25 =$EmpItm_2_26 =$EmpItm_2_27= $EmpItm_2_28 =$EmpItm_2_29 =0 ; 
				
				for($i=0;$i<count($temp);$i++)
				{
					if($temp[$i]['emp_mode'] == 1 && $temp[$i]['pt'] == 1  )
						$EmpItm_1_1 = $temp[$i]['cn2'] ;
					elseif($temp[$i]['emp_mode'] == 2 && $temp[$i]['pt'] == 1  )
						$EmpItm_1_2 = $temp[$i]['cn2'] ;
					elseif($temp[$i]['emp_mode'] == 3 && $temp[$i]['pt'] == 1  )
						$EmpItm_1_3 = $temp[$i]['cn2'] ;						
					elseif($temp[$i]['emp_mode'] == 4 && $temp[$i]['pt'] == 1  )
						$EmpItm_1_4 = $temp[$i]['cn2'] ;					
					elseif($temp[$i]['emp_mode'] == 5 && $temp[$i]['pt'] == 1  )
						$EmpItm_1_5 = $temp[$i]['cn2'] ;
					elseif($temp[$i]['emp_mode'] == 6 && $temp[$i]['pt'] == 1  )
						$EmpItm_1_6 = $temp[$i]['cn2'] ;
					elseif($temp[$i]['emp_mode'] == 7 && $temp[$i]['pt'] == 1  )
						$EmpItm_1_7 = $temp[$i]['cn2'] ;
					elseif($temp[$i]['emp_mode'] == 8 && $temp[$i]['pt'] == 1  )
						$EmpItm_1_8 = $temp[$i]['cn2'] ;
					elseif($temp[$i]['emp_mode'] == 9 && $temp[$i]['pt'] == 1  )
						$EmpItm_1_9 = $temp[$i]['cn2'] ;
					elseif($temp[$i]['emp_mode'] == 10 && $temp[$i]['pt'] == 1  )
						$EmpItm_1_10 = $temp[$i]['cn2'] ;
			
					elseif($temp[$i]['emp_mode'] == 11 && $temp[$i]['pt'] == 1  )
						$EmpItm_1_11 = $temp[$i]['cn2'] ;
					elseif($temp[$i]['emp_mode'] == 12 && $temp[$i]['pt'] == 1  )
						$EmpItm_1_12 = $temp[$i]['cn2'] ;
					elseif($temp[$i]['emp_mode'] == 13 && $temp[$i]['pt'] == 1  )
						$EmpItm_1_13 = $temp[$i]['cn2'] ;						
					elseif($temp[$i]['emp_mode'] == 14 && $temp[$i]['pt'] == 1  )
						$EmpItm_1_14 = $temp[$i]['cn2'] ;
					elseif($temp[$i]['emp_mode'] == 15 && $temp[$i]['pt'] == 1  )
					$EmpItm_1_15 = $temp[$i]['cn2'] ;
					elseif($temp[$i]['emp_mode'] == 16 && $temp[$i]['pt'] == 1  )
					$EmpItm_1_16 = $temp[$i]['cn2'] ;
					elseif($temp[$i]['emp_mode'] == 17 && $temp[$i]['pt'] == 1  )
					$EmpItm_1_17 = $temp[$i]['cn2'] ;
					elseif($temp[$i]['emp_mode'] == 18 && $temp[$i]['pt'] == 1  )
					$EmpItm_1_18 = $temp[$i]['cn2'] ;
					elseif($temp[$i]['emp_mode'] == 19 && $temp[$i]['pt'] == 1  )
					$EmpItm_1_19 = $temp[$i]['cn2'] ;
					elseif($temp[$i]['emp_mode'] == 20 && $temp[$i]['pt'] == 1  )
					$EmpItm_1_20 = $temp[$i]['cn2'] ;
					
					elseif($temp[$i]['emp_mode'] == 21 && $temp[$i]['pt'] == 1  )
						$EmpItm_1_21 = $temp[$i]['cn2'] ;
					elseif($temp[$i]['emp_mode'] == 22 && $temp[$i]['pt'] == 1  )
						$EmpItm_1_22 = $temp[$i]['cn2'] ;
					elseif($temp[$i]['emp_mode'] == 23 && $temp[$i]['pt'] == 1  )
						$EmpItm_1_23 = $temp[$i]['cn2'] ;
					elseif($temp[$i]['emp_mode'] == 24 && $temp[$i]['pt'] == 1  )
						$EmpItm_1_24 = $temp[$i]['cn2'] ;
					elseif($temp[$i]['emp_mode'] == 25 && $temp[$i]['pt'] == 1  )
						$EmpItm_1_25 = $temp[$i]['cn2'] ;
					elseif($temp[$i]['emp_mode'] == 26 && $temp[$i]['pt'] == 1  )
						$EmpItm_1_26 = $temp[$i]['cn2'] ;
					elseif($temp[$i]['emp_mode'] == 27 && $temp[$i]['pt'] == 1  )
						$EmpItm_1_27 = $temp[$i]['cn2'] ;
					elseif($temp[$i]['emp_mode'] == 28 && $temp[$i]['pt'] == 1  )
						$EmpItm_1_28 = $temp[$i]['cn2'] ;
					elseif($temp[$i]['emp_mode'] == 29 && $temp[$i]['pt'] == 1  )
						$EmpItm_1_29 = $temp[$i]['cn2'] ;
						
					elseif($temp[$i]['emp_mode'] == 1 && $temp[$i]['pt'] == 2  )
						$EmpItm_2_1 = $temp[$i]['cn2'] ;
					elseif($temp[$i]['emp_mode'] == 2 && $temp[$i]['pt'] == 2  )
						$EmpItm_2_2 = $temp[$i]['cn2'] ;
					elseif($temp[$i]['emp_mode'] == 3 && $temp[$i]['pt'] == 2  )
						$EmpItm_2_3 = $temp[$i]['cn2'] ;						
					elseif($temp[$i]['emp_mode'] == 4 && $temp[$i]['pt'] == 2  )
						$EmpItm_2_4 = $temp[$i]['cn2'] ;					
					elseif($temp[$i]['emp_mode'] == 5 && $temp[$i]['pt'] == 2  )
						$EmpItm_2_5 = $temp[$i]['cn2'] ;
					elseif($temp[$i]['emp_mode'] == 6 && $temp[$i]['pt'] == 2  )
						$EmpItm_2_6 = $temp[$i]['cn2'] ;
					elseif($temp[$i]['emp_mode'] == 7 && $temp[$i]['pt'] == 2  )
						$EmpItm_2_7 = $temp[$i]['cn2'] ;
					elseif($temp[$i]['emp_mode'] == 8 && $temp[$i]['pt'] == 2  )
						$EmpItm_2_8 = $temp[$i]['cn2'] ;
					elseif($temp[$i]['emp_mode'] == 9 && $temp[$i]['pt'] == 2  )
						$EmpItm_2_9 = $temp[$i]['cn2'] ;
					elseif($temp[$i]['emp_mode'] == 10 && $temp[$i]['pt'] == 2  )
						$EmpItm_2_10 = $temp[$i]['cn2'] ;
						
					elseif($temp[$i]['emp_mode'] == 11 && $temp[$i]['pt'] == 2  )
						$EmpItm_2_11 = $temp[$i]['cn2'] ;
					elseif($temp[$i]['emp_mode'] == 12 && $temp[$i]['pt'] == 2  )
						$EmpItm_2_12 = $temp[$i]['cn2'] ;
					elseif($temp[$i]['emp_mode'] == 13 && $temp[$i]['pt'] == 2  )
						$EmpItm_2_13 = $temp[$i]['cn2'] ;						
					elseif($temp[$i]['emp_mode'] == 14 && $temp[$i]['pt'] == 2  )
						$EmpItm_2_14 = $temp[$i]['cn2'] ;
					elseif($temp[$i]['emp_mode'] == 15 && $temp[$i]['pt'] == 2  )
						$EmpItm_2_15 = $temp[$i]['cn2'] ;
					elseif($temp[$i]['emp_mode'] == 16 && $temp[$i]['pt'] == 2  )
						$EmpItm_2_16 = $temp[$i]['cn2'] ;
					elseif($temp[$i]['emp_mode'] == 17 && $temp[$i]['pt'] == 2  )
						$EmpItm_2_17 = $temp[$i]['cn2'] ;
					elseif($temp[$i]['emp_mode'] == 18 && $temp[$i]['pt'] == 2  )
						$EmpItm_2_18 = $temp[$i]['cn2'] ;
					elseif($temp[$i]['emp_mode'] == 19 && $temp[$i]['pt'] == 2  )
						$EmpItm_2_19 = $temp[$i]['cn2'] ;
					elseif($temp[$i]['emp_mode'] == 20 && $temp[$i]['pt'] == 2  )
						$EmpItm_2_20 = $temp[$i]['cn2'] ;
					
					elseif($temp[$i]['emp_mode'] == 21 && $temp[$i]['pt'] == 2  )
						$EmpItm_2_21 = $temp[$i]['cn2'] ;
					elseif($temp[$i]['emp_mode'] == 22 && $temp[$i]['pt'] == 2  )
						$EmpItm_2_22 = $temp[$i]['cn2'] ;
					elseif($temp[$i]['emp_mode'] == 23 && $temp[$i]['pt'] == 2  )
						$EmpItm_2_23 = $temp[$i]['cn2'] ;
					elseif($temp[$i]['emp_mode'] == 24 && $temp[$i]['pt'] == 2  )
						$EmpItm_2_24 = $temp[$i]['cn2'] ;
					elseif($temp[$i]['emp_mode'] == 25 && $temp[$i]['pt'] == 2  )
						$EmpItm_2_25 = $temp[$i]['cn2'] ;
					elseif($temp[$i]['emp_mode'] == 26 && $temp[$i]['pt'] == 2  )
						$EmpItm_2_26 = $temp[$i]['cn2'] ;
					elseif($temp[$i]['emp_mode'] == 27 && $temp[$i]['pt'] == 2  )
						$EmpItm_2_27 = $temp[$i]['cn2'] ;
					elseif($temp[$i]['emp_mode'] == 28 && $temp[$i]['pt'] == 2  )
						$EmpItm_2_28 = $temp[$i]['cn2'] ;
					elseif($temp[$i]['emp_mode'] == 29 && $temp[$i]['pt'] == 2  )
						$EmpItm_2_29 = $temp[$i]['cn2'] ;							
					
				}
				
				?>
				
				<tr >	
					<td>هيئت علمي</td>
					<td><a href="/HumanResources/reports/CompdetailReport.php?empMode=1&pt=1&Fdate=<?=$_POST['from_execute_date']?>&Tdate=<?=$_POST['to_execute_date']?>&UID=<?=$_POST['unitId']?>" target="_blank"><?=$EmpItm_1_1?></a></td>
					<td><a href="/HumanResources/reports/CompdetailReport.php?empMode=2&pt=1&Fdate=<?=$_POST['from_execute_date']?>&Tdate=<?=$_POST['to_execute_date']?>&UID=<?=$_POST['unitId']?>" target="_blank"><?=$EmpItm_1_2?></a></td>
					<td><a href="/HumanResources/reports/CompdetailReport.php?empMode=3&pt=1&Fdate=<?=$_POST['from_execute_date']?>&Tdate=<?=$_POST['to_execute_date']?>&UID=<?=$_POST['unitId']?>" target="_blank"><?=$EmpItm_1_3?></a></td>
					<td><a href="/HumanResources/reports/CompdetailReport.php?empMode=4&pt=1&Fdate=<?=$_POST['from_execute_date']?>&Tdate=<?=$_POST['to_execute_date']?>&UID=<?=$_POST['unitId']?>" target="_blank"><?=$EmpItm_1_4?></a></td>
					<td><a href="/HumanResources/reports/CompdetailReport.php?empMode=5&pt=1&Fdate=<?=$_POST['from_execute_date']?>&Tdate=<?=$_POST['to_execute_date']?>&UID=<?=$_POST['unitId']?>" target="_blank"><?=$EmpItm_1_5?></a></td>
					<td><a href="/HumanResources/reports/CompdetailReport.php?empMode=6&pt=1&Fdate=<?=$_POST['from_execute_date']?>&Tdate=<?=$_POST['to_execute_date']?>&UID=<?=$_POST['unitId']?>" target="_blank"><?=$EmpItm_1_6?></a></td>
					<td><a href="/HumanResources/reports/CompdetailReport.php?empMode=7&pt=1&Fdate=<?=$_POST['from_execute_date']?>&Tdate=<?=$_POST['to_execute_date']?>&UID=<?=$_POST['unitId']?>" target="_blank"><?=$EmpItm_1_7?></a></td>
					<td><a href="/HumanResources/reports/CompdetailReport.php?empMode=8&pt=1&Fdate=<?=$_POST['from_execute_date']?>&Tdate=<?=$_POST['to_execute_date']?>&UID=<?=$_POST['unitId']?>" target="_blank"><?=$EmpItm_1_8?></a></td>
					<td><a href="/HumanResources/reports/CompdetailReport.php?empMode=9&pt=1&Fdate=<?=$_POST['from_execute_date']?>&Tdate=<?=$_POST['to_execute_date']?>&UID=<?=$_POST['unitId']?>" target="_blank"><?=$EmpItm_1_9?></a></td>
					<td><a href="/HumanResources/reports/CompdetailReport.php?empMode=10&pt=1&Fdate=<?=$_POST['from_execute_date']?>&Tdate=<?=$_POST['to_execute_date']?>&UID=<?=$_POST['unitId']?>" target="_blank"><?=$EmpItm_1_10?></a></td>
					<td><a href="/HumanResources/reports/CompdetailReport.php?empMode=11&pt=1&Fdate=<?=$_POST['from_execute_date']?>&Tdate=<?=$_POST['to_execute_date']?>&UID=<?=$_POST['unitId']?>" target="_blank"><?=$EmpItm_1_11?></a></td>
					<td><a href="/HumanResources/reports/CompdetailReport.php?empMode=12&pt=1&Fdate=<?=$_POST['from_execute_date']?>&Tdate=<?=$_POST['to_execute_date']?>&UID=<?=$_POST['unitId']?>" target="_blank"><?=$EmpItm_1_12?></a></td>
					<td><a href="/HumanResources/reports/CompdetailReport.php?empMode=13&pt=1&Fdate=<?=$_POST['from_execute_date']?>&Tdate=<?=$_POST['to_execute_date']?>&UID=<?=$_POST['unitId']?>" target="_blank"><?=$EmpItm_1_13?></a></td>
					<td><a href="/HumanResources/reports/CompdetailReport.php?empMode=14&pt=1&Fdate=<?=$_POST['from_execute_date']?>&Tdate=<?=$_POST['to_execute_date']?>&UID=<?=$_POST['unitId']?>" target="_blank"><?=$EmpItm_1_14?></a></td>
					<td><a href="/HumanResources/reports/CompdetailReport.php?empMode=15&pt=1&Fdate=<?=$_POST['from_execute_date']?>&Tdate=<?=$_POST['to_execute_date']?>&UID=<?=$_POST['unitId']?>" target="_blank"><?=$EmpItm_1_15?></a></td>
					<td><a href="/HumanResources/reports/CompdetailReport.php?empMode=16&pt=1&Fdate=<?=$_POST['from_execute_date']?>&Tdate=<?=$_POST['to_execute_date']?>&UID=<?=$_POST['unitId']?>" target="_blank"><?=$EmpItm_1_16?></a></td>
					<td><a href="/HumanResources/reports/CompdetailReport.php?empMode=17&pt=1&Fdate=<?=$_POST['from_execute_date']?>&Tdate=<?=$_POST['to_execute_date']?>&UID=<?=$_POST['unitId']?>" target="_blank"><?=$EmpItm_1_17?></a></td>
					<td><a href="/HumanResources/reports/CompdetailReport.php?empMode=18&pt=1&Fdate=<?=$_POST['from_execute_date']?>&Tdate=<?=$_POST['to_execute_date']?>&UID=<?=$_POST['unitId']?>" target="_blank"><?=$EmpItm_1_18?></a></td>
					<td><a href="/HumanResources/reports/CompdetailReport.php?empMode=19&pt=1&Fdate=<?=$_POST['from_execute_date']?>&Tdate=<?=$_POST['to_execute_date']?>&UID=<?=$_POST['unitId']?>" target="_blank"><?=$EmpItm_1_19?></a></td>
					<td><a href="/HumanResources/reports/CompdetailReport.php?empMode=20&pt=1&Fdate=<?=$_POST['from_execute_date']?>&Tdate=<?=$_POST['to_execute_date']?>&UID=<?=$_POST['unitId']?>" target="_blank"><?=$EmpItm_1_20?></a></td>
					<td><a href="/HumanResources/reports/CompdetailReport.php?empMode=21&pt=1&Fdate=<?=$_POST['from_execute_date']?>&Tdate=<?=$_POST['to_execute_date']?>&UID=<?=$_POST['unitId']?>" target="_blank"><?=$EmpItm_1_21?></a></td>
					<td><a href="/HumanResources/reports/CompdetailReport.php?empMode=22&pt=1&Fdate=<?=$_POST['from_execute_date']?>&Tdate=<?=$_POST['to_execute_date']?>&UID=<?=$_POST['unitId']?>" target="_blank"><?=$EmpItm_1_22?></a></td>
					<td><a href="/HumanResources/reports/CompdetailReport.php?empMode=23&pt=1&Fdate=<?=$_POST['from_execute_date']?>&Tdate=<?=$_POST['to_execute_date']?>&UID=<?=$_POST['unitId']?>" target="_blank"><?=$EmpItm_1_23?></a></td>
					<td><a href="/HumanResources/reports/CompdetailReport.php?empMode=24&pt=1&Fdate=<?=$_POST['from_execute_date']?>&Tdate=<?=$_POST['to_execute_date']?>&UID=<?=$_POST['unitId']?>" target="_blank"><?=$EmpItm_1_24?></a></td>
					<td><a href="/HumanResources/reports/CompdetailReport.php?empMode=25&pt=1&Fdate=<?=$_POST['from_execute_date']?>&Tdate=<?=$_POST['to_execute_date']?>&UID=<?=$_POST['unitId']?>" target="_blank"><?=$EmpItm_1_25?></a></td>
					<td><a href="/HumanResources/reports/CompdetailReport.php?empMode=26&pt=1&Fdate=<?=$_POST['from_execute_date']?>&Tdate=<?=$_POST['to_execute_date']?>&UID=<?=$_POST['unitId']?>" target="_blank"><?=$EmpItm_1_26?></a></td>
					<td><a href="/HumanResources/reports/CompdetailReport.php?empMode=27&pt=1&Fdate=<?=$_POST['from_execute_date']?>&Tdate=<?=$_POST['to_execute_date']?>&UID=<?=$_POST['unitId']?>" target="_blank"><?=$EmpItm_1_27?></a></td>
					<td><a href="/HumanResources/reports/CompdetailReport.php?empMode=28&pt=1&Fdate=<?=$_POST['from_execute_date']?>&Tdate=<?=$_POST['to_execute_date']?>&UID=<?=$_POST['unitId']?>" target="_blank"><?=$EmpItm_1_28?></a></td>
					<td><a href="/HumanResources/reports/CompdetailReport.php?empMode=29&pt=1&Fdate=<?=$_POST['from_execute_date']?>&Tdate=<?=$_POST['to_execute_date']?>&UID=<?=$_POST['unitId']?>" target="_blank"><?=$EmpItm_1_29?></a></td>					
				</tr>	
				<tr >	
					<td>کارمند</td>
					<td><a href="/HumanResources/reports/CompdetailReport.php?empMode=1&pt=2&Fdate=<?=$_POST['from_execute_date']?>&Tdate=<?=$_POST['to_execute_date']?>&UID=<?=$_POST['unitId']?>" target="_blank"><?=$EmpItm_2_1?></a></td>
					<td><a href="/HumanResources/reports/CompdetailReport.php?empMode=2&pt=2&Fdate=<?=$_POST['from_execute_date']?>&Tdate=<?=$_POST['to_execute_date']?>&UID=<?=$_POST['unitId']?>" target="_blank"><?=$EmpItm_2_2?></a></td>
					<td><a href="/HumanResources/reports/CompdetailReport.php?empMode=3&pt=2&Fdate=<?=$_POST['from_execute_date']?>&Tdate=<?=$_POST['to_execute_date']?>&UID=<?=$_POST['unitId']?>" target="_blank"><?=$EmpItm_2_3?></a></td>
					<td><a href="/HumanResources/reports/CompdetailReport.php?empMode=4&pt=2&Fdate=<?=$_POST['from_execute_date']?>&Tdate=<?=$_POST['to_execute_date']?>&UID=<?=$_POST['unitId']?>" target="_blank"><?=$EmpItm_2_4?></a></td>
					<td><a href="/HumanResources/reports/CompdetailReport.php?empMode=5&pt=2&Fdate=<?=$_POST['from_execute_date']?>&Tdate=<?=$_POST['to_execute_date']?>&UID=<?=$_POST['unitId']?>" target="_blank"><?=$EmpItm_2_5?></a></td>
					<td><a href="/HumanResources/reports/CompdetailReport.php?empMode=6&pt=2&Fdate=<?=$_POST['from_execute_date']?>&Tdate=<?=$_POST['to_execute_date']?>&UID=<?=$_POST['unitId']?>" target="_blank"><?=$EmpItm_2_6?></a></td>
					<td><a href="/HumanResources/reports/CompdetailReport.php?empMode=7&pt=2&Fdate=<?=$_POST['from_execute_date']?>&Tdate=<?=$_POST['to_execute_date']?>&UID=<?=$_POST['unitId']?>" target="_blank"><?=$EmpItm_2_7?></a></td>
					<td><a href="/HumanResources/reports/CompdetailReport.php?empMode=8&pt=2&Fdate=<?=$_POST['from_execute_date']?>&Tdate=<?=$_POST['to_execute_date']?>&UID=<?=$_POST['unitId']?>" target="_blank"><?=$EmpItm_2_8?></a></td>
					<td><a href="/HumanResources/reports/CompdetailReport.php?empMode=9&pt=2&Fdate=<?=$_POST['from_execute_date']?>&Tdate=<?=$_POST['to_execute_date']?>&UID=<?=$_POST['unitId']?>" target="_blank"><?=$EmpItm_2_9?></a></td>
					<td><a href="/HumanResources/reports/CompdetailReport.php?empMode=10&pt=2&Fdate=<?=$_POST['from_execute_date']?>&Tdate=<?=$_POST['to_execute_date']?>&UID=<?=$_POST['unitId']?>" target="_blank"><?=$EmpItm_2_10?></a></td>
					<td><a href="/HumanResources/reports/CompdetailReport.php?empMode=11&pt=2&Fdate=<?=$_POST['from_execute_date']?>&Tdate=<?=$_POST['to_execute_date']?>&UID=<?=$_POST['unitId']?>" target="_blank"><?=$EmpItm_2_11?></a></td>
					<td><a href="/HumanResources/reports/CompdetailReport.php?empMode=12&pt=2&Fdate=<?=$_POST['from_execute_date']?>&Tdate=<?=$_POST['to_execute_date']?>&UID=<?=$_POST['unitId']?>" target="_blank"><?=$EmpItm_2_12?></a></td>
					<td><a href="/HumanResources/reports/CompdetailReport.php?empMode=13&pt=2&Fdate=<?=$_POST['from_execute_date']?>&Tdate=<?=$_POST['to_execute_date']?>&UID=<?=$_POST['unitId']?>" target="_blank"><?=$EmpItm_2_13?></a></td>
					<td><a href="/HumanResources/reports/CompdetailReport.php?empMode=14&pt=2&Fdate=<?=$_POST['from_execute_date']?>&Tdate=<?=$_POST['to_execute_date']?>&UID=<?=$_POST['unitId']?>" target="_blank"><?=$EmpItm_2_14?></a></td>
					<td><a href="/HumanResources/reports/CompdetailReport.php?empMode=15&pt=2&Fdate=<?=$_POST['from_execute_date']?>&Tdate=<?=$_POST['to_execute_date']?>&UID=<?=$_POST['unitId']?>" target="_blank"><?=$EmpItm_2_15?></a></td>
					<td><a href="/HumanResources/reports/CompdetailReport.php?empMode=16&pt=2&Fdate=<?=$_POST['from_execute_date']?>&Tdate=<?=$_POST['to_execute_date']?>&UID=<?=$_POST['unitId']?>" target="_blank"><?=$EmpItm_2_16?></a></td>
					<td><a href="/HumanResources/reports/CompdetailReport.php?empMode=17&pt=2&Fdate=<?=$_POST['from_execute_date']?>&Tdate=<?=$_POST['to_execute_date']?>&UID=<?=$_POST['unitId']?>" target="_blank"><?=$EmpItm_2_17?></a></td>
					<td><a href="/HumanResources/reports/CompdetailReport.php?empMode=18&pt=2&Fdate=<?=$_POST['from_execute_date']?>&Tdate=<?=$_POST['to_execute_date']?>&UID=<?=$_POST['unitId']?>" target="_blank"><?=$EmpItm_2_18?></a></td>
					<td><a href="/HumanResources/reports/CompdetailReport.php?empMode=19&pt=2&Fdate=<?=$_POST['from_execute_date']?>&Tdate=<?=$_POST['to_execute_date']?>&UID=<?=$_POST['unitId']?>" target="_blank"><?=$EmpItm_2_19?></a></td>
					<td><a href="/HumanResources/reports/CompdetailReport.php?empMode=20&pt=2&Fdate=<?=$_POST['from_execute_date']?>&Tdate=<?=$_POST['to_execute_date']?>&UID=<?=$_POST['unitId']?>" target="_blank"><?=$EmpItm_2_20?></a></td>
					<td><a href="/HumanResources/reports/CompdetailReport.php?empMode=21&pt=2&Fdate=<?=$_POST['from_execute_date']?>&Tdate=<?=$_POST['to_execute_date']?>&UID=<?=$_POST['unitId']?>" target="_blank"><?=$EmpItm_2_21?></a></td>
					<td><a href="/HumanResources/reports/CompdetailReport.php?empMode=22&pt=2&Fdate=<?=$_POST['from_execute_date']?>&Tdate=<?=$_POST['to_execute_date']?>&UID=<?=$_POST['unitId']?>" target="_blank"><?=$EmpItm_2_22?></a></td>
					<td><a href="/HumanResources/reports/CompdetailReport.php?empMode=23&pt=2&Fdate=<?=$_POST['from_execute_date']?>&Tdate=<?=$_POST['to_execute_date']?>&UID=<?=$_POST['unitId']?>" target="_blank"><?=$EmpItm_2_23?></a></td>
					<td><a href="/HumanResources/reports/CompdetailReport.php?empMode=24&pt=2&Fdate=<?=$_POST['from_execute_date']?>&Tdate=<?=$_POST['to_execute_date']?>&UID=<?=$_POST['unitId']?>" target="_blank"><?=$EmpItm_2_24?></a></td>
					<td><a href="/HumanResources/reports/CompdetailReport.php?empMode=25&pt=2&Fdate=<?=$_POST['from_execute_date']?>&Tdate=<?=$_POST['to_execute_date']?>&UID=<?=$_POST['unitId']?>" target="_blank"><?=$EmpItm_2_25?></a></td>
					<td><a href="/HumanResources/reports/CompdetailReport.php?empMode=26&pt=2&Fdate=<?=$_POST['from_execute_date']?>&Tdate=<?=$_POST['to_execute_date']?>&UID=<?=$_POST['unitId']?>" target="_blank"><?=$EmpItm_2_26?></a></td>
					<td><a href="/HumanResources/reports/CompdetailReport.php?empMode=27&pt=2&Fdate=<?=$_POST['from_execute_date']?>&Tdate=<?=$_POST['to_execute_date']?>&UID=<?=$_POST['unitId']?>" target="_blank"><?=$EmpItm_2_27?></a></td>
					<td><a href="/HumanResources/reports/CompdetailReport.php?empMode=28&pt=2&Fdate=<?=$_POST['from_execute_date']?>&Tdate=<?=$_POST['to_execute_date']?>&UID=<?=$_POST['unitId']?>" target="_blank"><?=$EmpItm_2_28?></a></td>
					<td><a href="/HumanResources/reports/CompdetailReport.php?empMode=29&pt=2&Fdate=<?=$_POST['from_execute_date']?>&Tdate=<?=$_POST['to_execute_date']?>&UID=<?=$_POST['unitId']?>" target="_blank"><?=$EmpItm_2_29?></a></td>	
				</tr>				
							
			</table>
			<!--//وضعيت استخدامي---------------->
			<br>
		<table width="80%">
		<tr><td width="50%">
			<table style="text-align: right;width=100%!important"   class="reportGenerator"   cellpadding="4" cellspacing="0" >
				<tr class="header" style="background-color: #C2D1E0 !important">	 
					<td>وضعيت استخدامي</td>
					<td>رسمي قطعي</td>
					<td>رسمي آزمايشي</td>
					<td>پيماني</td>
					<td>روزمزد بيمه اي</td>
					<td>قراردادي</td>															
				</tr>
				<?
				
				$STItm_1_1 = $STItm_1_2 =	$STItm_1_3 = $STItm_1_4 = $STItm_1_5 = $STItm_1_8 = $STItm_1_10 = $STItm_1_11= 0 ; 								
				$STItm_2_1 = $STItm_2_2 =	$STItm_2_3 = $STItm_2_4 = $STItm_2_5 = $STItm_2_8 = $STItm_2_10 = $STItm_2_11= 0 ; 		
								
				for($i=0;$i<count($temp2);$i++)
				{
					if($temp2[$i]['emp_state'] == 1 && $temp2[$i]['pt'] == 1  )
						$STItm_1_1 = $temp2[$i]['cn2'] ;
					elseif($temp2[$i]['emp_state'] == 2 && $temp2[$i]['pt'] == 1 )
						$STItm_1_2 = $temp2[$i]['cn2'] ;						
					elseif($temp2[$i]['emp_state'] == 3 && $temp2[$i]['pt'] == 1  )
						$STItm_1_3 = $temp2[$i]['cn2'] ;					
					elseif($temp2[$i]['emp_state'] == 4 && $temp2[$i]['pt'] == 1  )
						$STItm_1_4 = $temp2[$i]['cn2'] ;				
					elseif($temp2[$i]['emp_state'] == 5 && $temp2[$i]['pt'] == 1 )
						$STItm_1_5 = $temp2[$i]['cn2'] ;						
					elseif($temp2[$i]['emp_state'] == 8 && $temp2[$i]['pt'] == 1  )
						$STItm_1_8 = $temp2[$i]['cn2'] ;					
					elseif($temp2[$i]['emp_state'] == 10 && $temp2[$i]['pt'] == 1  )
						$STItm_1_10 = $temp2[$i]['cn2'] ;				
					elseif($temp2[$i]['emp_state'] == 11 && $temp2[$i]['pt'] == 1 )
						$STItm_1_11 = $temp2[$i]['cn2'] ;
						
					elseif($temp2[$i]['emp_state'] == 1 && $temp2[$i]['pt'] == 2  )
						$STItm_2_1 = $temp2[$i]['cn2'] ;
					elseif($temp2[$i]['emp_state'] == 2 && $temp2[$i]['pt'] == 2 )
						$STItm_2_2 = $temp2[$i]['cn2'] ;						
					elseif($temp2[$i]['emp_state'] == 3 && $temp2[$i]['pt'] == 2  )
						$STItm_2_3 = $temp2[$i]['cn2'] ;					
					elseif($temp2[$i]['emp_state'] == 4 && $temp2[$i]['pt'] == 2  )
						$STItm_2_4 = $temp2[$i]['cn2'] ;				
					elseif($temp2[$i]['emp_state'] == 5 && $temp2[$i]['pt'] == 2 )
						$STItm_2_5 = $temp2[$i]['cn2'] ;						
					elseif($temp2[$i]['emp_state'] == 8 && $temp2[$i]['pt'] == 2  )
						$STItm_2_8 = $temp2[$i]['cn2'] ;					
					elseif($temp2[$i]['emp_state'] == 10 && $temp2[$i]['pt'] == 2  )
						$STItm_2_10 = $temp2[$i]['cn2'] ;				
					elseif($temp2[$i]['emp_state'] == 11 && $temp2[$i]['pt'] == 2 )
						$STItm_2_11 = $temp2[$i]['cn2'] ;
															
				}
				
				?>
				
				<tr >	
					<td>هيئت علمي</td>
					<td><?=$STItm_1_4?></td>					
					<td><?=$STItm_1_3?></td>					
					<td><?= $STItm_1_1 + $STItm_1_2 + $STItm_1_10 + $STItm_1_11 ?></td>
					<td><?=$STItm_1_8?></td>
					<td><?=$STItm_1_5?></td>					
				</tr>	
				<tr >	
					<td>کارمند</td>
					<td><?=$STItm_2_4?></td>					
					<td><?=$STItm_2_3?></td>					
					<td><?= ($STItm_2_1 + $STItm_2_2 + $STItm_2_10 + $STItm_2_11) ?></td>
					<td><?=$STItm_2_8?></td>
					<td><?=$STItm_2_5?></td>			
				</tr>				
							
			</table>
			</td>
				<!--// آمار نيروي انساني-------------------->
			<td>
			<table style="text-align: right;width=100%!important"  width="100%" class="reportGenerator"   cellpadding="4" cellspacing="0" >
				<tr class="header" style="background-color:#C2D1E0 !important">	 
					<td>آمار نيروي انساني</td>
					<td> فروردين</td>
					<td>ارديبهشت</td>
					<td>خرداد</td>
					<td>تير</td>
					<td>مرداد</td>															
					<td>شهريور</td>
					<td>مهر</td>
					<td>آبان</td>
					<td>آذر</td>
					<td>دي</td>
					<td>بهمن</td>
					<td>اسفند</td>
				</tr>
							
				<tr >	
					<td>سال <?=(substr($_POST['from_execute_date'],0,4)-1)?></td>
					<td><?=$month1_92?></td>					
					<td><?=$month2_92?></td>					
					<td><?=$month3_92?></td>
					<td><?=$month4_92?></td>	
					<td><?=$month5_92?></td>	
					<td><?=$month6_92?></td>	
					<td><?=$month7_92?></td>	
					<td><?=$month8_92?></td>	
					<td><?=$month9_92?></td>	
					<td><?=$month10_92?></td>	
					<td><?=$month11_92?></td>	
					<td><?=$month12_92?></td>						
				</tr>	
				<tr >	
					<td>سال <?=(substr($_POST['from_execute_date'],0,4))?></td>
					<td><?=$month1_93?></td>					
					<td><?=$month2_93?></td>					
					<td><?=$month3_93?></td>
					<td><?=$month4_93?></td>	
					<td><?=$month5_93?></td>	
					<td><?=$month6_93?></td>	
					<td><?=$month7_93?></td>	
					<td><?=$month8_93?></td>	
					<td><?=$month9_93?></td>	
					<td><?=$month10_93?></td>	
					<td><?=$month11_93?></td>	
					<td><?=$month12_93?></td>					
				</tr>				
							
			</table>
			</td></tr></table>
			
			<!----- اطلاعات کارکنان -->
			<?
			
				$qry = " select   ( sum(sumg) / count(*) ) Av
							from (
									SELECT PersonID ,  EvlPeriodID , sum(Grade) sumg

										 FROM ease.SEVL_ItemScore se inner join persons p 
										                                    on se.PersonID = p.PersonID 
																	 inner join staff s 
																			on p.PersonID = s.PersonID

										 where ItemID in (6,7,8) AND
									           EvlPeriodID = 3 ".$whrUID."

										 group by  PersonID ,
									   EvlPeriodID ) t1" ; 
			    $temp8 = PdoDataAccess::runquery($qry); 
				
				  
				
			?>
			<br>
			<table style="text-align: right;width:95%!important"  width="80%" class="reportGenerator"   cellpadding="4" cellspacing="0" >
				<tr class="header" style="background-color: #CEDEFF !important">	 
					<td>ميانگين مبلغ هر ساعت اضافه کار</td>		
					<td>جمع اضافه کار <br>مبلغ - ساعت</td>
					<td>ميانگين نمره ارزشيابي</td>
					<td>ميانگين سنوات خدمت</td>
					<td>کمترين سنوات خدمت</td>
					<td>بيشترين سنوات خدمت</td>
					<td>درصد کارمندان از کل </td>
					<td>درصد اعضاي هيئت علمي از کل</td>
					<td>تعداد عدم تطابق پست و مدرک</td>
					<td>تعداد افراد خارج شده - وارد شده</td>
					<td>تعداد کارکنان داراي فوق العاده مديريت</td>
				</tr>
							
				<tr >	
					<td><?=$AVeExtraWork?></td>	
					<td><? echo ($temp6[0]['sv'] + $temp7[0]['sv'] ) ; echo "مبلغ"."<br>" ; 
							echo $temp6[0]['hrs'] + $temp7[0]['hrs'] ;echo "ساعت"; ?></td> 
					<td><?=round($temp8[0]['Av'])?></td>
					<td><? echo $temp9[0]['AveYear']."&nbsp;سال &nbsp;<br>".$temp9[0]['AveMonth']."&nbsp;ماه&nbsp;<br>".$temp9[0]['AveDay']."&nbsp;روز&nbsp;" ; ?></td>
					<td><?=$temp9[0]['MinYear']?></td>
					<td><?=$temp9[0]['MaxYear']?></td>
					<td><?=round(($temp10[0]['cn']/$tmp[0]['cn'])*100)?></td>
					<td><?=round(($temp11[0]['cn']/$tmp[0]['cn'])*100)?></td>
					<td></td>
					<td><?=$temp12[0]['cn'].'<br>'.$temp13[0]['cn']?></td>
					<td><?=$temp14[0]['cn']?></td>
				</tr>	
										
			</table>
			
		</center>
	</body>
</html>

<?

die() ; 
	
}

?>

<form id="mainFormReport">
    <center>
        <div id="mainpanelReport"></div>
    </center>    
</form>