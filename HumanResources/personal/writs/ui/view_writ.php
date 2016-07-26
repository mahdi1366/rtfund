<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	94.12
//---------------------------
require_once '../../../header.inc.php';
require_once '../class/writ.class.php';

$staff_id = $_REQUEST["STID"];
$writ_id = $_REQUEST["WID"];
$writ_ver = $_REQUEST["WVER"];


$temp = PdoDataAccess::runquery("select writ_id, writ_ver, staff_id, execute_date from HRM_writs
	where staff_id=?
	order by execute_date,writ_id,writ_ver", array($staff_id));

$jsArr = "";
$currentWritIndex = 0;

for($i=0; $i < count($temp); $i++)
{
	if($writ_id == $temp[$i]["writ_id"] && $writ_ver == $temp[$i]["writ_ver"])
		$currentWritIndex = $i;
	$jsArr .= "{writ_id: " . $temp[$i]["writ_id"] . ", writ_ver: " . $temp[$i]["writ_ver"] . ", execute_date: '" .
		$temp[$i]["execute_date"] . "'},";
}
$jsArr = substr($jsArr, 0, strlen($jsArr)-1);

require_once '../js/view_writ.js.php';
?>

<style>
	.moveItem, .disable_moveItem{width: 120px;}
	div.moveItem:hover{background-color: #DBCBF2;}
	.moveItem{cursor: pointer;background-color: #F4EBFC;}
	.disable_moveItem{opacity:.5;-moz-opacity:.5;filter:alpha(opacity=50);cursor: default;background-color: #efefef;}
	.moveItem img, .disable_moveItem img{vertical-align: middle;}
</style>
<div id="form_viewWrit">
	<center>
	<div id="DIV_moveWrit">
		<table  border="1" cellspacing="0" cellpadding="0">
			<tr>
				<td>
					<div id="btn_last" class="moveItem" onclick="ViewWritObject.moveWrit(this,'last');">
						<img src="img/right4.png" class="">آخرین حکم</div></td>
				<td>
					<div id="btn_next" class="moveItem"  onclick="ViewWritObject.moveWrit(this,'next');">
						<img src="img/right.png" class="">حکم بعدی</div></td>
				<td>
					<div id="btn_previous" class="moveItem" class="moveItem" onclick="ViewWritObject.moveWrit(this,'previous');">
						<img   src="img/left.png" class="">حکم قبلی</div></td>
				<td>
					<div id="btn_nextVersion" class="moveItem" onclick="ViewWritObject.moveWrit(this,'nextVersion');">
						<img src="img/right2.png" class="">نسخه بعدی حکم</div></td>
				<td>
					<div id="btn_previosVersion" class="moveItem" onclick="ViewWritObject.moveWrit(this,'previosVersion');">
						<img src="img/left2.png" class="">نسخه قبلی حکم</div></td>
				<td>
					<div id="btn_first" class="moveItem" onclick="ViewWritObject.moveWrit(this,'first');">
						<img src="img/left4.png" class="">اولین حکم</div></td>
				<td>
			</tr>
		</table>
	</div>
	<!------------------------------------------------------------------ -->
	<div id="DIV_writ"></div>
	<div id="newItemWindow" class="x-hidden"></div>
	</center>
</div>