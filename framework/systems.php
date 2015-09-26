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
			
			.header-top {
				height : 70px;
				background-color : #a07eb0;
			}
			
			.header {
				background-image: url('icons/header.jpg');
				height: 350px;
			}
			
			.menus{
				width : 1000px;				
			}
			
			.systemRow{
				width : 100%;
				margin-top: 20px;
			}
			
			.system {
				display: -moz-box;
				width: 30%;
				margin-left: 15px;
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
				overflow: hidden;
			}
			
			.SystemDesc {
				line-height: 19px;
				margin: 10px;
				height : 150px;
				overflow: hidden;
			}
			
			.systemTitle {
				padding-bottom: 10px;
				border-bottom : 1px solid #a07eb0;
				font-weight: bold;
				color : #67426d;
				width : 185px;
			}			
						
			.footer-top {
				width : 100%;
				border-top: 3px solid #a07eb0;
				margin: 20px 0 20px 0;
			}
			
			.footer {
				height : 70px;
				background-color: #67406c;
			}
		</style>
	</head>
	<body DIR=RTL>
		
		<div class="header-top"></div>
			
		<div class="header"></div>
		
		<center>
			<div class="menus" align="right">
				
				<?
				for ($i = 0; $i < count($systems); $i++) {
					if ($i % 3 == 0)
						echo $i == 0 ? '<div class="systemRow">' : '</div><div class="systemRow">';

					echo "
						<div class=system onclick=\"OpenSystem('" . $systems[$i]["SystemID"] . "','" . $systems[$i]["SysPath"] . "')\" >" .
							"<div class=SystemIcon>" . "<img src='icons/SysIcons/" . $systems[$i]["SysPath"]  . ".png'>" . "</div>" . 
							"<div class=SystemDesc><div class=SystemTitle>" . $systems[$i]["SysName"] . "</div><br>" . $systems[$i]["menuNames"] . "</p>" .  "</div></div>";
				}
				if (count($systems) % 3 != 0)
					echo "</div>";
				?>
				
			</div>
			
			<div class="footer-top" > </div>
			
			<div class="footer"></div>
			
		</center>
</body>
</html>