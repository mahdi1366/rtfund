<script type="text/javascript">
    //-----------------------------
    //	Programmer	: Sh.Jafarkhani
    //	Date		: 97.05
    //-----------------------------

    ExecuteEvent.prototype = {
		TabID : '<?= $_REQUEST["ExtTabID"] ?>',
		address_prefix : "<?= $js_prefix_address ?>",
		EventID : <?= $EventID?>,
		
        get : function(elementID){
            return findChild(this.TabID, elementID);
        }
    };
    
    function ExecuteEvent(){
		
		this.grid = <?=  $grid?>;
		this.grid.getStore().on("load", function(){
			for(var i=0; i<this.totalCount; i++)
			{
				record = this.getAt(i);
				if(record.data.ComputeItemID*1 == 0)
				{
					new Ext.form.CurrencyField({
						renderTo : record.data.CostType == "DEBTOR" ?
							ExecuteEventObj.get("DebtorAmount_" + record.data.RowID) :	
							ExecuteEventObj.get("CreditorAmount_" + record.data.RowID),
						hideTrigger : true,
						name : "amount",
						width : 80
					})
				}
			}
		});
		this.grid.render(this.get("div_grid"));
		this.grid.getStore().proxy.form = this.get("MainForm");
		
    }
    
	ExecuteEvent.DebtorAmountRenderer = function(v,p,r){
		return "<div id=DebtorAmount_" + r.data.RowID + ">"+v+"</div>";
	}
	
	ExecuteEvent.CreditorAmountRenderer = function(v,p,r){
		return "<div id=CreditorAmount_" + r.data.RowID + ">"+v+"</div>";
	}
	
    ExecuteEventObj = new ExecuteEvent();

    ExecuteEvent.prototype.SaveEvent = function(){
		
        mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخیره سازی ...'});
        //mask.show();

		Ext.Ajax.Request({
			url: this.address_prefix + 'ExecuteEvent.data.php?task=ExecuteEvent',
            method: 'POST',
			
			success : function(response){
				mask.hide();
				result = Ext.decode(response.responseText);
                if(result.success)
                {
					
                }
                else
                {
                    Ext.MessageBox.alert("خطا", result.data);
                }
			},
			failure : function(){mask.hide();}
		});
    }
        
</script>
