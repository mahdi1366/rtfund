<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	89.03
//---------------------------
require_once "../../header.inc.php";
require_once inc_dataGrid;

if(isset($_REQUEST["getFree"]))
{
	$proCode = $_POST["ProCode"];

	$query = "CREATE TEMPORARY TABLE FreeProCode TYPE=MyISAM AS select * from (select " . $proCode . " as ProCode";
	for($i=1; $i < 30; $i++)
		$query .= " union select " . ($proCode + $i) . " as ProCode";

	$query .= ") as tbl";
	PdoDataAccess::runquery($query);
	
	$query = "select FreeProCode.ProCode from FreeProCode left join staff using(ProCode)
				where staff.ProCode is null
				order by ProCode";

	$temp = PdoDataAccess::runquery($query);

	$colIndex = 1;
	echo "<table width='100%'><tr>";
	for($i=0; $i< count($temp); $i++, $colIndex++)
	{
		echo "<td><a href='javascript:void(0)' onclick='select(" . $temp[$i][0] . ");'>" . $temp[$i][0] . "</a></td>";
		if($colIndex == 10)
		{
			echo "</tr><tr>";
			$colIndex = 0;
		}
	}
	echo "</tr></table>";
	die();	
}

$FacCode = $_GET["FacCode"];
$EduGrpCode = $_GET["EduGrpCode"];

?>
<html>
	<meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>
	<link rel="stylesheet" type="text/css" href="/generalUI/ext4/resources/css/ext-all.css" />
	<link rel="stylesheet" type="text/css" href="/generalUI/ext4/resources/css/ext-rtl.css" />
	<link rel="stylesheet" type="text/css" href="/generalUI/ext4/resources/css/icons.css" />
	<body dir="rtl" onLoad="document.getElementById('ProCode').focus();">
	<script type="text/javascript" src="/generalUI/ext4/resources/ext-all.js"></script>
	<script type="text/javascript" src="/generalUI/ext4/resources/ext-extend.js"></script>
	<script type="text/javascript" src="/generalUI/ext4/ux/component.js"></script>
	<script type="text/javascript" src="/generalUI/ext4/ux/message.js"></script>
	<script type="text/javascript">
	var pnl;
	Ext.onReady(function(){
		pnl = new Ext.panel.Panel({
			renderTo: "searchPersonDIV",
			contentEl : "searchPersonPNL",
			title: "جستجوی ProCode های آزاد",
			autoHeight: true,
			width: 300,
			frame: true,
			buttons : [{
					text : "جستجو",
					handler : SearchFreeProCodes,
					iconCls : "search"
			}]
		});

		Ext.get("ProCode").addKeyListener(13, SearchFreeProCodes);
	});
	
	function select(value)
	{
		window.returnValue = value;
		window.close();
	}
	function SearchFreeProCodes()
	{
		var mask = new Ext.LoadMask(pnl, {msg:'در حال ذخيره سازي...'});
		mask.show();
		
		Ext.Ajax.request({
			url: 'ProCodeLOV.php?getFree=true',
			params:{
				ProCode: document.getElementById("ProCode").value
			},
			method: 'POST',

			success: function(response){
				mask.hide();
				document.getElementById("freeProCOdes").innerHTML = response.responseText;
			}
		});
	}
	</script>


	<center>
	<div id="searchPersonDIV" align="center">
	<table width="100%" id="searchPersonPNL" style="background-color:white">
		<tr>
			<td>ProCodeهای آزاد :</td>
			<td><input type="text" tabindex="1" id="ProCode" name="ProCode" class="x-form-text x-form-field"></td>
		</tr>
	</table>
	</div>
	<div class="panel" style="width:95%" id="freeProCOdes"></div>
	<table width=95% border=1 cellspacing=0>
		<tr style="background-color:#99BBE8">
			<td width=1%>رديف</td><td>نام و نام خانوادگي</td><td>ProCode</td>
		</tr>
			<?
				$query = "select * from persons p join staff s on(s.PersonID=p.PersonID AND s.person_type=p.person_type)
					where p.person_type=1 AND EduGrpCode=" . $EduGrpCode . " order by ProCode DESC";

				$res = PdoDataAccess::runquery($query);
				for($i=0; $i<count($res); $i++)
				{
					echo "<tr>";
					echo "<td>".($i+1)."</td>";
					echo "<td>". $res[$i]["pfname"] . " " . $res[$i]["plname"] . "</td>";
					echo "<td>". $res[$i]["ProCode"] . "</td>";
					echo "</tr>";
				}
			?>
	</table>
	</center>
</body>
</html>