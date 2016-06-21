<script>
//---------------------------
// programmer:	Mahdipour
// create Date:		91.05.12
//---------------------------   

    CostCenter.prototype = {
        TabID : '<?= $_REQUEST["ExtTabID"]?>',
        address_prefix : "<?= $js_prefix_address?>",
        get : function(elementID){
            return findChild(this.TabID, elementID);
        }
    };
function CostCenter()
    {
	this.formPanel = new Ext.form.Panel({
			applyTo: this.get("mainpanel"),
			layout: {
                                type:"table",
                                columns:2
                            },
			collapsible: true,
			frame: true,
			title: 'ثبت مرکز هزینه',
			bodyPadding: '2 2 0',
			width:780,
			fieldDefaults: {
				msgTarget: 'side',
				labelWidth: 180	 
			},
			defaultType: 'textfield',
			items: [{	xtype:'displayfield',
					fieldLabel: 'کد مرکز هزینه',
					name: 'costcenterid' 	,itemId : 'costcenterid'								
				},{
					fieldLabel: 'عنوان مرکز هزینه ',
					name: 'title',
					labelWidth:100,
					width:300,
					allowBlank : false
				},{
					fieldLabel: 'شماره کارگاه روز مزد بیمه ای ',
					name: 'daily_work_place_no',
					width:300,
					allowBlank : false
				},{
					fieldLabel: 'نام کارگاه',
					labelWidth:100,
					width:300,
					name: 'detective_name',
					allowBlank : false
				},{
					fieldLabel: 'نام کارفرما',width:400,
					name: 'employer_name',
					allowBlank : false
				},{
					fieldLabel: 'آدرس کارگاه',
					labelWidth:100,
					width:360,
					name: 'detective_address',					
					allowBlank : false
				},{
					fieldLabel: 'نام شعبه بیمه تامین اجتماعی',
					name: 'collective_security_branch',
					allowBlank : false,
					colspan : 2 ,
					width:400
				},{
					xtype:'textareafield',
					fieldLabel: 'شرح',
					name: 'description',
					colspan:2,
					width:450
				},{
				    xtype : "hidden",
				    name : 'cost_center_id'
				  }],
				buttons: [{
					text : "ذخیره",
					iconCls : "save",
					handler : function(){
						CostCenterObject.formPanel.getForm().submit({
							clientValidation: true,
							url:  'baseInfo/data/cost_centers.data.php?task=SaveCostCenter',
							method : "POST",
							success : function(form,action){
								if(action.result.success)
								{
									CostCenterObject.grid.getStore().load();
								}
								else
								{
									alert(action.result.data);
								}
							}
						});

						CostCenterObject.formPanel.hide();

					}
				},{
					text : "انصراف",
					iconCls : "undo",
					handler : function(){
						CostCenterObject.formPanel.hide();
					}
				}]

		});
		
		this.formPanel.hide();
        
     return ;            
            
    }

var CostCenterObject = new CostCenter();

CostCenter.prototype.AddCostCenter = function()
{
        this.formPanel.getForm().reset();
	this.formPanel.show();
	this.formPanel.center();
}

CostCenter.prototype.editCostCenter = function(store,record,op)
{
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix + '../data/cost_centers.data.php?task=SaveCostCenter'  ,
		params:{
			record: Ext.encode(record.data)
		},
		method: 'POST',
                                     
                        success: function(response,op){
                                mask.hide();
                                var st = Ext.decode(response.responseText);

                                if(st.success === "true" )
                                { 
                                        alert("ذخیره سازی با موفقیت انجام گردید.");
                                        CostCenterObject.grid.getStore().load();
                                        return;
                                }
                                else
                                {  
                                        ShowExceptions("ErrorDiv",st.data);
                                }		
		
		},                
		failure: function(){}
	});
}

CostCenter.opRender = function(value, p, record)
{
	var st = "";
	st += "<div  title='ویرایش اطلاعات' class='edit' onclick='CostCenterObject.editCC();' " +
			"style='float:right;background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;width:50%;height:16'></div>";
	
	st +=	"<div  title='حذف اطلاعات' class='remove' onclick='CostCenterObject.deleteCC();' " +
			"style='float:left;background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;width:50%;height:16'></div>";
	
	return st;
}

CostCenter.prototype.editCC = function()
{    
	this.formPanel.show();
	var record = this.grid.getSelectionModel().getLastSelected();
	this.formPanel.loadRecord(record);
	this.formPanel.down('[itemId=costcenterid]').setValue(record.data.cost_center_id) ; 
	   	
}	

CostCenter.prototype.deleteCC = function()
{
	if(!confirm("آیا از حذف اطمینان دارید؟"))
		return;
	
	var record = this.grid.getSelectionModel().getLastSelected();
	
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();


	Ext.Ajax.request({
		url: this.address_prefix + '../data/cost_centers.data.php?task=removeCC',
		params:{
			cid: record.data.cost_center_id
		},
		method: 'POST',
        success: function(response,option){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				alert("حذف با موفقیت انجام شد.");
				CostExceptionObject.grid.getStore().load();
			}
			else
			{
				alert(st.data);
			}
		},
		failure: function(){}		
	});
}

</script>












