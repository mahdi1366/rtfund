<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 95.02
//-----------------------------

require_once '../header.inc.php';

if(isset($_REQUEST["print"]))
{
	ShowTrace();
}

?>
<script>
TraceTraffic.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	DocID : "",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

function TraceTraffic()
{
	this.mainPanel = new Ext.form.Panel({
		renderTo : this.get("main"),
		frame : true,
		bodyStyle : "text-align:right;padding:5px",
		title : "گزارش تردد",
		width : 400,
		items :[{
			xtype : "combo",
			width : 380,
			hidden : true,
			store: new Ext.data.Store({
				proxy:{
					type: 'jsonp',
					url: '/framework/person/persons.data.php?task=selectPersons&UserType=IsStaff',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				fields :  ['PersonID','fullname']
			}),
			displayField: 'fullname',
			valueField : "PersonID",
			name : "PersonID"
		},{
			xtype : "combo",
			store: MonthStore,   
			displayField: 'fullname',
			valueField : "PersonID",
			name : "PersonID"
		}],
		buttons : [{
			text : "",
			iconCls : "print",
			handler : function()
			{
				window.open(TraceTrafficObj.address_prefix + "TraceTraffic.php?print=true&TafsiliID=" + 
					TraceTrafficObj.mainPanel.down("[itemId=TafsiliID]").getValue());
			}
		}]
	});
}

TraceTrafficObj = new TraceTraffic();


</script>
<form id="mainForm">
	<center><br>
		<div id="main" ></div><br>
		<div><div id="regDocDIV"></div></div>
	</center>
</form>