<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	88.12
//---------------------------
require_once '../header.inc.php';
require_once 'unit.data.php';
ini_set("display_errors","On") ; 
$drp_units = manage_units::DRP_Units("org_units","","","210","");

jsConfig::initialExt();
jsConfig::tree();
jsConfig::window();
require_once 'ManageUnits.js.php';
?>
<body dir="rtl">
	<table width="750px">
		<tr>
			<td width="40%">
				<div id="tree-div" style="overflow:auto; width:250px;border:1px solid #c3daf9;"></div>
			</td>
			<td valign="top" style="padding-right: 5px">
				<!-- -------------------------------------------- -->
				<div id="DIV_NewUnit" class="x-hide-display">
					<div id="PNL_NewUnit">
					</div>
				</div>
				<!-- -------------------------------------------- -->
			</td>
		</tr>
	</table>
	<!-- -------------------------------------------- -->
	<div id="moveDIV" class="x-hidden">
		<div id="movePNL">
			<table width="100%">
				<tr>
					<td width="20%">انتخاب واحد پدر :</td>
					<td><?= $drp_units?></td>
				</tr>
			</table>
		</div>
	</div>
	<!-- -------------------------------------------- -->
</body>