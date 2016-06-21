<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	91.5
//---------------------------
require_once("../header.inc.php");
require_once inc_manage_unit;

$whereW ="" ;
$workDate = "" ; 
$exitW = "" ; 
if(!empty($_GET['fromDate'])) {
   // $workDate = " AND s.work_start_date >= '".DateModules::shamsi_to_miladi($_GET['fromDate'])."'" ;     
    $whereW = " AND w.execute_date >= '".DateModules::shamsi_to_miladi($_GET['fromDate'])."'  " ; 
    }
if(!empty($_GET['toDate']) ) {
    $workDate .= " AND s.work_start_date <= '".DateModules::shamsi_to_miladi($_GET['toDate'])."'" ;  
    $whereW .= " AND w.execute_date <= '".DateModules::shamsi_to_miladi($_GET['toDate'])."'" ; 
    $exitW .= " AND w.execute_date <= '".DateModules::shamsi_to_miladi($_GET['toDate'])."'" ; 
    $salaryDate =  DateModules::shamsi_to_miladi($_GET['toDate']) ;
    }
else {
    $workDate .= " AND s.work_start_date <= '".DateModules::Now()."'" ; 
    $exitW .= " AND w.execute_date <= '".DateModules::Now()."'" ;  
    $salaryDate =  DateModules::Now() ; 
}
 $title = "" ;    
    
 if(!empty($_GET['rasmiGh']) &&  $_GET['rasmiGh'] == 1 ) {  
 
    $title = "گزارش کل کارکنان رسمی قطعی شاغل" ; 
    $query = "  select  s.staff_id , p.pfname , p.plname , bi1.Title emp_state_title,bi2.Title emp_mode_title , bi3.Title person_type_title ,w.ouid
                    from persons p inner join staff s
                                       on p.personid = s.personid 
                              inner join (SELECT    staff_id,
                                                    SUBSTRING_INDEX(SUBSTRING(max_execute_date,11),'.',1) writ_id,
                                                    SUBSTRING_INDEX(max_execute_date,'.',-1) writ_ver
                                                FROM (SELECT w.staff_id,
                                                            max( CONCAT(w.execute_date,w.writ_id,'.',w.writ_ver) ) max_execute_date
                                                        FROM writs w
                                                                INNER JOIN staff ls
                                                                        ON(w.staff_id = ls.staff_id)
                                                        WHERE w.history_only = 0 ".$whereW."
                                                        GROUP BY w.staff_id)tbl2) tbl1
                                             on s.staff_id = tbl1.staff_id 
                              inner join writs w
                                       on tbl1.writ_id = w.writ_id and
                                          tbl1.writ_ver = w.writ_ver and
                                          tbl1.staff_id = w.staff_id
                                    inner join Basic_Info bi1 on bi1.typeid = 3 and w.emp_state = bi1.infoid
                                    inner join Basic_Info bi2 on bi2.typeid = 4 and w.emp_mode = bi2.infoid
                                    inner join Basic_Info bi3 on bi3.typeid = 16 and s.person_type = bi3.infoid

                        where w.emp_mode in(1,2,3,4,6,8,10,21) and s.person_type in (2) AND w.emp_state in (4)  $whereW " ; 
 
    $data = PdoDataAccess::runquery($query) ; 
    
    
}
else if(!empty($_GET['rasmiAz']) &&  $_GET['rasmiAz'] == 1 ) { 
    $title = "گزارش کل کارکنان رسمی آزمایشی شاغل"; 
    $query = "  select  s.staff_id , p.pfname , p.plname , bi1.Title emp_state_title,bi2.Title emp_mode_title , bi3.Title person_type_title ,w.ouid
                    from persons p inner join staff s
                                       on p.personid = s.personid 
                              inner join (SELECT    staff_id,
                                                    SUBSTRING_INDEX(SUBSTRING(max_execute_date,11),'.',1) writ_id,
                                                    SUBSTRING_INDEX(max_execute_date,'.',-1) writ_ver
                                                FROM (SELECT w.staff_id,
                                                            max( CONCAT(w.execute_date,w.writ_id,'.',w.writ_ver) ) max_execute_date
                                                        FROM writs w
                                                                INNER JOIN staff ls
                                                                        ON(w.staff_id = ls.staff_id)
                                                        WHERE w.history_only = 0 ".$whereW."
                                                        GROUP BY w.staff_id)tbl2) tbl1
                                             on s.staff_id = tbl1.staff_id 
                              inner join writs w
                                       on tbl1.writ_id = w.writ_id and
                                          tbl1.writ_ver = w.writ_ver and
                                          tbl1.staff_id = w.staff_id
                                    inner join Basic_Info bi1 on bi1.typeid = 3 and w.emp_state = bi1.infoid
                                    inner join Basic_Info bi2 on bi2.typeid = 4 and w.emp_mode = bi2.infoid
                                    inner join Basic_Info bi3 on bi3.typeid = 16 and s.person_type = bi3.infoid

                        where w.emp_mode in(1,2,3,4,6,8,10,21) and s.person_type in (2) AND w.emp_state in (3)  $whereW " ; 

    $data = PdoDataAccess::runquery($query) ; 
}
else if(!empty($_GET['Peymani']) &&  $_GET['Peymani'] == 1 ) { 
    $title = "گزارش کل کارکنان پیمانی شاغل";
    $query = "  select  s.staff_id , p.pfname , p.plname , bi1.Title emp_state_title,bi2.Title emp_mode_title , bi3.Title person_type_title ,w.ouid
                    from persons p inner join staff s
                                       on p.personid = s.personid 
                              inner join (SELECT    staff_id,
                                                    SUBSTRING_INDEX(SUBSTRING(max_execute_date,11),'.',1) writ_id,
                                                    SUBSTRING_INDEX(max_execute_date,'.',-1) writ_ver
                                                FROM (SELECT w.staff_id,
                                                            max( CONCAT(w.execute_date,w.writ_id,'.',w.writ_ver) ) max_execute_date
                                                        FROM writs w
                                                                INNER JOIN staff ls
                                                                        ON(w.staff_id = ls.staff_id)
                                                        WHERE w.history_only = 0 ".$whereW."
                                                        GROUP BY w.staff_id)tbl2) tbl1
                                             on s.staff_id = tbl1.staff_id 
                              inner join writs w
                                       on tbl1.writ_id = w.writ_id and
                                          tbl1.writ_ver = w.writ_ver and
                                          tbl1.staff_id = w.staff_id
                                    inner join Basic_Info bi1 on bi1.typeid = 3 and w.emp_state = bi1.infoid
                                    inner join Basic_Info bi2 on bi2.typeid = 4 and w.emp_mode = bi2.infoid
                                    inner join Basic_Info bi3 on bi3.typeid = 16 and s.person_type = bi3.infoid

                        where w.emp_mode in(1,2,3,4,6,8,10,21) and s.person_type in (2) AND w.emp_state in (1,2,10)  $whereW " ; 

    $data = PdoDataAccess::runquery($query) ; 
}
else if(!empty($_GET['Roozmozd']) &&  $_GET['Roozmozd'] == 1 ) {  
    $title ="گزارش کل کارکنان روزمزد  بیمه ای شاغل";
    $query = "  select  s.staff_id , p.pfname , p.plname , bi1.Title emp_state_title,bi2.Title emp_mode_title , bi3.Title person_type_title ,w.ouid
                    from persons p inner join staff s
                                       on p.personid = s.personid 
                              inner join (SELECT    staff_id,
                                                    SUBSTRING_INDEX(SUBSTRING(max_execute_date,11),'.',1) writ_id,
                                                    SUBSTRING_INDEX(max_execute_date,'.',-1) writ_ver
                                                FROM (SELECT w.staff_id,
                                                            max( CONCAT(w.execute_date,w.writ_id,'.',w.writ_ver) ) max_execute_date
                                                        FROM writs w
                                                                INNER JOIN staff ls
                                                                        ON(w.staff_id = ls.staff_id)
                                                        WHERE w.history_only = 0 ".$whereW."
                                                        GROUP BY w.staff_id)tbl2) tbl1
                                             on s.staff_id = tbl1.staff_id 
                              inner join writs w
                                       on tbl1.writ_id = w.writ_id and
                                          tbl1.writ_ver = w.writ_ver and
                                          tbl1.staff_id = w.staff_id
                                    inner join Basic_Info bi1 on bi1.typeid = 3 and w.emp_state = bi1.infoid
                                    inner join Basic_Info bi2 on bi2.typeid = 4 and w.emp_mode = bi2.infoid
                                    inner join Basic_Info bi3 on bi3.typeid = 16 and s.person_type = bi3.infoid

                        where w.emp_mode in(1,2,3,4,6,8,10,21) and s.person_type in (3) AND w.emp_state in (8)  $whereW " ; 

    $data = PdoDataAccess::runquery($query) ; 
}
else if(!empty($_GET['gharardadi']) &&  $_GET['gharardadi'] == 1 ) {   
    $title = "گزارش کل کارکنان قرارداد انجام کار مشخص شاغل" ; 
    $query = "  select  s.staff_id , p.pfname , p.plname , bi1.Title emp_state_title,bi2.Title emp_mode_title , bi3.Title person_type_title ,w.ouid
                   from persons p inner join staff s
                                       on p.personid = s.personid
                              inner join (SELECT    staff_id,
                                                    SUBSTRING_INDEX(SUBSTRING(max_execute_date,11),'.',1) writ_id,
                                                    SUBSTRING_INDEX(max_execute_date,'.',-1) writ_ver
                                                FROM (SELECT w.staff_id,
                                                            max( CONCAT(w.execute_date,w.writ_id,'.',w.writ_ver) ) max_execute_date
                                                        FROM writs w
                                                                INNER JOIN staff ls
                                                                        ON(w.staff_id = ls.staff_id)
                                                        WHERE w.history_only = 0 ".$whereW."
                                                        GROUP BY w.staff_id)tbl2) tbl1
                                             on s.staff_id = tbl1.staff_id 
                              inner join writs w
                                       on tbl1.writ_id = w.writ_id and
                                          tbl1.writ_ver = w.writ_ver and
                                          tbl1.staff_id = w.staff_id
                                    inner join Basic_Info bi1 on bi1.typeid = 3 and w.emp_state = bi1.infoid
                                    inner join Basic_Info bi2 on bi2.typeid = 4 and w.emp_mode = bi2.infoid
                                    inner join Basic_Info bi3 on bi3.typeid = 16 and s.person_type = bi3.infoid

                        where w.emp_mode in(1,2,3,4,6,8,10,21) and s.person_type in (5) AND w.emp_state in (5)  $whereW " ; 

    $data = PdoDataAccess::runquery($query) ; 
}
else if(!empty($_GET['tarh']) &&  $_GET['tarh'] == 1 ) { 
    $title = "گزارش کل کارکنان طرح نیروی انسانی شاغل";
    $query = "  select  s.staff_id , p.pfname , p.plname , bi1.Title emp_state_title,bi2.Title emp_mode_title , bi3.Title person_type_title ,w.ouid
                    from persons p inner join staff s
                                       on p.personid = s.personid 
                              inner join (SELECT    staff_id,
                                                    SUBSTRING_INDEX(SUBSTRING(max_execute_date,11),'.',1) writ_id,
                                                    SUBSTRING_INDEX(max_execute_date,'.',-1) writ_ver
                                                FROM (SELECT w.staff_id,
                                                            max( CONCAT(w.execute_date,w.writ_id,'.',w.writ_ver) ) max_execute_date
                                                        FROM writs w
                                                                INNER JOIN staff ls
                                                                        ON(w.staff_id = ls.staff_id)
                                                        WHERE w.history_only = 0 ".$whereW."
                                                        GROUP BY w.staff_id)tbl2) tbl1
                                             on s.staff_id = tbl1.staff_id 
                              inner join writs w
                                       on tbl1.writ_id = w.writ_id and
                                          tbl1.writ_ver = w.writ_ver and
                                          tbl1.staff_id = w.staff_id
                                    inner join Basic_Info bi1 on bi1.typeid = 3 and w.emp_state = bi1.infoid
                                    inner join Basic_Info bi2 on bi2.typeid = 4 and w.emp_mode = bi2.infoid
                                    inner join Basic_Info bi3 on bi3.typeid = 16 and s.person_type = bi3.infoid

                        where w.emp_mode in(1,2,3,4,6,8,10,21) and s.person_type in (2) AND w.emp_state in (9)  $whereW " ; 

    $data = PdoDataAccess::runquery($query) ; 
}
else if(!empty($_GET['totalPersonal']) &&  $_GET['totalPersonal'] == 1 ) {   
    $title = "گزارش کل کارکنان شاغل" ; 
    $query = "  select  s.staff_id , p.pfname , p.plname , bi1.Title emp_state_title,bi2.Title emp_mode_title , bi3.Title person_type_title ,w.ouid
                    from persons p inner join staff s
                                       on p.personid = s.personid 
                              inner join (SELECT    staff_id,
                                                    SUBSTRING_INDEX(SUBSTRING(max_execute_date,11),'.',1) writ_id,
                                                    SUBSTRING_INDEX(max_execute_date,'.',-1) writ_ver
                                                FROM (SELECT w.staff_id,
                                                            max( CONCAT(w.execute_date,w.writ_id,'.',w.writ_ver) ) max_execute_date
                                                        FROM writs w
                                                                INNER JOIN staff ls
                                                                        ON(w.staff_id = ls.staff_id)
                                                        WHERE w.history_only = 0 ".$whereW."
                                                        GROUP BY w.staff_id)tbl2) tbl1
                                             on s.staff_id = tbl1.staff_id 
                              inner join writs w
                                       on tbl1.writ_id = w.writ_id and
                                          tbl1.writ_ver = w.writ_ver and
                                          tbl1.staff_id = w.staff_id
                                    inner join Basic_Info bi1 on bi1.typeid = 3 and w.emp_state = bi1.infoid
                                    inner join Basic_Info bi2 on bi2.typeid = 4 and w.emp_mode = bi2.infoid
                                    inner join Basic_Info bi3 on bi3.typeid = 16 and s.person_type = bi3.infoid

                        where w.emp_mode in(1,2,3,4,6,8,10,21) and s.person_type in (2,3,5)  $whereW " ; 

    $data = PdoDataAccess::runquery($query) ; 
}
else if(!empty($_GET['entrance']) &&  $_GET['entrance'] == 1 ) {   
    $title = "گزارش کل کارکنان ورودی به دانشگاه" ; 
    $query = "  select s.staff_id , p.pfname , p.plname , bi1.Title emp_state_title,bi2.Title emp_mode_title , bi3.Title person_type_title ,w.ouid

                    from persons p inner join staff s
                                            on p.personid = s.personid
                                     inner join (SELECT    staff_id,
                                                    SUBSTRING_INDEX(SUBSTRING(max_execute_date,11),'.',1) writ_id,
                                                    SUBSTRING_INDEX(max_execute_date,'.',-1) writ_ver
                                                FROM (SELECT w.staff_id,
                                                            max( CONCAT(w.execute_date,w.writ_id,'.',w.writ_ver) ) max_execute_date
                                                        FROM writs w
                                                                INNER JOIN staff ls
                                                                        ON(w.staff_id = ls.staff_id)
                                                        WHERE w.history_only = 0 ".$whereW."
                                                        GROUP BY w.staff_id)tbl2) tbl1
                                             on s.staff_id = tbl1.staff_id             
                                    inner join writs w
                                                on  tbl1.writ_id = w.writ_id and
                                                    tbl1.writ_ver = w.writ_ver and
                                                    tbl1.staff_id = w.staff_id
                                                    
                                    inner join Basic_Info bi1 on bi1.typeid = 3 and w.emp_state = bi1.infoid
                                    inner join Basic_Info bi2 on bi2.typeid = 4 and w.emp_mode = bi2.infoid
                                    inner join Basic_Info bi3 on bi3.typeid = 16 and s.person_type = bi3.infoid

                    where s.person_type in (2,3,5) ".$workDate ; 
   
    $data = PdoDataAccess::runquery($query) ; 
}
else if(!empty($_GET['leave']) &&  $_GET['leave'] == 1 ) {
      $title = "گزارش کل کارکنان خروجی به دانشگاه" ; 
        $query = "select  s.staff_id , p.pfname , p.plname , bi1.Title emp_state_title,bi2.Title emp_mode_title , bi3.Title person_type_title ,w.ouid

                        from persons p inner join staff s
                                                on p.personid = s.personid

                                        inner join writs w
                                                on s.last_writ_id = w.writ_id and
                                                    s.last_writ_ver = w.writ_ver and
                                                    s.staff_id = w.staff_id
                                    inner join Basic_Info bi1 on bi1.typeid = 3 and w.emp_state = bi1.infoid
                                    inner join Basic_Info bi2 on bi2.typeid = 4 and w.emp_mode = bi2.infoid
                                    inner join Basic_Info bi3 on bi3.typeid = 16 and s.person_type = bi3.infoid

                    where s.person_type in (2,3,5,10) and ( s.last_person_type is null or  s.last_person_type = 2 ) and w.emp_mode in (7,8,9,11,12,13,14,15,16,24)  $exitW " ; 
    $data = PdoDataAccess::runquery($query) ;
}
else if(!empty($_GET['razmande']) &&  $_GET['razmande'] == 1 ) {
      $title = "گزارش کل رزمندگان" ; 
    $query ="   select  s.staff_id , p.pfname , p.plname , bi1.Title emp_state_title,bi2.Title emp_mode_title , bi3.Title person_type_title ,w.ouid

                    from (

                    select count(*) cn , pd.devotion_type , pd.personid

                    from person_devotions pd
                                    inner join persons p on pd.personid = p.personid

                    where (if(devotion_type = 5 , personel_relation in (5,6) , (1=1))) AND s.person_type in (2,3,5)

                    group by devotion_type , personid

                    ) tbl1 inner join persons p 
                                    on tbl1.personid = p.personid
                            inner join staff s
                                                on p.personid = s.personid
                            inner join writs w
                                                on s.last_writ_id = w.writ_id and
                                                    s.last_writ_ver = w.writ_ver and
                                                    s.staff_id = w.staff_id
                            inner join Basic_Info bi1 on bi1.typeid = 3 and w.emp_state = bi1.infoid
                            inner join Basic_Info bi2 on bi2.typeid = 4 and w.emp_mode = bi2.infoid
                            inner join Basic_Info bi3 on bi3.typeid = 16 and s.person_type = bi3.infoid
                            where devotion_type = 1
            " ; 
    $data = PdoDataAccess::runquery($query) ;
}
else if(!empty($_GET['janbaz']) &&  $_GET['janbaz'] == 1 ) {
    $title = "گزارش کل جانبازان" ; 
    $query ="   select  s.staff_id , p.pfname , p.plname , bi1.Title emp_state_title,bi2.Title emp_mode_title , bi3.Title person_type_title ,w.ouid

                    from (

                    select count(*) cn , pd.devotion_type , pd.personid

                    from person_devotions pd
                                    inner join persons p on pd.personid = p.personid

                    where (if(devotion_type = 5 , personel_relation in (5,6) , (1=1))) AND s.person_type in (2,3,5)

                    group by devotion_type , personid

                    ) tbl1 inner join persons p 
                                    on tbl1.personid = p.personid
                            inner join staff s
                                                on p.personid = s.personid
                            inner join writs w
                                                on s.last_writ_id = w.writ_id and
                                                    s.last_writ_ver = w.writ_ver and
                                                    s.staff_id = w.staff_id
                            inner join Basic_Info bi1 on bi1.typeid = 3 and w.emp_state = bi1.infoid
                            inner join Basic_Info bi2 on bi2.typeid = 4 and w.emp_mode = bi2.infoid
                            inner join Basic_Info bi3 on bi3.typeid = 16 and s.person_type = bi3.infoid
                            where devotion_type = 3
            " ; 
    $data = PdoDataAccess::runquery($query) ;
}
else if(!empty($_GET['azade']) &&  $_GET['azade'] == 1 ) {
    $title ="گزارش کل آزادگان";
    $query ="   select  s.staff_id , p.pfname , p.plname , bi1.Title emp_state_title,bi2.Title emp_mode_title , bi3.Title person_type_title ,w.ouid

                    from (

                    select count(*) cn , pd.devotion_type , pd.personid

                    from person_devotions pd
                                    inner join persons p on pd.personid = p.personid

                    where (if(devotion_type = 5 , personel_relation in (5,6) , (1=1))) AND s.person_type in (2,3,5)

                    group by devotion_type , personid

                    ) tbl1 inner join persons p 
                                    on tbl1.personid = p.personid
                            inner join staff s
                                                on p.personid = s.personid
                            inner join writs w
                                                on s.last_writ_id = w.writ_id and
                                                    s.last_writ_ver = w.writ_ver and
                                                    s.staff_id = w.staff_id
                            inner join Basic_Info bi1 on bi1.typeid = 3 and w.emp_state = bi1.infoid
                            inner join Basic_Info bi2 on bi2.typeid = 4 and w.emp_mode = bi2.infoid
                            inner join Basic_Info bi3 on bi3.typeid = 16 and s.person_type = bi3.infoid
                            where devotion_type =2
            " ; 
    $data = PdoDataAccess::runquery($query) ;
}
else if(!empty($_GET['farzandshahid']) &&  $_GET['farzandshahid'] == 1 ) {
      $title = "گزارش کل فرزندان شهید";
    $query ="   select  s.staff_id , p.pfname , p.plname , bi1.Title emp_state_title,bi2.Title emp_mode_title , bi3.Title person_type_title ,w.ouid

                    from (

                    select count(*) cn , pd.devotion_type , pd.personid

                    from person_devotions pd
                                    inner join persons p on pd.personid = p.personid

                    where (if(devotion_type = 5 , personel_relation in (5,6) , (1=1))) AND s.person_type in (2,3,5)

                    group by devotion_type , personid

                    ) tbl1 inner join persons p 
                                    on tbl1.personid = p.personid
                            inner join staff s
                                                on p.personid = s.personid
                            inner join writs w
                                                on s.last_writ_id = w.writ_id and
                                                    s.last_writ_ver = w.writ_ver and
                                                    s.staff_id = w.staff_id
                            inner join Basic_Info bi1 on bi1.typeid = 3 and w.emp_state = bi1.infoid
                            inner join Basic_Info bi2 on bi2.typeid = 4 and w.emp_mode = bi2.infoid
                            inner join Basic_Info bi3 on bi3.typeid = 16 and s.person_type = bi3.infoid
                            where devotion_type =5
            " ; 
    $data = PdoDataAccess::runquery($query) ;
}
else if(!empty($_GET['Isargaran']) &&  $_GET['Isargaran'] == 1 ) {
    $title = "گزارش کل ایثارگران" ; 
    $query ="   select  s.staff_id , p.pfname , p.plname , bi1.Title emp_state_title,bi2.Title emp_mode_title , bi3.Title person_type_title ,w.ouid

                    from (

                    select count(*) cn , pd.devotion_type , pd.personid

                    from person_devotions pd
                                    inner join persons p on pd.personid = p.personid

                    where (if(devotion_type = 5 , personel_relation in (5,6) , (1=1))) AND s.person_type in (2,3,5)

                    group by devotion_type , personid

                    ) tbl1 inner join persons p 
                                    on tbl1.personid = p.personid
                            inner join staff s
                                                on p.personid = s.personid
                            inner join writs w
                                                on s.last_writ_id = w.writ_id and
                                                    s.last_writ_ver = w.writ_ver and
                                                    s.staff_id = w.staff_id
                            inner join Basic_Info bi1 on bi1.typeid = 3 and w.emp_state = bi1.infoid
                            inner join Basic_Info bi2 on bi2.typeid = 4 and w.emp_mode = bi2.infoid
                            inner join Basic_Info bi3 on bi3.typeid = 16 and s.person_type = bi3.infoid
                           
            " ; 
    $data = PdoDataAccess::runquery($query) ;
}
else if(!empty($_GET['freePost']) &&  $_GET['freePost'] == 1 ) {
    
     $title = "گزارش پست های سازمانی بلا تصدی" ; 
     $query =" select *
                         from position                                     
                                    where person_type in (2,3,5) and staff_id is null " ; 
     $data = PdoDataAccess::runquery($query) ;
    
}
else if(!empty($_GET['fullpost']) &&  $_GET['fullpost'] == 1 ) {
    
     $title = "گزارش پست های سازمانی با تصدی";
     $query =" select s.staff_id , p.pfname , p.plname ,bi3.Title person_type_title  , po.post_id , po.post_no , po.title ,po.ouid
                        from  staff s inner join writs w
                                            on  s.staff_id = w.staff_id and 
                                                s.last_writ_id = w.writ_id and 
                                                s.last_writ_ver = w.writ_ver
                                       inner join persons p 
                                            on p.personid = s.personid                                            
                                       inner join Basic_Info bi3 
                                            on bi3.typeid = 16 and s.person_type = bi3.infoid
                                       inner join position po 
                                            on po.post_id = w.post_id  
                            
                        where w.post_id is not null and w.person_type in (2,3,5) " ; 
     
     $data = PdoDataAccess::runquery($query) ;    
}
else if(!empty($_GET['mgi']) &&  $_GET['mgi'] == 1 ) {
    
     $title = "گزارش کارکنان دارای فوق العاده مدیریت " ; 
     $query =" select s.staff_id , p.pfname , p.plname , bi1.Title emp_state_title,bi2.Title emp_mode_title , bi3.Title person_type_title ,w.ouid
                        from staff s inner join (SELECT    staff_id,
                                                    SUBSTRING_INDEX(SUBSTRING(max_execute_date,11),'.',1) writ_id,
                                                    SUBSTRING_INDEX(max_execute_date,'.',-1) writ_ver
                                                FROM (SELECT w.staff_id,
                                                            max( CONCAT(w.execute_date,w.writ_id,'.',w.writ_ver) ) max_execute_date
                                                        FROM writs w
                                                                INNER JOIN staff ls
                                                                        ON(w.staff_id = ls.staff_id)
                                                        WHERE w.history_only = 0 ".$whereW."
                                                        GROUP BY w.staff_id)tbl2) tbl1
                                             on s.staff_id = tbl1.staff_id             
                                    inner join writs w
                                                on  tbl1.writ_id = w.writ_id and
                                                    tbl1.writ_ver = w.writ_ver and
                                                    tbl1.staff_id = w.staff_id
                                                    
                                    inner join Basic_Info bi1 on bi1.typeid = 3 and w.emp_state = bi1.infoid
                                    inner join Basic_Info bi2 on bi2.typeid = 4 and w.emp_mode = bi2.infoid
                                    inner join Basic_Info bi3 on bi3.typeid = 16 and s.person_type = bi3.infoid
                                    inner join writ_salary_items wsi
                                                        on  w.staff_id = wsi.staff_id and
                                                            w.writ_id = wsi.writ_id and w.writ_ver = wsi.writ_ver
                                    inner join  persons p on p.personid = s.personid 

                                  where wsi.salary_item_type_id = 35  ". $whereW ;
     $data = PdoDataAccess::runquery($query) ;
    
}
else if(!empty($_GET['After30']) &&  $_GET['After30'] == 1 ) {
    
     $title = "گزارش کارکنان با سابقه خدمتی بیش از سی سال" ; 
     $query =" select s.staff_id , p.pfname , p.plname , bi1.Title emp_state_title,bi2.Title emp_mode_title , bi3.Title person_type_title ,w.ouid

                    from staff s inner join (SELECT    staff_id,
                                                    SUBSTRING_INDEX(SUBSTRING(max_execute_date,11),'.',1) writ_id,
                                                    SUBSTRING_INDEX(max_execute_date,'.',-1) writ_ver
                                                FROM (SELECT w.staff_id,
                                                            max( CONCAT(w.execute_date,w.writ_id,'.',w.writ_ver) ) max_execute_date
                                                        FROM writs w
                                                                INNER JOIN staff ls
                                                                        ON(w.staff_id = ls.staff_id)
                                                        WHERE w.history_only = 0 ".$whereW."
                                                        GROUP BY w.staff_id)tbl2) tbl1
                                             on s.staff_id = tbl1.staff_id             
                                    inner join writs w
                                                on  tbl1.writ_id = w.writ_id and
                                                    tbl1.writ_ver = w.writ_ver and
                                                    tbl1.staff_id = w.staff_id
                                    inner join Basic_Info bi1 on bi1.typeid = 3 and w.emp_state = bi1.infoid
                                    inner join Basic_Info bi2 on bi2.typeid = 4 and w.emp_mode = bi2.infoid
                                    inner join Basic_Info bi3 on bi3.typeid = 16 and s.person_type = bi3.infoid
                                    inner join persons p on s.personid = p.personid

                    where w.onduty_year >= 30 and s.person_type in (2,3,5) and
                        w.emp_mode <> 13 " ;
    
     $data = PdoDataAccess::runquery($query) ;
    
}
else if(!empty($_GET['Before30']) &&  $_GET['Before30'] == 1 ) {
    
     $title = "گزارش کارکنان با سابقه خدمت   بیش از 25 سال و کمتر از 30 سال " ; 
     $query =" select s.staff_id , p.pfname , p.plname , bi1.Title emp_state_title,bi2.Title emp_mode_title , bi3.Title person_type_title ,w.ouid

                    from staff s inner join (SELECT    staff_id,
                                                    SUBSTRING_INDEX(SUBSTRING(max_execute_date,11),'.',1) writ_id,
                                                    SUBSTRING_INDEX(max_execute_date,'.',-1) writ_ver
                                                FROM (SELECT w.staff_id,
                                                            max( CONCAT(w.execute_date,w.writ_id,'.',w.writ_ver) ) max_execute_date
                                                        FROM writs w
                                                                INNER JOIN staff ls
                                                                        ON(w.staff_id = ls.staff_id)
                                                        WHERE w.history_only = 0 ".$whereW."
                                                        GROUP BY w.staff_id)tbl2) tbl1
                                             on s.staff_id = tbl1.staff_id             
                                    inner join writs w
                                                on  tbl1.writ_id = w.writ_id and
                                                    tbl1.writ_ver = w.writ_ver and
                                                    tbl1.staff_id = w.staff_id
                                    inner join Basic_Info bi1 on bi1.typeid = 3 and w.emp_state = bi1.infoid
                                    inner join Basic_Info bi2 on bi2.typeid = 4 and w.emp_mode = bi2.infoid
                                    inner join Basic_Info bi3 on bi3.typeid = 16 and s.person_type = bi3.infoid
                                    inner join persons p on s.personid = p.personid

                    where w.onduty_year >= 25 and w.onduty_year < 30   and s.person_type in (2,3,5) and   w.emp_mode <> 13" ; 
    
     $data = PdoDataAccess::runquery($query) ;
    
}
else if((!empty($_GET['item24']) &&  $_GET['item24'] == 1 ) || 
        (!empty($_GET['item25']) &&  $_GET['item25'] == 1 ) || 
        (!empty($_GET['item26']) &&  $_GET['item26'] == 1 ) ||
        (!empty($_GET['item27']) &&  $_GET['item27'] == 1 ) ||
        (!empty($_GET['item28']) &&  $_GET['item28'] == 1 ) ||
        (!empty($_GET['item35']) &&  $_GET['item35'] == 1 )) {
    
    if(!empty($_GET['item24']) &&  $_GET['item24'] == 1 ){
        $title = "گزارش کل کارکنان مرد با سابقه خدمتی بیش از 20 سال و کمتر از 25 سال" ; 
        $whr = " w.onduty_year >= 20 and w.onduty_year < 25   and s.person_type in (2,3,5) and   w.emp_mode <> 13 and p.sex = 1 " ; 
        
        }
    
    if(!empty($_GET['item25']) &&  $_GET['item25'] == 1 ){
        $title = "گزارش کل کارکنان زن با سابقه خدمتی بیش از 20 سال و کمتر از 25 سال" ; 
        $whr = " w.onduty_year >= 20 and w.onduty_year < 25   and s.person_type in (2,3,5) and   w.emp_mode <> 13 and p.sex = 2 " ; 
        
        }
        
    if(!empty($_GET['item26']) &&  $_GET['item26'] == 1 ){
        $title = "گزارش کل کارکنان با سابقه خدمتی کمتر از 20 سال ";
        $whr = "  w.onduty_year < 20  and s.person_type in (2,3,5) and   w.emp_mode <> 13 " ; 
        
        }
    
    if(!empty($_GET['item27']) &&  $_GET['item27'] == 1 ){
        $title = "گزارش کل کارکنان مرد" ; 
        $whr = "  s.person_type in (2,3,5) and p.sex = 1" ; 
        
        }
        
    if(!empty($_GET['item28']) &&  $_GET['item28'] == 1 ){
        $title = "گزارش کل کارکنان زن";
        $whr = "  s.person_type in (2,3,5) and p.sex = 2" ; 
        
        }
        
     if(!empty($_GET['item35']) &&  $_GET['item35'] == 1 ){
        $title = "گزارش کارکنان زن سرپرست خانواده" ; 
        $whr = "  s.person_type in (2,3,5) and family_protector = 1 and p.sex = 2 " ; 
        
        }
    
     
     $query =" select s.staff_id , p.pfname , p.plname , bi1.Title emp_state_title,bi2.Title emp_mode_title , bi3.Title person_type_title ,w.ouid
                        from staff s inner join (SELECT    staff_id,
                                                    SUBSTRING_INDEX(SUBSTRING(max_execute_date,11),'.',1) writ_id,
                                                    SUBSTRING_INDEX(max_execute_date,'.',-1) writ_ver
                                                FROM (SELECT w.staff_id,
                                                            max( CONCAT(w.execute_date,w.writ_id,'.',w.writ_ver) ) max_execute_date
                                                        FROM writs w
                                                                INNER JOIN staff ls
                                                                        ON(w.staff_id = ls.staff_id)
                                                        WHERE w.history_only = 0 ".$whereW."
                                                        GROUP BY w.staff_id)tbl2) tbl1
                                             on s.staff_id = tbl1.staff_id             
                                    inner join writs w
                                                on  tbl1.writ_id = w.writ_id and
                                                    tbl1.writ_ver = w.writ_ver and
                                                    tbl1.staff_id = w.staff_id
                                                
                                     inner join persons p on s.personid = p.personid
                                     inner join Basic_Info bi1 on bi1.typeid = 3 and w.emp_state = bi1.infoid
                                     inner join Basic_Info bi2 on bi2.typeid = 4 and w.emp_mode = bi2.infoid
                                     inner join Basic_Info bi3 on bi3.typeid = 16 and s.person_type = bi3.infoid


                        where  ".$whr ; 
    
     $data = PdoDataAccess::runquery($query) ;
    
}
else if((!empty($_GET['item29']) &&  $_GET['item29'] == 1 ) || 
        (!empty($_GET['item30']) &&  $_GET['item30'] == 1) ||
        (!empty($_GET['item31']) &&  $_GET['item31'] == 1) ||
        (!empty($_GET['item32']) &&  $_GET['item32'] == 1) ||
        (!empty($_GET['item33']) &&  $_GET['item33'] == 1) ||
        (!empty($_GET['item34']) &&  $_GET['item34'] == 1)){
    
    if(!empty($_GET['item29']) &&  $_GET['item29'] == 1){
         $title = "گزارش کارکنان شاغل دارای مدرک تحصیلی زیردیپلم";
         $whr = " and bi4.MasterID = 1 " ; 
    }
     else if(!empty($_GET['item30']) &&  $_GET['item30'] == 1){
         $title = "گزارش کارکنان شاغل دارای مدرک تحصیلی دیپلم";
         $whr = " and bi4.MasterID = 2 " ; 
    }
    else if(!empty($_GET['item31']) &&  $_GET['item31'] == 1){
         $title = "گزارش کارکنان شاغل دارای مدرک تحصیلی کاردانی";
         $whr = " and bi4.MasterID = 3 " ; 
    }
     else if(!empty($_GET['item32']) &&  $_GET['item32'] == 1){
         $title = "گزارش کارکنان شاغل دارای مدرک تحصیلی کارشناسی";
         $whr = " and bi4.MasterID = 4 " ; 
    }
    else if(!empty($_GET['item33']) &&  $_GET['item33'] == 1){
         $title = "گزارش کارکنان شاغل دارای مدرک تحصیلی کارشناسی ارشد";
         $whr = " and bi4.MasterID = 5 " ; 
    }
    else if(!empty($_GET['item34']) &&  $_GET['item34'] == 1){
         $title = "گزارش کارکنان شاغل دارای مدرک تحصیلی دکتری تخصصی";
         $whr = " and bi4.MasterID = 6 " ; 
    }
     
      $query = " select bi4.MasterID  ,  s.staff_id , p.pfname , p.plname , bi1.Title emp_state_title,bi2.Title emp_mode_title , bi3.Title person_type_title ,w.ouid
                        from persons p inner join staff s
                                                on p.personid = s.personid 
                                        inner join (SELECT    staff_id,
                                                    SUBSTRING_INDEX(SUBSTRING(max_execute_date,11),'.',1) writ_id,
                                                    SUBSTRING_INDEX(max_execute_date,'.',-1) writ_ver
                                                FROM (SELECT w.staff_id,
                                                            max( CONCAT(w.execute_date,w.writ_id,'.',w.writ_ver) ) max_execute_date
                                                        FROM writs w
                                                                INNER JOIN staff ls
                                                                        ON(w.staff_id = ls.staff_id)
                                                        WHERE w.history_only = 0 ".$whereW."
                                                        GROUP BY w.staff_id)tbl2) tbl1
                                             on s.staff_id = tbl1.staff_id             
                                    inner join writs w
                                                on  tbl1.writ_id = w.writ_id and
                                                    tbl1.writ_ver = w.writ_ver and
                                                    tbl1.staff_id = w.staff_id                                                    
                                        
                                     inner join Basic_Info bi1 on bi1.typeid = 3 and w.emp_state = bi1.infoid
                                     inner join Basic_Info bi2 on bi2.typeid = 4 and w.emp_mode = bi2.infoid
                                     inner join Basic_Info bi3 on bi3.typeid = 16 and s.person_type = bi3.infoid
                                     
                                        inner join Basic_Info bi4 on w.education_level = bi4.InfoID and  bi4.typeid = 6
                                        inner join Basic_Info bi5 on bi4.MasterID = bi5.InfoID and  bi5.typeid = 35

                            where s.person_type in (2,3,5) and w.emp_mode in (1,2,3,4,6,8,10,21) $whr

                             " ; 
      
      $data = PdoDataAccess::runquery($query) ;
      
}
else if((!empty($_GET['bedoonHoghogh']) &&  $_GET['bedoonHoghogh'] == 1 ) || 
        (!empty($_GET['stelagi']) &&  $_GET['stelagi'] == 1 ) ||
         (!empty($_GET['mamoorBe']) &&  $_GET['mamoorBe'] == 1 ) ||
         (!empty($_GET['mamoorAz']) &&  $_GET['mamoorAz'] == 1 ) ){
    
    if(!empty($_GET['bedoonHoghogh']) &&  $_GET['bedoonHoghogh'] == 1){
         $title = "گزارش کارکنان مرخصی بدون حقوق";
         $whr = " and  w.emp_mode = 3 " ; 
    }
    else if(!empty($_GET['stelagi']) &&  $_GET['stelagi'] == 1){
         $title = "گزارش کارکنان مرخصی استعلاجی";
         $whr = " and w.emp_mode = 21 " ; 
    }
    else if(!empty($_GET['mamoorBe']) &&  $_GET['mamoorBe'] == 1){
         $title = "گزارش کارکنان  مامور به سازمان دیگر";
         $whr = " and w.emp_mode = 6 " ; 
    }
    else if(!empty($_GET['mamoorAz']) &&  $_GET['mamoorAz'] == 1){
         $title = "گزارش کارکنان  مامور از سازمان دیگر" ;
         $whr = " and w.emp_mode = 16 " ; 
    }
    
    $query = " select   s.staff_id , p.pfname , p.plname , bi1.Title emp_state_title,bi2.Title emp_mode_title , bi3.Title person_type_title ,w.ouid ,w.emp_mode

                   from staff s  inner join (SELECT    staff_id,
                                                    SUBSTRING_INDEX(SUBSTRING(max_execute_date,11),'.',1) writ_id,
                                                    SUBSTRING_INDEX(max_execute_date,'.',-1) writ_ver
                                                FROM (SELECT w.staff_id,
                                                            max( CONCAT(w.execute_date,w.writ_id,'.',w.writ_ver) ) max_execute_date
                                                        FROM writs w
                                                                INNER JOIN staff ls
                                                                        ON(w.staff_id = ls.staff_id)
                                                        WHERE w.history_only = 0 ".$whereW."
                                                        GROUP BY w.staff_id)tbl2) tbl1
                                             on s.staff_id = tbl1.staff_id             
                                    inner join writs w
                                             on  tbl1.writ_id = w.writ_id and
                                                 tbl1.writ_ver = w.writ_ver and
                                                 tbl1.staff_id = w.staff_id
                                    inner join persons p on p.personid = s.personid
                                    inner join Basic_Info bi1 on bi1.typeid = 3 and w.emp_state = bi1.infoid
                                    inner join Basic_Info bi2 on bi2.typeid = 4 and w.emp_mode = bi2.infoid
                                    inner join Basic_Info bi3 on bi3.typeid = 16 and s.person_type = bi3.infoid
                                                    
                                where w.emp_mode in (3,21,6,16) ". $whereW.$whr   ; 
             
        $data = PdoDataAccess::runquery($query);
}
if((!empty($_GET['item36']) &&  $_GET['item36'] == 1 ))
    {
    
        $query = " select  s.staff_id , p.pfname , p.plname , bi1.Title emp_state_title,bi2.Title emp_mode_title , bi3.Title person_type_title ,w.ouid ,w.emp_mode
            
                            from staff s inner join (SELECT    staff_id,
                                                    SUBSTRING_INDEX(SUBSTRING(max_execute_date,11),'.',1) writ_id,
                                                    SUBSTRING_INDEX(max_execute_date,'.',-1) writ_ver
                                                                    FROM (SELECT w.staff_id,
                                                                                max( CONCAT(w.execute_date,w.writ_id,'.',w.writ_ver) ) max_execute_date
                                                                            FROM writs w
                                                                                    INNER JOIN staff ls
                                                                                            ON(w.staff_id = ls.staff_id)
                                                                            WHERE w.history_only = 0 ".$whereW."
                                                                            GROUP BY w.staff_id)tbl2) tbl1
                                                                on s.staff_id = tbl1.staff_id   
                                         inner join writs w
                                                    on  tbl1.writ_id = w.writ_id and
                                                        tbl1.writ_ver = w.writ_ver and
                                                        tbl1.staff_id = w.staff_id      

                                        left join payments pa 
                                                    on pa.staff_id = s.staff_id and pa.payment_type = 1 and pa.pay_year = ".substr(DateModules::miladi_to_shamsi($salaryDate),0,4)." and
                                                                                                          pa.pay_month = ".substr(DateModules::miladi_to_shamsi($salaryDate),5,2)."
                                        
                                        inner join persons p on p.personid = s.personid
                                        inner join Basic_Info bi1 on bi1.typeid = 3 and w.emp_state = bi1.infoid
                                        inner join Basic_Info bi2 on bi2.typeid = 4 and w.emp_mode = bi2.infoid
                                        inner join Basic_Info bi3 on bi3.typeid = 16 and s.person_type = bi3.infoid

                    where s.person_type in (2,3,5) and pa.staff_id is null ".$whereW."
                   
                    " ; 
         
         $data = PdoDataAccess::runquery($query);
    
    }

