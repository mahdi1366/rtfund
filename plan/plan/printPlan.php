<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1395.01
//-----------------------------

require_once '../header.inc.php';
require_once inc_dataGrid;
require_once 'plan.class.php';

if(empty($_REQUEST["PlanID"]))
	die();

$PlanID = $_REQUEST["PlanID"];
//-----------------------------------------------------
if(isset($_SESSION["USER"]["framework"]))
	$User = "Staff";
else
{
	if($_SESSION["USER"]["IsAgent"] == "YES")
		$User = "Agent";
	else if($_SESSION["USER"]["IsCustomer"] == "YES")
		$User = "Customer";
}
//-----------------------------------------------------
?>
<META http-equiv=Content-Type content="text/html; charset=UTF-8" >
<link rel="stylesheet" type="text/css" href="/generalUI/fonts/fonts.css" />
<style>
	body{font-family: Nazanin;font-size:12pt;}
	.level1{font-weight: bold;}
	.level2{font-weight: bold;}
	.level3{font-weight: bold;padding-right:20px;}
	.form{margin-top:10px;width:95%;border-collapse: collapse;text-align: justify; direction: rtl}
	.form td{padding:0 4px 0 4px;}
	.titles{background-color: #eee;font-weight: bold;text-align: center;}
	.values{text-align: center;}
</style>
<body dir="rtl">
<?
//--------------------- events --------------------
$dt = PdoDataAccess::runquery("select * from PLN_PlanEvents where PlanID=? order by EventDate", array($PlanID));
echo "<center><table class=form border=1>
	<tr>
		<td align=center colspan=2 class=titles>رویدادهای مرتبط با طرح</td>
	</tr>";
foreach($dt as $row)
{
	echo "<tr><td >" . $row["EventTitle"] . "</td><td align=center>" . 
			DateModules::miladi_to_shamsi($row["EventDate"]) . "</td></tr>";
}
echo "</table>";
//-------------------------------------------------

$query = "
		select 
			ifnull(g2.GroupDesc,g1.GroupDesc) level1,
			if(g2.GroupDesc is not null,g1.GroupDesc,g.GroupDesc) level2,
			if(g2.GroupDesc is not null,g.GroupDesc,'') level3,
			
			ifnull(g2.GroupID,g1.GroupID) levelcode1,
			if(g2.GroupID is not null,g1.GroupID,g.GroupID) levelcode2,
			if(g2.GroupID is not null,g.GroupID,'') levelcode3
			
		FROM PLN_groups g
		left join PLN_groups g1 on(g.ParentID=g1.GroupID)
		left join PLN_groups g2 on(g1.ParentID=g2.GroupID)
		
		left join PLN_Elements e on(e.ParentID=0 AND g.GroupID=e.GroupID)
		left join PLN_PlanItems pi on(pi.PlanID=? AND e.ElementID=pi.ElementID)
		
		group by g.GroupID
		having count(pi.RowID)>0 
		order by levelcode1,levelcode2,levelcode3";

$groups = PdoDataAccess::runquery($query, array($PlanID));

$currentlevel1 = "";
$currentlevel2 = "";
foreach($groups as $group)
{
	if($currentlevel1 != $group["levelcode1"])
	{
		echo "<div class=level1>" . $group["level1"] . "<hr></div>";
		$currentlevel1 = $group["levelcode1"];
	}
	if($currentlevel2 != $group["levelcode2"])
	{
		echo "<div class=level2>" . $group["level2"] . "</div>";
		$currentlevel2 = $group["levelcode2"];
	}
	if($group["levelcode3"] != "")
		echo "<div class=level3>" . $group["level3"] . "</div>";
	//-----------------------------------------------------------
	$GroupID = $group["levelcode3"] != "" ? $group["levelcode3"] : $group["levelcode2"];
	
	$dt = PdoDataAccess::runquery("select e.*,p.ElementValue
		from PLN_Elements e
		left join PLN_PlanItems p on(PlanID=? AND p.ElementID=e.ElementID)
		
		where GroupID=? AND ParentID=0 
		group by ElementID
		order by ElementID", array($PlanID, $GroupID));
	
	for($i=0; $i < count($dt); $i++)
	{
		if($dt[$i]["ElementType"] == "grid")
			printGrid($PlanID, $dt[$i]["ElementID"]);
		
		if($dt[$i]["ElementType"] == "panel")
			printForm($dt[$i]["ElementID"], $dt[$i]["ElementValue"]);		
	}
}

function printGrid($PlanID, $ParentID){

	$columns = PdoDataAccess::runquery("select * from PLN_Elements e 
		where ParentID=? order by ElementID", array($ParentID));
	
	echo "<center><table class=form border=1><tr>";
	foreach($columns as $col)
		echo "<td class=titles>" . $col["ElementTitle"] . "</td>";
	echo "</tr>";
	
	$data = PdoDataAccess::runquery("select ElementValue from PLN_PlanItems where PlanID=? AND ElementID=?",
		array($PlanID, $ParentID));
	
	foreach($data as $row)
	{
		$vals = array();
		$p = xml_parser_create();
		xml_parse_into_struct($p, $row["ElementValue"], $vals);
		xml_parser_free($p);
		
		$rowValues = array();
		foreach($vals as $element)
			if(strpos($element["tag"],"ELEMENT_") !== false)
				$rowValues[ substr($element["tag"],8) ] = empty($element["value"]) ? "" : $element["value"];
			
		foreach($columns as $col)
		{
			echo "<td class=values>";
			if($col["ElementType"] == "currencyfield")
			{
				$value = $rowValues[ $col["ElementID"] ]*1;
				echo number_format($value);
			}
			else
				echo $rowValues[ $col["ElementID"] ];
			echo "</td>";
		}
		echo "</tr>";	
	}
	
	echo "</table></center><br>";
}

function printForm($ParentID, $ElementValue){
	
	$vals = array();
	$p = xml_parser_create();
	xml_parse_into_struct($p, $ElementValue, $vals);
	xml_parser_free($p);
	
	$planValues = array();
	foreach($vals as $element)
		if(strpos($element["tag"],"ELEMENT_") !== false)
			$planValues[ substr($element["tag"],8) ] = empty($element["value"]) ? "" : $element["value"];
	
	$dt = PdoDataAccess::runquery("select *	from PLN_Elements e 
		where ParentID=? order by ElementID", array($ParentID));
	
	$index = 0;
	echo "<center>";
	foreach($dt as $element)
	{
		if($element["ElementType"] == "displayfield")
		{
			echo "<div class=form>" .  nl2br($element["ElementValues"]) . "</div>";
			continue;
		}
		if(empty($planValues[ $element["ElementID"] ]))
			continue;
		if($element["ElementType"] == "textarea")
		{
			echo "<div class=form>" . nl2br($planValues[ $element["ElementID"] ]) . "</div>";
			break;
		}
		if($index == 0)
			echo "<table class=form border=1>";
		
		if($index % 2 == 0)
			echo "<tr>";
		
		$colspan = 1;
		if($element["ElementTitle"] != "")
		{
			echo "<td class=titles>" . $element["ElementTitle"] . "</td>";
			$colspan = 2;
		}
		echo "<td class=values colspan=$colspan>";
		if($element["ElementType"] == "currencyfield")
			echo number_format($planValues[ $element["ElementID"] ]);
		else
			echo $planValues[ $element["ElementID"] ];
		echo "</td>";
		
		if($index % 2 != 0)
			echo "</tr>";
		
		$index++;
	}
	if($index > 0)
		echo "</table>";
	
	echo "</center><br>";
}
?>
