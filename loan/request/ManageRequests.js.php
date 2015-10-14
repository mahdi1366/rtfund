<script>
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.07
//-----------------------------

ManageRequest.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function ManageRequest(){
	
	
}

ManageRequestObject = new ManageRequest();

ManageRequest.prototype.ManageRequest = function(){
	if(this.get("new_pass").value != this.get("new_pass2").value)
	{
		return;
	}
}

ManageRequest.OperationRender = function(value, p, record){
	
	return "<div  title='عملیات' class='setting' onclick='ManageRequestObject.OperationMenu(event);' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

ManageRequest.prototype.OperationMenu = function(e){

	record = this.grid.getSelectionModel().getLastSelected();
	var op_menu = new Ext.menu.Menu();
	
	if(record.data.StatusID == "10")
	{
		op_menu.add({text: 'جزئیات درخواست',iconCls: 'info2', 
		handler : function(){ return ManageRequestObject.LoanInfo(); }});
	
		op_menu.add({text: 'تایید درخواست',iconCls: 'tick', 
		handler : function(){ return ManageRequestObject.ChangeStatus("confirm"); }});
	
		op_menu.add({text: 'رد درخواست',iconCls: 'undo',
		handler : function(){ return ManageRequestObject.ChangeStatus("reject"); }});
	}
	op_menu.showAt(e.pageX-120, e.pageY);
}

ManageRequest.prototype.ChangeStatus = function(mode){
	
	
}

ManageRequest.prototype.LoanInfo = function(){
	
	var record = this.grid.getSelectionModel().getLastSelected();
	framework.OpenPage(this.address_prefix + "RequestInfo.php", "اطلاعات درخواست وام" , {
		RequestID : record.data.RequestID
	});
	
	return;
	
	if(!this.LoanInfoWin)
	{
		this.LoanInfoWin = new Ext.window.Window({
			width : 500,
			renderTo : this.get(this.TabID),
			height : 430,
			title: 'مشخصات درخواست وام',
			modal : true,
			closeAction : "hide",
			items : new Ext.form.Panel({
				items: [{
					xtype : "fieldset",
					title : "انتخاب وام درخواستی",
					layout : "column",
					columns : 2,
					style : "margin-right:10px",
					anchor : "98%",
					defaults : {
						labelWidth : 80,
						width : 220,
						hideTrigger : true
					},
					items : [{
						fieldLabel: 'سقف مبلغ',
						name: 'MaxAmount',
						style : "margin-bottom:8px",
						xtype : "displayfield",
						renderer : function(v){ return Ext.util.Format.Money(v) + " ریال"},
						fieldCls : "blueText"
					},{
						fieldLabel: 'مبلغ درخواست',
						name: 'ReqAmount',
						style : "margin-bottom:8px",
						xtype : "displayfield",
						renderer : function(v){ return Ext.util.Format.Money(v) + " ریال"},
						fieldCls : "blueText"
					},{
						fieldLabel: 'فاصله اقساط',
						xtype : "numberfield",
						afterSubTpl: "&nbsp;روز",
						name: 'PartInterval'
					},{
						fieldLabel: 'تعداد اقساط',
						name: 'PayCount',
						width : 205,
						xtype : "numberfield"
					},{
						fieldLabel: 'مدت تنفس',
						xtype : "numberfield",
						afterSubTpl: "&nbsp;روز",
						name: 'DelayCount'
					},{
						fieldLabel: 'مبلغ بیمه',
						name: 'InsureAmount',
						xtype : "currencyfield",
						afterSubTpl: "ریال"
					},{
						fieldLabel: 'مبلغ قسط اول',
						name: 'FirstPartAmount',
						xtype : "currencyfield",
						afterSubTpl: "ریال"
					},{
						fieldLabel: 'درصد سود',
						name: 'ProfitPercent',
						xtype : "numberfield",
						afterSubTpl: "&nbsp;%&nbsp;",
						MaxValue : 100
					},{
						fieldLabel: 'درصد دیرکرد',
						xtype : "numberfield",
						afterSubTpl: "&nbsp;%&nbsp;",
						MaxValue : 100,
						name: 'ForfeitPercent'
					},{
						fieldLabel: 'درصد کارمزد',
						xtype : "numberfield",
						afterSubTpl: "&nbsp;%&nbsp;",
						MaxValue : 100,
						name: 'FeePercent'
					},{
						fieldLabel: 'مبلغ کارمزد',
						name: 'FeeAmount',
						xtype : "currencyfield",
						afterSubTpl: "ریال"
					}]
				},{
					xtype : "fieldset",
					title : "جزئیات درخواست",
					anchor : "98%",
					style : "margin-right:10px",
					items : [{
						xtype : "currencyfield",
						name : "OkAmount",
						fieldLabel : "مبلغ مورد تایید",
						hideTrigger : true,
						afterSubTpl: "ریال"
					},{
						xtype : "textarea",
						fieldLabel : "توضیحات",
						anchor : "90%",
						name : "ReqDetails"
					}]
				}]
			}),
			buttons :[{
				text : "ذخیره",
				iconCls : "save",
				handler : function(){
					ManageRequestObject.SaveLoanRequest();
				}
			},{
				text : "بازگشت",
				iconCls : "undo",
				handler : function(){
					this.up('window').hide();
				}
			}]
		});
	}
	
	var record = this.grid.getSelectionModel().getLastSelected();
	this.LoanInfoWin.down('form').loadRecord(record);
	this.LoanInfoWin.down("[name=OkAmount]").setMaxValue(record.data.MaxAmount);
	this.LoanInfoWin.show();
	this.LoanInfoWin.center();
}

ManageRequest.prototype.SaveLoanRequest = function(){
	
	mask = new Ext.LoadMask(this.LoanInfoWin, {msg:'در حال ذخيره سازي...'});
	mask.show();  
	this.LoanInfoWin.down('form').getForm().submit({
		clientValidation: true,
		url: this.address_prefix + 'request.data.php?task=SaveLoanRequest' , 
		method: "POST",
		params : {
			RequestID : this.grid.getSelectionModel().getLastSelected().data.RequestID
		},
		
		success : function(form,action){
			mask.hide();
			ManageRequestObject.LoanInfoWin.hide();
			ManageRequestObject.grid.getStore().load();
		},
		failure : function(){
			mask.hide();
			//Ext.thisssageBox.alert("","عملیات مورد نظر با شکست مواجه شد");
		}
	});
}

</script>