<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.06
//-----------------------------

require_once getenv("DOCUMENT_ROOT") . '/framework/session.php';
session::sec_session_start();
session::logout();

?>
<script>
	window.location = "/framework/login.php";
</script>