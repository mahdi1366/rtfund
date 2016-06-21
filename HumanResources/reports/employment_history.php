<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	90.11
//---------------------------

print_r($keys);

die();

define("pfname",				"202");// => tbl10000.pfname-tbl10000.pfname
define("plname",				"203");// => tbl10000.plname-tbl10000.plname
define("father_name",			"204");// => tbl10000.father_name-tbl10000.father_name
define("staff_id",				"205");// => tbl10002.staff_id-tbl10002.staff_id
define("birth_date",			"206");// => tbl10000.birth_date-tbl10000.birth_date
define("birth_place",			"207");// => استان و شهر محل تولد
define("idcard_no",				"210");// => tbl10000.idcard_no-tbl10000.idcard_no
define("national_code",			"211");// => tbl10000.national_code-tbl10000.national_code
define("worktime_type_title",	"212");// => tbl6.Title-tbl10003.worktime_type
define("worktime_type",			"2122");// => tbl10003.worktime_type
define("science_level_title",	"213");// => tbl8.Title-tbl10003.science_level
define("science_level",			"2132");// => tbl10003.science_level
define("sf_sb_title",			"215");// => رشته - گرایش تحصیلی
define("doc_date",				"218");// => tbl10019.doc_date-tbl10019.doc_date
define("study_palce",			"223");// => محل اخذ
define("unitName",				"226");// => concat(tbl2.ptitle,'-',tbl1.ptitle)-tbl10003.ouid
define("ouid",					"2262");// => tbl10003.ouid
define("emp_state_title",		"227");// => tbl3.Title-tbl10003.emp_state
define("emp_state",				"2272");// => tbl10003.emp_state
define("post_no",				"229");// => tbl10005.post_no-tbl10005.post_no
define("post_title",			"230");// => tbl10005.title-tbl10005.title
define("exe_post_title",		"238");// => tbl18.title-tbl10020.post_id
define("exe_post_id",			"2382");// => tbl10020.post_id
define("exe_from_date",			"239");// => tbl10020.from_date-tbl10020.from_date
define("razmAmount",			"241");// => مدت رزمندگی
define("janbazAmount",			"242");// => درصد جانبازی
define("military_to_date",		"243");// => tbl10000.military_to_date-tbl10000.military_to_date
define("military_from_date",	"244");// => tbl10000.military_from_date-tbl10000.military_from_date
define("military_duration",		"245");// => tbl10000.military_duration-tbl10000.military_duration
define("military_type_title",	"246");// => tbl23.Title-tbl10000.military_type
define("military_type",			"2462");// => tbl10000.military_type
define("base",					"247");// => tbl10003.base-tbl10003.base
define("person_type_title",		"248");// => tbl25.Title-tbl10000.person_type
define("person_type",			"2482");// => tbl10000.person_type
define("PersonID",				"249");// => tbl10000.PersonID-tbl10000.PersonID
define("education_level_title",	"276");//] => tbl7.Title-tbl10003.education_level
define("education_level",		"2762");//] => tbl10003.education_level
define("show_in_summary_doc",	"2772");// => tbl10022.show_in_summary_doc-tbl10022.show_in_summary_doc
define("exe_to_date",			"281");//tbl27.to_date-tbl27.to_date
define("devotion_type_title",	"282");//] => tbl34.Title-tbl33.devotion_type
define("devotion_type",			"2822");//] => tbl33.devotion_type

