<?php

require_once("phpExcelReader.php");

$data = new Spreadsheet_Excel_Reader();
$data->setOutputEncoding('utf-8');
$data->setRowColOffset(0);
$data->read('test.xls');

echo $data->ConvertToHtmlTable();

?>
