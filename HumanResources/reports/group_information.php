<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	90.09
//---------------------------

define("person_type",			"11402"); //tbl0.person_type
define("person_type_title",		"1138"); // tbl7.Title-tbl0.person_type
define("emp_mode",				"9802"); //tbl2.emp_mode
define("emp_mode_title",		"980"); //tbl3.Title-tbl2.emp_mode
define("CountPID",				"983"); //persons.PersonID

$record = "";
$data = $statement->fetchAll() ; 

for($i=0 ; $i< count($data) ; $i++)
{
	
	$record .= "<tr><td>".$data[$i][person_type_title]."</td><td>".$data[$i][emp_mode_title]."</td>
					<td><a href='/HumanResources/ReportGenerator/ui/reportResult.php?".
						"Q0=15&pt=".$data[$i][person_type].
						"&emp_mode=".$data[$i][emp_mode].
						"&FDATE=" . DateModules::shamsi_to_miladi($_POST["FDATE:72"]) .
						"&TDATE=" . DateModules::shamsi_to_miladi($_POST["TDATE:72"]) .
						
					"' target = '_blank' >".$data[$i][CountPID]."</a></td></tr>";
}

	$tags =  array('<!--record-->' => $record ,
				   '<!--now-->' => DateModules::shNow());

	$content = file_get_contents("../../reports/group_information.htm");
	$content = str_replace(array_keys($tags), array_values($tags), $content);
	echo $content;
?>
