<?php
//---------------------------
// programmer:	jafarkhani
// create Date:	90.03
//---------------------------
if($_GET['transcript_no']!='pooya'){
    
    require_once '../../../header.inc.php'; 
}
else {
    
    require_once '../../../pooyaheader.inc.php'; 
}
require_once '../../persons/class/education.class.php';
require_once '../../persons/class/devotion.class.php';
require_once '../class/writ_item.class.php';
require_once inc_manage_unit;
require_once inc_QueryHelper;

$transcript_no = isset($_REQUEST["transcript_no"]) ? $_REQUEST["transcript_no"] : "0";
global $equal_payment_system_gdate;
$equal_payment_system_gdate = DateModules::shamsi_to_miladi('1388/01/01');

function writ_print_list($transcript_no, $last_writ_flag)
{  
	$staff_onclause = $last_writ_flag ? 
		"s.staff_id=w.staff_id AND s.last_writ_id=w.writ_id AND s.last_writ_ver=w.writ_ver" : "s.staff_id=w.staff_id";

	$title = $transcript_no == "all" ? 'wts.title transcripts_title,wts.transcript_id , tbl1.mtid,' : "";
	$writ_transcripts_join = $transcript_no == "all" ? '
			LEFT OUTER JOIN writ_transcripts wts ON(w.person_type = wts.person_type AND w.emp_state = wts.emp_state)
			LEFT OUTER JOIN (select max(transcript_id) mtid ,person_type , emp_state
							 from writ_transcripts group by person_type , emp_state) tbl1
				ON(tbl1.person_type = w.person_type AND tbl1.emp_state = w.emp_state)' : "";
	$order = $transcript_no == "all" ? '    wts.transcript_id ASC' : "";

	$query = " select
				s.staff_id,
				w.writ_id,
				w.writ_ver,
				w.staff_id,
				w.person_type,
				w.onduty_year,
				w.onduty_month,
				w.onduty_day,
				w.writ_type_id,
				w.emp_state,
				w.emp_mode,
				w.ref_letter_date,
				w.ref_letter_no,
				w.send_letter_no,
				w.contract_start_date,
				w.contract_end_date,
				w.corrective,
				w.issue_date,
				w.execute_date ,
				wst.time_limited,
				p.sex sex ,w.marital_status , 
				w.notes,w.family_responsible , 
				w.pay_date pay_date,
                                w.MissionPlace , 
				msts.Title marital_status_title,
				edulv.Title education_level_title,

				(w.cur_group-jf.start_group)+1 old_grup,
				(w.cur_group - 4) new_grup,
				w.cur_group ,
				w.description description,
				w.included_children_count,
				w.children_count,
				miltype.Title as military,miltype.InfoID as militaryID , 
				w.job_id,
				sinclv.Title snc_level,
				w.base,
				w.ouid,
				empstt.Title emp_st,
				worktime.Title worktime,
				p.sex gnd,
				p.pfname ps_pfname,
				p.birth_place ps_birth_place,
				p.issue_place,
				p.plname ps_plname,
				p.idcard_no ps_idcard_no,
				p.father_name ps_father_name,
				p.birth_date ps_birth_date,
				p.national_code,
				p.military_from_date,
				p.military_to_date,
				p.military_type,
				p.military_duration_day, 
				p.military_duration ,
				o.ptitle o_ptitle,
				o.ouid o_ouid,
				parentu.ouid ou_ouid ,
				parentu.ptitle ou_ptitle ,
				o.org_unit_type org_sub_unit_type ,
				o.ptitle os_ptitle ,
				punit.ouid po_ouid,
				punit.ptitle po_ptitle,
				psubunit.ptitle pos_ptitle ,
				psubunit.ouid pos_ouid ,
				j.title j_title,
				j.job_group,
				sf.ptitle sf_title,
				sbs.ptitle sbs_title,
				wst.print_title wst_title,
				po.title p_title,
				po.post_no p_post_no,
				po.post_id ,
				po.ouid pouid ,
				posttype.Title post_type,
				cw.ptitle cw_ptitle,
				cb.ptitle cb_ptitle,
				ci.ptitle ci_ptitle,		
				si.ptitle si_ptitle,
				sw.ptitle sw_ptitle,
				jf.title jf_title,
				jsc.title jsc_title,
				jc.title jc_title,
				s.personel_no,
				s.work_start_date,
				c.ptitle country_title,
				sbs.ptitle sbs_title,
				sf.ptitle sf_ptitle,
				sf.sfid , s.unitCode ,
				j.job_id ,
				j.title j_title,
				wt.title writ_type,
				".$title."
				p.PersonID ,
				w.writ_signature_post_title,
				w.writ_signature_post_owner ,w.grade ,
				history_only,
				wst.req_staff_signature, 
				wsi.param8 E_base,                                 
				wsi.param5 ComputeGrade, 
				ba.SBase S_base, 
				ba.IsarValue I_base ,
				ba.TashvighiValue T_base ";

   $query .= " from staff s
			 INNER JOIN writs w ON (" . $staff_onclause . ")

			 LEFT OUTER JOIN writ_subtypes wst
				  ON ((w.person_type = wst.person_type) AND (w.writ_type_id = wst.writ_type_id) AND (w.writ_subtype_id=wst.writ_subtype_id))
			 LEFT OUTER JOIN writ_types wt
				  ON ((w.person_type = wt.person_type) AND (w.writ_type_id = wt.writ_type_id))
			 LEFT OUTER JOIN person_educations pe1
				  ON ((w.education_level = pe1.education_level) AND (w.sfid = pe1.sfid) AND (w.sbid = pe1.sbid) AND (pe1.PersonID = s.PersonID))
			 LEFT OUTER JOIN countries c ON (pe1.country_id = c.country_id)
			 LEFT OUTER JOIN universities u ON ((pe1.university_id = u.university_id) AND (pe1.country_id = u.country_id))
			 LEFT OUTER JOIN study_fields sf ON ((w.sfid = sf.sfid))
			 LEFT OUTER JOIN study_branchs sbs ON ((w.sfid = sbs.sfid)AND(w.sbid=sbs.sbid))
			 LEFT OUTER JOIN persons p ON (s.PersonID = p.PersonID)
			 LEFT OUTER JOIN cities cw ON ((w.work_city_id = cw.city_id) AND (w.work_state_id = cw.state_id))
			 " . $writ_transcripts_join . "
			 LEFT OUTER JOIN states sw ON (cw.state_id=sw.state_id)
			 LEFT OUTER JOIN cities cb ON ((p.birth_city_id = cb.city_id) AND (p.birth_state_id = cb.state_id))
			 LEFT OUTER JOIN cities ci ON ((p.issue_city_id=ci.city_id) AND (p.issue_state_id = ci.state_id))
			 LEFT OUTER JOIN states si ON (ci.state_id=si.state_id)
			 LEFT OUTER JOIN position po ON (w.post_id = po.post_id)
			 LEFT OUTER JOIN org_new_units psubunit ON (psubunit.ouid = po.ouid)
			 LEFT OUTER JOIN org_new_units punit ON (punit.ouid = psubunit.parent_ouid)
			 LEFT OUTER JOIN job_fields jf ON (po.jfid = jf.jfid)
			 LEFT OUTER JOIN job_subcategory jsc ON ((jf.jsid = jsc.jsid) AND (jf.jcid=jsc.jcid))
			 LEFT OUTER JOIN job_category jc ON (jsc.jcid = jc.jcid)
			 LEFT OUTER JOIN org_new_units o ON (w.ouid = o.ouid)
			 LEFT OUTER JOIN org_new_units parentu ON (parentu.ouid = o.parent_ouid)
			 LEFT OUTER JOIN jobs j ON (w.job_id = j.job_id) 
			 
			 LEFT OUTER JOIN writ_salary_items wsi ON w.writ_id = wsi.writ_id AND 
								w.writ_ver = wsi.writ_ver AND w.staff_id = wsi.staff_id AND wsi.salary_item_type_id = 10364 
								
			 LEFT OUTER JOIN (select    PersonID , sum(if(ba.BaseType in (6,2,20,21,22,23,24,25,26,27) and ba.BaseStatus = 'NORMAL' ,ba.BaseValue,0))  TashvighiValue ,
										sum(if(ba.BaseType in (3,4,5,7) and ba.BaseStatus = 'NORMAL' ,ba.BaseValue,0))  IsarValue ,
										sum(if(ba.BaseType in (1 ) and ba.BaseStatus = 'NORMAL' ,ba.BaseValue,0)) SBase

									from bases ba

							  group by PersonID) ba 
							  ON ba.PersonID = s.PersonID " .

			QueryHelper::makeBasicInfoJoin(BINFTYPE_marital_status, "msts", "w.marital_status") .
			QueryHelper::makeBasicInfoJoin(BINFTYPE_education_level, "edulv", "w.education_level") .
			QueryHelper::makeBasicInfoJoin(BINFTYPE_military_type, "miltype", "p.military_type") .
			QueryHelper::makeBasicInfoJoin(BINFTYPE_science_level, "sinclv", "w.science_level") .
			QueryHelper::makeBasicInfoJoin(BINFTYPE_emp_state, "empstt", "w.emp_state") .
			QueryHelper::makeBasicInfoJoin(BINFTYPE_worktime_type, "worktime", "w.worktime_type") .
			QueryHelper::makeBasicInfoJoin(BINFTYPE_post_type, "posttype", "po.post_type");

	$where = "1=1";


	$whereParam = array();

	require_once '../data/writ.data.php';

	MakeAdvanceSearchWhere($where, $whereParam);
		
	if (!empty($_REQUEST["writ_id"]) && !empty($_REQUEST["writ_ver"]) && !empty($_REQUEST["staff_id"]))
	{
		$where .= " AND w.writ_id = :wid AND w.writ_ver=:wver AND w.staff_id=:stid";
		$whereParam[":wid"] = $_REQUEST["writ_id"];
		$whereParam[":wver"] = $_REQUEST["writ_ver"];
		$whereParam[":stid"] = $_REQUEST["staff_id"];
	}
	else
	{	if($last_writ_flag == 1)
			$where .= " AND w.writ_id = s.last_writ_id AND w.writ_ver=s.last_writ_ver";
	}

	if(!empty($_REQUEST["ouid"]))
	{
		$return = QueryHelper::MK_org_units($_REQUEST["ouid"]);
		$where .= " AND " . $return["where"];
		$whereParam = array_merge($whereParam, $return["param"]);
	}

	if ( $transcript_no != 1 && $transcript_no != 2)
		$transcript_no = 2;

	//--------------------------------------------------------------------------
if( $_REQUEST['transcript_no'] == "all") {
     $where .= " AND wts.transcript_id not in (117 , 123 , 127 , 44 , 9 , 114 ) " ;
}
    $query .= " where " . $where;
	$query .= " order by p.plname , p.pfname " ; 
	$dt = PdoDataAccess::runquery_fetchMode($query, $whereParam);

	return $dt;
	
}

