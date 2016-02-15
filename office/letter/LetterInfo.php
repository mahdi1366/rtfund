<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.10
//-----------------------------
require_once '../header.inc.php';
require_once 'letter.class.php';
require_once '../../dms/dms.class.php';

$LetterID = !empty($_POST["LetterID"]) ? $_POST["LetterID"] : "";
if(empty($LetterID))
	die();

$LetterObj = new OFC_letters($LetterID);
//..............................................................................

if(!empty($_REQUEST["SendID"]))
	OFC_send::UpdateIsSeen($_REQUEST["SendID"]);

//..............................................................................
$content = "<br><div style=margin-left:30px;float:left; >شماره نامه : " . 
	$LetterObj->LetterID . "<br>تاریخ نامه : " . 
	DateModules::miladi_to_shamsi($LetterObj->LetterDate) . "</div><br><br>";

$content .= "<b>";
$dt = PdoDataAccess::runquery("
	select  p1.sex,FromPersonID,
		if(p1.IsReal='YES',concat(p1.fname, ' ', p1.lname),p1.CompanyName) FromPersonName ,
		if(p2.IsReal='YES',concat(p2.fname, ' ', p2.lname),p2.CompanyName) ToPersonName 
	from OFC_send s
		join OFC_letters l using(LetterID)
		join BSC_persons p1 on(s.FromPersonID=p1.PersonID)
		join BSC_persons p2 on(s.ToPersonID=p2.PersonID)
	where LetterID=? 
	order by SendID
	", array($LetterID));
foreach($dt as $row)
{
	if($row["FromPersonID"] != $LetterObj->PersonID)
		break;	
	$content .= $row["sex"] == "MALE" ? "جناب آقای " : "سرکار خانم ";
	$content .= $row['ToPersonName'] . "<br>";
}

$content .= "<br> موضوع : " . $LetterObj->LetterTitle . "<br><br></b>";
$content .= str_replace("\r\n", "", $LetterObj->context);
$content .= "<br><br><div align=left style=width:80%><b>" . $dt[0]["FromPersonName"] . "</b></div>";
//..............................................................................
$imageslist = array();
$images = DMS_documents::SelectAll("ObjectType='letter' AND ObjectID=?", array($LetterID));
foreach($images as $img)
	$imageslist[] = "'/dms/ShowFile.php?DocumentID=" . $img["DocumentID"] . 
		"&ObjectID=" . $LetterID . "'";
$imageslist = implode(",", $imageslist);
//..............................................................................
?>
<script>

LetterInfo.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	LetterID : '<?= $LetterID ?>',
	imagesList : [<?= $imageslist ?>],
	
	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function LetterInfo(){
	
	this.tabPanel = new Ext.TabPanel({
		renderTo: this.get("mainForm"),
		activeTab: 0,
		plain:true,
		autoHeight : true,
		width: 700,
		defaults:{
			autoWidth : true            
		},
		items:[{
			title : "متن نامه",
			items :[{
				xtype : "container",
				autoScroll: true,
				cls : "LetterContent",
				html : '<?= $content ?>'
			}],
			buttons : [{
				text : "بایگانی",
				iconCls : "archive",
				handler : function(){
					LetterInfoObject.ArchiveWindowShow();
				}
			},{
				text : "ارجاع",
				iconCls : "sendLetter",
				itemId : "btn_send",
				handler : function(){
					LetterInfoObject.SendWindowShow();
				}
			},{
				text : "چاپ",
				iconCls : "print",
				handler : function(){ 
					window.open( MyLetterObject.address_prefix + 
						"PrintLetter.php?LetterID=" + <?= $LetterID ?>); 
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
			AfterSendHandler : function(){
				framework.CloseTab(LetterInfoObject.TabID);
			},
			
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



</script>
<style>
	.LetterContent {
		padding:20px;
		margin:10px; 
		height : 500;
		font-size : 11px;
		line-height: 20px;
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
	}
	.LetterContent ol {
		padding : 20px;
	}
</style>
	<br>
	<div style="margin-right : 20px" id="mainForm"></div>