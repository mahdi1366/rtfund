<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.10
//-----------------------------
require_once '../header.inc.php';
require_once 'letter.class.php';
require_once inc_dataReader;
require_once inc_dataGrid;
require_once '../dms/dms.class.php';

$LetterID = !empty($_POST["LetterID"]) ? $_POST["LetterID"] : "";
if(empty($LetterID))
	die();

$dt = OFC_send::GetAll("LetterID=?", array($LetterID));
if(count($dt) == 0)
{
	require_once 'NewLetter.php';
	die();
}

$ForView = isset($_POST["ForView"]) && $_POST["ForView"] == "true" ? true : false;

$LetterObj = new OFC_letters($LetterID);
//..............................................................................
if(!empty($_REQUEST["SendID"]))
	OFC_send::UpdateIsSeen($_REQUEST["SendID"]);
//..............................................................................
$imageslist = array();
$doc = DMS_documents::SelectAll("ObjectType='letter' AND ObjectID=?", array($LetterID));
if(count($doc) > 0)
{
	$images = DMS_DocFiles::selectAll("DocumentID=?", array($doc[0]["DocumentID"]));
	foreach($images as $img)
		$imageslist[] = array(
			"url" => "/office/dms/ShowFile.php?RowID=" . $img["RowID"] . "&DocumentID=" . $img["DocumentID"] . 
					"&ObjectID=" . $LetterID . "&inline=true",
			"fileType" => $img["FileType"]);
}
$imageslist = json_encode($imageslist);
//..............................................................................
$editable = false;
if($LetterObj->LetterType == "OUTCOME" && $LetterObj->IsSigned == "NO")
{
	$dt = OFC_letters::SelectReceivedLetters(" AND l.LetterID=:lid", array(":lid"=>$LetterID));
	if($dt->rowCount()>0)
		$editable = true;
}
$signing = false;
if($LetterObj->LetterType == "OUTCOME" && 
	$LetterObj->IsSigned == "NO" && 
	$LetterObj->SignerPersonID == $_SESSION["USER"]["PersonID"])
	$signing = true;
//..............................................................................
$dg = new sadaf_datagrid("dg", $js_prefix_address . "letter.data.php?task=GetLetterComments&LetterID=" . 
		$LetterID, "grid_div");

$dg->addColumn("", "fromPerson", "", true);
$dg->addColumn("", "SendDate", "", true);

$col = $dg->addColumn("", "SendComment");
$col->renderer = "LetterInfo.CommentsRender";
$col->width = 200;

$dg->autoExpandColumn = "SendComment";
$dg->emptyTextOfHiddenColumns = true;
$dg->height = 470;
$dg->disableFooter = true;
$dg->width = 200;
$dg->EnableSearch = false;
$dg->EnablePaging = false;
$grid = $dg->makeGrid_returnObjects();
?>
<script>

