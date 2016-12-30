<script>
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.07
//-----------------------------

ManageForm.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	AddAccess : <?= $accessObj->AddFlag ? "true" : "false" ?>,
	EditAccess : <?= $accessObj->EditFlag ? "true" : "false" ?>,
	RemoveAccess : <?= $accessObj->RemoveFlag ? "true" : "false" ?>,
	
	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function ManageForm(){
	
}

ManageFormObject = new ManageForm();

ManageForm.OperationRender = function(value, p, record){
	
	return "<div  title='عملیات' class='setting' onclick='ManageFormObject.OperationMenu(event);' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

ManageForm.prototype.OperationMenu = function(e){

	record = this.grid.getSelectionModel().getLastSelected();
	var op_menu = new Ext.menu.Menu();
	
	if(this.EditAccess)
	{
		op_menu.add({text: 'تغییر وضعیت',iconCls: 'refresh', 
		handler : function(){ return ManageFormObject.SetStatus(record); }});
	
		op_menu.add({text: 'اطلاعات آیتم',iconCls: 'info2', 
			handler : function(){ return ManageFormObject.FormInfo(); }});
	}
	op_menu.add({text: 'سابقه درخواست',iconCls: 'history', 
		handler : function(){ return ManageFormObject.ShowHistory(); }});
	
	op_menu.showAt(e.pageX-120, e.pageY);
}

ManageForm.prototype.FormInfo = function(){
	
	if(!this.FormInfoWindow)
	{
		this.FormInfoWindow = new Ext.window.Window({
			width : 800,
			height : 400,
			autoScroll : true,
			modal : true,
			title : "اطلاعات فرم مربوطه",
			bodyStyle : "background-color:white",
			loader : {
				scripts : true
			},
			closeAction : "hide",
			buttons : [{
				text : "بازگشت",
				iconCls : "undo",
				handler : function(){this.up('window').hide();}
			}]
		});
	}
	
	var record = this.grid.getSelectionModel().getLastSelected();
	if(record.data.target == "1")
	{
		window.open(record.data.url + "?" + 
			record.data.parameter + "=" + record.data.ObjectID );
		return;
	}
	this.FormInfoWindow.show();	
	eval("param = {ExtTabID : '" + this.FormInfoWindow.getEl().id + "'," + 
					record.data.parameter + " : '" + record.data.ObjectID + "'," +
					"ReadOnly : true}");
	
	this.FormInfoWindow.loader.load({
		url : record.data.url,
		params : param
	}); 	
}

ManageForm.prototype.ShowHistory = function(){

	if(!this.HistoryWin)
	{
		this.HistoryWin = new Ext.window.Window({
			title: 'سابقه گردش',
			modal : true,
			autoScroll : true,
			width: 700,
			height : 500,
			closeAction : "hide",
			loader : {
				url : this.address_prefix + "history.php",
				scripts : true
			},
			buttons : [{
					text : "بازگشت",
					iconCls : "undo",
					handler : function(){
						this.up('window').hide();
					}
				}]
		});
		Ext.getCmp(this.TabID).add(this.HistoryWin);
	}
	this.HistoryWin.show();
	this.HistoryWin.center();
	this.HistoryWin.loader.load({
		params : {
			RowID : this.grid.getSelectionModel().getLastSelected().data.RowID
		}
	});
}

ManageForm.prototype.SetStatus = function(record){
	
	this.setStatusWin = new Ext.window.Window({
		width : 412,
		height : 198,
		modal : true,
		title : "تغییر وضعیت",
		defaults : {width : 380},
		bodyStyle : "background-color:white",
		items : [{
			xtype : "combo",
			store : new Ext.data.SimpleStore({
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + "wfm.data.php?task=selectFlowSteps&FlowID=" + record.data.FlowID,
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				fields : ['StepID','StepDesc'],
				autoLoad : true					
			}),
			fieldLabel : "مرحله جدید",
			queryMode : 'local',
			allowBlank : false,
			displayField : "StepDesc",
			valueField : "StepID",
			itemId : "StepID"
		},{
			xtype : "textarea",
			itemId : "comment",
			fieldLabel : "توضیحات"
		}],
		closeAction : "destroy",
		buttons : [{
			text : "تغییر وضعیت",				
			iconCls : "save",
			itemId : "btn_save",
			handler : function(){
				StepID = this.up('window').getComponent("StepID").getValue();
				comment = this.up('window').getComponent("comment").getValue();
				
				mask = new Ext.LoadMask(ManageFormObject.grid, {msg:'در حال ذخيره سازي...'});
				mask.show();  

				Ext.Ajax.request({
					methos : "post",
					url : ManageFormObject.address_prefix + "wfm.data.php",
					params : {
						task : "ChangeStatus",
						mode : "CONFIRM",
						StepID : StepID,
						RowID : record.data.RowID,
						ActionComment : "[تغییر وضعیت]" + comment
					},
					success : function(){
						mask.hide();
						ManageFormObject.grid.getStore().load();
					}
				});
				this.up('window').hide();
			}
		},{
			text : "بازگشت",
			iconCls : "undo",
			handler : function(){this.up('window').close();}
		}]
	});

	Ext.getCmp(this.TabID).add(this.setStatusWin);
	this.setStatusWin.show();
	this.setStatusWin.center();
}


</script>