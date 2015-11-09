<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.06
//-----------------------------

if (!empty($_POST["SystemID"])) {
	if (!empty($_POST["SysPath"])) {
		require_once getenv("DOCUMENT_ROOT") . "/" . $_POST["SysPath"] . "/start.php";
		die();
	}
}

require_once 'header.inc.php';
require_once 'management/framework.class.php';

$systems = FRW_access::getAccessSystems();
$_SESSION['USER']["RecentSystems"] = array();
?>
<html>
	<head>
		<title>نرم افزار جامع <?= SoftwareName?></title>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<script>
			function OpenSystem(SystemID, SysPath){
				window.location = "../" + SysPath + "/start.php?SystemID=" + SystemID;
			}
		</script>
		<style>
			body{
				margin: 0;
			}
			
			.headerDiv{
				height: 180px;
				width : 100%;
				background-color: #7175cd;
				border-bottom: 15px solid #50467f;
			}
			
			.menus{
				width : 1000px;	
				margin-top: 35px;
			}
			
			.systemRow{
				width : 100%;
				margin-top: 20px;
			}
			
			.system {
				float : right;
				width: 300px;
				margin-bottom: 20px;
				margin-left: 20px;
				font-family: tahoma;
				font-size: 12px;
				border : medium none rgba(255, 255, 255, 0.2);
				box-shadow: 2px 2px 5px #c1c1c1;
				height : 200px;
				border-radius: 15px;
				cursor : pointer;
			}
			
			.SystemIcon {
				background-color: #f2f2f2;
				border-radius: 0 15px 15px 0;
				width : 100px;
				text-align: center;
				padding-top: 50px;
				text-overflow: ellipsis;
				vertical-align: middle;	
				height : 150px;
				float: right;
				overflow: hidden;
				background-position: center center;
				background-repeat: no-repeat;
			}
			
			.SystemDesc {
				line-height: 19px;
				margin: 10px;
				height : 150px;
				padding-right : 4px;
				overflow: hidden;
			}
			
			.systemTitle {
				padding-bottom: 10px;
				border-bottom : 1px solid #a07eb0;
				font-weight: bold;
				color : #67426d;
				width : 185px;
			}			
						
			.footerDiv{
				background-color: #575ca5;
				position: absolute;
				bottom: 0;
				font-family: tahoma;
				font-size: 12px;
				height : 100px;
				width : 100%;
				color : white;		
			}
		</style>
	</head>
	<body DIR=RTL style="margin:0">
		
		<div class="headerDiv" align="center">
			<div style="width:800;right : 20%;position: absolute;" align="right"><br><br><img width="180px" src="../framework/icons/LoginLogo.png"></div>
		</div>
		<center>
			<div class="menus" align="right">
			<?php

			if(count($systems) == 0)
			{
				echo "<center><br><br><br><font style='font-family:b titr;'>";
				echo "شما به نرم افزار جامع صندوق پژوهش و فناوری خراسان رضوی دسترسی ندارید.";
				echo "</font></center></body>";
				die();
			}
			else
			{
				for ($i = 0; $i < count($systems); $i++) {
					if ($i % 3 == 0)
						echo $i == 0 ? '<div class="systemRow">' : '</div><div class="systemRow">';

					echo "
						<div class=system onclick=\"OpenSystem('" . $systems[$i]["SystemID"] . "','" . $systems[$i]["SysPath"] . "')\" >" .
							"<div class=SystemIcon style=background-image:url('icons/SysIcons/" . $systems[$i]["SysPath"]  . ".png')></div>" . 
							"<div class=SystemDesc><div class=SystemTitle>" . $systems[$i]["SysName"] . "</div><br>" . $systems[$i]["menuNames"] . "</p>" .  "</div></div>";
				}
				if (count($systems) % 3 != 0)
					echo "</div>";
			}
			?>

			</div>
			<div class="footerDiv"> <br><br>
<br>© کلیه حقوق این نرم افزار محفوظ است.</div>
		</center>
</body>
</html>