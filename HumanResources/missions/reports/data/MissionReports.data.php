<?php

//-----------------------------
//	Programmer	: Fatemipour
//	Date		: 94.02
//-----------------------------
ini_set('display_errors', 'On');
require_once "../../../header.inc.php";
require_once "ReportGenerator.class.php";

$query = "select 
            mr.RequestID,
            concat( persons.pfname , ' ' , persons.plname) as person,
            null as unit,
            mr.subject,
            g2j(mr.FromDate) as FromDate, g2j(ToDate) as ToDate,
            concat(cities.ptitle , ' ' , states.ptitle) as location,
            bi1.Title as status,
            org_new_units.ptitle as Sender
          from Miss_Requests mr
          join hrmstotal.persons using (PersonID)
          join hrmstotal.cities using (city_id)
          join hrmstotal.states on (states.state_id = mr.state_id)
          join Basic_Info bi1 on (bi1.TypeID = " . TYPE_STATUS . " and bi1.InfoID = mr.status)
          join staff on (persons.PersonID = staff.PersonID  and persons.person_type = staff.person_type )
          join org_new_units on (org_new_units.ouid = staff.ouid)
          ";
$where = " where 1=1 ";
$whereParams = array();

//-------------------------------------------------------------------------
$statuses = '';
foreach ($_POST as $cond) {
    if (substr($cond, 0, 6) == "status")
        $statuses .= "," . substr($cond, 7, 2);
}
$statuses = trim( $statuses ,',');
$where .= " AND mr.status in ( $statuses )";
//$whereParams[":status"] = substr($statuses, 1, strlen($statuses));

if (!empty($_POST['from_date'])) {
    $where .= " AND mr.FromDate >= :fd";
    $whereParams[":fd"] = DateModules::shamsi_to_miladi($_POST['from_date'], '/');
}

if (!empty($_POST['to_date'])) {
    $where .= " AND mr.ToDate <= :td";
    $whereParams[":td"] = DateModules::shamsi_to_miladi($_POST['to_date'], '/');
}
//-------------------------------------------------------------------------
$order = " order by mr.RequestID ";
$res = PdoDataAccess::runquery($query . $where . $order." LIMIT 500", $whereParams);
if ($_SESSION['User']->UserID == 'fatemipour') echo PdoDataAccess::GetLatestQueryString (); 
if ($_SESSION['User']->UserID == 'bmahdipour') {


 }

$rpg = new ReportGenerator();
$rpg->excel = !empty($_REQUEST["excel"]);

$rpg->addColumn(" شماره درخواست", "RequestID");
$rpg->addColumn(" درخواست دهنده", "person");
$rpg->addColumn(" واحد اعزام کننده", "Sender");
$rpg->addColumn(" موضوع  ", "subject");
$rpg->addColumn(" از تاریخ  ", "FromDate");
$rpg->addColumn(" لغایت  ", "ToDate");
$rpg->addColumn("مکان  ", "location");
$rpg->addColumn(" وضعیت  ", "status");

$rpg->mysql_resource = $res;
$ReportHeader = " گزارش ماموریت های روزانه ";
if (!$rpg->excel) {
    echo '<META http-equiv=Content-Type content="text/html; charset=UTF-8" ><body dir="rtl">';
    echo "<table style='border:2px groove #9BB1CD;border-collapse:collapse;width:100%'><tr>
			<td width=60px><img src='../../img/ferdowsi_logo.gif'></td>
			<td align='center' style='font-family:b titr;font-size:15px'>
				" . $ReportHeader . "
			</td>
			<td width='200px' align='center' style='font-family:tahoma;font-size:11px'>تاریخ تهیه گزارش : "
    . DateModules::shNow() . "<br>";
    echo "</td></tr></table>";
}
$rpg->generateReport();
?>
