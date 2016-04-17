<?php
//-----------------------------
//	Programmer	: Fatemipour
//	Date		: 94.08
//-----------------------------
ini_set("display_errors", "On");
//error_reporting(E_ALL); 
require_once '../../header.inc.php';
require_once '../../global/CNTconfig.class.php';
?>
<br>
<center>
    <div id="NewTplDIV"></div>    
    <div id='NewTplEditorDIV'>
       <!-- <textarea id='NewTplEditor' style='width:780px; height:590px' rows='100' > </textarea> -->
    </div>
</center>
<script type='text/javascript'>
    NewTemplate.prototype = {
        TabID: '<?= $_REQUEST["ExtTabID"] ?>',
        TplItemSeperator: '<?= CNTconfig::TplItemSeperator ?>',
        address_prefix: "<?= $js_prefix_address ?>",
        get: function (elementID) {
            return findChild(this.TabID, elementID);
        }
    };

    function NewTemplate() {
        this.HtmlEdForm = new Ext.form.field.HtmlEditor({
            width: 750,
            height: 300,
            hasfocus: true
                    /*,
                     defaults: { // defaults are applied to items, not the container
                     autoScroll: true,
                     align : 'right'
                     }*/
                    /*style: {
                     width: '95%',
                     direction : 'rtl'
                     }*/
        });
        /* { xtype : 'htmleditor',width : 750, height : 300 };*/
        this.NewTplForm = new Ext.form.Panel({
            renderTo: this.get('NewTplDIV'),
            width: 760,
            frame: true,
            fieldDefaults: {
                labelWidth: 120
            },
            layout: {
                type: 'table',
                columns: 1
            },
            items: [{
                    xtype: 'hidden',
                    itemId: 'TplId',
                    value: 0
                }, {
                    xtype: 'textfield',
                    fieldLabel: 'عنوان',
                    itemId: 'TplTitle',
                    width: 400,
                }, {
                    xtype: 'combo',
                    fieldLabel: 'انتخاب آیتم',
                    store: new Ext.data.Store({
                        pageSize: 10,
                        proxy: {
                            type: 'jsonp',
                            url: this.address_prefix + '../data/templates.data.php?task=selectTemplateItems',
                            reader: {root: 'rows', totalProperty: 'totalCount'}
                        },
                        fields: ['TplItemId', 'TplItemName', 'TplItemType']
                    }),
                    displayField: 'TplItemName',
                    valueField: "TplItemId",
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
                            CKEDITOR.instances.NewTplEditor.insertText(' ' + NewTemplateObj.TplItemSeperator + records[0].data.TplItemId + '--' + records[0].data.TplItemName + NewTemplateObj.TplItemSeperator + ' ');
                        }
                    }
                }/*, this.HtmlEdForm*/],
            buttons: [{
                    iconCls: "save",
                    text: " ذخیره",
                    handler: function () {
                        Ext.Ajax.request({
                            url: NewTemplateObj.address_prefix + "../data/templates.data.php",
                            method: "POST",
                            params: {
                                task: 'SaveTpl',
                                TplContent: CKEDITOR.instances.NewTplEditor.getData(), //NewTemplateObj.HtmlEdForm.getValue(),
                                TplTitle: NewTemplateObj.NewTplForm.getComponent('TplTitle').getValue(),
                                TplId: NewTemplateObj.NewTplForm.getComponent('TplId').getValue()
                            },
                            success: function (response) {
                                var sd = Ext.decode(response.responseText);
                                if (sd.success) {
                                    NewTemplateObj.NewTplForm.getComponent('TplId').setValue(sd.data);
                                    Ext.MessageBox.alert('', 'با موفقیت ذخیره شد');
                                } else {
                                    Ext.MessageBox.alert('خطا', sd.data);
                                }
                            }
                        });
                    }
                }]
        });

        this.TplEditorForm = new Ext.form.Panel({
            renderTo: this.get('NewTplEditorDIV'),
            width: 1000,
            height: 600,
            frame: true,
            items: [{                    
                    html: "<textarea id='NewTplEditor' style='width:780px; height:590px' rows='100' > </textarea> "
                }]
        });
    }



    NewTemplateObj = new NewTemplate();
    /* if (CKEDITOR.instances.NewTplEditor) {
     CKEDITOR.instances.NewTplEditor.destroy();
     }*/
    CKEDITOR.replace('NewTplEditor', {width: 780, heigth: 490}, {});

</script>