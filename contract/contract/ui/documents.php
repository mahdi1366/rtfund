<?php
//-----------------------------
//	Programmer	: Fatemipour
//	Date		: 94.10
//-----------------------------
ini_set("display_errors", "On");

require_once '../../header.inc.php';
require_once '../class/UserAccess.class.php';
ini_set('display_errors', 'On'); error_reporting(E_ALL); 
if (empty($_REQUEST['ObjectType']) || empty($_REQUEST['ObjectID'])) {
    die('اطلاعات ناقص است.');
}

$dg = new sadaf_datagrid("dg", $js_prefix_address . "../data/document.data.php?task=GetAll&ObjectID=" . $_REQUEST['ObjectID'] . '&ObjectType=' . $_REQUEST['ObjectType'], "div_dg");

$dg->addColumn("", "DocId", "", true);
$dg->addColumn("", "ObjectType", "", true);
$dg->addColumn("", "ObjectID", "", true);
$dg->addColumn("", "StatusCode", "", true);
$dg->addColumn("", "IsConfirm", "", true);

$col = $dg->addColumn(" نام فایل", "RealFileName");
$col = $dg->addColumn(" نوع سند", "DocType");
$col = $dg->addColumn(" نوع فایل", "FileType");

$col = $dg->addColumn(" دانلود ", "");
$col->width = 50;
$col->renderer = "DocumentObject.DownloadRender";

if (CNT_UserAccess::IsAllowedToConfirmDocs($_REQUEST['ObjectID'], $_REQUEST['ObjectType'])) {
    $col = $dg->addColumn(" تایید ", "");
    $col->width = 50;
    $col->renderer = "DocumentObject.ConfirmRender";
}

$col = $dg->addColumn(" ویرایش ", "");
$col->width = 50;
$col->renderer = "DocumentObject.EditRender";

$col = $dg->addColumn(" حذف ", "");
$col->width = 50;
$col->renderer = "DocumentObject.DeleteRender";

$dg->width = 650;
$dg->height = 265;
$dg->title = 'لیست مدارک';
$dg->EnableRowNumber = true;
$dg->EnablePaging = false;
$grid = $dg->makeGrid_returnObjects();
?>
<center>
    <form id="mainForm">
        <div id='div_form'></div>
    </form>
    <div id='div_dg'></div>
</center>
<script>

    Document.prototype = {
        parent: <?= $_REQUEST["parentObj"] ?>,
        TabID: <?= $_REQUEST["parentObj"] . ".getEl().id" ?>,
        address_prefix: "<?= $js_prefix_address ?>",
        get: function (elementID) {
            return findChild(this.TabID, elementID);
        }
    };

    function Document() {


        this.DocForm = new Ext.form.Panel({
            renderTo: this.get('div_form'),
            itemId: "DocForm",
            width: 650,
            frame: true,
            title: "",
            fieldDefaults: {
                labelWidth: 120
            },
            layout: {
                type: 'table',
                columns: 1
            },
            items: [
                {xtype: 'hidden', name: 'DocId', itemId: 'DocId', value: ''},
                {xtype: 'hidden', name: 'ObjectType', itemId: 'ObjectType', value: '<?= $_REQUEST['ObjectType'] ?>'},
                {xtype: 'hidden', itemId: 'ObjectID', name: 'ObjectID', value: '<?= $_REQUEST['ObjectID'] ?>'},
                {xtype: 'hidden', itemId: 'StatusCode', name: 'StatusCode', value: ''},
                {
                    xtype: "textfield",
                    name: "DocType", itemId: 'DocType',
                    fieldLabel: "نوع سند", width: 400
                }, {
                    xtype: "textfield",
                    name: "FileType", width: 400, itemId: 'FileType',
                    fieldLabel: "نوع فایل"
                }, {
                    xtype: "filefield",
                    name: "DocFile", width: 400,
                    //width : 0,   
                    //style : 'margin : 5 0 0 80 ',
                    fieldLabel: "تصویر فاکتور"
                }
            ],
            buttons: [{
                    iconCls: "clear",
                    text: " پاک کردن فرم",
                    handler: function () {
                        this.up("form").getForm().reset();

                    }
                }, {
                    iconCls: "save",
                    text: "ذخیره ",
                    handler: function () {
                        //        var mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
                        //        mask.show();        
                        Ext.Ajax.request({
                            url: DocumentObject.address_prefix + "../data/document.data.php",
                            method: "POST",
                            isUpload: true,
                            form: DocumentObject.get("mainForm"),
                            params: {
                                task: "AddDoc"
                            },
                            success: function (response) {
                                DocumentObject.grid.getStore().load();
                            }
                        });

                    }
                }]
        });
    }

    Document.prototype.DeleteRender = function () {
        return  "<div title='حذف اطلاعات' class='remove' onclick='DocumentObject.RemoveItem();' " +
                "style='background-repeat:no-repeat;background-position:center;" +
                "cursor:pointer;height:16'></div>";
    }

    Document.prototype.DownloadRender = function (v, p, r)
    {
        return  "<div title='دانلود' class='down' onclick='DocumentObject.DownloadItem();' " +
                "style='background-repeat:no-repeat;background-position:center;" +
                "cursor:pointer;height:16'></div>";
    }

    Document.prototype.EditRender = function (v, p, r)
    {
        return  "<div title='ویرایش' class='edit' onclick='DocumentObject.EditItem();' " +
                "style='background-repeat:no-repeat;background-position:center;" +
                "cursor:pointer;height:16'></div>";
    }
    
    Document.prototype.ConfirmRender = function (v, p, r)
    {
        return  "<div title='تایید' class='tick' onclick='DocumentObject.EditItem();' " +
                "style='background-repeat:no-repeat;background-position:center;" +
                "cursor:pointer;height:16'></div>";
    }    

    Document.prototype.RemoveItem = function () {
    }
    Document.prototype.DownloadItem = function () {
    }
    Document.prototype.EditItem = function () {
        DocumentObject.DocForm.getForm().reset()
        var record = DocumentObject.grid.getSelectionModel().getLastSelected();
        DocumentObject.DocForm.getForm().loadRecord(record);
    }

    DocumentObject = new Document();
    DocumentObject.grid = <?= $grid ?>;
    DocumentObject.grid.render(DocumentObject.get("div_dg"));
</script>






