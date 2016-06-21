<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	91.04
//---------------------------

$fromDate = "" ; $workDate ="" ; 
if(!empty($_POST['FDATE:72'])) {
    $whereDate = " AND  tbl2.execute_date>='".DateModules::shamsi_to_miladi($_POST['FDATE:72'])."'" ;  
    $whereW = " AND w.execute_date >= '".DateModules::shamsi_to_miladi($_POST['FDATE:72'])."'  " ; 
   // $workDate = " AND s.work_start_date >= '".DateModules::shamsi_to_miladi($_POST['FDATE:72'])."'" ;     
    $fromDate = $_POST['FDATE:72'] ; 
    }
if(!empty($_POST['TDATE:72']) ) {
    $whereDate .= " AND tbl2.execute_date<='".DateModules::shamsi_to_miladi($_POST['TDATE:72'])."'" ;  
    $whereW .= " AND w.execute_date <= '".DateModules::shamsi_to_miladi($_POST['TDATE:72'])."'" ; 
    $salaryDate =  DateModules::shamsi_to_miladi($_POST['TDATE:72']) ;
    $toDate = $_POST['TDATE:72'] ; 
    }
else {
     $salaryDate =  DateModules::Now() ; 
     $toDate = DateModules::shNow() ;
}    
    
if(!empty($_POST['TDATE:72']) ) {    
    $workDate .= " AND s.work_start_date <= '".DateModules::shamsi_to_miladi($_POST['TDATE:72'])."'" ;     
    $exitW = " AND w.execute_date <= '".DateModules::shamsi_to_miladi($_POST['TDATE:72'])."'" ; 
}    
else 
{
    $workDate .= " AND s.work_start_date <= '".DateModules::Now()."'" ;  
    $exitW = " AND w.execute_date <= '".DateModules::Now()."'" ;  
}
        
$query = "        
        
select count(*) cn , w.emp_state , s.person_type
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

            where w.emp_mode in(1,2,3,4,6,8,10,16,21) and s.person_type in (2,3,5) ".$whereW."

            group by s.person_type , w.emp_state ";


$data = PdoDataAccess::runquery($query) ; 
 
$total = 0 ; 
$rasmi_ghatee_count = 0 ; 
$peymani_count = 0 ; 
$rasmi_azmayeshi_count =0 ;
$tarh_count=0 ;
$roozmozd_count =0 ; 
$gharardadi_count = 0 ; 

