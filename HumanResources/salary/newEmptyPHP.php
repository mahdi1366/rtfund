<?php
require_once '../../../pooyaheader.inc.php';  
require_once '../../persons/class/devotion.class.php';
require_once '../class/writ_item.class.php';
require_once('num2str.php');
require_once inc_manage_unit;
require_once(config::$root_path . "educ/MPDF52/mpdf.php");
$mysql = pdodb::getInstance();
ini_set('display_errors', 'on');
//HTMLUtil::$dont_print = true;
$out = '<!DOCTYPE html>
		<html dir="rtl" lang="fa">
		<head>
		<meta charset="utf-8">'.'<link rel="stylesheet" href="'.config::$css_path.'StaffWrit.css" type="text/css"/>';

function Newshdate($st)
{
        $st = shdate($st);
        $yy = substr($st,6,2); 
        $mm = substr($st,3,2); 
        $dd = substr($st,0,2);
        return "13".$yy."/".$mm."/".$dd;
}
//.............	

class CurrencyModulesclass
{
	private static $number_array = array(
		1 => 'يک',
		2 => 'دو',
		3 => 'سه',
		4 => 'چهار',
		5 => 'پنج',
		6 => 'شش',
		7 => 'هفت',
		8 => 'هشت',
		9 => 'نه',

		10 => 'ده',
		11 => 'يازده',
		12 => 'دوازده',
		13 => 'سيزده',
		14 => 'چهارده',
		15 => 'پانزده',
		16 => 'شانزده',
		17 => 'هفده',
		18 => 'هيجده',
		19 => 'نوزده',

		20 => 'بيست',
		30 => 'سي',
		40 => 'چهل',
		50 => 'پنجاه',
		60 => 'شصت',
		70 => 'هفتاد',
		80 => 'هشتاد',
		90 => 'نود',

		100 => 'يکصد',
		200 => 'دويست',
		300 => 'سيصد',
		400 => 'چهارصد',
		500 => 'پانصد',
		600 => 'ششصد',
		700 => 'هفتصد',
		800 => 'هشتصد',
		900 => 'نهصد'
		);

    private static $extend = array(
		0 => '',
		1 => 'هزار',
		2 => 'ميليون',
		3 => 'ميليارد',
		4 => 'تريليون');
	
	static function toCurrency($value)
	{
		$value = number_format(abs($value),0,NUMBER_DECIMAL_POINT, NUMBER_THOUSANDS_POINT);
	    if ($value < 0)
	    {
	    	$value = str_replace('N',$value, NUMBER_NEGATIVE_VIEW);
	    }
	    return $value;
	}

	/**
	 * این تابع مبلغ را به حروف بر می گرداند
	 * @param int $value
	 * @return string
	 */
	public static function CurrencyToString($value)
	{
		
		if ($value == "" || $value == 0)
                return 'صفر';
        $extend  = self::$extend;
        $counter = 0;

        $number_string = '';
        while ($value > 0) {
                $three_digit_number = 0;
                $three_digit_number = ($value % 1000);
                $value = floor($value / 1000);
                if ($three_digit_number > 0) {
                        $three_digit_string = self::convertThreeDigitNumberToString($three_digit_number);
                        $temp_string = '';
                        if ($counter > 0)
                                $temp_string .= ' ';
                        if ($counter == 1 && ($three_digit_number%10) == 1) /*   'يکهزار'  */
                                $temp_string .= $three_digit_string . $extend[$counter];
                        else $temp_string .= $three_digit_string . ' ' . $extend[$counter];
                        if ($counter > 0 && $number_string > '')
                                $temp_string .= ' و ';
                        $number_string =  $temp_string . $number_string;
                }
                $counter++;
        }


        return $number_string;
	}

	private static function ConvertThreeDigitNumberToString($three_digit_number)
	{
        if ($three_digit_number == 0)
                return '';
        $number_array = self::$number_array;
        $three_digit_string = '';
        if ($three_digit_number > 99) {
                $three_digit_string = $number_array[floor($three_digit_number / 100) * 100];
                $three_digit_number %= 100;
        }

        if ($three_digit_number > 0) {
                if ($three_digit_string > '')
                        $three_digit_string .= ' و ';
                if ($three_digit_number < 20) {
                        $three_digit_string .= $number_array[$three_digit_number];
                }
                else {
                        $three_digit_string .= $number_array[floor($three_digit_number / 10) * 10];
                        $three_digit_number %= 10;
                        if ($three_digit_number > 0) {
                                if ($three_digit_string > '')
                                        $three_digit_string .= ' و ';
                                $three_digit_string .= $number_array[$three_digit_number];
                        }
                }
        }
        return $three_digit_string;
	}
}

