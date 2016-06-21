<script>
//---------------------------
// programmer:	Mahdipour
// create Date:		91.05
//---------------------------   

    University.prototype = {
        TabID : '<?= $_REQUEST["ExtTabID"]?>',
        address_prefix : "<?= $js_prefix_address?>",
        get : function(elementID){
            return findChild(this.TabID, elementID);
        }
    };
function University()
    {
        
     return ;            
            
    }

var UniversityObject = new University();


University.prototype.AddUni = function()
{
        var modelClass = this.grid.getStore().model;
	var record = new modelClass({
                                        country_id: "",
                                        university_id: null , 
                                        university_category : null ,
                                        ptitle : null , 
                                        etitle: null 
                                    });

	this.grid.plugins[0].cancelEdit();
	this.grid.getStore().insert(0, record);
	this.grid.plugins[0].startEdit(0, 0);    
}

University.prototype.editUniversity = function(store,record,op)
{
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix + '../data/universities.data.php?task=SaveUni'  ,
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
                                        UniversityObject.grid.getStore().load();
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

University.opRender = function(value, p, record)
{
    
    return   "<div  title='حذف' class='remove' onclick='UniversityObject.deleteUni();' " +
			 "style='float:left;background-repeat:no-repeat;background-position:center;" +
			 "cursor:pointer;width:50%;height:16'></div>" ;
}

University.prototype.deleteUni = function()
{
	if(!confirm("آیا از حذف اطمینان دارید؟"))
		return;
	
	var record = this.grid.getSelectionModel().getLastSelected();
	
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();


	Ext.Ajax.request({
		url: this.address_prefix + '../data/universities.data.php?task=removeUni',
		params:{
			uid: record.data.university_id  ,
                        cid: record.data.country_id
		},
		method: 'POST',
        success: function(response,option){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				alert("حذف با موفقیت انجام شد.");
				UniversityObject.grid.getStore().load();
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












