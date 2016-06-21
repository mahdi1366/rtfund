<script>
//---------------------------
// programmer:	Mahdipour
// create Date:		91.05.03
//---------------------------   

    CostException.prototype = {
        TabID : '<?= $_REQUEST["ExtTabID"]?>',
        address_prefix : "<?= $js_prefix_address?>", 
        newMode : 0 ,
        get : function(elementID){
            return findChild(this.TabID, elementID);
        }
    };
function CostException()
    {
     
        var store1 = <?= dataReader::MakeStoreObject($js_prefix_address . "../data/salary_item_type.data.php?task=searchSubtractItem","'salary_item_type_id','full_title','person_type_title','person_type'") ?> ; 
        
        this.subItemCombo = new Ext.form.ComboBox({
		store: store1 , 
		emptyText:'جستجوی قلم ...',
		typeAhead: false,
		listConfig :{
			loadingText: 'در حال جستجو...'
		},
		pageSize:10,
		width: 200,

		tpl: new Ext.XTemplate(
			'<table cellspacing="0" width="100%"><tr class="x-grid3-header">'
				,'<td height="23px">کد پرسنلی</td>'
				,'<td>کد شخص</td>'
				,'<td>نام</td>'
				,'</tr>',
			'<tpl for=".">',
			'<tr class="x-boundlist-item" style="border-left:0;border-right:0">'
				,'<td style="border-left:0;border-right:0" class="search-item">{salary_item_type_id}</td>'
				,'<td style="border-left:0;border-right:0" class="search-item">{full_title}</td>'
				,'<td style="border-left:0;border-right:0" class="search-item">{person_type_title}</td>'
				,'</tr>',
			'</tpl>'
			,'</table>')

		,listeners : {
			Select: function(combo, records){
				this.setValue(records[0].data.salary_item_type_id + " " + records[0].data.full_title);
				CostExceptionObject.grid.getStore().getAt(0).data.SalaryItemTypeID = records[0].data.salary_item_type_id;
                                CostExceptionObject.grid.getStore().getAt(0).data.PersonType = records[0].data.person_type;
				this.collapse();
			}
		}
	});
           
            
    }

var CostExceptionObject = new CostException();

CostException.opRender = function(value, p, record)
{
    
    return   "<div  title='حذف اطلاعات' class='remove' onclick='CostExceptionObject.deleteCE();' " +
			 "style='float:left;background-repeat:no-repeat;background-position:center;" +
			 "cursor:pointer;width:50%;height:16'></div>" ;
}

CostException.prototype.AddCostException = function()
{
        var modelClass = this.grid.getStore().model;
	var record = new modelClass({
		SalaryItemTypeID: "",
		PersonType: null , 
                CostCenterID : null 
                
	});       
         this.newMode  = 1 ;
	this.grid.plugins[0].cancelEdit();
	this.grid.getStore().insert(0, record);
	this.grid.plugins[0].startEdit(0, 0);    
}

CostException.prototype.editCostException = function(store,record,op)
{
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix + '../data/cost_center_exception.data.php?task=SaveCostException&newMode=' + this.newMode  ,
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
                                        CostExceptionObject.grid.getStore().load();
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

CostException.prototype.deleteCE = function()
{
	if(!confirm("آیا از حذف اطمینان دارید؟"))
		return;
	
	var record = this.grid.getSelectionModel().getLastSelected();
	
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();


	Ext.Ajax.request({
		url: this.address_prefix + '../data/cost_center_exception.data.php?task=removeCostException',
		params:{
			sid: record.data.SalaryItemTypeID , 
                        pty: record.data.PersonType , 
                        cid: record.data.CostCenterID
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












