<?php
//--------------------------
// developer:	Jafarkhani
// Date:        94.06
//--------------------------

class Manage_Report extends PdoDataAccess{

    public static function BeginReport($includeCss = true) {

        echo '<html>
			<head>
				<link rel="stylesheet" type="text/css" href="/generalUI/fonts/fonts.css" />
				<META http-equiv=Content-Type content="text/html; charset=UTF-8" >' .
                ($includeCss ? '<link rel="stylesheet" type="text/css" href="/accounting/global/report.css" />' : '') . 
			'</head>
			<body dir="rtl">';
    }

    public static function PageBreak() {
        echo "<div class=pageBreak></div>";
    }

}

?>
