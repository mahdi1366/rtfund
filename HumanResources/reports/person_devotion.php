<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	90.08
//---------------------------

//print_r($keys); die();
define("staff_id",					"925");// => tbl2.staff_id
define("plname",					"411");// => tbl0.plname-tbl0.plname
define("pfname",					"412");// => tbl0.pfname-tbl0.pfname
define("national_code",				"414");// => tbl0.national_code-tbl0.national_code
define("idcard_no",					"413");// => tbl0.idcard_no-tbl0.idcard_no
define("idcard_serial",				"415");// => tbl0.idcard_serial-tbl0.idcard_serial
define("father_name",				"416");// => tbl0.father_name-tbl0.father_name
define("birth_date",				"417");// => tbl0.birth_date-tbl0.birth_date
define("birth_place",				"420");// => محل تولد
define("onduty_year",				"421");// => tbl3.onduty_year-tbl3.onduty_year
define("onduty_month",				"422");// => tbl3.onduty_month-tbl3.onduty_month
define("onduty_day",				"423");// => tbl3.onduty_day-tbl3.onduty_day
define("post_title",				"424");// => tbl4.title-tbl4.title
define("full_unit_title",			"911");// => tbl4.title-tbl4.title
define("emp_state_title",			"425");// => tbl7.Title-tbl3.emp_state
define("emp_state",					"4252");// => tbl3.emp_state
define("devotion_type_title",		"426");// => tbl11.Title-tbl10.devotion_type
define("devotion_type",				"4262");// => tbl10.devotion_type
define("personel_relation_title",	"427");// => tbl14.Title-tbl10.personel_relation
define("personel_relation",			"4272");// => tbl10.personel_relation
define("from_date",					"428");// => person_devotions.from_date
define("amount",					"1035");// => person_devotions.amount
define("PersonID",					"910");// => tbl0.PersonID-tbl0.PersonID
define("education_level" , "1095");

$data = $statement->fetchAll() ; 

$content = "";
$current_PersonID = $data[0][PersonID];
$relation = $data[0][personel_relation_title];
$devotion_info = array();
$index = 1;

for($i=0; $i < count($data)+1; $i++)
{
	if(!isset($data[$i]) || $current_PersonID != $data[$i][PersonID])
	{
		$content .= "
			<tr>
				<td>" . $index++ . "</td>
				<td>" . $data[$i-1][staff_id] . "</td>
				<td>" . $data[$i-1][plname] . "</td>
				<td>" . $data[$i-1][pfname] . "</td>
				<td>" . $data[$i-1][national_code] . "</td>
				<td>" . $data[$i-1][idcard_no] . "</td>
				<td>" . $data[$i-1][idcard_serial] . "</td>
				<td>" . $data[$i-1][father_name] . "</td>
				<td>" . $data[$i-1][education_level]."</td>
				<td>" . $relation . "</td>
				<td>" . DateModules::miladi_to_shamsi($data[$i-1][birth_date]) . "</td>
				<td>" . $data[$i-1][birth_place] . "</td>
				<td>" . $data[$i-1][onduty_year] . "</td>
				<td>" . $data[$i-1][onduty_month] . "</td>
				<td>" . $data[$i-1][onduty_day] . "</td>
				<td>" . $data[$i-1][post_title] . "</td>
				<td>" . $data[$i-1][full_unit_title] . "</td>
				<td>" . (in_array($data[$i-1][emp_state],array(EMP_STATE_PROBATIONAL_CEREMONIOUS, EMP_STATE_APPROVED_CEREMONIOUS)) ? "*" : "") . "</td>
				<td>" . (in_array($data[$i-1][emp_state],array(EMP_STATE_SOLDIER_CONTRACTUAL, EMP_STATE_CONTRACTUAL)) ? "*" : "") . "</td>
				<td>" . (isset($devotion_info['c0']) ? $devotion_info['c0'] : "") . "</td>
				<td>" . (isset($devotion_info['c1']) ? $devotion_info['c1'] : "") . "</td>
				<td>" . (isset($devotion_info['c2']) ? $devotion_info['c2'] : "") . "</td>
				<td>" . (isset($devotion_info['c3']) ? $devotion_info['c3'] : "") . "</td>
				<td>" . (isset($devotion_info['c4']) ? $devotion_info['c4'] : "") . "</td>
				<td>" . (isset($devotion_info['c5']) ? $devotion_info['c5'] : "") . "</td>
				<td>" . (isset($devotion_info['c6']) ? $devotion_info['c6'] : "") . "</td>
				<td>" . (isset($devotion_info['c7']) ? $devotion_info['c7'] : "") . "</td>
				<td>" . (isset($devotion_info['c8']) ? $devotion_info['c8'] : "") . "</td>
			</tr>";
		if(isset($data[$i]))
		{
			$relation = $data[$i][personel_relation_title];
			$devotion_info = array();
			$current_PersonID = $data[$i][PersonID];
			$i--;
			continue;
		}
	}
	else
	{
		
		switch ($data[$i][devotion_type])
		{
			case BEHOLDER_FAMILY_DEVOTION:
				$devotion_info['c0'] = $data[$i][personel_relation_title];
				break;
			case FIGHTING_DEVOTION :
				$relation = $data[$i][personel_relation_title];
				$devotion_info['c4'] = $data[$i][amount];
				break;
			case SACRIFICE_DEVOTION :
				$devotion_info['c1'] = DateModules::miladi_to_shamsi($data[$i][from_date]);
				$devotion_info['c2'] = $data[$i][amount];
				break;
			case FREEDOM_DEVOTION :
				$devotion_info['c3'] = $data[$i][amount];
				break;
			case WAR_REGION_TEACHING_DEVOTION :
				$devotion_info['c5'] = $data[$i][amount];
				break;
			case DEVOTION_THERAPY_DURATION :
				$devotion_info['c6'] = $data[$i][amount];
				break;
			case DEVOTION_FAMILY :
				$devotion_info['c7'] = $data[$i][amount];
				break;
			case ASHOURA_BATTALION :
				$devotion_info['c8'] = $data[$i][amount];
				break;
		}
	}
}

$tags =  array(
	'<!--data-->' => $content,
	'<!--now-->' => DateModules::shNow()
);

$content = file_get_contents("../../reports/person_devotion.html");
$content = str_replace(array_keys($tags), array_values($tags), $content);
echo $content;



?>