//.................   
$wid = $_GET['writ_id'];
$wver = $_GET['writ_ver'];
$sid = $_GET['staff_id'];
$transcript_no = isset($_REQUEST["transcript_no"]) ? $_REQUEST["transcript_no"] : "0";
  $transcripts_title = ($transcript_no == "all") ? $row['transcripts_title'] : 'مستخدم';


    $query="select s.staff_id, w.writ_id, w.writ_ver, w.staff_id, w.person_type, w.onduty_year, w.onduty_month, w.onduty_day, w.writ_type_id, w.emp_state, 
    w.emp_mode, w.ref_letter_date, w.ref_letter_no, w.send_letter_no, w.contract_start_date, w.contract_end_date, w.corrective, w.issue_date, w.execute_date ,
    wst.time_limited, p.sex sex , w.notes,w.family_responsible , w.pay_date pay_date, w.MissionPlace , msts.Title marital_status_title, edulv.Title education_level_title, (w.cur_group-jf.start_group)+1 old_grup, (w.cur_group - 4) new_grup, w.cur_group , w.description description, w.included_children_count, w.children_count, miltype.Title as military,
    miltype.InfoID as militaryID , w.job_id, sinclv.Title snc_level, w.base, w.ouid, empstt.Title emp_st, worktime.Title worktime, p.sex gnd, p.pfname ps_pfname, p.birth_place ps_birth_place, p.issue_place, p.plname ps_plname, p.idcard_no ps_idcard_no, p.father_name ps_father_name, p.birth_date ps_birth_date, p.national_code, p.military_from_date,
    p.military_to_date, p.military_type,s.ledger_number , o.ptitle o_ptitle, o.ouid o_ouid, parentu.ouid ou_ouid , parentu.ptitle ou_ptitle , o.org_unit_type org_sub_unit_type , o.ptitle os_ptitle ,
    punit.ouid po_ouid, punit.ptitle po_ptitle, psubunit.ptitle pos_ptitle , psubunit.ouid pos_ouid , j.title j_title, j.job_group, sf.ptitle sf_title,
    sbs.ptitle sbs_title, wst.print_title wst_title, po.title p_title, po.post_no p_post_no, po.post_id , po.ouid pouid , posttype.Title post_type, 
    cw.ptitle cw_ptitle, cb.ptitle cb_ptitle, ci.ptitle ci_ptitle, si.ptitle si_ptitle, sw.ptitle sw_ptitle, jf.title jf_title, jsc.title jsc_title,
    jc.title jc_title, s.personel_no, s.work_start_date, s.bank_id, s.account_no, c.ptitle country_title, sbs.ptitle sbs_title, sf.ptitle sf_ptitle,
    sf.sfid , s.unitCode , j.job_id , j.title j_title, wt.title writ_type, p.PersonID , w.writ_signature_post_title, w.writ_signature_post_owner ,
    w.grade , history_only, wst.req_staff_signature , wsi.param8 E_base, ba.SBase S_base, ba.IsarValue I_base , ba.TashvighiValue T_base 
    from hrmstotal.staff s INNER JOIN hrmstotal.writs w ON (s.staff_id=w.staff_id)
    LEFT OUTER JOIN hrmstotal.writ_subtypes wst ON ((w.person_type = wst.person_type) 
    AND (w.writ_type_id = wst.writ_type_id) AND (w.writ_subtype_id=wst.writ_subtype_id)) 
    LEFT OUTER JOIN hrmstotal.writ_types wt ON ((w.person_type = wt.person_type) AND (w.writ_type_id = wt.writ_type_id)) 
    LEFT OUTER JOIN hrmstotal.person_educations pe1 ON ((w.education_level = pe1.education_level) AND (w.sfid = pe1.sfid) AND (w.sbid = pe1.sbid) AND (pe1.PersonID = s.PersonID))
    LEFT OUTER JOIN hrmstotal.countries c ON (pe1.country_id = c.country_id)
    LEFT OUTER JOIN hrmstotal.universities u ON ((pe1.university_id = u.university_id) AND (pe1.country_id = u.country_id))
    LEFT OUTER JOIN hrmstotal.study_fields sf ON ((w.sfid = sf.sfid)) 
    LEFT OUTER JOIN hrmstotal.study_branchs sbs ON ((w.sfid = sbs.sfid)AND(w.sbid=sbs.sbid))
    LEFT OUTER JOIN hrmstotal.persons p ON (s.PersonID = p.PersonID) 
    LEFT OUTER JOIN hrmstotal.cities cw ON ((w.work_city_id = cw.city_id) AND (w.work_state_id = cw.state_id)) 
    LEFT OUTER JOIN hrmstotal.states sw ON (cw.state_id=sw.state_id)
    LEFT OUTER JOIN hrmstotal.cities cb ON ((p.birth_city_id = cb.city_id) AND (p.birth_state_id = cb.state_id)) 
    LEFT OUTER JOIN hrmstotal.cities ci ON ((p.issue_city_id=ci.city_id) AND (p.issue_state_id = ci.state_id)) 
    LEFT OUTER JOIN hrmstotal.states si ON (ci.state_id=si.state_id)
    LEFT OUTER JOIN hrmstotal.position po ON (w.post_id = po.post_id)
    LEFT OUTER JOIN hrmstotal.org_new_units psubunit ON (psubunit.ouid = po.ouid)
    LEFT OUTER JOIN hrmstotal.org_new_units punit ON (punit.ouid = psubunit.parent_ouid)
    LEFT OUTER JOIN hrmstotal.job_fields jf ON (po.jfid = jf.jfid) 
    LEFT OUTER JOIN hrmstotal.job_subcategory jsc ON ((jf.jsid = jsc.jsid) AND (jf.jcid=jsc.jcid))
    LEFT OUTER JOIN hrmstotal.job_category jc ON (jsc.jcid = jc.jcid) 
    LEFT OUTER JOIN hrmstotal.org_new_units o ON (w.ouid = o.ouid)
    LEFT OUTER JOIN hrmstotal.org_new_units parentu ON (parentu.ouid = o.parent_ouid) 
    LEFT OUTER JOIN hrmstotal.jobs j ON (w.job_id = j.job_id)
    LEFT OUTER JOIN writ_salary_items wsi ON w.writ_id = wsi.writ_id AND w.writ_ver = wsi.writ_ver AND w.staff_id = wsi.staff_id AND wsi.salary_item_type_id = 10364 LEFT OUTER JOIN (select PersonID , sum(if(ba.BaseType in (6,2,20,21,22,23,24,25,26,27) and ba.BaseStatus = 'NORMAL' ,ba.BaseValue,0)) TashvighiValue , sum(if(ba.BaseType in (3,4,5,7) and ba.BaseStatus = 'NORMAL' ,ba.BaseValue,0)) IsarValue ,
    sum(if(ba.BaseType in (1 ) and ba.BaseStatus = 'NORMAL' ,ba.BaseValue,0)) SBase from bases ba group by PersonID) ba ON ba.PersonID = s.PersonID left join Basic_Info msts on(msts.TypeID=15 AND w.marital_status=msts.InfoID)left join Basic_Info edulv on(edulv.TypeID=6 AND w.education_level=edulv.InfoID)left join Basic_Info miltype on(miltype.TypeID=10 AND p.military_type=miltype.InfoID)left join Basic_Info sinclv on(sinclv.TypeID=8 AND w.science_level=sinclv.InfoID)
    left join Basic_Info empstt on(empstt.TypeID=3 AND w.emp_state=empstt.InfoID)
    left join Basic_Info worktime on(worktime.TypeID=14 AND w.worktime_type=worktime.InfoID)left join Basic_Info posttype on(posttype.TypeID=27 AND po.post_type=posttype.InfoID)
    where (1=1) AND w.writ_id = '".$wid."' AND w.writ_ver='".$wver."' AND w.staff_id='".$sid."' ";

    $res = $mysql->Execute($query);
   /* echo $query;
    die();*/
    $rec=$res->fetch();
    //print_r($rec["PersonID"]);die();
    
    $writ_title = "قرارداد کارکنان قراردادی";
    
    $post_title = $rec["p_title"] . "- " . $rec["p_post_no"];
    
    $grade=($rec['grade'] == 1 ) ? "مقدماتی" : " ";
   
    $MilitaryD = "" ; 
    if($rec['militaryID'] != 17 ){
        $MilitaryD = " <militaryDate> تاریخ شروع : <span>".Newshdate($rec['military_from_date'])."</span>
                                                            تاریخ پایان : <span>        
        ".Newshdate($rec["military_to_date"])."
        </span></militaryDate>
		" ; 
    } 
	// عنوان كامل واحد سازماني
	$org_unit_title = $full_title = manage_units::get_full_title($rec['ouid']);
        
