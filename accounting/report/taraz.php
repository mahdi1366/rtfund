<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.06
//-----------------------------

require_once '../header.inc.php';
require_once "ReportGenerator.class.php";

if(isset($_REQUEST["show"]))
{
	$levelsDescArr = array(
		"l1" => "گروه حساب",
		"l2" => "کل",
		"l3" => "معین",
		"l4" => "گروه تفصیلی",
		"l5" => "تفصیلی"		
		);
	global $level;
	$level = empty($_REQUEST["level"]) ? "l1" : $_REQUEST["level"];
	
	$rpg = new ReportGenerator();
	$whereParam = array();
	$where = "";
	$group = "cc.level1";
	
	$select = "select 
		sum(di.DebtorAmount) bdAmount,
		sum(di.CreditorAmount) bsAmount,
		concat('[ ' , b1.BlockCode , ' ] ', b1.BlockDesc) level1Desc,
		concat('[ ' , b2.BlockCode , ' ] ', b2.BlockDesc) level2Desc,
		concat('[ ' , b3.BlockCode , ' ] ', b3.BlockDesc) level3Desc,
		InfoDesc TafsiliTypeDesc,
		concat('[ ', t.TafsiliID, ' ] ',t.TafsiliDesc) TafsiliDesc,
		cc.level1,cc.level2,cc.level3,di.TafsiliType,di.TafsiliID
		";
	$from = " from ACC_DocItems di 
				join ACC_docs d using(DocID)
				join ACC_CostCodes cc using(CostID)
				join ACC_blocks b1 on(level1=b1.BlockID)
				left join ACC_blocks b2 on(level2=b2.BlockID)
				left join ACC_blocks b3 on(level3=b3.BlockID)
				left join BaseInfo b on(TypeID=2 AND di.TafsiliType=InfoID)
				left join ACC_tafsilis t using(TafsiliID)
	";
			
	if($level >= "l1")
		$rpg->addColumn("گروه حساب", "level1Desc",$level =="l1" ? "levelRender" : "");
	if($level >= "l2")
		$rpg->addColumn("حساب کل", "level2Desc", $level =="l2" ? "levelRender" : "");
	if($level >= "l3")
		$rpg->addColumn("حساب معین", "level3Desc", $level =="l3" ? "levelRender" : "");
	if($level >= "l4")
		$rpg->addColumn("گروه تفصیلی", "TafsiliTypeDesc", $level =="l4" ? "levelRender" : "");
	if($level >= "l5")
		$rpg->addColumn("تفصیلی", "TafsiliDesc", "showDocs");
	
	switch($level)
	{
		case "l2" : $group .= ",cc.level2"; break;
		case "l3" : $group .= ",cc.level3";	break;
		case "l4" : $group .= ",di.TafsiliType"; break;
		case "l5" : $group .= ",di.TafsiliID"; break;
	}
	
	function levelRender($row, $value){
		
		global $level;
		
		if($value == "")
			$value = "-----";
		
		return "<a onclick=changeLevel('" . $level . "','".
				($level >= "l1" ? $row["level1"] : "") . "','" . 
				($level >= "l2" ? $row["level2"] : "") . "','" . 
				($level >= "l3" ? $row["level3"] : ""). "','" . 
				($level >= "l4" ? $row["TafsiliType"] : ""). "','" . 
				($level >= "l5" ? $row["TafsiliID"] : ""). "') "." 
				href='javascript:void(0);'>" . $value . "</a>";
	}
	
	function showDocs($row, $value){
		if($value == "")
			$value = "-----";
		
		return "<a onclick=\"window.open('flow.php?show=true".
				"&level1=" . $row["level1"] . 
				"&level2=" . $row["level2"] . 
				"&level3=" . $row["level3"] . 
				"&TafsiliType=" . $row["TafsiliType"] . 
				"&TafsiliID=" . $row["TafsiliID"] .
				(!empty($_POST["fromDate"]) ? "&fromDate=" . $_POST["fromDate"] : "") . 
				(!empty($_POST["toDate"]) ? "&toDate=" . $_POST["toDate"] : "") .
				"');\" href=javascript:void(0)>" . $value . "</a>";
	}
	
	function MakeWhere(&$where, &$whereParam){

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
		if(isset($_REQUEST["tafsiligroup"]))
		{
			if($_REQUEST["tafsiligroup"] == "")
				$where .= " AND di.TafsiliType is null";
			else
			{
				$where .= " AND di.TafsiliType=:tt";
				$whereParam[":tt"] = $_REQUEST["tafsiligroup"];
			}
		}
		//..............................................
		if(!empty($_POST["from_level1"]))
		{
			$where .= " AND b1.blockCode >= :bf1";
			$whereParam[":bf1"] = $_POST["from_level1"];
		}
		if(!empty($_POST["to_level1"]))
		{
			$where .= " AND b1.BlockCode <= :bt1";
			$whereParam[":bt1"] = $_POST["to_level1"];
		}
		//----------------------------------------------
		if(!empty($_POST["from_level2"]))
		{
			$where .= " AND b2.blockCode >= :bf2";
			$whereParam[":bf2"] = $_POST["from_level2"];
		}
		if(!empty($_POST["to_level2"]))
		{
			$where .= " AND b2.BlockCode <= :bt2";
			$whereParam[":bt2"] = $_POST["to_level2"];
		}
		//----------------------------------------------
		if(!empty($_POST["from_level3"]))
		{
			$where .= " AND b3.blockCode >= :bf3";
			$whereParam[":bf3"] = $_POST["from_level3"];
		}
		if(!empty($_POST["to_level3"]))
		{
			$where .= " AND b3.BlockCode <= :bt3";
			$whereParam[":bt3"] = $_POST["to_level3"];
		}
		//----------------------------------------------
		
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
	
	MakeWhere($where, $whereParam);
	
	$query = $select . $from . " where 
		d.CycleID=" . $_SESSION["accounting"]["CycleID"] . " AND 
		d.BranchID= " . $_SESSION["accounting"]["BranchID"] . $where;
	$query .= $group != "" ? " group by " . $group : "";
		
	$query .= " order by b1.BlockCode,b2.BlockCode,b3.BlockCode";

	$dataTable = PdoDataAccess::runquery($query, $whereParam);
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
	
	$col = $rpg->addColumn("بدهکار", "bdAmount" , "moneyRender");
	$col->EnableSummary();
	$col = $rpg->addColumn("بستانکار", "bsAmount", "moneyRender");
	$col->EnableSummary();

	$col = $rpg->addColumn("مانده بدهکار", "bdAmount", "bdremainRender");
	$col->EnableSummary(true);
	
	$col = $rpg->addColumn("مانده بستانکار", "bsAmount", "bsremainRender");
	$col->EnableSummary(true);

	if(!$rpg->excel)
	{
		echo '<META http-equiv=Content-Type content="text/html; charset=UTF-8" ><body dir="rtl">';
		echo "<div style=display:none>" . PdoDataAccess::GetLatestQueryString() . "</div>";
		echo "<table style='border:2px groove #9BB1CD;border-collapse:collapse;width:100%'><tr>
				<td width=60px><img src='/framework/icons/logo.jpg' style='width:120px'></td>
				<td align='center' style='height:100px;vertical-align:middle;font-family:b titr;font-size:15px'>
					تراز دفتر 
				".$levelsDescArr[$level]." 
				</td>
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
		function changeLevel(curlevel,level1,level2,level3,TafsiliGroup,TafsiliID)
		{
			nextLevel = "l" + (curlevel.substring(1)*1+1);
			var form = document.getElementById("subForm");
			form.action = "taraz.php?show=true&level=" + nextLevel;
			
			if(curlevel >= "l1")
				form.action += "&level1=" + level1;
			if(curlevel >= "l2")
				form.action += "&level2=" + level2;
			if(curlevel >= "l3")
				form.action += "&level3=" + level3;
			if(curlevel >= "l4")
				form.action += "&tafsiligroup=" + TafsiliGroup;
			if(curlevel >= "l5")
				form.action += "&TafsiliID=" + TafsiliID;
			
			form.submit();
			return;
		}
	</script>
<form id="subForm" method="POST" target="blank">
	<input type="hidden" name="fromDate" id="fromDate" value="<?= $_POST["fromDate"] ?>">
	<input type="hidden" name="toDate" id="toDate" value="<?= isset($_POST["toDate"]) ? $_POST["toDate"] : ""?>">
</form>
	<?
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
	this.form.submit();
	this.get("excel").value = "";
	return;
}

function AccReport_taraz()
{
	this.blockTpl = new Ext.XTemplate(
		'<table cellspacing="0" width="100%"><tr class="x-grid-header-ct">'
		,'<td>کد</td><td>عنوان</td>'
		,'<tpl for=".">'
		,'<tr class="x-boundlist-item" style="border-left:0;border-right:0">'
		,'<td style="border-left:0;border-right:0" class="search-item">{BlockCode}</td>'
		,'<td style="border-left:0;border-right:0" class="search-item">{BlockDesc}</td>'
		,'</tpl>'
		,'</table>');

				
	this.formPanel = new Ext.form.Panel({
		renderTo : this.get("main"),
		frame : true,
		layout :{
			type : "table",
			columns :2
		},
		bodyStyle : "text-align:right;padding:5px",
		title : "گزارش تراز",
		defaults : {
			labelWidth :100
		},
		width : 600,
		items :[{
			xtype : "combo",
			displayField : "BlockDesc",
			fieldLabel : "گروه حساب از",
			valueField : "BlockID",
			itemId : "cmp_from_level1",
			hiddenName : "from_level1",
			store : new Ext.data.Store({
				fields:["BlockID","BlockCode","BlockDesc"],
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../baseinfo/baseinfo.data.php?task=SelectBlocks&level=1',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				autoLoad : true
			}),
			tpl: this.blockTpl
		},{
			xtype : "combo",
			displayField : "BlockDesc",
			fieldLabel : "تا",
			valueField : "BlockID",
			itemId : "cmp_to_level1",
			hiddenName : "to_level1",
			store : new Ext.data.Store({
				fields:["BlockID","BlockCode","BlockDesc"],
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../baseinfo/baseinfo.data.php?task=SelectBlocks&level=1',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				autoLoad : true
			}),
			tpl: this.blockTpl
		},{
			xtype : "combo",
			displayField : "BlockDesc",
			fieldLabel : "حساب کل از",
			valueField : "BlockID",
			itemId : "cmp_from_level2",
			hiddenName : "from_level2",
			store : new Ext.data.Store({
				fields:["BlockID","BlockCode","BlockDesc"],
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../baseinfo/baseinfo.data.php?task=SelectBlocks&level=2',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				autoLoad : true
			}),
			tpl: this.blockTpl
		},{
			xtype : "combo",
			displayField : "BlockDesc",
			fieldLabel : "تا",
			valueField : "BlockID",
			itemId : "cmp_to_level2",
			hiddenName : "to_level2",
			store : new Ext.data.Store({
				fields:["BlockID","BlockCode","BlockDesc"],
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../baseinfo/baseinfo.data.php?task=SelectBlocks&level=2',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				autoLoad : true
			}),
			tpl: this.blockTpl
		},{
			xtype : "combo",
			displayField : "BlockDesc",
			fieldLabel : "حساب معین از",
			valueField : "BlockID",
			itemId : "cmp_from_level3",
			hiddenName : "from_level3",
			store : new Ext.data.Store({
				fields:["BlockID","BlockCode","BlockDesc"],
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../baseinfo/baseinfo.data.php?task=SelectBlocks&level=3',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				autoLoad : true
			}),
			tpl: this.blockTpl
		},{
			xtype : "combo",
			displayField : "BlockDesc",
			fieldLabel : "تا",
			valueField : "BlockID",
			itemId : "cmp_to_level3",
			hiddenName : "to_level3",
			store : new Ext.data.Store({
				fields:["BlockID","BlockCode","BlockDesc"],
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../baseinfo/baseinfo.data.php?task=SelectBlocks&level=3',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				autoLoad : true
			}),
			tpl: this.blockTpl
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
					el.setValue();
					el.getStore().proxy.extraParams["TafsiliType"] = this.getValue();
					el.getStore().load();
				}
			}
		},{
			xtype : "combo",
			displayField : "TafsiliDesc",
			fieldLabel : "تفصیلی",
			valueField : "TafsiliID",
			itemId : "cmp_tafsiliID",
			hiddenName : "TafsiliID",
			store : new Ext.data.Store({
				fields:["TafsiliID","TafsiliDesc"],
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../baseinfo/baseinfo.data.php?task=GetAllTafsilis',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				autoLoad : true
			})
		},{
			xtype : "shdatefield",
			name : "fromDate",
			fieldLabel : "از تاریخ"
		},{
			xtype : "shdatefield",
			name : "toDate",
			fieldLabel : "تا تاریخ"
		}/*,{
			xtype : "container",
			html : "<input type='radio' name='col' id='2col' value='2' checked> دو ستونی &nbsp;&nbsp;&nbsp;" + 
				"<input type='radio' name='col' id='4col' value='4' >&nbsp; چهار ستونی"			
		},{
			xtype : "container",
			fieldLabel : "تراز بر اساس",
			html :  "<input type='radio' name='level' value='kol' checked> کل  &nbsp;&nbsp;&nbsp;" + 
					"<input type='radio' name='level' value='moin' > معین  &nbsp;&nbsp;&nbsp;" + 
					"<input type='radio' name='level' value='tafsili' > تفصیلی  &nbsp;&nbsp;&nbsp;" + 
					"<input type='radio' name='level' value='tafsili2' >&nbsp; تفصیلی2"			
		}*/],
		buttons : [{
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
			}			
		}]
	});
}

AccReport_tarazObj = new AccReport_taraz();
</script>
<form id="mainForm">
	<center><br>
		<div id="main" ></div>
	</center>
	<input type="hidden" name="excel" id="excel">
</form>