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
					VoteResultObject.LoadResults(this.getValue());
				}
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
		frame : true,
		layout : {
			type : "table",
			columns : 2
		}
	});
	
	this.ItemsStore = new Ext.data.Store({
		fields: ['FormID','ItemID','ItemType',"ItemTitle", 'ItemValues'],
		proxy: {
			type: 'jsonp',
			url: this.address_prefix + "../../office/vote/vote.data.php?task=SelectItems",
			reader: {
				root: 'rows',
				totalProperty: 'totalCount'
			}
		}
	});
	
	//..........................................................................
	this.chartPanel1 = new Ext.form.Panel({
        width: 900,
        height: 300,
		autoScroll : true,
        hidden: true,
        maximizable: true,
        renderTo: this.get("chart1"),
        layout: 'fit', 
		items :[{
			id: 'chartCmp',
            xtype: 'chart',
            style: 'background:#fff',
            animate: true,
            shadow: true,
            store: new Ext.data.Store({
				fields: ["ItemTitle","mid",{
					name : "data",
					convert : function(v,r){return parseInt(r.data.mid);}
				}],
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + "../../office/vote/vote.data.php?task=SelectChart1Data",
					reader: {
						root: 'rows',
						totalProperty: 'totalCount'
					}
				}
			}),
            axes: [{
                type: 'Numeric',
                position: 'left',
                fields: ['data'],
                maximum: 100
            }, {
                type: 'Category',
                position: 'bottom',
                fields: ['ItemTitle']
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
                    var value = (record.data.mid > 0) % 5;
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
                  height: 28,
                  renderer: function(storeItem, item) {
                    this.setTitle(storeItem.get('ItemTitle') + ' ' + storeItem.get('data'));
                  }
                }
            }]
		}]
	});

}

VoteResult.viewRender = function(v,p,r){
	
	return "<div align='center' title='نمایش' class='view' onclick='VoteResultObject.PreviewForm();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

VoteResultObject = new VoteResult();

VoteResult.prototype.LoadResults = function(FormID){
	
	this.chartPanel1.show();
	this.MainPanel.show();
	
	this.grid.getStore().proxy.extraParams.FormID = FormID;
	this.chartPanel1.down('chart').getStore().proxy.extraParams.FormID = FormID;
	this.chartPanel1.down('chart').getStore().load();
	
	if(this.grid.rendered)
		this.grid.getStore().load();
	else
		this.grid.render(this.get("div_grid"));
}

VoteResult.prototype.FillForm = function(FormID){
	
	this.FormWin.down("[itemId=saveBtn]").show();
	this.FormWin.show();
	this.ItemsStore.load({
		params : {
			FormID : FormID
		},
		callback : function(){
			VoteResultObject.FormWin.down("[name=FormID]").setValue(this.getAt(0).data.FormID);
			parent = VoteResultObject.FormWin.down('[itemId=form]');
			parent.removeAll();
			
			for(i=0; i<this.getCount(); i++)
			{
				record = this.getAt(i);
				
				if(record.data.ItemType == "combo")
				{
					arr = record.data.ItemValues.split("#");
					data = [];
					for(j=0;j<arr.length;j++)
						data.push([ arr[j] ]);
					
					parent.add({
						store : new Ext.data.SimpleStore({
							fields : ['value'],
							data : data
						}),
						xtype: record.data.ItemType,
						valueField : "value",
						displayField : "value",
						name : "elem_" + record.data.ItemID,
						fieldLabel : record.data.ItemTitle,
						colspan : 2
					});
				}
				else if(record.data.ItemType == "radio")
				{
					parent.add({
						xtype : "displayfield",
						value : record.data.ItemTitle,
						width : 400
					});
					var items = new Array();
					arr = record.data.ItemValues.split("#");
					for(j=0; j<arr.length; j++)
						items.push({
							boxLabel : arr[j],
							inputValue : arr[j],
							name : "elem_" + record.data.ItemID,
							width : 50
						});
					parent.add({
						xtype : "radiogroup",
						items : items,		
						width : 200
					});
				}
				else
				{
					parent.add({
						xtype: record.data.ItemType,
						fieldLabel : record.data.ItemName,
						name : "elem_" + record.data.ItemID,
						hideTrigger : record.data.ItemType == 'numberfield' || record.data.ItemType == 'currencyfield' ? true : false,
						value : record.data.ItemValues,
						colspan : 2
					});
				}
			}
		}
	});	
}

VoteResult.prototype.SaveFilledForm = function(){

	mask = new Ext.LoadMask(this.FormWin, {msg:'در حال ذخیره سازی ...'});
	mask.show();
	
	this.FormWin.down('[itemId=form]').getForm().submit({
		url : this.address_prefix + "../../office/vote/vote.data.php",
		method : "post",
		params : {
			task : "SaveFilledForm",
			FormID : this.FormWin.down('[name=FormID]').getValue()
		},
		
		success : function(){
			mask.hide();
			VoteResultObject.FormWin.hide();
			VoteResultObject.NewVoteResultStore .load();
			VoteResultObject.grid.getStore().load();
		},
		
		failure : function(){
			mask.hide();
		}
	});

}

VoteResult.prototype.PreviewForm = function(){
	
	if(!this.ValuesStore)
	{
		this.ValuesStore = new Ext.data.Store({
			fields: ['ItemID','ItemValue','ItemType',"ItemTitle", 'ItemValues'],
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
			
			for(i=0; i<this.totalCount; i++)
			{
				record = this.getAt(i);
				
				if(record.data.ItemType == "radio")
				{
					parent.add({
						xtype : "displayfield",
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
					parent.add({
						xtype : "radiogroup",
						items : items
					});
				}
				else
				{
					if(record.data.ItemType == "textarea")
					{
						parent.add({
							xtype : "displayfield",
							value : record.data.ItemTitle,
							colspan : 2,
							width : 590
						});
					}
					parent.add({
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
<div id="chart1"></div>
<table>
	<tr>
		<td><div id="div_grid"></div></td>
		<td><div id="div_form"></div></td>
	</tr>
</table>
</center>