define("all_writ_id",			"250");// => tbl10004.writ_id-tbl10004.writ_id
define("all_writ_ver",			"251");// => tbl10004.writ_ver-tbl10004.writ_ver
define("all_execute_date",		"252");// => tbl10004.execute_date-tbl10004.execute_date
define("all_emp_state_title",	"253");// => tbl14.Title-tbl10004.emp_state
define("all_emp_state",			"2532");// => tbl10004.emp_state
define("all_emp_mode_title",	"254");// => tbl15.Title-tbl10004.emp_mode
define("all_emp_mode",			"2542");// => tbl10004.emp_mode
define("all_unitName",			"255");// => concat(tbl13.ptitle,'-',tbl12.ptitle)-tbl10004.ouid
define("all_ouid",				"2552");// => tbl10004.ouid
define("all_job_title",			"256");// => tbl10007.title-tbl10007.title
define("all_post_title",		"257");// => tbl10005.title-tbl10005.title
define("all_send_letter_no",	"258");// => tbl10004.send_letter_no-tbl10004.send_letter_no
define("all_science_level_title","259");// => tbl16.Title-tbl10004.science_level
define("all_science_level",		"2592");// => tbl10004.science_level
define("all_annual_effect_title","260");// => tbl17.Title-tbl10004.annual_effect
define("all_annual_effect",		"2602");// => tbl10004.annual_effect
define("all_base",				"275");//] => tbl10004.base-tbl10004.base


$cur_staff = "";
$index = 0;
$history = "";
$row_index = 0;
$valid_writs = array();
$total_year = 0;$total_month = 0;$total_day = 0;
$total_non_year = 0;$total_non_month = 0;$total_non_day = 0;
$history_row = array();

