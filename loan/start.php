<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.06
//-----------------------------


require_once (getenv("DOCUMENT_ROOT") . '/framework/MainFrame.php');

?>
<script src="/generalUI/ckeditor/ckeditor.js"></script>
<script>
FrameWorkClass.StartPage = "/loan/FirstPage.php";

function LoanRFID(RequestID)
{
	st = RequestID.lpad("0", 7);
	SUM = st[0]*1 + st[1]*2 + st[2]*3 + st[3]*4 + st[4]*5 + st[5]*6 + st[6]*7;
	remain = SUM % 11;
	remain = remain == 10 ? 0 : remain;
	
	code = st + remain;
	return code;
}
</script>