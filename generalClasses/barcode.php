<?php
//-----------------------------
// Programmer	: SH.Jafarkhani
// Date		: 91.01
//-----------------------------
 
$code = "ean13";
$tickness = 20;
$resolution = $_GET["size"];
$rotation = 0;
$input = $_GET["value"];
$output = 1;
$dpi = 72;
$fontFamily = 'Arial.ttf';// : "-1";
$fontSize = 8;

$class_dir = "barcodeClasses";
require($class_dir . '/BCGColor.php');
require($class_dir . '/BCGBarcode.php');
require($class_dir . '/BCGDrawing.php');
require($class_dir . '/BCGFont.php');

if (include($class_dir . '/BCG' . $code . '.barcode.php')) {
    $font = ($fontFamily != "-1") ? 
	$font = new BCGFont($class_dir . '/font/' . $fontFamily, intval($fontSize)) : "";
    
    $color_black = new BCGColor(0, 0, 0);
    $color_white = new BCGColor(255, 255, 255);
    $codebar = 'BCG' . $code;
    $code_generated = new $codebar();
    
    //$code_generated->setChecksum(true);
    //$code_generated->setStart($_GET['a2']);
    //$code_generated->setLabel($_GET['a3']);
    
    $code_generated->setThickness($tickness);
    $code_generated->setScale($resolution);
    $code_generated->setBackgroundColor($color_white);
    $code_generated->setForegroundColor($color_black);
    $code_generated->setFont($font);
    $code_generated->parse($input);
    $drawing = new BCGDrawing('', $color_white);
    $drawing->setBarcode($code_generated);
    $drawing->setRotationAngle($rotation);
    $drawing->setDPI($dpi == 'null' ? null : (int) $dpi);
    $drawing->draw();
    if (intval($output) === 1) {
		header('Content-Type: image/png');
    } elseif (intval($output) === 2) {
		header('Content-Type: image/jpeg');
    } elseif (intval($output) === 3) {
		header('Content-Type: image/gif');
    }

    $drawing->finish(intval($output));
}
?>
