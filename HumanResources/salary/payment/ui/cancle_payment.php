<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	93.05
//---------------------------
require_once '../../../header.inc.php';  
require_once inc_dataReader;
require_once '../js/cancle_payment.js.php';

?>
<form id="mainForm">
    <center>		
        <div id="mainpanel"></div><br>
		<div align="right" class="panel" id="result" style="display:none;width: 580px;">			
			<font style="font-size:'10px';font-weight: bold" color="#194775" > &nbsp;&nbsp;&nbsp;
			نتیجه ابطال فیش ها 
			<hr size="3" width="85%" noshade align="right" style="color:#66A3E0" >				
			</font>
				<div id="result_data"></div>
		</div><br><br>
    </center>    
	
</form>