for($i=0 ; $i< count($data) ; $i++)
{
	$total += $data[$i]['cn']; 
        
        if( $data[$i]['person_type']== 2 && ($data[$i]['emp_state'] == 1 || $data[$i]['emp_state'] == 2  || $data[$i]['emp_state'] == 10 ))
            $peymani_count += $data[$i]['cn']; 
        
        if( $data[$i]['person_type']== 2 && ($data[$i]['emp_state'] == 4 ))
            $rasmi_ghatee_count += $data[$i]['cn']; 
        
        if( $data[$i]['person_type']== 2 && $data[$i]['emp_state'] == 3 )
            $rasmi_azmayeshi_count += $data[$i]['cn']; 
        
        if( $data[$i]['emp_state'] == 9 )
            $tarh_count += $data[$i]['cn']; 
        
        if( $data[$i]['person_type']== 3 && $data[$i]['emp_state'] == 8 )
            $roozmozd_count += $data[$i]['cn']; 
        
        if( $data[$i]['person_type']== 5 && $data[$i]['emp_state'] == 5 )
            $gharardadi_count += $data[$i]['cn']; 
                
}

        $query = "  select count(*) cn

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

                    where s.person_type in (2,3,5) ".$workDate."
                    "; 
     
        unset($data); 
        $data = PdoDataAccess::runquery($query); 
        
        $entrance_employee = $data[0]['cn'];   
        
        $query = "select count(*) cn

                    from persons p inner join staff s
                                            on p.personid = s.personid

                                    inner join writs w
                                            on s.last_writ_id = w.writ_id and
                                                s.last_writ_ver = w.writ_ver and
                                                s.staff_id = w.staff_id

                where s.person_type in (2,3,5,10) and ( s.last_person_type is null or  s.last_person_type = 2 ) and w.emp_mode in (7,8,9,11,12,13,14,15,16,24)  $exitW 

                " ; 
        $data = PdoDataAccess::runquery($query);         
        $leave_employee = $data[0]['cn'] ; 
        
        $query = "               
                    select count(*) devCount , devotion_type

                    from (

                    select count(*) cn , pd.devotion_type , pd.personid

                    from person_devotions pd
                                    inner join persons p on pd.personid = p.personid

                    where (if(devotion_type = 5 , personel_relation in (5,6) , (1=1))) AND s.person_type in (2,3,5)

                    group by devotion_type , personid

                    ) tbl1

                    group by devotion_type
                " ; 
        $devdata = PdoDataAccess::runquery($query);   
        $RazmandeCnt = 0  ; 
        $JanbazCnt = 0 ; 
        $AzadeCnt = 0 ; 
        $FarzandShahid = 0 ; 
        $totalDev = 0 ; 
        
        for($i=0 ; $i < count($devdata) ; $i++ )
        {
           if($devdata[$i]['devotion_type'] == 1 ) {
               $RazmandeCnt = $devdata[$i]['devCount'] ;
               $totalDev += $RazmandeCnt ;                
               }
           elseif($devdata[$i]['devotion_type'] == 3){               
               $JanbazCnt = $devdata[$i]['devCount'] ;
               $totalDev += $JanbazCnt ;               
               } 
           elseif($devdata[$i]['devotion_type'] == 2){
               $AzadeCnt  = $devdata[$i]['devCount'] ;
               $totalDev += $AzadeCnt ; 
               } 
           elseif($devdata[$i]['devotion_type'] ==5 ){
               $FarzandShahid  = $devdata[$i]['devCount'] ; 
               $totalDev += $FarzandShahid ; 
               }
            
        }
        
        $query = " select *
                         from position 
                                    where person_type in (2,3,5) and staff_id is null" ; 
        $FreePost = PdoDataAccess::runquery($query);
        
        $query = " select count(*) cn
                        from  staff s inner join writs w
                                            on s.staff_id = w.staff_id and s.last_writ_id = w.writ_id and s.last_writ_ver = w.writ_ver

                                        where w.post_id is not null and w.person_type in (2,3,5) " ; 
        
        $FullPost = PdoDataAccess::runquery($query);
        
        $query = " select count(*) cn
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
                                        inner join writ_salary_items wsi
                                                        on  w.staff_id = wsi.staff_id and
                                                            w.writ_id = wsi.writ_id and w.writ_ver = wsi.writ_ver

                                  where wsi.salary_item_type_id = 35  ". $whereW ;
        $ManagerItem = PdoDataAccess::runquery($query);
        
        $query = " select count(*) cn ,w.emp_mode

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
                                                    
                                where w.emp_mode in (3,21,6,16) ". $whereW ."
                   group by w.emp_mode
                " ; 
        $res = PdoDataAccess::runquery($query);
        $bedoonHoghogh = 0  ; 
        $stelagi = 0 ; 
        $mamoorBe = 0 ; 
        $mamoorAz = 0 ; 
        
        for($i=0 ; $i< count($res) ; $i++ )
        {
            if($res[$i]['emp_mode'] == 3 )
                $bedoonHoghogh += $res[$i]['cn'] ; 
            else if($res[$i]['emp_mode'] == 21 )
                $stelagi += $res[$i]['cn'] ; 
            else if($res[$i]['emp_mode'] == 6 )
                $mamoorBe += $res[$i]['cn'] ; 
            else if($res[$i]['emp_mode'] == 16 )
                $mamoorAz += $res[$i]['cn'] ; 

        }
        
        $query = " select count(*) cn

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

                    where w.onduty_year >= 30 and s.person_type in (2,3,5) and
                        w.emp_mode <> 13 ";
        
        $After30 = PdoDataAccess::runquery($query);
        
        $query = "select count(*) cn

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


                    where w.onduty_year >= 25 and w.onduty_year < 30   and s.person_type in (2,3,5) and   w.emp_mode <> 13" ; 
        
        $Before30 = PdoDataAccess::runquery($query);
        
        $query = " select  p.sex , count(*) cn
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


                        where w.onduty_year >= 20 and w.onduty_year < 25   and s.person_type in (2,3,5) and   w.emp_mode <> 13
                        group by p.sex " ; 
        
         $duty = PdoDataAccess::runquery($query);
         
         $query = " select  count(*) cn
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


                        where  w.onduty_year < 20  and s.person_type in (2,3,5) and   w.emp_mode <> 13
                  " ; 
        
         $duty_under_20 = PdoDataAccess::runquery($query);
         
         $query = " select p.sex , count(*) cn
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

                    where s.person_type in (2,3,5) AND w.emp_mode in(1,2,3,4,6,8,10,16,21)

                    group by p.sex
                    order by  p.sex

                    " ;
		 		 
          $seprate_sex = PdoDataAccess::runquery($query);
          
          $query = " select bi1.MasterID  , count(*) cn
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

                                        inner join Basic_Info bi1 on w.education_level = bi1.InfoID and  bi1.typeid = 6
                                        inner join Basic_Info bi2 on bi1.MasterID = bi2.InfoID and  bi2.typeid = 35

                            where s.person_type in (2,3,5) and w.emp_mode in (1,2,3,4,6,8,10,16,21)

                            group by bi1.MasterID

                            " ; 
          $educ_res = PdoDataAccess::runquery($query);
          
          $zireDeplom = 0 ; 
          $diplom = 0 ; 
          $kardani = 0 ; 
          $karshenasi = 0 ; 
          $karshenasiArshad = 0 ; 
          $doctora = 0 ; 
          
          for($i=0 ; $i < count($educ_res) ; $i++ )
          {
              if($educ_res[$i]['MasterID'] == 1 ) 
                  $zireDeplom += $educ_res[$i]['cn'];
              
              else if($educ_res[$i]['MasterID'] == 2 ) 
                  $diplom += $educ_res[$i]['cn'];
              
              else if($educ_res[$i]['MasterID'] == 3 ) 
                  $kardani += $educ_res[$i]['cn'];
                
              else if($educ_res[$i]['MasterID'] == 4 ) 
                  $karshenasi += $educ_res[$i]['cn'];
                 
              else if($educ_res[$i]['MasterID'] == 5 ) 
                  $karshenasiArshad += $educ_res[$i]['cn'];
                  
              else if($educ_res[$i]['MasterID'] == 6 ) 
                  $doctora += $educ_res[$i]['cn'];
              
          }  
          
          $query = "select  count(*) cn
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

                        where s.person_type in (2,3,5)  and family_protector = 1 and p.sex = 2  ".$whereW ; 
          
          $family_protector_res = PdoDataAccess::runquery($query);
          
          $query = " select count(*) cn 
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

                                        left join payments p 
                                                    on p.staff_id = s.staff_id and p.payment_type = 1 and pay_year = ".substr(DateModules::miladi_to_shamsi($salaryDate),0,4)." and
                                                                                                          pay_month = ".substr(DateModules::miladi_to_shamsi($salaryDate),5,2)."

                    where s.person_type in (2,3,5) and p.staff_id is null ".$whereW."
                        
                    order by  w.emp_mode
                    " ; 
          
        $notGetSalary = PdoDataAccess::runquery($query);
        
	$tags =  array('<!--total_personal_count-->' => $total ,
                       '<!--rasmi_ghatee_count-->' => $rasmi_ghatee_count , 
                       '<!--rasmi_azmayeshi_count-->' => $rasmi_azmayeshi_count ,
                       '<!--peymani_count-->' => $peymani_count ,
                       '<!--roozmozd_count-->' => $roozmozd_count ,
                       '<!--gharardadi_count-->' => $gharardadi_count ,
                       '<!--tarh_count-->' => $tarh_count ,
                       '<!--entrance_employee-->' => $entrance_employee ,
                       '<!--leave_employee-->' => $leave_employee ,
                       '<!--RazmandeCnt-->' => $RazmandeCnt , 
                       '<!--JanbazCnt-->' => $JanbazCnt , 
                       '<!--AzadeCnt-->' => $AzadeCnt , 
                       '<!--FarzandShahid-->' => $FarzandShahid ,
                       '<!--totalDev-->' => $totalDev ,
                       '<!--freePost-->' => count($FreePost) ,
                       '<!--FullPost-->' => $FullPost[0]['cn'] ,
                       '<!--ManagerItem-->' => $ManagerItem[0]['cn'],
                       '<!--bedoonHoghogh-->' => $bedoonHoghogh , 
                       '<!--stelagi-->' => $stelagi ,
                       '<!--mamoorBe-->' => $mamoorBe ,
                       '<!--mamoorAz-->' => $mamoorAz , 
                       '<!--After30-->' => $After30[0]['cn'] , 
                       '<!--Before30-->' => $Before30[0]['cn'] , 
                       '<!--fromDate-->' => $fromDate, 
                       '<!--toDate-->' => $toDate, 
                       '<!--item24-->' => $duty[0]['cn'] , 
                       '<!--item25-->' => $duty[1]['cn'] ,
                       '<!--item26-->' => $duty_under_20[0]['cn'] , 
                       '<!--item27-->' => $seprate_sex[0]['cn'] , 
                       '<!--item28-->' => $seprate_sex[1]['cn'] , 
                       '<!--item29-->' => $zireDeplom ,
                       '<!--item30-->' => $diplom ,
                       '<!--item31-->' => $kardani ,
                       '<!--item32-->' => $karshenasi ,
                       '<!--item33-->' => $karshenasiArshad ,
                       '<!--item34-->' => $doctora ,
                       '<!--item35-->' => $family_protector_res[0]['cn'] , 
                       '<!--item36-->' => $notGetSalary[0]['cn'],
                       '<!--item37-->' => substr(DateModules::miladi_to_shamsi($salaryDate),5,2)
            
                        );

	$content = file_get_contents("../../reports/statistical_Report_Format.htm");
	$content = str_replace(array_keys($tags), array_values($tags), $content);
	echo $content;
?>
