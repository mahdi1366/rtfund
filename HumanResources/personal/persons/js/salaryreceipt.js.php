<script type="text/javascript">
//---------------------------
// programmer:	Mahdipour
// create Date:	90.10
//---------------------------

PersonReceipt.prototype = {
	parent : PersonObject,

	grid : "",
	
	get : function(elementID){
		return findChild(this.form,elementID);
	}
};

function PersonReceipt()
{
	this.form = this.parent.get("form_PersonReceipt");
	
	this.afterLoad();
}

PersonReceipt.opRender = function(value, p, record)
{
	var st = "";
	st += "<div  title='چاپ فیش' class='print' onclick='PersonReceiptObject.SalaryPrint();' " +
			"style='float:right;background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;width:50%;height:16'></div>";
	
	return st;
}

PersonReceipt.prototype.SalaryPrint = function()
{
	var record = this.grid.getSelectionModel().getLastSelected();
	
	window.open(PersonObject.address_prefix + "../../../salary/payment/ui/print_salary_receipt.php" +
		"?staff_id=" + record.data.staff_id + "&pay_year=" + record.data.pay_year + "&pay_month=" + record.data.pay_month + "&payment_type=" + record.data.payment_type  ,
		"",'directories=0,location=0,menubar=1,status=0,toolbar=1,scrollbars,resizable,height=500,width=1200');
}


</script>