<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	91.08.07
//---------------------------
require_once("../../../header.inc.php");
?>
<style>
		.reportGenerator {border-collapse: collapse;border: 1px solid black;font-family: tahoma;font-size: 8pt;
						  text-align: center;width: 50%;padding: 2px;}
		.reportGenerator .header {color: white;font-weight: bold;background-color:#3F5F96}
		.reportGenerator td {border: 1px solid #555555;height: 20px;}
</style>
<?
	$qry = " select ouid , ptitle 
				from org_new_units 
				      where ouid =".$_GET['ouid'] ;
    
	$res  = PdoDataAccess::runquery($qry) ; 
	
//....................	
	$qry = " select p.PersonID,s.staff_id , p.plname , p.pfname ,
					case w.person_type
						  when 1 then 'هيئت علمي'
						  when 2 then 'کارمند'
						  when 3 then 'روز مزد بيمه اي'
						  when 5 then 'قراردادي'
					end  pt 
					,
					bi1.title emp_state_title ,
					bi2.title emp_mode_title ,
					o.ptitle unit_title 

			   from staff s inner join writs w
										   on s.staff_id = w.staff_id and
											  s.last_writ_id = w.writ_id and
											  s.last_writ_ver = w.writ_ver
						    inner join persons p 
											on s.personid = p.personid
							inner join Basic_Info bi1 
											on  bi1.typeid = 3 and bi1.infoid = w.emp_state
							inner join Basic_Info bi2 
											on  bi2.typeid = 4 and bi2.infoid  = w.emp_mode 												
							inner join org_new_units o 
											on o.ouid = w.ouid

				  where w.execute_date > '2014-03-20' and w.person_type in ( 1,2,3,5 )
						 and w.ouid = ".$_GET['ouid']." 
				  order by s.person_type " ;  
						 
	$dt = PdoDataAccess::runquery($qry) ; 
	
	echo '<META http-equiv=Content-Type content="text/html; charset=UTF-8" ><body dir="rtl"><center>';
	if(count($dt) > 0 ) {
	 echo '<table  class="reportGenerator" style="text-align: right;width:50%!important" cellpadding="4" cellspacing="0">
			 <tr class="header" ><td colspan=8 >واحد سازمانی: &nbsp; '.$dt[0]['unit_title'].'</td></tr>
			 <tr class="header">					
				<td>ردیف </td>			
				<td>شماره شناسایی</td>
				<td>نام خانوادگی</td>				
				<td>نام</td>
				<td>نوع فرد </td>
				<td>وضعیت استخدامی</td>
				<td>حالت استخدامی</td>
				<td>عکس</td>
			</tr>' ; 
	
	
		for($i=0;$i<count($dt); $i++) 
		{
			echo " <tr>
					<td>".( $i + 1 )."</td>			   
					<td>".$dt[$i]['staff_id']."</td> 
					<td>".$dt[$i]['plname']."</td>
					<td>".$dt[$i]['pfname']."</td>     
					<td>".$dt[$i]['pt']."</td> 
					<td>".$dt[$i]['emp_state_title']."</td> 
					<td>".$dt[$i]['emp_mode_title']."</td>
					<td><img src='showImage.php?PersonID=".$dt[$i]['PersonID']."' style='height:100px;width:80px;'></td>" ;				
			echo " </tr>" ; 
			
		}
		echo "</table>" ; 
	}
//..................................................................................
	
	$qry = " select ouid , ptitle 
				from org_new_units 
				      where parent_ouid =".$_GET['ouid'] ;
    
	$dt2 = PdoDataAccess::runquery($qry) ; 
	if(count($dt2) > 0 ) {		
		echo '<br><table  class="reportGenerator" style="text-align: right;width:50%!important" cellpadding="4" cellspacing="0">
				<tr class="header" ><td colspan=4 > واحد اصلی :'.$res[0]['ptitle'].'</td></tr>
				 <tr class="header">					
					<td>ردیف </td>			
					<td>واحد محل فرعی</td>
					<td>تعداد کارکنان</td>				
				</tr>' ; 
				
		for($i=0 ; $i < count($dt2) ; $i++)
		{	 
		
			$qry = " select count(*) cn

					   from staff s inner join writs w
												   on s.staff_id = w.staff_id and
													  s.last_writ_id = w.writ_id and
													  s.last_writ_ver = w.writ_ver

						  where w.execute_date > '2014-03-20' and w.person_type in ( 1,2,3,5 )  and 
								w.ouid in ( select ouid
												 from org_new_units

												 
												where  ( ouid = ".$dt2[$i]['ouid']."  OR parent_ouid = ".$dt2[$i]['ouid']." OR
														 parent_path LIKE '%,".$dt2[$i]['ouid'].",%' OR
														 substring(parent_path ,1,( length(".$dt2[$i]['ouid'].") + 1 ) ) = '".$dt2[$i]['ouid'].",' OR
														 substring(parent_path ,-( length(".$dt2[$i]['ouid'].") + 1 ),( length(".$dt2[$i]['ouid'].") + 1 ) ) = ',".$dt2[$i]['ouid']."' )
										   ) " ;  

			$dt4 = PdoDataAccess::runquery($qry) ; 
	  
		   echo " <tr>
					<td>".( $i + 1 )."</td>			   
					<td>".$dt2[$i]['ptitle']."</td> " ; 
					if($dt4[0]['cn'] > 0 )
						echo "<td><a href='/HumanResources/salary/reports/ui/SubUnitDetail1.php?ouid=".$dt2[$i]['ouid']."' target='_blank'>".$dt4[0]['cn']."</td> " ; 
					else echo "<td>".$dt4[0]['cn']."</td>"	; 
		   echo "</tr>" ; 
		
		}
	}
	
die() ;  

?>