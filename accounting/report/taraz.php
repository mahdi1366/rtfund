<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.06
//-----------------------------

require_once '../header.inc.php';
require_once "ReportGenerator.class.php";

if(isset($_REQUEST["show"]))
{
	showReport();
}

function showReport(){
	
	$dt = PdoDataAccess::runquery("select * from BSC_branches");
	$branches = array();
	foreach($dt as $row)
		$branches[ $row["BranchID"] ] = $row["BranchName"];
	
	//......................................................
	
	$levelsDescArr = array(
		"l0" => "گروه",
		"l1" => "کل",
		"l2" => "معین",
		"l3" => "جزء معین",
		"l4" => "جزء معین2",
		"l5" => "تفصیلی"
		);
	global $level;
	$level = empty($_REQUEST["level"]) ? "l1" : $_REQUEST["level"];
	
	$rpg = new ReportGenerator();
	$rpg->excel = !empty($_POST["excel"]);
	$whereParam = array();
	$where = "";
	
	$select = "select 
		sum(di.DebtorAmount) bdAmount,
		sum(di.CreditorAmount) bsAmount,
		b0.BlockDesc level0Desc,
		b1.BlockDesc level1Desc,
		b2.BlockDesc level2Desc,
		b3.BlockDesc level3Desc,
		b4.BlockDesc level4Desc,
		
		b0.BlockCode level0Code,
		b1.BlockCode level1Code,
		b2.BlockCode level2Code,
		b3.BlockCode level3Code,
		b4.BlockCode level4Code,
		b.InfoDesc TafsiliTypeDesc,
		t.TafsiliDesc TafsiliDesc,
		t.TafsiliCode TafsiliCode,
		bi2.InfoDesc TafsiliTypeDesc2,
		t2.TafsiliDesc TafsiliDesc2,
		t2.TafsiliCode TafsiliCode2,
		
		b1.GroupID as level0,
		cc.level1,
		cc.level2,
		cc.level3,
		cc.level4,

		di.TafsiliType,
		di.TafsiliType2,
		di.TafsiliID,
		di.TafsiliID2,
		
		tdt.StartCycleDebtor,
		tdt.StartCycleCreditor
		";
	$from = " from ACC_DocItems di 
				join ACC_docs d using(DocID)
				join ACC_CostCodes cc using(CostID)
				join ACC_blocks b1 on(level1=b1.BlockID)
				left join ACC_blocks b0 on(b1.GroupID=b0.BlockID)
				left join ACC_blocks b2 on(level2=b2.BlockID)
				left join ACC_blocks b3 on(level3=b3.BlockID)
				left join ACC_blocks b4 on(level4=b4.BlockID)
				left join BaseInfo b on(TypeID=2 AND di.TafsiliType=InfoID)
				left join ACC_tafsilis t using(TafsiliID)
				left join BaseInfo bi2 on(bi2.TypeID=2 AND di.TafsiliType2=bi2.InfoID)
				left join ACC_tafsilis t2 on(t2.TafsiliID=di.TafsiliID2)
				
				left join (
					select CycleID,CostID,di.TafsiliID,di.TafsiliID2,sum(DebtorAmount) StartCycleDebtor,
							sum(CreditorAmount) StartCycleCreditor
					from ACC_DocItems di join ACC_docs using(DocID)
					where DocType=1 AND CycleID=".$_SESSION["accounting"]["CycleID"].
					(!empty($_POST["BranchID"]) ? " AND BranchID=:b" : "") . "	
					group by CostID,TafsiliID,TafsiliID2
				)tdt on(d.CycleID=tdt.CycleID AND di.CostID=tdt.CostID AND di.TafsiliID=tdt.TafsiliID AND di.TafsiliID2=tdt.TafsiliID2)
	";
	$group = "";
	if($level >= "l0")
	{
		$col = $rpg->addColumn("کد گروه", "level0Code");
		$col = $rpg->addColumn("گروه", "level0Desc",$level =="l0" ? "levelRender" : "");
		$group = "b1.GroupID";
		$col->ExcelRender = false;
	}
	
	if($level >= "l1")
	{
		$col = $rpg->addColumn("کد کل", "level1Code");
		$col = $rpg->addColumn("کل", "level1Desc",$level =="l1" ? "levelRender" : "");
		$col->ExcelRender = false;
		$group = "cc.level1";
	}
	if($level >= "l2")
	{
		$group .= ",cc.level2"; 
		$col = $rpg->addColumn("کد معین", "level2Code");
		$col = $rpg->addColumn("معین", "level2Desc", $level =="l2" ? "levelRender" : "");
		$col->ExcelRender = false;
	}
	if($level >= "l3")
	{
		$group .= ",cc.level3"; 
		$col = $rpg->addColumn("کد جزء معین", "level3Code");
		$col = $rpg->addColumn("جزء معین", "level3Desc", $level =="l3" ? "levelRender" : "");
		$col->ExcelRender = false;
	}
	if($level >= "l4")
	{
		$group .= ",cc.level4"; 
		$col = $rpg->addColumn("کد جزء معین2", "level4Code");
		$col = $rpg->addColumn("جزء معین2", "level4Desc", $level =="l4" ? "levelRender" : "");
		$col->ExcelRender = false;
	}
	if($level == "l5")
	{
		$group .= ",di.TafsiliID,di.TafsiliID2";
		$col = $rpg->addColumn("کد تفصیلی", "TafsiliCode");
		$col = $rpg->addColumn("تفصیلی", "TafsiliDesc", "showDocs");
		$col->ExcelRender = false;
		
		$col = $rpg->addColumn("کد تفصیلی2", "TafsiliCode2");
		$col = $rpg->addColumn("تفصیلی2", "TafsiliDesc2", "showDocs2");
		$col->ExcelRender = false;
	}
		
	function levelRender($row, $value){
		
		global $level;
		
		if($value == "")
			$value = "-----";
		
		return "<a onclick=changeLevel('" . $level . "','".
				($level >= "l0" ? $row["level0"] : "") . "','" . 
				($level >= "l1" ? $row["level1"] : "") . "','" . 
				($level >= "l2" ? $row["level2"] : "") . "','" . 
				($level >= "l3" ? $row["level3"] : ""). "','" . 
				($level == "l4" ? $row["level4"] : ""). "') "." 
				href='javascript:void(0);'>" . $value . "</a>";
	}
	function levelRender2($row, $value){
		global $level;
		return "<a onclick=changeLevel('l5','".
				($level >= "l0" ? $row["level0"] : "") . "','" . 
				($level >= "l1" ? $row["level1"] : "") . "','" . 
				($level >= "l2" ? $row["level2"] : "") . "','" . 
				($level >= "l3" ? $row["level3"] : ""). "','" . 
				($level == "l4" ? $row["level4"] : ""). "') "." 
				href='javascript:void(0);'>" . $value . "</a>";
	}
	
	function showDocs($row, $value){
		if($value == "")
			$value = "-----";
		
		return "<a onclick=\"window.open('flow.php?show=true&taraz=true".
				"&level1=" . $row["level1"] . 
				"&level2=" . $row["level2"] . 
				"&level3=" . $row["level3"] . 
				"&level4=" . $row["level4"] . 
				"&TafsiliID=" . $row["TafsiliID"] .
				(!empty($_REQUEST["fromDate"]) ? "&fromDate=" . $_REQUEST["fromDate"] : "") . 
				(!empty($_REQUEST["toDate"]) ? "&toDate=" . $_REQUEST["toDate"] : "") .
				(!empty($_REQUEST["fromLocalNo"]) ? "&fromLocalNo=" . $_REQUEST["fromLocalNo"] : "") . 
				(!empty($_REQUEST["TafsiliID"]) ? "&TafsiliID=" . $_REQUEST["TafsiliID"] : "") . 
				(!empty($_REQUEST["TafsiliType"]) ? "&TafsiliType=" . $_REQUEST["TafsiliType"] : "") . 
				(!empty($_REQUEST["toLocalNo"]) ? "&toLocalNo=" . $_REQUEST["toLocalNo"] : "") .
				(!empty($_REQUEST["BranchID"]) ? "&BranchID=" . $_REQUEST["BranchID"] : "") .
				(!empty($_REQUEST["IncludeRaw"]) ? "&IncludeRaw=1" : "") .
				(!empty($_REQUEST["IncludeStart"]) ? "&IncludeStart=1" : "") .
				(!empty($_REQUEST["IncludeEnd"]) ? "&IncludeEnd=1" : "") .
				"');\" href=javascript:void(0)>" . $value . "</a>";
	}
	function showDocs2($row, $value){
		if($value == "")
			$value = "-----";
		
		return "<a onclick=\"window.open('flow.php?taraz=true&show=true&taraz=true".
				"&level1=" . $row["level1"] . 
				"&level2=" . $row["level2"] . 
				"&level3=" . $row["level3"] . 
				"&level4=" . $row["level4"] . 
				"&TafsiliID2=" . $row["TafsiliID2"] .
				(!empty($_POST["fromDate"]) ? "&fromDate=" . $_POST["fromDate"] : "") . 
				(!empty($_POST["toDate"]) ? "&toDate=" . $_POST["toDate"] : "") .
				(!empty($_REQUEST["fromLocalNo"]) ? "&fromLocalNo=" . $_REQUEST["fromLocalNo"] : "") . 
				(!empty($_REQUEST["toLocalNo"]) ? "&toLocalNo=" . $_REQUEST["toLocalNo"] : "") .
				(!empty($_REQUEST["TafsiliID"]) ? "&TafsiliID=" . $_REQUEST["TafsiliID"] : "") . 
				(!empty($_REQUEST["TafsiliType"]) ? "&TafsiliType=" . $_REQUEST["TafsiliType"] : "") . 
				(!empty($_REQUEST["BranchID"]) ? "&BranchID=" . $_REQUEST["BranchID"] : "") .
				(!empty($_REQUEST["IncludeRaw"]) ? "&IncludeRaw=1" : "") .
				"');\" href=javascript:void(0)>" . $value . "</a>";
	}
	
	function MakeWhere(&$where, &$whereParam){
 
		if(empty($_REQUEST["IncludeRaw"]))
		{
			$where .= " AND d.DocStatus != 'RAW'";
		}
		/*if(empty($_REQUEST["IncludeStart"]))
		{
			$where .= " AND d.DocType != " . DOCTYPE_STARTCYCLE;
		}*/
		if(empty($_REQUEST["IncludeEnd"]))
		{
			$where .= " AND d.DocType != " . DOCTYPE_ENDCYCLE;
		}
		if(isset($_REQUEST["level0"]))
		{
			$where .= " AND b1.GroupID=:l0";
			$whereParam[":l0"] = $_REQUEST["level0"];
		}
		if(isset($_REQUEST["level1"]))
		{
			if($_REQUEST["level1"] == "")
				$where .= " AND cc.level1 is null";
			else
			{
				$where .= " AND cc.level1=:l1";
				$whereParam[":l1"] = $_REQUEST["level1"];
			}
		}
		if(isset($_REQUEST["level2"]))
		{
			if($_REQUEST["level2"] == "")
				$where .= " AND cc.level2 is null";
			else
			{
				$where .= " AND cc.level2=:l2";
				$whereParam[":l2"] = $_REQUEST["level2"];
			}
		}
		if(isset($_REQUEST["level3"]))
		{
			if($_REQUEST["level3"] == "")
				$where .= " AND cc.level3 is null";
			else
			{
				$where .= " AND cc.level3=:l3";
				$whereParam[":l3"] = $_REQUEST["level3"];
			}
		}
		if(isset($_REQUEST["level4"]))
		{
			if($_REQUEST["level4"] == "")
				$where .= " AND cc.level4 is null";
			else
			{
				$where .= " AND cc.level4=:l4";
				$whereParam[":l4"] = $_REQUEST["level4"];
			}
		}
		//..............................................
		if(!empty($_POST["level0s"]))
		{
			$level0s = preg_replace("/[^0-9,]+/", "", $_POST["level0s"]);
            $where .= " AND b1.GroupID in( " . $level0s . ")";
		}
		//----------------------------------------------
		if(!empty($_POST["level1s"]))
		{
			$level1s = preg_replace("/[^0-9,]+/", "", $_POST["level1s"]);
            $where .= " AND cc.level1 in( " . $level1s . ")";
		}
		//----------------------------------------------
		if(!empty($_POST["level2s"]))
		{
			$level2s = preg_replace("/[^0-9,]+/", "", $_POST["level2s"]);
			$level2s = substr($level2s, 0, strlen($level2s)-1);
            $where .= " AND cc.level2 in( " . $level2s . ")";
		}
		//----------------------------------------------
		if(!empty($_POST["level3s"]))
		{
			$level3s = preg_replace("/[^0-9,]+/", "", $_POST["level3s"]);
			$level3s = substr($level3s, 0, strlen($level3s)-1);
            $where .= " AND cc.level3 in( " . $level3s . ")";
		}
		//----------------------------------------------
		if(!empty($_POST["TafsiliGroup"]))
		{
			$where .= " AND di.TafsiliType=:tt";
			$whereParam[":tt"] = $_POST["TafsiliGroup"];
		}
		if(!empty($_POST["TafsiliGroup2"]))
		{
			$where .= " AND di.TafsiliType2=:tt2";
			$whereParam[":tt2"] = $_POST["TafsiliGroup2"];
		}
		if(!empty($_POST["TafsiliID"]))
		{
			$where .= " AND di.TafsiliID=:tid";
			$whereParam[":tid"] = $_POST["TafsiliID"];
		}
		if(!empty($_POST["TafsiliID2"]))
		{
			$where .= " AND di.TafsiliID2=:tid2";
			$whereParam[":tid2"] = $_POST["TafsiliID2"];
		}
		if(!empty($_REQUEST["fromLocalNo"]))
		{
			$where .= " AND d.LocalNo >= :lo1 ";
			$whereParam[":lo1"] = $_REQUEST["fromLocalNo"];
		}
		if(!empty($_REQUEST["toLocalNo"]))
		{
			$where .= " AND d.LocalNo <= :lo2 ";
			$whereParam[":lo2"] = $_REQUEST["toLocalNo"];
		}
		if(!empty($_POST["fromDate"]))
		{
			$where .= " AND d.DocDate >= :q1 ";
			$whereParam[":q1"] = DateModules::shamsi_to_miladi($_POST["fromDate"], "-");
		}
		if(!empty($_POST["toDate"]))
		{
			$where .= " AND d.DocDate <= :q2 ";
			$whereParam[":q2"] = DateModules::shamsi_to_miladi($_POST["toDate"], "-");
		}
	}
		
	$where = "";
	$whereParam = array();
	
	if(!empty($_POST["BranchID"]))
	{
		$where .= " AND d.BranchID=:b";
		$whereParam[":b"] = $_POST["BranchID"];
	}	
	
	MakeWhere($where, $whereParam);
	
	$query = $select . $from . " where 
		d.CycleID=" . $_SESSION["accounting"]["CycleID"] . $where;
	$query .= $group != "" ? " group by " . $group : "";
		
	$query .= " order by b1.BlockCode,b2.BlockCode,b3.BlockCode,b4.BlockCode,di.TafsiliID,di.TafsiliID2";

	$dataTable = PdoDataAccess::runquery($query, $whereParam);
	
	$redirect = "";
	if(count($dataTable) == 1)
	{
		$LevelValue = "";
		switch($level)
		{
			case "l2" : $LevelValue = $dataTable[0]["level2"];	break;
			case "l3" : $LevelValue = $dataTable[0]["level3"];	break;
			case "l4" : $LevelValue = $dataTable[0]["level4"];	break;
		}
		if($LevelValue == "" && $level >= "l2" && $level <= "l4")
		{
			$row = $dataTable[0];
			$redirect =  "<script>changeLevel('" . $level . "','".
			($level >= "l0" ? $row["level0"] : "") . "','" . 
			($level >= "l1" ? $row["level1"] : "") . "','" . 
			($level >= "l2" ? $row["level2"] : "") . "','" . 
			($level >= "l3" ? $row["level3"] : ""). "','" . 
			($level == "l4" ? $row["level4"] : ""). "');</script>";
		}
	}
	//..........................................................................
		
	function dateRender($row, $val){
		return DateModules::miladi_to_shamsi($val);
	}	
	
	function moneyRender($row, $val) {
		return number_format($val, 0, '.', ',');
	}

	function bdremainRender($row){
		$v = $row["bdAmount"] - $row["bsAmount"];
		return $v < 0 ? 0 : number_format($v);
	}
	
	function bsremainRender($row){
		$v = $row["bsAmount"] - $row["bdAmount"];
		return $v < 0 ? 0 : number_format($v);
	}
	
	if($_REQUEST["resultColumns"]*1 >= 6)
	{
		$col = $rpg->addColumn("بدهکار", "StartCycleDebtor", "moneyRender");
		$col->GroupHeader = "حساب ابتدای دوره";
		$col->EnableSummary(true);
		$col = $rpg->addColumn("بستانکار", "StartCycleCreditor", "moneyRender");
		$col->GroupHeader = "حساب ابتدای دوره";
		$col->EnableSummary(true);
	}
	if($_REQUEST["resultColumns"]*1 >= 4)
	{
		$col = $rpg->addColumn("بدهکار", "bdAmount" , "moneyRender");
		$col->GroupHeader = "گردش طی دوره";
		$col->EnableSummary();
		$col = $rpg->addColumn("بستانکار", "bsAmount", "moneyRender");
		$col->GroupHeader = "گردش طی دوره";
		$col->EnableSummary();
	}
	$col = $rpg->addColumn("مانده بدهکار", "bdAmount", "bdremainRender");
	$col->GroupHeader = "مانده پایان دوره";
	$col->EnableSummary(true);
	$col = $rpg->addColumn("مانده بستانکار", "bsAmount", "bsremainRender");
	$col->GroupHeader = "مانده پایان دوره";
	$col->EnableSummary(true);

	if(!$rpg->excel)
	{
		BeginReport();
		echo "<div style=display:none>" . PdoDataAccess::GetLatestQueryString() . "</div>";
		echo "<table style='border:2px groove #9BB1CD;border-collapse:collapse;width:100%'><tr>
				<td width=60px><img src='/framework/icons/logo.jpg' style='width:120px'></td>
				<td align='center' style='height:100px;vertical-align:middle;font-family:titr;font-size:15px'>
					تراز دفتر 
				".$levelsDescArr[$level]. 
				" <br> ".
				( empty($_POST["BranchID"]) ? "کلیه شعبه ها" : $branches[$_POST["BranchID"]]) .
				"<br>" . "دوره سال " . $_SESSION["accounting"]["CycleID"] .
				"</td>
				<td width='200px' align='center' style='font-family:tahoma;font-size:11px'>تاریخ تهیه گزارش : " 
			. DateModules::shNow() . "<br>";
		if(!empty($_POST["fromDate"]))
		{
			echo "<br>گزارش از تاریخ : " . $_POST["fromDate"] . ($_POST["toDate"] != "" ? " - " . $_POST["toDate"] : "");
		}
		echo "</td></tr></table>";
	}
	
	$rpg->mysql_resource = $dataTable;
	$rpg->generateReport();
	
	?>
	<script>
		function changeLevel(curlevel,level0,level1,level2,level3,level4,TafsiliID,TafsiliID2)
		{
			nextLevel = (curlevel.substring(1)*1);
			nextLevel = nextLevel+1;
				
			var form = document.getElementById("subForm");
			form.action = "taraz.php?show=true&level=" + "l" + nextLevel;
			form.target = "_blank";
			if(curlevel >= "l0")
				form.action += "&level0=" + level0;
			if(curlevel >= "l1")
				form.action += "&level1=" + level1;
			if(curlevel >= "l2")
				form.action += "&level2=" + level2;
			if(curlevel >= "l3")
				form.action += "&level3=" + level3;
			if(curlevel >= "l4")
				form.action += "&level4=" + level4;
			if(curlevel == "l5")
				form.action += "&TafsiliID=" + TafsiliID + "&TafsiliID2=" + TafsiliID2;
			
			form.submit();
			return;
		}
	</script>
	<form id="subForm" method="POST" target="blank">
	
	<input type="hidden" name="fromDate" value="<?= !empty($_REQUEST["fromDate"]) ? $_REQUEST["fromDate"] : "" ?>">
	<input type="hidden" name="toDate" value="<?= !empty($_REQUEST["toDate"]) ? $_REQUEST["toDate"] : "" ?>">
	<input type="hidden" name="fromLocalNo" value="<?= !empty($_REQUEST["fromLocalNo"]) ? $_REQUEST["fromLocalNo"] : "" ?>">
	<input type="hidden" name="TafsiliID" value="<?= !empty($_REQUEST["TafsiliID"]) ? $_REQUEST["TafsiliID"] : "" ?>">
	<input type="hidden" name="TafsiliType" value="<?= !empty($_REQUEST["TafsiliType"]) ? $_REQUEST["TafsiliType"] : "" ?>">
	<input type="hidden" name="TafsiliID2" value="<?= !empty($_REQUEST["TafsiliID2"]) ? $_REQUEST["TafsiliID2"] : "" ?>">
	<input type="hidden" name="TafsiliType2" value="<?= !empty($_REQUEST["TafsiliType2"]) ? $_REQUEST["TafsiliType2"] : "" ?>">
	<input type="hidden" name="toLocalNo" value="<?= !empty($_REQUEST["toLocalNo"]) ? $_REQUEST["toLocalNo"] : "" ?>">
	<input type="hidden" name="IncludeRaw" value="<?= !empty($_REQUEST["IncludeRaw"]) ? $_REQUEST["IncludeRaw"] : "" ?>">
	<input type="hidden" name="IncludeStart" value="<?= !empty($_REQUEST["IncludeStart"]) ? $_REQUEST["IncludeStart"] : "" ?>">
	<input type="hidden" name="IncludeEnd" value="<?= !empty($_REQUEST["IncludeEnd"]) ? $_REQUEST["IncludeEnd"] : "" ?>">
	<input type="hidden" name="BranchID" value="<?= !empty($_REQUEST["BranchID"]) ? $_REQUEST["BranchID"] : "" ?>">
	<input type="hidden" name="resultColumns" value="<?= $_REQUEST["resultColumns"] ?>">
	
	<input type="hidden" name="level1s" id="level1s" value="<?= $_POST["level1s"] ?>">
	<input type="hidden" name="level2s" id="level2s" value="<?= $_POST["level2s"] ?>">
	<input type="hidden" name="level3s" id="level3s" value="<?= $_POST["level3s"] ?>">
</form>
	<?
	echo $redirect;
	die();
}
?>
<script>
	
