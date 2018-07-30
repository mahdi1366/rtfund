<?php
//---------------------------
// developer:	Sh.Jafarkhani
// Date:		92.07
//---------------------------
require_once '../header.inc.php';
//................  GET ACCESS  .....................
$accessObj = FRW_access::GetAccess($_POST["MenuID"]);
//...................................................

?>
<script type="text/javascript" src="/generalUI/ext4/ux/Printer/Printer-all.js"></script>
<script type="text/javascript">
    Process.prototype = {
        TabID: '<?= $_REQUEST["ExtTabID"] ?>',
        address_prefix: "<?= $js_prefix_address ?>",

		AddAccess : <?= $accessObj->AddFlag ? "true" : "false" ?>,
		EditAccess : <?= $accessObj->EditFlag ? "true" : "false" ?>,
		RemoveAccess : <?= $accessObj->RemoveFlag ? "true" : "false" ?>,
	
        get: function (elementID) {
            return findChild(this.TabID, elementID);
        }
    };

    function Process() {

        this.infoWin = new Ext.window.Window({
            modal: true,
            title: "اطلاعات فرایندها",
            width: 600,
            closeAction: "hide",

            items: new Ext.form.Panel({
                bodyStyle: "text-align:right;padding:5px",
                frame: true,
                fieldDefaults: {
                    labelWidth: 120
                },
                items: [{
                        xtype: "numberfield",
                        name: "ProcessID",
                        itemId: "ProcessID",
                        fieldLabel: "کد",
                        hideTrigger: true
                    }, {
                        xtype: "textfield",
                        name: "ProcessTitle",
                        itemId: "ProcessTitle",
                        fieldLabel: "عنوان",
                        anchor: "100%"
                    }, {
                        xtype: "combo",
                        itemId: "EventID",
                        anchor: "100%",
                        fieldLabel: "رویداد مالی",
                        store: new Ext.data.Store({
                            fields: ['EventID', 'EventTitle'],
                            proxy: {
                                type: 'jsonp',
                                url: this.address_prefix + "baseinfo.data.php?task=GetAllEvents",
                                reader: {root: 'rows', totalProperty: 'totalCount'}
                            }
                        }),
                        tpl: new Ext.XTemplate(
                                '<table cellspacing="0" width="99%">'
                                , '<tpl for=".">'
                                , '<tr class="search-item">'
                                , '<td height="23px">{EventID}</td>'
                                , '<td>{EventTitle}</td></tr>'
                                , '</tpl>'
                                , '</table>'),
                        listConfig: {
                            loadingText: 'در حال جستجو...',
                            emptyText: 'فاقد اطلاعات',
                            itemCls: "search-item"
                        },
                        name: 'EventID',
                        valueField: 'EventID',
                        displayField: 'EventTitle',
                        typeAhead: false,
                        pageSize: 20,
                        queryDelay: 0
                    }, {
                        xtype: "textfield",
                        fieldLabel: "توضیحات",
                        name: "description",
                        itemId: "description",
                        anchor: "100%"
                    }, {
                        xtype: "hidden",
                        itemId: "ParentID",
                        name: "ParentID"
                    }, {
                        xtype: "hidden",
                        itemId: "old_ProcessID",
                        name: "old_ProcessID"
                    }],
                buttons: [{
                        text: "ذخیره",
                        handler: function () {
                            ProcessObject.SaveProcess();
                        },
                        iconCls: "save"
                    }, {
                        text: "انصراف",
                        handler: function () {
                            ProcessObject.infoWin.hide();
                        },
                        iconCls: "undo"
                    }]
            })
        });
		Ext.getCmp(this.TabID).add(this.infoWin);


        this.tree = new Ext.tree.Panel({
            renderTo: this.get('tree-div'),
            frame: true,
            width: 750,
            height: 600,
            title: "فرایندها",
            plugins: [new Ext.tree.Search()],
            store: new Ext.data.TreeStore({
                root: {
                    id: "source",
                    text: "فرایندها",
                    expanded: true
                },
                proxy: {
                    type: 'ajax',
                    url: this.address_prefix + "baseinfo.data.php?task=GetProcessTree"
                }
            })
        });

        this.tree.getDockedItems('toolbar[dock="top"]')[0].add({
            xtype: "button",
            iconCls: "print",
            text: "چاپ",
            handler: function () {
                Ext.ux.Printer.print(ProcessObject.tree);
            }
        }, '-', {
            xtype: "button",
            iconCls: "refresh",
            text: "بازگذاری مجدد",
            handler: function () {
                ProcessObject.tree.getStore().load();
            }
        });

        this.tree.on("itemcontextmenu", function (view, record, item, index, e)
        {
            e.stopEvent();
            e.preventDefault();
            view.select(index);
			me = ProcessObject;

            this.Menu = new Ext.menu.Menu();

            if (record.data.id == "source")
            {
				if(me.AddAccess)
                    this.Menu.add({
                        text: 'ایجاد فعالیت',
                        iconCls: 'add',
                        handler: function () {
                            ProcessObject.BeforeSaveProcess("new", "group");
                        }
                    });
            } 
			else if (record.parentNode.data.id == "source")
            {
				if(me.AddAccess)
                    this.Menu.add({
                        text: 'ایجاد چرخه',
                        iconCls: 'add',
                        handler: function () {
                            ProcessObject.BeforeSaveProcess("new", "process");
                        }
                    });
            } 
			else if (record.parentNode.parentNode.data.id == "source")
            {
				if(me.AddAccess)
                    this.Menu.add({
                        text: 'ایجاد مرحله',
                        iconCls: 'add',
                        handler: function () {
                            ProcessObject.BeforeSaveProcess("new", "process");
                        }
                    });
				if(me.EditAccess)
					this.Menu.add({
						text: 'ویرایش چرخه',
						iconCls: 'edit',
						handler: function () {
							ProcessObject.BeforeSaveProcess("edit", "process");
						}
					});
				if(me.RemoveAccess)
					this.Menu.add({
						text: 'حذف چرخه',
						iconCls: 'remove',
						handler: function () {
							ProcessObject.DeleteProcess("process");
						}
					});
            } 
			else if (record.parentNode.parentNode.parentNode.data.id == "source")
            {
				if(me.EditAccess)
					this.Menu.add({
						text: 'ویرایش مرحله',
						iconCls: 'edit',
						handler: function () {
							ProcessObject.BeforeSaveProcess("edit", "process");
						}
					});
				if(me.RemoveAccess)
					this.Menu.add({
						text: 'حذف مرحله',
						iconCls: 'remove',
						handler: function () {
							ProcessObject.DeleteProcess("process");
						}
					});
            } 
			else
            {
				if(me.AddAccess || me.EditAccess)
				{
					this.Menu.add({
						text: 'ردیف های رویداد مرحله',
						iconCls: 'list',
						handler: function () {
							var record = ProcessObject.tree.getSelectionModel().getSelection()[0];
							if (record.raw.EventID == "0" || record.raw.EventID == "" || record.raw.EventID == null)
							{
								Ext.MessageBox.alert("", "چرخه فاقد رویداد مالی می باشد");
								return;
							}
							framework.OpenPage(ProcessObject.address_prefix + "EventRows.php?EventID="
									+ record.raw.EventID, "ردیف های رویداد" + record.raw.EventID,
									{
										MenuID : "<?= $_POST["MenuID"] ?>",
										EventID: record.raw.EventID,
										EventTitle: record.raw.EventTitle

									});
						}
					});
				}
            }
            var coords = e.getXY();
            this.Menu.showAt([coords[0] - 120, coords[1]]);
        });
    }

    var ProcessObject = new Process();

    Process.prototype.BeforeSaveProcess = function (mode, obj)
    {
        var record = this.tree.getSelectionModel().getSelection()[0];

        this.infoWin.down('form').getForm().reset();
        this.infoWin.down('form').getComponent("EventID").show();

        if (mode == "new" && record.data.id == "source")
            this.infoWin.down('form').getComponent("EventID").hide();
        if (mode == "edit" && (record.data.id == "source" || record.parentNode.data.id == "source"))
            this.infoWin.down('form').getComponent("EventID").hide();

        this.infoWin.show();

        if (mode == "edit")
        {
            this.infoWin.down('form').getComponent("ParentID").setValue(record.raw.ParentID);
            this.infoWin.down('form').getComponent("ProcessTitle").setValue(record.raw.ProcessTitle);
            this.infoWin.down('form').getComponent("ProcessID").setValue(record.data.id);
            this.infoWin.down('form').getComponent("description").setValue(record.raw.description);
            this.infoWin.down('form').getComponent("old_ProcessID").setValue(record.data.id);
            //this.infoWin.down('form').getComponent("tafsilis").setValue(record.raw.tafsilis);			

            if (record.raw.EventID > 0)
                this.infoWin.down('form').getComponent("EventID").getStore().load({
                    params: {EventID: record.raw.EventID},
                    callback: function () {
                        ProcessObject.infoWin.down('form').getComponent("EventID").select(this.getAt(0));
                    }
                });
        } else {
            if (obj == "process")
                this.infoWin.down('form').getComponent("ParentID").setValue(record.data.id);
            else
                this.infoWin.down('form').getComponent("ParentID").setValue(0);
        }
    }

    Process.prototype.DeleteProcess = function () {

        var record = this.tree.getSelectionModel().getSelection()[0];

        if (record.childNodes.length != 0)
        {
            alert("ابتدا فعالیتهای این گروه را حذف کنید");
            return;
        }
        if (!confirm("آیا مایل به حذف می باشید؟"))
            return;

        mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg: 'در حال ذخيره سازي...'});
        mask.show();

        Ext.Ajax.request({
            params: {
                task: 'DeleteProcess',
                ProcessID: record.data.id
            },
            url: this.address_prefix + 'baseinfo.data.php',
            method: 'POST',
            success: function (response) {
                mask.hide();
                record.remove();
            },
            failure: function () {}
        });

    }

    Process.prototype.SaveProcess = function () {

        this.infoWin.down('form').getForm().submit({
            clientValidation: true,
            url: this.address_prefix + 'baseinfo.data.php?task=SaveProcess',
            method: "POST",

            success: function (form, action) {
         
                ProcessObject.tree.getStore().load();
                ProcessObject.infoWin.down('form').getForm().reset();
                ProcessObject.infoWin.hide();

            },
            failure: function (form, action)
            {
                alert("کد وارد شده تکراری می باشد");
            }
        });
    }

    Process.prototype.TableInfo = function () {

        if (!this.TableInfoWin)
        {
            this.TableInfoWin = new Ext.window.Window({
                title: 'اطلاعات منبع فرایند',
                modal: true,
                width: 600,
                height: 600,
                closeAction: "hide",
                loader: {
                    url: this.address_prefix + "ProcessTable.php",
                    params: {
                        parentObj: "ProcessObject.TableInfoWin"
                    },
                    method: "POST",
                    scripts: true
                }
            });
        }

        var record = this.tree.getSelectionModel().getSelection()[0];
        this.TableInfoWin.loader.load({params: {
                ProcessID: record.data.id
            }});
        this.TableInfoWin.show();
    }

</script>
</script>
<div style="margin: 10" align="center">
	<div id="tree-div"></div>
	<div id="NewWIN" class="x-hide-display"></div>
</div>

