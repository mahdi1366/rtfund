<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.06
//-----------------------------

require_once '../header.inc.php';
require_once "ReportGenerator.class.php";

if(isset($_REQUEST["show"]))
{
	$rpg = new ReportGenerator();
	$rpg->excel = !empty($_POST["excel"]);
	
	function dateRender($row, $val){
		return DateModules::miladi_to_shamsi($val);
	}	
	
	function PrintDocRender($row, $val){
		
		return "<a target=_blank href='../docs/print_doc.php?DocID=" . $row["DocID"] . "'>" . $val . "</a>";
	}
	
	$rpg->addColumn("شماره سند", "LocalNo", "PrintDocRender");
	$rpg->addColumn("گروه حساب", "level0Desc");
	$rpg->addColumn("حساب کل", "level1Desc");
	$rpg->addColumn("حساب معین", "level2Desc");
	$rpg->addColumn("حساب جزء معین", "level3Desc");
	$rpg->addColumn("تفصیلی", "TafsiliDesc");
	$rpg->addColumn("تفصیلی2", "TafsiliDesc2");
	$rpg->addColumn("تاریخ سند", "DocDate","dateRender");
	$rpg->addColumn("توضیحات", "description");	
	
	function MakeWhere(&$where, &$whereParam){
		
		if(!empty($_REQUEST["GroupID"]))
		{
			$where .= " AND b1.GroupID = :gid";
			$whereParam[":gid"] = $_REQUEST["GroupID"];
		}
		
		if(!empty($_REQUEST["level1"]))
		{
			$where .= " AND b1.BlockID = :bf1";
			$whereParam[":bf1"] = $_REQUEST["level1"];
		}
		if(!empty($_REQUEST["level2"]))
		{
			$where .= " AND b2.BlockID = :bf2";
			$whereParam[":bf2"] = $_REQUEST["level2"];
		}
		if(!empty($_REQUEST["level3"]))
		{
			$where .= " AND b3.BlockID = :bf3";
			$whereParam[":bf3"] = $_REQUEST["level3"];
		}
		if(isset($_REQUEST["TafsiliID"]))
		{
			if($_REQUEST["TafsiliID"] == "")
				$where .= " AND (di.TafsiliID=0 OR di.TafsiliID is null)";
			else
			{
				$where .= " AND di.TafsiliID = :tid ";
				$whereParam[":tid"] = $_REQUEST["TafsiliID"];
			}
		}
		if(!empty($_REQUEST["TafsiliType"]))
		{
			$where .= " AND di.TafsiliType = :tt ";
			$whereParam[":tt"] = $_REQUEST["TafsiliType"];
		}
		if(!empty($_REQUEST["TafsiliID2"]))
		{
			$where .= " AND di.TafsiliID2 = :tid";
			$whereParam[":tid"] = $_REQUEST["TafsiliID2"];
		}
		if(!empty($_REQUEST["TafsiliType2"]))
		{
			$where .= " AND di.TafsiliType2 = :tt ";
			$whereParam[":tt"] = $_REQUEST["TafsiliType2"];
		}
		/*if(!empty($_POST["from_level1"]) || !empty($_POST["to_Level1"]))
		{
			if(!empty($_POST["from_level1"]))
			{
				$where .= " AND b1.BlockCode >= :bf1";
				$whereParam[":bf1"] = $_POST["from_level1"];
			}
			if(!empty($_POST["to_level1"]))
			{
				$where .= " AND b1.BlockCode <= :bt1";
				$whereParam[":bt1"] = $_POST["to_level1"];
			}
		}

		if(!empty($_POST["from_level2"]) || !empty($_POST["to_Level2"]))
		{
			if(!empty($_POST["from_level2"]))
			{
				$where .= " AND b2.BlockCode >= :bf2";
				$whereParam[":bf2"] = $_POST["from_level2"];
			}
			if(!empty($_POST["to_level2"]))
			{
				$where .= " AND b2.BlockCode <= :bt2";
				$whereParam[":bt2"] = $_POST["to_level2"];
			}
		}
		if(!empty($_POST["from_level3"]) || !empty($_POST["to_Level3"]))
		{
			if(!empty($_POST["from_level3"]))
			{
				$where .= " AND b3.BlockCode >= :bf3";
				$whereParam[":bf3"] = $_POST["from_level3"];
			}
			if(!empty($_POST["to_level3"]))
			{
				$where .= " AND b3.BlockCode <= :bt3";
				$whereParam[":bt3"] = $_POST["to_level3"];
			}
		}
		if(!empty($_POST["from_tafsiliType"]) || !empty($_POST["to_tafsiliType"]))
		{
			if(!empty($_POST["from_TafsiliType"]))
			{
				$where .= " AND di.TafsiliType >= :ttf";
				$whereParam[":ttf"] = $_POST["from_TafsiliType"];
			}
			if(!empty($_POST["to_TafsiliType"]))
			{
				$where .= " AND di.TafsiliType <= :ttt";
				$whereParam[":ttt"] = $_POST["to_TafsiliType"];
			}
		}
		if(!empty($_POST["from_TafsiliID"]) || !empty($_POST["to_TafsiliID"]))
		{
			if(!empty($_POST["from_TafsiliID"]))
			{
				$where .= " AND di.TafsiliID >= :tf";
				$whereParam[":tf"] = $_POST["from_TafsiliID"];
			}
			if(!empty($_POST["to_TafsiliID"]))
			{
				$where .= " AND di.TafsiliID <= :tt";
				$whereParam[":tt"] = $_POST["to_TafsiliID"];
			}
		}*/
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
		if(!empty($_REQUEST["fromDate"]))
		{
			$where .= " AND d.docDate >= :q1 ";
			$whereParam[":q1"] = DateModules::shamsi_to_miladi($_REQUEST["fromDate"], "-");
		}
		if(!empty($_REQUEST["toDate"]))
		{
			$where .= " AND d.docDate <= :q2 ";
			$whereParam[":q2"] = DateModules::shamsi_to_miladi($_REQUEST["toDate"], "-");
		}
	}	
	
	//.....................................
	$query = "select d.*,di.DebtorAmount,CreditorAmount,
		concat('[ ' , b0.BlockCode , ' ] ', b0.BlockDesc) level0Desc,
		concat('[ ' , b1.BlockCode , ' ] ', b1.BlockDesc) level1Desc,
		concat('[ ' , b2.BlockCode , ' ] ', b2.BlockDesc) level2Desc,
		concat('[ ' , b3.BlockCode , ' ] ', b3.BlockDesc) level3Desc,
		b.InfoDesc TafsiliTypeDesc,
		t.TafsiliDesc,
		bi2.InfoDesc TafsiliTypeDesc2,
		t2.TafsiliDesc TafsiliDesc2
		
		from ACC_DocItems di join ACC_docs d using(DocID)
			join ACC_CostCodes cc using(CostID)
			join ACC_blocks b1 on(level1=b1.BlockID)
			left join ACC_blocks b0 on(b1.GroupID=b0.BlockID)
			left join ACC_blocks b2 on(level2=b2.BlockID)
			left join ACC_blocks b3 on(level3=b3.BlockID)
			left join BaseInfo b on(TypeID=2 AND di.TafsiliType=InfoID)
			left join ACC_tafsilis t using(TafsiliID)
			left join BaseInfo bi2 on(bi2.TypeID=2 AND di.TafsiliType2=bi2.InfoID)
			left join ACC_tafsilis t2 on(di.TafsiliID2=t2.TafsiliID)
	";
	$where = "";
	$whereParam = array();
	MakeWhere($where, $whereParam);
	$query .= " where d.CycleID=" . $_SESSION["accounting"]["CycleID"]
			. " AND BranchID=" . $_SESSION["accounting"]["BranchID"] . $where;

	if(!isset($_REQUEST["IncludeRaw"]))
		$query .= " AND d.DocStatus != 'RAW' ";
	
	
	$dataTable = PdoDataAccess::runquery($query, $whereParam);
	
	function moneyRender($row, $val) {
		return number_format($val);
	}
	
	$col = $rpg->addColumn("مبلغ بدهکار", "DebtorAmount", "moneyRender");
	$col->EnableSummary();
	$col = $rpg->addColumn("مبلغ بستانکار", "CreditorAmount", "moneyRender");
	$col->EnableSummary();
	
	function bdremainRender($row){
		$v = $row["DebtorAmount"] - $row["CreditorAmount"];
		return $v < 0 ? 0 : number_format($v);
	}
	
	function bsremainRender($row){
		$v = $row["CreditorAmount"] - $row["DebtorAmount"];
		return $v < 0 ? 0 : number_format($v);
	}
	
	$col = $rpg->addColumn("مانده بدهکار", "DebtorAmount", "bdremainRender");
	$col->EnableSummary(true);
	$col = $rpg->addColumn("مانده بستانکار", "CreditorAmount", "bsremainRender");
	$col->EnableSummary(true);
	
	$rpg->mysql_resource = $dataTable;
	if(!$rpg->excel)
	{
		echo '<META http-equiv=Content-Type content="text/html; charset=UTF-8" ><body dir="rtl">';
		if($_SESSION["USER"]["UserName"] == "admin")
			echo PdoDataAccess::GetLatestQueryString ();
		echo "<div style=display:none>" . PdoDataAccess::GetLatestQueryString() . "</div>";
		echo "<table style='border:2px groove #9BB1CD;border-collapse:collapse;width:100%'><tr>
				<td width=60px><img src='/framework/icons/logo.jpg' style='width:120px'></td>
				<td align='center' style='height:100px;vertical-align:middle;font-family:b titr;font-size:15px'>
					گزارش گردش حساب ها 
					 <br> ".
				 $_SESSION["accounting"]["BranchName"]. "<br>" . "دوره سال " .
				$_SESSION["accounting"]["CycleID"] .
				"</td>
				<td width='200px' align='center' style='font-family:tahoma;font-size:11px'>تاریخ تهیه گزارش : " 
			. DateModules::shNow() . "<br>";
		if(!empty($_POST["fromDate"]))
		{
			echo "<br>گزارش از تاریخ : " . $_POST["fromDate"] . ($_POST["toDate"] != "" ? " - " . $_POST["toDate"] : "");
		}
		echo "</td></tr></table>";
		}
	$rpg->generateReport();
	die();
}
?>
<script>
AccReport_flow.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

