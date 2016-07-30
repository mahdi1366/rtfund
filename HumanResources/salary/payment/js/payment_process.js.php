<script type="text/javascript">
//---------------------------
// programmer:	b.Mahdipour
// create Date:	94.11
//---------------------------
<?
ini_set("display_errors","On");

?>
PaymentProcess.prototype = {

	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",    

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function PaymentProcess(){
	
	new Ext.form.field.ComboBox({
		transform :this.get("person_type"),
		typeAhead: false,
		queryMode : "local"
	});
	
	new Ext.form.field.ComboBox({
		transform :this.get("tax_normalized_month"),
		typeAhead: false,
		queryMode : "local"
	});
	
	this.field = new Ext.form.TriggerField({
		triggerCls:'x-form-search-trigger',
		onTriggerClick : function(){
			this.setValue(LOV_staff());
		},
		applyTo : this.get("from_staff_id"),
		width : 90
	});

	this.field = new Ext.form.TriggerField({
		triggerCls:'x-form-search-trigger',
		onTriggerClick : function(){
			this.setValue(LOV_staff());
		},
		applyTo : this.get("to_staff_id"),
		width : 90
	});

	new Ext.form.TriggerField({
		triggerCls:'x-form-search-trigger',
		onTriggerClick : function(){
			returnVal = LOV_OrgUnit();

			if(returnVal != "")
			{
				this.setValue(returnVal);
			}
		},
		applyTo : this.get("ouid"),
		width : 90
	});

	this.resultPanel = new Ext.Panel({
		applyTo : this.get("result_data"),
		width : 730,
		border : 0,				
		autoHeight: true,
		layout : "vbox",
		items : [{
			xtype : "container",
			width : 700,
			itemId : "resultPnl",
			loader : {
				url : "/HumanResources/tempDir/pay_calc_monitor_file.html",
				scripts: true
			}
		},{
			xtype : "container",
			width : 700,
			itemId : "resultSt"
		}]
		
	});
   

	new Ext.Panel({
		id: "j1",
		applyTo: this.get("issuePayment_DIV"),
		contentEl : this.get("issuePayment_TBL"),
		title: "محاسبه حقوق",
		autoHeight: true,
		width: 750,
		collapsible : true,
		frame: true,
		bodyCfg: {style : "padding-right:10px;background-color:white;"},
		buttons: [{
				text : "محاسبه حقوق",
				iconCls : "process",
				handler : function(){
					
					if(PaymentProcessObject.cmp_start_date.getValue() == null || PaymentProcessObject.cmp_end_date.getValue() == null)
					{
						alert("تکمیل تاریخ شروع و پایان الزامی است");
						return;
					}
					PaymentProcessObject.IssuePayment(this);
				}
			}]
	});

	this.cmp_start_date = new Ext.form.SHDateField({
		id: 'ext_start_date',
		applyTo: this.get('start_date'),
		format: 'Y/m/d'
	});

	this.cmp_end_date = new Ext.form.SHDateField({
		id: 'ext_end_date',
		applyTo: this.get('end_date'),
		format: 'Y/m/d'
	});
}

var PaymentProcessObject = new PaymentProcess();

PaymentProcess.prototype.IssuePayment = function(btn){

	//btn.disable();
	//btn.setText(' در حال محاسبه حقوق، لطفا منتظر بمانید...') ; 
		
	this.get('result').style.display = 'block';
	this.loadChain = setInterval(function(){PaymentProcessObject.loadProgress()},1000);
	
	Ext.Ajax.request({
		url: this.address_prefix + '../data/payment.data.php?task=ProcessPayment',
		method: 'POST',
		form : this.get("mainForm"),
		
		success: function(response){
			var sd = Ext.decode(response.responseText);
			if(!sd.success)
			{					
				clearInterval(PaymentProcessObject.loadChain);
				PaymentProcessObject.get("result").style.display = "none";		
				alert(sd.data);									
		
			}
			else {								
				var cnt = sd.data.split("_");
				PaymentProcessObject.get("img_loading").style.display = "none";		
				PaymentProcessObject.resultPanel.down("[itemId=resultSt]").update(
					"<br>" + "<a href='/HumanResources/tempDir/success_log.php' target='_blank' >" +
					"<img src=<?=HR_ImagePath?>success.gif > " + (cnt[0]-1 ) + " موفق " + "</a> &nbsp;&nbsp;" + "<a href='/HumanResources/tempDir/fail_log.php' target='_blank' >" +
					"<img src=<?=HR_ImagePath?>fail.gif > " + (cnt[1]-1) + " شکست  " );  				
			}
		},
		failure: function(){}
	});
}

PaymentProcess.prototype.loadProgress = function(){

	
    this.resultPanel.down("[itemId=resultPnl]").loader.load({callback:function(){
						
			var t = PaymentProcessObject.resultPanel.getEl().dom.innerHTML.indexOf('پايان') ;					
			if(t > 0 ) {
				clearInterval(PaymentProcessObject.loadChain);						
			} 
	}});
		
}
</script>