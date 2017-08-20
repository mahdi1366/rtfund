<?php
//-----------------------------
//	Programmer	: Jafarkhani
//	Date		: 95.03
//-----------------------------

require_once '../header.inc.php';
require_once "ReportGenerator.class.php";

if(isset($_REQUEST["show"]))
	ShowReport();

function ShowReport(){
	
	$query = "SELECT c.*,concat_ws(' ',p1.fname,p1.lname,p1.CompanyName) fullname,
			concat_ws(' ',p2.fname,p2.lname,p2.CompanyName)fullname2,bf.InfoDesc ContractTypeDesc 
		FROM CNT_contracts c 
			join BaseInfo bf on(bf.TypeID=18 AND bf.InfoID=ContractType)
			left join CNT_ContractItems ci using(ContractID)
			left join BSC_persons p1 on(c.PersonID=p1.PersonID)
			left join BSC_persons p2 on(c.PersonID2=p2.PersonID)
		where 1=1
	";
	$params = array();
	
	$index = 0;
	$itemsWhere = "1=1 ";
	$keys = array_keys($_POST);
	foreach($keys as $key)
	{
		if(empty($_POST[$key]))
			continue;
		
		switch($key)
		{
			case "TemplateID":
			case "ContractType":
			case "PersonID":
			case "PersonID2":
				$query .= " AND c." . $key . "=:p" . $index;
				$params[":p" . $index++] = $_POST[ $key ];
				break;
			
			case "FromContractAmount":
			case "FromStartDate":
			case "FromEndDate":
				$query .= " AND c." . $key . " <= :p" . $index;
				$params[":p" . $index++] = $_POST[ $key ];
				break;
			
			case "ToContractAmount":
			case "ToStartDate":
			case "ToEndDate":
				$query .= " AND c." . $key . " >= :p" . $index;
				$params[":p" . $index++] = $_POST[ $key ];
				break;
			
			case "description":
				$query .= " AND c." . $key . " like :p" . $index;
				$params[":p" . $index++] = "%" . $_POST[ $key ] . "%";
				break;
			
			default: 
				if(strpos($key, "TplItem_") !== false)
				{
					$key = substr($key, 8);
					$itemsWhere .= " OR if(ci.TemplateItemID=" . $key . ",ItemValue like :p" . $index . ",1=0)";
					$params[":p" . $index++] = "%" . $_POST[ "TplItem_" . $key ] . "%";
				}
		}
	}
	
	$data = PdoDataAccess::runquery($query . " AND ($itemsWhere)" . " group by c.ContractID", $params);
	//echo PdoDataAccess::GetLatestQueryString();
	$rpg = new ReportGenerator();
	$rpg->excel = !empty($_POST["excel"]);
	$rpg->mysql_resource = $data;
	
	function dateRender($row, $val){
		return DateModules::miladi_to_shamsi($val);
	}	
	function moneyRender($row,$value){
		return number_format($value);
	}
	function ContractRender($row, $value){
		return "<a target=blank href=../contract/PrintContract.php?ContractID=" . $value . ">" . $value . "</a>";
	}
	
	$rpg->addColumn("شماره قرارداد", "ContractID","ContractRender");
	$rpg->addColumn("تاریخ شروع", "StartDate","dateRender");
	$rpg->addColumn("تاریخ پایان", "EndDate","dateRender");
	$rpg->addColumn("نوع قرارداد", "ContractTypeDesc");
	$rpg->addColumn("طرف اول قرارداد", "fullname");
	$rpg->addColumn("طرف دوم قرارداد", "fullname2");
	$rpg->addColumn("مبلغ قرارداد", "ContractAmount", "moneyRender");
	
	if(!$rpg->excel)
	{
		BeginReport();
		echo "<table style='border:2px groove #9BB1CD;border-collapse:collapse;width:100%'><tr>
				<td width=60px><img src='/framework/icons/logo.jpg' style='width:120px'></td>
				<td align='center' style='height:100px;vertical-align:middle;font-family:titr;font-size:15px'>
					گزارش قرارداد ها
				</td>
				<td width='200px' align='center' style='font-family:tahoma;font-size:11px'>تاریخ تهیه گزارش : " 
			. DateModules::shNow() . "<br>";
		echo "</td></tr></table>";
		}
	$rpg->generateReport();
	die();
	
	die();
}
?>
<script type="text/javascript">

TotalReport.prototype = {
	TabID: '<?= $_REQUEST["ExtTabID"] ?>',
	address_prefix: "<?= $js_prefix_address ?>",
	
	get: function (elementID) {
		return findChild(this.TabID, elementID);
	}
}

