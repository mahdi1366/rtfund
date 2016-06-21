<script>
//---------------------------
// programmer:	Mahdipour
// create Date:		92.10
//---------------------------   

priority.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",
	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};
function priority()
    {
        
     return ;            
            
    }

var priorityObject = new priority();


priority.prototype.AddPriority = function()
{
	var modelClass = this.grid.getStore().model;
	var record = new modelClass({
									PriorityID: "",
									PriorityTitle: null                                        
								});

	this.grid.plugins[0].cancelEdit();
	this.grid.getStore().insert(0, record);
	this.grid.plugins[0].startEdit(0, 0);    
}

priority.prototype.editPriority = function(store,record,op)
{
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix + '../data/priority.data.php?task=SavePriority'  ,
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
                                        priorityObject.grid.getStore().load();
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

priority.opRender = function(value, p, record)
{
    
    return   "<div  title='حذف' class='remove' onclick='priorityObject.deletePriority();' " +
			 "style='float:left;background-repeat:no-repeat;background-position:center;" +
			 "cursor:pointer;width:50%;height:16'></div>" ;
}

priority.prototype.deletePriority = function()
{
	if(!confirm("آیا از حذف اطمینان دارید؟"))
		return;
	
	var record = this.grid.getSelectionModel().getLastSelected();
	
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix + '../data/priority.data.php?task=remove',
		params:{
			pid: record.data.PriorityID  
		},
		method: 'POST',
        success: function(response,option){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				alert("حذف با موفقیت انجام شد.");
				priorityObject.grid.getStore().load();
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