//print_r($org_unit_title);
	$ArrayUnit = preg_split('/-/', $full_title);
     //   print_r();
	$cnt = count($ArrayUnit);
	if( $cnt == 1 ) 
	{
	    if($rec['emp_mode']== 6)			
			       $full_title .= "<b>مامور به </b>" . $rec['MissionPlace'];
	    
	    if($rec['emp_mode']== 16)
		$full_title .= "<b>مامور از </b>" . $rec['MissionPlace']; 

	}
	
	if($cnt > 1)
	{ 
		$full_title = '';
		for ($i=0 ; $i < $cnt ; $i++ )
		{
			if($i == ($cnt - 1 ) && $cnt > 1 && $rec['emp_mode']!= 6 && $rec['emp_mode']!= 16 ){
			 	
                            $full_title .= "<b>شاغل در </b>" . $ArrayUnit[$i]; 
                                
                                }
                        else if($i == ($cnt - 1 ) && $cnt > 1 && ($rec['emp_mode']== 6 || $rec['emp_mode']== 16 )){
			     
			    if($rec['emp_mode']== 6)			
			       $full_title .= "<b>مامور به </b>" . $rec['MissionPlace'];
			    if($rec['emp_mode']== 16)
				$full_title .= "<b>مامور از </b>" . $rec['MissionPlace']; 
                                
                                }
			elseif($i == ($cnt - 2 ))
				$full_title .= $ArrayUnit[$i] . "&nbsp;";
			else
				$full_title .= $ArrayUnit[$i] . "-";
	      }
	}

	if($rec['emp_mode'] == EMP_MODE_ENGAGEMENT){
            $full_title = manage_units::get_full_title($rec['unitCode']);
            $full_title .= '(حالت اشتغال) ' ;
                
                }
	
	// در صورتي كه واحد پست و واحد شخص متفاوت است
	else if($rec['o_ouid'] != $rec['pos_ouid'] && $rec['pos_ouid'])
	{  
		$same_org_unit = ($rec['ou_ouid'] == $rec['po_ouid']) ;
		$full_title = '' ;
		if( $rec['person_type'] == HR_PROFESSOR &&
			($rec['emp_state'] == EMP_STATE_PROBATIONAL_CEREMONIOUS || $rec['emp_state'] == EMP_STATE_APPROVED_CEREMONIOUS ))
		{
			$full_title = 'موقت از ' ;
			$full_title .= $rec['pos_ptitle'];
			$full_title .=' - شاغل در ';
			$full_title .= $rec['o_ptitle'];
			$os_ptitle = $full_title ;
		}
		else
		{
			$full_title = '' ; 
			if($rec['person_type'] == HR_PROFESSOR)
				$full_title = 'موقت از ' ;

			$full_title .= manage_units::get_full_title($rec['pouid']);
                       
			if($rec['person_type'] == HR_PROFESSOR || ( $rec['emp_mode'] != 6  && $rec['emp_mode'] != 16 ) ) {                           
                             
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
                        
                        else if($rec['person_type'] != HR_PROFESSOR && $rec['emp_mode'] == 6  ){
                           
                            $full_title .=' مامور به ' .$rec['MissionPlace']  ; 
                              
                              }
                        
                        else if($rec['person_type'] != HR_PROFESSOR && $rec['emp_mode'] == 16  )
                              $full_title .=' مامور از  ' .$rec['MissionPlace']  ;
                         
			$os_ptitle = $full_title ;
		}
	}

	if($rec['emp_mode'] == 13 )
		$full_title = $ArrayUnit[0];
	//گروه آموزشي / پژوهشي هيات علمي
	// واحد سازماني فرعي
	// در صورتي كه پست فرد از يك واحدي غير از واحد سازماني فرد باشد شرح آن در واحد فرعي آمده
	if(!$os_ptitle)
		$os_ptitle = $rec['os_ptitle'];

	// وضعيت ايثارگري
	$devotion_recSet = manage_person_devotion::get_person_devotions($rec['PersonID'], NULL, 'ALL');
	$devotion_type = "";
	if(is_array($devotion_recSet))
		foreach($devotion_recSet as $devotion_rec)
			$devotion_type .= " " . $devotion_rec['devotionTypeName'] . "  ";
	//print_r($devotion_type);//die()

        	$salary_items = $corrective_detail = $professor_post_type = $org_sub_unit_type = $writ_title = $contact_title =
		$worker_salary_item1_title = $worker_salary_item1_value = $worker_salary_item2_title = $worker_salary_item2_value =
		$worker_other_salary_items = $worker_base_salary = $emp_sal_items = $template_file = $exe_date_title = "";

                	// اقلام حقوقی کارکنان قراردادی
	
		$item_recset = manage_writ_item::GetAllWritItems("writ_id=? AND writ_ver=? AND staff_id=?",
			array($rec['writ_id'], $rec['writ_ver'], $rec["staff_id"]));
		
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
						<td class='text' >" . $indx[$i-1] . $title1 . "</td>
						<td align='left' class='money' class='info' >" . $val1 . "</td>
						<td class='text' > . " . $indx[$i] . $title2 . "</td>
						<td align='left' class='money' class='info'>" . $val2 . "</td></tr>";
			}
		}

		$salary_items = ob_get_contents();
		ob_end_clean();
	

        
        
        
        

