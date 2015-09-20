<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.06
//-----------------------------

if(!empty($_POST["SystemID"]))
{
	if(!empty($_POST["SysPath"]))
	{
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
		<meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>	
		<title>پرتال جامع صندوق پژوهش و فناوری</title>
		<link rel="stylesheet" type="text/css" href="/generalUI/ext4/resources/css/Loading.css" />
		<link rel="stylesheet" type="text/css" href="/generalUI/ext4/resources/css/ext-all.css" />

		<style type="text/css">
			html, body {
				font:normal 9px tahoma;
				margin:0;
				padding:0;
				border:0 none;
				overflow:hidden;
				height:100%;
			}
			td {
				font-family: b titr;
				font-size: 15px;
				
			}
		</style>
		<script>
			function OpenSystem(SystemID, SysPath){
				form = document.getElementById("MainForm");
				form.SystemID.value = SystemID;
				form.SysPath.value = SysPath;
				form.submit();
			}
		</script>
	</head>
	<body dir="rtl">
		<div style="height:200px"></div>
	<center>
		<form id="MainForm" method="post">
			<input type="hidden" name="SystemID" id="SystemID">
			<input type="hidden" name="SysPath" id="SysPath">
			<table style="width:750px">
				<?
				for ($i = 0; $i < count($systems); $i++) {
					if ($i % 2 == 0)
						echo $i == 0 ? "<tr>" : "</tr><tr>";

					echo "<td style='cursor:pointer' onclick=\"OpenSystem('" . $systems[$i]["SystemID"] . "','" . $systems[$i]["SysPath"] . "')\">
						<image style='vertical-align: middle;' src='icons/SysIcons/" . $systems[$i]["SysIcon"] . "' >
							" . $systems[$i]["SysName"] . "
					</td>";
				}
				?>
				</tr>
			</table>
		</form>
	</center>
</body>
</html>