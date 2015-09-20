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
		<title>نرم افزار جامع صندوق پژوهش و فناوری خراسان رضوی</title>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<link rel="stylesheet" href="ext/skel.css" />
		<link rel="stylesheet" href="ext/style.css" />
		<link rel="stylesheet" href="ext/style-normal.css" />
		<script>
			function OpenSystem(SystemID, SysPath){
				window.location = "../" + SysPath + "/start.php?SystemID=" + SystemID;
			}
		</script>
	</head>
	<body DIR=RTL>

		<!-- Wrapper -->
			<div class="wrapper style1">

				<!-- Header -->
					<div id="header" class="skel-panels-fixed" style="font-family:b titr">
						<center>نرم افزار جامع صندوق پژوهش و فناوری خراسان رضوی</center>
					</div>
			
				<!-- Extra -->
					<div id="extra">
						<div class="container">
							<?
							for ($i = 0; $i < count($systems); $i++) {
								if ($i % 4 == 0)
									echo $i == 0 ? '<div class="row no-collapse-1">' : '</div>><div class="row no-collapse-1">';

								echo "
									<section class=3u>
										<div class=SysHeader onclick=\"OpenSystem('" . $systems[$i]["SystemID"] . "','" . $systems[$i]["SysPath"] . "')\" ".
											" style='background-image:url(icons/SysIcons/" . $systems[$i]["SysPath"] . ".jpg)'>
											" . 
												$systems[$i]["SysName"] . "</div>
										<div class=box>
											<p class=SystemDesc>" . $systems[$i]["menuNames"] . "</p>
										</div>
									</section>";
							}
							?>
							</div>
						</div>
					</div>

			
	</div>

	<!-- Footer -->
		<div id="footer" class="wrapper style2">
			<div class="container">
				<section>
				
				</section>
			</div>
		</div>

	<!-- Copyright -->
		<div id="copyright">
			<div class="container">
				<div class="copyright">
					
				</div>
			</div>
		</div>

	</body>
</html>