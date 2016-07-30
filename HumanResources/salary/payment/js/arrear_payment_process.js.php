<script type="text/javascript">
//---------------------------
// programmer:	b.Mahdipour
// create Date:	93.07
//---------------------------

ArrearPayProcess.prototype = {

	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",
    FacilID : <?= $_REQUEST["FacilID"]?>,

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function ArrearPayProcess(){
	
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
				url : "/HumanResources/HRProcess/arrear_pay_calc_monitor_file.html",
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
		title: "محاسبه دیون",
		autoHeight: true,
		width: 750,
		collapsible : true,
		frame: true,
		bodyCfg: {style : "padding-right:10px;background-color:white;"},
		buttons: [{
				text : "محاسبه دیون",
				iconCls : "refresh",
				handler : function(){
                    
                   if(!(ArrearPayProcessObject.get("mainForm").pay_year.value > 0 ))
					{
						alert("لطفا سال محاسبه را وارد نمایید.");
						return;
					}
					ArrearPayProcessObject.IssueArrearPayment(this);
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

var ArrearPayProcessObject = new ArrearPayProcess();

ArrearPayProcess.prototype.IssueArrearPayment = function(btn){

	//btn.disable();
	//btn.setText(' در حال محاسبه حقوق، لطفا منتظر بمانید...') ; 
		
	this.get('result').style.display = 'block';
	this.loadChain = setInterval(function(){ArrearPayProcessObject.loadProgress()},1000);
	
	Ext.Ajax.request({
		url: this.address_prefix + '../data/payment.data.php?task=ProcessArrearPayment',
		method: 'POST',
		form : this.get("mainForm"),
		
		success: function(response){
			var sd = Ext.decode(response.responseText);
			if(!sd.success)
			{					
				clearInterval(ArrearPayProcessObject.loadChain);
				ArrearPayProcessObject.get("result").style.display = "none";
				alert(sd.data);										
			}
			else {				
							
				var cnt = sd.data.split("_");
				ArrearPayProcessObject.get("img_loading").style.display = "none";
				ArrearPayProcessObject.resultPanel.down("[itemId=resultSt]").update(
					"<br>" + "<a href='/HumanResources/HRProcess/arrear_success_log.php' target='_blank' >" +
					"<img src=<?=HR_ImagePath?>success.gif > " + (cnt[0]-1 ) + " موفق " + "</a> &nbsp;&nbsp;" + "<a href='/HumanResources/HRProcess/arrear_fail_log.php' target='_blank' >" +
					"<img src=<?=HR_ImagePath?>fail.gif > " + (cnt[1]-1) + " شکست  " );  				
			}
		},
		failure: function(){}
	});
}

ArrearPayProcess.prototype.loadProgress = function(){

	
    this.resultPanel.down("[itemId=resultPnl]").loader.load({callback:function(){
						
			var t = ArrearPayProcessObject.resultPanel.getEl().dom.innerHTML.indexOf('پايان') ;
			if(t > 0 ) {
				clearInterval(ArrearPayProcessObject.loadChain);
			} 
	}});
	
}
</script>