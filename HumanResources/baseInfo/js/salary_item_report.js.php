<script>
//---------------------------
// programmer:	Mahdipour
// create Date:		91.05.10
//---------------------------   

    SalaryItemReport.prototype = {
        TabID : '<?= $_REQUEST["ExtTabID"]?>',
        address_prefix : "<?= $js_prefix_address?>", 
        get : function(elementID){
            return findChild(this.TabID, elementID);
        }
    };
    function SalaryItemReport()
    {
        return ;    
    }

    var SalaryItemReportObject = new SalaryItemReport();

SalaryItemReport.opRender = function(value, p, record)
{
    
    return   "<div  title='حذف اطلاعات' class='remove' onclick='SalaryItemReportObject.deleteSIR();' " +
			 "style='float:left;background-repeat:no-repeat;background-position:center;" +
			 "cursor:pointer;width:50%;height:16'></div>" ;
}

SalaryItemReport.prototype.AddSIR = function()
{
        var modelClass = this.grid.getStore().model;
	var record = new modelClass({
		SalaryItemReportID: "",
		SalaryItemTitle: null , 
                description : null ,
                BeneficiaryID : null , 
                ItemValue : null ,
                ItemType : null , 
                PayYear : null , 
                PayMonth : null , 
                PersonType : null,
                state : 1 
	});
       
	this.grid.plugins[0].cancelEdit();
	this.grid.getStore().insert(0, record);
	this.grid.plugins[0].startEdit(0, 0);    
}

SalaryItemReport.prototype.editSIR = function(store,record,op)
{
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix + '../data/salary_item_report.data.php?task=SaveSIR' ,
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
                                        SalaryItemReportObject.grid.getStore().load();
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

SalaryItemReport.prototype.deleteSIR = function()
{
	if(!confirm("آیا از حذف اطمینان دارید؟"))
		return;
	
	var record = this.grid.getSelectionModel().getLastSelected();
	
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();


	Ext.Ajax.request({
		url: this.address_prefix + '../data/salary_item_report.data.php?task=removeSIR',
		params:{
			sid: record.data.SalaryItemReportID                        
		},
		method: 'POST',
        success: function(response,option){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				alert("حذف با موفقیت انجام شد.");
				SalaryItemReportObject.grid.getStore().load();
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