LetterInfo.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	LetterID : '<?= $LetterID ?>',
	imagesList : <?= $imageslist ?>,
	
	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function LetterInfo(){
	
	this.grid = <?= $grid ?>;
	this.grid.render(this.get("div_grid"));
	
	buttons = new Array();
	<? if($editable){?>
		buttons.push({text : "ویرایش",
			iconCls : "edit",
			itemId : "btn_edit",
			handler : function(){
				LetterInfoObject.EditLetter();
			}
		},'-');
		buttons.push({
			text : "امضاء نامه",
			iconCls : "sign",
			handler : function(){
				LetterInfoObject.SignLetter();
			}
		},'-');
	<?} if(!$ForView){?>
		buttons.push({
			text : "ارجاع",
			iconCls : "sendLetter",
			itemId : "btn_send",
			handler : function(){
				LetterInfoObject.SendWindowShow();
			}
		},'-');
		buttons.push({
			text : "بایگانی",
			iconCls : "archive",
			handler : function(){
				LetterInfoObject.ArchiveWindowShow();
			}
		},'-');
	<?}?>	
	buttons.push({
		text : "چاپ",
		iconCls : "print",
		handler : function(){ 
			window.open( LetterInfoObject.address_prefix + 
				"PrintLetter.php?LetterID=" + LetterInfoObject.LetterID); 
		}
	},'-');
	
	buttons.push({
		text : "عطف",
		tooltip : "ایجاد نامه ای عطف به این نامه",
		iconCls : "connect",
		handler : function(){ 
			framework.OpenPage(LetterInfoObject.address_prefix + 
				"NewLetter.php", "ایجاد نامه", {
				MenuID : LetterInfoObject.MenuID,
				RefLetterID : LetterInfoObject.LetterID
			});
		}
	},'-');
	
	buttons.push({
		text : "کپی",
		tooltip : "ایجاد کپی از نامه",
		iconCls : "copy",
		handler : function(){ LetterInfoObject.copyLetter();}
	},'-');
	
	this.tabPanel = new Ext.TabPanel({
		renderTo: this.get("mainForm"),
		activeTab: 0,
		plain:true,
		height : 490,
		autoHeight : true,
		width: 600,
		defaults:{
			autoWidth : true            
		},
		items:[{
			title : "متن نامه",
			tbar : {
				items :buttons
			},
			itemId : "tab_letter",
			items :[{
				xtype : "container",
				height : 400,
				autoScroll: true,
				cls : "LetterContent",
				loader : {
					url : this.address_prefix + "LetterContent.php?LetterID=<?= $LetterID ?>",
					autoLoad : true
				}				
			}]			
		},{
			title : "پیوست های نامه",
			loader : {
				url : this.address_prefix + "attach.php",
				method : "post",
				text: "در حال بار گذاری...",
				scripts : true
			},
			listeners : {
				activate : function(){
					if(this.loader.isLoaded)
						return;
					this.loader.load({
						params : {
							LetterID : LetterInfoObject.LetterID,
							ExtTabID : this.getEl().id,
							lock : "true"
						}
					});
				}
			}
		},{
			title : "سابقه نامه",
			loader : {
				url : this.address_prefix + "history.php",
				method: "POST",
				text: "در حال بار گذاری...",
				scripts : true
			},
			listeners : {
				activate : function(){
					if(this.loader.isLoaded)
						return;
					this.loader.load({
						params : {
							LetterID : LetterInfoObject.LetterID,
							ExtTabID : this.getEl().id
						}
					});
				}
			}
		},{
			title : "ذینفعان نامه",
			loader : {
				url : this.address_prefix + "LetterCustomers.php",
				method: "POST",
				text: "در حال بار گذاری...",
				scripts : true
			},
			listeners : {
				activate : function(){
					if(this.loader.isLoaded)
						return;
					this.loader.load({
						params : {
							LetterID : LetterInfoObject.LetterID,
							ExtTabID : this.getEl().id,
							editable : "false"
						}
					});
				}
			}
		},{
			title : "یادداشت های نامه",
			loader : {
				url : this.address_prefix + "LetterNotes.php",
				method: "POST",
				text: "در حال بار گذاری...",
				scripts : true
			},
			listeners : {
				activate : function(){
					if(this.loader.isLoaded)
						return;
					this.loader.load({
						params : {
							LetterID : LetterInfoObject.LetterID,
							ExtTabID : this.getEl().id
						}
					});
				}
			}
		}]
	});	
	
	
	
	if(this.imagesList.length > 0)
		this.tabPanel.insert(1,{
			title : "تصاویر نامه",
			itemId : "tab_images",
			items : new MultiImageViewer({
				height : 525,
				src: this.imagesList
			})
			
		});
}

LetterInfo.CommentsRender = function(v,p,r){
	
	return "<font style=color:gray>" + r.data.fromPerson + "<br>" + 
		MiladiToShamsi(r.data.SendDate) + "</font><br><br>" + v;
}

LetterInfoObject = new LetterInfo();

LetterInfo.prototype.SendWindowShow = function(){
	
	if(!this.SendingWin)
	{
		this.SendingWin = new Ext.window.Window({
			width : 620,			
			height : 435,
			modal : true,
			bodyStyle : "background-color:white;",
			closeAction : "hide",
			loader : {
				url : this.address_prefix + "sending.php",
				scripts : true
			}
		});
		Ext.getCmp(this.TabID).add(this.SendingWin);
	}

	this.SendingWin.show();
	this.SendingWin.center();
	
	this.SendingWin.loader.load({
		scripts : true,
		params : {
			ExtTabID : this.SendingWin.getEl().id,
			parent : "LetterInfoObject.SendingWin",
			AfterSendHandler : "LetterInfoObject.AfterSend" ,			
			LetterID : this.LetterID
		}
	});
}

