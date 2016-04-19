<script type="text/javascript">
    //-----------------------------
    //	Programmer	: Fatemipour
    //	Date		: 94.08
    //-----------------------------

    MyContracts.prototype = {
        TabID: '<?= $_REQUEST["ExtTabID"] ?>',
        address_prefix: "<?= $js_prefix_address ?>",
        ContractStatus_Raw: <?= CNTconfig::ContractStatus_Raw ?>,
        ContractStatus_Sent: <?= CNTconfig::ContractStatus_Sent ?>,
        get: function (elementID) {
            return findChild(this.TabID, elementID);
        }
    }
    function MyContracts() {

    }

    MyContracts.prototype.OperationRender = function () {
        return  "<div title='عملیات' class='setting' onclick='MyContractsObj.OperationMenu(event);' " +
                "style='background-repeat:no-repeat;background-position:center;" +
                "cursor:pointer;height:16'></div>";
    }

    MyContracts.prototype.OperationMenu = function (e)
    {
        var record = this.grid.getSelectionModel().getLastSelected();
        var op_menu = new Ext.menu.Menu();

        if (record.data.StatusCode == this.ContractStatus_Raw) {
            op_menu.add({text: ' ویرایش', iconCls: 'edit',
                handler: function () {
                    MyContractsObj.Edit(record.data.CntID, record.data.TemplateID);
                }});
            op_menu.add({text: ' ارسال', iconCls: 'send',
                handler: function () {
                    MyContractsObj.Send(record.data.CntID, record.data.TemplateID);
                }});
        }

        op_menu.add({text: ' چاپ', iconCls: 'print',
            handler: function () {
                window.open(this.address_prefix + '../../print/contract.php?CntID=' + record.data.CntID);
            }});

        op_menu.showAt(e.pageX - 120, e.pageY);
    }

    MyContracts.prototype.Edit = function (CntID, TemplateID)
    {
        framework.OpenPage(MyContractsObj.address_prefix + 'NewContract.php?CntID=' + CntID + '&TemplateID=' + TemplateID, 'ویرایش قرارداد');
    }

    MyContracts.prototype.Send = function (CntID, TemplateID)
    {
        Ext.Ajax.request({
            url: MyContractsObj.address_prefix + "../data/contract.data.php",
            method: "POST",
            params: {
                task: 'Send',
                CntID : CntID
            },
            success: function (response) {
                var sd = Ext.decode(response.responseText);
                if (sd.success) {
                    MyContractsObj.grid.getStore().load();
                } else {
                    Ext.MessageBox.alert('خطا', sd.data);
                }
            }
        });
    }

</script>