<script>
//---------------------------
// programmer:	Mahdipour
// create Date:		91.06
//---------------------------   

    PersonSalaryReceipt.prototype = {
        TabID: '<?= $_REQUEST["ExtTabID"] ?>',
        address_prefix: "<?= $js_prefix_address ?>",
        get: function (elementID) {
            return findChild(this.TabID, elementID);
        }
    };
    function PersonSalaryReceipt()
    {

        this.form = this.get("form_PersonReceipt");           
        this.afterLoad();
    }

  


 PersonSalaryReceipt.opRender = function(value, p, record)
{
	var st = "";
	st += "<div  title='چاپ فیش' class='print' onclick='PersonSalaryReceiptObject.SalaryPrint();' " +
			"style='float:right;background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;width:50%;height:16'></div>";
	
	return st;
}

PersonSalaryReceipt.prototype.SalaryPrint = function()
{
	var record = this.grid.getSelectionModel().getLastSelected();
	
	window.open(PersonSalaryReceiptObject.address_prefix + "../../../salary/payment/ui/print_salary_receipt.php" +
		"?staff_id=" + record.data.staff_id + "&pay_year=" + record.data.pay_year + "&pay_month=" + record.data.pay_month + "&payment_type=" + record.data.payment_type  ,
		"",'directories=0,location=0,menubar=1,status=0,toolbar=1,scrollbars,resizable,height=500,width=1200');
}
 

 

</script>












