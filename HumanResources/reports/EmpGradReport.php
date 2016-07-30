<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	94.02
//---------------------------
require_once("../header.inc.php");
//require_once '../personal/writs/class/writ.class.php';
require_once '../salary/salary_params/class/salary_params.class.php';

require_once 'EmpGradReport.js.php';


function duty_year_month_day($staff_id = "", $personID = "", $toDate) {
    if ($staff_id == "" && $personID = "") {
        PdoDataAccess::PushException("يکي از دو پارامتر staff_id و PersonID بايد فرستاده شود");
        return false;                
    }
    $query = "select w.execute_date,
						w.contract_start_date ,
						w.contract_end_date ,
						w.person_type ,
						w.onduty_year ,
						w.onduty_month ,
						w.onduty_day ,
						w.annual_effect
			from writs as w";

    if ($personID != "")
        $query .= " join staff as s using(staff_id) where s.PersonID=" . $personID;

    else if ($staff_id != "")
        $query .= " where w.staff_id = $staff_id";

    $query .= " AND (w.history_only != " . HISTORY_ONLY . " OR w.history_only is null) AND w.execute_date <= '$toDate'
						order by w.execute_date DESC,w.writ_id DESC,w.writ_ver DESC
						limit 1";

    $temp = PdoDataAccess::runquery($query);

    if (count($temp) == 0)
        return array("year" => 0, "month" => 0, "day" => 0);

    $writ_rec = $temp[0];

    $temp_duration = 0;

    if (DateModules::CompareDate($toDate, $writ_rec['execute_date']) >= 0)
        $temp_duration = DateModules::GDateMinusGDate($toDate, $writ_rec['execute_date']);

    if ($writ_rec['annual_effect'] == HALF_COMPUTED)
        $temp_duration *= 0.5;
    else if ($writ_rec['annual_effect'] == DOUBLE_COMPUTED)
        $temp_duration *= 2;
    else if ($writ_rec['annual_effect'] == NOT_COMPUTED)
        $temp_duration = 0;

    $prev_writ_duration = DateModules::ymd_to_days($writ_rec['onduty_year'], $writ_rec['onduty_month'], $writ_rec['onduty_day']);

    $duration = $prev_writ_duration + $temp_duration;
    
       
    $return = array();
    DateModules::day_to_ymd($duration, $return['year'], $return['month'], $return['day']);

    return $return;
}