?>
<html dir='rtl'>
	<head>
		<style>
		.reportGenerator {border-collapse: collapse;border: 1px solid black;font-family: tahoma;font-size: 8pt;
			text-align: center;width: 70%;padding: 2px;}
		.reportGenerator .header {color: white;font-weight: bold;background-color:#3F5F96}
		.reportGenerator td {border: 1px solid #555555;height: 20px;}
		</style>
		<title>تعداد کل کارکنان رسمی قطعی شاغل </title>
		<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
		<link rel=stylesheet href="/HumanResources/css/writ.css">
	</head>
	<body>
		<center>
			<table width="70%" cellpadding="0" cellspacing="0">
				<tr class="header" >
					<td width="20%"><img src="/HumanResources/img/fum_symbol.jpg" ></td>
					<td align="center" style="font-family:b titr;font-size: 9pt;font-weight: bold;"><?=$title?></td>
					<td width="20%" align="left" style="font-family:tahoma;font-size:8pt">تاریخ :  <?= DateModules::shNow()?></td>
				</tr>
			</table>
			<table style="text-align: right;" class="reportGenerator" cellpadding="4" cellspacing="0">
			
				<?
                                if((!empty($_GET['freePost']) &&  $_GET['freePost'] == 1) || 
                                   (!empty($_GET['fullpost']) &&  $_GET['fullpost'] == 1) ){
                                    
                                    echo "<tr class='header' >
                                            <td>شماره شناسایی پست</td>
                                            <td>شماره پست</td>
                                            <td>عنوان پست</td><td>محل پست</td>" ; 
                                    
                                    if((!empty($_GET['fullpost']) &&  $_GET['fullpost'] == 1)){
                                        
                                        echo "<td>شماره شناسایی</td>
                                            <td>
                                               نام و نام خانوادگی دارنده پست
                                              </td>
                                              <td>
                                              نوع فرد
                                              </td>" ; 
                                        
                                    }
                                    
                                    echo "</tr>" ; 
                                     for($i=0; $i< count($data) ; $i++)
                                        {                                         
                                         echo "<tr> 
                                                <td>".$data[$i]['post_id']."</td><td>".$data[$i]['post_no']."</td><td>".$data[$i]['title']."</td>
                                                <td>".manage_units::get_full_title($data[$i]['ouid'])."</td>";
                                          if((!empty($_GET['fullpost']) &&  $_GET['fullpost'] == 1)){
                                        
                                        echo "<td>".$data[$i]['staff_id']."</td>
                                              <td>".$data[$i]['pfname']."  ".$data[$i]['plname']."</td>
                                              <td>".$data[$i]['person_type_title']."</td>" ; 
                                        
                                    }
                                         echo "</tr>";

                                        }
                                    
                                }
                                else {  ?>
                                    <tr class="header">					
					<td width="10%">شماره شناسایی</td>
                                        <td>نام</td>
                                        <td>نام خانوادگی</td>
                                        <td>عنوان کامل محل خدمت</td>
                                        <td>وضعیت استخدامی</td>
                                        <td>حالت استخدامی</td>
                                        <td>نوع فرد</td>
				</tr>
                                    <?
                                        for($i=0; $i< count($data) ; $i++)
                                        {
                                            echo "<tr>
                                                    <td>".$data[$i]['staff_id']."</td><td>".$data[$i]['pfname']."</td><td>".$data[$i]['plname']."</td>
                                                    <td>".manage_units::get_full_title($data[$i]['ouid'])."</td><td>".$data[$i]['emp_state_title']."</td>
                                                    <td>".$data[$i]['emp_mode_title']."</td><td>".$data[$i]['person_type_title']."</td>
                                                </tr>";                                     
                                        }
                                    
                                }
                                
                                
                                ?>							
			</table>
			
		</center>
	</body>
</html>
