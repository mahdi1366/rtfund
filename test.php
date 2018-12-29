<?php
require_once "framework/header.inc.php";

$dt = PdoDataAccess::runquery("select * from COM_EventRows");
print_r($dt); 