function TotalReport() {
	
	this.MainForm = new Ext.form.Panel({
		plain: true,            
		frame: true,
		bodyPadding: 5,
		width: 800,
		autoHeight : true,
		fieldDefaults: {
			labelWidth: 100
		},
		renderTo: this.get("SelectTplComboDIV"),
		layout: {
			type: 'table',                
			columns : 2
		},
		items: [{
			xtype: 'combo',
			fieldLabel: 'انتخاب الگو',
			itemId: 'TemplateID',
			store: new Ext.data.Store({
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../templates/templates.data.php?task=SelectTemplates',
					reader: {root: 'rows', totalProperty: 'totalCount'}
				},
				fields: ['TemplateID', 'TemplateTitle', 'TplContent'],
				autoLoad : true
			}),
			displayField: 'TemplateTitle',
			valueField: "TemplateID",
			hiddenName : "TemplateID",
			queryMode : "local",
			listConfig: {
				loadingText: 'در حال جستجو...',
				emptyText: 'فاقد اطلاعات',
				itemCls: "search-item"
			},
			width: 350,
			listeners: {
				select: function (combo, records) {
					this.collapse();
					masktpl = new Ext.LoadMask(TotalReportObj.MainForm, {msg:'در حال ذخيره سازي...'});
					masktpl.show();
					TotalReportObj.TplItemsStore.load({
						params : {TemplateID : records[0].data.TemplateID},
						callback : function(){
							TotalReportObj.ShowTplItemsForm(records[0].data.TemplateID, false);
							masktpl.hide();
						}
					});
					
				}
			}
		},{
			xtype : "combo",
			store : new Ext.data.SimpleStore({
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../contract/contract.data.php?task=SelectContractTypes',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				fields : ['InfoID','InfoDesc'],
				autoLoad : true
			}),
			fieldLabel : "نوع قرارداد",
			displayField : "InfoDesc",
			width: 350,
			queryMode : "local",
			valueField : "InfoID",
			hiddenName : "ContractType"
		},{
			xtype : "combo",
			store : new Ext.data.SimpleStore({
				proxy: {
					type: 'jsonp',
					url: '/framework/person/persons.data.php?task=selectPersons',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				fields : ['PersonID','fullname']
			}),
			fieldLabel : "طرف قرارداد اول",
			displayField : "fullname",
			pageSize : 20,
			width: 350,
			valueField : "PersonID",
			hiddenName : "PersonID"
		},{
			xtype : "combo",
			store : new Ext.data.SimpleStore({
				proxy: {
					type: 'jsonp',
					url: '/framework/person/persons.data.php?task=selectPersons',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				fields : ['PersonID','fullname']
			}),
			fieldLabel : "طرف قرارداد دوم",
			displayField : "fullname",
			pageSize : 20,
			width: 350,
			valueField : "PersonID",
			hiddenName : "PersonID2"
		},{
			xtype : "currencyfield",
			fieldLabel: 'مبلغ قرارداد از',
			name : "FromContractAmount",
			hideTrigger : true
		},{
			xtype : "currencyfield",
			fieldLabel: 'مبلغ قرارداد تا',
			name : "ToContractAmount",
			hideTrigger : true
		},{
			xtype : "shdatefield",
			fieldLabel: 'تاریخ شروع از',
			name : "FromStartDate"
		},{
			xtype : "shdatefield",
			fieldLabel: 'تاریخ شروع تا',
			name : "ToStartDate"
		},{
			xtype : "shdatefield",
			fieldLabel: 'تاریخ پایان از',
			name : "FromEndDate"
		},{
			xtype : "shdatefield",
			fieldLabel: 'تاریخ پایان تا',
			name : "ToEndDate"
		},{
			xtype: 'textfield',
			fieldLabel: 'توضیحات',
			itemId: 'description',
			name : "description",
			width: 740,
			colspan : 2
		},{
			xtype: "fieldset",
			title : "جزئیات قرارداد",
			itemId: "templateItems",
			width : 780,
			maxHeight : 300,
			autoScroll: true,
			colspan : 2,
			layout: {
				type: 'table',                
				columns : 2
			},
			defaults: {
				labelWidth: 160,
				width : 370
			}
		}],
		buttons: [{
			text : "گزارش ساز",
			iconCls : "db",
			handler : function(){ReportGenerator.ShowReportDB(
						TotalReportObj, 
						<?= $_REQUEST["MenuID"] ?>,
						"mainForm",
						"formPanel"
						);}
		},'->',{
			text: "مشاهده گزارش",
			handler : Ext.bind(this.showReport,this),
			iconCls: "report"
		}]
	});
	
	this.TplItemsStore = new Ext.data.Store({
		fields: ['TemplateItemID',"TemplateID", 'ItemName', 'ItemType'],
		proxy: {
			type: 'jsonp',
			url: this.address_prefix + "../templates/templates.data.php?task=selectTemplateItems",
			reader: {
				root: 'rows',
				totalProperty: 'totalCount'
			}
		},
		pageSize: 500
	});
}

TotalReport.prototype.showReport = function(btn, e)
{
	this.form = this.get("mainForm")
	this.form.target = "_blank";
	this.form.method = "POST";
	this.form.action =  this.address_prefix + "TotalReport.php?show=true";
	this.form.submit();
	this.get("excel").value = "";
	return;
}

TotalReport.prototype.ShowTplItemsForm = function (TemplateID, LoadValues) {

	this.MainForm.getComponent("templateItems").removeAll();

	mask = new Ext.LoadMask(this.MainForm.getComponent("templateItems"), {msg:'در حال ذخيره سازي...'});
	mask.show();
	  
	Ext.Ajax.request({
		url: TotalReportObj.address_prefix + '../templates/templates.data.php?task=GetTemplateContent',
		params: {
			TemplateID: TemplateID
		},
		method: 'POST',
		success: function (response) {
			me = TotalReportObj;
			
			for(i=0; i<me.TplItemsStore.getCount(); i++)
			{
				record = me.TplItemsStore.getAt(i);
				if(record.data.ItemType == "")
					continue;
				me.MainForm.getComponent("templateItems").add({
					xtype: record.data.ItemType,
					itemId: 'TplItem_' + record.data.TemplateItemID,
					name: 'TplItem_' + record.data.TemplateItemID,
					fieldLabel : record.data.ItemName,
					hideTrigger : record.data.ItemType == 'numberfield' || record.data.ItemType == 'currencyfield' ? true : false
				});				       
			}
			mask.hide();
			return;
		},
		failure: function () {
		}
	});
}

TotalReportObj = new TotalReport();
</script>
<br>
<form id="mainForm">
<center>
    <div id="SelectTplComboDIV"></div>
</center>
</form>
<br>