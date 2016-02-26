<?php
//--------------------------
// developer:	Jafarkhani
// Date:        94.06
//--------------------------

class Manage_Report extends PdoDataAccess{

    public static function BeginReport($includeCss = true) {

        echo '<html>
			<head>
				<META http-equiv=Content-Type content="text/html; charset=UTF-8" >' .
                ($includeCss ? '<link rel="stylesheet" type="text/css" href="/accounting/global/report.css" />' : '') . 
			'</head>
			<body dir="rtl">';
    }

    public static function MakeHeader($title, $fromDate = "", $toDate = "", $pageNumber = "", $returnMode = false, $excel = false) {

		$str = "
			<div style='text-align: right;float:right;width:20%;' align=right><img width='60px' style='padding:2px' 
				" . ($excel ? "" : "src='/framework/icons/logo.jpg'") . "></div>".

			"<div style='float:right;font-family:b titr;font-size:15px;width:60%' align=center>" . 
				"<br>" . SoftwareName .
				"<br>" . $title . "</div>

			<div style='text-align: left;float:right;width:20%;font-family:tahoma;font-size:11px' align=left>
			<br>تاریخ تهیه گزارش : " . DateModules::shNow() . "<br>" ;
		
		$str .=!empty($fromDate) ? "<br>گزارش از تاریخ : " . $fromDate . ($toDate != "" ? " - " . $toDate : "") : "";
        $str .= $pageNumber != "" ? "<br>شماره صفحه :" . $pageNumber : "";
		
		$str .=	"</div>
		<br>";
		
		if($returnMode)
			return $str;
		else
			echo $str;
    }

    public static function PageBreak() {
        echo "<div class=pageBreak></div>";
    }

}

?>