$out .='<table class="report" style="font-size:12px!important" width="98%" cellpadding="0" cellspacing="0"  border=1>
	<tr>
		<td style="padding:2px;border-top:1px solid black" align="center">
                    <img src="/HumanResources/img/fum_symbol.jpg" width="50px">
                </td>
		<td style="border-top:1px solid black;vertical-align:middle" align="center" colspan="3" class="title">قرارداد کارکنان قراردادی</td>
	</tr>
	<tr style="background-color:#bbbbbb">
		<td width="28%" class="text" >-1 نام دستگاه :<font class="info">دانشگاه فردوسي مشهد</font></td>
		<td width="24%" style="vertical-align:middle"  class="text" >-2 شماره ملی:<span  class="info" >'.$rec['national_code'].'</span></td>
		<td width="48%" colspan="2"  class="text" >3-نام و نام خانوادگي طرف قرارداد : <font class="info">'.$rec["ps_pfname"].' '.$rec["ps_plname"].'</font></td>
	</tr>
	<tr>
		<td class="text" >4-نام پدر : <span class="info"  >'.$rec["ps_father_name"].'</span></td>
		<td class="text"  >5-شماره شناسنامه : <span class="info"  >'.$rec["ps_idcard_no"].'</span></td>
		<td width="25%" class="text"  >6-محل صدور شناسنامه: <span class="info"  >'.$rec["ci_ptitle"].'</span></td>
		<td class="text"  >-7 محل تولد : &nbsp;<span class="info"  >'.$rec["cb_ptitle"].'</span></td>
		
	</tr>
	<tr>
		<td class="text"  >8-تاریخ تولد: <span class="info"  >'.Newshdate($rec["ps_birth_date"]).'</span></td>
		<td colspan="3" class="text"  >9-بالاترين مدرک و رشته تحصيلي : 
			 مقطع: <span class="info"  >
                         '.$rec["education_level_title"].'</span>
			 رشته:  <span class="info"  >
                        '.$rec["sf_title"].''.$rec["sbs_title"].' </span></td>
	</tr>
	<tr>
		<td colspan="2" style="line-height:1.2" class="text"  >10-مستند :
		بند ۳ بخشنامه شماره 84515/ت 34613 مورخ 15/12/1384 هیات محترم وزیران و بخشنامه شماره 4848/100 مورخ 20/01/1386 سازمان مدیریت و برنامه ریزی کشور</td>
		<td colspan="2" class="text" >11-نوع قرارداد : <span class="info" >'.$rec["wst_title"].'</span></td>
	</tr>
	<tr>
		<td colspan="1"  class="text"   >12-شغل : <span class="info"  >'.$post_title.'</span></td>
		<td colspan="3"  class="text"  >-13
					پایه: <span class="info"  >
                                         '.(($rec['E_base']) + ($rec['S_base']) + ($rec['T_base']) + ($rec['I_base'])) .'
                                        </span>
				
		(
		 استحقاقی:
		 <span class="info"  >'.$rec["E_base"].'</span> 	
		 ایثارگری:
		 	 <span class="info"  >'.$rec["I_base"].'</span> 	
		 سربازی:
		 	 <span class="info"  >'.$rec["S_base"].'</span> 	
		 تشویقی:
		 	 <span class="info"  >'.$rec["T_base"].'</span>
		 )
	
		 رتبه: <span class="info"  >
                 '.$grade.'
                     </span>
		</td>
	</tr>
	<tr>
		<td colspan="2" class="text"  >14-واحد سازماني : <span class="info"  >'.$full_title.'</span></td>
		<td colspan="2" class="text"  >15-محل جغرافياي خدمت : <span class="info"  >'.$rec["cw_ptitle"].'</span></td>
	</tr>
	<tr>
		<td colspan="2" class="text"  >16-وضعيت ايثارگري:<span class="info"  >'. $devotion_type.'</span></td>
		<td colspan="2" class="text"  >17-وضعيت تاهل : <span class="info"  >'.$rec["marital_status_title"].'</span>
		<children>&nbsp;تعداد فرزندان : <span class="info"  >'.$rec["included_children_count"].' نفر</span></children>
		</td>
	</tr>
	<tr>
		<td colspan="2" class="text"  >-18 وضعيت نظام وظيفه : <span class="info"  >'.$rec["military"].'</span>
                '.$MilitaryD.'
		</td>
		<td colspan="2" class="text" >19-تاريخ شروع و خاتمه قرارداد : شروع:<span class="info"  >'.Newshdate($rec["contract_start_date"]).'</span> خاتمه:<span class="info"  >
                    '.Newshdate($rec["contract_end_date"]).'</span></td>
	</tr>
	<tr>
		<td colspan="2" class="text"  >20-ساعات کار:
                40 ساعت کار در هفته که زمانبندی آن از طرف واحد محل خدمت تعیین می شود.</td>
		<td colspan="2" class="text"  >21-موضوع قرارداد : <span>انجام امور محوله بر اساس بند 12 قرارداد</span></td>
	</tr>
	<tr>
		<td colspan="4" style="text-align:justify;line-height:1.2" class="text"  >22-شرح قرارداد : '.'<span calss="info" >'.htmlentities(str_replace(array('>>','<<'), array('<','>'),$rec["description"]), ENT_QUOTES, 'UTF-8').'</span></td>
	</tr>
	<tr style="background-color:#bbbbbb">
		<td colspan="4"><font class="text" >23-حق الزحمه : </font></td>
	</tr>
	<tr>
		'.$salary_items.'
	</tr>
	<tr style="background-color:#bbbbbb">
		<td colspan="4" class="text" >جمع مزد و مزايای طرف قرارداد :  <font class="info" >'.CurrencyModulesclass::toCurrency($sum).' ریال</font>
		&nbsp;&nbsp;&nbsp;&nbsp;جمع مزد و مزايا به حروف <font class="info" >'.Full_No2Str($sum).'</font></td>
	</tr>
	<tr>
		<td colspan="4" style="text-align:justify;line-height:1.2" class="text" >-25 شرایط قرارداد به شرح ذیل مورد توافق طرفین می باشد: <br>
			الف) موضوع بند 22 در صورت انجام کار موضوع قرارداد و گواهی ناظر موضوع بند (ه ردیف 23) این قرارداد با رعایت مقررات مالی و پس از کسر کسور قانونی و حق بیمه تامین اجتماعی از محل اعتبار دانشگاه فردوسی مشهد قابل پرداخت است.
