<?php 
//---------------------------
// programmer:	jafarkhani
// create Date:	90.03
//---------------------------
require_once '../../../header.inc.php';
require_once '../../persons/class/education.class.php';
require_once '../../persons/class/devotion.class.php';
require_once '../../../organization/unit.class.php';
require_once '../class/writ_item.class.php';
require_once inc_manage_unit;
require_once inc_QueryHelper;

function summary_doc_list()
{
	 $query = " DROP TABLE if EXISTS tmp_doc_writs ";
     PdoDataAccess::runquery($query);

     $query = " CREATE TABLE tmp_doc_writs
                (
                      auto_id int(11) NOT NULL auto_increment,
                      writ_id int(11) default NULL,
                      writ_ver smallint(6) default NULL,
                      staff_id int(11) default NULL,
                      annual_effect smallint(1) default NULL,
                      show_in_summary_doc smallint(1) default NULL,
                      execute_date datetime NULL ,
                      PRIMARY KEY  (auto_id)
                )type=MYISAM AS
                        SELECT
                                w.writ_id ,
                                w.writ_ver ,
                                w.staff_id ,
                                w.annual_effect ,
                                wst.show_in_summary_doc ,
                                w.execute_date
                        FROM writs w
                                INNER JOIN writ_subtypes wst
                                         ON w.writ_type_id = wst.writ_type_id   AND
                                            w.writ_subtype_id = wst.writ_subtype_id AND
                                            w.person_type = wst.person_type
                        WHERE
                            (w.history_only=0 OR w.history_only IS NULL) AND
                             w.person_type in(". manage_access::getValidPersonTypes() .")
                        ORDER BY staff_id , execute_date , writ_id , writ_ver' " ;

        PdoDataAccess::runquery($query);

        $query = "ALTER TABLE tmp_doc_writs ADD INDEX(writ_id,writ_ver)";
        PdoDataAccess::runquery($query);

        $query = "ALTER TABLE tmp_doc_writs ADD INDEX(staff_id,execute_date,writ_id,writ_ver)";
        PdoDataAccess::runquery($query);

        $query = "  DELETE tmp_doc_writs sw1
                    FROM tmp_doc_writs sw1
                            INNER JOIN tmp_doc_writs sw2
                                     ON sw1.staff_id = sw2.staff_id AND
                                        sw1.execute_date = sw2.execute_date
                                    WHERE (sw1.writ_id > sw2.writ_id OR (sw1.writ_id = sw2.writ_id AND sw1.writ_ver > sw2.writ_ver))";

        PdoDataAccess::runquery($query);

        $query = "DROP TABLE if EXISTS tmp_sum_doc_writs";
        PdoDataAccess::runquery($query);

        $query = "  CREATE TABLE tmp_sum_doc_writs
                    (
                       auto_id int(11) NOT NULL auto_increment,
                       writ_id int(11) default NULL,
                       writ_ver smallint(6) default NULL,
                       staff_id  int null,
                       execute_date datetime null ,
                       annual_effect smallint null ,
                       show_in_summary_doc smallint null ,
                       PRIMARY KEY  (auto_id)
                    )type=MYISAM AS
                    SELECT w1.writ_id , w1.writ_ver , w1.staff_id , w1.execute_date execute_date , w1.annual_effect , w1.show_in_summary_doc
                    FROM tmp_doc_writs w1
                        LEFT OUTER JOIN tmp_doc_writs w0
                            ON w1.auto_id - 1  = w0.auto_id AND w1.staff_id = w0.staff_id
                        LEFT OUTER JOIN tmp_doc_writs w2
                            ON w1.auto_id + 1 = w2.auto_id AND w1.staff_id = w2.staff_id
                    WHERE ((w1.annual_effect <> w0.annual_effect OR w0.writ_id is null)OR(w1.show_in_summary_doc=1))
                    ORDER by w1.staff_id " ;
        PdoDataAccess::runquery($query);

        $query = "DROP TABLE IF EXISTS temp_writs";
        PdoDataAccess::runquery($query);

        $query = "  CREATE TABLE temp_writs TYPE=MyIsam AS
                            SELECT wa.writ_type_id ,
                                   wa.writ_subtype_id ,
                                   wa.staff_id,
                                   wa.emp_mode,
                                   wa.annual_effect,
                                   wa.science_level,
                                   wa.execute_date,
                                   dw2.execute_date end_date ,
                                   wa.writ_id,
                                   wa.writ_ver
                            FROM staff s
                                 INNER JOIN writs w
                                    ON w.writ_id = s.last_writ_id AND w.writ_ver = s.last_writ_ver AND w.staff_id = s.staff_id 
                                 INNER JOIN writs wa
                                    ON wa.staff_id = s.staff_id
                                 INNER JOIN tmp_sum_doc_writs dw
                                       ON(dw.writ_id = wa.writ_id AND dw.writ_ver = wa.writ_ver)
                                 LEFT OUTER JOIN tmp_sum_doc_writs dw2
                                       ON(dw2.auto_id -1 = dw.auto_id and dw2.staff_id = dw.staff_id)
                            WHERE (wa.history_only = 0 OR wa.history_only IS NULL) AND
                                   s.person_type in(". manage_access::getValidPersonTypes() .") AND
                                  (dw.annual_effect <> ".ANNUAL_NOT_CALC." OR dw.show_in_summary_doc=1 OR dw2.auto_id IS NOT NULL) AND
                                   '.sisSession('__sisReportWhereClause') ";
        
           PdoDataAccess::runquery($query);

           $query = " ALTER TABLE temp_writs ADD INDEX(writ_id,writ_ver) ";
           PdoDataAccess::runquery($query);

           $query = " ALTER TABLE temp_writs ADD INDEX(staff_id,execute_date,writ_id,writ_ver) ";
           PdoDataAccess::runquery($query);

           $query = " DELETE temp_writs tw1
                        FROM temp_writs tw1
                                    INNER JOIN temp_writs tw2
                                        ON tw1.staff_id = tw2.staff_id  AND
                                           tw1.execute_date = tw2.execute_date
                        WHERE (tw1.writ_id > tw2.writ_id OR (tw1.writ_id = tw2.writ_id AND tw1.writ_ver > tw2.writ_ver)) ";
           PdoDataAccess::runquery($query);

           $query = "DROP TABLE IF EXISTS temp_person_doc";
           PdoDataAccess::runquery($query);

           $query = "CREATE TABLE temp_person_doc TYPE=MyIsam AS
                                SELECT w.staff_id,
                                       w.science_level,
                                       w.execute_date first_date,
                                       CASE
                                           WHEN w.end_date IS NULL THEN NOW()
                                           ELSE w.end_date
                                       END last_date,
                                       CASE w.annual_effect
                                            WHEN ".ANNUAL_NOT_CALC." THEN 1
                                            ELSE 2
                                       END accept_id,
                                       writ_id twrit_id ,
                                       writ_ver twrit_ver
                                FROM temp_writs w ";

             PdoDataAccess::runquery($query);

             $query = "ALTER TABLE temp_person_doc ADD INDEX(twrit_id)" ;
             PdoDataAccess::runquery($query);

             $query = "DROP TABLE IF EXISTS temp_history";
             PdoDataAccess::runquery($query);

             $query = " CREATE TABLE temp_history TYPE=MyIsam AS
                                        (SELECT 1 AS rowno,
                                               p.PersonID,
                                               NULL AS post_title,
                                               p.military_from_date AS from_date,
                                               p.military_to_date AS to_date,
                                               (p.military_duration DIV 12) AS d_year,
                                               (p.military_duration - ((p.military_duration DIV 12) * 12)) AS d_month,
                                               0 AS d_day,
                                                               \'قابل قبول\' AS accept_type,
                                               \'سربازي\' AS org_title,
                                               \'سربازي\' AS emp_state_title,
                                               NULL AS writ_id,
                                               NULL AS writ_date
                                        FROM persons p
                                        WHERE p.military_duration IS NOT NULL AND
                                              p.military_from_date IS NOT NULL AND
                                              p.military_to_date IS NOT NULL)
				UNION
                                        (SELECT 2 AS rowno,
                                               pe.PersonID,
                                               pe.title AS post_title,
                                               pe.from_date,
                                               pe.to_date,
                                               retired_duration_year AS d_year,
                                               retired_duration_month AS d_month,
                                               retired_duration_day AS d_day,
                                               \'قابل قبول\' AS accept_type,
                                               pe.organization AS org_title,
                                               emps.Title emp_state_title,
                                               NULL AS writ_id,
                                               NULL AS writ_date
                                        FROM person_employments pe  " .QueryHelper::makeBasicInfoJoin(BINFTYPE_emp_state, "emps", "pe.emp_state") ." )
				UNION
                                        (SELECT 3 AS rowno,
                                               pe.PersonID,
                                               pe.title AS post_title,
                                               pe.from_date,
                                               pe.to_date,
                                               FLOOR(((duration_year - retired_duration_year)*365.25 + (duration_month - retired_duration_month)*30.4375 + (duration_day - retired_duration_day)) / 365.25) AS d_year,
                                               FLOOR((((duration_year - retired_duration_year)*365.25 + (duration_month - retired_duration_month)*30.4375 + (duration_day - retired_duration_day)) -FLOOR(((duration_year - retired_duration_year)*365.25 + (duration_month - retired_duration_month)*30.4375 + (duration_day - retired_duration_day)) / 365.25)*365.25) / 30.4375) AS d_month ,
                                               (((duration_year - retired_duration_year)*365.25 + (duration_month - retired_duration_month)*30.4375 + (duration_day - retired_duration_day))-
                                               FLOOR(((duration_year - retired_duration_year)*365.25 + (duration_month - retired_duration_month)*30.4375 + (duration_day - retired_duration_day)) / 365.25) *365.25
                                               -ROUND(FLOOR((((duration_year - retired_duration_year)*365.25 + (duration_month - retired_duration_month)*30.4375 + (duration_day - retired_duration_day)) -FLOOR(((duration_year - retired_duration_year)*365.25 + (duration_month - retired_duration_month)*30.4375 + (duration_day - retired_duration_day)) / 365.25)*365.25) / 30.4375)*30.4375)) AS d_day,
                                               \'غير قابل قبول\' AS accept_type,
                                               pe.organization AS org_title,
                                               emps.Title emp_state_title , 
                                               NULL AS writ_id,
                                               NULL AS writ_date
                                        FROM person_employments pe  " .QueryHelper::makeBasicInfoJoin(BINFTYPE_emp_state, "emps", "pe.emp_state") ." )
				UNION
                                        (SELECT
                                              4 AS rowno,
                                              s.PersonID,
                                              CASE
                                                    WHEN w.person_type =".HR_PROFESSOR." THEN slvl.Title
                                                    WHEN w.person_type =".HR_WORKER." THEN j.title
                                                    ELSE p.title
                                              END post_title ,
                                              pd.first_date AS from_date,
                                              pd.last_date AS to_date,
                                              FLOOR(DATEDIFF(pd.last_date,pd.first_date) / 365.25) AS d_year,
                                              FLOOR(  (  DATEDIFF(pd.last_date,pd.first_date) - FLOOR(DATEDIFF(pd.last_date,pd.first_date) / 365.25)*365.25  ) / 30.4375 ) AS d_month,
                                              DATEDIFF(pd.last_date,pd.first_date) - FLOOR(DATEDIFF(pd.last_date,pd.first_date) / 365.25)*365.25 -  FLOOR(  (  DATEDIFF(pd.last_date,pd.first_date) - FLOOR(DATEDIFF(pd.last_date,pd.first_date) / 365.25)*365.25  ) / 30.4375 )*30.4375   AS d_day,
                                              CASE pd.accept_id
                                                   WHEN  1 THEN  \'غير قابل قبول\'
                                                   ELSE \'قابل قبول\'
                                              END AS accept_type,
                                              CASE pd.accept_id
                                                   WHEN 1 THEN empd.Title
                                                   ELSE o.ptitle
                                              END AS org_title,
                                              emps.Title emp_state_title,
                                              w.send_letter_no AS writ_id,
                                              w.execute_date AS writ_date
                                        FROM temp_person_doc pd
                                             INNER JOIN writs w
                                                   ON(w.writ_id = pd.twrit_id and w.writ_ver=pd.twrit_ver)
                                             INNER JOIN staff s
                                                   ON(w.staff_id = s.staff_id)
                                             LEFT OUTER JOIN org_new_units o
                                                   ON(w.ouid = o.ouid)
                                             LEFT OUTER JOIN position p
                                                   ON p.post_id = w.post_id
                                             LEFT OUTER JOIN jobs j
                                                   ON w.job_id = j.job_id ".
                                             QueryHelper::makeBasicInfoJoin(BINFTYPE_science_level, "slvl", "w.science_level").
                                             QueryHelper::makeBasicInfoJoin(BINFTYPE_emp_mode, "empd", "w.emp_mode").
                                             QueryHelper::makeBasicInfoJoin(BINFTYPE_emp_state, "emps", "w.emp_state").
                                     " WHERE w.history_only = 0) ";
           
    //..............................................................................
    $query = " DROP TABLE IF EXISTS temp_exe_post ";
    PdoDataAccess::runquery($query);

    $query = " CREATE TABLE temp_exe_post Type=MyIsam AS
                        SELECT staff_id,SUBSTRING(MAX(CONCAT(from_date , row_no)),11) max_row
                            FROM professor_exe_posts
                                 GROUP BY staff_id";
    PdoDataAccess::runquery($query);

    $query = " ALTER TABLE temp_exe_post ADD INDEX(staff_id,max_row) ";
    PdoDataAccess::runquery($query);

     $query = "DROP TABLE IF EXISTS temp_devotion " ;
	 PdoDataAccess::runquery($query);

     $query = " CREATE TABLE temp_devotion Type=MyIsam AS
					SELECT
					      PersonID,
					      SUM(CASE devotion_type
					              WHEN ".FIGHTING_DEVOTION." THEN amount
					              ELSE 0
					          END) razm,
					      MAX(CASE devotion_type
					              WHEN ".SACRIFICE_DEVOTION." THEN amount
					              ELSE 0
					          END) janbaz
					FROM  person_devotions
					WHERE devotion_type IN(".FIGHTING_DEVOTION.",".SACRIFICE_DEVOTION.")
					GROUP BY PersonID " ;
    
     PdoDataAccess::runquery($query);

     $query = "ALTER TABLE temp_devotion ADD INDEX(PersonID)";
     PdoDataAccess::runquery($query);

     $query = "  SELECT   s.staff_id,
                          p.plname,
                          p.pfname,
                          p.father_name ,
                          p.birth_date ,
                          idcard_no ,
                          national_code ,
                          concat(st.ptitle ,'-',ct.ptitle )  birth_place ,
                          w.base ,
                          sf.ptitle AS field_title,
                          sb.ptitle AS branch_title,
                          pe.doc_date,
                          c.ptitle AS country_title,
                          u.ptitle AS university_title,
                          td.razm ,
                          td.janbaz,
                          o.ptitle AS unit_title,
                          o.ouid ,
                          po.post_no,
                          po.title AS last_post_title,
                          po2.title AS exe_post_title,
                          pep.from_date AS exe_post_from,
                          slvl.Title    science_level_title ,
                          wtt.Title worktime_type_title ,
                          mlt.Title military_type_title ,
                          edl.Title education_level_title ,
                          emps.Title emp_state_title 

        FROM    staff s      
					     INNER JOIN persons p
					           ON(s.PersonID = p.PersonID)
					     LEFT OUTER JOIN states st
					     		ON (st.state_id = p.birth_state_id)
					     LEFT OUTER JOIN cities ct
					     		ON (ct.city_id = p.birth_city_id)
					     LEFT OUTER JOIN writs w
					           ON(s.last_writ_ver = w.writ_ver AND s.last_writ_id = w.writ_id)
					     LEFT OUTER JOIN person_educations pe
					           ON(s.PersonID = pe.PersonID AND w.education_level = pe.education_level AND w.sfid = pe.sfid AND (w.sbid = pe.sbid OR w.sbid IS NULL))
					     LEFT OUTER JOIN study_fields sf
					          ON(w.sfid = sf.sfid)
					     LEFT OUTER JOIN study_branchs sb
					           ON(w.sfid = sb.sfid AND w.sbid = sb.sbid)
					     LEFT OUTER JOIN countries c
					           ON(pe.country_id = c.country_id)
					     LEFT OUTER JOIN universities u
					           ON(pe.country_id = u.country_id AND pe.university_id = u.university_id)
					     LEFT OUTER JOIN org_new_units o
					           ON(w.ouid = o.ouid)
					     LEFT OUTER JOIN position po
					           ON(w.post_id = po.post_id)
					     LEFT OUTER JOIN temp_exe_post tep
					           ON(s.staff_id = tep.staff_id)
					     LEFT OUTER JOIN professor_exe_posts pep
					           ON(tep.staff_id = pep.staff_id AND tep.max_row = pep.row_no AND (pep.to_date IS NULL OR pep.to_date>=CURDATE()))
					     LEFT OUTER JOIN position po2
					           ON(pep.post_id = po2.post_id)
					     LEFT OUTER JOIN temp_devotion td
					           ON(s.PersonID = td.PersonID) " .
                         QueryHelper::makeBasicInfoJoin(BINFTYPE_science_level, "slvl", "w.science_level") .
                         QueryHelper::makeBasicInfoJoin(BINFTYPE_worktime_type, "wtt", "w.worktime_type") .
                         QueryHelper::makeBasicInfoJoin(BINFTYPE_military_type, "mlt", "w.military_type") .
                         QueryHelper::makeBasicInfoJoin(BINFTYPE_education_level, "edl", "w.education_level") .
                         QueryHelper::makeBasicInfoJoin(BINFTYPE_emp_state, "emps", "w.emp_state") .
         "  WHERE  ((th.d_year<>0 OR th.d_month<>0 OR th.d_day<>0)OR(w.emp_mode IN(11,12,13,14) AND
                     th.writ_date = w.execute_date)) AND
                     s.person_type in(". manage_access::getValidPersonTypes() .") AND
                     s.last_cost_center_id in(" . manage_access::getValidCostCenters() . ") ";
	
         $dt = PdoDataAccess::runquery($query, $whereParam);

	return $dt;
}

