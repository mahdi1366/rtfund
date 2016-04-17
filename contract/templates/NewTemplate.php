<?php
//-----------------------------
//	Programmer	: Fatemipour
//	Date		: 94.08
//-----------------------------
require_once '../header.inc.php';
require_once '../global/CNTconfig.class.php';

if (!empty($_REQUEST['TplId']))
    $TplId = $_REQUEST['TplId'];
else
    $TplId = 0;
?>

<br>
<center>
    <div id="NewTplDIV"></div>    
    <div id='NewTplEditorDIV'>
        <div id='NewTplEditor'></div>       
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
        });
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
                    value: <?= $TplId ?>
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
                            CKEDITOR.instances.NewTplEditor.editor.focus();
                            //CKEDITOR.instances.NewTplEditor.focus();
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
        /* this.TplEditorForm = new Ext.form.Panel({
         renderTo: this.get('NewTplEditorDIV'),
         width: 780,
         height: 600,
         frame: true,
         items: [{
         html: "<div id='NewTplEditor'></div>"
         }]
         });*/
    }

	if ( CKEDITOR.env.ie && CKEDITOR.env.version < 9 )
		CKEDITOR.tools.enableHtml5Elements( document );

    NewTemplateObj = new NewTemplate();
    CKEDITOR.config.width = 780;
    CKEDITOR.config.height = 270;
    CKEDITOR.config.autoGrow_minHeight = 200;
    CKEDITOR.replace('NewTplEditor');

    CKEDITOR.on('instanceReady', function (ev) {
        if (NewTemplateObj.NewTplForm.getComponent('TplId').getValue() > 0) {            
            Ext.Ajax.request({
                url: NewTemplateObj.address_prefix + "../data/templates.data.php",
                method: "POST",
                params: {
                    task: 'GetTplTitle',                    
                    TplId: NewTemplateObj.NewTplForm.getComponent('TplId').getValue()
                },
                success: function (response) {
                    var sd = Ext.decode(response.responseText);
                    if (sd.success) {
                        NewTemplateObj.NewTplForm.getComponent('TplTitle').setValue(sd.data);
                    } else {
                        Ext.MessageBox.alert('خطا', sd.data);
                    }
                }
            });
            Ext.Ajax.request({
                url: NewTemplateObj.address_prefix + "../data/templates.data.php",
                method: "POST",
                params: {
                    task: 'GetTplContentToEdit',                 
                    TplId: NewTemplateObj.NewTplForm.getComponent('TplId').getValue()
                },
                success: function (response) {
                    var sd = Ext.decode(response.responseText);
                    if (sd.success) {
                        ev.editor.setData(sd.data);
                    } else {
                        Ext.MessageBox.alert('خطا', sd.data);
                    }
                }
            });

        }
        ev.editor.focus();
    });
    /*CKEDITOR.config.extraPlugins = 'onchange';
     CKEDITOR.on('onchange', function (ev) {
     alert('11');
     ev.editor.focus();
     });*/

</script>