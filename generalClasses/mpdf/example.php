<?php
require_once './header.inc.php';

$html='<p align="center">به نام خدا</p>';
require_once inc_Mpdf;
$mpdf=new mPDF('utf-8');
$html=iconv("utf-8","UTF-8//IGNORE",$html);
$mpdf=new mPDF('ar','A4','','',5,5,5,5,16,13);
$mpdf->SetDirectionality('rtl');
$mpdf->WriteHTML($html);
$mpdf->Output();