function PrintSummary($PersonInfo)
  {

     $qry = " select  th.post_title,
                      th.from_date,
                      th.to_date,
                      th.d_year,
                      th.d_month,
                      th.d_day,
                      th.accept_type,
                      th.org_title,
                      th.emp_state_title,
                      th.writ_id,
                      th.writ_date

               from temp_history th
                        where th.PersonID = ".$PersonInfo["PersonID"] ;

     $history_work = PdoDataAccess::runquery($qry);
     $history_work_items = " ";
      ob_start();
      echo " <tr>
                <td>".($PersonInfo['person_type'] == HR_WORKER) ? 'عنوان شغل' : 'عنوان پست' ."</td>
                <td>از تاريخ</td>
                <td>تا تاريخ</td>
                <td>مدت (روز)</td>
                <td>مدت (ماه)</td>
                <td>مدت (سال)</td>
                <td>نوع پذيرش</td>
                <td>محل خدمت</td>
                <td>نوع خدمت</td>
                <td>شماره حکم</td>
                <td>تاريخ حکم</td>
            </tr>";

     for($j=0;$j<count($history_work); $j++)
     {

       echo "   <tr>
                    <td>".$history_work[$j]['post_title']."</td>
                    <td>".$history_work[$j]['from_date']."</td>
                    <td>".$history_work[$j]['to_date']."</td>
                    <td>".$history_work[$j]['d_year']."</td>
                    <td>".$history_work[$j]['d_month']."</td>
                    <td>".$history_work[$j]['d_day']."</td>
                    <td>".$history_work[$j]['accept_type']."</td>
                    <td>".$history_work[$j]['org_title']."</td>
                    <td>".$history_work[$j]['emp_state_title']."</td>
                    <td>".$history_work[$j]['writ_id']."</td>
                    <td>".$history_work[$j]['writ_date']."</td>
                </tr>
            ";

     }
    $history_work_items = ob_get_contents();
	ob_end_clean();

      $prof1 = '
                <tr>
                    <td>مرتبه :</td>
                    <td>'.$PersonInfo["science_level"].'</td>
                    <td>پايه :</td>
                    <td>'.$PersonInfo["base"].'</td>
                </tr>
               ' ;

    $studyField =  $PersonInfo['field_title']." - ".  $PersonInfo['branch_title'] ;
    $studyPlace =  $PersonInfo['country_title']." - ".$PersonInfo['university_title'] ;
    $unitTitle = manage_units::get_full_title($PersonInfo['ouid']);

    $prof2 = ' <tr>
                    <td>سمت اجرايي :</td>
                    <td>'.$PersonInfo['exe_post_title'].'</td>
                    <td>تاريخ سمت اجرايي :</td>
                    <td>'.$PersonInfo['exe_post_from'].'</td>
               </tr>
             ';

    
    
    $tags =  array('<!--fname-->' => $PersonInfo['pfname'],
                   '<!--plname-->' => $PersonInfo['plname'],
                   '<!--father_name-->' => $PersonInfo['father_name'],
                   '<!--staff_id-->' => $PersonInfo['staff_id'],
                   '<!--birth_date-->' => $PersonInfo['birth_date'],
                   '<!--birth_place-->' => $PersonInfo['birth_place'],
                   '<!--idcard_no-->' => $PersonInfo['idcard_no'],
                   '<!--national_code-->' => $PersonInfo['national_code'],        
                   '<!--prof1-->' => $prof1,        
                   '<!--worktime_type-->' => $PersonInfo['worktime_type'],
                   '<!--military_type-->' => $PersonInfo['military_type'],
                   '<!--education_level-->' => $PersonInfo['education_level'],
                   '<!--field_title-->' =>$studyField,
                   '<!--doc_date-->' => $PersonInfo['doc_date'],
                   '<!--studyPlace-->' => $studyPlace,
                   '<!--razm-->' => $PersonInfo['razm'],
                   '<!--janbaz-->' => $PersonInfo['janbaz'],
                   '<!--ouid-->' => $unitTitle,
                   '<!--emp_state-->' => $PersonInfo['emp_state'],
                   '<!--post_no-->' => $PersonInfo['post_no'],
                   '<!--last_post_title-->' => $PersonInfo['last_post_title'],
                   '<!--prof2-->' => $prof2,
                   '<!--histroy_work-->' => $history_work_items
                  );

                  // مشخص کردن فایل template  مربوط به خلاصه پرونده 
                  $content .= file_get_contents("PrintWritTemplates/Summary_doc_report.htm");
                  $content = str_replace(array_keys($tags), array_values($tags), $content);

                  

     return $content;
  }



function generateReport()
{
	$fileNames = array();
	$dt = summary_doc_list();
	if(count($dt) == 0)
	{
		echo "گزارش هیچ نتیجه ایی در بر ندارد.";
		return;
	}
	
	$writForm = PrintSummary($dt[0]);

	for($i=0; $i<count($dt); $i++)
	{
		echo $writForm	 ; 
		if($i != count($dt)-1)
			echo "<div class='pageBreak'></div>";
	}

	return $fileNames;
}
?>
<html dir='rtl'>
	<head>
		<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
		<link rel=stylesheet href="/HumanResources/css/writ.css">
	</head>
	<body>
		<center>
		<? generateReport();?>
		</center>
	</body>
</html>

	