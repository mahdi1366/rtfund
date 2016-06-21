<script>
//---------------------------
// programmer:	Mahdipour
// create Date:		92.07
//---------------------------   

    Job.prototype = {
        TabID : '<?= $_REQUEST["ExtTabID"]?>',
        address_prefix : "<?= $js_prefix_address?>",
        get : function(elementID){
            return findChild(this.TabID, elementID);
        }
    };
function Job()
    {
        
     return ;            
            
    }

var JobObject = new Job();


Job.prototype.AddJob = function()
{
	var modelClass = this.grid.getStore().model;
	var record = new modelClass({
                                        job_id: "",
                                        title: null , 
                                        PersonType : 101 
                                });

	this.grid.plugins[0].cancelEdit();
	this.grid.getStore().insert(0, record);
	this.grid.plugins[0].startEdit(0, 0);    
}

Job.prototype.editJob = function(store,record,op)
{
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix + '../data/jobs.data.php?task=SaveJob'  ,
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
                                        JobObject.grid.getStore().load();
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

Job.opRender = function(value, p, record)
{
    
    return   "<div  title='حذف' class='remove' onclick='JobObject.deleteJob();' " +
			 "style='float:left;background-repeat:no-repeat;background-position:center;" +
			 "cursor:pointer;width:50%;height:16'></div>" ;
}

Job.prototype.deleteJob = function()
{
	if(!confirm("آیا از حذف اطمینان دارید؟"))
		return;
	
	var record = this.grid.getSelectionModel().getLastSelected();
	
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();


	Ext.Ajax.request({
		url: this.address_prefix + '../data/jobs.data.php?task=removeJob',
		params:{
			jid: record.data.job_id  
		},
		method: 'POST',
        success: function(response,option){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				alert("حذف با موفقیت انجام شد.");
				JobObject.grid.getStore().load();
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