AccReport_taraz.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

AccReport_taraz.prototype.showReport = function(btn, e)
{
	this.form = this.get("mainForm")
	this.form.target = "_blank";
	this.form.method = "POST";
	this.form.action =  this.address_prefix + "taraz.php?show=true";
	
	this.get("level0s").value = this.formPanel.down("[itemId=cmp_level0]").getValue();
	this.get("level1s").value = this.formPanel.down("[itemId=cmp_level1]").getValue();
	this.get("level2s").value = this.formPanel.down("[itemId=cmp_level2]").getValue();
	/*this.get("level2s").value = "";
	this.formPanel.down('[itemId=cmp_level2]').getStore().each(function(r){
		AccReport_tarazObj.get("level2s").value += r.data.BlockID + ",";
	});
	this.get("level3s").value = "";
	this.formPanel.down('[itemId=cmp_level3]').getStore().each(function(r){
		AccReport_tarazObj.get("level3s").value += r.data.BlockID + ",";
	});*/
	
	this.form.submit();
	this.get("excel").value = "";
	return;
}

function AccReport_taraz()
{
	this.formPanel = new Ext.form.Panel({
		renderTo : this.get("main"),
		frame : true,
		layout :{
			type : "table",
			columns :4
		},
		bodyStyle : "text-align:right;padding:5px",
		title : "گزارش تراز",
		defaults : {
			labelWidth :75,
			width : 240,
			style : "margin-left:15px"
		},
		width : 800,
		items :[{
			xtype : "combo",
			colspan : 4,
			width : 400,
			store : new Ext.data.SimpleStore({
				proxy: {
					type: 'jsonp',
					url: "/accounting/global/domain.data.php?task=GetAccessBranches",
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				fields : ['BranchID','BranchName'],
				autoLoad : true					
			}),
			fieldLabel : "شعبه",
			queryMode : 'local',
			value : "<?= !isset($_SESSION["accounting"]["BranchID"]) ? "" : $_SESSION["accounting"]["BranchID"] ?>",
			displayField : "BranchName",
			valueField : "BranchID",
			hiddenName : "BranchID"
		},{
			xtype : "displayfield",
			fieldLabel : "گروه"
		},{
			xtype : "displayfield",
			fieldLabel : "کل"			
		},{
			xtype : "displayfield",
			fieldLabel : "معین",
			colspan : 2
		},{
			xtype : "multiselect",
			height : 195,
			valueField : "BlockID",
			displayField : "BlockDesc",
			itemId : "cmp_level0",
			name : "multi_level0",
			store : new Ext.data.Store({
				fields:["BlockID","BlockCode","BlockDesc"],
				
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../baseinfo/baseinfo.data.php?task=SelectBlocks&All=true&level=0',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				autoLoad : true
			})
		},{
			xtype : "multiselect",
			height : 195,
			name : "multi_level1",
			valueField : "BlockID",
			displayField : "full",
			itemId : "cmp_level1",
			store : new Ext.data.Store({
				fields:["BlockID","BlockCode","BlockDesc",
					{name : "full", 
					convert: function(v,r){return "[" + r.data.BlockCode + "] " + r.data.BlockDesc }}],
				
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../baseinfo/baseinfo.data.php?task=SelectBlocks&All=true&level=1',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				autoLoad : true
			})
		},{
			xtype : "multiselect",
			height : 195,
			colspan : 2,
			name : "multi_level2",
			valueField : "BlockID",
			displayField : "full",
			itemId : "cmp_level2",
			store : new Ext.data.Store({
				fields:["BlockID","BlockCode","BlockDesc",
					{name : "full", 
					convert: function(v,r){return "[" + r.data.BlockCode + "] " + r.data.BlockDesc }}],
				
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../baseinfo/baseinfo.data.php?task=SelectBlocks&All=true&level=2',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				autoLoad : true
			})
		},{
			xtype : "container",
			colspan : 4,
			width : 500,
			cls : "blueText",
			html : "* " + "با نگه داشتن کلید CTRL می توانید چندین مورد را انتخاب کنید"
		},{
			xtype : "container",
			html : "<br>",
			colspan : 4
		},{
			xtype : "combo",
			displayField : "InfoDesc",
			fieldLabel : "گروه تفصیلی",
			valueField : "InfoID",
			hiddenName : "TafsiliGroup",
			store : new Ext.data.Store({
				fields:['InfoID','InfoDesc'],
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../baseinfo/baseinfo.data.php?task=SelectTafsiliGroups',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				autoLoad : true
			}),
			listeners : {
				select : function(combo,records){
					el = AccReport_tarazObj.formPanel.down("[itemId=cmp_tafsiliID]");
					el.enable();
					el.setValue();
					el.getStore().proxy.extraParams["TafsiliType"] = this.getValue();
					el.getStore().load();
				}
			}
		},{
			xtype : "combo",
			displayField : "TafsiliDesc",
			fieldLabel : "تفصیلی",
			disabled : true,
			valueField : "TafsiliID",
			itemId : "cmp_tafsiliID",
			hiddenName : "TafsiliID",
			
			store : new Ext.data.Store({
				fields:["TafsiliID","TafsiliDesc"],
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../baseinfo/baseinfo.data.php?task=GetAllTafsilis',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				}
			})
		},{
			xtype : "container",
			rowspan : 5,
			layout : "column",
			width : 120,
			items :[{
				xtype : "container",
				style : "margin-left:10px",
				html :  "<div align=center>تراز بر اساس </div><hr>" +
						"<input type='radio' name='level' id='level-l0' value='l0' > گروه<br>" + 
						"<input type='radio' name='level' id='level-l1' value='l1' checked> کل <br>" + 
						"<input type='radio' name='level' id='level-l2' value='l2' > معین  <br>" + 
						"<input type='radio' name='level' id='level-l3' value='l3' > جزء معین <br>" + 
						"<input type='radio' name='level' id='level-l4' value='l4' > جزء معین 2<br>" + 
						"<input type='radio' name='level' id='level-l5' value='l5' > تفصیلی " 			
			}]
		},{
			xtype : "container",
			layout : "column",
			rowspan : 5,
			width : 120,
			items :[{
				xtype : "container",
				style : "margin-left:10px",
				html :  "<div align=center>ستون های تراز</div><hr>" +
						"<input type='radio' name='resultColumns' id='resultColumns-2' value='2' >دوستونی<br>" + 
						"<input type='radio' name='resultColumns' id='resultColumns-4' value='4' checked>چهارستونی <br>" + 
						"<input type='radio' name='resultColumns' id='resultColumns-6' value='6' >شش ستونی"
			}]
		},{
			xtype : "combo",
			displayField : "InfoDesc",
			fieldLabel : "گروه تفصیلی2",
			valueField : "InfoID",
			hiddenName : "TafsiliGroup2",
			store : new Ext.data.Store({
				fields:['InfoID','InfoDesc'],
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../baseinfo/baseinfo.data.php?task=SelectTafsiliGroups',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				autoLoad : true
			}),
			listeners : {
				select : function(combo,records){
					el = AccReport_tarazObj.formPanel.down("[itemId=cmp_tafsiliID2]");
					el.enable();
					el.setValue();
					el.getStore().proxy.extraParams["TafsiliType"] = this.getValue();
					el.getStore().load();
				}
			}
		},{
			xtype : "combo",
			displayField : "TafsiliDesc",
			fieldLabel : "تفصیلی2",
			disabled : true,
			valueField : "TafsiliID",
			itemId : "cmp_tafsiliID2",
			hiddenName : "TafsiliID2",
			
			store : new Ext.data.Store({
				fields:["TafsiliID","TafsiliDesc"],
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../baseinfo/baseinfo.data.php?task=GetAllTafsilis',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				}
			})
		},{
			xtype : "numberfield",
			hideTrigger : true,
			name : "fromLocalNo",
			fieldLabel : "از سند شماره"
		},{
			xtype : "numberfield",
			hideTrigger : true,
			name : "toLocalNo",
			fieldLabel : "تا سند شماره"
		},{
			xtype : "shdatefield",
			name : "fromDate",
			fieldLabel : "از تاریخ"
		},{
			xtype : "shdatefield",
			name : "toDate",
			fieldLabel : "تا تاریخ"
		},{
			xtype : "container",
			html : "<input type=checkbox name=IncludeRaw id=IncludeRaw> گزارش شامل اسناد خام نیز باشد." + "<br>" +
				"<input type=checkbox name=IncludeEnd id=IncludeEnd> گزارش شامل سند اختتامیه باشد."
		}],
		buttons : [{
			text : "گزارش ساز",
			iconCls : "db",
			handler : function(){ReportGenerator.ShowReportDB(
						AccReport_tarazObj, 
						<?= $_REQUEST["MenuID"] ?>,
						"mainForm",
						"formPanel"
						);}
		},'->',{
			text : "مشاهده گزارش",
			handler : Ext.bind(this.showReport,this),
			iconCls : "report"
		},{
			text : "خروجی excel",
			handler : Ext.bind(this.showReport,this),
			listeners : {
				click : function(){
					AccReport_tarazObj.get('excel').value = "true";
				}
			},
			iconCls : "excel"
		},{
			text : "پاک کردن گزارش",
			iconCls : "clear",
			handler : function(){
				AccReport_tarazObj.formPanel.getForm().reset();
				AccReport_tarazObj.get("mainForm").reset();
				AccReport_tarazObj.formPanel.down("[itemId=cmp_tafsiliID]").disable();
			}			
		}]
	});
}
/*,{
	xtype : "container",
	layout : "column",
	colmns : 3,
	items : [{
		xtype : "combo",
		width : 195,
		displayField : "full",				
		valueField : "BlockID",
		itemId : "combo_level2",
		pageSize : 10,
		store : new Ext.data.Store({
			fields:["BlockID","BlockCode","BlockDesc",
			{name : "full", 
			convert: function(v,r){return "[" + r.data.BlockCode + "] " + r.data.BlockDesc; }}],
			proxy: {
				type: 'jsonp',
				url: this.address_prefix + '../baseinfo/baseinfo.data.php?task=SelectBlocks&level=2',
				reader: {root: 'rows',totalProperty: 'totalCount'}
			},
			autoLoad : true
		})
	},{
		xtype : "button",
		iconCls : "add",
		handler : function(){
			comboEl = AccReport_tarazObj.formPanel.down('[itemId=combo_level2]');
			if(comboEl.getValue() == "")
				return;
			elem = AccReport_tarazObj.formPanel.down('[itemId=cmp_level2]');
			elem.getStore().add({
				BlockID : comboEl.getValue(),
				title : comboEl.getRawValue()
			});
			comboEl.setValue();
		}
	},{
		xtype : "button",
		iconCls : "cross",
		handler : function(){
			elem = AccReport_tarazObj.formPanel.down('[itemId=cmp_level2]');
			elem.getStore().removeAt(elem.getStore().find("BlockID",elem.getValue()));
		}
	},{
		xtype : "multiselect",
		width : 240,
		colspan : 3,
		itemId : "cmp_level2",
		height : 170,
		store: new Ext.data.Store({
			fields : ['BlockID','title']
		}),
		ddReorder: true,
		valueField : "BlockID",
		displayField : "title"
	}]			
},{
	xtype : "container",
	layout : "column",
	colmns : 3,
	items : [{
		xtype : "combo",
		width : 195,
		displayField : "full",
		valueField : "BlockID",
		itemId : "combo_level3",
		pageSize : 10,
		store : new Ext.data.Store({
			fields:["BlockID","BlockCode","BlockDesc",
			{name : "full", 
			convert: function(v,r){return "[" + r.data.BlockCode + "] " + r.data.BlockDesc; }}],
			proxy: {
				type: 'jsonp',
				url: this.address_prefix + '../baseinfo/baseinfo.data.php?task=SelectBlocks&level=3',
				reader: {root: 'rows',totalProperty: 'totalCount'}
			},
			autoLoad : true
		})
	},{
		xtype : "button",
		iconCls : "add",
		handler : function(){
			comboEl = AccReport_tarazObj.formPanel.down('[itemId=combo_level3]');
			if(comboEl.getValue() == "")
				return;
			elem = AccReport_tarazObj.formPanel.down('[itemId=cmp_level3]');
			elem.getStore().add({
				BlockID : comboEl.getValue(),
				title : comboEl.getRawValue()
			});
			comboEl.setValue();
		}
	},{
		xtype : "button",
		iconCls : "cross",
		handler : function(){
			elem = AccReport_tarazObj.formPanel.down('[itemId=cmp_level3]');
			elem.getStore().removeAt(elem.getStore().find("BlockID",elem.getValue()));
		}
	},{
		xtype : "multiselect",
		width : 240,
		colspan : 3,
		itemId : "cmp_level3",
		height : 170,
		store: new Ext.data.Store({
			fields : ['BlockID','BlockCode','BlockDesc']
		}),
		ddReorder: true,
		valueField : "BlockID",
		displayField : "BlockCode"
	}]			
}*/
AccReport_tarazObj = new AccReport_taraz();
</script>
<form id="mainForm">
	<center><br>
		<div id="main" ></div>
	</center>
	<input type="hidden" name="excel" id="excel">
	<input type="hidden" name="level0s" id="level0s">
	<input type="hidden" name="level1s" id="level1s">
	<input type="hidden" name="level2s" id="level2s">
	<input type="hidden" name="level3s" id="level3s">
</form>
