<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.06
//-----------------------------


require_once (getenv("DOCUMENT_ROOT") . '/framework/MainFrame.php');

?>
<script src="/generalUI/ckeditor/ckeditor.js"></script>
<script>
FrameWorkClass.StartPage = "/HumanResources/FirstPage.php";

var MonthStore = new Ext.data.SimpleStore({
	fields : ['id','title'],
	data : [ 
		["1", "فروردین"],
		["2", "اردیبهشت"],
		["3", "خرداد"],
		["4", "تیر"],
		["5", "مرداد"],
		["6", "شهریور"],
		["7", "مهر"],
		["8", "آبان"],
		["9", "آذر"],
		["10", "دی"],
		["11", "بهمن"],
		["12", "اسفند"]
	]
});

var YearStore = new Ext.data.SimpleStore({
	fields : ['id','title'],
	data : [ 
		["1395", "1395"],
		["1396", "1396"],
		["1397", "1397"],
		["1398", "1398"],
		["1399", "1399"],
		["1400", "1400"],
		["1401", "1401"],
		["1402", "1402"],
		["1403", "1403"],
		["1404", "1404"]
	]
});

</script>