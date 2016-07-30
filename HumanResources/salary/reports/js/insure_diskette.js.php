<script type="text/javascript">
//---------------------------
// programmer:	B.Mahdipour
// create Date:	93.06
//---------------------------

InsureDisk.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",
	mainPanel : "",    	
	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};
InsureDisk.prototype.showReport = function(btn,e)
{     	
	this.form = this.get("mainForm")
	
	if(!this.form.pay_year.value)
	{
		alert('وارد کردن سال الزامی می باشد.')  ; 	
		return false ; 
	}
	
	if(!this.form.pay_month.value)
	{
		alert('وارد کردن ماه الزامی می باشد.')  ; 	
		return false ; 
	}
	
	this.form.target = "_blank";
	this.form.method = "POST";
	if(this.get("Type").value == "1")
		this.form.action =  this.address_prefix + "insure_diskette.php?task=ShowList";
	else if(this.get("Type").value == "2")
		this.form.action =  this.address_prefix + "insure_diskette.php?task=GetDisk";
	else if(this.get("Type").value == "3")
		this.form.action =  this.address_prefix + "insure_diskette.php?task=ApprovedForm";
	this.form.submit();	
	return;
}

function InsureDisk()
{
	   
var types = Ext.create('Ext.data.ArrayStore', {
fields: ['val', 'title'],
		data : [
		['1','هیئت علمی'],                               
		['2','کارمند'],                               
		['3','روزمزدبیمه ای'],                               
		['5','قراردادی'],                               
		['102','هیئت علمی،کارمند،روزمزد'],		 
		['10','بازنشسته'],

		]
});

var RepTYP = Ext.create('Ext.data.ArrayStore', {
			fields: ['val', 'title'],
			data : [
					 ['1','بر اساس مبالغ و تفاوت ها '],
					 ['2','بر اساس مبالغ'],
					 ['3','براساس تفاوت ها ']
					]
});
							
							 
							
							 
	 this.formPanel = new Ext.form.Panel({
			applyTo: this.get("mainpanel"),
			layout: {
                        type:"table",
                        columns:2
                    },
                                collapsible: false,
                                frame: true,
                                title: 'گزارش تهیه دیسکت بیمه',
                                bodyPadding: '5 5 0',
                                width:740,
                                fieldDefaults: {
                                        msgTarget: 'side',
                                        labelWidth: 130	 
                                },
                                defaultType: 'textfield',
                                items: [{
                                         xtype:"numberfield" ,
                                         fieldLabel: 'سال',
										 inputId:'pay_year',
                                         name: 'pay_year',
                                         width:200,
                                         hideTrigger:true
                                        },
										{
                                         xtype:"numberfield" ,
                                         fieldLabel: 'ماه',
                                         name: 'pay_month',
                                         width:200,
                                         hideTrigger:true
                                        },																												
										{
                                            xtype : "combo",
                                            hiddenName:"PersonType",                                    
                                            fieldLabel : "نوع فرد",
                                            store: types,
                                            valueField: 'val',
                                            displayField: 'title'
                                        },										 
                                        {
											xtype : "combo",
											store :  new Ext.data.Store({
												fields : ["InfoID","Title"],
												proxy : {
															type: 'jsonp',
															url : this.address_prefix + "../../../global/domain.data.php?task=searchPayType",
															reader: {
																root: 'rows',
																totalProperty: 'totalCount'
															}
														}
																		}),
											valueField : "InfoID",
											displayField : "Title",
											hiddenName : "PayType",
											fieldLabel : "نوع پرداخت",
											listConfig: {
												loadingText: 'در حال جستجو...',
												emptyText: 'فاقد اطلاعات',
												itemCls : "search-item"
											},
											width:300
										},
										{
											xtype: 'fieldset',
											title : "کارگاه",
											colspan : 3,
											style:'background-color:#DFEAF7',					
											width : 700,						
											fieldLabel: 'Auto Layout',
											itemId : "chkgroup2",	
											collapsible: true,
											collapsed: true,
											layout : {
												type : "table",
												columns : 3,
												tableAttrs : {
													width : "100%",
													align : "center"
												},
												tdAttrs : {							
													align:'right',
													width : "۱6%"
												}
											},
											items : [{
												xtype : "checkbox",
												boxLabel : "همه",
												checked : true,							
												listeners : {
													change : function(){
														parentNode = InsureDiskObject.formPanel.down("[itemId=chkgroup2]").getEl().dom;
														elems = parentNode.getElementsByTagName("input");
														for(i=0; i<elems.length; i++)
														{
															if(elems[i].id.indexOf("chkDetect_") != -1)
																elems[i].checked = this.getValue();
														}
													}
												}
											}]
										},	
										{
											xtype: 'fieldset',
											title : "مراکز هزینه",
											colspan : 3,		
											style:'background-color:#DFEAF7',					
											width : 700,						
											fieldLabel: 'Auto Layout',
											itemId : "chkgroup",
											collapsible: true,
											collapsed: true,
											layout : {
												type : "table",
												columns : 4,
												tableAttrs : {
													width : "100%",
													align : "center"
												},
												tdAttrs : {							
													align:'right',
													width : "۱6%"
												}
											},
											items : [{
												xtype : "checkbox",
												boxLabel : "همه",
												checked : true,
												listeners : {
													change : function(){
														parentNode = InsureDiskObject.formPanel.down("[itemId=chkgroup]").getEl().dom;
														elems = parentNode.getElementsByTagName("input");
														for(i=0; i<elems.length; i++)
														{
															if(elems[i].id.indexOf("chkcostID_") != -1)
																elems[i].checked = this.getValue();
														}
													}
												}
											}]
										},
										{
                                            xtype : "combo",
                                            hiddenName:"ReportType",                                    
                                            fieldLabel : "نوع گزارش",
                                            store: RepTYP,
                                            valueField: 'val',
                                            displayField: 'title'
                                        }
										] , 
                                buttons: [
										 {
											text : "لیست بیمه",
											handler : function(){InsureDiskObject.showReport()},
											listeners : {
												click : function(){
													InsureDiskObject.get('Type').value = "1";
												}
											},
											iconCls : "report"
										 },
										 {
                                            text : "تهیه دیسکت",
                                            handler : Ext.bind(this.showReport,this),
											listeners : {
												click : function(){
													InsureDiskObject.get('Type').value = "2";
												}
											},
                                            iconCls : "save"                                
                                          },
										  {
											text : "فرم تائید پرداخت بیمه",
											handler : function(){InsureDiskObject.showReport()},
											listeners : {
												click : function(){
													InsureDiskObject.get('Type').value = "3";
												}
											},
											iconCls : "list"
										 }]
                                });
								
					new Ext.data.Store({
							fields : ["cost_center_id","title"],
							proxy : {
								type: 'jsonp',
								url : this.address_prefix + "../../../global/domain.data.php?task=searchCostCenter",
								reader: {
									root: 'rows',
									totalProperty: 'totalCount'
								}
							},
							autoLoad : true,
							listeners:{
								load : function(){
									this.each(function (record) {
										InsureDiskObject.formPanel.down("[itemId=chkgroup]").add({
											xtype : "container",
											html : "<input type=checkbox name=chkcostID_" + record.data.cost_center_id + " id=chkcostID_" + record.data.cost_center_id + " checked > " + record.data.title
										});
										
									});
														
								}}
							
						});
						
					new Ext.data.Store({
							fields : ["daily_work_place_no","detective_name"],
							proxy : {
								type: 'jsonp',
								url : this.address_prefix + "../../../global/domain.data.php?task=searchDetective",
								reader: {
									root: 'rows',
									totalProperty: 'totalCount'
								}
							},
							autoLoad : true,
							listeners:{
								load : function(){
									this.each(function (record) {
										InsureDiskObject.formPanel.down("[itemId=chkgroup2]").add({
											xtype : "container",
											html : "<input type=checkbox name=chkDetect_" + record.data.daily_work_place_no + " id=chkDetect_" + record.data.detective_name + " checked > " + record.data.detective_name
										});
										
									});
														
								}}
							
						});
	
}

var InsureDiskObject = new InsureDisk() ; 


</script>