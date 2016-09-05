<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.10
//-----------------------------
require_once '../header.inc.php';
require_once 'letter.class.php';
require_once inc_dataReader;
require_once '../dms/dms.class.php';

$LetterID = !empty($_POST["LetterID"]) ? $_POST["LetterID"] : "";
if(empty($LetterID))
	die();

$ForView = isset($_POST["ForView"]) && $_POST["ForView"] == "true" ? true : false;

$LetterObj = new OFC_letters($LetterID);
//..............................................................................

if(!empty($_REQUEST["SendID"]))
	OFC_send::UpdateIsSeen($_REQUEST["SendID"]);

//..............................................................................
$letterYear = substr(DateModules::miladi_to_shamsi($LetterObj->LetterDate),0,4);

$content = "<br><div style=margin-left:30px;float:left; >شماره نامه : " . 
	"<span dir=ltr>" . $letterYear . "-" . $LetterObj->LetterID . "</span>". 
	"<br>تاریخ نامه : " . DateModules::miladi_to_shamsi($LetterObj->LetterDate);

if($LetterObj->LetterType == "INCOME")
{
	$content .= "<br>شماره نامه وارده : " . $LetterObj->InnerLetterNo;
	$content .= "<br>تاریخ نامه وارده : " . DateModules::miladi_to_shamsi($LetterObj->InnerLetterDate);
}

if($LetterObj->RefLetterID != "")
{
	$refObj = new OFC_letters($LetterObj->RefLetterID);
	$RefletterYear = substr(DateModules::miladi_to_shamsi($refObj->LetterDate),0,4);
	$content .= "<br>عطف به نامه : <a href=javascript:void(0) onclick=LetterInfo.OpenRefLetter(" . 
		$LetterObj->RefLetterID . ")>".
		"<span dir=ltr>" . $RefletterYear . "-" . $LetterObj->RefLetterID. "</span></a>";
}
$content .= "</div><br><br>";

$content .= "<b><br><div align=center>بسمه تعالی</div><br>";
$dt = PdoDataAccess::runquery("
	select  p2.sex,FromPersonID,p3.PersonSign signer, p1.PersonSign regSign,
		if(p1.IsReal='YES',concat(p1.fname, ' ', p1.lname),p1.CompanyName) RegPersonName ,
		if(p2.IsReal='YES',concat(p2.fname, ' ', p2.lname),p2.CompanyName) ToPersonName ,
		concat(p3.fname, ' ', p3.lname) SignPersonName ,
		po.PostName,
		s.IsCopy
	from OFC_send s
		join OFC_letters l using(LetterID)
		join BSC_persons p1 on(l.PersonID=p1.PersonID)
		join BSC_persons p2 on(s.ToPersonID=p2.PersonID)
		left join BSC_persons p3 on(l.SignerPersonID=p3.PersonID)
		left join BSC_posts po on(p3.PostID=po.PostID)
	where LetterID=? 
	order by SendID
	", array($LetterID));

if($LetterObj->LetterType == "INNER")
{
	foreach($dt as $row)
	{
		if($row["FromPersonID"] != $LetterObj->PersonID || $row["IsCopy"] == "YES")
			continue;	
		$content .= $row["sex"] == "MALE" ? "جناب آقای " : "سرکار خانم ";
		$content .= $row['ToPersonName'] . "<br>";
	}
	
	$content .= "<br> موضوع : " . $LetterObj->LetterTitle . "<br><br></b>";
	$content .= str_replace("\r\n", "", $LetterObj->context);
	
	$sign = $dt[0]["regSign"] != "" ? "background-image:url(\"" .
			data_uri($dt[0]["regSign"],'image/jpeg') . "\")" : "";
	
	$content .= "<table width=100%><tr><td><div class=signDiv style=" . $sign . "><b>" . 
			$dt[0]["RegPersonName"] . "</b><br><br>" . $dt[0]["PostName"] . "</div></td></tr></table>";
}
if($LetterObj->LetterType == "OUTCOME")
{
	$content .= $LetterObj->OrgPost . " " . $LetterObj->organization . "<br>" ;
	$content .= "<br> موضوع : " . $LetterObj->LetterTitle . "<br><br></b>";
	$content .= str_replace("\r\n", "", $LetterObj->context);
	
	$sign = $LetterObj->IsSigned == "YES" && $dt[0]["signer"] != "" ? 
			"background-image:url(\"" . data_uri($dt[0]["signer"],'image/jpeg') . "\")" : "";
	
	$content .= "<table width=100%><tr><td><div class=signDiv style=" . $sign . "><b>" . 
			$dt[0]["SignPersonName"] . "</b><br><br>" . $dt[0]["PostName"] . "</div></td></tr></table>";
}
foreach($dt as $row)
{
	if($row["FromPersonID"] != $LetterObj->PersonID || $row["IsCopy"] == "NO")
		continue;	
	$content .= "<b> رونوشت : " . ($row["sex"] == "MALE" ? "جناب آقای " : "سرکار خانم ") . 
			$row['ToPersonName'] . "<br></b>";
}

if($LetterObj->OuterCopies != "")
{
	$LetterObj->OuterCopies = str_replace("\r\n", " , ", $LetterObj->OuterCopies);
	$content .= "<br><b> رونوشت خارج از سازمان : " . $LetterObj->OuterCopies . "</b><br>";
}

//..............................................................................
$imageslist = array();
$doc = DMS_documents::SelectAll("ObjectType='letter' AND ObjectID=?", array($LetterID));
if(count($doc) > 0)
{
	$images = DMS_DocFiles::selectAll("DocumentID=?", array($doc[0]["DocumentID"]));
	foreach($images as $img)
		$imageslist[] = "'/office/dms/ShowFile.php?RowID=" . $img["RowID"] . "&DocumentID=" . $img["DocumentID"] . 
			"&ObjectID=" . $LetterID . "'";
}
$imageslist = implode(",", $imageslist);
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
	
	buttons = new Array();
	<? if($editable){?>
		buttons.push({text : "ویرایش",
			iconCls : "edit",
			itemId : "btn_edit",
			handler : function(){
				LetterInfoObject.EditLetter();
			}
		});
		buttons.push({
			text : "امضاء نامه",
			iconCls : "sign",
			handler : function(){
				LetterInfoObject.SignLetter();
			}
		});
	<?} if(!$ForView){?>
		buttons.push({
			text : "ارجاع",
			iconCls : "sendLetter",
			itemId : "btn_send",
			handler : function(){
				LetterInfoObject.SendWindowShow();
			}
		});
		buttons.push({
			text : "بایگانی",
			iconCls : "archive",
			handler : function(){
				LetterInfoObject.ArchiveWindowShow();
			}
		});
	<?}?>	
	buttons.push({
		text : "چاپ",
		iconCls : "print",
		handler : function(){ 
			window.open( LetterInfoObject.address_prefix + 
				"PrintLetter.php?LetterID=" + <?= $LetterID ?>); 
		}
	});
	
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
			itemId : "tab_letter",
			items :[{
				xtype : "container",
				autoScroll: true,
				cls : "LetterContent",
				html : '<?= $content ?>'
			}],
			buttons : buttons
			
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
	<div style="margin-right : 20px" id="mainForm"></div>