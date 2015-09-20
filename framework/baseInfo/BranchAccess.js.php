<script type="text/javascript">
    //---------------------------
    // programmer:	Jafarkhani	
    // create Date: 94.06
    //-----------------------

    BranchAccess.prototype = {
        TabID : '<?= $_REQUEST["ExtTabID"] ?>',
        address_prefix : "<?= $js_prefix_address ?>",

        get : function(elementID){
            return findChild(this.TabID, elementID);
        }
    };

    function BranchAccess()
    {
        this.PersonCombo = new Ext.form.ComboBox({
			store: new Ext.data.Store({
				proxy:{
					type: 'jsonp',
					url: this.address_prefix + '../management/framework.data.php?task=selectPersons',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				fields :  ['PersonID','fullname']
			}),
			fieldLabel : "کاربر",
			displayField: 'fullname',
			valueField : "PersonID",
			hiddenName : "PersonID",
			width : 400,
			itemId : "PersonID"
		});
		
        this.BranchCombo = new Ext.form.ComboBox({
			store :  new Ext.data.Store({
				proxy: {type: 'jsonp',
					url: this.address_prefix + 'baseInfo.data.php?task=SelectBranches',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				fields:['BranchID','BranchName'],
				autoLoad : true
			}),
			valueField : "BranchID",
			displayField : "BranchName",
			queryMode : 'local'
		});
       
    }
    
	var BranchAccessObject = new BranchAccess();
    
	BranchAccess.Save = function(store,record,op)
    {    
        mask = new Ext.LoadMask(Ext.getCmp(BranchAccessObject.TabID), {msg:'در حال ذخيره سازي...'});
        mask.show();    
        Ext.Ajax.request({
            url:  BranchAccessObject.address_prefix + 'baseInfo.data.php?task=SaveBranchAccess',
            params:{
                PersonID : record.data.PersonID,
				BranchID : record.data.BranchID
            },
            method: 'POST',
            success: function(response,option){
                mask.hide();
                BranchAccessObject.grid.getStore().load();
            },
            failure: function(){}
        });
    }
    
	BranchAccess.prototype.Add = function()
    {  
        var modelClass = this.grid.getStore().model;
        var record = new modelClass({
            BranchID:null,
            PersonID:null		
		
        });
        this.grid.plugins[0].cancelEdit();
        this.grid.getStore().insert(0, record);
        this.grid.plugins[0].startEdit(0, 0);
    }
    
	BranchAccess.deleteRender = function(value, p, record)
    {
        return  "<div  title='حذف اطلاعات' class='remove' onclick='BranchAccessObject.DeleteAccess();' " +
            "style='float:left;background-repeat:no-repeat;background-position:center;" +
            "cursor:pointer;width:50%;height:16'></div>";
    }
    
	BranchAccess.prototype.DeleteAccess = function()
    {    
        if(!confirm("آیا مایل به حذف می باشید؟"))
            return;
	
        var record = this.grid.getSelectionModel().getLastSelected();
	
        mask = new Ext.LoadMask(Ext.getCmp(BranchAccessObject.TabID), {msg:'در حال ذخيره سازي...'});
        mask.show();
        Ext.Ajax.request({
            url: this.address_prefix + 'baseInfo.data.php?task=DeleteBranchAccess',
            params:{
				PersonID : record.data.PersonID,
				BranchID : record.data.BranchID
            },
            method: 'POST',

            success: function(response,option){
                mask.hide();
                BranchAccessObject.grid.getStore().load();
            },
            failure: function(){}
        });
    }

</script>
