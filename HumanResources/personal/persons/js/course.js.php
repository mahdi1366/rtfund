<script type="text/javascript">
//---------------------------
// programmer:	Jafarkhani
// create Date:	90.03
//---------------------------

PersonCourse.prototype = {
	parent : PersonObject,
	grid : "",

	get : function(elementID){
		return findChild(this.form, elementID); 
	}
    

};

function PersonCourse()
{
	this.form = this.parent.get("courseForm");
	
	this.sumTotalHours = new Ext.panel.Panel({
		renderTo : this.get("totalHours"),
		contentEl : document.getElementById("Sum_TBL"),
		width : 780,
		height : 40,
		frame : true /*,
		
		items :[{
				xtype : "displayfield",
				fieldLabel :"جمع ساعات",
				colspan: 3 , 
				style :"font-weight:bold;font-family: 'B nazanin';font-size: 16px;text-align:left;float:left ", 				  
				labelWidth : 100,
				itemId : "cmp_th",
				width : 550
			}]*/
	}); 
	
	this.afterLoad();
}

</script>