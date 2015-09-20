<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.06
//-----------------------------

session_start();
unset ($_SESSION["USER"]);
?>
<script>
	window.location = "login.php";
</script>