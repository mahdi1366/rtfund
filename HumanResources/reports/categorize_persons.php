<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	91.04
//---------------------------
    
 $empWhere = "(" ;
 for($i=1; $i<30 ; $i++)
 {     
     $index= 'CHECKLIST:emp_mode:'.$i ; 
     if(isset($_POST[$index]))
        {
            $empWhere.= $i.","; 
        }
 }      
$empWhere = substr($empWhere , 0 , -1 ) ;
$empWhere .= ")";  

$whereDate =""; 
$whereW="";

if(!empty($_POST['FDATE:72'])) {
    $whereDate = " AND  tbl2.execute_date>='".DateModules::shamsi_to_miladi($_POST['FDATE:72'])."'" ;  
    $whereW = " AND w.execute_date >= '".DateModules::shamsi_to_miladi($_POST['FDATE:72'])."'  " ; 
    }

if(!empty($_POST['TDATE:72']) ) {
    $whereDate .= " AND tbl2.execute_date<='".DateModules::shamsi_to_miladi($_POST['TDATE:72'])."'" ;  
    $whereW .= " AND w.execute_date <= '".DateModules::shamsi_to_miladi($_POST['TDATE:72'])."'" ; 
    }


        
$query = "select tbl2.emp_mode as '9802',tbl3.Title as '980',count(tbl0.PersonID) as '983',tbl0.person_type as '10572',
                 tbl1.person_type as '10582',tbl2.emp_mode as '11162',tbl1.person_type as '11382',tbl8.Title as '1138',
                 tbl2.emp_mode as '11392',tbl1.person_type as '11402',tbl2.emp_mode as '11412',tbl1.person_type as '11432',
                 tbl0.person_type as '11442',tbl2.emp_mode as '11452'
                 
 from persons as tbl0
 left join staff as tbl1 on(tbl0.PersonID=tbl1.PersonID )
 left join (SELECT staff_id,
                               SUBSTRING_INDEX(SUBSTRING(max_execute_date,11),'.',1) writ_id,
                               SUBSTRING_INDEX(max_execute_date,'.',-1) writ_ver
                        FROM (SELECT w.staff_id,
       max( CONCAT(w.execute_date,w.writ_id,'.',w.writ_ver) ) max_execute_date
                        FROM writs w
                                 INNER JOIN staff ls
                                         ON(w.staff_id = ls.staff_id)
                        WHERE 
                                  w.history_only = 0 and w.person_type = ".$_POST['SELECT:196']." ".$whereW." 
                        GROUP BY w.staff_id ) med) as mtbl2 on(tbl1.staff_id=mtbl2.staff_id)

                        inner join writs as tbl2 on mtbl2.staff_id = tbl2.staff_id and
                                            mtbl2.writ_id = tbl2.writ_id and mtbl2.writ_ver = tbl2.writ_ver

 left join Basic_Info as tbl3 on(tbl3.TypeID=4 AND tbl3.InfoID=tbl2.emp_mode)
 left join Basic_Info as tbl4 on(tbl4.TypeID=4 AND tbl4.InfoID=tbl2.emp_mode)
 left join Basic_Info as tbl5 on(tbl5.TypeID=4 AND tbl5.InfoID=tbl2.emp_mode)
 left join Basic_Info as tbl6 on(tbl6.TypeID=4 AND tbl6.InfoID=tbl2.emp_mode)
 left join Basic_Info as tbl7 on(tbl7.TypeID=4 AND tbl7.InfoID=tbl2.emp_mode)
 left join Basic_Info as tbl8 on(tbl8.TypeID=16 AND tbl8.InfoID=tbl1.person_type)
 left join Basic_Info as tbl9 on(tbl9.TypeID=16 AND tbl9.InfoID=tbl1.person_type)
 left join Basic_Info as tbl10 on(tbl10.TypeID=16 AND tbl10.InfoID=tbl1.person_type)
 left join Basic_Info as tbl11 on(tbl11.TypeID=16 AND tbl11.InfoID=tbl1.person_type)
 left join Basic_Info as tbl12 on(tbl12.TypeID=16 AND tbl12.InfoID=tbl0.person_type)
 left join Basic_Info as tbl13 on(tbl13.TypeID=16 AND tbl13.InfoID=tbl0.person_type)

 where 1=1 AND tbl2.emp_mode in ".$empWhere." 
           AND tbl1.person_type = ".$_POST['SELECT:196']." ".$whereDate."
            
  group by tbl2.emp_mode,tbl1.person_type
  order by tbl13.Title,tbl7.Title ";
 


$data = PdoDataAccess::runquery($query) ; 
 
define("person_type",			"11402"); //tbl0.person_type
define("person_type_title",		"1138"); // tbl7.Title-tbl0.person_type
define("emp_mode",				"9802"); //tbl2.emp_mode
define("emp_mode_title",		"980"); //tbl3.Title-tbl2.emp_mode
define("CountPID",				"983"); //persons.PersonID


$record = "";

for($i=0 ; $i< count($data) ; $i++)
{
	
	$record .= "<tr><td>".$data[$i][person_type_title]."</td><td>".$data[$i][emp_mode_title]."</td>
					<td><a href='/HumanResources/reports/FullInformation.php?".
						"Q0=15&pt=".$data[$i][person_type].
						"&emp_mode=".$data[$i][emp_mode].
						"&FDATE=" . DateModules::shamsi_to_miladi($_POST["FDATE:72"]) .
						"&TDATE=" . DateModules::shamsi_to_miladi($_POST["TDATE:72"]) .
						
					"' target = '_blank' >".$data[$i][CountPID]."</a></td></tr>";
}

	$tags =  array('<!--record-->' => $record ,
				   '<!--now-->' => DateModules::shNow());

	$content = file_get_contents("../../reports/group_information.htm");
	$content = str_replace(array_keys($tags), array_values($tags), $content);
	echo $content;
?>