if (isset($_GET['showRes']) && $_GET['showRes'] == 1) {

    $EndDate = DateModules::shamsi_to_miladi($_POST['ToDate']);
    
    //................. برای تعیین تاریخ اخرین ارزیابی تا آن زمان
    
    $arr = preg_split('/\//', $_POST['ToDate']);
     
    if($arr[1] < 7 )
        $EvlEndDate = DateModules::shamsi_to_miladi($arr[0]."/06/31");
    elseif($arr[1] > 6 && $arr[1] <= 12 )
         $EvlEndDate = DateModules::shamsi_to_miladi($arr[0]."/12/29");

    $EvlStartDate = DateModules::shamsi_to_miladi($arr[0]."/01/01");
     
//..........................................................
    $query = " select s.staff_id ,  bi.MasterID , w.grade , case w.grade
							when 1 then 'مقدماتي'
							when 2 then 'پايه'
							when 3 then ' ارشد'
							when 4 then ' خبره'
							when 5 then ' عالي'
					  end gradeTitle , w.education_level , w.onduty_year, po.SupervisionKind , s.UnitCode

					from staff s inner join (SELECT  w.staff_id,
        SUBSTRING_INDEX(SUBSTRING(max(CONCAT(w.execute_date,w.writ_id,'.',w.writ_ver)),11),'.',1) writ_id,
						SUBSTRING_INDEX(max(CONCAT(w.execute_date,w.writ_id,'.',w.writ_ver)),'.',-1) writ_ver

						FROM writs w

						WHERE  emp_mode <> 13  and corrective = 0 and w.staff_id = " . $_POST['staff_id'] . " and execute_date <= '" . $EndDate . "' ) wr
										on s.staff_id = wr.staff_id
               inner join writs w
                    on wr.staff_id = w.staff_id and
                       wr.writ_id = w.writ_id and wr.writ_ver = w.writ_ver

								inner join Basic_Info bi
										on bi.TypeID = 6 and bi.InfoID = w.education_level
inner join position po 
								        on po.post_id = w.post_id 

			   where s.staff_id = " . $_POST['staff_id'];
    $res1 = PdoDataAccess::runquery($query);

    $query = " select t1.staff_id , st.PersonID ,bi.MasterID , wr.execute_date , pe.grade Moadel , pe.university_id
				from (
						SELECT  w.staff_id,
								SUBSTRING_INDEX(SUBSTRING(min(CONCAT(w.execute_date,w.writ_id,'.',w.writ_ver)),11),'.',1) writ_id,
								SUBSTRING_INDEX(min(CONCAT(w.execute_date,w.writ_id,'.',w.writ_ver)),'.',-1) writ_ver

						FROM writs w
								INNER JOIN staff ls ON(w.staff_id = ls.staff_id)

						WHERE /*w.history_only = 0 and*/ w.grade = " . $res1[0]['grade'] . " and  w.staff_id = " . $_POST['staff_id'] . " ) t1

						inner join writs wr on t1.staff_id = wr.staff_id and t1.writ_id = wr.writ_id and  t1.writ_ver = wr.writ_ver
						inner join Basic_Info bi
											on bi.TypeID = 6 and bi.InfoID = wr.education_level 
											
						inner join staff st 
											on st.staff_id = t1.staff_id						
						inner join person_educations pe  
											on st.PersonID = pe.PersonID AND pe.education_level =  wr.education_level ";

    $res2 = PdoDataAccess::runquery($query);



    $Condition1 = 0;
    $totalYear = 0;

    $Madrak = $res1[0]['MasterID'];
    $LastMadrak = $res1[0]['MasterID'];
   
    $duty_duration = duty_year_month_day($_POST['staff_id'], "", $EndDate);

    $onduty_year = $duty_duration['year'];
    $onduty_month = $duty_duration['month'];
    $onduty_day = $duty_duration['day'];

    // مدت سنوات تجربی
    $ValDuty = manage_salary_params::get_salaryParam_value("", 101, 65, $EndDate, ($res1[0]['grade'] + 1), $res2[0]['MasterID']);

if(empty($ValDuty))
{
	
	  $query = " select t1.staff_id , st.PersonID ,bi.MasterID , wr.execute_date , pe.grade Moadel , pe.university_id
				from (
						SELECT  w.staff_id,
								SUBSTRING_INDEX(SUBSTRING(min(CONCAT(w.execute_date,w.writ_id,'.',w.writ_ver)),11),'.',1) writ_id,
								SUBSTRING_INDEX(min(CONCAT(w.execute_date,w.writ_id,'.',w.writ_ver)),'.',-1) writ_ver

						FROM writs w
								INNER JOIN staff ls ON(w.staff_id = ls.staff_id)
inner join Basic_Info bi2 on bi2.TypeID = 6 and bi2.InfoID = w.education_level 

						WHERE bi2.MasterID = ".($res2[0]['MasterID']+1)." and /*w.history_only = 0 and*/ w.grade = " . $res1[0]['grade'] . " and  w.staff_id = " . $_POST['staff_id'] . "
  ) t1

						inner join writs wr on t1.staff_id = wr.staff_id and t1.writ_id = wr.writ_id and  t1.writ_ver = wr.writ_ver
						inner join Basic_Info bi
											on bi.TypeID = 6 and bi.InfoID = wr.education_level 
											
						inner join staff st 
											on st.staff_id = t1.staff_id						
						inner join person_educations pe  
											on st.PersonID = pe.PersonID AND pe.education_level =  wr.education_level 
									where bi.MasterID = ".($res2[0]['MasterID']+1) ;

    $res2 = PdoDataAccess::runquery($query);

	$ValDuty = manage_salary_params::get_salaryParam_value("", 101, 65, $EndDate, ($res1[0]['grade'] + 1), $res2[0]['MasterID']);
	
}


    $totalYear = $onduty_year;
    if ($onduty_year >= $ValDuty) {
        $Condition1 = 1; //  احراز شرط اول 
        $Madrak = $res2[0]['MasterID'];
    }     
    
    if ($res1[0]['MasterID'] != $res2[0]['MasterID']) {

        $qry = " SELECT doc_date , grade , university_id
				 FROM person_educations  p
									inner join staff s on p.personID = s.PersonID

				 WHERE s.staff_id = " . $_POST['staff_id'] . " and p.education_level =" . $res1[0]['education_level'];

        $res3 = PdoDataAccess::runquery($qry);
        $DayRes = DateModules::GDateMinusGDate($EndDate, $res3[0]['doc_date']);

        DateModules::day_to_ymd($DayRes, $Ryear, $Rmonth, $Rday);

        // مدت سنوات تجربی
        $ValDuty = manage_salary_params::get_salaryParam_value("", 101, 65, $EndDate, ($res1[0]['grade'] + 1), $res1[0]['MasterID']); //manage_salary_params::get_salaryParam_value("", 101, 65, DateModules::Now(), ($res1[0]['grade'] + 1), $res1[0]['MasterID']);



        if ($Ryear >= $ValDuty) {
            $Condition1 = 1; //  احراز شرط اول 
            $Madrak = $res1[0]['MasterID'];
            $totalYear = $Ryear;
        }
    }


if($Condition1 == 0 ) {
     $ConTitle = "  عدم " ; 
     $Madrak = $res2[0]['MasterID'];
     }
elseif($Condition1 == 1 ) 
    $ConTitle = " " ;

//..................................... چنانچه فرد ایثارگر باشد بایستی از امتیازات یک مقطع بالاتر برخوردار شود.......................
$qry = " select *
            from bases
                where BaseType in(3,4) AND
                      BaseStatus = 'NORMAL' AND  BaseValue > 0
		      PersonID = " .$res2[0]['PersonID'] ; 

$res17 =  PdoDataAccess::runquery($qry);
$Madrak2= "" ; 
if( count($res17) > 0 )
{    
    $Madrak2 = $Madrak + 1; 

    $LastMadrak = ($LastMadrak == 2) ? ($LastMadrak + 1) : $LastMadrak ;
  
}
else 
{
    $Madrak2= $Madrak ; 
}

    //............................ بررسی امتیازات سوابق تحصیلی فرد ...............................	
    //.................... مشخص کردن اینکه شغل مرتبط است یا خیر ؟ .. و امتیاز مدرک تحصیلی..........................
    //................... ارتقاء به مهارتی....................

    if ($LastMadrak == 3 && $res1[0]['grade'] == 1) {
        $RelatedScroe = 6;
        $EducLevelScore = 24;
    } elseif ($LastMadrak == 4 && $res1[0]['grade'] == 1) {
        $RelatedScroe = 9;
        $EducLevelScore = 36;
    } elseif ($LastMadrak == 5 && $res1[0]['grade'] == 1) {
        $RelatedScroe = 12;
        $EducLevelScore = 48;
    } elseif ($LastMadrak == 6 && $res1[0]['grade'] == 1) {
        $RelatedScroe = 15;
        $EducLevelScore = 60;
    }

    //...................... ارتقاء به رتبه 3 .................
    if ($LastMadrak == 3 && $res1[0]['grade'] == 2) {
         
        $RelatedScroe = 9;
        $EducLevelScore = 34;
    } elseif ($LastMadrak == 4 && $res1[0]['grade'] == 2) {
        $RelatedScroe = 13;
        $EducLevelScore = 51;
    } elseif ($LastMadrak == 5 && $res1[0]['grade'] == 2) {
        $RelatedScroe = 17;
        $EducLevelScore = 68;
    } elseif ($LastMadrak == 6 && $res1[0]['grade'] == 2) {
        $RelatedScroe = 21;
        $EducLevelScore = 85;
    }
    //............. ارتقاء به رتبه 2...........................
    if ($LastMadrak == 4 && $res1[0]['grade'] == 3) {
        $RelatedScroe = 17;
        $EducLevelScore = 66;
    } elseif ($LastMadrak == 5 && $res1[0]['grade'] == 3) {
        $RelatedScroe = 22;
        $EducLevelScore = 88;
    } elseif ($LastMadrak == 6 && $res1[0]['grade'] == 3) {
        $RelatedScroe = 28;
        $EducLevelScore = 110;
    }
    //.........................ارتقاء به رتبه1.................
    if ($LastMadrak == 4 && $res1[0]['grade'] == 4) {
        $RelatedScroe = 21;
        $EducLevelScore = 84;
    } elseif ($LastMadrak == 5 && $res1[0]['grade'] == 4) {
        $RelatedScroe = 28;
        $EducLevelScore = 112;
    } elseif ($LastMadrak == 6 && $res1[0]['grade'] == 4) {
        $RelatedScroe = 35;
        $EducLevelScore = 140;
    }

    //..................................................
    //.................................معدل مدرک تحصیلی

    if ($res1[0]['MasterID'] != $res2[0]['MasterID']) {
        $Grade = $res3[0]['grade'];
        $Uni = $res3[0]['university_id'];
    } else {
        $Grade = $res2[0]['Moadel'];
        $Uni = $res2[0]['university_id'];
    }
    
    
    if ($res1[0]['grade'] == 1) {
        if ($LastMadrak == 3 && $Grade >= 17 && $Grade <= 20)
            $GradeScore = 12;

        elseif ($LastMadrak == 4 && $Grade >= 17 && $Grade <= 20)
            $GradeScore = 18;

        elseif ($LastMadrak == 5 && $Grade >= 18 && $Grade <= 20)
            $GradeScore = 24;

        elseif ($LastMadrak == 6 && $Grade >= 19 && $Grade <= 20)
            $GradeScore = 30;
        //..............................
        elseif ($LastMadrak == 3 && $Grade >= 14 && $Grade < 17)
            $GradeScore = 11;
        elseif ($LastMadrak == 4 && $Grade >= 14 && $Grade < 17)
            $GradeScore = 16;

        elseif ($LastMadrak == 5 && $Grade >= 16 && $Grade < 18)
            $GradeScore = 22;

        elseif ($LastMadrak == 6 && $Grade >= 17.5 && $Grade < 19)
            $GradeScore = 27;
        //..............................
        elseif ($LastMadrak == 3 && $Grade >= 12 && $Grade < 14)
            $GradeScore = 9;
        elseif ($LastMadrak == 4 && $Grade >= 12 && $Grade < 14)
            $GradeScore = 14;

        elseif ($LastMadrak == 5 && $Grade >= 14 && $Grade < 16)
            $GradeScore = 18;

        elseif ($LastMadrak == 6 && $Grade >= 16 && $Grade < 17.5)
            $GradeScore = 23;
    }
    //....................ارتقاء به رتبه 3..............
    if ($res1[0]['grade'] == 2) {
        if ($LastMadrak == 3 && $Grade >= 17 && $Grade <= 20)
            $GradeScore = 18;

        elseif ($LastMadrak == 4 && $Grade >= 17 && $Grade <= 20)
            $GradeScore = 26;

        elseif ($LastMadrak == 5 && $Grade >= 18 && $Grade <= 20)
            $GradeScore = 34;

        elseif ($LastMadrak == 6 && $Grade >= 19 && $Grade <= 20)
            $GradeScore = 42;
        //..............................
        elseif ($LastMadrak == 3 && $Grade >= 14 && $Grade < 17)
            $GradeScore = 15;
        elseif ($LastMadrak == 4 && $Grade >= 14 && $Grade < 17)
            $GradeScore = 23;

        elseif ($LastMadrak == 5 && $Grade >= 16 && $Grade < 18)
            $GradeScore = 31;

        elseif ($LastMadrak == 6 && $Grade >= 17.5 && $Grade < 19)
            $GradeScore = 38;
        //..............................
        elseif ($LastMadrak == 3 && $Grade >= 12 && $Grade < 14)
            $GradeScore = 13;
        elseif ($LastMadrak == 4 && $Grade >= 12 && $Grade < 14)
            $GradeScore = 19;

        elseif ($LastMadrak == 5 && $Grade >= 14 && $Grade < 16)
            $GradeScore = 26;

        elseif ($LastMadrak == 6 && $Grade >= 16 && $Grade < 17.5)
            $GradeScore = 32;
    }

    //.......................... ارتقاء به رتبه 2............
    if ($res1[0]['grade'] == 3) {
        if ($LastMadrak == 4 && $Grade >= 17 && $Grade <= 20)
            $GradeScore = 34;

        elseif ($LastMadrak == 5 && $Grade >= 18 && $Grade <= 20)
            $GradeScore = 44;

        elseif ($LastMadrak == 6 && $Grade >= 19 && $Grade <= 20)
            $GradeScore = 56;
        //..............................
        if ($LastMadrak == 4 && $Grade >= 14 && $Grade < 17)
            $GradeScore = 30;

        elseif ($LastMadrak == 5 && $Grade >= 16 && $Grade < 18)
            $GradeScore = 40;

        elseif ($LastMadrak == 6 && $Grade >= 17.5 && $Grade < 19)
            $GradeScore = 50;
        //..............................
        if ($LastMadrak == 4 && $Grade >= 12 && $Grade < 14)
            $GradeScore = 25;

        elseif ($LastMadrak == 5 && $Grade >= 14 && $Grade < 16)
            $GradeScore = 33;

        elseif ($LastMadrak == 6 && $Grade >= 16 && $Grade < 17.5)
            $GradeScore = 41;
    }
    //............ ارتقاء به رتبه 1...................
    if ($res1[0]['grade'] == 4) {
        if ($LastMadrak == 4 && $Grade >= 17 && $Grade <= 20)
            $GradeScore = 42;

        elseif ($LastMadrak == 5 && $Grade >= 18 && $Grade <= 20)
            $GradeScore = 56;

        elseif ($LastMadrak == 6 && $Grade >= 19 && $Grade <= 20)
            $GradeScore = 70;
        //..............................
        if ($LastMadrak == 4 && $Grade >= 14 && $Grade < 17)
            $GradeScore = 38;

        elseif ($LastMadrak == 5 && $Grade >= 16 && $Grade < 18)
            $GradeScore = 50;

        elseif ($LastMadrak == 6 && $Grade >= 17.5 && $Grade < 19)
            $GradeScore = 63;
        //..............................
        if ($LastMadrak == 4 && $Grade >= 12 && $Grade < 14)
            $GradeScore = 32;

        elseif ($LastMadrak == 5 && $Grade >= 14 && $Grade < 16)
            $GradeScore = 42;

        elseif ($LastMadrak == 6 && $Grade >= 16 && $Grade < 17.5)
            $GradeScore = 53;
    }


    //..........................................محل اخذ مدرک تحصیلی..................

    $qry = " SELECT UniType FROM universities where university_id = " . $Uni;
    $res5 = PdoDataAccess::runquery($qry);
    $MLocation = $res5[0]['UniType'];

    //................ ارتقاء به رتبه مهارتی.....................	

    if ($res1[0]['grade'] == 1) {
        if ($LastMadrak == 3 && ( $MLocation == 1 || $MLocation == 2 ))
            $LocScore = 18;
        elseif ($LastMadrak == 4 && ( $MLocation == 1 || $MLocation == 2 ))
            $LocScore = 27;
        elseif ($LastMadrak == 5 && ( $MLocation == 1 || $MLocation == 2 ))
            $LocScore = 36;
        elseif ($LastMadrak == 6 && ( $MLocation == 1 || $MLocation == 2 ))
            $LocScore = 45;

        elseif ($LastMadrak == 3 && ( $MLocation == 3 || $MLocation == 4 || $MLocation == 5 || $MLocation == 6 || $MLocation == 7 ))
            $LocScore = 16;
        elseif ($LastMadrak == 4 && ( $MLocation == 3 || $MLocation == 4 || $MLocation == 5 || $MLocation == 6 || $MLocation == 7 ))
            $LocScore = 24;
        elseif ($LastMadrak == 5 && ( $MLocation == 3 || $MLocation == 4 || $MLocation == 5 || $MLocation == 6 || $MLocation == 7 ))
            $LocScore = 32;
        elseif ($LastMadrak == 6 && ( $MLocation == 3 || $MLocation == 4 || $MLocation == 5 || $MLocation == 6 || $MLocation == 7 ))
            $LocScore = 41;

        elseif ($LastMadrak == 3 && ( $MLocation == 8 || $MLocation == 9 || $MLocation == 10 || $MLocation == 11 || $MLocation == 12))
            $LocScore = 14;
        elseif ($LastMadrak == 4 && ( $MLocation == 8 || $MLocation == 9 || $MLocation == 10 || $MLocation == 11 || $MLocation == 12))
            $LocScore = 21;
        elseif ($LastMadrak == 5 && ( $MLocation == 8 || $MLocation == 9 || $MLocation == 10 || $MLocation == 11 || $MLocation == 12))
            $LocScore = 28;
        elseif ($LastMadrak == 6 && ( $MLocation == 8 || $MLocation == 9 || $MLocation == 10 || $MLocation == 11 || $MLocation == 12))
            $LocScore = 35;
    }

//................. ارتقاء به رتبه 3.....................................

    if ($res1[0]['grade'] == 2) {
        if ($LastMadrak == 3 && ( $MLocation == 1 || $MLocation == 2 ))
            $LocScore = 26;
        elseif ($LastMadrak == 4 && ( $MLocation == 1 || $MLocation == 2 ))
            $LocScore = 38;
        elseif ($LastMadrak == 5 && ( $MLocation == 1 || $MLocation == 2 ))
            $LocScore = 52;
        elseif ($LastMadrak == 6 && ( $MLocation == 1 || $MLocation == 2 ))
            $LocScore = 64;

        elseif ($LastMadrak == 3 && ( $MLocation == 3 || $MLocation == 4 || $MLocation == 5 || $MLocation == 6 || $MLocation == 7 ))
            $LocScore = 23;
        elseif ($LastMadrak == 4 && ( $MLocation == 3 || $MLocation == 4 || $MLocation == 5 || $MLocation == 6 || $MLocation == 7 ))
            $LocScore = 34;
        elseif ($LastMadrak == 5 && ( $MLocation == 3 || $MLocation == 4 || $MLocation == 5 || $MLocation == 6 || $MLocation == 7 ))
            $LocScore = 46;
        elseif ($LastMadrak == 6 && ( $MLocation == 3 || $MLocation == 4 || $MLocation == 5 || $MLocation == 6 || $MLocation == 7 ))
            $LocScore = 58;

        elseif ($LastMadrak == 3 && ( $MLocation == 8 || $MLocation == 9 || $MLocation == 10 || $MLocation == 11 || $MLocation == 12))
            $LocScore = 20;
        elseif ($LastMadrak == 4 && ( $MLocation == 8 || $MLocation == 9 || $MLocation == 10 || $MLocation == 11 || $MLocation == 12))
            $LocScore = 29;
        elseif ($LastMadrak == 5 && ( $MLocation == 8 || $MLocation == 9 || $MLocation == 10 || $MLocation == 11 || $MLocation == 12))
            $LocScore = 39;
        elseif ($LastMadrak == 6 && ( $MLocation == 8 || $MLocation == 9 || $MLocation == 10 || $MLocation == 11 || $MLocation == 12))
            $LocScore = 49;
    }
//........... ارتقاء به رتبه 2.............................................

    if ($res1[0]['grade'] == 3) {
        if ($LastMadrak == 4 && ( $MLocation == 1 || $MLocation == 2 ))
            $LocScore = 50;
        elseif ($LastMadrak == 5 && ( $MLocation == 1 || $MLocation == 2 ))
            $LocScore = 66;
        elseif ($LastMadrak == 6 && ( $MLocation == 1 || $MLocation == 2 ))
            $LocScore = 82;

        if ($LastMadrak == 4 && ( $MLocation == 3 || $MLocation == 4 || $MLocation == 5 || $MLocation == 6 || $MLocation == 7 ))
            $LocScore = 45;
        elseif ($LastMadrak == 5 && ( $MLocation == 3 || $MLocation == 4 || $MLocation == 5 || $MLocation == 6 || $MLocation == 7 ))
            $LocScore = 60;
        elseif ($LastMadrak == 6 && ( $MLocation == 3 || $MLocation == 4 || $MLocation == 5 || $MLocation == 6 || $MLocation == 7 ))
            $LocScore = 74;

        if ($LastMadrak == 4 && ( $MLocation == 8 || $MLocation == 9 || $MLocation == 10 || $MLocation == 11 || $MLocation == 12))
            $LocScore = 38;
        elseif ($LastMadrak == 5 && ( $MLocation == 8 || $MLocation == 9 || $MLocation == 10 || $MLocation == 11 || $MLocation == 12))
            $LocScore = 51;
        elseif ($LastMadrak == 6 && ( $MLocation == 8 || $MLocation == 9 || $MLocation == 10 || $MLocation == 11 || $MLocation == 12))
            $LocScore = 63;
    }
//............................... ارتقاء به رتبه 1.............................
    if ($res1[0]['grade'] == 4) {
        if ($LastMadrak == 4 && ( $MLocation == 1 || $MLocation == 2 ))
            $LocScore = 64;
        elseif ($LastMadrak == 5 && ( $MLocation == 1 || $MLocation == 2 ))
            $LocScore = 84;
        elseif ($LastMadrak == 6 && ( $MLocation == 1 || $MLocation == 2 ))
            $LocScore = 106;

        if ($LastMadrak == 4 && ( $MLocation == 3 || $MLocation == 4 || $MLocation == 5 || $MLocation == 6 || $MLocation == 7 ))
            $LocScore = 57;
        elseif ($LastMadrak == 5 && ( $MLocation == 3 || $MLocation == 4 || $MLocation == 5 || $MLocation == 6 || $MLocation == 7 ))
            $LocScore = 76;
        elseif ($LastMadrak == 6 && ( $MLocation == 3 || $MLocation == 4 || $MLocation == 5 || $MLocation == 6 || $MLocation == 7 ))
            $LocScore = 95;

        if ($LastMadrak == 4 && ( $MLocation == 8 || $MLocation == 9 || $MLocation == 10 || $MLocation == 11 || $MLocation == 12))
            $LocScore = 48;
        elseif ($LastMadrak == 5 && ( $MLocation == 8 || $MLocation == 9 || $MLocation == 10 || $MLocation == 11 || $MLocation == 12))
            $LocScore = 64;
        elseif ($LastMadrak == 6 && ( $MLocation == 8 || $MLocation == 9 || $MLocation == 10 || $MLocation == 11 || $MLocation == 12))
            $LocScore = 81;
    }

    //..........................سوابق اجرایی...................................
    //................................ارزشیابی کارکنان .................................

$CrrentYear = 1394 ;

    $qry = " select distinct p.personid , s.UnitCode
                from persons p  inner join staff s
                                            on p.personid = s.personid and p.person_type = s.person_type
                                inner join writs w
                                            on w.staff_id = s.staff_id

             where w.execute_date >= '".$EvlStartDate."' and w.execute_date <= '".$EvlEndDate."' and 
                   emp_mode in ( 2 , 4  ) and p.person_type = 2 and p.personID = ".$res2[0]['PersonID'] ; 
    
    $resException = PdoDataAccess::runquery($qry);
 

    $qry = " SELECT g2j(max(FromDate)) mdf
		FROM ease.SEVL_EvlPeriods 
                   WHERE ToDate <= '".$EvlEndDate."'";
    $res6 = PdoDataAccess::runquery($qry);

    $MaxDate = substr($res6[0]['mdf'], 0, 4);
     
    $strat_date = DateModules::shamsi_to_miladi($MaxDate . "/01/01");
    $end_date = DateModules::shamsi_to_miladi($MaxDate . "/12/29");
    $count = 3 ;
    if ($MaxDate == 1393) {
        $strat_date = DateModules::shamsi_to_miladi($MaxDate . "/01/01");
        $end_date = DateModules::shamsi_to_miladi($MaxDate . "/12/29");
        $count = 1;
    }

    $EvalScore = 0;

    for ($t = 0; $t < $count; $t++) {
        $qry = " SELECT
				 	 s.PersonID,
                       round(AVG(if(s.ProtestScore != 0.000,
						            s.ProtestScore,
						            s.TotalScore)) + sum(document),2) as score,
					p.FromDate,
					p.ToDate
				FROM
					ease.SEVL_Reports s
						left join					
					ease.SEVL_EvlPeriods p ON (s.EvlPeriodID = p.EvlPeriodID)
				where
					p.FromDate >= '" . $strat_date . "'
						and p.ToDate <= '" . $end_date . "'
						and s.PersonID = " . $res2[0]['PersonID'] . "

				group by  s.PersonID , substr(g2j(p.ToDate),1,4) ";
        
    if( !empty($resException[0]['personid']) && $resException[0]['personid'] > 0 )
    {
        /* $qry = " SELECT  AVG(if(s.ProtestScore != 0.000,
						s.ProtestScore,
						s.TotalScore) ) as score
                  FROM
                    ease.SEVL_Reports s
                        left join
                    ease.SEVL_EvlPeriods p ON (s.EvlPeriodID = p.EvlPeriodID)

                    inner join staff st on st.PersonID = s.PersonID

                    WHERE
                         p.FromDate >= '" . $strat_date . "' AND
                         p.ToDate <= '" . $end_date . "' AND st.UnitCode = ".$resException[0]['UnitCode']  ; */

            $qry = " SELECT AVG(score) score

			from (

				SELECT   round(AVG(if(s.ProtestScore != 0.000,
				s.ProtestScore,
				s.TotalScore)) + sum(document),2) as score
				FROM
				ease.SEVL_Reports s
				left join
				ease.SEVL_EvlPeriods p ON (s.EvlPeriodID = p.EvlPeriodID)

				inner join staff st on st.PersonID = s.PersonID

				WHERE
					p.FromDate >= '" . $strat_date . "' AND
					p.ToDate <= '" . $end_date . "' AND st.UnitCode = ".$resException[0]['UnitCode']."

				group by  s.PersonID , substr(g2j(p.ToDate),1,4)

				) t1 
		"  ; 

      
    }

       $resES = PdoDataAccess::runquery($qry);
//echo PdoDataAccess::GetLatestQueryString()."---<br>"; 

  //........................چنانچه فرد امتیاز نداشته باشد بایستی میانگین واحد برای آن در نظر گرفته شود......................
     
        if($resES[0]['score'] == 0 && ($CrrentYear - $t) > 1392 ) {
           
            /*$qry = " SELECT  AVG(if(s.ProtestScore != 0.000,
                                                    s.ProtestScore,
                                                    s.TotalScore)) as score
                    FROM
                        ease.SEVL_Reports s
                            left join
                        ease.SEVL_EvlPeriods p ON (s.EvlPeriodID = p.EvlPeriodID)

                        inner join staff st on st.PersonID = s.PersonID

                        WHERE
                            p.FromDate >= '" . $strat_date . "' AND
                            p.ToDate <= '" . $end_date . "' AND st.UnitCode = ".$res1[0]['UnitCode']  ;*/

                       $qry = " SELECT AVG(score) score

						from (

							SELECT   round(AVG(if(s.ProtestScore != 0.000,
							s.ProtestScore,
							s.TotalScore)) + sum(document),2) as score
							FROM
							ease.SEVL_Reports s
							left join
							ease.SEVL_EvlPeriods p ON (s.EvlPeriodID = p.EvlPeriodID)

							inner join staff st on st.PersonID = s.PersonID

							WHERE
								p.FromDate >= '" . $strat_date . "' AND
								p.ToDate <= '" . $end_date . "' AND st.UnitCode = ".$res1[0]['UnitCode']."

							group by  s.PersonID , substr(g2j(p.ToDate),1,4)

							) t1 
					"  ;  
           
            $resES = PdoDataAccess::runquery($qry);
        }
        //..............................................


       /* if (($MaxDate) == 1393) {
            $Eval93 = $resES[0]['score'];
            continue;
        }*/


        $evalScore += ($resES[0]['score'] == NULL ) ? 0 : $resES[0]['score'];

if (($CrrentYear - $t) == 1393)
 $Eval93 = $resES[0]['score'] ; 

if(($CrrentYear - $t) == 1392)
$evalScore += $Eval93;

       /* if (($CrrentYear - $t) < 1394)
            $evalScore += $Eval93;*/


        $strat_date = DateModules::shamsi_to_miladi(($CrrentYear - $t -1) . "/01/01");
        $end_date = DateModules::shamsi_to_miladi(($CrrentYear - $t -1 ) . "/12/29");
    }

    if ($MaxDate == 1393) {
        $MinEval = $Eval93;
    } else {

        $MinEval = round(($evalScore / 3));
        //$MinEval = round($resES[0]['score']);
    }
            
if( $res2[0]['PersonID'] == 201199 )
    {
     $MinEval = 98.66 ;    
    }


//....................................................................
/*if($_SESSION['UserID'] == 'jafarkhani' ){

echo $Madrak2.'***'.$MinEval ; die();
}*/
    if ($Madrak2 == 1 && $MinEval >= 70)
        $EvalScore = $onduty_year * 5;

    elseif ($Madrak2 == 2) {
        if ($MinEval >= 75)
            $EvalScore = $onduty_year * 6;
        elseif ($MinEval >= 70 && $MinEval < 75)
            $EvalScore = $onduty_year * 5;
    }

    elseif ($Madrak2 == 3) {
        if ($MinEval >= 77)
            $EvalScore = $onduty_year * 7;
        elseif ($MinEval >= 75 && $MinEval < 77)
            $EvalScore = $onduty_year * 6;
        elseif ($MinEval >= 70 && $MinEval < 75)
            $EvalScore = $onduty_year * 5;
    }

    elseif ($Madrak2 == 4) {
        if ($MinEval >= 80)
            $EvalScore = $onduty_year * 13;
        elseif ($MinEval >= 77 && $MinEval < 80)
            $EvalScore = $onduty_year * 7;
        elseif ($MinEval >= 75 && $MinEval < 77)
            $EvalScore = $onduty_year * 6;
        elseif ($MinEval >= 70 && $MinEval < 75)
            $EvalScore = $onduty_year * 5;
    }
    elseif ($Madrak2 == 5) {

        if ($MinEval >= 83)
            $EvalScore = $onduty_year * 16;
        elseif ($MinEval >= 80 && $MinEval < 83)
            $EvalScore = $onduty_year * 13;
        elseif ($MinEval >= 75 && $MinEval < 80)
            $EvalScore = $onduty_year * 7;
        elseif ($MinEval >= 75 && $MinEval < 77)
            $EvalScore = $onduty_year * 6;
        elseif ($MinEval >= 70 && $MinEval < 75)
            $EvalScore = $onduty_year * 5;
    }
    elseif ($Madrak2 == 6) {
        if ($MinEval >= 85)
            $EvalScore = $onduty_year * 16;
        elseif ($MinEval >= 83 && $MinEval < 85)
            $EvalScore = $onduty_year * 16;
        elseif ($MinEval >= 80 && $MinEval < 83)
            $EvalScore = $onduty_year * 13;
        elseif ($MinEval >= 75 && $MinEval < 80)
            $EvalScore = $onduty_year * 7;
        elseif ($MinEval >= 75 && $MinEval < 77)
            $EvalScore = $onduty_year * 6;
        elseif ($MinEval >= 70 && $MinEval < 75)
            $EvalScore = $onduty_year * 5;
    }
  
    //...................... امتیاز سمت اجرایی..................................

    $qry = " select w.writ_id , w.writ_ver ,w.execute_date , w.post_id , po.SupervisionKind
				from writs  w inner join position po
								on w.post_id = po.post_id

							left join Basic_Info bi
								on bi.TypeID = 42 and bi.InfoID = po.SupervisionKind

			 where  history_only = 0 and  w.staff_id = " . $_POST['staff_id'] . " and w.execute_date <= '" . $EndDate . "'
			 order by w.execute_date ";


    $res4 = PdoDataAccess::runquery($qry);

    $prevExe = $res4[0]['execute_date'];
    $prevKindS = $res4[0]['SupervisionKind'];
    $ManagerScore = 0;
    for ($k = 1; $k < count($res4); $k++) {

        if ($res4[$k]['SupervisionKind'] != $prevKindS) {
            $DiffDay = DateModules::GDateMinusGDate($res4[$k]['execute_date'], $prevExe);
            DateModules::day_to_ymd($DiffDay, $myear, $mmonth, $mday);

            if ($myear >= 1) {
                if ($prevKindS == 1) {
                    $ManagerScore += $myear * 8;
                } elseif ($prevKindS == 2) {
                    $ManagerScore += $myear * 7;
                } elseif ($prevKindS == 3) {
                    $ManagerScore += $myear * 5;
                } elseif ($prevKindS == 4 || $prevKindS == 5) {
                    $ManagerScore += $myear * 4;
                }
            }

            $prevKindS = $res4[$k]['SupervisionKind'];
            $prevExe = $res4[$k]['execute_date'];
        }
    }

    //..........................................................................		

    if ($res4[$k - 1]['SupervisionKind'] > 0) {

        $DiffDay = DateModules::GDateMinusGDate(DateModules::Now(), $prevExe);
        DateModules::day_to_ymd($DiffDay, $myear, $mmonth, $mday);

        if ($myear >= 1) {
            if ($prevKindS == 1) {
                $ManagerScore += $myear * 8;
            } elseif ($prevKindS == 2) {
                $ManagerScore += $myear * 7;
            } elseif ($prevKindS == 3) {
                $ManagerScore += $myear * 5;
            } elseif ($prevKindS == 4 || $prevKindS == 5) {
                $ManagerScore += $myear * 4;
            }
        }
    }

    if ($ManagerScore > 50)
        $ManagerScore = 50;
    //................................... عضویت در کارگروهها ................
    $qry = " SELECT sum(SessionNum) sn
				FROM CommitteeMembers
					WHERE PersonID = " . $res2[0]['PersonID'] . " AND ReceiptDate < '" . $EndDate . "' ";
    $res16 = PdoDataAccess::runquery($qry);

    $karGrpScore = 0;

    if (count($res16) > 0 && $res16[0]['sn'] > 0) {

        if ($res16[0]['sn'] > 0) {
            $karGrpScore = intval($res16[0]['sn'] * 2 / 10);

            if ($res1[0]['SupervisionKind'] == 1)
                $karGrpScore = $karGrpScore * 1;
            elseif ($res1[0]['SupervisionKind'] == 2)
                $karGrpScore = $karGrpScore * 2;
            elseif ($res1[0]['SupervisionKind'] == 3)
                $karGrpScore = $karGrpScore * 3;
            elseif ($res1[0]['SupervisionKind'] == 4 || $res1[0]['SupervisionKind'] == 5)
                $karGrpScore = $karGrpScore * 4;
            else
                $karGrpScore = $karGrpScore * 5;
        }
    }

    //....................... دوره های آموزشی.....................

    $qry = " select
				tbl.PersonID,SUM(tbl.hours) as TotalHours
			 from
				(
				select
					(p.hours) as hours,r.PersonID
				from
					ease.SED_registeration r
				inner join ease.SED_presentation p ON (r.p_id = p.p_id
					and r.group_id = p.group_id)
				where r.PersonID = " . $res2[0]['PersonID'] . " and r.state<>1 and p.from_date
				between '1922-03-22' and '" . $EndDate . "' and r.status_hrms = 'YES' and r.state = 2
				group by r.PersonID , p.p_id , p.group_id

				) as tbl

			group by tbl.PersonID
			";

    $res7 = PdoDataAccess::runquery($qry);

    $qry = " select pt.PersonID,sum(p.hours) as TotalHours
			FROM ease.SED_presentation p
					LEFT JOIN hrmstotal.staff f on  (f.staff_id=p.teacher_id)
					INNER join hrmstotal.persons pt on (pt.PersonID=f.PersonID)
					inner join ease.SED_lesson  l on (p.lesson_id=l.lesson_id)
			where f.PersonID=" . $res2[0]['PersonID'] . " and p.from_date between '1922-03-22' and '" . $EndDate . "' 
			group by pt.PersonID
				";
    $res8 = PdoDataAccess::runquery($qry);

    $TrainScore = round(($res7[0]['TotalHours'] + $res8[0]['TotalHours'] ) / 10);
 $TrainScore2 = round(($res7[0]['TotalHours'] + $res8[0]['TotalHours'] ));

    //.......................میزان تسلط به نرم افزار ها ....................................

    $qry = " select r.PersonID,sum(if(SWType = 1 ,p.hours,0) ) as val1 ,sum(if(SWType = 2 ,p.hours,0) ) as val2
				from ease.SED_registeration r
							inner join  ease.SED_presentation  p on (r.p_id=p.p_id and r.group_id=p.group_id )
							inner join  ease.SED_lesson sl on sl.lesson_id = p.lesson_id and  SWType in (1,2)
			 where  PersonID= " . $res2[0]['PersonID'] . " and r.state<>1 and p.from_date between '1922-03-22' and '" . $EndDate . "' 
			 group by r.PersonID
			 order by r.PersonID asc
				";
    $res8 = PdoDataAccess::runquery($qry);

    $qry = "select PersonID 
				from person_educations 
					where PersonID= " . $res2[0]['PersonID'] . " and
                                              sfid in (SELECT sfid FROM study_fields where ptitle like '%کامپیوتر%')";
    $res13 = PdoDataAccess::runquery($qry);

    if ($res8[0]['val2'] > 0 || (count($res13) > 0 && $res13[0]['PersonID'] > 0)) {
        $swGrade = 64;
    }

    //.......................نرم افزارهای تخصصی.................

    $qry2 = " select count(*) cn 
			  from framework.UsersSystems us
							inner join AccountSpecs acc on us.UserID = acc.WebUserID

			  where UserSystemStatus = 'ENABLE' and acc.PersonID =" . $res2[0]['PersonID'];
    $res9 = PdoDataAccess::runquery($qry2);

    if (count($res9) > 0) {
        $Item_1 = 36;
    }
    //...............................میزان تسلط به زبان های خارجی...............................
    if ($Madrak == 2)
        $LanguageScore = 6;

    elseif ($Madrak == 3)
        $LanguageScore = 11;

    elseif ($Madrak == 4)
        $LanguageScore = 17;

    elseif ($Madrak == 5)
        $LanguageScore = 24;

    elseif ($Madrak == 6)
        $LanguageScore = 30;
    //...............................فعالیت های علمی و پژوهشی................
    //...............پیشنهاد ها .....................

   $qry = "    select count(*)  as ImplementedSuggests

                from suggest.suggests
                    LEFT JOIN suggest.SuggestStatus
                            on (suggests.SuggestStatusID=SuggestStatus.SuggestStatusID)
                    left join  suggest.AccountSpecs
                            on (AccountSpecs.WebUserID=suggests.UserID)
                    left join suggest.persons
                            on (persons.PersonID=AccountSpecs.PersonID)

                where   suggests.SuggestStatusID= 74  and persons.PersonID = " . $res2[0]['PersonID'] . " and
                        suggests.RegisterDate<='" . $EndDate . "'";

    $resIm = PdoDataAccess::runquery($qry);
    $ImpSug = $resIm [0]['ImplementedSuggests'] * 10;

    $qry = " select count(*)  as accept
		      from suggest.suggests
		      LEFT JOIN suggest.SuggestAudit h on(suggests.SuggestID=h.SuggestID)
		      left join  suggest.AccountSpecs on (AccountSpecs.WebUserID=suggests.UserID)
		      left join suggest.persons on (persons.PersonID=AccountSpecs.PersonID)
		      where  (h.NewStatus='19' or  h.NewStatus='74') 
		      and persons.PersonID= " . $res2[0]['PersonID'] . " and suggests.RegisterDate<= '".$EndDate."'";
            

    $resAc = PdoDataAccess::runquery($qry);
    $AccSug = $resAc [0]['accept'] * 8;


    $qry = "select count(*)  as unitSug
		      from suggest.suggests
		      left join  suggest.AccountSpecs on (AccountSpecs.WebUserID=suggests.UserID)
		      left join suggest.persons on (persons.PersonID=AccountSpecs.PersonID)
			  inner join hrmstotal.staff s on  s.PersonID =  persons.PersonID
			  inner join suggest.committees co on suggests.CommitteeID = co.CommitteeID
		  where     persons.PersonID=" . $res2[0]['PersonID'] . " and suggests.RegisterDate<= '".$EndDate."' AND
                  ( co.UnitCode = s.UnitCode || co.UnitCode = s.ouid )";
    $resUnit = PdoDataAccess::runquery($qry);
    $UnitSug = $resUnit [0]['unitSug'] * 2;


    $qry = " 
			  select count(*)  as univerSug
		      from suggest.suggests
		      left join  suggest.AccountSpecs on (AccountSpecs.WebUserID=suggests.UserID)
		      left join suggest.persons on (persons.PersonID=AccountSpecs.PersonID)
			    inner join hrmstotal.staff s on  s.PersonID =  persons.PersonID
			    inner join suggest.committees co on suggests.CommitteeID = co.CommitteeID
		  where   persons.PersonID=" . $res2[0]['PersonID'] . " and suggests.RegisterDate<= '".$EndDate."' AND
                  co.UnitCode = 100  ";
    $resUni = PdoDataAccess::runquery($qry);
    $UniSug = $resUni [0]['univerSug'] * 5;


    //................................. دستاوردهای پژوهشی .......................

$qry = " select
            ps.PersonID,
            concat(ps.pfname, ' ', ps.plname) as pname,

( 

SELECT 	count(sr.ItemType) as tConferencePaper

FROM ease.SEVL_ResearchScores sr

where   ItemType = 'ConferencePaper' and
        TotalScore > 0 and
        ConfirmDate <= '".$EndDate."' and
        PersonID = " . $res2[0]['PersonID'] . "

) as ConferencePaper,
(
select	count(sr.ItemType) as tpaper

FROM
    ease.SEVL_ResearchScores sr

where   ItemType = 'paper' and
        TotalScore > 0 and
        ConfirmDate <= '".$EndDate."' and
        PersonID = " . $res2[0]['PersonID'] . "
) as paper,
(
select
    count(sr.ItemType) as tlecture
FROM
    ease.SEVL_ResearchScores sr

where
    ItemType = 'lecture' and
    TotalScore > 0 and
    ConfirmDate <= '".$EndDate."' and
    PersonID = " . $res2[0]['PersonID'] . "
) as lecture,
(
select
        count(sr.ItemType) as tbook
FROM
        ease.SEVL_ResearchScores sr

where
    ItemType = 'book' and
    TotalScore > 0 and
    ConfirmDate <= '".$EndDate."' and
    PersonID = " . $res2[0]['PersonID'] . "

) as book,
(
select
        count(sr.ItemType) as tAppPlanCoHelper
FROM
        ease.SEVL_ResearchScores sr

where
    ItemType = 'AppPlanCoHelper' and
    TotalScore > 0 and
    ConfirmDate <= '".$EndDate."' and
    PersonID = " . $res2[0]['PersonID'] . "
                                                        
) as AppPlanCoHelper,
(
select
        count(sr.ItemType) as tTechnicalReport
FROM
        ease.SEVL_ResearchScores sr

where
    ItemType = 'TechnicalReport' and
    TotalScore > 0 and
    ConfirmDate <= '".$EndDate."' and
    PersonID = " . $res2[0]['PersonID'] . "     

) as TechnicalReport,
                                                        
(
select	count(sr.ItemType) as tinvention

FROM
ease.SEVL_ResearchScores sr

where
    ItemType = 'invention' and
    TotalScore > 0 and
    ConfirmDate <= '".$EndDate."' and
    PersonID = " . $res2[0]['PersonID'] . " 
        
) as invention ,
(  
select
        count(sr.ItemType) as tlno
FROM
        ease.SEVL_ResearchScores sr

where
    ItemType = 'TranslatedPaper' and
    TotalScore > 0 and
    ConfirmDate <= '".$EndDate."' and
    PersonID = " . $res2[0]['PersonID'] . " 
        
) as TranslatedPaper ,

(  
select
        count(sr.ItemType) as tecRepNo
FROM
        ease.SEVL_ResearchScores sr

where
    ItemType = 'TechnicalReport' and
    TotalScore > 0 and
    ConfirmDate <= '".$EndDate."' and
    PersonID = " . $res2[0]['PersonID'] . " 
        
) as TechnicalReport
				
FROM        
        hrmstotal.persons ps 
where	ps.PersonID = " . $res2[0]['PersonID'];

    $resResearch = PdoDataAccess::runquery($qry);

if( $_SESSION['UserID'] == 'jafarkhani' )
{    
  //  echo PdoDataAccess::GetLatestQueryString(); die();
 //echo $resResearch[0]['AppPlanCoHelper'].'****'.$resResearch[0]['TechnicalReport'] ; die(); 
}

    $inventionScore = $resResearch[0]['invention'] * 3;

    $paperReleaseScore = ($resResearch[0]['paper'] + $resResearch[0]['TranslatedPaper'] ) * 3;
    $presentScore = ($resResearch[0]['ConferencePaper'] + $resResearch[0]['lecture']) * 3;
    $TranslateScore = $resResearch[0]['book'] * 5;
    $PlanScore = ($resResearch[0]['AppPlanCoHelper'] + $resResearch[0]['TechnicalReport']) * 5;


    //...................................مستندات......................................

    $qry = " SELECT
				ed.CreatorID,				
				count(*) as TotalTajrobiyat
			 FROM
				ease.SEVL_ExperimentalDocuments ed
					left join
				ease.SEVL_EvlPeriods p ON (p.EvlPeriodID = ed.EvlPeriodID)
					left join
				hrmstotal.persons ps ON (ps.PersonID = ed.CreatorID)
			 WHERE
					ed.MeritType = '0'
					and (ed.SupGrade1 + ed.SupGrade2 + ed.SupGrade3 + ed.SupGrade4 + ed.SupGrade5) != '0.00'
					and ed.DocStatus = '3'					
					and p.ToDate <= '" .$EvlEndDate . "'
					and ed.CreatorID = " . $res2[0]['PersonID'];

    $resDoc = PdoDataAccess::runquery($qry);

    $DocScore = $resDoc[0]['TotalTajrobiyat'] * 5;
  
     
    //................. مستندات قبل از سال 93 ......................
    $qry = " select score from ResearchAct where ActType = 1 and  PersonID = ".$res2[0]['PersonID'] ; 
    $resRA2 = PdoDataAccess::runquery($qry); 
    
    if(count($resRA2) > 0 && $resRA2[0]['score'] > 0 ) 
    {
        $DocScore += $resRA2[0]['score'] ; 
    }
    
if($res2[0]['PersonID'] == 200852 ) 		
	{
		$DocScore = 30 ; 		
	}

    //............................................. گزارشات تخصصی داوطلبانه ..............

    $qry = " SELECT
				ed.PersonID,				
				count(*) as TotalDastavard
			FROM
				ease.SEVL_TechnicalReport ed
					left join
				ease.SEVL_EvlPeriods p ON (p.EvlPeriodID = ed.PeriodID)
					left join
				hrmstotal.persons ps ON (ps.PersonID = ed.PersonID)
			where
				(ed.SupGrade1 + ed.SupGrade2 + ed.SupGrade3 + ed.SupGrade4 + ed.SupGrade5) != '0.00'
					and ed.RepStatus = '1'					
					and p.ToDate <= '" . $EvlEndDate . "'
					and ed.PersonID = " . $res2[0]['PersonID'];

    $resTechRep = PdoDataAccess::runquery($qry);
    $TechRepScore = $resTechRep[0]['TotalDastavard'] * 4;


    //.........................................نشان لیاقت.................................

    $qry = " SELECT MeritType , count(*) cn
				FROM Merits
			where PersonID = " . $res2[0]['PersonID'] . " and ReceiptDate <= '".$EndDate."'
			group by MeritType ";
    $res12 = PdoDataAccess::runquery($qry);

    if (count($res12) > 0) {

        for ($t = 0; $t < count($res12); $t++) {

            if ($res12[$t]['MeritType'] == 1) {
                $resMerit1 = $res12[$t]['cn'] * 13;
            }
            if ($res12[$t]['MeritType'] == 2) {
                $resMerit2 = $res12[$t]['cn'] * 11;
            }
            if ($res12[$t]['MeritType'] == 3) {
                $resMerit3 = $res12[$t]['cn'] * 12;
            }
            if ($res12[$t]['MeritType'] == 4) {
                $resMerit4 = $res12[$t]['cn'] * 10;
            }
            if ($res12[$t]['MeritType'] == 5) {
                $resMerit5 = $res12[$t]['cn'] * 10;
            }
            if ($res12[$t]['MeritType'] == 6) {
                $resMerit6 = $res12[$t]['cn'] * 9;
            }
            if ($res12[$t]['MeritType'] == 7) {
                $resMerit7 = $res12[$t]['cn'] * 10;
            }
            if ($res12[$t]['MeritType'] == 8) {
                $resMerit8 = $res12[$t]['cn'] * 9;
            }
            if ($res12[$t]['MeritType'] == 9) {
                $resMerit9 = $res12[$t]['cn'] * 8;
            }
            if ($res12[$t]['MeritType'] == 10) {
                $resMerit10 = $res12[$t]['cn'] * 8;
            }


            if ($res12[$t]['MeritType'] == 11) {
                $resMerit11 = $res12[$t]['cn'] * 30;
            }
            if ($res12[$t]['MeritType'] == 12) {
                $resMerit12 = $res12[$t]['cn'] * 24;
            }
            if ($res12[$t]['MeritType'] == 13) {
                $resMerit13 = $res12[$t]['cn'] * 18;
            }
        }
    }

    //..........................................................................

    $qry = " select p.PersonID , 
                    s.staff_id ,p.sex , p.pfname , p.plname , bi.Title  GradeTitle   , bi2.Title EducTitle ,
		    po.title PostTitle, jf.title JobTitle , sf.ptitle StudyTitle ,  
                    sb.ptitle BranchTitle , pe.grade
				from persons p inner join staff s 
										on p.PersonID = s.PersonID
							   inner join writs w 
										on 	s.staff_id = w.staff_id and 
											s.last_writ_id = w.writ_id and  
											s.last_writ_ver = w.writ_ver
							   inner join Basic_Info bi 
										on bi.typeid = 44 and bi.InfoID = w.grade 
                                                            inner join Basic_Info bi2 
										on bi2.typeid = 6 and bi2.InfoID = w.education_level    
							    left join position po 
										on w.post_id = po.post_id
								left join job_fields jf 
										on jf.jfid = po.jfid
								left join study_fields sf 
										on w.sfid = sf.sfid
								left join study_branchs sb 
										on w.sfid = sb.sfid and w.sbid = sb.sbid
								left join person_educations pe 
										on p.PersonID = pe.PersonID and  w.education_level = pe.education_level

							   
					where  s.staff_id = " . $_POST['staff_id'];
    $ResPInfo = PdoDataAccess::runquery($qry);

    $resMadrak = PdoDataAccess::runquery("select title from Basic_Info where typeid = 35 and  InfoID = " . $Madrak);

//..... تعظیم شعائر اسلامی و مذهبی .....

    $qry = " select BehaviorScore
				from ease.SEVL_Reports
					where PersonID = " . $res2[0]['PersonID'] . " 
					LIMIT 1 ";
 
    if( !empty($resException[0]['personid']) && $resException[0]['personid'] > 0 )
    {
        $qry = "  select AVG(BehaviorScore) BehaviorScore
				from ease.SEVL_Reports sr
                    inner join staff s on sr.personid = s.personid

					where s.UnitCode = ".$resException[0]['UnitCode'] ;

    }

    $res15 = PdoDataAccess::runquery($qry);

    $Item_10 = round(($res15[0]['BehaviorScore'] * 24 ) / 10);

    // مشارکت در فعالیت های فرهنگی.........

    $CulturalPshipScore = 0;

    $CulturalPshipScore = round(($res15[0]['BehaviorScore'] * 6 ) / 10);


    //..................... توسعه فردی و تسلط به قوانین..................
    $qry = "   SELECT
					f.EvlFormID,
					fi.IndicatorID,
					f.PersonID,
					fi.grade1,
					fi.grade2,
					fi.grade3,
					fi.grade4,
					fi.grade5,
					concat(pfname,' ',plname) as pname
				FROM
					ease.SEVL_EvlFormIndicators fi
						left join
					ease.SEVL_EvlForms f ON (f.EvlFormID = fi.EvlFormID)
				left join
					hrmstotal.persons p ON (p.PersonID = f.PersonID)
				where
					fi.IndicatorID in (7,16) AND p.personID = " . $res2[0]['PersonID'];

     if( !empty($resException[0]['personid']) && $resException[0]['personid'] > 0 )
    {
        $qry = "   SELECT
					f.EvlFormID,
					fi.IndicatorID,
					f.PersonID,
					fi.grade1,
					fi.grade2,
					fi.grade3,
					fi.grade4,
					fi.grade5,
					concat(pfname,' ',plname) as pname
				FROM
					ease.SEVL_EvlFormIndicators fi
						left join
					ease.SEVL_EvlForms f ON (f.EvlFormID = fi.EvlFormID)
				left join
					hrmstotal.persons p ON (p.PersonID = f.PersonID)
                                         inner join staff s on p.personid = s.personid
				where
					fi.IndicatorID in (7,16) AND s.UnitCode = ".$resException[0]['UnitCode'] ; 
    }
    
    $res11 = PdoDataAccess::runquery($qry);

    $sumGrade2 = 0;
    $GNO2 = 0;

    for ($j = 0; $j < count($res11); $j++) {

        if ($res11[$j]['grade1'] > 0) {

            $sumGrade2+= $res11[$j]['grade1'];
            $GNO2++;
        }
        if ($res11[$j]['grade2'] > 0) {

            $sumGrade2+= $res11[$j]['grade2'];
            $GNO2++;
        }
        if ($res10[$j]['grade3'] > 0) {

            $sumGrade2+= $res11[$j]['grade3'];
            $GNO2++;
        }
        if ($res10[$j]['grade4'] > 0) {
            $sumGrade2+= $res11[$j]['grade4'];
            $GNO2++;
        }
        if ($res10[$j]['grade5'] > 0) {
            $sumGrade2+= $res11[$j]['grade5'];
            $GNO2++;
        }
    }

    $minItem11 = round($sumGrade2 / $GNO2);

    if ($minItem11 == 1)
        $Item_11 = 8;
    else if ($minItem11 == 2)
        $Item_11 = 12;
    else if ($minItem11 == 3)
        $Item_11 = 16;
    else if ($minItem11 == 4)
        $Item_11 = 20;
    else if ($minItem11 == 5)
        $Item_11 = 24;
    else if ($minItem11 == 6)
        $Item_11 = 28;

    // تکریم ارباب رجوع .....

    $qry = "   SELECT
					f.EvlFormID,
					fi.IndicatorID,
					f.PersonID,
					fi.grade1,
					fi.grade2,
					fi.grade3,
					fi.grade4,
					fi.grade5,
					concat(pfname,' ',plname) as pname
				FROM
					ease.SEVL_EvlFormIndicators fi
						left join
					ease.SEVL_EvlForms f ON (f.EvlFormID = fi.EvlFormID)
				left join
					hrmstotal.persons p ON (p.PersonID = f.PersonID)
				where
					fi.IndicatorID in (15) AND p.personID = " . $res2[0]['PersonID'];

    if( !empty($resException[0]['personid']) && $resException[0]['personid'] > 0 )
    { 
         $qry = "   SELECT
					f.EvlFormID,
					fi.IndicatorID,
					f.PersonID,
					fi.grade1,
					fi.grade2,
					fi.grade3,
					fi.grade4,
					fi.grade5,
					concat(pfname,' ',plname) as pname
				FROM
					ease.SEVL_EvlFormIndicators fi
						left join
					ease.SEVL_EvlForms f ON (f.EvlFormID = fi.EvlFormID)
				left join
					hrmstotal.persons p ON (p.PersonID = f.PersonID)
                                inner join staff s on p.personid = s.personid
				where
					fi.IndicatorID in (15) AND s.UnitCode = ".$resException[0]['UnitCode'] ; 
    }
    
    $res11 = PdoDataAccess::runquery($qry);

    $sumGrade2 = 0;
    $GNO2 = 0;

    for ($j = 0; $j < count($res11); $j++) {

        if ($res11[$j]['grade1'] > 0) {

            $sumGrade2+= $res11[$j]['grade1'];
            $GNO2++;
        }
        if ($res11[$j]['grade2'] > 0) {

            $sumGrade2+= $res11[$j]['grade2'];
            $GNO2++;
        }
        if ($res10[$j]['grade3'] > 0) {

            $sumGrade2+= $res11[$j]['grade3'];
            $GNO2++;
        }
        if ($res10[$j]['grade4'] > 0) {
            $sumGrade2+= $res11[$j]['grade4'];
            $GNO2++;
        }
        if ($res10[$j]['grade5'] > 0) {
            $sumGrade2+= $res11[$j]['grade5'];
            $GNO2++;
        }
    }


    $minItem11 = round($sumGrade2 / $GNO2);
    $CustomerOrientedScore = ($minItem11 * 21) / 6;


    //............................ خدمات برجسته ................................
    $qry = " SELECT BaseValue
				FROM bases
				 WHERE BaseType = 27 and PersonID = " . $res2[0]['PersonID'] . " and ExecuteDate <= '".$EndDate."' AND  BaseStatus ='NORMAL' ";
    $res14 = PdoDataAccess::runquery($qry);
    $Item_12 = 0;
    $Item_12 = $res14[0]['BaseValue'] * 11;
if($_SESSION['UserID'] == 'jafarkhani' ) 
{
 //echo  PdoDataAccess::GetLatestQueryString()."---"; die();
}
    //.............................................................................
    $Item_9 = 0  ; // احصا فرآیند ها و روشهای انجام کار........
    $qry = " select score from ResearchAct where ActType = 2 and  PersonID = ".$res2[0]['PersonID'] ; 
    $resRA1 = PdoDataAccess::runquery($qry);   
    
    if( count($resRA1) > 0 && $resRA1[0]['score'] > 0 )
    {
        $Item_9 = $resRA1[0]['score'];     
    } 
    
if($res2[0]['PersonID'] == 200852 ) 		
	{
		$Item_9 = 30 ; 		
	}
    
    $Item_7 = $inventionScore; // ثبت اختراع و کارهای بدیع هنری .........
    //.................... ماکزیمم امتیازات ......................

    if ($Madrak2 == 1) {
        $scoreC1 = 150;
    } elseif ($Madrak2 == 2) {
        $scoreC1 = 180;
    } elseif ($Madrak2 == 3) {
        $scoreC1 = 210;
    } elseif ($Madrak2 == 4) {
        $scoreC1 = 385;
    } elseif ($Madrak2 == 5) {
        $scoreC1 = 490;
    } elseif ($Madrak2 == 6) {
        $scoreC1 = 490;
    }

    if ($res1[0]['grade'] == 1) {

        if ($LastMadrak == 3) {
            $scoreA1 = 60;
        } elseif ($LastMadrak == 4) {
            $scoreA1 = 90;
        } elseif ($LastMadrak == 5) {
            $scoreA1 = 120;
        } elseif ($LastMadrak == 6) {
            $scoreA1 = 150;
        }
    } elseif ($res1[0]['grade'] == 2) {
        if ($LastMadrak == 3) {
            $scoreA1 = 85;
        } elseif ($LastMadrak == 4) {
            $scoreA1 = 127;
        } elseif ($LastMadrak == 5) {
            $scoreA1 = 170;
        } elseif ($LastMadrak == 6) {
            $scoreA1 = 213;
        }
    } elseif ($res1[0]['grade'] == 3) {
        if ($LastMadrak == 4) {
            $scoreA1 = 165;
        } elseif ($LastMadrak == 5) {
            $scoreA1 = 220;
        } elseif ($LastMadrak == 6) {
            $scoreA1 = 275;
        }
    } elseif ($res1[0]['grade'] == 4) {
        if ($LastMadrak == 4) {
            $scoreA1 = 210;
        } elseif ($LastMadrak == 5) {
            $scoreA1 = 280;
        } elseif ($LastMadrak == 6) {
            $scoreA1 = 350;
        }
    }


    $nextGradeTitle = '';
    $MinScore = 0;


//.................. کنترل امتیاز های مازاد..............
    if ($TrainScore > 150) {
        $TrainScore = 150;
    }

    if ($EvalScore > $scoreC1) {
        $EvalScore = $scoreC1;
    }

    if (($ManagerScore + $karGrpScore ) > 50) {
        $karGrpScore = 50 - $ManagerScore;
    }

    if ($Item_12 > 22) {
        $Item_12 = 22;
    }

    if ($inventionScore > 11)
        $inventionScore = 11;

    if ($paperReleaseScore > 14)
        $paperReleaseScore = 14;

    if ($presentScore > 11)
        $presentScore = 11;

    if ($TranslateScore > 30)
        $TranslateScore = 30;

    if ($PlanScore > 30)
        $PlanScore = 30;

    if ($Item_7 > 11)
        $Item_7 = 11;

    if ($DocScore > 30)
        $DocScore = 30;

    if ($Item_9 > 30)
        $Item_9 = 30;

    if ($TechRepScore > 11)
        $TechRepScore = 11;

    if ($ImpSug > 56)
        $ImpSug = 56;

    if ($AccSug > 40)
        $AccSug = 40;

    if ($UniSug > 25)
        $UniSug = 25;

    if ($UnitSug > 11)
        $UnitSug = 11;
$totalSug = ($ImpSug + $AccSug + $UniSug + $UnitSug) ;  
$totalSug = ( $totalSug > 56 ) ? 56 :  $totalSug ; 

    if ($resMerit1 > 52)
        $resMerit1 = 52;

    if ($resMerit3 > 48)
        $resMerit3 = 48;

    if ($resMerit2 > 44)
        $resMerit2 = 44;

    if ($resMerit4 > 40)
        $resMerit4 = 40;

    if ($resMerit5 > 40)
        $resMerit5 = 40;

    if ($resMerit6 > 36)
        $resMerit6 = 36;

    if ($resMerit7 > 40)
        $resMerit7 = 40;

    if ($resMerit8 > 36)
        $resMerit8 = 36;


    $ItemMerit9 = $resMerit9 + $resMerit10;
    if (($resMerit9 + $resMerit10) > 32)
        $ItemMerit9 = 32;
    
    $totalMerit = ($resMerit1 + $resMerit2 + $resMerit3 + $resMerit4 + $resMerit5 + $resMerit6 + $resMerit7 + $resMerit8 + $resMerit9 + $resMerit10 ) ; 
    $totalMerit = ($totalMerit > 50 ) ? 50 : $totalMerit ; 
 

    if ($resMerit11 > 30)
        $resMerit11 = 30;

    if ($resMerit12 > 24)
        $resMerit12 = 24;

    if ($resMerit13 > 18)
        $resMerit13 = 18;
    
    $totalMeritNemooneh = ($resMerit11 + $resMerit12 + $resMerit13 ) ; 
    $totalMeritNemooneh = ($totalMeritNemooneh > 30) ? 30 : $totalMeritNemooneh ; 
     
    //.............. تبصره 7 آیین نامه ................................

    if($MinEval > 75 && $MinEval <= 80 ) 
    {
        $extraScore = $MinEval - 75  ;  
    }
    elseif ($MinEval > 80 ) 
    {
        $extraScore = ( $MinEval - 80 ) * 1.5  ;  
        $extraScore = ( $extraScore  > 10 ) ? 10  : $extraScore  ;
    }

    $TotalTrainScore = $TrainScore + $swGrade + $Item_1 + $LanguageScore + $Item_11 + $extraScore ; 


    $TotalTrainScore = ($TotalTrainScore > 280 ) ? 280 : round($TotalTrainScore) ;

//......................................................
    $TotalScore = $EducLevelScore + $RelatedScroe + $GradeScore + $LocScore +
            $TotalTrainScore  +
            $EvalScore + $ManagerScore + $karGrpScore +
            $inventionScore + $paperReleaseScore + $presentScore + $TranslateScore + $PlanScore + $Item_7 + $DocScore + $Item_9 + $TechRepScore +
            $totalSug +
            $totalMeritNemooneh + 
            $resMerit14 + $CustomerOrientedScore + $Item_10 + $CulturalPshipScore + $Item_12 +
            $totalMerit ;

    if ($res1[0]['grade'] == 1) {
        $nextGradeTitle = "مهارتی";
        $MinScore = 470;
    } elseif ($res1[0]['grade'] == 2) {
        $nextGradeTitle = "3";
        $MinScore = 680;
    } elseif ($res1[0]['grade'] == 3) {
        $nextGradeTitle = "2";
        $MinScore = 950;
    } elseif ($res1[0]['grade'] == 4) {
        $nextGradeTitle = "1";
        $MinScore = 1200;
    }
    
    
 
//...............................................................
    if ($ResPInfo[0]['sex'] == 1)
        $SexTitle = " جناب آقای ";
    else
        $SexTitle = " سرکار خانم ";

//.............................................................................

    if ($_POST['registerEmpGradeDoc']) {

        $ItemA1 = ($EducLevelScore > 0 ) ? $EducLevelScore : 0;
        $ItemA2 = ($RelatedScroe > 0 ) ? $RelatedScroe : 0;
        $ItemA3 = ($GradeScore > 0 ) ? $GradeScore : 0;
        $ItemA4 = ($LocScore > 0 ) ? $LocScore : 0;

        $ItemB1 = ($TrainScore > 0 ) ? $TrainScore : 0;
        $ItemB2 = (( $swGrade + $Item_1 ) > 0 ) ? ( $swGrade + $Item_1 ) : 0;
        $ItemB3 = ($LanguageScore > 0 ) ? $LanguageScore : 0;
        $ItemB4 = ($Item_11 > 0 ) ? $Item_11 : 0;

        $ItemC1 = ($EvalScore > 0 ) ? $EvalScore : 0;
        $ItemC2 = ($ManagerScore > 0 ) ? $ManagerScore : 0;
        $ItemC3 = ($karGrpScore > 0 ) ? $karGrpScore : 0;

        $ItemD1 = ($inventionScore > 0 ) ? $inventionScore : 0;
        $ItemD2 = ($paperReleaseScore > 0 ) ? $paperReleaseScore : 0;
        $ItemD3 = ($presentScore > 0 ) ? $presentScore : 0;
        $ItemD4 = ($TranslateScore > 0 ) ? $TranslateScore : 0;
        $ItemD5 = ($PlanScore > 0 ) ? $PlanScore : 0;
        $ItemD6 = ($Item_7 > 0) ? $Item_7 : 0;
        $ItemD7 = ($DocScore > 0 ) ? $DocScore : 0;
        $ItemD8 = ($Item_9 > 0 ) ? $Item_9 : 0;
        $ItemD9 = ($TechRepScore > 0 ) ? $TechRepScore : 0;
        $ItemD10 = ($ImpSug > 0 ) ? $ImpSug : 0;
        $ItemD11 = ($AccSug > 0 ) ? $AccSug : 0;
        $ItemD12 = ($UniSug > 0 ) ? $UniSug : 0;
        $ItemD13 = ($UnitSug > 0 ) ? $UnitSug : 0;
        $ItemD14 = ($resMerit11 > 0 ) ? $resMerit11 : 0;
        $ItemD15 = ($resMerit12 > 0 ) ? $resMerit12 : 0;
        $ItemD16 = ($resMerit13 > 0 ) ? $resMerit13 : 0;
        $ItemD17 = ($CustomerOrientedScore > 0 ) ? $CustomerOrientedScore : 0;
        $ItemD18 = ($Item_10 > 0 ) ? $Item_10 : 0;
        $ItemD19 = ($CulturalPshipScore > 0 ) ? $CulturalPshipScore : 0;
        $ItemD20 = ($Item_12 > 0 ) ? $Item_12 : 0;
        $ItemD21 = ($resMerit1 > 0 ) ? $resMerit1 : 0;
        $ItemD22 = ($resMerit3 > 0 ) ? $resMerit3 : 0;
        $ItemD23 = ($resMerit2 > 0 ) ? $resMerit2 : 0;
        $ItemD24 = ($resMerit4 > 0 ) ? $resMerit4 : 0;
        $ItemD25 = ($resMerit5 > 0 ) ? $resMerit5 : 0;
        $ItemD26 = ($resMerit6 > 0 ) ? $resMerit6 : 0;
        $ItemD27 = ($resMerit7 > 0 ) ? $resMerit7 : 0;
        $ItemD28 = ($resMerit8 > 0 ) ? $resMerit8 : 0;
        $ItemD29 = ($ItemMerit9 > 0 ) ? $ItemMerit9 : 0;

        $qry = " select count(*) cn  from UpgradeRank where PersonID = " . $res2[0]['PersonID'] . " and PrevGradeDate = '" . $res2[0]['execute_date'] . "'";
        $ExistRes = PdoDataAccess::runquery($qry);

        if (count($ExistRes) > 0 && $ExistRes[0]['cn'] > 0) {
            PdoDataAccess::runquery(" delete from UpgradeRank where PersonID = " . $res2[0]['PersonID'] . " and PrevGradeDate = '" . $res2[0]['execute_date'] . "'");
        }

        $query = " insert into UpgradeRank (PersonID ,PrevGrade, PrevGradeDate, EducLevel, Grade, DutyYear, ItemA1, ItemA2, ItemA3, ItemA4, ItemB1, ItemB2, ItemB3, ItemB4, 
									    ItemC1, ItemC2, ItemC3, ItemD1, ItemD2 , ItemD3 , ItemD4 , ItemD5 , ItemD6 , ItemD7 , ItemD8 , ItemD9 , ItemD10 , ItemD11 , 
									    ItemD12 , ItemD13, ItemD14, ItemD15, ItemD16, ItemD17, ItemD18, ItemD19, ItemD20, ItemD21, ItemD22, ItemD23, ItemD24, ItemD25, 
									    ItemD26, ItemD27, ItemD28, ItemD29) values 
								 	   (" . $res2[0]['PersonID'] . "," . $res1[0]['grade'] . ",'" . $res2[0]['execute_date'] . "'," . $Madrak . "," . ($res1[0]['grade'] + 1) . "," . $totalYear . ",
									    " . $ItemA1 . "," . $ItemA2 . "," . $ItemA3 . "," . $ItemA4 . "," . $ItemB1 . "," . $ItemB2 . "," . $ItemB3 . "," . $ItemB4 . ",
										" . $ItemC1 . "," . $ItemC2 . "," . $ItemC3 . ",
										" . $ItemD1 . "," . $ItemD2 . "," . $ItemD3 . "," . $ItemD4 . "," . $ItemD5 . "," . $ItemD6 . "," . $ItemD7 . "," . $ItemD8 . "," . $ItemD9 . "," . $ItemD10 . ",
										" . $ItemD11 . "," . $ItemD12 . "," . $ItemD13 . "," . $ItemD14 . "," . $ItemD15 . "," . $ItemD16 . "," . $ItemD17 . "," . $ItemD18 . "," . $ItemD19 . ",
										" . $ItemD20 . "," . $ItemD21 . "," . $ItemD22 . "," . $ItemD23 . "," . $ItemD24 . "," . $ItemD25 . "," . $ItemD26 . "," . $ItemD27 . "," . $ItemD28 . "," . $ItemD29 . ")";

        PdoDataAccess::runquery($query);
//echo PdoDataAccess::GetLatestQueryString(); die();	
    }
    ?>

    <html dir='rtl'>
        <head>
            <style>
                .reportGenerator {border-collapse:collapse;border: 1px solid black;font-family: tahoma;font-size: 8pt;
                                  text-align: center;width: 100%;padding: 2px;}
                .reportGenerator .header {color: white;font-weight: bold;background-color:#3F5F96}
                .reportGenerator td {border: 1px solid #555555;height: 20px;}

            </style>
            <title> شناسنامه ارتقاء رتبه اعضای غیرهیئت علمی</title>
            <meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
            <link rel=stylesheet href="/HumanResources/css/writ.css">
        </head>
        <body>
        <center>

            <!--جهت ذخیره و تایید ارتقاء رتبه فرد -->

            <form class="noPrint" id="MainForm" method="post">				
                <input type="hidden" name="staff_id" value="<?= $_POST['staff_id'] ?>">  
                <input type="submit" name="registerEmpGradeDoc" value="تائید فرم ارتقاء رتبه">				
            </form>

            <table width="90%" cellpadding="0" cellspacing="0">
                <tr height="50px">
                        <!--<td width="20%"><img src="/HumanResources/img/fum_symbol.jpg" width="70px" height="80px"></td>!-->
                    <td align="center" style="font-family:b titr" colspan="3"> شناسنامه ارتقاء رتبه اعضای غیرهیئت علمی</td>

                </tr>
            </table>

            <table style="background-image: url('/HumanResources/img/fiche_bg.gif'); background-size: 500px 600px;background-repeat: no-repeat; background-position: center;text-align: right;border-collapse:collapse;width:98%" class="reportGenerator" cellpadding="4" cellspacing="0">
                <tr>
                    <td><font style="font-family:tahoma;font-weight: bold;font-size:8pt"> شماره شناسایی : &nbsp;</font>
    <?= $ResPInfo[0]['staff_id'] ?>
                    </td>
                    <td colspan="3" ><font style="font-family:tahoma;font-weight: bold;font-size:8pt"> نام : &nbsp;</font>
    <?= $ResPInfo[0]['pfname'] ?>				 &nbsp;&nbsp;&nbsp;&nbsp;
                        <font style="font-family:tahoma;font-weight: bold;font-size:8pt">نام خانوادگی :&nbsp; </font>
    <?= $ResPInfo[0]['plname'] ?>  	 &nbsp;&nbsp;&nbsp;&nbsp;
                        <font style="font-family:tahoma;font-weight: bold;font-size:8pt">عنوان پست سازمانی :</font>
    <?= $ResPInfo[0]['PostTitle'] ?> &nbsp;&nbsp;&nbsp;&nbsp;
                        <font style="font-family:tahoma;font-weight: bold;font-size:8pt">رشته شغلی :</font>
    <?= $ResPInfo[0]['JobTitle'] ?> &nbsp;&nbsp;&nbsp;&nbsp;


                    </td>																
                </tr>
                <tr>
                    <td><font style="font-family:tahoma;font-weight: bold;font-size:8pt"> 
                        دوره آموزشی : &nbsp;</font> <?=$TrainScore2?> &nbsp;
                        ساعت
                    &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
                    <font style="font-family:tahoma;font-weight: bold;font-size:8pt"> 
                     میانگین نمره ارزشیابی :&nbsp;</font>                     
                     
                    <?= round($MinEval) ?> &nbsp;
                    </td>
                    <td><font style="font-family:tahoma;font-weight: bold;font-size:8pt">  تاریخ اخذ آخرین رتبه(قانون خدمات کشوری): </font>
    <?= DateModules::miladi_to_shamsi($res2[0]['execute_date']); ?> </td>
                    <td colspan="3"><font style="font-family:tahoma;font-weight: bold;font-size:8pt">رشته تحصیلی : </font> 
    <?= $ResPInfo[0]['StudyTitle'] . '-' . $ResPInfo[0]['BranchTitle'] ?> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <font style="font-family:tahoma;font-weight: bold;font-size:8pt">	مدرک تحصیلی : &nbsp; </font> 
    <?= $ResPInfo[0]['EducTitle'] ?>&nbsp;&nbsp;&nbsp;&nbsp;
                        <font style="font-family:tahoma;font-weight: bold;font-size:8pt">معدل مدرک : &nbsp;</font> 
                        <?= $ResPInfo[0]['grade'] ?> 
                    </td>											
                </tr>
                <tr height='30px' ><td colspan ="4" style="font-family:tahoma;font-weight: bold;font-size:8pt" > سنوات تجربی لازم برای ارتقاء رتبه بالاتر  </td></tr>
                <tr>
                    <td>مدرک تحصیلی : &nbsp; <?= $resMadrak[0]['title'] ?> </td>
                    <td >رتبه فعلی : &nbsp;	<?= $ResPInfo[0]['GradeTitle'] ?></td> 				
                    <td >مدت سنوات : &nbsp; <?= $totalYear ?> </td>
                    <td>&nbsp;</td>
                </tr>
                <tr style="height:100px">

                    <td colspan ="1" style="padding:0;border-collapse: collapse;width:350px" > 
                        <table width="100%"  height="100%" cellpadding="5px" class="reportGenerator" >
                            <tr style="font-family:tahoma;font-weight: bold;font-size:8pt;"  >
                                <td colspan="2" width="80%" align="right">الف. سوابق تحصیلی</td><td width="10%">امتیاز</td><td width="10%">سقف</td>
                            </tr>
                            <tr> 
                                <td colspan="2" align='right'> مدرک تحصیلی </td>
                                <td><?= $EducLevelScore ?></td>
                                <td rowspan="4"><?= $scoreA1 ?></td>
                            </tr>		 							
                            <tr>
                                <td colspan="2" align='right'>میزان ارتباط مدرک تحصیلی با شغل مورد تصدی </td>
                                <td><?= $RelatedScroe ?></td>

                            </tr>
                            <tr>
                                <td colspan="2" align='right'> معدل مدرک تحصیلی  </td>
                                <td><?= $GradeScore ?></td>

                            </tr>
                            <tr>
                                <td colspan="2" align='right'> محل اخذ مدرک تحصیلی </td>
                                <td><?= $LocScore ?></td>												
                            </tr>

                        </table>
                    </td>

                    <td colspan ="2" style="padding:0;" > 
                        <table width="100%"  height="100%" style="border-collapse: collapse;" cellpadding="5px" class="reportGenerator" >
                            <tr style="font-family:tahoma;font-weight: bold;font-size:8pt;" >
                                <td colspan="2" width="80%" align="right" > ب. سوابق آموزشی 
                                   (280 امتیاز)
                                </td><td width="10%">امتیاز</td><td width="10%">سقف</td>
                            </tr>
                            <tr>
                                <td colspan="2" align='right'>     فراگیری و ارائه دوره های آموزشی  </td>
                                <td><?= $TrainScore ?></td>
                                <td>150</td>
                            </tr>											
                            <tr>
                                <td colspan="2" align='right'> میزان تسلط به استفاده از نرم افزارها </td>
                                <td><?= ( $swGrade + $Item_1 ) ?></td>
                                <td>100</td>
                            </tr>
                            <tr>
                                <td colspan="2" align='right'>  میزان تسلط به زبان های خارجی </td>
                                <td><?= $LanguageScore ?></td>
                                <td>30</td>
                            </tr>
                            <tr>
                                <td colspan="2" align='right'>    توسعه فردی و تسلط به قوانین و مقررات جاری و فرادستی </td>
                                <td><?= $Item_11 ?></td>
                                <td>28</td>
                            </tr>											
                        </table>
                    </td>

                    <td colspan ="1" style="padding:0;" > 
                        <table width="100%"  height="100%" style="border-collapse: collapse;" cellpadding="5px" class="reportGenerator" >
                            <tr style="font-family:tahoma;font-weight: bold;font-size:8pt;" >
                                <td colspan="2" width="80%" align="right" > ج. سوابق اجرایی و تجربی </td><td width="10%">امتیاز</td><td width="10%"> سقف</td>
                            </tr>  
                            <tr>
                                <td colspan="2" align='right'> سنوات خدمت  </td>
                                <td><?= $EvalScore ?></td>
                                <td><?= $scoreC1 ?></td>
                            </tr>											
                            <tr>
                                <td colspan="2" align='right'>  سمت اجرایی </td>
                                <td><?= $ManagerScore ?></td>
                                <td rowspan="2" >50</td>
                            </tr>
                            <tr>
                                <td colspan="2" align='right'>عضویت در کمیته ها، کارگروهها و... &nbsp;</td>
                                <td><?= $karGrpScore ?></td>																		
                            </tr><tr>
                                <td colspan="4" > &nbsp;</td>												
                            </tr>

                        </table>
                    </td>

                </tr>

                <tr height='30px' ><td colspan ="4" style="font-family:tahoma;font-weight: bold;font-size:8pt" >د. فعالیت های علمی، پژوهشی و فرهنگی</td></tr>

                <tr  height="290px">
                    <td colspan ="1" style="padding:0;vertical-align: top;" > 
                        <table width="100%"  height='313px' style="border-collapse: collapse;vertical-align: top" cellpadding="5px" class="reportGenerator" >
                            <tr style="font-family:tahoma;font-weight: bold;font-size:8pt;" >
                                <td colspan="2" width="80%">&nbsp;</td><td width="10%">امتیاز</td><td width="10%">سقف</td>
                            </tr>
                            <tr>
                                <td colspan="2" align='right'>اکتشافات و اختراعات </td>
                                <td><?= $inventionScore ?></td>
                                <td>11</td>
                            </tr>

                            <tr>
                                <td colspan="2" align='right' >انتشار مقالات در مجلات معتبر </td>
                                <td><?= $paperReleaseScore ?></td>
                                <td>14</td>
                            </tr>

                            <tr>
                                <td colspan="2" align='right' > ارائه مقالات در سمینارهای علمی </td>
                                <td><?= $presentScore ?></td>
                                <td>11</td>
                            </tr>

                            <tr>
                                <td colspan="2" align='right'>
                                    تالیف و ترجمه کتاب 
                                </td>
                                <td><?= $TranslateScore ?></td>
                                <td>30</td>
                            </tr>

                            <tr style='height:33px'>
                                <td colspan="2" align='right'>	ارائه طرح های تحقیقاتی 	</td>
                                <td><?= $PlanScore ?></td>
                                <td>30</td>
                            </tr>

                            <tr>
                                <td colspan="2" align='right' >ثبت اختراع و کارهای بدیع هنری </td>
                                <td><?= $Item_7 ?></td>
                                <td>11</td>
                            </tr>			

                            <tr>
                                <td colspan="2" align='right' >مستند سازی فعالیت ها و تجربیات  </td>
                                <td><?= $DocScore ?></td>
                                <td>30</td>
                            </tr>	
                            <tr>
                                <td colspan="2" align='right' >احصاء فرایندها و روش های انجام کار  </td>
                                <td><?= $Item_9 ?></td>
                                <td>30</td>
                            </tr>	
                            <tr  height="86px" >
                                <td colspan="2" align='right' >ارائه گزارشهای تخصصی شغلی<br><br><br><br><br></td>
                                <td><?= $TechRepScore ?><br><br><br><br><br></td>
                                <td>11<br><br><br><br><br></td>
                            </tr> 							
                        </table>									
                    </td>								

                    <td colspan ="2" style="padding:0;vertical-align:top">
                        <table width="100%" height='313px' style="border-collapse: collapse;vertical-align: top" cellpadding="5px" class="reportGenerator" >
                            <tr style="font-family:tahoma;font-weight: bold;font-size:8pt;" >
                                <td colspan="2" width="80%" align='right' > ارائه پیشنهادهای نو و ابتکاری :
                                (56 امتیاز)
                                </td><td width="10%">امتیاز</td><td width="10%">سقف </td>
                            </tr>
                            <tr>
                                <td colspan="2" align='right' >  اجرا شده </td>
                                <td><?= $ImpSug ?></td>
                                <td>56</td>
                            </tr>  
                            <tr>
                                <td colspan="2" align='right' >   پذیرفته شده </td>
                                <td><?= $AccSug ?></td>
                                <td>40</td>
                            </tr>  
                            <tr>
                                <td colspan="2" align='right' >  ارائه شده در سطح دانشگاه </td>
                                <td><?= $UniSug ?></td>
                                <td>25</td>
                            </tr>  
                            <tr>
                                <td colspan="2" align='right' >  ارائه شده در سطح واحد </td>
                                <td><?= $UnitSug ?></td>
                                <td>11</td>
                            </tr> 

                            <tr style="font-family:tahoma;font-weight: bold;font-size:8pt;" >
                                <td colspan="2" width="80%" align='right' >     کسب عنوان عضو منتخب و نمونه: 
                                (30 امتیاز)
                                </td><td width="10%">امتیاز</td><td width="10%">سقف </td>
                            </tr>
                            <tr>
                                <td colspan="2" align='right' >  درسطح کشور  </td>
                                <td><?= $resMerit11 ?></td>
                                <td>30</td>
                            </tr> 
                            <tr>
                                <td colspan="2" align='right' > درسطح دانشگاه </td>
                                <td><?= $resMerit12 ?></td>
                                <td>24</td>
                            </tr> 
                            <tr>
                                <td colspan="2" align='right' >  درسطح واحد </td>
                                <td><?= $resMerit13 ?></td>
                                <td>18</td>
                            </tr> 

                            <tr>
                                <td colspan="2" align='right' >  تکریم ارباب رجوع </td>
                                <td><?= $CustomerOrientedScore ?></td>
                                <td>21</td>
                            </tr>												
                            <tr>
                                <td colspan="2" align='right' >  تعظیم شعاءر اسلامی و مذهبی  </td>
                                <td><?= $Item_10 ?></td>
                                <td>24</td>
                            </tr>
                            <tr>
                                <td colspan="2" align='right' >  مشارکت در فعالیت های فرهنگی </td>
                                <td><?= $CulturalPshipScore ?></td>
                                <td>6</td>
                            </tr>                                                                                               
                            <tr>
                                <td colspan="2" align='right' >خدمات برجسته (پایه تشویقی)</td>
                                <td><?= $Item_12 ?></td>
                                <td>22</td>
                            </tr>    
                        </table>

                    </td>

                    <td colspan ="1" style="padding:0;vertical-align: top;" >
                        <table width="100%" height='313px' style="border-collapse: collapse;vertical-align: top;border:0" cellpadding="5px" class="reportGenerator" >
                            <tr style="font-family:tahoma;font-weight: bold;font-size:8pt;" >
                                <td colspan="2" width="80%" align='right' >  دریافت لوح تشویق و تقدیر :  
                                    (50 امتیاز)
                                </td><td width="10%">امتیاز</td><td width="10%"> سقف</td>
                            </tr>
                            <tr>
                                <td colspan="2" align='right' >رئیس جمهور </td>
                                <td><?= $resMerit1 ?></td>
                                <td>52</td>
                            </tr>  
                            <tr>
                                <td colspan="2" align='right' > معاون رئیس جمهور </td>
                                <td><?= $resMerit3 ?></td>
                                <td>48</td>
                            </tr> 
                            <tr>
                                <td colspan="2" align='right' > وزیر</td>
                                <td><?= $resMerit2 ?></td>
                                <td>44</td>
                            </tr> 
                            <tr>
                                <td colspan="2" align='right' >  معاون وزیر </td>
                                <td><?= $resMerit4 ?></td>
                                <td>40</td>
                            </tr> 
                            <tr>
                                <td colspan="2" align='right' > استاندار</td>
                                <td><?= $resMerit5 ?></td>
                                <td>40</td>
                            </tr> 
                            <tr>
                                <td colspan="2" align='right' > فرماندار</td>
                                <td><?= $resMerit6 ?></td>
                                <td>36</td>
                            </tr> 
                            <tr>
                                <td colspan="2" align='right' > رئیس دانشگاه</td>
                                <td><?= $resMerit7 ?></td>
                                <td>40</td>
                            </tr> 
                            <tr>
                                <td colspan="2" align='right' > معاون دانشگاه</td>
                                <td><?= $resMerit8 ?></td>
                                <td>36</td>
                            </tr> 
                            <tr height="95px" >
                                <td colspan="2" align='right' >  رئیس دانشکده/مدیر ستادی <br><br><br><br><br><br></td>
                                <td><?= $ItemMerit9 ?><br><br><br><br><br><br></td>
                                <td>32<br><br><br><br><br><br></td>
                            </tr>							
                        </table>

                    </td>										
                </tr>
                <tr>
                    <td colspan="4" style="font-family:tahoma;font-weight: bold;font-size:8pt" >
                        جمع کل امتیازات :  &nbsp; &nbsp;&nbsp; <?= $TotalScore ?>					 

                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; امتیاز سوابق تحصیلی : &nbsp;
    <?= ($EducLevelScore + $RelatedScroe + $GradeScore + $LocScore ) ?>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; امتیاز سوابق آموزشی و تبصره بند7 : 
    <?= $TotalTrainScore ?>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; امتیاز سوابق اجرایی و تجربی :
    <?= ($EvalScore + $ManagerScore + $karGrpScore ) ?>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; امتیاز فعالیت های علمی،پژوهشی و فرهنگی :
    <?=
    ($inventionScore + $paperReleaseScore + $presentScore + $TranslateScore + $PlanScore + $Item_7 + $DocScore + $Item_9 + $TechRepScore +
            $totalSug +
            $totalMeritNemooneh + 
            $resMerit14 + $CustomerOrientedScore + $Item_10 + $CulturalPshipScore + $Item_12 +
            $totalMerit )
    ?>
                    </td>
                </tr>
    <? if ($TotalScore >= $MinScore && $Condition1 == 1 ) { ?>
                    <tr>
                        <td colspan="4" style="font-family:tahoma;font-weight: bold;font-size:8pt"  >حداقل امتیاز مورد نیاز برای رتبه <?= $nextGradeTitle ?>،&nbsp;
        <?= $MinScore ?>&nbsp; امتیاز
                            می باشد. با توجه به کسب امتیاز 
        <?= $TotalScore ?>
                            از عوامل امتیاز آور فوق
                            و
                             
                             &nbsp; <?= $ConTitle ?> 
                             توقف لازم در رتبه قبلی،
        <?= $SexTitle . " " . $ResPInfo[0]['pfname'] . ' ' . $ResPInfo[0]['plname'] ?>
                            
                            از تاریخ 
                            <?=$_POST['ToDate'] ?>
                            حائز دریافت رتبه 
        <?= $nextGradeTitle ?>
                            می باشند.
                        </td>
                    </tr>
                        <? } else { ?>
                    <tr>
                        <td colspan="4" style="font-family:tahoma;font-weight: bold;font-size:8pt"  >حداقل امتیاز مورد نیاز برای رتبه <?= $nextGradeTitle ?>،&nbsp;
                            <?= $MinScore ?>&nbsp; امتیاز
                            می باشد. با توجه به کسب امتیاز 
                            <?= $TotalScore ?>
                            از عوامل امتیاز آور فوق
                            
                             و 
                             &nbsp; <?= $ConTitle ?> 
                             توقف لازم در رتبه قبلی ،
                            <?= $SexTitle . " " . $ResPInfo[0]['pfname'] . ' ' . $ResPInfo[0]['plname'] ?>
  در تاریخ 
                            <?=$_POST['ToDate'] ?>
                            حائز دریافت رتبه 
                            <?= $nextGradeTitle ?>
                            نمی باشند.
                        </td>
                    </tr>
                <? } ?>

            </table>					

            <br>
            <table width="90%" cellpadding="0" cellspacing="0" style="font-weight:bold;font-family:tahoma;font-size: 11px" >

                <tr>
                    <td>حسین ضامنی <br>
                        معاونت مدیریت کارگزینی و رفاه  <br>
                        مسئول کارگروه<br>
                    </td>
                    <td>
                        بهروز شکیبا<br>
                        رئیس اداره کارگزینی<br>
                        عضو کارگروه<br>
                    </td>
                    <td>
                        ناصر دلکلاله<br>
                        کارشناس حوزه معاونت اداری و مالی<br>
                        عضو کارگروه<br>
                    </td>
                    <td>
                        محمد اسحاق شریفی<br>
                        کارشناس مسئول تشکیلات<br>
                        عضو کارگروه<br>
                    </td>
                </tr>
            </table>
            <br><br><br>
        </center>
    </body>
    </html>

    <?
    die();
}
?>

<form id="form_SearchGrad" >
    <center>
        <div>
            <div id="AdvanceSearchDIV">				
            </div>
        </div>
    </center>
</form>