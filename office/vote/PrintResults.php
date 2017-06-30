<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.06
//-----------------------------

require_once '../header.inc.php';
require_once './vote.class.php';

$FormID = $_REQUEST["FormID"];
$obj = new VOT_forms($FormID);
?>
<html>
	<head>
		<meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>	
		<link rel="stylesheet" type="text/css" href="/generalUI/ext4/resources/css/Loading.css" />
		<link rel="stylesheet" type="text/css" href="/generalUI/ext4/resources/css/ext-all.css" />

		<style type="text/css">
			html, body {
				font:normal 11px tahoma;
				margin:0;
				padding:0;
				border:0 none;
				overflow:hidden;
				height:100%;
			}
		</style>
	</head>
	<body dir="rtl">
		<div id="loading-mask"></div>
		<div id="loading">
			<div class="loading-indicator">در حال بارگذاری سیستم . . .
				<img src="/generalUI/ext4/resources/themes/icons/loading-balls.gif" style="margin-right:8px;" align="absmiddle"/></div>
		</div>

		<link rel="stylesheet" type="text/css" href="/generalUI/ext4/resources/css/icons.css" />
		<link rel="stylesheet" type="text/css" href="/generalUI/fonts/fonts.css" />
		<script type="text/javascript" src="/generalUI/ext4/resources/ext-all.js"></script>

		<link rel="stylesheet" type="text/css" href="/generalUI/ext4/resources/css/ext-rtl.css" />
		<script type="text/javascript" src="/generalUI/ext4/resources/ext-extend.js"></script>
		<script type="text/javascript" src="/generalUI/ext4/ux/component.js"></script>
		<script type="text/javascript" src="/generalUI/ext4/ux/message.js"></script>
		<script type="text/javascript" src="/generalUI/ext4/ux/grid/SearchField.js"></script>
		<script type="text/javascript" src="/generalUI/ext4/ux/TreeSearch.js"></script>
		<script type="text/javascript" src="/generalUI/ext4/ux/CurrencyField.js"></script>
		<script type="text/javascript" src="/generalUI/ext4/ux/grid/ExtraBar.js"></script>
		<script type="text/javascript" src="/generalUI/ext4/ux/grid/gridprinter/Printer.js"></script>
		<script type="text/javascript" src="/generalUI/ext4/ux/Printer/Printer-all.js"></script>
		<script type="text/javascript" src="/generalUI/ckeditor/ckeditor.js"></script>
		<script type="text/javascript" src="/generalUI/pdfobject.js"></script>
		<script type="text/javascript" src="/generalUI/ext4/ux/ImageViewer.js"></script>
		<link rel="stylesheet" type="text/css" href="/office/icons/icons.css" />		
		<link rel="stylesheet" type="text/css" href="/generalUI/ext4/ux/calendar/resources/css/calendar.css" />
<script>

VoteResult.prototype = {
	TabID : document.body,
	address_prefix : "<?= $js_prefix_address?>",

	FormID : <?= $FormID ?>,
	FormValue : 0,
	TotalFormWeights : 0,
	
	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function VoteResult()
{
	this.MainPanel = new Ext.Panel({
		renderTo : this.get("form"),
		frame : true,
		bodyStyle : "text-align:right",
		width : 500,
		items : [{
			xtype : "displayfield",
			fieldLabel : "عنوان فرم",
			style : "margin:5px",
			value : "<?= $obj->FormTitle ?>",
			fieldCls : "blueText"
		},{
			xtype : "displayfield",
			fieldLabel : "امتیاز کل فرم",
			style : "margin:5px",
			fieldCls : "blueText",
			cls :  "blueText",
			itemId : "cmp_total"
		}]
	});
	//..........................................................................
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
	
	this.LoadResults(this.FormID);
}

setTimeout(function(){
	Ext.get('loading').remove();
	Ext.get('loading-mask').fadeOut({
		remove:true
	});
}, 1);

var VoteResultObject;
Ext.onReady(function(){

	VoteResultObject = new VoteResult();
});

VoteResult.prototype.LoadResults = function(FormID){
	
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
				VoteResultObject.MainPanel.down("[itemId=cmp_total]").
					setValue(Math.round(VoteResultObject.FormValue/VoteResultObject.TotalFormWeights) + "%");
			}
		}, 1000);
		}
	});
}

</script>
<center>
	<br>
<div id="form"></div>
<div id="charts"></div>
</center>