<br>
تبصره 1: عیدی و پاداش سالانه بر اساس ابلاغ مصوبه هیئت محترم دولت به تشخیص کارفرما در طول مدت قرارداد قابل پرداخت است.
<br>
تبصره 2 : در صورت ارجاع کار اضافی مجموعا تا سقف 25 درصد کل قرارداد برای طول مدت قرارداد قابل پرداخت است.
<br>
ب) طرف قرارداد از نظر خدمات درمانی, بازنشستگی, حوادث ناشی از کار و غیر آن و سایر خدمات مشمول مقررات تامین اجتماعی خواهد بود و حق بیمه مقرر همه ماهه برابر قانون تامین اجتماعی به صندوق ذیربط واریز خواهد شد.
<br>
ج) تعهدات طرف قرارداد :
<br>
- طرف قرارداد متعهد است مطابق شرح وظایف مقررات و ضوابط نسبت به انجام موضوع قرارداد اقدام کند.
<br>
- طرف قرارداد اقرار می کند مشمول قانون منع مداخله کارکنان دولت در معاملات دولتی مصوب 1377 نیست.
<br>
- عقد قرارداد هیچگونه تعهدی مبنی بر استخدام رسمی یا پیمانی از سوی وزارتخانه/ سازمان/ دانشگاه فردوسی مشهد دستگاه اجرایی برای طرف قرارداد ایجاد نمی کند.
<br>
طرف قرارداد مسوول حفظ و نگهداری وسایل و اموال در اختیار است و در صورت ایجاد خسارت وزارتخانه/ سازمان/ دانشگاه فردوسی مشهد می تواند با تشخیص خود من جمله از محل قرارداد خسارت مربوط را جبران کند.
<br>
د) مرخصی استحقاقی سالانه مطابق با فصل هشتم آیین نامه استخدامی اعضای غیر هیئت علمی می باشد.(ماده 55،56)
<br>
ه) ناظر بر اجرای قرارداد آقای / خانم <span style="padding-left:100px">&nbsp;</span>به عنوان ناظر بر حسن اجرای قرارداد مشخص می شود.
<br>
و) طرفين قرارداد مي توانند پس از<span style="padding-left:50px">&nbsp;</span>روز(حداکثر يک ماه)با اعلام قبلي اين قراردادرافسخ نمايد.</td>
	</tr>
	<tr>
		<td colspan="4" style="border-bottom:0" class="text" >24-این قرارداد شامل 24 بند در 4 نسخه تنظیم شده که هر 4 نسخه حکم واحد را دارد و پس از امضا و ثبت معتبر خواهد بود.
		<br>
		</td>
	</tr>
	<tr>    		
                <td  style="border:0;border-bottom:1px solid black;border-right:1px solid black" class="text" >تاریخ اجرای قرارداد : <span class="info" >'.Newshdate($rec['execute_date']).'</span></td>		
		<td  style="border:0;border-bottom:1px solid black" class="text" >شماره قرارداد : <span class="info" > '.$rec["send_letter_no"].'</span></td>		
		<td  colspan="2" style="border:0;border-bottom:1px solid black;border-left:1px solid black" class="text" >تاریخ صدور : <span class="info" >'.Newshdate($rec['issue_date']).'</span></td>
	</tr>
	<tr>
		<td colspan="2" class="text" ><div style="float:right"></div><div style="float:left;width:80%;padding-left:20px" align="left" >نام و نام خانوادگی طرف قرارداد</div>
			<br><div align="left" style="padding-left:50px">امضاء</div></td>
		<td colspan="2" style="padding:0;" class="text" >
			<table cellpadding="0" cellspacing="0" width="100%" border="0">
				<tr>
					<td width="49%" style="border:0" class="text" > نام ونام خانوادگي مقام  مسئول : </td>
					<td width="51%" style="border:0"><span class="info" >'.$rec["writ_signature_post_owner"].'</span></td>
				</tr>
				<tr>
					<td style="border:0" class="text" >عنوان پست ثابت سازماني : </td>
					<td style="border:0"><span class="info" > '.$rec["writ_signature_post_title"].'</span></td>
				</tr>
				<tr>
					<td style="border:0">&nbsp;</td>
					<td style="border:0;padding-left:50px" align="center" class="text" >امضاء 
					'."<br><img width='20%' src=" . '/rcssimgs/SaghiSignature.jpg' . " >" .
					'</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td colspan="4" class="text" >نسخه : <span class="info" >'.$transcripts_title.'</span></td>
	</tr>
</table>
';


$out .= HTMLUtil::HTMLEnd();
$mpdf = new mPDF('fa', 'A4');
$mpdf->SetDirectionality('rtl');
$mpdf->SetDisplayMode('fullpage');
//$mpdf->SetFont(' Arial, Helvetica, sans-serif normal 12px/24px ');
$mpdf->WriteHTML($out);
$mpdf->Output();
exit;

?>