<script type="text/javascript">
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.06
//-----------------------------

WFM.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function WFM()
{
	
}

WFM.deleteRender = function(v,p,r)
{
	return "<div align='center' title='حذف ' class='remove' onclick='WFMObject.Deleting();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

WFM.StepsRender = function(v,p,r)
{
	return "<div align='center' title='حذف ' class='step' onclick='WFMObject.Steps();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

WFM.prototype.Adding = function()
{
	var modelClass = this.grid.getStore().model;
	var record = new modelClass({
		FlowID : "",
		FlowDesc : ""
	});

	this.grid.plugins[0].cancelEdit();
	this.grid.getStore().insert(0, record);
	this.grid.plugins[0].startEdit(0, 0);
}

WFM.prototype.saveData = function(store,record)
{
    mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		params: {
			task: 'SaveFlow',
			record : Ext.encode(record.data)
		},
		url: this.address_prefix +'wfm.data.php',
		method: 'POST',

		success: function(response){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				WFMObject.grid.getStore().load();
			}
			else
			{
				Ext.MessageBox.alert("Error",st.data);
			}
		},
		failure: function(){}
	});
}

WFM.prototype.Deleting = function()
{
	var record = this.grid.getSelectionModel().getLastSelected();
	if(record && confirm("آيا مايل به حذف مي باشيد؟"))
	{
		Ext.Ajax.request({
		  	url : this.address_prefix + "wfm.data.php",
		  	method : "POST",
		  	params : {
		  		task : "DeleteFlow",
		  		FlowID : record.data.FlowID
		  	},
		  	success : function(response,o)
		  	{
		  		WFMObject.grid.getStore().load();
		  	}
		});
	}
}

WFM.prototype.Steps = function(){
	
	var record = this.grid.getSelectionModel().getLastSelected();
	this.StepsGrid.getStore().proxy.extraParams = {
		FlowID : record.data.FlowID
	};
	if(!this.stepsWin)
	{
		this.stepsWin = new Ext.window.Window({
			width : 610,
			title : "مراحل گردش",
			height : 460,
			modal : true,
			closeAction : "hide",
			items : [this.StepsGrid],
			buttons :[{
				text : "بازگشت",
				iconCls : "undo",
				handler : function(){this.up('window').hide();}
			}]
		});
		Ext.getCmp(this.TabID).add(this.stepsWin);
	}
	else
		this.StepsGrid.getStore().load();

	this.stepsWin.show();
	this.stepsWin.center();
}

//----------------------------------------------------------

WFM.deleteStepRender = function(v,p,r)
{
	return "<div align='center' title='حذف ' class='remove' onclick='WFMObject.DeleteStep();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

WFM.upRender = function(v,p,r)
{
	return "<div align='center' title='حذف ' class='up' onclick='WFMObject.moveStep(-1);' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

WFM.downRender = function(v,p,r)
{
	return "<div align='center' title='حذف ' class='down' onclick='WFMObject.moveStep(1);' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

WFM.prototype.AddStep = function()
{
	var record = this.grid.getSelectionModel().getLastSelected();
	
	var modelClass = this.StepsGrid.getStore().model;
	var record = new modelClass({
		FlowID : record.data.FlowID,
		StepID : "",
		StepDesc : ""
	});

	this.StepsGrid.plugins[0].cancelEdit();
	this.StepsGrid.getStore().insert(0, record);
	this.StepsGrid.plugins[0].startEdit(0, 0);
}

WFM.prototype.saveStep = function(store,record)
{
    mask = new Ext.LoadMask(this.stepsWin, {msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		params: {
			task: 'SaveStep',
			record : Ext.encode(record.data)
		},
		url: this.address_prefix +'wfm.data.php',
		method: 'POST',

		success: function(response){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				WFMObject.StepsGrid.getStore().load();
			}
			else
			{
				Ext.MessageBox.alert("Error",st.data);
			}
		},
		failure: function(){}
	});
}

WFM.prototype.moveStep = function(direction)
{
	var record = this.StepsGrid.getSelectionModel().getLastSelected();
	
    mask = new Ext.LoadMask(this.stepsWin, {msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		params: {
			task: 'MoveStep',
			FlowID : record.data.FlowID,
			StepID : record.data.StepID,
			direction : direction
		},
		url: this.address_prefix + 'wfm.data.php',
		method: 'POST',

		success: function(response){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				WFMObject.StepsGrid.getStore().load();
			}
			else
			{
				Ext.MessageBox.alert("Error",st.data);
			}
		},
		failure: function(){}
	});
}

WFM.prototype.DeleteStep = function()
{
	var record = this.StepsGrid.getSelectionModel().getLastSelected();
	if(record && confirm("آيا مايل به حذف مي باشيد؟"))
	{
		Ext.Ajax.request({
		  	url : this.address_prefix + "wfm.data.php",
		  	method : "POST",
		  	params : {
		  		task : "DeleteStep",
		  		FlowID : record.data.FlowID,
				StepID : record.data.StepID
		  	},
		  	success : function(response,o)
		  	{
		  		WFMObject.StepsGrid.getStore().load();
		  	}
		});
	}
}

</script>
