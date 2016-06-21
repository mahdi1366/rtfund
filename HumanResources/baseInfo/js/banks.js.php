<script>
//---------------------------
// programmer:	Mahdipour
// create Date:		91.06
//---------------------------   

    bank.prototype = {
        TabID : '<?= $_REQUEST["ExtTabID"]?>',
        address_prefix : "<?= $js_prefix_address?>",
        get : function(elementID){
            return findChild(this.TabID, elementID);
        }
    };
function bank()
    {
        
     return ;            
            
    }

var bankObject = new bank();


bank.prototype.AddBnak = function()
{
        var modelClass = this.grid.getStore().model;
	var record = new modelClass({
                                        bank_id: "",
                                        name: null , 
                                        branch_code : null ,
                                        type : null 
                                    });

	this.grid.plugins[0].cancelEdit();
	this.grid.getStore().insert(0, record);
	this.grid.plugins[0].startEdit(0, 0);    
}

bank.prototype.editBank = function(store,record,op)
{
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix + '../data/banks.data.php?task=SaveBank'  ,
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
                                        bankObject.grid.getStore().load();
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

bank.opRender = function(value, p, record)
{
    
    return   "<div  title='حذف' class='remove' onclick='bankObject.deletebank();' " +
			 "style='float:left;background-repeat:no-repeat;background-position:center;" +
			 "cursor:pointer;width:50%;height:16'></div>" ;
}

bank.prototype.deletebank = function()
{
	if(!confirm("آیا از حذف اطمینان دارید؟"))
		return;
	
	var record = this.grid.getSelectionModel().getLastSelected();
	
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix + '../data/banks.data.php?task=removebank',
		params:{
			bid: record.data.bank_id  
		},
		method: 'POST',
        success: function(response,option){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				alert("حذف با موفقیت انجام شد.");
				bankObject.grid.getStore().load();
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