for($i=0; $i < count($data)+1; $i++)
{
	if(!isset($data[$i]) || $cur_staff != $data[$i][staff_id])
	{
		if($i != 0)
		{			
			for($k=0; $k < count($valid_writs); $k++)
			{
				//--------------------------------------------------------------------------
				$first_date = $valid_writs[$k][all_execute_date];
				$last_date = ($k+1 < count($valid_writs)) ? $valid_writs[$k+1][all_execute_date] : date("Y-m-d");

				$diff = strtotime($last_date) - strtotime($first_date);
				$diff = floor($diff/(60*60*24));

				$year = (floor($diff/ 365.25));
				$month = (floor(($diff - floor($diff / 365.25)*365.25  ) / 30.4375 ));
				$day = floor($diff - floor($diff / 365.25)*365.25 -  floor(($diff - floor($diff / 365.25)*365.25  ) / 30.4375 )*30.4375);
				$history_row[DateModules::miladi_to_shamsi($first_date) . "_4"] =
					"<tr>
						<td>" . ($valid_writs[$k][person_type] ==  HR_PROFESSOR ? $valid_writs[$k][all_science_level_title] :
									($valid_writs[$k][person_type] ==  HR_WORKER ? $valid_writs[$k][all_job_title] : $valid_writs[$k][all_post_title])) . "</td>
						<td>" . DateModules::miladi_to_shamsi($first_date) . "</td>
						<td>" . DateModules::miladi_to_shamsi($last_date) . "</td>
						<td>" . $year . "</td>
						<td>" . $month . "</td>
						<td>" . $day . "</td>
						<td>" . ($valid_writs[$k][all_annual_effect] == "3" ? "غیر قابل قبول" : "قابل قبول") . "</td>
						<td>" . ($valid_writs[$k][all_annual_effect] == "3" ? $valid_writs[$k][all_emp_mode_title] : $valid_writs[$k][all_unitName]) . "</td>
						<td>" . $valid_writs[$k][all_emp_state_title] . "</td>
						<td>" . $valid_writs[$k][all_send_letter_no] . "</td>
						<td>" . DateModules::miladi_to_shamsi($valid_writs[$k][all_execute_date]) . "</td>
					</tr>";
				
				if($valid_writs[$k][all_annual_effect] == "3")
				{
					$total_non_year += $year;
					$total_non_month += $month;
					$total_non_day += $day;
				}
				else
				{
					$total_year += $year;
					$total_month += $month;
					$total_day += $day;
				}				
			}
			$keys = array_keys($history_row);
			sort($keys);
			for($k=0; $k < count($keys); $k++)
				$history .= $history_row[$keys[$k]];
			//---------------- sum row ----------------------
			$total = ($total_year + $total_non_year)*365.25 + ($total_month + $total_non_month)*30.4375 + ($total_day + $total_non_day);
			$y = (int)($total / 365.25);
			$m = (int)(($total - $y*365.25) / 30.4375);
			$d = round(($total - $y*365.25 - $m*30.4375));
			$history .= "<tr style='font-weight:bold;background-color:#F0F3FF'>
				<td align=right colspan=4>جمع کل سنوات : " . $y . " سال و " . $m . " ماه و " . $d . " روز" ."</td>";

			$total = ($total_year)*365.25 + ($total_month)*30.4375 + ($total_day);
			$y = (int)($total / 365.25);
			$m = (int)(($total - $y*365.25) / 30.4375);
			$d = round(($total - $y*365.25 - $m*30.4375));
			$history .= "<td align=right colspan=4>سنوات قابل قبول : " . $y . " سال و " . $m . " ماه و " . $d . " روز" ."</td>";

			$total = ($total_non_year)*365.25 + ($total_non_month)*30.4375 + ($total_non_day);
			$y = (int)($total / 365.25);
			$m = (int)(($total - $y*365.25) / 30.4375);
			$d = round(($total - $y*365.25 - $m*30.4375));
			$history .= "<td align=right colspan=3>سنوات غير قابل قبول : " . $y . " سال و " . $m . " ماه و " . $d . " روز" ."</td></tr>";
			
			//----------------------------------------------
			$tags =  array('<!--history-->' => $history);
			$content = str_replace(array_keys($tags), array_values($tags), $content);
			echo $content;
			if(!isset($data[$i]))
				break;
			echo "<div class='pageBreak'></div>";			
		}			

		$prof1 = $prof2 = "";
		if($data[$i][person_type] == HR_PROFESSOR)
		{
			$prof1 = '<tr><td style="background-color:#F0F3FF">مرتبه :</td><td style="font-weight:bold">'.$data[$i][science_level_title].'</td>
						  <td style="background-color:#F0F3FF">پايه :</td><td style="font-weight:bold">'.$data[$i][base].'</td></tr>';
			
			if($data[$i][exe_to_date] == "" || $data[$i][exe_to_date] == "0000-00-00" || 
				DateModules::CompareDate($data[$i][exe_to_date], DateModules::Now()) > 0)
				$prof2 =  '<tr><td style="background-color:#F0F3FF">سمت اجرايي :</td><td style="font-weight:bold">'.$data[$i][exe_post_title].'</td>
							  <td style="background-color:#F0F3FF">تاريخ سمت اجرايي :</td><td style="font-weight:bold">'.DateModules::miladi_to_shamsi($data[$i][exe_from_date]).'</td></tr>';
			else
				$prof2 =  '<tr><td style="background-color:#F0F3FF">سمت اجرايي :</td><td style="font-weight:bold"></td>
							  <td style="background-color:#F0F3FF">تاريخ سمت اجرايي :</td><td style="font-weight:bold"></td></tr>';
		}
		$tags =  array(
			'<!--post_title-->' => $data[$i][person_type] == HR_WORKER ? 'عنوان شغل' : 'عنوان پست',
			'<!--fname-->' => $data[$i][pfname],
			'<!--lname-->' => $data[$i][plname],
			'<!--father_name-->' => $data[$i][father_name],
			'<!--staff_id-->' => $data[$i][staff_id],
			'<!--birth_date-->' => DateModules::miladi_to_shamsi($data[$i][birth_date]),
			'<!--birth_place-->' => $data[$i][birth_place],
			'<!--idcard_no-->' => $data[$i][idcard_no],
			'<!--national_code-->' => $data[$i][national_code],
			'<!--prof1-->' => $prof1,
			'<!--worktime_type-->' => $data[$i][worktime_type_title],
			'<!--military_type-->' => $data[$i][military_type_title],
			'<!--education_level-->' => $data[$i][education_level_title],
			'<!--field_title-->' =>$data[$i][sf_sb_title],
			'<!--doc_date-->' => DateModules::miladi_to_shamsi($data[$i][doc_date]),
			'<!--studyPlace-->' => $data[$i][study_palce],
			'<!--razm-->' => $data[$i][razmAmount],
			'<!--janbaz-->' => $data[$i][janbazAmount],
			'<!--unit-->' => $data[$i][unitName],
			'<!--emp_state-->' => $data[$i][emp_state_title],
			'<!--post_no-->' => $data[$i][post_no],
			'<!--last_post_title-->' => $data[$i][post_title],
			'<!--prof2-->' => $prof2,
			'<!--now-->' => DateModules::shNow()
                  );

		// مشخص کردن فایل template  مربوط به خلاصه پرونده
		$content = file_get_contents("../../reports/summary_doc.htm");
		$content = str_replace(array_keys($tags), array_values($tags), $content);
		
		$cur_staff = $data[$i][staff_id];
		$index = $i;
		$row_index = $i;
		$history = "";
		$i--;
		$valid_writs = array();
		$total_year = 0;$total_month = 0;$total_day = 0;
		$total_non_year = 0;$total_non_month = 0;$total_non_day = 0;
		$history_row = array();
		continue;
	}
	//--------------------------------------------------------------------------
	// get last version in a date
	if($i-1 >= $index && $data[$i][all_execute_date] == $data[$i-1][all_execute_date] &&
		($data[$i][all_writ_id] > $data[$i-1][all_writ_id] ||
			($data[$i][all_writ_id] == $data[$i-1][all_writ_id] && $data[$i][all_writ_ver] > $data[$i-1][all_writ_ver])))
	{
		continue;
	}
	
	if($data[$i][show_in_summary_doc] == 0 &&
	  $i == $row_index+1 && $data[$i][all_annual_effect] == $data[$row_index][all_annual_effect])
	{
		$row_index = $i;
		continue;
	}
	
	$row_index = $i;
	$valid_writs[] = $data[$i];
	//if($i == $index && $data[$i][all_annual_effect] == 3 && $data[$i][all_show_in_summary_doc] == 0)
	//	continue;

	if($index == $i)
	{
		if($data[$i][military_from_date] != "" && $data[$i][military_to_date] != "" && $data[$i][military_duration] != "")
		{
			$history_row[DateModules::miladi_to_shamsi($data[$i][military_from_date]) . "_1"] =
				"<tr>
					<td>&nbsp;</td>
					<td>" . DateModules::miladi_to_shamsi($data[$i][military_from_date]) . "</td>
					<td>" . DateModules::miladi_to_shamsi($data[$i][military_to_date]) . "</td>
					<td>" . floor($data[$i][military_duration] / 12) . "</td>
					<td>" . ($data[$i][military_duration] - (floor($data[$i][military_duration] / 12) * 12)) . "</td>
					<td>0</td>
					<td>قابل قبول</td>
					<td>سربازی</td>
					<td>سربازی</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>";
			$total_year += floor($data[$i][military_duration] / 12);
			$total_month += ($data[$i][military_duration] - (floor($data[$i][military_duration] / 12) * 12));
			$total_day += 0;
		}
		//......................................................................
		require_once "../../personal/persons/class/employment.class.php";
		$temp = manage_person_employment::GetAllEmp("PersonID=" . $data[$i][PersonID]);
		$history2 = "";
		for($j=0; $j < count($temp); $j++)
		{
			if($temp[$j]["retired_duration_year"] != 0 || $temp[$j]["retired_duration_month"] != 0 ||
				$temp[$j]["retired_duration_day"] != 0)
			{
				$history_row[DateModules::miladi_to_shamsi($temp[$j]["from_date"]) . "_2"] =
					"<tr>
						<td>" . $temp[$j]["title"] . "</td>
						<td>" . DateModules::miladi_to_shamsi($temp[$j]["from_date"]) . "</td>
						<td>" . DateModules::miladi_to_shamsi($temp[$j]["to_date"]) . "</td>
						<td>" . $temp[$j]["retired_duration_year"] . "</td>
						<td>" . $temp[$j]["retired_duration_month"] . "</td>
						<td>" . $temp[$j]["retired_duration_day"] . "</td>
						<td>قابل قبول</td>
						<td>" . $temp[$j]["organization"] . "</td>
						<td>" . $temp[$j]["empstateTitle"] . "</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
					</tr>";
				$total_year += $temp[$j]["retired_duration_year"];
				$total_month += $temp[$j]["retired_duration_month"];
				$total_day += $temp[$j]["retired_duration_day"];
			}
			//------------------------------------------------------------------
			$year = floor((($temp[$j]["duration_year"] - $temp[$j]["retired_duration_year"])*365.25 +
							($temp[$j]["duration_month"] - $temp[$j]["retired_duration_month"])*30.4375 +
							($temp[$j]["duration_day"] - $temp[$j]["retired_duration_day"])) / 365.25);
			$month = floor(((($temp[$j]["duration_year"] - $temp[$j]["retired_duration_year"])*365.25 +
							($temp[$j]["duration_month"] - $temp[$j]["retired_duration_month"])*30.4375 +
							($temp[$j]["duration_day"] - $temp[$j]["retired_duration_day"])) -
							floor((($temp[$j]["duration_year"] - $temp[$j]["retired_duration_year"])*365.25 +
							($temp[$j]["duration_month"] - $temp[$j]["retired_duration_month"])*30.4375 +
							($temp[$j]["duration_day"] - $temp[$j]["retired_duration_day"])) / 365.25)*365.25) / 30.4375);
			$day = ((($temp[$j]["duration_year"] - $temp[$j]["retired_duration_year"])*365.25 +
							($temp[$j]["duration_month"] - $temp[$j]["retired_duration_month"])*30.4375 +
							($temp[$j]["duration_day"] - $temp[$j]["retired_duration_day"]))-
							floor((($temp[$j]["duration_year"] - $temp[$j]["retired_duration_year"])*365.25 +
							($temp[$j]["duration_month"] - $temp[$j]["retired_duration_month"])*30.4375 +
							($temp[$j]["duration_day"] - $temp[$j]["retired_duration_day"])) / 365.25) *365.25
							-round(floor(((($temp[$j]["duration_year"] - $temp[$j]["retired_duration_year"])*365.25 +
							($temp[$j]["duration_month"] - $temp[$j]["retired_duration_month"])*30.4375 +
							($temp[$j]["duration_day"] - $temp[$j]["retired_duration_day"])) -
							floor((($temp[$j]["duration_year"] - $temp[$j]["retired_duration_year"])*365.25 +
							($temp[$j]["duration_month"] - $temp[$j]["retired_duration_month"])*30.4375 +
							($temp[$j]["duration_day"] - $temp[$j]["retired_duration_day"])) / 365.25)*365.25) / 30.4375)*30.4375));
			if($year != 0 || $month != 0 || $day !=0)
			{
				$history_row[DateModules::miladi_to_shamsi($temp[$j]["from_date"]) . "_3"] =
					"<tr>
						<td>" . $temp[$j]["title"] . "</td>
						<td>" . DateModules::miladi_to_shamsi($temp[$j]["from_date"]) . "</td>
						<td>" . DateModules::miladi_to_shamsi($temp[$j]["to_date"]) . "</td>
						<td>" . $year . "</td>
						<td>" . $month . "</td>
						<td>" . round($day) . "</td>
						<td>غیر قابل قبول</td>
						<td>" . $temp[$j]["organization"] . "</td>
						<td>" . $temp[$j]["empstateTitle"] . "</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
					</tr>";
				$total_non_year += $year;
				$total_non_month += $month;
				$total_non_day += $day;
			}
		}
	}	
}

?>
