<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 90.10
//-----------------------------

if (!isset($_REQUEST["task"])){	
	set_include_path(get_include_path() . PATH_SEPARATOR . getenv("DOCUMENT_ROOT") . "/generalClasses");
	require_once '../../../header.inc.php';
	require_once 'md5.php';
}

$task = "" ; 
$task = isset($_REQUEST["task"]) ? $_REQUEST["task"] : "";	

if(!empty($task)){
	
	switch ($task)
	{
		case "changePass":
		changePass();
	}  
}

function changePass()
{
	require_once '../../../header.inc.php';
	require_once(inc_response);
	require_once inc_dataReader;
	
	$pdoAcc = PdoDataAccess::getPdoObject(	config::$db_servers['master']["host"], 
											config::$db_servers['master']["framework_user"], 
											config::$db_servers['master']["framework_pass"], "framework");
	
		
	$dt = PdoDataAccess::runquery("select * from AccountSpecs 
										where personID=:psid",
												array(":psid" => $_SESSION['PersonID']),$pdoAcc);
	if(count($dt) == 0)
	{ 		
		echo "false";
		die();
	}

	$password = md5($_POST["cur_pass"]); 
	    
	$stored_seed = substr($dt[0]["pswd1"],40,10);    
				
    if (sha1($stored_seed.$password.$stored_seed).$stored_seed != $dt[0]["pswd1"])
	{		
		echo "CurPassError";
		die();
	}
   
	$seed=''; 
	
	$password2 = md5($_POST["new_pass"]); 
	
	for ($i = 1; $i <= 10; $i++)
		 $seed .= substr('0123456789abcdef', rand(0,15), 1);
		
	PdoDataAccess::RUNQUERY("update AccountSpecs set pswd1=:pswd where personID=:psid",
									array(":pswd" => sha1($seed.$password2.$seed).$seed ,
									":psid" => $_SESSION['PersonID'] ),$pdoAcc);
								
	if( ExceptionHandler::GetExceptionCount() != 0 )
	{				
		echo "CurPassError";
		die();
	}
	
	//PdoDataAccess::AUDIT("AccountSpecs","تغییر رمز عبور", "");	
	echo "true";
	die();
}

?>
<script>
Password.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function Password()
{
	Ext.form.Field.prototype.msgTarget = 'side';

	Ext.apply(Ext.form.VTypes, {

		password : function(val, field) {
			if (field.initialPassField) {
				var pwd = Ext.getCmp(field.initialPassField);
				return (val == pwd.getValue());
			}
			return true;
		},

		passwordText : 'رمزهای عبور جدید یکسان نمی باشند'
	});

	this.passform = new Ext.form.FormPanel({
		renderTo : "pass_div",
		frame: true,
		title: 'تغییر رمز عبور',
		bodyStyle:'padding:5px 5px 0px',
		width: 400,
		defaults: {
			inputType: 'password',
			allowBlank:false
		},
		defaultType: 'textfield',
		items: [{
			fieldLabel: 'رمز عبور فعلی',
			name: 'cur_pass',
			id: 'cur_pass'
		},{
			fieldLabel: 'رمز عبور جدید',
			name: 'new_pass',
			id: 'new_pass'
		},{
			fieldLabel: 'تکرار رمز عبور جدید',
			name: 'new_pass2',
			id: 'new_pass2',
			vtype: 'password',
			initialPassField: 'new_pass' // id of the initial password field
		}],

		buttons : [{
			text : "ذخیره",
			iconCls: 'save',
			handler: function() {
				if (PasswordObject.passform.form.isValid()) {
					var mask = new Ext.LoadMask(Ext.getCmp(PasswordObject.TabID),{msg: 'تغییر رمز عبور ...'});
					mask.show();

					Ext.Ajax.request({
						url: 'global/access/ui/change_pass.php' , 
						params: {
							task: "changePass",
							cur_pass : Ext.getCmp("cur_pass").getValue(), //MD5(Ext.getCmp("cur_pass").getValue()),
							new_pass :Ext.getCmp("new_pass").getValue()
						},
						method: "POST",

						success:function(response,options)
						{
							mask.hide();
							if(response.responseText == "CurPassError")
							{
								alert("رمز عبور فعلی اشتباه می باشد");
								return;
							}
							if(response.responseText == "true")
							{
								alert("رمز شما با موفقیت تغییر یافت");
								Ext.getCmp("cur_pass").setValue();
								Ext.getCmp("new_pass").setValue();
								Ext.getCmp("new_pass2").setValue();
							}
							else
								alert("عملیات مورد نظر با شکست مواجه شد");
						}
					});
				}
			}

		}]
	});

	this.passform.center();
}

PasswordObject = new Password();

Password.prototype.changePass = function()
{
	if(this.get("new_pass").value != this.get("new_pass2").value)
	{
		return;
	}
}
</script>
<br><br>
<div id="pass_div"></div>
