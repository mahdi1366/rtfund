<?php
//---------------------------
// programmer:	Sh.Jafarkhani
// create Date:	97.11
//---------------------------
require_once '../header.inc.php';
require_once '../framework/person/persons.class.php';
require_once '../framework//baseInfo/baseInfo.class.php';
require_once "../office/workflow/wfm.class.php";

$PersonID = session::IsPortal() ? $_SESSION["USER"]["PersonID"] : $_REQUEST["PersonID"];
$personObj = new BSC_persons($PersonID);

$pObj = new BSC_processes(PROCESS_REGISTRATION);
$result = WFM_FlowRows::GetFlowInfo($pObj->FlowID, PROCESS_REGISTRATION, $PersonID);
/*if($result["IsEnded"])
{
	$personObj->IsActive = "YES";
	$personObj->EditPerson();
	$_SESSION["USER"]["IsActive"] = "YES";
	echo "<script>window.location = window.location;</script>";
	die();
}*/
$status = 'raw';
if($result["IsEnded"])
	$status = 'end';
else if($result["IsStarted"])
	$status = $result["ActionType"] == "CONFIRM" ? 'start' : "reject";
?>
<center>	
	<br>
    <form id="mainForm"><div id="div_form1"></div><div id="div_form"></div></form>
</center>
<script type="text/javascript">

