<?php
?>
<script type="text/javascript" src="scan.js"></script>
<script>
	function ScanComplete(result)
	{
		alert("Done Scan!");
		alert(result);
	}
</script>
<center>
<br><br>
<h1><u>مراحل راه اندازی برنامه اسکن</u></h1>
<br><br>
<a href="dotnetfx.exe">نصب .Net framework</a>
<br><br>
<a href="setup.exe">نصب برنامه اسکن</a>
<br><br>
1.Jus type "about:config" in address bar of your firefox window and you'll get a list of preferences.<br>
2.Now search for "signed.applets.codebase_principal_support" in this list and toggle its value to "true".<br>
<br><br>
<input type="button" value="SCAN" onclick="new Scan('example-operation.php?x=10&y=2','ScanComplete');" >
</center>