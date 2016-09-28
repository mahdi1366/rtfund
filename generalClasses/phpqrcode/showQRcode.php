<?php

require_once('qrlib.php');

QRcode::png($_REQUEST["value"]);

?>
