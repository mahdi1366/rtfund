<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	91.04
//---------------------------
require_once '../header.inc.php';
require_once inc_dataReader;
require_once inc_manage_unit;

$whereDate =""; 
$whereW="";

if(!empty($_REQUEST['FDATE']) && $_REQUEST['FDATE'] != '0000-00-00' ) {
    $whereDate = " AND  tbl2.execute_date>='".$_REQUEST['FDATE']."'" ;  
    $whereW = " AND w.execute_date >= '".$_REQUEST['FDATE']."'  " ; 
    }

if(!empty($_REQUEST['TDATE']) && $_REQUEST['TDATE'] != '0000-00-00' ) {
    $whereDate .= " AND tbl2.execute_date<='".$_REQUEST['TDATE']."'" ;  
    $whereW .= " AND w.execute_date <= '".$_REQUEST['TDATE']."'" ; 
    }
    
$qry = "  select tbl1.staff_id , tbl0.pfname , tbl0.plname , tbl0.national_code ,  tbl2.ouid , bi1.Title educatin_title , bi2.Title emp_state , tbl0.birth_date , 
                 bi3.Title marital_title ,  tbl2.children_count , sf.ptitle field_title  , tbl1.work_start_date , wt.title writ_type_title , wst.title sub_writ_title ,
                 tbl2.writ_id , tbl2.writ_ver , bi4.Title emp_mode_title 
                 
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
                                                                        w.history_only = 0 and w.person_type = ".$_REQUEST['pt']." ".$whereW." 
                                                                GROUP BY w.staff_id ) med) as mtbl2 on(tbl1.staff_id=mtbl2.staff_id)

                                                                inner join writs as tbl2 on mtbl2.staff_id = tbl2.staff_id and
                                                                                    mtbl2.writ_id = tbl2.writ_id and mtbl2.writ_ver = tbl2.writ_ver 
                                                                inner join Basic_Info bi1 on bi1.InfoID = tbl2.education_level  and bi1.typeid = 6 
                                                                inner join Basic_Info bi2 on bi2.InfoID = tbl2.emp_state  and bi2.typeid = 3 
                                                                inner join Basic_Info bi3 on bi3.InfoID = tbl2.marital_status  and bi3.typeid = 15 
                                                                inner join study_fields sf on sf.sfid =   tbl2.sfid  
                                                                inner join writ_types wt on wt.writ_type_id = tbl2.writ_type_id and  wt.person_type = tbl2.person_type
                                                                inner join writ_subtypes wst on  wst.person_type  = tbl2.person_type and  
                                                                                                 wst.writ_type_id  = tbl2.writ_type_id and 
                                                                                                 wst.writ_subtype_id  = tbl2.writ_subtype_id 
                                                                inner join Basic_Info bi4 on bi4.InfoID = tbl2.emp_mode  and bi4.typeid = 4
                                                                                                 


                                       

        where 1=1   AND tbl2.emp_mode = ".$_REQUEST['emp_mode']." 
                    AND tbl1.person_type = ".$_REQUEST['pt']." ".$whereDate."
      
       
    
" ; 


$data = PdoDataAccess::runquery($qry) ; 
$record=""; 

for($i=0 ; $i< count($data) ; $i++)
{
    
	$record .= "<tr><td>".$i."</td><td>".$data[$i]['staff_id']."</td><td>".$data[$i]['pfname']."</td><td>".$data[$i]['plname']."</td>
                        <td>".$data[$i]['national_code']."</td><td>".manage_units::get_full_title($data[$i]['ouid'])."</td><td>".$data[$i]['educatin_title']."</td><td>".$data[$i]['emp_state']."</td>
                        <td>".DateModules::miladi_to_shamsi($data[$i]['birth_date'])."</td><td>".$data[$i]['marital_title']."</td><td>".$data[$i]['children_count']."</td><td>".$data[$i]['field_title']."</td>
                        <td>".DateModules::miladi_to_shamsi($data[$i]['work_start_date'])."</td><td>".$data[$i]['writ_type_title']."</td><td>".$data[$i]['sub_writ_title']."</td><td>".$data[$i]['writ_id']."</td>
                        <td>".$data[$i]['writ_ver']."</td><td>".$data[$i]['emp_mode_title']."</td>
                    </tr>";
}

	$tags =  array('<!--record-->' => $record ,
				   '<!--now-->' => DateModules::shNow());

	$content = file_get_contents("FullInformation.htm");
	$content = str_replace(array_keys($tags), array_values($tags), $content);
	echo $content;
?>
