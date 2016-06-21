<script>
	//---------------------------
	// programmer:	Mahdipour
	// create Date:		91.05.03
	//---------------------------   

    SubInfo.prototype = {
        TabID : '<?= $_REQUEST["ExtTabID"] ?>',
        address_prefix : "<?= $js_prefix_address ?>",
        newMode : 0 ,
        get : function(elementID){
            return findChild(this.TabID, elementID);
        }
    };

	function SubInfo()
    {
     
        var store1 = <?= dataReader::MakeStoreObject($js_prefix_address . "../data/salary_item_type.data.php?task=searchSubtractItem", 
				"'salary_item_type_id','full_title','person_type_title','person_type'") ?> ; 
        
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
			,'<td style="border-left:0;border-right:0" class="search-item">{SalaryItemTypeID}</td>'
			,'<td style="border-left:0;border-right:0" class="search-item">{full_title}</td>'
			,'<td style="border-left:0;border-right:0" class="search-item">{person_type_title}</td>'
			,'</tr>',
			'</tpl>'
			,'</table>')

			,listeners : {
				Select: function(combo, records){
					this.setValue(records[0].data.salary_item_type_id + " : " + records[0].data.full_title);
					SubInfoObject.grid.getStore().getAt(0).data.SalaryItemTypeID = records[0].data.salary_item_type_id;
					SubInfoObject.grid.getStore().getAt(0).data.PersonType = records[0].data.person_type;
					this.collapse();
				}
			}
		});
           
            
    }

	var SubInfoObject = new SubInfo();

	SubInfo.prototype.AddSubInfo = function()
	{
        var modelClass = this.grid.getStore().model;
		var record = new modelClass({
			SalaryItemTypeID: "",
			PersonType: null , 
			description : null ,
			BeneficiaryID : null , 
			order : null , 
			FromDate : null , 
			ToDate : null 
			    
		});
        this.newMode = 1 ; 
		this.grid.plugins[0].cancelEdit();
		this.grid.getStore().insert(0, record);
		this.grid.plugins[0].startEdit(0, 0);    
	}

	SubInfo.prototype.editSubInfo = function(store,record,op)
	{
		mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
		mask.show();

		Ext.Ajax.request({
			url: this.address_prefix + '../data/subtract_info.data.php?task=SaveSubInfo&newMode=' + this.newMode  ,
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
					SubInfoObject.grid.getStore().load();
					return;
				}
				else
				{  
					ShowExceptions(SubInfoObject.get("ErrorDiv"),st.data);
				}		
		
			},                
			failure: function(){}
		});
	}
        
        SubInfo.opRender = function(value, p, record)
        {

            return   "<div  title='حذف اطلاعات' class='remove' onclick='SubInfoObject.deleteSI();' " +
                                "style='float:left;background-repeat:no-repeat;background-position:center;" +
                                "cursor:pointer;width:50%;height:16'></div>" ;
        }
        
        SubInfo.prototype.deleteSI = function()
        {
                if(!confirm("آیا از حذف اطمینان دارید؟"))
                        return;

                var record = this.grid.getSelectionModel().getLastSelected();

                mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
                mask.show();


                Ext.Ajax.request({
                        url: this.address_prefix + '../data/subtract_info.data.php?task=removeSI',
                        params:{
                                sid: record.data.SalaryItemTypeID , 
                                pty: record.data.PersonType 
                        },
                        method: 'POST',
                success: function(response,option){
                                mask.hide();
                                var st = Ext.decode(response.responseText);
                                if(st.success)
                                {
                                        alert("حذف با موفقیت انجام شد.");
                                        SubInfoObject.grid.getStore().load();
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












