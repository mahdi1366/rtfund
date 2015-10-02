<script>
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.07
//-----------------------------

LoanRequests.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function LoanRequests(){
	
	
}

LoanRequestsObject = new LoanRequests();

LoanRequests.prototype.LoanRequests = function(){
	if(this.get("new_pass").value != this.get("new_pass2").value)
	{
		return;
	}
}

LoanRequests.OperationRender = function(value, p, record){
	
	return "<div  title='عملیات' class='setting' onclick='LoanRequestsObject.OperationMenu(event);' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

LoanRequests.prototype.OperationMenu = function(e){

	record = this.grid.getSelectionModel().getLastSelected();
	var op_menu = new Ext.menu.Menu();
	
	if(record.data.StatusID == "10")
	{
		op_menu.add({text: 'جزئیات درخواست',iconCls: 'info2', 
		handler : function(){ return LoanRequestsObject.LoanInfo(); }});
	
		op_menu.add({text: 'تایید درخواست',iconCls: 'tick', 
		handler : function(){ return LoanRequestsObject.ChangeStatus("confirm"); }});
	
		op_menu.add({text: 'رد درخواست',iconCls: 'undo',
		handler : function(){ return LoanRequestsObject.ChangeStatus("reject"); }});
	}
	op_menu.showAt(e.pageX-120, e.pageY);
}

LoanRequests.prototype.ChangeStatus = function(mode){
	
	
}

LoanRequests.prototype.LoanInfo = function(){
	
	if(!this.LoanInfoWin)
	{
		this.LoanInfoWin = new Ext.window.Window({
			width : 500,
			renderTo : this.get(this.TabID),
			height : 410,
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
						xtype : "displayfield",
						style : "margin-top:10px",
						labelWidth : 80,
						width : 220,
						fieldCls : "blueText"
					},
					items : [{
						fieldLabel: 'سقف مبلغ',
						name: 'MaxAmount',
						renderer : function(v){ return Ext.util.Format.Money(v) + " ریال"}
					},{
						fieldLabel: 'تعداد اقساط',
						name: 'CostusCount'
					},{
						fieldLabel: 'فاصله اقساط',
						renderer : function(v){ return v + " روز"},
						name: 'CostusInterval',
						value : 0
					},{
						fieldLabel: 'مدت تنفس',
						renderer : function(v){ return v + " ماه"},
						name: 'DelayCount'
					},{
						fieldLabel: 'مبلغ بیمه',
						name: 'InsureAmount',
						renderer : function(v){ return Ext.util.Format.Money(v) + " ریال"}
					},{
						fieldLabel: 'مبلغ قسط اول',
						name: 'FirstCostusAmount',
						renderer : function(v){ return Ext.util.Format.Money(v) + " ریال"}
					},{
						fieldLabel: 'درصد سود',
						name: 'ProfitPercent',
						renderer : function(v){ return v + " %"},
						value : 0
					},{
						fieldLabel: 'درصد دیرکرد',
						renderer : function(v){ return v + " %"},
						name: 'ForfeitPercent'
					},{
						fieldLabel: 'درصد کارمزد',
						renderer : function(v){ return v + " %"},
						name: 'FeePercent'
					},{
						fieldLabel: 'مبلغ کارمزد',
						name: 'FeeAmount',
						rrenderer : function(v){ return Ext.util.Format.Money(v) + " ریال"}
					}]
				},{
					xtype : "fieldset",
					title : "جزئیات درخواست",
					anchor : "98%",
					style : "margin-right:10px",
					items : [{
						xtype : "displayfield",
						name : "ReqAmount",
						style : "margin-top:10px",
						fieldCls : "blueText",
						fieldLabel : "مبلغ درخواستی",
						afterSubTpl: '<tpl>ریال</tpl>'
					},{
						xtype : "displayfield",
						fieldLabel : "توضیحات",
						style : "margin-top:10px",
						fieldCls : "blueText",
						anchor : "90%",
						name : "ReqDetails"
					}]
				}]
			}),
			buttons :[{
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
	this.LoanInfoWin.show();
	this.LoanInfoWin.center();
}

</script>