LetterInfo.prototype.ArchiveWindowShow = function(){
	
	if(!this.ArchiveWin)
	{
		this.ArchiveWin = new Ext.window.Window({
			width : 412,			
			height : 535,
			modal : true,
			bodyStyle : "background-color:white;",
			closeAction : "hide",
			loader : {
				url : this.address_prefix + "archive.php?mode=adding",
				scripts : true
			}
		});
		Ext.getCmp(this.TabID).add(this.ArchiveWin);
	}

	this.ArchiveWin.show();
	this.ArchiveWin.center();
	
	this.ArchiveWin.loader.load({
		scripts : true,
		params : {
			ExtTabID : this.ArchiveWin.getEl().id,
			parent : "LetterInfoObject.ArchiveWin",			
			LetterID : this.LetterID
		}
	});
}

LetterInfo.prototype.EditLetter = function(){
	
	framework.CloseTab(this.TabID);
	framework.OpenPage(this.address_prefix + "NewLetter.php", "ویرایش نامه", {LetterID : this.LetterID});
}

LetterInfo.prototype.SignLetter = function(){
	
	Ext.MessageBox.confirm("","آیا مایل به امضا می باشید؟", function(btn){
		if(btn == "no")
			return;
		me = LetterInfoObject;
		mask = new Ext.LoadMask(Ext.getCmp(me.TabID), {msg:'در حال ذخيره سازي...'});
		mask.show();  

		Ext.Ajax.request({
			url: me.address_prefix + 'letter.data.php?task=SignLetter' , 
			method: "POST",
			params : {
				LetterID : me.LetterID
			},

			success : function(response){
				mask.hide();
				Ext.getCmp(LetterInfoObject.TabID).loader.load();
			},
			failure : function(){
				mask.hide();
			}
		});
	})
	
}

LetterInfo.OpenRefLetter = function(LetterID){
	framework.OpenPage("/office/letter/LetterInfo.php", "مشخصات نامه", {
		LetterID : LetterID
	});
}

LetterInfo.prototype.AfterSend = function(){
	
	framework.CloseTab(LetterInfoObject.TabID);
}

LetterInfo.prototype.copyLetter = function(){

	Ext.Ajax.request({
		url : this.address_prefix + "letter.data.php?task=CopyLetter",
		method : "post",
		params : {
			LetterID : this.LetterID
		},
		success : function(response)
		{
			result = Ext.decode(response.responseText);
			framework.OpenPage(LetterInfoObject.address_prefix + 
				"NewLetter.php", "ایجاد نامه", {
				MenuID : LetterInfoObject.MenuID,
				LetterID : result.data
			});
		}
	});

	
}
</script>
<style>
	.LetterContent {
		padding:20px;
		margin:10px; 
		height : 500;
		font-size : 11px;
		line-height: 30px;
		border: 1px solid #bbb;
		border-radius: 10px;
		background: -webkit-linear-gradient(left, #fff 50%, #eee); /* For Safari 5.1 to 6.0 */
		background: -o-linear-gradient(right, #fff 50%, #eee); /* For Opera 11.1 to 12.0 */
		background: -moz-linear-gradient(right, #fff 50%, #eee); /* For Firefox 3.6 to 15 */
		background: linear-gradient(right, #fff 50%, #eee); /* Standard syntax */
		box-shadow: 0 4px 4px 0 rgba(0, 0, 0, 0.2), 0 4px 20px 0 rgba(0, 0, 0, 0.19);
	} 
	.LetterContent li {
		list-style: inherit !important;
		margin-right: 40px;
	}
	.LetterContent ol {
		padding : 20px;
	}
	.signDiv {
			height: 200px;
			float : left;
			background-repeat: no-repeat; 
			width: 200px; 
			text-align: center; 
			padding-top: 60px;
		}
</style>
	<br>
	<table>
		<tr>
			<td><div style="margin-right : 8px" id="mainForm"></div></td>
			<td style="vertical-align: top;padding-top:20px"><div style="" id="div_grid"></div></td>
		</tr>
	</table>
	
