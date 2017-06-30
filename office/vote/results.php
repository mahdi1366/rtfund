<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.06
//-----------------------------

require_once '../header.inc.php';
require_once inc_dataGrid;

$dg = new sadaf_datagrid("dg", $js_prefix_address . "../../office/vote/vote.data.php".
		"?task=SelectFilledForms" , "grid_div");

$dg->addColumn("", "FormID", "", true);
$dg->addColumn("", "PersonID", "", true);
$dg->addColumn("", "RegDate", "", true);

$col = $dg->addColumn("ذینفع", "fullname", "");

$col = $dg->addColumn("تاریخ ثبت", "RegDate", GridColumn::ColumnType_datetime);
$col->width = 120;

$col = $dg->addColumn("فرم", "", "");
$col->renderer = "function(v,p,r){return VoteResult.viewRender(v,p,r)}";
$col->editor = "this.FileCmp";
$col->align = "center";
$col->width = 40;

$dg->height = 330;
$dg->width = 300;
$dg->title = "فرم های نظر سنجی تکمیل شده";
$dg->EnableSearch = false;
$dg->EnablePaging = false;
$dg->DefaultSortField = "RegDate";
$dg->autoExpandColumn = "fullname";
$grid = $dg->makeGrid_returnObjects();

?>
<script>

VoteResult.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	FormID : "",
	FormValue : 0,
	TotalFormWeights : 0,
	
	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function VoteResult()
{
	this.FormFieldset = new Ext.form.FieldSet({
		title : "انتخاب فرم",
		renderTo : this.get("mainForm"),
		width : 500,
		items :[{
			xtype : "combo",
			store : new Ext.data.Store({
				fields: ['FormID','FormTitle'],
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + "vote.data.php?task=SelectAllForms",
					reader: {
						root: 'rows',
						totalProperty: 'totalCount'
					}
				}
			}),
			width : 450,
			displayField : "FormTitle",
			valueField : "FormID",
			listeners : {
				change : function(){
					VoteResultObject.FormID = this.getValue();
					VoteResultObject.LoadResults(this.getValue());
				}
			}
		},{
			xtype : "button",
			text : "چاپ نتایج",
			iconCls : "print",
			handler : function(){
				if(VoteResultObject.FormID == "")
					return;
				window.open(VoteResultObject.address_prefix + "PrintResults.php?FormID=" + 
						VoteResultObject.FormID);
			}
		}]
	});
	
	this.grid = <?= $grid ?>;
	
	this.MainPanel = new Ext.form.Panel({
		renderTo : this.get("div_form"),
		itemId : "form",
		hidden : true,
		width : 600,
		height : 330,
		autoScroll : true,
		frame : true
	});
	
	this.GroupStore = new Ext.data.Store({
		fields: ['GroupID','GroupDesc',"GroupWeight"],
		proxy: {
			type: 'jsonp',
			url: this.address_prefix + "vote.data.php?task=SelectGroups",
			reader: {
				root: 'rows',
				totalProperty: 'totalCount'
			}
		}
	});	
	//..........................................................................
	
	this.chart = {
		xtype : "chart",
		style: 'background:#fff',
		height : 200,
		width : 500,
		animate: true,
		shadow: true,
		axes: [{
			type: 'Numeric',
			position: 'left',
			fields: ['data'],
			maximum: 100,minimum : 0

		}, {
			type: 'Category',
			position: 'bottom',
			fields: ['ordering']
		}],
		series: [{
			type: 'column',
			axis: 'left',
			label: {
				display: 'insideEnd',
				field: 'data',
				orientation: 'horizontal',
				color: '#333',
				'text-anchor': 'middle',
				contrast: true
			},
			highlight: true,
			xField: ['ItemTitle'],
			yField: ['data'],
			renderer: function(sprite, record, attr, index, store) {
				var value = record.data.mid % 7;
				var color = ['rgb(213, 70, 121)', 
								'rgb(44, 153, 201)', 
								'rgb(146, 6, 157)', 
								'rgb(49, 149, 0)', 
								'rgb(249, 153, 0)'][value];
				return Ext.apply(attr, {
					fill: color
				});
			},
			tips: {
				trackMouse: true,
				width: 250,
				autoHeight: true,
				renderer: function(storeItem, item) {
				this.setTitle(storeItem.get('ItemTitle') + ' [ ' + storeItem.get('data') + ' % ]');
				}
			}
		}]
	};
	
	this.ChartPanel = new Ext.form.Panel({
        width: 900,
        autoHeight : true,
		border : false,		
        maximizable: true,
        renderTo: this.get("charts")
	});
}