function PrintWrit($writ_rec)
{
	$sum = 0;
	$content = "";
	$emp_sal_scores = $emp_sal_vals = "";
	$salary_items = $corrective_detail = $professor_post_type = $org_sub_unit_type = $writ_title = $contact_title =
		$worker_salary_item1_title = $worker_salary_item1_value = $worker_salary_item2_title = $worker_salary_item2_value =
		$worker_other_salary_items = $worker_base_salary = $emp_sal_items = $template_file = $exe_date_title = "";
	$post_title = "";
	$os_ptitle = "";
	$sit2_annual_inc_coef = "";
	$indx = array("الف-","ب-","پ-","ت-","ث-","ج-","چ-","ح-","خ-","د-","ذ-","ر-","ز-","س-","ش-","و-","ه-","ي-");

	global $equal_payment_system_gdate;

	// مشخص كردن template مربوط به حكم جاري
	if($writ_rec['person_type'] == HR_EMPLOYEE)
	{ 
		if($writ_rec['emp_state'] == EMP_STATE_PROBATIONAL_CEREMONIOUS ||
			$writ_rec['emp_state'] == EMP_STATE_APPROVED_CEREMONIOUS)
		{	
			

				
			if((DateModules::CompareDate($writ_rec['execute_date'], $equal_payment_system_gdate) >= 0 && 
			   DateModules::CompareDate($writ_rec['execute_date'], '2013-02-19') < 0 ) || 
					( DateModules::CompareDate($writ_rec['execute_date'], '2013-03-21') >= 0 && 
					  DateModules::CompareDate($writ_rec['execute_date'], '2014-03-21') < 0 )) {
								
				if($writ_rec['emp_mode'] == 14  )
					$template_file = 'employee_ceremonious_writ_print4.htm';
				else 
					$template_file = 'employee_ceremonious_writ_print2.htm';
								
				}
			else if ((DateModules::CompareDate($writ_rec['execute_date'], '2013-02-19')  >= 0 && DateModules::CompareDate($writ_rec['execute_date'], '2013-03-21') < 0 ) || 
					  DateModules::CompareDate($writ_rec['execute_date'], '2014-03-20') > 0) {				
					  
				$template_file = 'employee_ceremonious_writ_print3.htm'; 		
				}					
			else
				$template_file = 'employee_ceremonious_writ_print.htm';
				
				
			
		}
		if($writ_rec['emp_state'] == EMP_STATE_CONTRACTUAL)
		{  
			if((DateModules::CompareDate($writ_rec['execute_date'], $equal_payment_system_gdate) >= 0 && 
			    DateModules::CompareDate($writ_rec['execute_date'], '2013-02-19') < 0) ||
					                        (DateModules::CompareDate($writ_rec['execute_date'], '2013-03-21') >= 0 && DateModules::CompareDate($writ_rec['execute_date'], '2014-03-20') < 0 )  )
				$template_file = 'employee_contractual_writ_print_report2.htm';
			
			else if ((DateModules::CompareDate($writ_rec['execute_date'], '2013-02-19')  >= 0 && DateModules::CompareDate($writ_rec['execute_date'], '2013-03-21') < 0) || 
					  DateModules::CompareDate($writ_rec['execute_date'], '2014-03-20') > 0 ) {
				$template_file = 'employee_contractual_writ_print_report3.htm'; }
			else
				$template_file = 'employee_contractual_writ_print_report.htm';
			
			$writ_title = "قرارداد کارمند پیمانی" ; 
		}
	}
	else if($writ_rec['person_type'] == HR_PROFESSOR )
	{
		if( $writ_rec['emp_state'] == EMP_STATE_PROBATIONAL_CEREMONIOUS ||
			 $writ_rec['emp_state'] == EMP_STATE_APPROVED_CEREMONIOUS)
		{
				$template_file = 'professor_ceremonious_writ_print.htm';
				$writ_title = 'حکم استخدام رسمي اعضاي هيات علمي';
		}
		else if ($writ_rec['emp_state'] == EMP_STATE_SOLDIER_CONTRACTUAL ||
				 $writ_rec['emp_state'] == EMP_STATE_ONUS_SOLDIER_CONTRACTUAL ||
				 $writ_rec['emp_state'] == EMP_STATE_CONTRACTUAL ||  $writ_rec['emp_state'] == 11 )
		{
                    
			$template_file = 'professor_contractual_writ_print_report.htm';
			if($writ_rec['time_limited'] == 1)
				$writ_title = 'قرارداد استخدام پيماني اعضاي هيات علمي';
			else
				$writ_title = $writ_rec['wst_title'].' اعضاي هيات علمي پيماني';
                        
                        if($writ_rec['emp_state'] == 11)
                            				$writ_title = " قرارداد پیمانی (مشروط) اعضای هیئت علمی " ; 

		}

		if($writ_rec['corrective'])
			$template_file = 'professor_ceremonious_writ_print.htm';
		
	}
	
	else if($writ_rec['person_type'] == HR_WORKER)
	{
		$template_file = 'worker_writ_print.htm';
		/*if( DateModules::CompareDate($writ_rec['execute_date'], '2013-02-19') < 0)
			$template_file = 'worker_writ_print.htm';
		else
			$template_file = 'worker_writ_print2.htm';*/
		
		if($writ_rec['corrective'])
			$writ_title = 'طرح طبقه بندي مشاغل <br>حكم اصلاحي كارگزيني';
		else
			$writ_title = 'طرح طبقه بندي مشاغل <br>حکم کارگزيني';
	}
    else if($writ_rec['person_type'] == HR_CONTRACT)
    {
        if( DateModules::CompareDate($writ_rec['execute_date'], '2013-02-19') < 0 ||  
			(DateModules::CompareDate($writ_rec['execute_date'], '2013-03-21') >= 0 && DateModules::CompareDate($writ_rec['execute_date'], '2014-03-20') < 0 )) {
			
			$template_file = 'contract_writ_print.htm';
			$writ_title = "قرار داد انجام کار مشخص"; 
		
		}
	else {
	    $template_file = 'contract_writ_print3.htm';
	    $writ_title = "قرارداد کارکنان قراردادی";
	    }
					
	
	
    }
			
	echo "<div style='display:none'>" . $template_file . "</div>";
    
	// محل تحصيل و زمان اخذ مدرك
	$person_education_rec = manage_person_education::GetEducationLevelByDate($writ_rec['PersonID'], $writ_rec['execute_date']);
   
	$edu_c_ptitle = $person_education_rec['countryTitle'];
	$edu_u_ptitle = $person_education_rec['universityTitle'];
	$education_level_title = $writ_rec['education_level_title'];

    $edu_doc_date = "";
	if($writ_rec['sfid'] != PROFESSIONAL_WITHOUT_CERTIFY)
		$edu_doc_date = DateModules::miladi_to_shamsi($person_education_rec['doc_date']);
	else
		$education_level_title = "" ;
	
	// سوابق خدمت
	$onduty = "";
	if($writ_rec['onduty_year'] > 0)
	{
		$onduty .= $writ_rec['onduty_year'];
		$onduty .=' سال ';
	}
	if($writ_rec['onduty_month'] > 0)
	{
		if($writ_rec['onduty_year'] > 0) 
			$onduty .=' و ';
		
		$onduty .=$writ_rec['onduty_month'];
		$onduty .=' ماه ';
	}
	if($writ_rec['onduty_day'] > 0 || $writ_rec['onduty_year'] > 0)
	{
		if($writ_rec['onduty_month'] > 0)
			$onduty .= "&nbsp;" . ' و ';
		
		$onduty .= $writ_rec['onduty_day'];
		$onduty .=' روز';
	}

	// وضعيت ايثارگري
	$devotion_recSet = manage_person_devotion::get_person_devotions($writ_rec['PersonID'], NULL, 'ALL');
	$devotion_type = "";
	if(is_array($devotion_recSet))
		foreach($devotion_recSet as $devotion_rec)
			$devotion_type .= " " . $devotion_rec['devotionTypeName'] . "  ";
	
	// اقلام مربوط به نظام هماهنگ پرداخت
	if(!$writ_rec['corrective'] &&
		$writ_rec['person_type'] == HR_EMPLOYEE &&
		DateModules::CompareDate($writ_rec['execute_date'], $equal_payment_system_gdate) >= 0 && 
		(DateModules::CompareDate($writ_rec['execute_date'], '2013-02-19') < 0 || 
		(DateModules::CompareDate($writ_rec['execute_date'], '2013-03-20') > 0 && DateModules::CompareDate($writ_rec['execute_date'], '2014-03-21') < 0)  ) )
	{	 
		$item_recset = manage_writ_item::GetAllWritItems("writ_id=? AND writ_ver=? AND staff_id=?",
			array($writ_rec['writ_id'], $writ_rec['writ_ver'], $writ_rec["staff_id"]));
		
		$emp_sal_items = array();
		$Sayer = 0 ;
		$Sayer_Score = 0 ;
		$score_sum = 0 ;
				
		foreach($item_recset as $rec)
		{
			
			if($writ_rec['emp_mode'] == 14 ) {
			
				$emp_sal_items['<!---item_'.$rec['salary_item_type_id'].'-->'] = CurrencyModulesclass::toCurrency($rec['value'],'CURRENCY');
				if($rec['value'])
				{
					$emp_sal_vals[$rec['salary_item_type_id']] = $rec['value'] ;
					$sum += $rec['value'] ;
				}
			
			}		
			else {	
			
					
				if ($rec['salary_item_type_id'] != 57 && $rec['salary_item_type_id'] != 45  )
				{	
					
					$emp_sal_items['<!---item_'.$rec['salary_item_type_id'].'-->'] = CurrencyModulesclass::toCurrency($rec['value'],'CURRENCY')." ".( $rec['value'] < 0 ?  '-' : '' )  ;
					$emp_sal_items['<!---item_'.$rec['salary_item_type_id'].'_score-->'] = $rec['param1'];
					if($rec['value'])
					{
						$emp_sal_vals[$rec['salary_item_type_id']] = $rec['value'] ;
						$sum += $rec['value'] ;
					}
					if($rec['param1'])
					{
						$emp_sal_scores[$rec['salary_item_type_id']] = $rec['param1'] ;
						$score_sum += $rec['param1'];
					}
				} else
				{
					$rec['salary_item_type_id'] = 45;
					$Sayer += $rec['value'];
					$Sayer_Score += $rec['param1'];
					$emp_sal_items['<!---item_'.$rec['salary_item_type_id'].'-->'] = CurrencyModulesclass::toCurrency($Sayer);
					$emp_sal_items['<!---item_'.$rec['salary_item_type_id'].'_score-->']= ( $Sayer_Score != 0 ) ? $Sayer_Score : "" ;
					if($rec['value'])
					{
						$emp_sal_vals[$rec['salary_item_type_id']] = $Sayer ;
						$sum += $rec['value'] ;
					}
					if($rec['param1'] > 0 )
					{
						$emp_sal_scores[$rec['salary_item_type_id']] = $Sayer_Score ;
						$score_sum += $rec['param1'];
					}
				}
			
			}
		}

		if($emp_sal_scores != "") 
					
			$emp_sal_items['<!---fix_salary_score-->'] = CurrencyModulesclass::toCurrency(
				(isset($emp_sal_scores[34]) ? $emp_sal_scores[34] : 0) +
				(isset($emp_sal_scores[35]) ? $emp_sal_scores[35] : 0) +
				(isset($emp_sal_scores[36]) ? $emp_sal_scores[36] : 0)); 
			
				
		if($emp_sal_vals != "") 
				
			$emp_sal_items['<!---fix_salary_value-->'] = CurrencyModulesclass::toCurrency(
				(isset($emp_sal_vals[34]) ? $emp_sal_vals[34] : 0) +
				(isset($emp_sal_vals[35]) ? $emp_sal_vals[35] : 0) +
				(isset($emp_sal_vals[36]) ? $emp_sal_vals[36] : 0)); 
				
	
	
	$emp_sal_items['<!---salary_score-->'] = CurrencyModulesclass::toCurrency($score_sum);
	$emp_sal_items['<!---salary_sum-->'] = CurrencyModulesclass::toCurrency($sum);
		
	}
	else if(!$writ_rec['corrective'] &&
		 $writ_rec['person_type'] == HR_EMPLOYEE &&
		 (DateModules::CompareDate($writ_rec['execute_date'], '2013-02-19') >= 0 || 
		  DateModules::CompareDate($writ_rec['execute_date'], '2014-03-20') >= 0 )  || 
		($writ_rec['person_type'] == HR_CONTRACT && ($writ_rec['execute_date'] >= '2013-02-19' || DateModules::CompareDate($writ_rec['execute_date'], '2014-03-21') >= 0) ) )
	{ 	
		
		$item_recset = manage_writ_item::GetAllWritItems("writ_id=? AND writ_ver=? AND staff_id=?",
			array($writ_rec['writ_id'], $writ_rec['writ_ver'], $writ_rec["staff_id"]));
		
		$emp_sal_items = array();
		$Sayer = 0 ;
		$Sayer_Score = 0 ;
		$score_sum = 0 ;
	
			
		foreach($item_recset as $rec)
		{
							
					if($rec['salary_item_type_id'] == 10364) 
						$rec['salary_item_type_id'] = 10264  ;  
					elseif ($rec['salary_item_type_id'] == 10367) 
						$rec['salary_item_type_id'] = 10267  ; 
					elseif ($rec['salary_item_type_id'] == 10366) 
						$rec['salary_item_type_id'] = 10266  ; 
					elseif ($rec['salary_item_type_id'] == 10365) 
						$rec['salary_item_type_id'] = 10265  ;
					elseif ($rec['salary_item_type_id'] == 10373) 
						$rec['salary_item_type_id'] = 10332  ;
					elseif ($rec['salary_item_type_id'] == 10374) 
						$rec['salary_item_type_id'] = 10333  ;
					elseif ($rec['salary_item_type_id'] == 10328) 
						$rec['salary_item_type_id'] = 10369  ;
					elseif ($rec['salary_item_type_id'] == 10372 ) 
						$rec['salary_item_type_id'] = 10331   ;
					elseif ($rec['salary_item_type_id'] == 10371 ) 						
						$rec['salary_item_type_id'] = 10330  ; 						
					elseif ($rec['salary_item_type_id'] == 10370 ) 						
						$rec['salary_item_type_id'] = 10329   ; 												
					elseif ($rec['salary_item_type_id'] == 10368) 
						$rec['salary_item_type_id'] = 10327  ;
					elseif ($rec['salary_item_type_id'] == 10335) 
						$rec['salary_item_type_id'] = 10376  ;
					elseif ($rec['salary_item_type_id'] == 10334) 
						$rec['salary_item_type_id'] = 10375  ;
					
					
			$emp_sal_items['<!---item_'.$rec['salary_item_type_id'].'-->'] = CurrencyModulesclass::toCurrency($rec['value'],'CURRENCY');
			
			
				//$emp_sal_items['<!---item_'.$rec['salary_item_type_id'].'_score-->'] = $rec['param1'];
				if($rec['value'])
				{
					$emp_sal_vals[$rec['salary_item_type_id']] = $rec['value'] ;
					$sum += $rec['value'] ;
				}
				/*if($rec['param1'])
				{
					$emp_sal_scores[$rec['salary_item_type_id']] = $rec['param1'] ;
					$score_sum += $rec['param1'];
				}*/
				
		
		
		}

		//$emp_sal_items['<!---salary_score-->'] = CurrencyModulesclass::toCurrency($score_sum);
		$emp_sal_items['<!---salary_sum-->'] = CurrencyModulesclass::toCurrency($sum);
		 
		
				
	}
	// اقلام حقوقي هيات علمي و استخدام كشوري
	else if(!$writ_rec['corrective'] &&
		($writ_rec['person_type'] == HR_EMPLOYEE || $writ_rec['person_type'] == HR_PROFESSOR))
	{
		ob_start();
		$item_recset = manage_writ_item::GetAllWritItems("writ_id=? AND writ_ver=? AND staff_id=?",
								    array($writ_rec['writ_id'], $writ_rec['writ_ver'], $writ_rec["staff_id"]));

		$counter = 0;
		$sum = 0;
		foreach($item_recset as $rec) {
			echo "<tr>
				<td class='list-beginnormal' width='50%' style='padding-top:2; padding-bottom:2'>
					<span class='report_data'>" . $indx[$counter] . $rec['print_title'];

			if($rec['salary_item_type_id'] == SIT_PROFESSOR_FOR_BYLAW_15_3015)
				$content .= ' (' . $rec['param1'] . ' پايه)';

			echo "</span>
				 </td><td class='list-beginnormal' width='25%' style='padding-top:2; padding-bottom:2'>
					<span class='report_data'>" . CurrencyModulesclass::toCurrency($rec['value']) . "</span></td>
					
				<td class='list-beginnormal' width='25%' align='center' style='padding-top:2; padding-bottom:2'
					<span class='report_data'>ريال</span></td>
			</tr>";
			
			$counter++;
			$sum = $sum + $rec['value'];
		}

		if(!$item_recset && $writ_rec['person_type'] == HR_PROFESSOR)
		{
			$salary_item_types_recSet = manage_writ_item::GetAllWritItems(
				'person_type = ' . $writ_rec["person_type"] .
				 ' AND compute_place = ' . SALARY_ITEM_COMPUTE_PLACE_WRIT.
				 ' AND salary_compute_type = ' . SALARY_COMPUTE_TYPE_FUNCTION.
				 ' AND user_data_entry = ' . AUTOMATIC.
				 " AND validity_start_date <= '".$writ_rec['execute_date'].
				 "' AND (validity_end_date >= '".$writ_rec['execute_date']."' OR validity_end_date IS NULL)".
				 ' ORDER BY print_order'
			);
			
			if ($salary_item_types_recSet)
			{
				foreach ($salary_item_types_recSet as $key => $salary_item_types_rec)
				{
					echo "<tr>
							<td class='list-beginnormal' colspan='2' style='padding-top:2; padding-bottom:2'>" .
								$indx[$counter] . $salary_item_types_rec['print_title'];

					if ($salary_item_types_rec['salary_item_type_id'] == SIT_PROFESSOR_FOR_BYLAW_15_3015)
						$content .= ' (' . $salary_item_types_rec['param1'] . ' پايه)';

					echo "</td>
						<td class='list-beginnormal' style='padding-top:2; padding-bottom:2'> ----- </td>
						<td class='list-beginnormal' style='padding-top:2; padding-bottom:2'>ريال</td>
					</tr>";

					$counter++;
				}
			}
		}

		for($i=$counter ; $i<8 ; $i++){

			echo "<tr>
				<td class='list-beginnormal' width='50%' style='padding-top:2; padding-bottom:2'>&nbsp;</td>
				<td class='list-beginnormal' width='25%' style='padding-top:2; padding-bottom:2'>&nbsp;</td>
				<td class='list-beginnormal' width='25%' align='center' style='padding-top:2; padding-bottom:2'>&nbsp;</td>
				</tr>";
		}
		$salary_items = ob_get_contents();
		ob_end_clean();
	}

	// اقلام حقوقی کارکنان قراردادی
	if($writ_rec["person_type"] == HR_CONTRACT && ( $writ_rec["execute_date"] < '2013-02-19' || $writ_rec["execute_date"] >= '2013-03-21' ) )
	{
		$item_recset = manage_writ_item::GetAllWritItems("writ_id=? AND writ_ver=? AND staff_id=?",
			array($writ_rec['writ_id'], $writ_rec['writ_ver'], $writ_rec["staff_id"]));
		
		ob_start();
		$sum = 0;
		
		for($i=0;$i < count($item_recset);$i++)
		{
			$sum += $item_recset[$i]['value'];
			
			if ($i%2 == 0 && $item_recset[$i]['value'] > 0)
			{
				$val1 = $item_recset[$i]['value'] > 0 ? CurrencyModulesclass::toCurrency($item_recset[$i]['value']) . " ریال" : "&nbsp;";
				$title1 = $item_recset[$i]['print_title'];
				$i++;
				if($i < count($item_recset))
				{
					$sum += $item_recset[$i]['value'];
					$val2 = $item_recset[$i]['value'] > 0 ? CurrencyModulesclass::toCurrency($item_recset[$i]['value']) . " ریال" : "&nbsp;";
					$title2 = $item_recset[$i]['print_title'];
				}
				else
				{
					$val2 = "&nbsp;";
					$title2 = "&nbsp;";
				}
				echo  "<tr>
						<td>" . $indx[$i-1] . $title1 . "</td>
						<td align='left' class='money'>" . $val1 . "</td>
						<td> . " . $indx[$i] . $title2 . "</td>
						<td align='left' class='money'>" . $val2 . "</td></tr>";
			}
		}

		$salary_items = ob_get_contents();
		ob_end_clean();
	}

	if(!$writ_rec['corrective'] && $writ_rec['person_type'] == HR_WORKER)
	{
		$item_recset = manage_writ_item::GetAllWritItems("writ_id=? AND writ_ver=? AND staff_id=?",
			array($writ_rec['writ_id'], $writ_rec['writ_ver'], $writ_rec['staff_id']));

		if(count($item_recset)>0)
		{
			$worker_salary_item1_title = $item_recset[0]['print_title'];
			$worker_salary_item1_value =  CurrencyModulesclass::toCurrency($item_recset[0]['value']);

			$worker_salary_item2_title = $item_recset[1]['print_title'];
			$worker_salary_item2_value =  CurrencyModulesclass::toCurrency($item_recset[1]['value']);
			$worker_base_salary =  CurrencyModulesclass::toCurrency($sum);
		}
		ob_start();
		for($i=0; $i<=9; $i++)
		{
            echo "<tr>
					<td width='80px'>" . (isset($item_recset[$i]) ? $item_recset[$i]['print_title'] : "") . "</td>
					<td align='left' class='money'>";
			
			$sum += isset($item_recset[$i]) ? $item_recset[$i]['value'] : 0;
			
			if (isset($item_recset[$i]) && $item_recset[$i]['value'] > 0)
				echo CurrencyModulesclass::toCurrency($item_recset[$i]['value']);
			else
				echo "&nbsp;";

			echo "</td>
				<td style='padding-left:4px' align='left' width='40px'>" . (isset($item_recset[$i]['value']) ? 'ريال' : "&nbsp;") . "</td></tr>";
			
			if($i==1){
				echo "<tr>
						<td><span>جمع مزد مبنا:</span></td>
						<td class='money' style='font-size:12px' align='left'>";
				echo (isset($item_recset[0]) && isset($item_recset[1])) ?
					CurrencyModulesclass::toCurrency($item_recset[1]['value'] + $item_recset[0]['value']) : "";
				echo	"</td>
						<td style='padding-left:4px' align='left' width='40px'>ريال</td>
					</tr>
					<tr>
						<td style='background-color:#bbbbbb;font-family:titr' colspan=3>20 - مزد مزايا : </td>
					</tr>";
			}
		}
		$worker_other_salary_items = ob_get_contents();
		ob_end_clean();
	}

	// موارد مربوط به استخدام كشوري
	if($writ_rec['person_type'] == HR_EMPLOYEE)
	{
		//  وضعيت قرارداد استخدام كشوري
		$prev_writ_obj = manage_writ::get_last_writ_by_date($writ_rec['staff_id'], $writ_rec['execute_date']);
		$contact_title = ($prev_writ_obj->writ_id == "") ? 'انعقاد قرارداد' : 'تمديد قرارداد' ;
		
		// ضريب افزايش سنواتي استخدام كشوري
		$WritItemObj = new manage_writ_item($writ_rec['writ_id'], $writ_rec['writ_ver'], $writ_rec['staff_id'], SIT_STAFF_ANNUAL_INC);
		$sit2_annual_inc_coef =  ($WritItemObj->param2 * 100);

		// پست سازماني استخدام كشوري
		$specialExtraWritItemObj = new manage_writ_item($writ_rec['writ_id'], $writ_rec['writ_ver'], $writ_rec['staff_id'], SIT_EMPLOYEE_SPECIAL_EXTRA);
		if ($specialExtraWritItemObj->writ_id)
		{
			$post_title = $writ_rec['p_title'];
			if ($specialExtraWritItemObj->param1 >= 600 && $specialExtraWritItemObj->param1 < 1100) 
				$post_title .= ' (کارشناس ارشد)';
			else if ($specialExtraWritItemObj->param1 >= 1100 && $specialExtraWritItemObj->param1 < 1600) 
				$post_title .= ' (کارشناس خبره)';
			else if ($specialExtraWritItemObj->param1 >= 1600) 
				$post_title .= ' (کارشناس عالي)';
		}
		else
		{
			$post_title = $writ_rec['p_title'];
		}
	}

	if($writ_rec["person_type"] == HR_CONTRACT)
	{
		$post_title = $writ_rec["p_title"] . " - " . $writ_rec["p_post_no"];
	}

	// عنوان كامل واحد سازماني
	$org_unit_title = $full_title = manage_units::get_full_title($writ_rec['ouid']);

	$ArrayUnit = preg_split('/-/', $full_title);
	$cnt = count($ArrayUnit);
	if( $cnt == 1 ) 
	{
	    if($writ_rec['emp_mode']== 6)			
			       $full_title .= "<b>مامور به </b>" . $writ_rec['MissionPlace'];
	    
	    if($writ_rec['emp_mode']== 16)
		$full_title .= "<b>مامور از </b>" . $writ_rec['MissionPlace']; 

	}
	
	if($cnt > 1)
	{ 
		$full_title = '';
		for ($i=0 ; $i < $cnt ; $i++ )
		{
			if($i == ($cnt - 1 ) && $cnt > 1 && $writ_rec['emp_mode']!= 6 && $writ_rec['emp_mode']!= 16 ){
			 	
                            $full_title .= "<b>شاغل در </b>" . $ArrayUnit[$i]; 
                                
                                }
                        else if($i == ($cnt - 1 ) && $cnt > 1 && ($writ_rec['emp_mode']== 6 || $writ_rec['emp_mode']== 16 )){
			     
			    if($writ_rec['emp_mode']== 6)			
			       $full_title .= "<b>مامور به </b>" . $writ_rec['MissionPlace'];
			    if($writ_rec['emp_mode']== 16)
				$full_title .= "<b>مامور از </b>" . $writ_rec['MissionPlace']; 
                                
                                }
			elseif($i == ($cnt - 2 ))
				$full_title .= $ArrayUnit[$i] . "&nbsp;";
			else
				$full_title .= $ArrayUnit[$i] . "-";
	      }
	}

	if($writ_rec['emp_mode'] == EMP_MODE_ENGAGEMENT){
            $full_title = manage_units::get_full_title($writ_rec['unitCode']);
            $full_title .= '(حالت اشتغال) ' ;
                
                }
	
	// در صورتي كه واحد پست و واحد شخص متفاوت است
	else if($writ_rec['o_ouid'] != $writ_rec['pos_ouid'] && $writ_rec['pos_ouid'])
	{  
		$same_org_unit = ($writ_rec['ou_ouid'] == $writ_rec['po_ouid']) ;
		$full_title = '' ;
		if( $writ_rec['person_type'] == HR_PROFESSOR &&
			($writ_rec['emp_state'] == EMP_STATE_PROBATIONAL_CEREMONIOUS || $writ_rec['emp_state'] == EMP_STATE_APPROVED_CEREMONIOUS ))
		{
			$full_title = 'موقت از ' ;
			$full_title .= $writ_rec['pos_ptitle'];
			$full_title .=' - شاغل در ';
			$full_title .= $writ_rec['o_ptitle'];
			$os_ptitle = $full_title ;
		}
		else
		{
			$full_title = '' ; 
			if($writ_rec['person_type'] == HR_PROFESSOR)
				$full_title = 'موقت از ' ;

			$full_title .= manage_units::get_full_title($writ_rec['pouid']);
                       
			if($writ_rec['person_type'] == HR_PROFESSOR || ( $writ_rec['emp_mode'] != 6  && $writ_rec['emp_mode'] != 16 ) ) {                           
                             
                            $full_title .=' - شاغل در ';                           
                            

                            if(!$same_org_unit)
                                    $full_title .= $ArrayUnit[0].' - ';

                            for ($i=1 ; $i < $cnt ; $i++ )
                            {
                                    if( $i != ($cnt-1) )
                                            $full_title .= $ArrayUnit[$i].' - ';
                                    else
                                            $full_title .= $ArrayUnit[$i] ;
                            }
                         
                         }                        
                        
                        else if($writ_rec['person_type'] != HR_PROFESSOR && $writ_rec['emp_mode'] == 6  ){
                           
                            $full_title .=' مامور به ' .$writ_rec['MissionPlace']  ; 
                              
                              }
                        
                        else if($writ_rec['person_type'] != HR_PROFESSOR && $writ_rec['emp_mode'] == 16  )
                              $full_title .=' مامور از  ' .$writ_rec['MissionPlace']  ;
                         
			$os_ptitle = $full_title ;
		}
	}

	if($writ_rec['emp_mode'] == 13 )
		$full_title = $ArrayUnit[0];

	
	//گروه آموزشي / پژوهشي هيات علمي
	// واحد سازماني فرعي
	// در صورتي كه پست فرد از يك واحدي غير از واحد سازماني فرد باشد شرح آن در واحد فرعي آمده
	if(!$os_ptitle)
		$os_ptitle = $writ_rec['os_ptitle'];
	
	

	// موارد مربوط به هيات علمي
	if($writ_rec['person_type'] == HR_PROFESSOR)
	{
		// نوع پست هيات علمي
		if ($writ_rec['post_type'] == POST_PROFESSOR_RSC)
			$professor_post_type = 'پژوهشي';

		// نوع گروه هيات علمي
		if ($writ_rec['org_sub_unit_type'] == EDUCATIONAL)
			$org_sub_unit_type = ' گروه آموزشي ';
		else if ($writ_rec['org_sub_unit_type'] == RESEARCH)
			$org_sub_unit_type = ' گروه پژوهشي ';

		// عنوان حكم
		if($writ_rec['corrective'])
			$writ_title = 'حکم اصلاحي اعضاي هيات علمي';
	}

	// موارد مربوط به حكم اصلاحي
	if ($writ_rec['corrective'] == 1)
	{
		// مبلغ درج شده در حكم اصلاحي
		// در صورتي که حکم اصلاحي جديد است
		if($writ_rec['history_only'])
		{
			$dt = PdoDataAccess::runquery("SELECT writ_id , writ_ver, staff_id FROM writs
					WHERE corrective_writ_id = " . $writ_rec["writ_id"] . "
						AND corrective_writ_ver = " . $writ_rec["writ_ver"] . "
						AND staff_id = " . $writ_rec["staff_id"] . "
					ORDER BY execute_date DESC , writ_id DESC , writ_ver DESC ");
			$corective_writ_rec = $dt[0];

			$corective_writ_items_obj = new manage_writ_item(
				$corective_writ_rec["writ_id"],
				$corective_writ_rec["writ_ver"],
				$corective_writ_rec["staff_id"],
				SIT_STAFF_ANNUAL_INC);

			$sit2_annual_inc_coef =  ($corective_writ_items_obj->param2 * 100);

			$sum = manage_writ_item::compute_writ_items_sum($corective_writ_rec["writ_id"],
				$corective_writ_rec["writ_ver"], $corective_writ_rec["staff_id"]);
		}
		else
			$sum = manage_writ_item::compute_writ_items_sum($writ_rec['writ_id'],
				$writ_rec['writ_ver'], $writ_rec["staff_id"]);

	}

	if ($writ_rec['corrective'] != 1)
	{
		$exe_date_title ='
			<tr height=25>
				<td colspan=2>
					21-تاریخ اجرای حکم :
					<span>' . DateModules::miladi_to_shamsi($writ_rec['execute_date']).'</span>
				</td>
		   </tr>';
	}
	/*****************************************************/
	$scores = array(
		1=>array(1=> 2400, 2=> 2650, 3=>2650 , 4=>2650 , 5=>2650) ,
		2=>array(1=> 2600, 2=> 2850, 3=>2850 , 4=>2850 , 5=>2850) ,
		3=>array(1=> 2800, 2=> 3050, 3=>3050 , 4=>3050 , 5=>3050) ,
		4=>array(1=> 3000, 2=> 3250, 3=> 3600, 4=>4050 , 5=> 4600) ,
		5=>array(1=> 3200, 2=> 3450, 3=> 3800, 4=>4250 , 5=> 4800) ,
		6=>array(1=> 3400, 2=> 3650, 3=> 4000, 4=>4450 , 5=> 5000) ,
		7=>array(1=> 3600, 2=> 3850, 3=> 4200, 4=>4650 , 5=> 5200) ,
		8=>array(1=> 3800, 2=> 4050, 3=> 4400, 4=>4850 , 5=> 5400) ,
		9=>array(1=> 4000, 2=> 4250, 3=> 4600, 4=>5050 , 5=> 5600) ,
		10=>array(1=> 4200, 2=> 4450, 3=> 4800, 4=>5250 , 5=> 5800) ,
		11=>array(1=> 4400, 2=> 4650, 3=> 5000, 4=>5450 , 5=> 6000) ,
		12=>array(1=> 4600, 2=> 4850, 3=> 5200, 4=>5650 , 5=> 6200) ,
		13=>array(1=> 4800, 2=> 5050, 3=> 5400, 4=>5850 , 5=> 6400) ,
		14=>array(1=> 5000, 2=> 5250, 3=> 5600, 4=>6050 , 5=> 6600) ,
		15=>array(1=> 5200, 2=> 5450, 3=> 5800, 4=>6250 , 5=> 6800) ,
		16=>array(1=> 5400, 2=> 5650, 3=> 6000, 4=>6450 , 5=> 7000)
	) ;
	$current_group = $writ_rec['cur_group'];

	$grup = "";
	if($writ_rec['person_type'] == HR_EMPLOYEE && DateModules::CompareDate($writ_rec['execute_date'], $equal_payment_system_gdate) >= 0)
	{


		$s = "";
		$indx = $current_group - 4;

		if($emp_sal_scores != "" && $emp_sal_scores[34])
			$s = $emp_sal_scores[34];
		else
		{
			$writ_obj = manage_writ::get_last_writ_by_date($writ_rec['staff_id'], $writ_rec['execute_date'], 34);
			if($writ_obj->writ_id)
			{
				$item_obj = new manage_writ_item($writ_obj->writ_id, $writ_obj->writ_ver, $writ_obj->staff_id, 34);
				$s = $item_obj->param1;
			}
		}

		$grade = "";
		if ($scores[$indx][1] == $s) {
			$grade = 'مقدماتی';
		} else if ($scores[$indx][2] == $s) {
			$grade = 'پایه';
		} else if ($scores[$indx][3] == $s) {
			$grade = 'ارشد';
		} else if ($scores[$indx][4] == $s) {
			$grade = 'خبره';
		} else if ($scores[$indx][5] == $s) {
			$grade = 'عالی';
		}
		$current_group = $grade ;
		$grup = $writ_rec['new_grup'];
	}
	else {
		$grup = $writ_rec['old_grup'];
	} 
	
   $listPNO  = "";
   if($writ_rec['personel_no'] != NULL && $writ_rec['person_type'] == 1 )
		$listPNO = preg_split('/-/',$writ_rec['personel_no']);
		 
	/*****************************************************/
 
   $MilitaryD = "" ; 
   if($writ_rec['militaryID'] != 17 ){
        $MilitaryD = " <militaryDate> شروع : <span><!--military_from_date--></span>
                                                             پایان: <span><!--military_to_date--></span></militaryDate>
		" ; 
        } 
		
		if( $writ_rec['E_base'] == NULL &&  ($writ_rec['person_type'] == HR_EMPLOYEE || $writ_rec['person_type'] == HR_CONTRACT ) ) 
		{
			$qry = " SELECT  w.staff_id,
							 SUBSTRING_INDEX(SUBSTRING(max(CONCAT(w.execute_date,w.writ_id,'.',w.writ_ver)),11),'.',1) writ_id,
										SUBSTRING_INDEX(max(CONCAT(w.execute_date,w.writ_id,'.',w.writ_ver)),'.',-1) writ_ver

					 FROM writs w
							INNER JOIN staff ls
								ON(w.staff_id = ls.staff_id)
							INNER JOIN writ_salary_items wsi
										ON w.staff_id = wsi.staff_id AND w.writ_id = wsi.writ_id AND
											w.writ_ver = wsi.writ_ver AND salary_item_type_id = 10364

					 WHERE   w.staff_id = ".$writ_rec['staff_id']  ; 
			$tmp2 = PdoDataAccess::runquery($qry) ; 
			 
			$qry = " select wsi.param8
						from writ_salary_items wsi
										 
                          where wsi.staff_id =".$writ_rec['staff_id']." AND wsi.writ_id = ".$tmp2[0]['writ_id']." AND 
							    wsi.writ_ver =".$tmp2[0]['writ_ver']." AND  wsi.salary_item_type_id = 10364 " ; 
						  
		    $tmp = PdoDataAccess::runquery($qry) ; 
  		    $writ_rec['E_base'] = (!empty($tmp[0]['param8'])) ? $tmp[0]['param8'] : 0; 
						  
		}
		
		if( $writ_rec['E_base'] == NULL || $writ_rec['E_base'] == 0 ) 
		{			
			
			if($writ_rec["sex"] == 1 &&  $writ_rec["person_type"] == 2 && ($writ_rec["military_duration_day"] > 0 || $writ_rec["military_duration"] > 0 ) )
			{
			
				$totalDayWrt = DateModules::ymd_to_days($writ_rec["onduty_year"], $writ_rec["onduty_month"], $writ_rec["onduty_day"]) ; 			
				$totalDaySar = DateModules::ymd_to_days(0, $writ_rec["military_duration"], $writ_rec["military_duration_day"]) ; 					
				$resDay = $totalDayWrt -  $totalDaySar  ; 

				$Vyear = 0 ; 
				$Vmonth = $Vday = 0 ; 
				DateModules::day_to_ymd($resDay, $Vyear, $Vmonth, $Vday) ; 
				$writ_rec['E_base'] =  $Vyear ; 
			//echo $Vyear." ---- ".$baseRes[0]["IsarValue"]."--isa---".$otherPoint  ;  die() ; 
			}						
			else  		
			$writ_rec['E_base'] =  $writ_rec["onduty_year"] ; 
		
		
		} 

if($writ_rec['ComputeGrade'] == NULL && ($writ_rec['person_type'] == HR_EMPLOYEE || $writ_rec['person_type'] == HR_CONTRACT ) )
		{
			$qry = " select  wsi.param5 ComputeGrade
						from writs w inner join writ_salary_items wsi
											on w.staff_id = wsi.staff_id and
												w.writ_id = wsi.writ_id  and
												w.writ_ver = wsi.writ_ver and wsi.salary_item_type_id = 10364

										where w.staff_id = ". $writ_rec["staff_id"]." and 
           w.corrective_writ_id = ".$writ_rec["writ_id"]." and
											w.corrective_writ_ver = ".$writ_rec["writ_ver"]."

					order  by w.writ_id  , w.writ_id

					limit 1" ; 
			$tmp2 = PdoDataAccess::runquery($qry) ; 
			$writ_rec['ComputeGrade'] = (!empty($tmp2[0]['ComputeGrade'])) ? $tmp2[0]['ComputeGrade'] : 0  ; 
		}
$GradeTitle = "" ;
    if($writ_rec['ComputeGrade'] == 1) 
		$GradeTitle = "مقدماتی" ; 
    elseif($writ_rec['ComputeGrade'] == 2) 
	    $GradeTitle = "مهارتی"; 
    elseif($writ_rec['ComputeGrade'] == 3) 
		$GradeTitle = "3"; 
	elseif($writ_rec['ComputeGrade'] == 4) 
		$GradeTitle = "2"; 
	elseif($writ_rec['ComputeGrade'] == 5) 
		$GradeTitle = "1";

        if( $writ_rec['marital_status'] == 3 ) 
		$writ_rec['marital_status_title'] = 'مجرد' ;
	else if($writ_rec['marital_status'] == 4 )
		$writ_rec['marital_status_title'] = 'متاهل' ; 

	$tags =  array(
		 '<!--personel_no-->' =>(!empty($listPNO)  && count($listPNO)> 1 )  ? $listPNO[1]."-".$listPNO[0] : $writ_rec['personel_no'] 
		,'<!--national_code-->' => $writ_rec['national_code']
		,'<!--ps_pfname-->'=>$writ_rec['ps_pfname']
		,'<!--ps_plname-->'=>$writ_rec['ps_plname']
		,'<!--ps_father_name-->'=>$writ_rec['ps_father_name']
		,'<!--ps_idcard_no-->'=>$writ_rec['ps_idcard_no']
		,'<!--si_ptitle-->'=>$writ_rec['si_ptitle']
		,'<!--ci_ptitle-->'=>$writ_rec['ci_ptitle']
		,'<!--issue_place-->'=>$writ_rec['issue_place']
		,'<!--cb_ptitle-->'=>$writ_rec['cb_ptitle']
		,'<!--ps_birth_place-->'=>$writ_rec['ps_birth_place']
		,'<!--ps_birth_date-->'=> DateModules::miladi_to_shamsi($writ_rec['ps_birth_date'])
		,'<!--education_level_title-->'=>$education_level_title
		,'<!--sf_ptitle-->'=>$writ_rec['sf_ptitle']
		,'<!--sbs_title-->'=>$writ_rec['sbs_title']
		,'<!--p_post_no-->'=>$writ_rec['p_post_no']
		,'<!--jc_title-->'=>$writ_rec['jc_title']
		,'<!--jf_title-->'=>$writ_rec['jf_title']
		,'<!--grup-->'=>  $grup
		,'<!--E_base-->'=> $writ_rec['E_base']
		,'<!--S_base-->'=> $writ_rec['S_base']
		,'<!--T_base-->'=> $writ_rec['T_base']
		,'<!--I_base-->'=> $writ_rec['I_base']
		,'<!--Total_base-->'=> ( $writ_rec['E_base'] + $writ_rec['S_base'] + $writ_rec['T_base'] + $writ_rec['I_base'] )
		,'<!--grade-->'=> $GradeTitle
		,'<!--cur_group-->'=>$current_group
		,'<!--post_title-->'=>$post_title
		,'<!--onduty-->'=>$onduty
		,'<!--sw_ptitle-->'=>$writ_rec['sw_ptitle']
		,'<!--cw_ptitle-->'=>$writ_rec['cw_ptitle']
		,'<!--sit2_annual_inc_coef-->'=>$sit2_annual_inc_coef
		,'<!--marital_status_title-->'=>( $writ_rec['marital_status'] == 3 ) ? 'مجرد' : $writ_rec['marital_status_title']
		,'<!--included_children_count-->'=> ($writ_rec['sex'] == 1 || $writ_rec['family_responsible'] == 1 ) ? $writ_rec['included_children_count'] : $writ_rec['children_count']
		,'<!--contract_start_date-->'=>DateModules::miladi_to_shamsi($writ_rec['contract_start_date'])
		,'<!--contract_end_date-->'=>DateModules::miladi_to_shamsi($writ_rec['contract_end_date'])
		,'<!--ref_letter_no-->'=>$writ_rec['ref_letter_no']
		,'<!--ref_letter_date-->'=>DateModules::miladi_to_shamsi($writ_rec['ref_letter_date'])
		,'<!--writ_type-->'=>$writ_rec['writ_type']
		,'<!--description-->'=>(nl2br($writ_rec['description']))
		,'<!--sum-->'=>CurrencyModulesclass::toCurrency($sum)
		,'<!--sum_str-->'=>CurrencyModulesclass::CurrencyToString($sum)
		,'<!--writ_signature_post_owner-->'=>$writ_rec['writ_signature_post_owner']
		,'<!--writ_signature_post_title-->'=>$writ_rec['writ_signature_post_title']
		,'<!--execute_date-->'=>DateModules::miladi_to_shamsi($writ_rec['execute_date'])
		,'<!--send_letter_no-->'=>$writ_rec['send_letter_no']
		,'<!--issue_date-->'=>DateModules::miladi_to_shamsi($writ_rec['issue_date'])
		,'<!--ref_letter_no-->'=>$writ_rec['ref_letter_no']
		,'<!--ref_letter_date-->'=>(DateModules::miladi_to_shamsi($writ_rec['ref_letter_date']))
		,'<!--SALARY_ITEMS-->' => $salary_items
		,'<!--org_unit_title-->' => $org_unit_title
		,'<!--devotion_type-->' =>$devotion_type
		,'<!--o_ptitle-->'=> $writ_rec['ou_ptitle']
		,'<!--military-->'=> $writ_rec['military']
                ,'<!--MilitaryD-->' => $MilitaryD
		,'<!--sf_title-->'=> $writ_rec['sf_title']
		,'<!--sbs_title-->'=> $writ_rec['sbs_title']
		,'<!--snc_level-->'=> $writ_rec['snc_level']
		,'<!--edu_c_ptitle-->'=> $edu_c_ptitle
		,'<!--edu_u_ptitle-->'=> $edu_u_ptitle
		,'<!--edu_doc_date-->'=> $edu_doc_date
		,'<!--base-->'=> $writ_rec['base']
		,'<!--worktime-->'=> $writ_rec['worktime']
		,'<!--emp_st-->'=> ($writ_rec['person_type']== 2 && ( $writ_rec['emp_st'] == 3 || $writ_rec['emp_st'] == 4 ) ) ? str_replace('رسمی قطعی','رسمي',$writ_rec['emp_st'])  : $writ_rec['emp_st']
		,'<!--org_sub_unit_type-->'=> $org_sub_unit_type
		,'<!--os_ptitle-->' => $os_ptitle
		,'<!--full_title-->' => $full_title 
		,'<!--gnd-->' => ($writ_rec['gnd'] == "1" ? "مرد" : "زن")
		,'<!--professor_post_type-->'=> $professor_post_type
		,'<!--writ_title-->'=> $writ_title
		,'<!--wst_title-->'=> $writ_rec['wst_title']
		,'<!--staff_id-->' => $writ_rec['staff_id']
		,'<!--work_start_date-->' => DateModules::miladi_to_shamsi($writ_rec['work_start_date'])
		,'<!--job_id-->' => $writ_rec['job_id']
		,'<!--j_title-->' => $writ_rec['j_title']
		,'<!--job_group-->' => $writ_rec['job_group']
		,'<!--contact_title-->' => $contact_title
		,'<!--worker_salary_item1_title-->'=>$worker_salary_item1_title
		,'<!--worker_salary_item1_value-->'=>$worker_salary_item1_value
		,'<!--worker_salary_item2_title-->'=>$worker_salary_item2_title
		,'<!--worker_salary_item2_value-->'=>$worker_salary_item2_value
		,'<!--WORKER_OTHER_SALARY_ITEMS-->'=> $worker_other_salary_items
		,'<--worker_base_salary-->'=>$worker_base_salary
		,'<!--sisIMAGEPATH-->'=>""
		,'<!--exe_date_title-->'=>$exe_date_title

		,'<!--notes-->'=>$writ_rec["notes"]
		,'<!--military_from_date-->'=> DateModules::miladi_to_shamsi($writ_rec["military_from_date"])
		,'<!--military_to_date-->'=>DateModules::miladi_to_shamsi($writ_rec["military_to_date"])
		
		,'<cr>'=>''
		,'</cr>'=>''
		,'<ncr>'=>''
		,'</ncr>'=>''
		,'<tlm>'=>''
		,'</tlm>'=>''
		,'<ntlm>'=>''
		,'</ntlm>'=>''
		,'<sts>'=>''
		,'</nsts>'=>''
		,'<cnt>'=>''
		,'</cnt>'=>''
		,'<ncnt>'=>''
		,'</ncnt>'=>''

	);

	if(is_array($emp_sal_items))
		$tags = array_merge($emp_sal_items, $tags);
	
	// مشخص كردن فايل template مربوط به حكم
	$content .= file_get_contents("PrintWritTemplates/" . $template_file);

	//حذف قسمتهاي مربوط به حكم اصلاحي از حكم غير اصلاحي و برعكس

	$rgEx = "" ;
	$sep = "" ;
	if($writ_rec['corrective']){
			$rgEx .= $sep . "<ncr>|<\/ncr>";
			$sep = "|";

	}
	else {
			$rgEx .= $sep . "<cr>|<\/cr>";
			$sep = "|";
	}

	if(!$writ_rec['time_limited']){
			$rgEx .= $sep . "<tlm>|<\/tlm>";
			$sep = "|";
	}
	else {
			$rgEx .= $sep . "<ntlm>|<\/ntlm>";
			$sep = "|";
	}
	if(!$writ_rec['req_staff_signature']){
			$rgEx .= $sep . "<sts>|<\/sts>";
			$sep = "|";
	}
	else {
			$rgEx .= $sep . "<nsts>|<\/nsts>";
			$sep = "|";
	}

	if($writ_rec["person_type"] == HR_CONTRACT)
	{
		/*if($writ_rec["sex"] == 2)
		{
			$rgEx .= $sep . "<children>|<\/children>";
			$sep = "|";
		}*/
		if (in_array($writ_rec['military_type'], array(2,11,13,15,16)))
		{
			$rgEx .= $sep . "<militaryDate>|<\/militaryDate>";
			$sep = "|";
		}
	}

	$parts = "";
	if(preg_match("/(.$rgEx.)/",$content)){
		$parts = preg_split('/('.$rgEx.')/',$content);
	}

	if($parts){
		$content = "" ;
		for($i=0 ; $i<count($parts) ; $i++){
			if($i%2 == 0)
				$content .= $parts[$i];
		}
	}
	$content = str_replace(array_keys($tags), array_values($tags), $content);

	return $content;
}

function corrective_writ_detail($writ_id, $writ_ver, $staff_id, $execute_date)
{

	 
	ob_start();
	// get corrected writs by a specfied writ
	$query = "SELECT w.* ,  wst.* , wsi.param3 

        FROM   writs w
        	   LEFT OUTER JOIN writ_subtypes wst
               		ON ((w.writ_type_id = wst.writ_type_id) AND
                    	(w.writ_subtype_id = wst.writ_subtype_id) AND
                        (w.person_type = wst.person_type))
			   LEFT JOIN writ_salary_items wsi 
					ON w.staff_id = wsi.staff_id  AND w.writ_id = wsi.writ_id AND w.writ_ver = wsi.writ_ver AND wsi.salary_item_type_id = 10364 
					
        WHERE  w.corrective_writ_id = $writ_id   AND
               w.corrective_writ_ver = $writ_ver AND
			   w.staff_id = $staff_id AND
               w.writ_ver > 1
        ORDER BY execute_date";
		
		

	$corrected_writs = PdoDataAccess::runquery($query);

	$writ_count = count($corrected_writs);
	if($writ_count == 0)
		return ;
    
	$persent = (75/$writ_count);

	echo "<tr>
			<td style='border-top:1px solid black' colspan=2>نوع حکم</td>";

	$width = round(400/count($corrected_writs));
	
	for($i=0; $i < count($corrected_writs); $i++)
		echo "<td style='border-top:1px solid black;width:".$width."px'>" . $corrected_writs[$i]["print_title"] . "</td>";

	echo "</tr>";

	//----------- get all writ salary items of specified person_type -----------
	$minExecuteDate = $corrected_writs[0]["execute_date"];
	$writs = "";
	$prevwrit = "";
	for($i=0; $i < count($corrected_writs); $i++)
	{
		$writs .= "'" . $corrected_writs[$i]["staff_id"] . "-" .
			$corrected_writs[$i]["writ_id"] . "-" . $corrected_writs[$i]["writ_ver"] . "',";

		$prevwrit .= "'" . $corrected_writs[$i]["staff_id"] . "-" .
						   $corrected_writs[$i]["writ_id"] . "-" .( $corrected_writs[$i]["writ_ver"] - 1 ) . "',";
						   
						   
						   
	
		/*$minExecuteDate = DateModules::CompareDate($corrected_writs[$i]["execute_date"], $minExecuteDate) < 0 ?
			$corrected_writs[$i]["execute_date"] : $minExecuteDate;*/
	}
	/*$query = "SELECT *
        FROM   salary_item_types sit
        WHERE  person_type = " . $corrected_writs[0]["person_type"] . " AND
        	   compute_place = " . SALARY_ITEM_COMPUTE_PLACE_WRIT . " AND
        	   validity_start_date <= '" . $execute_date . "' AND
        	   (validity_end_date IS NULL OR validity_end_date = '0000-00-00' OR
					(validity_end_date >= '" . $minExecuteDate . "' AND validity_end_date >= '" . $execute_date . "'))
        ORDER BY print_order";*/

	$writs = substr($writs, 0, strlen($writs)-1);
	$prevwrit = substr($prevwrit, 0, strlen($prevwrit)-1);
	$query = "select distinct sit.* from writ_salary_items si join salary_item_types sit using(salary_item_type_id)
				where concat(si.staff_id,'-',si.writ_id,'-',si.writ_ver) in (" . $writs . "," . $prevwrit . ")
								AND compute_place = " . SALARY_ITEM_COMPUTE_PLACE_WRIT . "
								AND person_type in (" . $corrected_writs[0]["person_type"] ." , 101 ) ORDER BY print_order";
	
	$salary_item_types = PdoDataAccess::runquery($query);

	//--------------------------------------------------------------------------
	$current_execute_date = $prior_execute_date = $current_field = $prior_field = $field_title = "";
	$current_writ_items = $prior_writ_items = array();
	$current_item_sum = $prior_item_sum = "";
	$field_title2 ="";
	   
	for($i=0; $i < count($corrected_writs); $i++)
	{
		$current_writ = $corrected_writs[$i];
	
		if($current_writ['writ_ver'] > 1)
		{
			$prior_writ = new manage_writ($current_writ["writ_id"], $current_writ["writ_ver"]-1, $current_writ["staff_id"]);
			if($current_writ["person_type"] != 1 ) 
			{
				$qry = " select param3 from writ_salary_items
								where writ_id= ".$current_writ["writ_id"]." and 
										writ_ver = ".($current_writ["writ_ver"]-1)." and staff_id = ".$current_writ["staff_id"]." and salary_item_type_id = 10364 " ; 
				
				$ResPrev = PdoDataAccess::runquery($qry) ;  
                           if(empty($ResPrev[0]['param3']))  $ResPrev[0]['param3'] = ' ' ; 
			}
			$current_execute_date .= "<td>" . DateModules::miladi_to_shamsi($current_writ["execute_date"]) . "</td>";
			$prior_execute_date .= "<td>" . DateModules::miladi_to_shamsi($prior_writ->execute_date) . "</td>";

			if($current_writ["person_type"] == HR_EMPLOYEE || $current_writ["person_type"] == HR_PROFESSOR)
			{	
				
				if( $current_writ["person_type"] == HR_EMPLOYEE && DateModules::CompareDate($execute_date, "2009-03-21") >= 0 && 
				    ( DateModules::CompareDate($execute_date, "2013-02-19") < 0 || 
					  ( DateModules::CompareDate($execute_date, "2013-03-20") > 0 && DateModules::CompareDate($execute_date, "2014-03-20") < 0 ) ))
				{
					$field_title = 'طبقه';
					$current_field .= "<td>" . ($current_writ["cur_group"] - 4) . "</td>";
					$prior_field .= "<td>" . ($prior_writ->cur_group - 4) . "</td>";
				}
				else if($current_writ["person_type"] == HR_EMPLOYEE && DateModules::CompareDate($execute_date, "2009-03-21") < 0)
				{
					$field_title = 'گروه';
					$current_field .= "<td>" . $current_writ["cur_group"] . "</td>";
					$prior_field .= "<td>" . $prior_writ->cur_group . "</td>";
				}
				else
				{	
				    $field_title2 ="";
				    $field_title = 'پایه';
					$current_writ["base"] = (DateModules::CompareDate($current_writ["execute_date"], "2013-02-19") >= 0 && $current_writ["person_type"] != 1 ) ? substr($current_writ["param3"],0,2) : $current_writ["base"] ; 
					$prior_writ_base = (DateModules::CompareDate($current_writ["execute_date"], "2013-02-19") >= 0 && $current_writ["person_type"] != 1 ) ? substr($ResPrev[0]['param3'],0,2) : $prior_writ->base ;  
				    $cf = (DateModules::CompareDate($current_writ["execute_date"], "2013-02-19") < 0 && $current_writ["person_type"] == HR_EMPLOYEE ) ?  "-"  : $current_writ["base"] ; 
				    $pf = (DateModules::CompareDate($prior_writ->execute_date, "2013-02-19") < 0 && $current_writ["person_type"] == HR_EMPLOYEE ) ? "-" : $prior_writ_base ; 
				   
				    $current_field .= "<td>" . $cf . "</td>";
				    $prior_field .= "<td>" .$pf. "</td>";				    
				    
				    if(DateModules::CompareDate($current_writ["execute_date"], "2013-02-19") < 0  || (DateModules::CompareDate($execute_date, "2013-03-20") > 0 && DateModules::CompareDate($execute_date, "2014-03-20") < 0) )
				    {								
						$field_title2 = 'طبقه';
						$cf2 = (DateModules::CompareDate($current_writ["execute_date"], "2013-02-19") < 0 || ( DateModules::CompareDate($execute_date, "2013-03-20") > 0 && DateModules::CompareDate($execute_date, "2014-03-20") < 0 )) ?  ($current_writ["cur_group"] - 4)  : "-" ; 
						$pf2 = (DateModules::CompareDate($prior_writ->execute_date, "2013-02-19") < 0 || ( DateModules::CompareDate($execute_date, "2013-03-20") > 0 && DateModules::CompareDate($execute_date, "2014-03-20") < 0 )) ? ($prior_writ->cur_group - 4) : "-" ; 
						$current_field2 .= "<td>" . $cf2 . "</td>";
						$prior_field2 .= "<td>" .$pf2. "</td>";
				    }
				    elseif(( DateModules::CompareDate($current_writ["execute_date"], "2013-02-19") >= 0 || 
						 DateModules::CompareDate($execute_date, "2013-03-21") < 0 || DateModules::CompareDate($execute_date, "2014-03-20") >= 0 ) && $field_title2 != "" )
				    { 						
						$current_field2 .= "<td>" ."-". "</td>";
						$prior_field2 .= "<td>" ."-". "</td>";
				    }
				}				
				
			}
			else {
				$field_title = ""; 
				$field_title2 = "";				
			     }
				 
			$sum1 = $sum2 = 0;
 
        $item46 =$item10374= 0 ; 
		$item10329 =$item10370= $item51= 0 ; 
		$item10330 =$item10371= $item50= 0 ; 
		
	foreach($salary_item_types as $element) {
		
        if(in_array("46", $element))
            $item46 = 1;
		if(in_array("10374", $element))
            $item10374 = 1;
        
		if(in_array("10329", $element))
            $item10329 = 1;
		if(in_array("10370", $element))
            $item10370 = 1;
		if(in_array("51", $element))
            $item51 = 1;
		
		if(in_array("10330", $element))
            $item10330 = 1;
		if(in_array("10371", $element))
            $item10371 = 1;
		if(in_array("50", $element))
            $item50 = 1;
		
		
		}

        	for($j=0; $j < count($salary_item_types); $j++)
			{ 



				if($salary_item_types[$j]["salary_item_type_id"] == 10374 &&   $item46 == 1 )
									continue;

				if($salary_item_types[$j]["salary_item_type_id"] == 10370 &&   $item51 == 1 )
									continue;

				if($salary_item_types[$j]["salary_item_type_id"] == 10370 &&   $item10329 == 1 )
									continue;


				if($salary_item_types[$j]["salary_item_type_id"] == 10371 &&   $item50 == 1 )
									continue;

				if($salary_item_types[$j]["salary_item_type_id"] == 10371 &&   $item10330 == 1 )
									continue;
				
				if(!isset($current_writ_items[$j]))
				{
					$current_writ_items[$j] = "";
					$prior_writ_items[$j] = "";
				}

				$val = manage_writ_item::get_writSalaryItem_value($current_writ["writ_id"], $current_writ["writ_ver"],
					$current_writ["staff_id"], $salary_item_types[$j]["salary_item_type_id"]);				

				if( $item46 == 1 &&  $item10374 ==1 && !($val > 0 ) &&  
						( $salary_item_types[$j]["salary_item_type_id"] == 46  || $salary_item_types[$j]["salary_item_type_id"] == 10374) )
				{
					$val = manage_writ_item::get_writSalaryItem_value($current_writ["writ_id"], $current_writ["writ_ver"],
					                                                  $current_writ["staff_id"], 10374);
					
				}
				
				if( $item51 == 1 &&  $item10370 ==1  && !($val > 0 ) &&  
						( $salary_item_types[$j]["salary_item_type_id"] == 51  || $salary_item_types[$j]["salary_item_type_id"] == 10370) )
				{
					$val = manage_writ_item::get_writSalaryItem_value($current_writ["writ_id"], $current_writ["writ_ver"],
					                                                  $current_writ["staff_id"], 10370);
					
				}
				
				if( $item50 == 1 &&  $item10371 ==1  && !($val > 0 ) &&  
						( $salary_item_types[$j]["salary_item_type_id"] == 50  || $salary_item_types[$j]["salary_item_type_id"] == 10371) )
				{
					$val = manage_writ_item::get_writSalaryItem_value($current_writ["writ_id"], $current_writ["writ_ver"],
					                                                  $current_writ["staff_id"], 10371);
					
				}
				
                                $current_writ_items[$j] .= "<td class='money'>" . ($val == 0 ? "-" : CurrencyModulesclass::toCurrency($val)) . "</td>";
				$sum2 += $val;

				$val = manage_writ_item::get_writSalaryItem_value($prior_writ->writ_id, $prior_writ->writ_ver, $prior_writ->staff_id,
					$salary_item_types[$j]["salary_item_type_id"]);

				if( $item46 == 1 &&  $item10374 ==1 && !($val > 0 ) && 
                               ( $salary_item_types[$j]["salary_item_type_id"] ==  46 || $salary_item_types[$j]["salary_item_type_id"] == 10374  ) )
				{
				$val = manage_writ_item::get_writSalaryItem_value($prior_writ->writ_id, $prior_writ->writ_ver, $prior_writ->staff_id, 10374);
					
				}
				
				if( $item51 == 1 &&  $item10370 ==1 && !($val > 0 ) && 
                               ( $salary_item_types[$j]["salary_item_type_id"] ==  51 || $salary_item_types[$j]["salary_item_type_id"] == 10370  ) )
				{
				$val = manage_writ_item::get_writSalaryItem_value($prior_writ->writ_id, $prior_writ->writ_ver, $prior_writ->staff_id, 10370);
					
				}
				
				if( $item50 == 1 &&  $item10371 ==1 && !($val > 0 ) && 
                               ( $salary_item_types[$j]["salary_item_type_id"] ==  50 || $salary_item_types[$j]["salary_item_type_id"] == 10371  ) )
				{
				$val = manage_writ_item::get_writSalaryItem_value($prior_writ->writ_id, $prior_writ->writ_ver, $prior_writ->staff_id, 10371);
					
				}
				
                                $prior_writ_items[$j] .= "<td class='money'>" . ($val == 0 ? "-" : CurrencyModulesclass::toCurrency($val)) . "</td>";
				$sum1 += $val;
			}
			$current_item_sum .= "<td class='money'>" . CurrencyModulesclass::toCurrency($sum2) . "</td>";
			$prior_item_sum .= "<td class='money'>" . CurrencyModulesclass::toCurrency($sum1) . "</td>";
		}
	}

	echo "<tr>
			<td style='width:80px' rowspan=2>تاريخ اجراي حکم</td>
			<td style='width:20px'>قبلي</td>
			" . $prior_execute_date . "
		</tr>
		<tr>
			<td>فعلی</td>
			" . $current_execute_date . "
		</tr>";
	if($field_title != "")
		echo "<tr>
			<td style='width:80px'  rowspan=2> " . $field_title . "</td>
			<td>قبلی</td>
			" . $prior_field . "
		</tr>
		<tr>
			<td>فعلی</td>
			" . $current_field . "
		</tr>";
	
	if($field_title2 != "" && $corrected_writs[0]["person_type"] != 1 )
		echo "<tr>
			<td style='width:80px'  rowspan=2> " . $field_title2 . "</td>
			<td>قبلی</td>
			" . $prior_field2 . "
		</tr>
		<tr>
			<td>فعلی</td>
			" . $current_field2 . "
		</tr>";
		
	for($i=0; $i<count($salary_item_types); $i++)
	{
              if($salary_item_types[$i]["salary_item_type_id"] == 10374 &&   $item46 == 1  )
					continue;
			  if($salary_item_types[$i]["salary_item_type_id"] == 10370 &&   $item51 == 1  )
					continue;
			  if($salary_item_types[$i]["salary_item_type_id"] == 10371 &&   $item50 == 1  )
					continue;
		echo "
		<tr>
			<td style='width:80px'  rowspan=2>" . $salary_item_types[$i]["print_title"]. "</td>
			<td>قبلي</td>
			" . $prior_writ_items[$i] . "
		</tr>
		<tr>
			<td>فعلی</td>
			" . $current_writ_items[$i] . "
		</tr>";
	}

	echo "<tr>
			<td style='width:80px' rowspan=2>جمع کل</td>
			<td>قبلي</td>
			" . $prior_item_sum . "
		</tr>
		<tr>
			<td>فعلی</td>
			" . $current_item_sum . "
		</tr>";

	$ret = ob_get_contents();
	ob_end_clean();
	return $ret;
}

function generateReport($transcript_no)
{
	global $equal_payment_system_gdate;
	
	$fileNames = array();
	$last_writ = (isset($_GET["last_writ_flag"]) || isset($_POST["last_writ_view"])) ? "1" : "" ; 
		
	$dt = writ_print_list($transcript_no,$last_writ);
	if($dt->rowCount() == 0)
	{
		echo "گزارش هیچ نتیجه ایی در بر ندارد.";
		return;
	}
        
	$current_staff_id = "";
	$cnt = $dt->rowCount() ; 
                            
	for($i=0; $i<$cnt; $i++)
	{       
                $row = $dt->fetch() ; 
		if($current_staff_id != $row["staff_id"])
		{
			$writForm = PrintWrit($row);
			$current_staff_id = $row["staff_id"];
		}

		if($row['person_type'] == HR_PROFESSOR)
		{ 
                   
			// نسخه هيات علمي
			$transcripts_title = ($transcript_no == "all") ? $row['transcripts_title'] : 'عضو هيات علمي';
		}
		else
		{
			// نسخه استخدام كشوري
			//$transcripts_title = ($transcript_no == "all") ? $row['transcripts_title'] : 'مستخدم';
$transcripts_title = ($transcript_no == "all") ? $row['transcripts_title'] : ' کارگزینی' ;
		}

		$tags = array('<!--transcripts_title-->'=>$transcripts_title);
		echo str_replace(array_keys($tags), array_values($tags), $writForm);

		echo "<div class=noprint><br><br></div>";
		
		if($i == $cnt-1 && $row['corrective'] == 1)
		{
			echo "<div class='pageBreak'></div>";
			// موارد مربوط به پشت حكم اصلاحي
			$corrective_detail = corrective_writ_detail($row['writ_id'], $row['writ_ver'], $row['staff_id'], $row['execute_date']);
			$tags = array('<!--CORRECTIVE_DETAIL-->' => $corrective_detail);

			$corrective_detail = str_replace(array_keys($tags), array_values($tags), $corrective_detail);

			if($row['person_type'] == HR_PROFESSOR || DateModules::CompareDate($row['execute_date'],$equal_payment_system_gdate) < 0)
				echo '<div class="report_header" style="font-size:medium"><B>اصلاحات احکام کارگزيني</B><br></div>
					<table cellpadding="0" cellspacing="0" class="big_corrective money" style="text-align:center">';
			else
				echo '<div class="report_header" style="font-size:medium"><B>اصلاحات احکام کارگزيني</B><br></div>
					<table cellpadding="0" cellspacing="0" class="report money" style="text-align:center">';
			
			echo $corrective_detail;
			echo "</table>";
		}
		//$fileNames[] = PrintWrit($row, $transcript_no);
		if($i != $cnt-1)
			echo "<div class='pageBreak'></div>";
	}

	return $fileNames;
}
?>
<html dir='rtl'>
	<head>
		<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
		<link rel=stylesheet href="/HumanResources/css/writ.css?v=1">
	</head>
	<body style="margin-top:0">
		<center>
		<? generateReport($transcript_no);?>
		</center>
	</body>
</html>

	