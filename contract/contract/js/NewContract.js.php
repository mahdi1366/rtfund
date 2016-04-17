<script type="text/javascript">
    //-----------------------------
    //	Programmer	: Fatemipour
    //	Date		: 94.08
    //-----------------------------

    NewContract.prototype = {
        TabID: '<?= $_REQUEST["ExtTabID"] ?>',
        address_prefix: "<?= $js_prefix_address ?>",
        TplItemSeperator: "<?= CNTconfig::TplItemSeperator ?>",
        get: function (elementID) {
            return findChild(this.TabID, elementID);
        }
    }

    function NewContract() {
        this.MainForm = new Ext.form.Panel({
            plain: true,            
            frame: true,
            bodyPadding: 5,
            width: 700,
            fieldDefaults: {
                labelWidth: 140
            },
            renderTo: this.get("SelectTplComboDIV"),
            layout: {
                type: 'table',                
                columns :1
            },
            items: [{
                    xtype: 'textfield',
                    fieldLabel: 'توضیحات',
                    itemId: 'description',
                    width: 400,
                }, {
                    xtype: 'combo',
                    fieldLabel: 'انتخاب الگو',
                    itemId: 'SelectTemplateTplCombo',
                    store: new Ext.data.Store({
                        pageSize: 10,
                        proxy: {
                            type: 'jsonp',
                            url: this.address_prefix + '../data/templates.data.php?task=SelectTemplates',
                            reader: {root: 'rows', totalProperty: 'totalCount'}
                        },
                        fields: ['TplId', 'TplTitle', 'TplContent']
                    }),
                    displayField: 'TplTitle',
                    valueField: "TplId",
                    typeAhead: false,
                    listConfig: {
                        loadingText: 'در حال جستجو...',
                        emptyText: 'فاقد اطلاعات',
                        itemCls: "search-item"
                    },
                    pageSize: 10,
                    width: 400,
                    listeners: {
                        select: function (combo, records) {
                            this.collapse();
                            NewContractObj.ShowTplItemsForm(records[0].data.TplId);
                        }
                    }
                }]
        });


        this.ResultPanel = new Ext.form.Panel({
            renderTo: this.get('TplContentDIV'),
            width: 700,            
            layout: {
                type: 'table',
                columns : 2
            },
            //hidden : true;
            frame: true,
            items: [{
                    xtype: "hidden",
                    itemId: "CntId",
                    value: 0
                }, {
                    xtype: "container",
                    itemId: "result",
                    anchor: "90%"
                }],
            buttons: [{
                    text: "  ذخیره",
                    handler: function () {
                        NewContractObj.SaveContract();
                    },
                    iconCls: "save"
                }, {
                    text: "  مشاهده",
                    handler: function () {
                        NewContractObj.SaveContract(true);
                    },
                    iconCls: "print"
                }]
        });
    }

    NewContractObj = new NewContract();

    NewContract.prototype.SaveContract = function (print) {
        if (arguments.length > 0) {
            NewContractObj.PrintAfterSave = 1;
        } else {
            NewContractObj.PrintAfterSave = 0;
        }
        Ext.Ajax.request({
            url: NewContractObj.address_prefix + '../data/contract.data.php?task=SaveContract',
            params: {
                TplId: NewContractObj.MainForm.getComponent('SelectTemplateTplCombo').getValue(),
                description: NewContractObj.MainForm.getComponent('description').getValue(),
                CntId: NewContractObj.ResultPanel.getComponent('CntId').getValue()
            },
            form: NewContractObj.get(TplContentForm),
            method: 'POST',
            success: function (res) {
                var sd = Ext.decode(res.responseText);
                if (!sd.success) {
                    Ext.MessageBox.alert('', 'خطا در اجرای عملیات');
                    return;
                }
                NewContractObj.ResultPanel.getComponent('CntId').setValue(sd.data);
                if (NewContractObj.PrintAfterSave > 0) {
                    var CntId = NewContractObj.ResultPanel.getComponent('CntId').getValue();
                    window.open(this.address_prefix + '../../print/contract.php?CntId=' + CntId);
                    //framework.OpenPage(NewContractObj.address_prefix + '../../print/contract.php?CntId=' + CntId,'');
                } else {
                    Ext.MessageBox.alert('', 'با موفقیت ذخیره شد');
                }
            }
        });
    }

    NewContract.prototype.TplItemsStore = new Ext.data.Store({
        fields: ['TplItemId', 'TplItemName', 'TplItemType'],
        proxy: {
            type: 'jsonp',
            url: NewContractObj.address_prefix + "../data/templates.data.php?task=selectTemplateItems&All=true",
            reader: {
                root: 'rows',
                totalProperty: 'totalCount'
            }
        }
    });

    NewContract.prototype.MakeTplItemsForm = function (TplId, ValuesStore) {
        if (arguments.length > 1)
            NewContractObj.LoadValues = 1;
        else
            NewContractObj.LoadValues = 0;
        this.TplItemsStore.load({
            callback: function () {
                Ext.Ajax.request({
                    url: NewContractObj.address_prefix + '../data/templates.data.php?task=GetTplContent',
                    params: {
                        TplId: TplId
                    },
                    method: 'POST',
                    success: function (response) {
                        var sd = Ext.decode(response.responseText);
                        if (sd.success == false) {
                            return;
                        }
                        var TplContent = sd.data;
                        var regex = new RegExp(NewContractObj.TplItemSeperator);
                        var res = TplContent.split(regex);
                        if (TplContent.substring(0, 3) !== NewContractObj.TplItemSeperator) {
                            var temp = [];
                            res = temp.concat(res);
                        }
                        var counter = 0;
                        var st = '';
                        for (var i = 0; i < res.length; i++) {
                            if (i % 2 != 0) {
                                var field = '';
                                var num = NewContractObj.TplItemsStore.find('TplItemId', res[i]);

                                var fieldname = NewContractObj.TplItemsStore.getAt(num).data.TplItemName;
                                switch (NewContractObj.TplItemsStore.getAt(num).data.TplItemType) {
                                    case 'numberfield':
                                        /*field = "<input type='number' name = 'TplItem_" + counter++ + "'></input>";                                       
                                         st += field;
                                         break;*/
                                    case 'textfield':
                                        if (NewContractObj.LoadValues > 0) {
                                            var num = ValuesStore.find('TplItemId', res[i]);
                                            field = "<input type='text' value='" + ValuesStore.getAt(num).data.ItemValue + "' name = 'TplItem_" + res[i] + "'></input>";
                                        } else {
                                            field = "<input type='text' name = 'TplItem_" + /*counter++ +"_"*/ +res[i] + "'></input>";
                                        }
                                        st += field;
                                        break;
                                    case 'shdatefield':
                                        if (NewContractObj.LoadValues > 0) {
                                            var num = ValuesStore.find('TplItemId', res[i]);
                                            var MiladiDate = ValuesStore.getAt(num).data.ItemValue;
                                            var ShamsiDateItems = MiladiToShamsi(MiladiDate).split('/');
                                            field = "<input type='text' size='2' value='" + ShamsiDateItems[2] + "' name='TplItem_" + res[i] + "_DAY' maxlength='3'>" +
                                                    "/" +
                                                    "<input type='text' size='2' value='" + ShamsiDateItems[1] + "' name='TplItem_" + res[i] + "_MONTH' maxlength='2'>" +
                                                    "/" +
                                                    "<input type='text' size='2' value='" + ShamsiDateItems[0] + "' name='TplItem_" + +res[i] + "_YEAR' maxlength='4'>";
                                        } else {
                                            field = "<input type='text' size='2' name='TplItem_" + /*counter +"_"*/ +res[i] + "_DAY' maxlength='3'>" +
                                                    "/" +
                                                    "<input type='text' size='2' name='TplItem_" + /*counter +"_"*/ +res[i] + "_MONTH' maxlength='2'>" +
                                                    "/" +
                                                    "<input type='text' size='2' name='TplItem_" + /*counter +"_"*/ +res[i] + "_YEAR' maxlength='4'>";
                                        }
                                        st += field;
                                        //1
                                        //NewContractObj.get('TplContentDIV').innerHTML += "<span id = 'SPAN_" + counter + "'></span>";
                                        //NewContractObj.getShdatefield("TplItem_" + counter, "SPAN_" + counter);
                                        //2
                                        //NewContractObj.get('TplContentDIV').innerHTML += " <button onclick='NewContractObj.ShowShdateForm('1')'>Click me</button> ";
                                        //3
                                        //NewContractObj.get('TplContentDIV').innerHTML += "<span id = 'SPAN_" + counter + "'></span>";
                                        //NewContractObj.getShdatefieldBtn("TplItem_" + counter, "SPAN_" + counter);                                        
                                        counter++;
                                        break;
                                }
                            } else {
                                st += res[i];
                            }
                        }
                        NewContractObj.ResultPanel.getComponent("result").update(st);
//NewContractObj.get('TplContentDIV').innerHTML = st;
                        /*
                         for (var i = 0; i < res.length; i++) {
                         if (i % 2 != 0) {
                         var field = '';
                         var num = NewContractObj.TplItemsStore.find('TplItemId', res[i]);
                         var fieldname = NewContractObj.TplItemsStore.getAt(num).data.TplItemName;
                         switch (NewContractObj.TplItemsStore.getAt(num).data.TplItemType) {
                         case 'numberfield':
                         NewContractObj.MainForm.down('[itemId=CntContent]').add({
                         xtype: 'numberfield',
                         width: 150,
                         name: 'TplItem_' + counter
                         });
                         break;
                         case 'shdatefield':
                         NewContractObj.MainForm.down('[itemId=CntContent]').add({
                         xtype: 'shdatefield',
                         width: 150,
                         name: 'TplItem_' + counter
                         });
                         counter++;
                         break;
                         case 'textfield':
                         NewContractObj.MainForm.down('[itemId=CntContent]').add({
                         xtype: 'textfield',
                         width: 200,
                         name: 'TplItem_' + counter
                         });
                         break;
                         }
                         } else {
                         NewContractObj.MainForm.down('[itemId=CntContent]').add({
                         xtype: 'container',
                         width: 100,
                         html: res[i]
                         });
                         }
                         }*/

                        //NewContractObj.get('TplContentDIV').innerHTML = st;
                    },
                    failure: function () {
                    }
                });
            }
        })
    }

    NewContract.prototype.ShowTplItemsForm = function (TplId, ValuesStore) {
        if (arguments.length > 1)
            NewContractObj.LoadValues = 1;
        else
            NewContractObj.LoadValues = 0;

        this.TplItemsStore.load({
            callback: function () {                
                Ext.Ajax.request({
                    url: NewContractObj.address_prefix + '../data/templates.data.php?task=GetTplContent',
                    params: {
                        TplId: TplId
                    },
                    method: 'POST',
                    success: function (response) {
                        var sd = Ext.decode(response.responseText);
                        if (sd.success == false) {
                            return;
                        }
                        var TplContent = sd.data;
                        var regex = new RegExp(NewContractObj.TplItemSeperator);
                        var res = TplContent.split(regex);

                        if (TplContent.substring(0, 3) !== NewContractObj.TplItemSeperator) {
                            var temp = [];
                            res = temp.concat(res);
                        }

                        var counter = 0;
                        var st = '';
                        for (var i = 0; i < res.length; i++) {
                            if (i % 2 != 0) {
                                var field = '';
                                var num = NewContractObj.TplItemsStore.find('TplItemId', res[i]);
                                var fieldname = NewContractObj.TplItemsStore.getAt(num).data.TplItemName;

                                var TheTplItemType = NewContractObj.TplItemsStore.getAt(num).data.TplItemType;
                                NewContractObj.ResultPanel.add({
                                    xtype: TheTplItemType,
                                    itemId: 'TplItem_' + res[i],
                                    name: 'TplItem_' + res[i],
                                    fieldLabel : fieldname,
                                    hideTrigger : TheTplItemType == 'numberfield' ? true : false
                                });
                                if (NewContractObj.LoadValues > 0) {
                                    var num = ValuesStore.find('TplItemId', res[i]);                                    
                                    if (ValuesStore.getAt(num)){
                                        switch(TheTplItemType){
                                            case "shdatefield" :
                                                NewContractObj.ResultPanel.getComponent('TplItem_' + res[i]).setValue(MiladiToShamsi(ValuesStore.getAt(num).data.ItemValue));
                                                break;
                                            default : 
                                                NewContractObj.ResultPanel.getComponent('TplItem_' + res[i]).setValue(ValuesStore.getAt(num).data.ItemValue);                                    
                                        }
                                    }
                                }                                
                            } 
                        }                        
                    },
                    failure: function () {
                    }
                });
            }
        })
    }


    NewContract.prototype.getShdatefield = function (fieldname, ren) {
        return new Ext.form.SHDateField(
                {
                    name: fieldname,
                    width: 150,
                    format: 'Y/m/d',
                    renderTo: NewContractObj.get(ren)
                }
        );
    };

    NewContract.prototype.getShdatefieldBtn = function (fieldname, ren) {
        var b = Ext.create('Ext.Button', {
            text: 'Click me',
            iconCls: 'add',
            renderTo: NewContractObj.get(ren),
            handler: function () {
                alert('You clicked the button!');
            }
        });
        returnb;
    };

    NewContract.prototype.ShowShdateForm = function (thesourceid) {
        if (!this.ShdatePickWin)
            this.ShdatePickWin = new Ext.window.Window({
                renderTo: document.body,
                modal: true,
                width: 200,
                closeAction: "hide",
                items: new Ext.form.Panel({
                    plain: true,
                    border: 0,
                    bodyPadding: 5,
                    items: [{
                            xtype: "shdatefield",
                            format: 'Y/m/d',
                            width: 60,
                        }, {
                            xtype: "hidden",
                            name: "sourcefield"
                        }],
                    buttons: [{
                            text: "انتخاب",
                            iconCls: "save",
                            handler: function () {
                                alert('**');
                                //this.up('form').down('shdatefield').getValue()
                            }
                        },
                        {
                            text: "انصراف",
                            iconCls: "undo",
                            handler: function () {
                                NewContractObj.ShdatePickWin.hide();
                            }
                        }]
                })
            });
        this.ShdatePickWin.down('[name=sourcefield]').setValue(thesourceid);
        this.ShdatePickWin.show();
    }


    NewContract.prototype.LoadContractItems = function () {
        if (!this.ContractItemsStore) {
            this.ContractItemsStore = new Ext.data.Store({
                proxy: {
                    type: 'jsonp',
                    url: this.address_prefix + '../data/contract.data.php?task=GetContractItems',
                    reader: {root: 'rows', totalProperty: 'totalCount'}
                },
                fields: ['CntItemId', 'CntId', 'TplItemId', 'ItemValue']
            })
        }
        this.MainForm.getComponent('SelectTemplateTplCombo').getStore().load({
            params: {TplId: '<?= $_REQUEST['TplId'] ?>'},
            callback: function (records) {
                NewContractObj.MainForm.getComponent('SelectTemplateTplCombo').setValue(NewContractObj.MainForm.getComponent('SelectTemplateTplCombo').getStore().getAt(0).data.TplId);
            }
        });

        // TODO : load value to desciption         //description
        this.ContractItemsStore.load({
            params: {
                CntId: NewContractObj.ResultPanel.getComponent('CntId').getValue()
            },
            callback: function () {
                NewContractObj.ShowTplItemsForm('<?= $_REQUEST['TplId'] ?>', NewContractObj.ContractItemsStore);
            }
        });
    }
</script>