VoteResult.viewRender = function(v,p,r){
	
	return "<div align='center' title='نمایش' class='view' onclick='VoteResultObject.PreviewForm();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

VoteResultObject = new VoteResult();

VoteResult.prototype.LoadResults = function(FormID){
	
	this.MainPanel.show();
	this.grid.getStore().proxy.extraParams.FormID = FormID;
	
	if(this.grid.rendered)
		this.grid.getStore().load();
	else
		this.grid.render(this.get("div_grid"));
	
	this.GroupStore.proxy.extraParams.FormID = FormID;
	this.GroupStore.load({
		callback : function(){
			
			me = VoteResultObject;
			me.FormValue = 0;
			me.TotalFormWeights = 0;
			me.ChartPanel.removeAll();
			
			for(i=0; i<this.totalCount; i++)
			{
				record = this.getAt(i);
				
				newchart = Ext.clone(me.chart);
				newchart.store = new Ext.data.Store({
					fields: ["ItemTitle","ordering","GroupID","GroupWeight","mid",{
						name : "data",
						convert : function(v,r){return parseInt(r.data.mid);}
					}],
					proxy: {
						type: 'jsonp',
						url: me.address_prefix + "vote.data.php?task=SelectChart1Data",
						reader: {
							root: 'rows',
							totalProperty: 'totalCount'
						}
					}
				});
				newchart.itemId = "chart" + i;
				me.ChartPanel.add({
					xtype : "fieldset",
					title : record.data.GroupDesc + " [ وزن گروه : " + record.data.GroupWeight + " ]",
					items : [newchart,{
						xtype : "displayfield",
						fieldLabel : "امتیاز کل گروه",
						fieldCls : "blueText",
						cls :  "blueText",
						itemId : "cmp_total_" + record.data.GroupID
					}]
				});
				
				chart = me.ChartPanel.down("[itemId=chart" + i + "]");
				chart.getStore().proxy.extraParams.FormID = FormID;
				chart.getStore().proxy.extraParams.GroupID = record.data.GroupID;
				window["R" + i] = newchart.store.load({
					callback : function(){
						record = this.getAt(0);
						GroupValue = this.getProxy().getReader().jsonData.message;
						VoteResultObject.ChartPanel.down("[itemId=cmp_total_" + record.data.GroupID + "]").
							setValue(this.getProxy().getReader().jsonData.message + "%");
						VoteResultObject.FormValue += GroupValue*record.data.GroupWeight;
						VoteResultObject.TotalFormWeights += record.data.GroupWeight*1;
					}
				});
			}
			me.ChartPanel.add({
				xtype : "fieldset",
				items : [{
					xtype : "displayfield",
					fieldLabel : "امتیاز کل فرم",
					fieldCls : "blueText",
					cls :  "blueText",
					itemId : "cmp_total"
				}]
			});
		
		var t = setInterval(function(){
			AllLoaded = true;
			for(var i=0; i < this.totalCount; i++)
				if(window["R" + i].isLoading())
				{
					AllLoaded = false;
					break;
				}
			if(AllLoaded)
			{
				clearInterval(t);
				VoteResultObject.ChartPanel.down("[itemId=cmp_total]").
					setValue(Math.round(VoteResultObject.FormValue/VoteResultObject.TotalFormWeights) + "%");
			}
		}, 1000);
		}
	});
}

VoteResult.prototype.PreviewForm = function(){
	
	if(!this.ValuesStore)
	{
		this.ValuesStore = new Ext.data.Store({
			fields: ['ItemID','ItemValue','ItemType',"ItemTitle", 'ItemValues','GroupID','GroupDesc'],
			proxy: {
				type: 'jsonp',
				url: this.address_prefix + "../../office/vote/vote.data.php?task=FilledItemsValues",
				reader: {
					root: 'rows',
					totalProperty: 'totalCount'
				}
			}
		});
	}
	
	mask = new Ext.LoadMask(this.MainPanel, {msg:'در حال بارگذاری ...'});
	mask.show();
	
	var record = this.grid.getSelectionModel().getLastSelected();
	this.ValuesStore.load({
		params : {
			FormID : record.data.FormID,
			PersonID : record.data.PersonID
		},
		callback : function(){
			
			parent = VoteResultObject.MainPanel;
			parent.removeAll();
			
			var CurGroupID = 0;
			for(i=0; i<this.totalCount; i++)
			{
				record = this.getAt(i);
				if(CurGroupID != record.data.GroupID)
				{
					parent.add({
						xtype : "fieldset",
						title : record.data.GroupDesc,
						itemId : "Group_" + record.data.GroupID,
						layout : {
							type : "table",
							columns : 2
						}
					});
					fsparent = parent.down("[itemId=Group_" + record.data.GroupID + "]");
					CurGroupID = record.data.GroupID;
				}
				
				if(record.data.ItemType == "radio")
				{
					fsparent.add({
						xtype : "displayfield",
						width : 300,
						value : record.data.ItemTitle
					});
					var items = new Array();
					arr = record.data.ItemValues.split("#");
					for(j=0; j<arr.length; j++)
						items.push({
							boxLabel : arr[j],
							name : "elem_" + record.data.ItemID,
							readOnly : true,
							inputValue : arr[j],
							checked : arr[j] == record.data.ItemValue ? true : false,
							width : 100
						});
					fsparent.add({
						xtype : "radiogroup",
						items : items
					});
				}
				else
				{
					if(record.data.ItemType == "textarea")
					{
						fsparent.add({
							xtype : "displayfield",
							value : record.data.ItemTitle,
							colspan : 2,
							width : 590
						});
					}
					fsparent.add({
						xtype: "displayfield",
						style : "line-height: 30px;",
						fieldCls : record.data.ItemType == "displayfield" ? "" : "blueText",
						fieldLabel : record.data.ItemType == "displayfield" ? "" : record.data.ItemTitle ,
						value : record.data.ItemType == "displayfield" ? record.data.ItemTitle : record.data.ItemValue,
						colspan : 2,
						width : 590
					});
				}
			}
			mask.hide();
		}
	});
}

</script>
<center>
<div id="mainForm"></div>
<br>
<div id="charts"></div>
<table>
	<tr>
		<td><div id="div_grid"></div></td>
		<td><div id="div_form"></div></td>
	</tr>
</table>
</center>