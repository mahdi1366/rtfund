<script type="text/javascript">
    //-----------------------------
    //	Programmer	: Fatemipour
    //	Date		: 94.08
    //-----------------------------

    ManageContracts.prototype = {
        TabID: '<?= $_REQUEST["ExtTabID"] ?>',
        address_prefix: "<?= $js_prefix_address ?>",
        ContractStatus_Raw: <?= CNTconfig::ContractStatus_Raw ?>,
        get: function (elementID) {
            return findChild(this.TabID, elementID);
        }
    }
    
	function ManageContracts() {

    }

    ManageContracts.prototype.OperationRender = function () {
		
        return  "<div title='عملیات' class='setting' onclick='ManageContractsObj.OperationMenu(event);' " +
                "style='background-repeat:no-repeat;background-position:center;" +
                "cursor:pointer;height:16'></div>";
    }

    ManageContracts.prototype.OperationMenu = function (e)
    {
        var record = this.grid.getSelectionModel().getLastSelected();
        var op_menu = new Ext.menu.Menu();

        op_menu.add({text: ' ویرایش', iconCls: 'edit',
                handler: function () {
                    ManageContractsObj.Edit(record.data.ContractID, record.data.TemplateID);
                }});
			
		op_menu.add({text: ' حذف', iconCls: 'remove',
			handler: function () {
				ManageContractsObj.RemoveContract(record.data.ContractID);
			}});
        
        op_menu.add({text: ' چاپ', iconCls: 'print',
            handler: function () {
                window.open(ManageContractsObj.address_prefix + 'PrintContract.php?ContractID=' + record.data.ContractID);
            }});
         
        op_menu.add({text: ' پیوست مدارک', iconCls: 'list',
            handler: function () {
                ManageContractsObj.UploadDocs(false);
            }});
        
        op_menu.showAt(e.pageX - 120, e.pageY);
    }

    ManageContracts.prototype.Edit = function (ContractID, TemplateID)
    {        
        framework.OpenPage(this.address_prefix + 'NewContract.php?ContractID=' + ContractID + '&TemplateID=' + TemplateID, 'ویرایش قرارداد');
    }

    ManageContracts.prototype.AddContract = function () {
	
        framework.OpenPage(this.address_prefix + "NewContract.php", "ثبت قرارداد");
    }

	ManageContracts.prototype.RemoveContract = function () {
		
		mask = new Ext.LoadMask(this.grid, {msg:'در حال ذخيره سازي...'});
		mask.show();
		
		Ext.Ajax.request({
		url: this.address_prefix + 'contract.data.php?task=DeleteContract',
		params: {                
			ContractID: this.grid.getSelectionModel().getLastSelected().data.ContractID            
		},
		method: 'POST',
		success: function (res) {
			mask.hide();
			var sd = Ext.decode(res.responseText);
			if (!sd.success) {
				if (sd.data != '')
					Ext.MessageBox.alert('', sd.data); 
				else
					Ext.MessageBox.alert('', 'خطا در اجرای عملیات');
				return;
			}
			ManageContractsObj.grid.getStore().load();
		}
	});
	}

</script>