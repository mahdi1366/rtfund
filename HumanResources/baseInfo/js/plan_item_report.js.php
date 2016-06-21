<script>
//---------------------------
// programmer:	Mahdipour
// create Date:		92.03
//---------------------------   

    PlanItemReport.prototype = {
        TabID : '<?= $_REQUEST["ExtTabID"]?>',
        address_prefix : "<?= $js_prefix_address?>", 
        get : function(elementID){
            return findChild(this.TabID, elementID);
        }
    };
    function PlanItemReport()
    {
        return ;    
    }

    var PlanItemReportObject = new PlanItemReport();

	PlanItemReport.opRender = function(value, p, record)
	{

		return   "<div  title='حذف اطلاعات' class='remove' onclick='PlanItemReportObject.deletePIR();' " +
				"style='float:left;background-repeat:no-repeat;background-position:center;" +
				"cursor:pointer;width:50%;height:16'></div>" ;
	}

	PlanItemReport.prototype.AddPIR = function()
	{
		var modelClass = this.grid.getStore().model;
		var record = new modelClass({
			PlanItemID: "",
			PlanItemTitle: null , 
			PayYear : null ,
			PayMonth : null , 
			PayValue : null ,
			RelatedItem : null , 
			CostCenterID : null , 
			PersonType : null
		});

		this.grid.plugins[0].cancelEdit();
		this.grid.getStore().insert(0, record);
		this.grid.plugins[0].startEdit(0, 0);    
	}

PlanItemReport.prototype.editPIR = function(store,record,op)
{
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix + '../data/plan_item_report.data.php?task=SavePIR' ,
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
						PlanItemReportObject.grid.getStore().load();
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

PlanItemReport.prototype.deletePIR = function()
{
	if(!confirm("آیا از حذف اطمینان دارید؟"))
		return;
	
	var record = this.grid.getSelectionModel().getLastSelected();
	
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix + '../data/plan_item_report.data.php?task=removePIR',
		params:{
			pid: record.data.PlanItemID                        
		},
		method: 'POST',
        success: function(response,option){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				alert("حذف با موفقیت انجام شد.");
				PlanItemReportObject.grid.getStore().load();
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