AccReport_flow.prototype.showReport = function(btn, e)
{
	this.form = this.get("mainForm")
	this.form.target = "_blank";
	this.form.method = "POST";
	this.form.action =  this.address_prefix + "flow.php?show=true";
	this.form.submit();
	this.get("excel").value = "";
	return;
}

function AccReport_flow()
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
		title : "گزارش گردش حساب ها",
		defaults : {
			labelWidth :100,
			width : 270
		},
		width : 600,
		items :[{
			xtype : "combo",
			displayField : "BlockDesc",
			fieldLabel : "گروه حساب",
			valueField : "BlockID",
			itemId : "cmp_level0",
			hiddenName : "GroupID",
			store : new Ext.data.Store({
				fields:["BlockID","BlockCode","BlockDesc"],
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../baseinfo/baseinfo.data.php?task=SelectBlocks&level=0',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				autoLoad : true
			}),
			tpl: this.blockTpl
		},{
			xtype : "combo",
			displayField : "BlockDesc",
			fieldLabel : "کل",
			valueField : "BlockID",
			itemId : "cmp_level1",
			hiddenName : "level1",
			store : new Ext.data.Store({
				fields:["BlockID","BlockCode","BlockDesc"],
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../baseinfo/baseinfo.data.php?task=SelectBlocks&level=1',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				autoLoad : true
			}),
			tpl: this.blockTpl,
			PageSize : 20,
			listeners : {
				select : function(combo,records){
					AccReport_flowObj.formPanel.down("[hiddenName=level2]").getStore().load({
						params : {
							PreLevel : records[0].data.BlockID
						}
					});
				}
			}
		},{
			xtype : "combo",
			displayField : "BlockDesc",
			fieldLabel : "معین",
			valueField : "BlockID",
			itemId : "cmp_level2",
			queryMode : "local",
			hiddenName : "level2",
			store : new Ext.data.Store({
				fields:["BlockID","BlockCode","BlockDesc"],
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../baseinfo/baseinfo.data.php?task=SelectBlocks&level=2',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				autoLoad : true
			}),
			tpl: this.blockTpl,
			listeners : {
				select : function(combo,records){
					AccReport_flowObj.formPanel.down("[hiddenName=level3]").getStore().load({
						params : {
							PreLevel : records[0].data.BlockID
						}
					});
				}
			}
		},{
			xtype : "combo",
			displayField : "BlockDesc",
			fieldLabel : "جزء معین",
			valueField : "BlockID",
			itemId : "cmp_level3",
			queryMode : "local",
			hiddenName : "level3",
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
					el = AccReport_flowObj.formPanel.down("[itemId=cmp_tafsiliID]");
					el.setValue();
					el.enable();
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
					el = AccReport_flowObj.formPanel.down("[itemId=cmp_tafsiliID2]");
					el.setValue();
					el.enable();
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
			fieldLabel : "تاریخ سند از"
		},{
			xtype : "shdatefield",
			name : "toDate",
			fieldLabel : "تا تاریخ"
		},{
			xtype : "container",
			colspan : 2,
			html : "<input type=checkbox name=IncludeRaw> گزارش شامل اسناد پیش نویس نیز باشد"
		}],
		buttons : [{
			text : "مشاهده گزارش",
			handler : Ext.bind(this.showReport,this),
			iconCls : "report"
		},{
			text : "خروجی excel",
			handler : Ext.bind(this.showReport,this),
			listeners : {
				click : function(){
					AccReport_flowObj.get('excel').value = "true";
				}
			},
			iconCls : "excel"
		},{
			text : "پاک کردن گزارش",
			iconCls : "clear",
			handler : function(){
				AccReport_flowObj.formPanel.getForm().reset();
				AccReport_flowObj.get("mainForm").reset();
			}			
		}]
	});
}

AccReport_flowObj = new AccReport_flow();
</script>
<form id="mainForm">
	<center><br>
		<div id="main" ></div>
	</center>
	<input type="hidden" name="excel" id="excel">
</form>