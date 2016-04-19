    //-----------------------------
    //	Programmer	: Fatemipour
    //	Date		: 94.08
    //-----------------------------

    ReceivedContracts.prototype = {
        TabID: '<?= $_REQUEST["ExtTabID"] ?>',
        address_prefix: "<?= $js_prefix_address ?>",
        ContractStatus_Raw: <?= CNTconfig::ContractStatus_Raw ?>,
        get: function (elementID) {
            return findChild(this.TabID, elementID);
        }
    }
    function ReceivedContracts() {

    }

    ReceivedContracts.prototype.OperationRender = function () {
        return  "<div title='عملیات' class='setting' onclick='ReceivedContractsObj.OperationMenu(event);' " +
                "style='background-repeat:no-repeat;background-position:center;" +
                "cursor:pointer;height:16'></div>";
    }

    ReceivedContracts.prototype.OperationMenu = function (e)
    {
        var record = this.grid.getSelectionModel().getLastSelected();
        var op_menu = new Ext.menu.Menu();

        if (record.data.StatusCode == this.ContractStatus_Raw) {
            op_menu.add({text: ' ویرایش', iconCls: 'edit',
                handler: function () {
                    ReceivedContractsObj.Edit(record.data.CntID, record.data.TemplateID);
                }});
        }

        op_menu.add({text: ' پیوست مدارک', iconCls: 'list',
            handler: function () {
                ReceivedContractsObj.UploadDocs(false);
            }});

        op_menu.add({text: ' تایید', iconCls: 'tick',
            handler: function () {
                ReceivedContractsObj.ConfirmContract(record.data.CntID);
            }});

        op_menu.add({text: ' چاپ', iconCls: 'print',
            handler: function () {
                window.open(this.address_prefix + '../../print/contract.php?CntID=' + record.data.CntID);
            }});

        op_menu.showAt(e.pageX - 120, e.pageY);
    }

    ReceivedContracts.prototype.Edit = function (CntID, TemplateID)
    {
        framework.OpenPage(ReceivedContractsObj.address_prefix + 'NewContract.php?CntID=' + CntID + '&TemplateID=' + TemplateID, 'ویرایش قرارداد');
    }

    ReceivedContracts.prototype.ConfirmContract = function (CntID)
    {
        Ext.Ajax.request({
            url: ReceivedContractsObj.address_prefix + '../data/contract.data.php?task=ConfirmRecContract',
            params: {
                CntID: CntID
            },
            method: 'POST',
            success: function (res) {
                var sd = Ext.decode(res.responseText);
                if (!sd.success) {
                    Ext.MessageBox.alert('', sd.data);
                    return;
                }
                ReceivedContractsObj.grid.getStore().load();
            }
        });
    }


    ReceivedContracts.prototype.UploadDocs = function (readOnly) {
        CntID = ReceivedContractsObj.grid.getSelectionModel().getLastSelected().data.CntID;
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
                parentObj: "ReceivedContractsObj.UploadDocsWin",
                readOnly: readOnly ? "true" : "false",
                ObjectType: 'CONTRACT', ObjectID: CntID
            }
        });
    }


</script>