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

        if (record.data.StatusCode == this.ContractStatus_Raw) {
            op_menu.add({text: ' ویرایش', iconCls: 'edit',
                handler: function () {
                    ManageContractsObj.Edit(record.data.CntId, record.data.TplId);
                }});
        }

        op_menu.add({text: ' چاپ', iconCls: 'print',
            handler: function () {
                window.open(this.address_prefix + '../../print/contract.php?CntId=' + record.data.CntId);
            }});
         
        op_menu.add({text: ' پیوست مدارک', iconCls: 'list',
            handler: function () {
                ManageContractsObj.UploadDocs(false);
            }});
        
        op_menu.showAt(e.pageX - 120, e.pageY);
    }

    ManageContracts.prototype.Edit = function (CntId, TplId)
    {        
        framework.OpenPage(ManageContractsObj.address_prefix + 'NewContract.php?CntId=' + CntId + '&TplId=' + TplId, 'ویرایش قرارداد');
    }


    ManageContracts.prototype.UploadDocs = function (readOnly) {
        CntId = ManageContractsObj.grid.getSelectionModel().getLastSelected().data.CntId;
        if (!this.UploadDocsWin)
        {
            this.UploadDocsWin = new Ext.window.Window({
                title: 'پیوست مدارک',
                modal: true,
                autoScroll: true,
                width: 662,
                height: 450,
                closeAction: "hide",
                loader: {
                    url: this.address_prefix + "../ui/documents.php",
                    scripts: true
                },
                buttons: [{
                        text: "بازگشت",
                        iconCls: "undo",
                        handler: function () {
                            this.up('window').hide();
                        }
                    }]
            });
            Ext.getCmp(this.TabID).add(this.UploadDocsWin);
        }
        this.UploadDocsWin.show();
        this.UploadDocsWin.center();
        this.UploadDocsWin.loader.load({
            params: {
                parentObj: "ManageContractsObj.UploadDocsWin",                
                readOnly: readOnly ? "true" : "false",
                ObjectType : 'CONTRACT' , ObjectID : CntId                
            }
        });
    }
</script>