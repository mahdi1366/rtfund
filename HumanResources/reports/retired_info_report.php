<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	92.05
//---------------------------
require_once("../header.inc.php");

if (isset($_GET['showRes']) && $_GET['showRes'] == 1) {

   //....شماره شناسایی..........
    if (!empty($_POST['SID'])) {
        $where.= " AND  s.staff_id = " . $_POST['SID'];
    }  
    
    if (!empty($_POST['PersonType'])) {
        $where.= " AND  s.person_type = " . $_POST['PersonType'];
    } 
        
    $qry = " select p.pfname , p.plname , p.PersonID , s.staff_id ,
                    case p.sex when 1 then 'مرد' when 2 then 'زن' end sex ,
                    p.father_name , p.national_code , p.idcard_no , 
                    case p.issue_state_id  when 35 then 'خراسان رضوي' else st.ptitle end issue_state_id ,
                    case p.issue_state_id  when 35 then 'مشهد' else c.ptitle end issue_city_id , 
                    g2j(p.birth_date) birth_date ,s.personel_no ,
                    bi.Title education_level , sf.ptitle sfid , 
                    case p.military_type when  12 then 'پايان خدمت' when 13 then 'معافيت'  end  military_type , 
                    jc.title jcid , 
                    case po.title 
                        when (po.post_id is null AND s.person_type = 1 ) then 'عضوهيات علمي' 
                        else po.title 
                        end post_title ,
                    jf.title jfid ,
                    bi5.title science_title ,(w.base) tabghe , 
                    case p.marital_status 
                        when 1 then 'مجرد'
                        when 2 then 'متاهل'
                        when 3 then 'معيل'
                        when 4 then 'معيل'
                    end marital_status ,
                    w.children_count ,
                    case w.emp_state 
                        when 2 then 'پيماني'
                        when 3 then 'آزمايشي' 
                        when 4 then 'رسمي'
                        when 11 then 'پيماني' else '-' end person_type , 'شاغل' /*bi4.Title*/ emp_mode ,
                    /*g2j(s.ProfWorkStart) ProfWorkStart ,
                    replace(replace(replace(replace(tbl3.Isar, '1','رزمنده'), '2','آزاده'), '3','جانباز'), '5','خانواده شهيد') isar , */
                    bin.title personel_relation ,
                    p.military_from_date , p.military_to_date , p.military_duration   ,bi6.title gradeTitle ,
                    tbl4.min_execute_date  min_execute_date , s.last_retired_pay last_retired_pay ,
                    g2j(w.execute_date) execute_date , concat(wt.title ,'-',wst.title ) writ_type_id ,                    
                    max(case when pit.salary_item_type_id in ( 1 , 10364 ) then (pit.pay_value + diff_pay_value) else 0 end) as 'hoghoogh',
                    max(case when pit.salary_item_type_id in ( 6,10367 ) then (pit.pay_value + diff_pay_value) else 0 end)  as 'fogh-Makhsoos',
                    max(case when pit.salary_item_type_id in ( 11,10368,10376) then (pit.pay_value + diff_pay_value) else 0 end)  as 'tatbigh',
                    max(case when pit.salary_item_type_id in (20,10375) then (pit.pay_value + diff_pay_value) else 0 end)  as 'karBaAshaeh',
                    max(case when pit.salary_item_type_id in ( 22 , 10366) then (pit.pay_value + diff_pay_value) else 0 end)  as 'fogh-jazb',
                    max(case when pit.salary_item_type_id = 33 then (pit.pay_value + diff_pay_value) else 0 end)  as 'isargari',
                    max(case when pit.salary_item_type_id = 44 then (pit.pay_value + diff_pay_value) else 0 end)  as 'jazbOmana',
                    max(case when pit.salary_item_type_id in(186 , 10365) then (pit.pay_value + diff_pay_value) else 0 end)  as 'Vizeh',  
                    max(case when pit.salary_item_type_id in (149,150) then pit.get_value else 0 end)  as 'get_value',  0 Maghamat ,
                    max(case when pit.salary_item_type_id in (149,150) then (pit.param3) else 0 end)  as 'param3',  
                    max(case when pit.salary_item_type_id in (149,150) then pit.diff_get_value else 0 end)  as 'diff_get_value',  
                    max(case when pit.salary_item_type_id in (149,150) then (pit.diff_param3) else 0 end)  as 'diff_param3',                      
                    max(case when ( pit.salary_item_type_id in (149,150) and s.last_retired_pay is not null ) then pit.param3 else 0 end)  as 'kosoorMazad',                      
                    max(case when pit.salary_item_type_id in( 9911,9915) then (pit.get_value) else 0 end)  as 'Mogharari',  
                    max(case when pit.salary_item_type_id in (242 ,399) then (pit.get_value) else 0 end)  as 'AghsatkosoorMazad',                     
                    max(case when pit.salary_item_type_id in (10373,10377) then (pit.pay_value + diff_pay_value) else 0 end)  as 'Modiriat', 
                    max(case when pit.salary_item_type_id in (10369) then (pit.pay_value + diff_pay_value) else 0 end)  as 'sakhtiKar',
                    tbl1.sval, tbl1.moavagheKosoor , '' jameMashmoolKosor , '' karmand , '' karfarma
                    
     from persons p
            inner join staff s 
                on p.personid = s.personid and p.person_type = s.person_type
            left join payments pa on s.staff_id = pa.staff_id and pa.pay_year = ".$_POST['pay_year']." AND 
                                     pa.pay_month = ".$_POST['pay_month']." and pa.payment_type = 1 
            inner join writs w on pa.staff_id = w.staff_id and pa.writ_id = w.writ_id and  pa.writ_ver = w.writ_ver        
            left join position po 
                on po.post_id = w.post_id
            left join job_fields jf 
                on po.jfid = jf.jfid
            left join job_category jc 
                on jc.jcid = jf.jcid
            left join study_fields sf 
                on sf.sfid = w.sfid
            left join (   select staff_id,sum(pay_value + diff_pay_value) sval , sum(diff_pay_value) moavagheKosoor 
                          from payment_items pit1 inner join salary_item_types sit2
                                                on pit1.salary_item_type_id = sit2.salary_item_type_id and 
                                                   sit2.retired_include = 1 
                          where  pay_year = ".$_POST['pay_year']." and 
                                 pay_month =".$_POST['pay_month']." and payment_type = 1 
                          group by staff_id ) tbl1                       
                on s.staff_id = tbl1.staff_id
                
            left join payment_items pit 
                on s.staff_id = pit.staff_id AND pit.pay_year = ".$_POST['pay_year']." AND
                   pit.pay_month = ".$_POST['pay_month']." AND pit.payment_type = 1

           /* left join ( select PersonID,GROUP_CONCAT( devotion_type SEPARATOR '-') as Isar
                                    from ( select PersonID ,devotion_type , personel_relation
                                            from person_devotions
                                                group by PersonID,devotion_type , personel_relation ) tbl2
                                group by personid ) tbl3
                on p.personid = tbl3.personid*/

            left join person_devotions pd 
                on pd.personid = p.personid and pd.devotion_type = 5
            left join Basic_Info bin 
                on bin.InfoID = pd.personel_relation and typeid = 1
            inner join writ_types wt 
                on w.writ_type_id = wt.writ_type_id and wt.person_type = w.person_type
            inner join writ_subtypes wst
                                    on wst.writ_type_id = wt.writ_type_id and wst.writ_subtype_id = w.writ_subtype_id and
                                       wst.person_type = w.person_type

            left join (
                        SELECT w.staff_id, min(w.execute_date) min_execute_date
                        FROM writs w INNER JOIN staff ls ON(w.staff_id = ls.staff_id)
                        WHERE w.history_only = 0 AND w.emp_state = 3
                        GROUP BY w.staff_id
                      ) tbl4 on tbl4.staff_id = s.staff_id

            left join states st on st.state_id = p.issue_state_id
            left join cities c on c.state_id = st.state_id and c.city_id = p.issue_city_id
            left join Basic_Info bi on bi.infoid = w.education_level and bi.typeid = 6
            /*left join Basic_Info bi2 on bi2.infoid = p.military_type and bi2.typeid = 10
            left join Basic_Info bi3 on bi3.infoid = p.marital_status and bi3.typeid = 15*/
            left join Basic_Info bi4 on bi4.infoid = w.emp_mode and bi4.typeid = 4
            left join Basic_Info bi5 on bi5.infoid = w.science_level and bi5.typeid = 8
            left join Basic_Info bi6 on bi6.infoid = w.grade and bi6.typeid = 44
            inner join ( select staff_id 
                            from payment_items 
                                where pay_year = ".$_POST['pay_year']." and pay_month = ".$_POST['pay_month']." and
                                      payment_type = 1 and salary_item_type_id in (149,150) ) tbl5 
                                      on tbl5.staff_id = s.staff_id 
            where  w.cost_center_id <> 46 AND  w.emp_state in (2,3,4,11) ".$where." 

            group by s.staff_id   ";

    $temp = PdoDataAccess::runquery($qry);
        
if($_SESSION['UserID'] == 'jafarkhani'){
     // echo PdoDataAccess::GetLatestQueryString(); die();  
}
    for ($i = 0; $i < count($temp); $i++) {
        
        $total_year = 0;
        $total_month = 0;
        $total_day = 0;
        $total_not_rasmi_year = 0;
        $total_not_rasmi_month = 0;
        $total_not_rasmi_day = 0;
        $year = $month = $day = 0;
        $sumDays = 0; 

        $query = " select min(execute_date) min_execute_date , staff_id 
					from writs 
						where staff_id =" . $temp[$i]['staff_id'] . " and emp_state in (2,3,4,11) and history_only = 0 ";

        $tmp = PdoDataAccess::runquery($query);
                
        $qry = " select w.writ_id , w.writ_ver , w.execute_date , w.annual_effect  
                 from writs w inner join  writ_types wt 						                    
                        on w.writ_type_id = wt.writ_type_id and w.person_type = wt.person_type 
                    inner join writ_subtypes wst 
                        on  wst.writ_type_id = w.writ_type_id and
                            wst.writ_subtype_id = w.writ_subtype_id and 
                            wst.person_type = w.person_type  

                where execute_date >= '" . $tmp[0]['min_execute_date'] . "' and  history_only =0 and  
                    staff_id =" . $temp[$i]['staff_id'] . " 
                order by w.execute_date ";

        $valid_rasmi_writs = PdoDataAccess::runquery($qry);
 
        if( $_POST['pay_month'] < 7 )
            $CurrentDate = $_POST['pay_year'].'/'.$_POST['pay_month']."/31"; 
        else if($_POST['pay_month'] > 6 && $_POST['pay_month'] < 12 )
            $CurrentDate = $_POST['pay_year'].'/'.$_POST['pay_month']."/31"; 
        else if ( $_POST['pay_month'] == 12)
            $CurrentDate = $_POST['pay_year'].'/'.$_POST['pay_month']."/29"; 
        
        $CurrentDate = DateModules::shamsi_to_miladi($CurrentDate); 


        for ($k = 0; $k < count($valid_rasmi_writs); $k++) {
            $first_date = $valid_rasmi_writs[$k]["execute_date"];
            $last_date = ($k + 1 < count($valid_rasmi_writs)) ? $valid_rasmi_writs[$k + 1]["execute_date"] : $CurrentDate ;

            $diff = strtotime($last_date) - strtotime($first_date);
            $diff = floor($diff / (60 * 60 * 24));

            $year = (floor($diff / 365));
            $month = (floor(($diff - floor($diff / 365) * 365 ) / 30));
            $day = floor($diff - floor($diff / 365) * 365 - floor(($diff - floor($diff / 365) * 365 ) / 30) * 30);

            if ($valid_writs[$k]["annual_effect"] != "3") {             

                $total_year += $year ;
                $total_month += $month;
                $total_day += $day;
                               
            }

                if($last_date == $CurrentDate)
                break;

        }
        


        $qry = " select writ_id , writ_ver , execute_date , w.annual_effect  
                    from writs w inner join  writ_types wt 						                    
                                    on w.writ_type_id = wt.writ_type_id and w.person_type = wt.person_type 

                                    inner join writ_subtypes wst 
                                    on  wst.writ_type_id = w.writ_type_id and
                                        wst.writ_subtype_id = w.writ_subtype_id and 
                                        wst.person_type = w.person_type   

                    where execute_date < '" . $tmp[0]['min_execute_date'] . "' and  history_only =0 and 
                                staff_id =" . $temp[$i]['staff_id'] . " 

                    order by execute_date ";

        $valid_not_rasmi_writs = PdoDataAccess::runquery($qry);

        for ($k = 0; $k < count($valid_not_rasmi_writs); $k++) {
            $first_date = $valid_not_rasmi_writs[$k]["execute_date"];
            $last_date = ($k + 1 < count($valid_not_rasmi_writs)) ? $valid_not_rasmi_writs[$k + 1]["execute_date"] : $tmp[0]['min_execute_date'];

            $diff = strtotime($last_date) - strtotime($first_date);
            $diff = floor($diff / (60 * 60 * 24));

            $year = (floor($diff / 365));
            $month = (floor(($diff - floor($diff / 365) * 365 ) / 30));
            $day = floor($diff - floor($diff / 365) * 365 - floor(($diff - floor($diff / 365) * 365 ) / 30) * 30);

            if ($valid_writs[$k]["annual_effect"] != "3") {
                $total_not_rasmi_year += $year;
                $total_not_rasmi_month += $month;
                $total_not_rasmi_day += $day;
                
            }
        }

        //..........................................................................
        $total_not_rasmi_year += floor($temp[$i]['military_duration'] / 12);
        $total_not_rasmi_month += ($temp[$i]['military_duration'] - (floor($temp[$i]['military_duration'] / 12) * 12));
        $total_not_rasmi_day += 0;
             
        //..........................................................................
        require_once $address_prefix . "/HumanResources/personal/persons/class/employment.class.php";
        //	$temp[$i]['PersonID'] = '100000' ; 
        $empRes = manage_person_employment::GetAllEmp("PersonID=" . $temp[$i]['PersonID']);

        for ($j = 0; $j < count($empRes); $j++) {
            if ($empRes[$j]["retired_duration_year"] != 0 || $empRes[$j]["retired_duration_month"] != 0 ||
                    $empRes[$j]["retired_duration_day"] != 0) {
                if ($empRes[$j]["emp_state"] == 3 || $empRes[$j]["emp_state"] == 4) {

                    $total_year += $empRes[$j]["retired_duration_year"];
                    $total_month += $empRes[$j]["retired_duration_month"];
                    $total_day += $empRes[$j]["retired_duration_day"];
                   
                } /*else {

                    $total_not_rasmi_year += $empRes[$j]["retired_duration_year"];
                    $total_not_rasmi_month += $empRes[$j]["retired_duration_month"];
                    $total_not_rasmi_day += $empRes[$j]["retired_duration_day"];
                }*/
            }
        }

        $total = ($total_year) * 365 + ($total_month) * 30 + ($total_day);
        $y = (int) ($total / 365);
        $m = (int) (($total - $y * 365) / 30);
        $d = round(($total - $y * 365 - $m * 30));


        $temp[$i]["s1"] = str_pad($y, 2, "0", STR_PAD_LEFT) . "" . str_pad($m, 2, "0", STR_PAD_LEFT) . "" . str_pad($d, 2, "0", STR_PAD_LEFT);
 
        $total_not_rasmi = ($total_not_rasmi_year) * 365 + ($total_not_rasmi_month) * 30 + ($total_not_rasmi_day);
        
               
        //........................................چنانچه سابقه خدمت کمتر از سی باشد بایستی در سنوات غیر رسمی دیده شود..............................
         if( $temp[$i]['last_retired_pay']!="" && $temp[$i]['last_retired_pay']!='0000-00-00') {
             $temp[$i]['last_retired_pay'] = DateModules::miladi_to_shamsi($temp[$i]['last_retired_pay']); 
       
        $sumDays1 = round($total + $total_not_rasmi) ;  
        if( ( 30 * 365 ) > $sumDays1 )  {

            $Diff_To_Now = DateModules::getDateDiff($CurrentDate, $temp[$i]['last_retired_pay']) + 1 ;
            $diffSubDays = ( 30 * 365 ) + 2 - $sumDays1 ; 
            $total_not_rasmi += ( $diffSubDays + $Diff_To_Now ) ; 

        }
            
        }

        if( $temp[$i]['last_retired_pay']=='0000-00-00' || DateModules::shamsi_to_miladi($temp[$i]['last_retired_pay']) >= $CurrentDate  )
            $temp[$i]['last_retired_pay'] = "" ; 
        //......................................................................
            
        $ny = (int) ($total_not_rasmi / 365);
        $nm = (int) (($total_not_rasmi - $ny * 365) / 30);
        $nd = round(($total_not_rasmi - $ny * 365 - $nm * 30));
 
        //..........................................................................
        $temp[$i]["s2"] = str_pad($ny, 2, "0", STR_PAD_LEFT) . "" . str_pad($nm, 2, "0", STR_PAD_LEFT) . "" . str_pad($nd, 2, "0", STR_PAD_LEFT);
        $temp[$i]["s2"] = ( $temp[$i]["s2"] == '000000' ) ? "" : $temp[$i]["s2"] ; 
        
        $temp[$i]["total"] = $temp[$i]['get_value'] +  $temp[$i]['param3'] + $temp[$i]['AghsatkosoorMazad'] + 
                             $temp[$i]['diff_param3'] + $temp[$i]['diff_get_value']; 
         
                  
            //.......................... بدست آوردن تاریخ شروع ...................



        $sumDays = round($total + $total_not_rasmi) ;             
        $sumDayst = " - $sumDays days " ; 
        $mynewdate = date_sub($CurrentDate,date_interval_create_from_date_string("40 days"));
        
        $sy = (int) ($sumDays / 365);
        $sm = (int) (($sumDays - $sy * 365) / 30);
        $sd = round(($sumDays - $sy * 365 - $sm * 30));

        $time = strtotime($CurrentDate."-".$sy." years -".$sm." months -".$sd."days");
        $startdate = date("Y-m-d", $time); 
        $temp[$i]['ProfWorkStart'] = DateModules::miladi_to_shamsi($startdate) ; 

 if($_SESSION['UserID'] == 'jafarkhani' ){
//echo $temp[$i]['ProfWorkStart'].'----' ; die();
 }
//...........................................................................
                
        $qry = " select PersonID ,devotion_type , personel_relation
                 from person_devotions
                 where personid = ".$temp[$i]['PersonID']."
                 group by PersonID,devotion_type , personel_relation "; 
        $resIsar = PdoDataAccess::runquery($qry); 
        
        $IsarTitle = "";
        
        for($k=0;$k<count($resIsar);$k++)
        {   
            
            if($resIsar[$k]['devotion_type'] == 3 )
            {
                $IsarTitle = "جانباز"." - ";
            }
            elseif($resIsar[$k]['devotion_type'] == 1 )
            {
                $IsarTitle = "رزمنده"." - ";
            }
            elseif($resIsar[$k]['devotion_type'] == 2 )
            {
                $IsarTitle = "آزاده"." - ";
            }
            
            if($resIsar[$k]['devotion_type'] == 5 && $resIsar[$k]['personel_relation'] == 4 ) 
            {
               $temp[$i]['personel_relation'] = "همسر شهيد"; 
            }
            elseif($resIsar[$k]['devotion_type'] == 5 && $resIsar[$k]['personel_relation'] == 2 ) 
            {
               $temp[$i]['personel_relation'] = "پدر شهيد"; 
            }
            elseif($resIsar[$k]['devotion_type'] == 5 && $resIsar[$k]['personel_relation'] == 3 ) 
            {
               $temp[$i]['personel_relation'] = "مادر شهيد"; 
            }
            elseif($resIsar[$k]['devotion_type'] == 5 && ( $resIsar[$k]['personel_relation'] == 5 ||  $resIsar[$k]['personel_relation'] == 6 )) 
            {
               $temp[$i]['personel_relation'] = "فرزند شهيد"; 
            }
            elseif($resIsar[$k]['devotion_type'] == 5 && $resIsar[$k]['personel_relation'] == 8 ) 
            {
               $temp[$i]['personel_relation'] = "خواهر شهيد"; 
            }
            elseif($resIsar[$k]['devotion_type'] == 5 && $resIsar[$k]['personel_relation'] == 7 ) 
            {
               $temp[$i]['personel_relation'] = "برادر شهيد"; 
            }
            
        }
       
        $IsarTitle = substr($IsarTitle,0, -3 ) ;   
        $temp[$i]['isar'] = $IsarTitle; 
        
       
        
        if( $temp[$i]['min_execute_date']!="" && $temp[$i]['min_execute_date']!='0000-00-00')
            $temp[$i]['min_execute_date'] = DateModules::miladi_to_shamsi($temp[$i]['min_execute_date']);
             
        
    }
        
        $item17 = ($_POST['PersonType'] == 1 ) ? "مرتبه علمی" : "رتبه" ; 
        $item32 = ($_POST['PersonType'] == 1 ) ? "حقوق ماهانه" : "حقوق پایه و مرتبه" ; 
        $item36 = ($_POST['PersonType'] == 1 ) ?   " فو ق العاده سختی شرایط محیط کار " :        "فوق العاده مدیریت"  ;
        $item38 = ($_POST['PersonType'] == 1 ) ? "فوق العاده کار با اشعه" : "ما به التفاوت مقامات" ;        
        $item39 = ($_POST['PersonType'] == 1 ) ? " " : "<td>فوق العاده سختی کار</td><td>فوق العاده کار با اشعه</td>" ;
        
              
//..............................................................................

    if ($_GET['excel'] == 'true') {
        ini_set("display_errors", "On");
        require_once 'excel.php';
        require_once "php_writeexcel-0.3.0/class.writeexcel_workbook.inc.php";
        require_once "php_writeexcel-0.3.0/class.writeexcel_worksheet.inc.php";

        $workbook = &new writeexcel_workbook("/tmp/temp.xls");
        $worksheet = & $workbook->addworksheet("Sheet1");
        $heading = & $workbook->addformat(array('align' => 'center', 'bold' => 1, 'bg_color' => 'blue', 'color' => 'white'));

        $worksheet->write(0, 0, "ردیف", $heading);
        if( $_POST['PersonType'] == 1 )
            $header = array("نام ",
                                                                                "نام خانوادگی" ,
                                                                                "جنسیت",
                                                                                "نام پدر",
                                                                                "شماره ملی",
                                                                                "شماره شناسنامه",
                                                                                "استان محل صدور",
                                                                                "شهر محل صدور",
                                                                                "تاریخ تولد",
                                                                                "شماره مستخدم",
                                                                                "مدرک تحصیلی",
                                                                                "رشته تحصیلی",
                                                                                "وضعیت نظام وظیفه",
                                                                                "رسته شغلی",
                                                                                "پست سازمانی",
                                                                                "رشته شغلی",
                                                                                "مرتبه هیئت علمی ",
                                                                                "پایه",
                                                                                "وضعیت تاهل",
                                                                                "تعداد فرزندان",
                                                                                "نوع استخدام",
                                                                                "حالت استخدام",
                                                                                "تاریخ استخدام",
                                                                                "وضعیت ایثارگری",
                                                                                "نسبت با ایثارگر",
                                                                                "تاریخ تغییر صندوق",
                                                                                "سنوات خدمت رسمی",
                                                                                "سنوات خدمت غیررسمی",
                                                                                "تاریخ معافیت از کسور (مازاد بر سی سال)",
                                                                                "تاريخ اجراء حکم ",
                                                                                "نوع حکم",
                                                                                "حقوق ماهانه",
                                                                                "فوق العاده مخصوص",
    "فوق العاده جذب" ,
                "فوق العااده ویژه",
                "فو ق العاده سختی شرایط محیط کار",
                                                                                    "تفاوت تطبیق",
                                                                            "فوق العاده کار با اشعه",
                "جمع حقوق و مزایای مشمول کسور(جاری)",

                "سهم کارمند(جاری)",
                                                                                "سهم کارفرما (جاری)",
                                                                                "جمع معوقه مشمول کسور",
                                                                                "سهم کارمند(معوقه)",
                                                                                "سهم کارفرما (معوقه)",
                "جمع مشمول کسور (سنوات قبل)",
                "سهم کارمند (سنوات قبل)",
                                                                                "سهم کارفرما (سنوات قبل)",
                                                                                "مقرری ماه اول",
                                                                                "کسور مازاد بر سی سال",
                                                                                "اقساط  کسور خدمت غیر رسمی ",
                                                                                "جمع کل کسوربازنشستگی");

        else 
            $header = array("نام ",
                                                                                "نام خانوادگی" ,
                                                                                "جنسیت",
                                                                                "نام پدر",
                                                                                "شماره ملی",
                                                                                "شماره شناسنامه",
                                                                                "استان محل صدور",
                                                                                "شهر محل صدور",
                                                                                "تاریخ تولد",
                                                                                "شماره مستخدم",
                                                                                "مدرک تحصیلی",
                                                                                "رشته تحصیلی",
                                                                                "وضعیت نظام وظیفه",
                                                                                "رسته شغلی",
                                                                                "پست سازمانی",
                                                                                "رشته شغلی",
                                                                                " رتبه",
                                                                                "پایه",
                                                                                "وضعیت تاهل",
                                                                                "تعداد فرزندان",
                                                                                "نوع استخدام",
                                                                                "حالت استخدام",
                                                                                "تاریخ استخدام",
                                                                                "وضعیت ایثارگری",
                                                                                "نسبت با ایثارگر",
                                                                                "تاریخ تغییر صندوق",
                                                                                "سنوات خدمت رسمی",
                                                                                "سنوات خدمت غیررسمی",
                                                                                "تاریخ معافیت از کسور (مازاد بر سی سال)",
                                                                                "تاريخ اجراء حکم ",
                                                                                "نوع حکم",
                                                                                "حقوق پایه و مرتبه  ",
                                                                                "فوق العاده مخصوص",
    "فوق العاده جذب" ,
                "فوق العااده ویژه",
 "فوق العاده مدیریت",
                                                                                    "تفاوت تطبیق",
"مابه التفاوت مقامات",
"فو ق العاده سختی کار",
                                                                            "فوق العاده کار با اشعه",
                "جمع حقوق و مزایای مشمول کسور(جاری)",
                "سهم کارمند(جاری)",
                                                                                "سهم کارفرما (جاری)",
                                                                                "جمع معوقه مشمول کسور",
                                                                                "سهم کارمند(معوقه)",
                                                                                "سهم کارفرما (معوقه)",
                "جمع مشمول کسور (سنوات قبل)",
                "سهم کارمند (سنوات قبل)",
                                                                                "سهم کارفرما (سنوات قبل)",
                                                                                "مقرری ماه اول",
                                                                                "کسور مازاد بر سی سال",
                                                                                "اقساط  کسور خدمت غیر رسمی ",
                                                                                "جمع کل کسوربازنشستگی");

                                                                            
                                                                        
        for ($i = 0; $i < count($header); $i++) {
            $worksheet->write(0, $i + 1, $header[$i], $heading);
        }
  if( $_POST['PersonType'] == 1 )
        $content = array("pfname", "plname", "sex", "father_name", "national_code", "idcard_no", "issue_state_id", "issue_city_id", "birth_date",
                         "personel_no", "education_level", "sfid", "military_type", "science_title", "post_title", "jfid", "science_title", "tabghe", 
                         "marital_status", "children_count", "person_type", "emp_mode", "ProfWorkStart", "isar", "personel_relation", "min_execute_date", 
                         "s1", "s2", "last_retired_pay", "execute_date", "writ_type_id", "hoghoogh", "fogh-Makhsoos","fogh-jazb", 
                         "Vizeh", "sakhtiKar", "tatbigh","karBaAshaeh", "sval", "get_value", "param3", "moavagheKosoor", "diff_get_value", "diff_param3", 
                         "jameMashmoolKosor", "karmand", "karfarma", "Mogharari", "kosoorMazad", "AghsatkosoorMazad", "total");
  else 
       $content = array("pfname", "plname", "sex", "father_name", "national_code", "idcard_no", "issue_state_id", "issue_city_id", "birth_date",
                         "personel_no", "education_level", "sfid", "military_type", "jcid", "post_title", "jfid", "gradeTitle", "tabghe", 
                         "marital_status", "children_count", "person_type", "emp_mode", "ProfWorkStart", "isar", "personel_relation", "min_execute_date", 
                         "s1", "s2", "last_retired_pay", "execute_date", "writ_type_id", "hoghoogh", "fogh-Makhsoos","fogh-jazb", 
                         "Vizeh", "Modiriat", "tatbigh","Maghamat","sakhtiKar","karBaAshaeh", "sval", "get_value", "param3", "moavagheKosoor", "diff_get_value", "diff_param3", 
                         "jameMashmoolKosor", "karmand", "karfarma", "Mogharari", "kosoorMazad", "AghsatkosoorMazad", "total");

        for ($index = 0; $index < count($temp); $index++) {
                    
            $row = $temp[$index];

            $worksheet->write($index + 1, 0, ($index + 1));

            for ($i = 0; $i < count($content); $i++) {
                $val = "";
                                           
                $val = $row[$content[$i]];
                
                if($_POST['PersonType'] == 1 && $content[$i] == "jfid" )    
                     $val = "مدرس";
                
                if($_POST['PersonType'] == 1 && $content[$i] == "sakhtiKar")
                    $val = "0";
                
                if($_POST['PersonType'] == 2 && $content[$i] == "Maghamat")
                    $val = "0";
                    
                
                $val = ( is_int($val) ) ? round($val) : $val;
                $worksheet->write($index + 1, $i + 1, $val);
            }
        }

        $workbook->close();

        header("Content-type: application/ms-excel");
        header("Content-disposition: inline; filename=excel.xls");

        echo file_get_contents("/tmp/temp.xls");
        unlink("/tmp/temp.xls");
        die();
    }

     //echo PdoDataAccess::GetLatestQueryString() ; die() ; 
    ?>

    <html dir='rtl'>
        <head>
            <style>
                .reportGenerator {border-collapse: collapse;border: 1px solid black;font-family: tahoma;font-size: 8pt;
                                  text-align: center;width: 80%;padding: 2px;}
                .reportGenerator .header {color: white;font-weight: bold;background-color:#3F5F96}
                .reportGenerator td {border: 1px solid #555555;height: 20px;}
            </style>
            <title>گزارش اطلاعات جامع سازمان بازنشستگی</title>
            <meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
            <link rel=stylesheet href="/HumanResources/css/writ.css">
        </head>
        <body>
        <center>
            <table width="50%" cellpadding="0" cellspacing="0">
                <tr>
                    <td width="20%"><img src="/HumanResources/img/fum_symbol.jpg" ></td>
                    <td align="center" style="font-family:'b titr'">گزارش  اطلاعات جامع سازمان بازنشستگی </td>
                    <td width="20%" align="left" style="font-family:tahoma;font-size:8pt">تاریخ : <?= DateModules::shNow() ?></td>
                </tr>
            </table>

            <table style="text-align: right;" class="reportGenerator" cellpadding="4" cellspacing="0" >
                <tr class="header">	
                    <td>نام</td>
                    <td>نام خانوادگی</td>
                    <td>جنسيت</td>
                    <td>نام پدر</td>
                    <td>شماره ملي</td>
                    <td>شماره شناسنامه</td>
                    <td>استان محل صدور</td>
                    <td>شهر محل صدور</td>
                    <td>تاريخ تولد</td>
                    <td>شماره مستخدم</td>
                    <td>مدرک تحصيلي</td>
                    <td>رشته تحصيلي</td>
                    <td>وضعيت نظام وظيفه</td>
                    <td>رسته شغلی</td>				
                    <td>پست سازمانی</td>
                    <td>رشته شغلي</td>
                    <td><?= $item17?> </td>                 
                    <td>پایه</td>
                    <td>وضعيت تاهل</td>
                    <td>تعداد فرزندان</td>
                    <td>نوع استخدام</td>
                    <td>حالت استخدام</td>
                    <td>تاريخ استخدام</td>
                    <td>وضعيت ايثارگري</td>
                    <td>نسبت با ايثارگر</td>
                    <td>تاريخ تغییر صندوق</td>
                    <td>سنوات خدمت رسمی</td>
                    <td>سنوات خدمت غیر رسمی</td>
                    <td>تاریخ معافیت از کسور (مازاد بر سی سال)</td>
                    <td>تاريخ اجراء حکم </td>
                    <td>نوع حکم</td>
                    <td><?= $item32?></td>                 
                    <td>فوق العاده مخصوص/شغل</td>       
                    <td>فوق العاده جذب</td>               
                    <td>فوق العااده ویژه</td>
                    <td><?= $item36?></td>    
                    <td>تفاوت تطبیق</td>
                    <td><?= $item38?></td>   
                    <?=$item39?>
                    <td>جمع حقوق و مزایای مشمول کسور(جاری)</td>										
                    <td>سهم کارمند(جاری)</td>
                    <td>سهم کارفرما (جاری)</td>
                    <td>جمع معوقه مشمول کسور</td>
                    <td>سهم کارمند(معوقه)</td>
                    <td>سهم کارفرما (معوقه)</td>
                    <td>جمع مشمول کسور (سنوات قبل)</td>
                    <td>سهم کارمند (سنوات قبل)</td>
                    <td>سهم کارفرما (سنوات قبل)</td>
                    <td>مقرری ماه اول</td>
                    <td>کسور مازاد بر سی سال</td>
                    <td>اقساط  کسور خدمت غیر رسمی </td>
                    <td>جمع کل کسوربازنشستگی</td>							

                </tr>		

    <?
    for ($i = 0; $i < count($temp); $i++) {

        echo "	<tr>												
                <td>" . $temp[$i]["pfname"] . "</td>
                <td>" . $temp[$i]["plname"] . "</td>
                <td>" . $temp[$i]["sex"] . "</td>
                <td>" . $temp[$i]["father_name"] . "</td>
                <td>" . $temp[$i]["national_code"] . "</td>
                <td>" . $temp[$i]["idcard_no"] . "</td>
                <td>" . $temp[$i]["issue_state_id"] . "</td>
                <td>" . $temp[$i]["issue_city_id"] . "</td>
                <td>" . $temp[$i]["birth_date"] . "</td>
                <td>" . $temp[$i]["personel_no"] . "</td>
                <td>" . $temp[$i]["education_level"] . "</td>
                <td>" . $temp[$i]["sfid"] . "</td>
                <td>" . $temp[$i]["military_type"] . "</td> ";
        
        if($_POST['PersonType'] == 1 )    
            echo "<td>" . $temp[$i]["science_title"] . "</td>";
        else 
            echo "<td>" . $temp[$i]["jcid"] . "</td>";
        
        echo  "<td>" . $temp[$i]["post_title"] . "</td> ";
         
        if($_POST['PersonType'] == 1 )    
            echo "<td>مدرس</td>";
        else 
            echo "<td>" . $temp[$i]["jfid"] . "</td> " ; 
        
        if($_POST['PersonType'] == 1 )    
            echo "<td>" . $temp[$i]["science_title"] . "</td>";
        else 
            echo "<td>" . $temp[$i]["gradeTitle"] . "</td>";

        echo   "<td>" . $temp[$i]["tabghe"] . "</td>
                <td>" . $temp[$i]["marital_status"] . "</td>
                <td>" . $temp[$i]["children_count"] . "</td>
                <td>" . $temp[$i]["person_type"] . "</td>
                <td>" . $temp[$i]["emp_mode"] . "</td>
                <td>" . $temp[$i]["ProfWorkStart"] . "</td>
                <td>" . $temp[$i]["isar"] . "</td>
                <td>" . $temp[$i]["personel_relation"] . "</td>
                <td>" . $temp[$i]["min_execute_date"] . "</td>
                <td>" . $temp[$i]["s1"] . "</td>
                <td>" . $temp[$i]["s2"] . "</td>
                <td>" . $temp[$i]["last_retired_pay"] . "</td>
                <td>" . $temp[$i]["execute_date"] . "</td>
                <td>" . $temp[$i]["writ_type_id"] . "</td>
                <td>" . $temp[$i]["hoghoogh"] . "</td>
                <td>" . $temp[$i]["fogh-Makhsoos"] . "</td>											                                        
                <td>" . $temp[$i]["fogh-jazb"] . "</td>                                       
                <td>" . $temp[$i]["Vizeh"] . "</td>";
        if($_POST['PersonType'] == 1 )    
            echo "<td>0</td> ";
        else 
            echo "<td>".$temp[$i]["Modiriat"]."</td>";
       
        echo   " <td>" . $temp[$i]["tatbigh"] . "</td> " ;
        
         if($_POST['PersonType'] == 1 )    
            echo "<td>" . $temp[$i]["karBaAshaeh"] . "</td> ";
        else 
            echo "<td>0</td><td>" . $temp[$i]["sakhtiKar"] . "</td><td>" . $temp[$i]["karBaAshaeh"] . "</td>";
       
        echo   "                                                                                                      
                <td>" . $temp[$i]["sval"] . "</td>
                <td>" . round($temp[$i]["get_value"]) . "</td>
                <td>" . round($temp[$i]["param3"]) . "</td>
                <td>" . round($temp[$i]["moavagheKosoor"]) . "</td>
                <td>" . round($temp[$i]["diff_get_value"]) . "</td>
                <td>" . round($temp[$i]["diff_param3"]) . "</td>
                <td>" . $temp[$i]["jameMashmoolKosor"] . "</td>
                <td>" . $temp[$i]["karmand"] . "</td>
                <td>" . $temp[$i]["karfarma"] . "</td>
                <td>" . $temp[$i]["Mogharari"] . "</td>
                <td>" . round($temp[$i]["kosoorMazad"]) . "</td>
                <td>" . $temp[$i]["AghsatkosoorMazad"] . "</td>
                <td>" . round($temp[$i]["total"]) . "</td>

        </tr>
        ";
    }
    ?>							
            </table>
        </center>
    </body>
    </html>

                <?
                die();
            }
            require_once 'retired_info_report.js.php';
            ?>

<form id="form_SearchPost" >
    <center>
         <div id="mainpanel"></div>
    </center>
</form>