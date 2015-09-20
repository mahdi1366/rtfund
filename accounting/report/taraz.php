<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 91.02
//-----------------------------

require_once '../header.inc.php';
require_once "ReportGenerator.class.php";

if(isset($_REQUEST["show"]))
{
	$levelsDescArr = array(
		"kol" => "کل",
		"moin" => "معین",
		"tafsili" => "تفصیلی",
		"tafsili2" => "تفصیلی2"
		);
	$level = $_REQUEST["level"];
	
	$rpg = new ReportGenerator();
	$whereParam = array();
	$where = "";
	$group = ",si.kolID";
	
	$select = "select 
		sum(si.bdAmount) bdAmount,
		sum(si.bsAmount) bsAmount,
		k.kolID,
		k.kolTitle";
	$from = " from acc_doc_items si 
				join acc_docs d using(docID)
				left join acc_kols k using(kolID)";

	switch($level)
	{
		case "kol" :
			$rpg->addColumn("کد کل", "kolID");
			$rpg->addColumn("عنوان کل", "kolTitle", "levelRender", "moin");
			break;
		
		case "moin" : 
			$select .= ",si.moinID,moinTitle";
			$from .= " left join acc_moins m on(m.kolID=si.kolID AND m.moinID=si.moinID)";
			$group .= ",si.moinID";
			
			$rpg->addColumn("کد کل", "kolID");
			$rpg->addColumn("کد معین", "moinID");
			$rpg->addColumn("عنوان معین", "moinTitle", "levelRender", "tafsili");
			break;
			
		case "tafsili" : 
			$select .= ",si.moinID,si.TafsiliID,t.tafsiliTitle";
			$from .= " left join acc_tafsilis t on(t.tafsiliID=si.tafsiliID)";
			$group .= ",si.tafsiliID";

			$rpg->addColumn("کد تفصیلی", "TafsiliID");
			$rpg->addColumn("عنوان تفصیلی", "tafsiliTitle", "levelRender", "tafsili2");
			break;
			
		case "tafsili2" : 
			$select .= ",si.moinID,si.TafsiliID,t2.tafsiliTitle as tafsiliTitle2";
			$from .= " left join acc_tafsilis t2 on(t2.tafsiliID=si.tafsili2ID)";
			$group .= ",si.tafsili2ID";
		
			$rpg->addColumn("کد تفصیلی2", "TafsiliID2");
			$rpg->addColumn("عنوان تفصیلی2", "tafsiliTitle2", "showDocs");
			break;
	}
	
	function levelRender($row, $value, $level){
		
		if($value == "")
			$value = "-----";
		
		return "<a onclick=changeLevel(" . $row["kolID"] . ",'".
				($level == "tafsili" || $level == "tafsili2" ? $row["moinID"] : "") . "','" . 
				($level == "tafsili2" ? $row["TafsiliID"] : ""). "','" . 
				$level . "') href='javascript:void(0);'>" . $value . "</a>";
	}
	
	function showDocs($row, $value)
	{
		if($value == "")
			$value = "-----";
		
		return "<a onclick=\"window.open('flow.php?show=true&kolID=" . $row["kolID"] . "&moinID=" . $row["moinID"] . 
				"&TafsiliID=" . $row["TafsiliID"] . "&Tafsili2ID=" . $row["TafsiliID2"] . 
				(!empty($_POST["fromDate"]) ? "&fromDate=" . $_POST["fromDate"] : "") . 
				(!empty($_POST["toDate"]) ? "&toDate=" . $_POST["toDate"] : "") .
				"');\" href=javascript:void(0)>" . $value . "</a>";
	}
	
	if(!empty($_POST["from_kolID"]))
	{
		$where .= " AND si.kolID >= :k1";
		$whereParam[":k1"] = $_POST["from_kolID"];
	}
	if(!empty($_POST["to_kolID"]))
	{
		$where .= " AND si.kolID <= :k2";
		$whereParam[":k2"] = $_POST["to_kolID"];
	}
	if(!empty($_POST["from_moinID"]))
	{
		$where .= " AND si.moinID >= :m1";
		$whereParam[":m1"] = $_POST["from_moinID"];
	}
	if(!empty($_POST["to_moinID"]))
	{
		$where .= " AND si.moinID <= :m2";
		$whereParam[":m2"] = $_POST["to_moinID"];
	}
	if(!empty($_POST["from_tafsiliID"]))
	{
		$where .= " AND si.tafsiliID >= :t1";
		$whereParam[":t1"] = $_POST["from_tafsiliID"];
	}
	if(!empty($_POST["to_tafsiliID"]))
	{
		$where .= " AND si.tafsiliID <= :t2";
		$whereParam[":t2"] = $_POST["to_tafsiliID"];
	}
	if(!empty($_POST["from_tafsiliID2"]))
	{
		$where .= " AND si.tafsili2ID >= :t21";
		$whereParam[":t21"] = $_POST["from_tafsiliID2"];
	}
	if(!empty($_POST["to_tafsiliID2"]))
	{
		$where .= " AND si.tafsili2ID <= :t22";
		$whereParam[":t22"] = $_POST["to_tafsiliID2"];
	}
	
	if(!empty($_POST["fromDate"]))
	{
		$where .= " AND d.docDate >= :q1 ";
		$whereParam[":q1"] = DateModules::shamsi_to_miladi($_POST["fromDate"], "-");
	}
	if(!empty($_POST["toDate"]))
	{
		$where .= " AND d.docDate <= :q2 ";
		$whereParam[":q2"] = DateModules::shamsi_to_miladi($_POST["toDate"], "-");
	}
	
	//...............................
	if(isset($_REQUEST["kolID"]))
	{
		if($_REQUEST["kolID"] == "")
			$where .= " AND si.kolID is null";
		else
		{
			$where .= " AND si.kolID = :k";
			$whereParam[":k"] = $_REQUEST["kolID"];
		}
	}
	if(isset($_REQUEST["moinID"]))
	{
		if($_REQUEST["moinID"] == "")
			$where .= " AND si.moinID is null";
		else
		{
			$where .= " AND si.moinID = :m";
			$whereParam[":m"] = $_REQUEST["moinID"];
		}
	}
	if(isset($_REQUEST["TafsiliID"]))
	{
		if($_REQUEST["TafsiliID"] == "")
			$where .= " AND si.TafsiliID is null";
		else
		{
			$where .= " AND si.TafsiliID = :t";
			$whereParam[":t"] = $_REQUEST["TafsiliID"];
		}
	}
	if(isset($_REQUEST["Tafsili2ID"]))
	{
		if($_REQUEST["Tafsili2ID"] == "")
			$where .= " AND si.Tafsili2ID is null";
		else
		{
			$where .= " AND si.Tafsili2ID = :t2";
			$whereParam[":t2"] = $_REQUEST["Tafsili2ID"];
		}
	}
	//...............................
	
	$query .= $select . $from . " where d.cycleID=" . $_SESSION["CYCLE"] . $where;
	$query .= $group != "" ? " group by " . substr($group,1) : "";
	
	/*if(isset($_POST["yesRemain"]) && $_POST["yesRemain"] == "1")
	{
		$query .= " having sum(si.bdAmount) <> sum(si.bsAmount)";
	}
	else if(isset($_POST["noRemain"]) && $_POST["noRemain"] == "1")
	{
		$query .= " having sum(si.bdAmount) = sum(si.bsAmount)";
	}*/
	
	$query .= " order by si.kolID,si.moinID,si.TafsiliID";

	$dataTable = PdoDataAccess::runquery($query, $whereParam);

	//..........................................................................
		
	function dateRender($row, $val)
	{
		return DateModules::miladi_to_shamsi($val);
	}	
	
	function moneyRender($row, $val) {
		return number_format($val, 0, '.', ',');
	}

	
	function bdremainRender($row)
	{
		$v = $row["bdAmount"] - $row["bsAmount"];
		return $v < 0 ? 0 : number_format($v);
	}
	function bsremainRender($row)
	{
		$v = $row["bsAmount"] - $row["bdAmount"];
		return $v < 0 ? 0 : number_format($v);
	}
	
	//if($_POST["col"] == "4")
	//{
		$col = $rpg->addColumn("بدهکار", "bdAmount" , "moneyRender");
		$col->EnableSummary();
		$col = $rpg->addColumn("بستانکار", "bsAmount", "moneyRender");
		$col->EnableSummary();
	//}

	$col = $rpg->addColumn("مانده بدهکار", "x", "bdremainRender");
	$col->EnableSummary(true);
	
	$col = $rpg->addColumn("مانده بستانکار", "y", "bsremainRender");
	$col->EnableSummary(true);

	if(!$rpg->excel)
	{
		echo '<META http-equiv=Content-Type content="text/html; charset=UTF-8" ><body dir="rtl">';
		echo "<div style=display:none>" . PdoDataAccess::GetLatestQueryString() . "</div>";
		echo "<table style='border:2px groove #9BB1CD;border-collapse:collapse;width:100%'><tr>
				<td width=60px><img src='../img/logo3.png'></td>
				<td align='center' style='font-family:b titr;font-size:15px'>
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
		function changeLevel(kolID, moinID, tafsiliID, level)
		{
			var form = document.getElementById("subForm");
			form.action = "taraz.php?show=true&kolID=" + kolID 
				+ (level == "tafsili" || level == "tafsili2" ? "&moinID=" + moinID : "") 
				+ (level == "tafsili2" ? "&TafsiliID=" + tafsiliID : "") 
				+ "&level=" + level;
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
	this.formPanel = new Ext.form.Panel({
		renderTo : this.get("main"),
		frame : true,
		layout :{
			type : "table",
			columns :2
		},
		bodyStyle : "text-align:right;padding:5px",
		title : "گزارش خلاصه حساب ها",
		defaults : {
			labelWidth :100
		},
		width : 600,
		items :[{
			xtype : "combo",
			displayField : "kolTitle",
			fieldLabel : "حساب کل از",
			valueField : "kolID",
			itemId : "cmp_kol",
			hiddenName : "from_kolID",
			store : new Ext.data.Store({
				fields:["kolID","kolTitle"],
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../account/data/kols.data.php?task=selectKol',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				autoLoad : true
			}),
			tpl: new Ext.XTemplate(
				'<table cellspacing="0" width="100%"><tr class="x-grid-header-ct">'
				,'<td>کد</td><td>عنوان</td>'
				,'<tpl for=".">'
				,'<tr class="x-boundlist-item" style="border-left:0;border-right:0">'
				,'<td style="border-left:0;border-right:0" class="search-item">{kolID}</td>'
				,'<td style="border-left:0;border-right:0" class="search-item">{kolTitle}</td>'
				,'</tpl>'
				,'</table>')
		},{
			xtype : "combo",
			displayField : "kolTitle",
			fieldLabel : "تا",
			valueField : "kolID",
			hiddenName : "to_kolID",
			store : new Ext.data.Store({
				fields:["kolID","kolTitle"],
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../account/data/kols.data.php?task=selectKol',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				autoLoad : true
			}),
			tpl: new Ext.XTemplate(
				'<table cellspacing="0" width="100%"><tr class="x-grid-header-ct">'
				,'<td>کد</td><td>عنوان</td>'
				,'<tpl for=".">'
				,'<tr class="x-boundlist-item" style="border-left:0;border-right:0">'
				,'<td style="border-left:0;border-right:0" class="search-item">{kolID}</td>'
				,'<td style="border-left:0;border-right:0" class="search-item">{kolTitle}</td>'
				,'</tpl>'
				,'</table>')
		},{
			xtype : "combo",
			displayField : "moinTitle",
			fieldLabel : "حساب معین از",
			valueField : "moinID",
			hiddenName : "from_moinID",
			store : new Ext.data.Store({
				fields:["kolID","moinID","moinTitle"],
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../account/data/moins.data.php?task=selectMoin',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				listeners : {
					beforeload : function(store){
						store.proxy.extraParams["kolID"] = AccReport_tarazObj.formPanel.down("[itemId=cmp_kol]").getValue();
					}
				}
			}),
			tpl: new Ext.XTemplate(
				'<table cellspacing="0" width="100%"><tr class="x-grid-header-ct">'
				,'<td>کد</td><td>عنوان</td>'
				,'<tpl for=".">'
				,'<tr class="x-boundlist-item" style="border-left:0;border-right:0">'
				,'<td style="border-left:0;border-right:0" class="search-item">{moinID}</td>'
				,'<td style="border-left:0;border-right:0" class="search-item">{moinTitle}</td>'
				,'</tpl>'
				,'</table>')
		},{
			xtype : "combo",
			displayField : "moinTitle",
			fieldLabel : "تا",
			valueField : "moinID",
			hiddenName : "to_moinID",
			store : new Ext.data.Store({
				fields:["kolID","moinID","moinTitle"],
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../account/data/moins.data.php?task=selectMoin',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				listeners : {
					beforeload : function(store){
						store.proxy.extraParams["kolID"] = AccReport_tarazObj.formPanel.down("[itemId=cmp_kol]").getValue();
					}
				}
			}),
			tpl: new Ext.XTemplate(
				'<table cellspacing="0" width="100%"><tr class="x-grid-header-ct">'
				,'<td>کد</td><td>عنوان</td>'
				,'<tpl for=".">'
				,'<tr class="x-boundlist-item" style="border-left:0;border-right:0">'
				,'<td style="border-left:0;border-right:0" class="search-item">{moinID}</td>'
				,'<td style="border-left:0;border-right:0" class="search-item">{moinTitle}</td>'
				,'</tpl>'
				,'</table>')
		},{
			xtype : "combo",
			displayField : "tafsiliTitle",
			fieldLabel : "حساب تفصیلی از",
			valueField : "tafsiliID",
			hiddenName : "from_tafsiliID",
			store : new Ext.data.Store({
				fields:["tafsiliID","tafsiliTitle"],
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../account/data/tafsilis.data.php?task=selectTafsili',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				}
			}),
			tpl: new Ext.XTemplate(
				'<table cellspacing="0" width="100%"><tr class="x-grid-header-ct">'
				,'<td>کد</td><td>عنوان</td>'
				,'<tpl for=".">'
				,'<tr class="x-boundlist-item" style="border-left:0;border-right:0">'
				,'<td style="border-left:0;border-right:0" class="search-item">{tafsiliID}</td>'
				,'<td style="border-left:0;border-right:0" class="search-item">{tafsiliTitle}</td>'
				,'</tpl>'
				,'</table>')
		},{
			xtype : "combo",
			displayField : "tafsiliTitle",
			fieldLabel : "تا",
			valueField : "tafsiliID",
			hiddenName : "to_tafsiliID",
			store : new Ext.data.Store({
				fields:["tafsiliID","tafsiliTitle"],
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../account/data/tafsilis.data.php?task=selectTafsili',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				}
			}),
			tpl: new Ext.XTemplate(
				'<table cellspacing="0" width="100%"><tr class="x-grid-header-ct">'
				,'<td>کد</td><td>عنوان</td>'
				,'<tpl for=".">'
				,'<tr class="x-boundlist-item" style="border-left:0;border-right:0">'
				,'<td style="border-left:0;border-right:0" class="search-item">{tafsiliID}</td>'
				,'<td style="border-left:0;border-right:0" class="search-item">{tafsiliTitle}</td>'
				,'</tpl>'
				,'</table>')
		},{
			xtype : "combo",
			displayField : "tafsiliTitle",
			fieldLabel : "حساب تفصیلی2 از",
			valueField : "tafsiliID",
			hiddenName : "from_tafsiliID2",
			store : new Ext.data.Store({
				fields:["tafsiliID","tafsiliTitle"],
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../account/data/tafsilis.data.php?task=selectTafsili',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				}
			}),
			tpl: new Ext.XTemplate(
				'<table cellspacing="0" width="100%"><tr class="x-grid-header-ct">'
				,'<td>کد</td><td>عنوان</td>'
				,'<tpl for=".">'
				,'<tr class="x-boundlist-item" style="border-left:0;border-right:0">'
				,'<td style="border-left:0;border-right:0" class="search-item">{tafsiliID}</td>'
				,'<td style="border-left:0;border-right:0" class="search-item">{tafsiliTitle}</td>'
				,'</tpl>'
				,'</table>')
		},{
			xtype : "combo",
			displayField : "tafsiliTitle",
			fieldLabel : "تا",
			valueField : "tafsiliID",
			hiddenName : "to_tafsiliID2",
			store : new Ext.data.Store({
				fields:["tafsiliID","tafsiliTitle"],
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../account/data/tafsilis.data.php?task=selectTafsili',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				}
			}),
			tpl: new Ext.XTemplate(
				'<table cellspacing="0" width="100%"><tr class="x-grid-header-ct">'
				,'<td>کد</td><td>عنوان</td>'
				,'<tpl for=".">'
				,'<tr class="x-boundlist-item" style="border-left:0;border-right:0">'
				,'<td style="border-left:0;border-right:0" class="search-item">{tafsiliID}</td>'
				,'<td style="border-left:0;border-right:0" class="search-item">{tafsiliTitle}</td>'
				,'</tpl>'
				,'</table>')
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
		}*/,{
			xtype : "container",
			fieldLabel : "تراز بر اساس",
			html :  "<input type='radio' name='level' value='kol' checked> کل  &nbsp;&nbsp;&nbsp;" + 
					"<input type='radio' name='level' value='moin' > معین  &nbsp;&nbsp;&nbsp;" + 
					"<input type='radio' name='level' value='tafsili' > تفصیلی  &nbsp;&nbsp;&nbsp;" + 
					"<input type='radio' name='level' value='tafsili2' >&nbsp; تفصیلی2"			
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