process_registration.prototype = {
	TabID: '<?= $_REQUEST["ExtTabID"] ?>',
	address_prefix : "<?= $js_prefix_address ?>",

	PersonID : <?= $personObj->PersonID ?>,
	status : "<?= $status ?>",
	
	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function process_registration(){	
	
	if(<?= session::IsPortal() ? "true" : "false" ?> && ( this.status == 'start' || this.status == 'reject'))
	{
		html = this.status == 'start' ? "<br><br><br>" + "فرم ثبت نام شما در مرحله بررسی می باشد." : 
				"فرم شما به دلایل زیر برگشت گردید:" + "<hr style=color:red>" + 
				"<?= preg_replace('/\n/', "<br>", $result["ActionComment"]) ?>"
		style = this.status == 'start' ? "blueText" : "redText";
		this.messagePanel = new Ext.form.Panel({
			width: 500,
			height : 200,
			frame : true,
			renderTo: this.get("div_form1"),
			html : html,
			bodyCls : style
			
		});
		if(this.status == 'start')
			return;
	}
	
	this.wizardPanel = new Ext.form.Panel({
		width: 770,
		autoHeight : true,			
		id : "card-wizard-panel",
		title: "تکمیل فرایند ثبت نام",
		renderTo: this.get("div_form"),
		defaults: {
			border: false,
			minHeight : 200,
			anchor: "100%",
			bodyStyle : "padding-bottom:10px"
		},
		frame: true,
		layout: "card",
		activeItem: 0, 
		items: [{
			id : "card-0",
			items: [{
				xtype: "container",
				cls : "blueText",
				html: '<img src="/generalUI/icons/arrow-left.gif" style="vertical-align: middle;"/>' + 
					 "لطفا فرم زیر را برای متقاضی حقیقی/حقوقی تکیل نمایید.تکمیل موارد ستاره‌دار الزامی می‌باشد."
			},{
				xtype : "panel",
				border: false,
				itemId : "pln_personInfo",
				loader : {
					url : "/framework/person/PersonInfo.php?justInfoTab=true",
					scripts : true
				}
			}]
		}, {
			id : "card-1",
			disabled : <?= $personObj->IsReal == "YES" ? "true" : "false" ?>,
			items: [{
				xtype: "container",
				cls : "blueText",
				html: '<img src="/generalUI/icons/arrow-left.gif" style="vertical-align: middle;"/>' +
						"اطلاعات نماینده تام الاختیار (رابط)خود را تکمیل نمایید" + "<br>&nbsp;"
			},{
				xtype : "panel",
				border: false,
				itemId : "pln_signer",
				loader : {
					url : "/framework/person/OrgSigners.php",
					scripts : true
				}
			}]
		},{
			id : "card-2",
			items: [{
				xtype : "panel",
				border: false,
				itemId : "pln_Agreement",
				loader : {
					url : "/framework/person/Agreement.php",
					scripts : true
				}
			}]
		},{
			id : "card-3",
			items : [{
				xtype: "container",
				cls : "blueText",
				style : "text-align:right; padding-right:5px",
				html: 	"لطفا مدارک زیر را بارگذاری نمایید" + "<br>" +
					'<font color=red>*  </font>' +
						" پیوست تصویر نامه درخواست رسمی"+"<Br>"+
						"پیوست طرح توجیهی در صورت وجود "+"<Br>"+
						"پیوست تصویر نامه معرفی متقاضی(" + 
						"در صورت دانش بنیان بودن شرکت یا داشتن گواهی ثبت اختراع در خصوص محصول/ خدمات موضوع طرح، الزامی نمی باشد.)"
			},{
				xtype : "panel",
				border: false,
				itemId : "pln_dms",
				loader : {
					url : "../../office/dms/documents.php",
					scripts : true
				}
			}]
		},{
			id : "card-4",
			items : [{
				xtype: "container",
				cls : "blueText",
				html: '<img src="/generalUI/icons/arrow-left.gif" style="vertical-align: middle;"/>' +
						"فهرست کلیه مجوزها و گواهینامه‌های مرتبط با طرح خود را بارگداری نمایید." + "<br>&nbsp;"  +
						"مجوزها از قبیل مجوز موسسه فناور، پارک علم و فناوری، مجوز فعالیت و مجوزهای صنفی، گواهینامه ثبت اختراع، گواهی نامه صلاحیت و ...) " + 
						"<br>&nbsp;"
			},{
				xtype : "panel",
				border: false,
				itemId : "pln_licenses",
				loader : {
					url : "/framework/person/licenses.php",
					scripts : true
				}
			}]
		},{
			id : "card-5",
			disabled : <?= session::IsPortal() ? "false" : "true" ?>,
			items : [{
				xtype: "container",				
				cls : "blueText",
				html: '<img src="/generalUI/icons/arrow-left.gif" style="vertical-align: middle;"/>' +
						"در صورتیکه کلیه اطلاعات را به درستی وارد کرده اید می توانید فرم را جهت تایید ارسال کنید." + 
						"<br>نتیجه بررسی فرم ثبت نام شما از طریق پیامک به اطلاع خواهد رسید" +
						"<br>&nbsp;"
			},{
				xtype : "button",
				border: true,
				text : "ارسال فرم",
				iconCls : "send",
				handler : function(){process_registrationObject.sendForm()}
			}]
		}],
		buttons: [{
			id: 'card-prev',
			text: '&laquo; قبلی',
			handler: function(){
				process_registrationObject.PrevCard();
			},
			disabled: true
		}, {
			id: 'card-next',
			text: 'بعدی &raquo;',
			handler: function() {
				process_registrationObject.NextCard();
			}
		}]
	});
	
	this.wizardPanel.down("[itemId=pln_personInfo]").loader.load({
		params : {
			ExtTabID : this.wizardPanel.down("[itemId=pln_personInfo]").getEl().id,
			activeTab : 1,
			PersonID : this.PersonID
		}
	});
}

process_registration.prototype.PrevCard = function() {
			
	var l = Ext.getCmp('card-wizard-panel').getLayout();
	var i = l.activeItem.id.split('card-')[1];
	var next = parseInt(i, 10) - 1;
	
	if(Ext.getCmp('card-'+next).isDisabled())
		next--;
	
	l.setActiveItem(next);
	
	Ext.getCmp('card-prev').setDisabled(next === 0);
	Ext.getCmp('card-next').setDisabled(false);
}

process_registration.prototype.NextCard = function() {
			
	var l = Ext.getCmp('card-wizard-panel').getLayout();
	var i = l.activeItem.id.split('card-')[1];
	var next = parseInt(i, 10) + 1;
	
	if(Ext.getCmp('card-'+next).isDisabled())
		next++;
	
	switch(next)
	{
		case 1:
			if(!this.wizardPanel.down("[itemId=pln_signer]").loader.isLoaded)
				this.wizardPanel.down("[itemId=pln_signer]").loader.load({
					params : {
						ExtTabID : this.wizardPanel.down("[itemId=pln_signer]").getEl().id,
						PersonID : this.PersonID
					}
				});
			break;
		case 2:
			if(!this.wizardPanel.down("[itemId=pln_Agreement]").loader.isLoaded)
				this.wizardPanel.down("[itemId=pln_Agreement]").loader.load({
					params : {
						ExtTabID : this.wizardPanel.down("[itemId=pln_Agreement]").getEl().id,
						PersonID : this.PersonID
					}
				});
			break;
		case 3:
			if(!this.wizardPanel.down("[itemId=pln_dms]").loader.isLoaded)
				this.wizardPanel.down("[itemId=pln_dms]").loader.load({
					params : {
						ExtTabID : this.wizardPanel.down("[itemId=pln_dms]").getEl().id,
						ObjectType : "person",
						ObjectID : this.PersonID
					}
				});
			break;
		case 4:
			if(!this.wizardPanel.down("[itemId=pln_licenses]").loader.isLoaded)
				this.wizardPanel.down("[itemId=pln_licenses]").loader.load({
					params : {
						ExtTabID : this.wizardPanel.down("[itemId=pln_licenses]").getEl().id,
						PersonID : this.PersonID
					}
				});
			break;	
			
	}
	
	l.setActiveItem(next);
	
	Ext.getCmp('card-prev').setDisabled(false);
	Ext.getCmp('card-next').setDisabled(Ext.getCmp('card-' + (next+1)) == undefined || 
										Ext.getCmp('card-' + (next+1)).isDisabled());
}

process_registration.prototype.sendForm = function(){

	Ext.MessageBox.confirm("","آیا مایل به ارسال فرم می باشید؟", function(btn){
		if(btn == "no")
			return;
		me = process_registrationObject;
	
		mask = new Ext.LoadMask(Ext.getCmp(me.TabID),{msg:'در حال ذخیره سازی ...'});
		mask.show();

		Ext.Ajax.request({
			url: me.address_prefix + '/process.data.php',
			method: "POST",
			params: {
				task: "SendRegisterProcess"
			},
			success: function(response){
				mask.hide();
				var st = Ext.decode(response.responseText);

				if(st.success)					
				{
					window.location = window.location;
				}
				else
				{
					if(st.data == "")
						Ext.MessageBox.alert("","خطا در اجرای عملیات");
					else
						Ext.MessageBox.alert("",st.data);
				}
			},
			failure: function(){
				mask.hide();
				Ext.MessageBox.alert("","خطا در اجرای عملیات");
			}
		});
	})	
}

var process_registrationObject = new process_registration();

</script>
