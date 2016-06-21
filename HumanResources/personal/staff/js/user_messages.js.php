<script>
//---------------------------
// programmer:	Mahdipour
// create Date:	90.09.25
//---------------------------

UserMsg.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",
    prof : '<?= $prof ?>' ,
    emp : '<?= $emp ?>',
    worker : '<?= $worker ?>',
    gharardadi : '<?= $gharardadi ?>',
	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};
 
function UserMsg()
{	
    this.form = this.get("form_UserMsg");
    
    this.ProfFieldSet = new Ext.form.FieldSet({
            collapsible: true,
            title : "پیامهای مربوط به هیئت علمی ",
            renderTo : this.get("FS_PROF"),
            contentEl : this.get("FS_PROF1"),
            autoHeight : true,
            width : 980 ,
            collapsed :true,
            listeners : {
                expand : function(){                            
                              WarningMsgObject.profgrid.render("ProfWarningMsgDIV");
                              WarningMsgObject.profigrid.render("ProfIcludedChildrenDIV");
                                                    
                }
		}
          
            
        });

        this.EmpFieldSet = new Ext.form.FieldSet({
            collapsible: true,
            title : "پیام های کارمندان ",
            renderTo : this.get("FS_EMP"),
            contentEl : this.get("FS_EMP1"),
            autoHeight : true,
            width : 980 ,
            collapsed :true,
            listeners : {
                expand : function(){
                              WarningMsgObject.empgrid.render("EmpWarningMsgDIV");
                              WarningMsgObject.empigrid.render("EmpIcludedChildrenDIV");
                             // WarningMsgObject.empeglgrid.render("EmpEglDIV");                             
                              WarningMsgObject.empretgrid.render("EmpRetMsgDIV");
                WarningMsgObject.emptarfigrid.render("EmpTarfiMsgDIV");
                }
            }

        });

        this.WorkerFieldSet = new Ext.form.FieldSet({
            collapsible: true,
            title : " پیام های روزمزد بیمه ای ها ",
            renderTo : this.get("FS_WORK"),
            contentEl : this.get("FS_WORK1"),
            autoHeight : true,
            width : 980 ,
            collapsed :true,
            listeners : {
                expand : function(){
                             WarningMsgObject.workergrid.render("WorkerWarningMsgDIV");
                             WarningMsgObject.workerigrid.render("WorkerIcludedChildrenDIV");
                             WarningMsgObject.workerretgrid.render("WRetMsgDIV"); 
                }
            }


        });

        this.GharardadiFieldSet = new Ext.form.FieldSet({
            collapsible: true,
            title : "پیام های قراردادی ها",
            renderTo : this.get("FS_GH"),
            contentEl : this.get("FS_GH1"),
            autoHeight : true,
            width : 980 ,
            collapsed :true,
            listeners : {
                expand : function(){
                            WarningMsgObject.ghgrid.render("GhWarningMsgDIV");
                            WarningMsgObject.ghigrid.render("GhIcludedChildrenDIV");
							WarningMsgObject.ghretgrid.render("GRetMsgDIV");  
                          WarningMsgObject.ghretgrid.render("GTarfiMsgDIV");                                
                }
            }


        });
        
        this.EstelajiFieldSet = new Ext.form.FieldSet({
            collapsible: true,
            title : "هشدار",
            renderTo : this.get("FS_ES"),
            contentEl : this.get("FS_ES1"),
            autoHeight : true,
            width : 850 ,
            collapsed :false

        });
  
	this.afterLoad();
}


UserMsg.profopRender = function(value, p, record)
{
	var st = "";
	st += "<div  title='ویرایش اطلاعات' class='edit' onclick='WarningMsgObject.profeditInfo();' " +
			"style='float:right;background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;width:50%;height:16'></div>";
	
	return st;
}

UserMsg.profopRenderIC = function(value, p, record)
{
	var st = "";
	st += "<div  title='ویرایش اطلاعات' class='edit' onclick='WarningMsgObject.profeditICInfo();' " +
			"style='float:right;background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;width:50%;height:16'></div>";

	return st;
}


UserMsg.prototype.profeditInfo = function()
{
        
    var record = this.profgrid.getSelectionModel().getLastSelected();

	framework.OpenPage(this.address_prefix + "../../writs/ui/view_writ.php", "مشاهده حکم",
		 {
			 WID : record.data.writ_id,
             STID : record.data.staff_id,
             WVER : record.data.writ_ver ,
			 PID : record.data.PersonID,
             ExeDate : record.data.execute_date
		 });
}

UserMsg.prototype.profeditICInfo = function()
{
	var record = this.profigrid.getSelectionModel().getLastSelected();

	framework.OpenPage(this.address_prefix + "../../persons/ui/new_person.php", "مشاهده وابستگان",
		 {
			 Q0 : record.data.PersonID
		 });
}	


//...................................................emp...........................

UserMsg.empopRender = function(value, p, record)
{
	var st = "";
	st += "<div  title='ویرایش اطلاعات' class='edit' onclick='WarningMsgObject.empeditInfo();' " +
			"style='float:right;background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;width:50%;height:16'></div>";

	return st;
}

UserMsg.empopRenderIC = function(value, p, record)
{
	var st = "";
	st += "<div  title='ویرایش اطلاعات' class='edit' onclick='WarningMsgObject.empeditICInfo();' " +
			"style='float:right;background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;width:50%;height:16'></div>";

	return st;
}
UserMsg.empopRenderegl = function(value, p, record)
{
	var st = "";
	st += "<div  title='ویرایش اطلاعات' class='edit' onclick='WarningMsgObject.empediteglInfo();' " +
			"style='float:right;background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;width:50%;height:16'></div>";

	return st;
}


UserMsg.prototype.empeditInfo = function()
{

    var record = this.empgrid.getSelectionModel().getLastSelected();

	framework.OpenPage(this.address_prefix + "../../writs/ui/view_writ.php", "مشاهده حکم",
		 {
			 WID : record.data.writ_id,
             STID : record.data.staff_id,
             WVER : record.data.writ_ver ,
			 PID : record.data.PersonID,
             ExeDate : record.data.execute_date
		 });
}

UserMsg.prototype.empeditICInfo = function()
{
	var record = this.empigrid.getSelectionModel().getLastSelected();

	framework.OpenPage(this.address_prefix + "../../persons/ui/new_person.php", "مشاهده وابستگان",
		 {
			 Q0 : record.data.PersonID
		 });
}

/*UserMsg.prototype.empediteglInfo = function()
{
	var record = this.empeglgrid.getSelectionModel().getLastSelected();

	framework.OpenPage(this.address_prefix + "../../persons/ui/new_person.php", "مشخصات فردی",
		 {
			 Q0 : record.data.PersonID
		 });
}*/
//.............................................. worker ...................................
UserMsg.workeropRender = function(value, p, record)
{
	var st = "";
	st += "<div  title='ویرایش اطلاعات' class='edit' onclick='WarningMsgObject.workereditInfo();' " +
			"style='float:right;background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;width:50%;height:16'></div>";

	return st;
}

UserMsg.workeropRenderIC = function(value, p, record)
{
	var st = "";
	st += "<div  title='ویرایش اطلاعات' class='edit' onclick='WarningMsgObject.workereditICInfo();' " +
			"style='float:right;background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;width:50%;height:16'></div>";

	return st;
}

UserMsg.prototype.workereditInfo = function()
{

    var record = this.workergrid.getSelectionModel().getLastSelected();

	framework.OpenPage(this.address_prefix + "../../writs/ui/view_writ.php", "مشاهده حکم",
		 {
			 WID : record.data.writ_id,
             STID : record.data.staff_id,
             WVER : record.data.writ_ver ,
			 PID : record.data.PersonID,
             ExeDate : record.data.execute_date
		 });
}

UserMsg.prototype.workereditICInfo = function()
{
	var record = this.workerigrid.getSelectionModel().getLastSelected();

	framework.OpenPage(this.address_prefix + "../../persons/ui/new_person.php", "مشاهده وابستگان",
		 {
			 Q0 : record.data.PersonID
		 });
}


//................................. gharardadi .................................

UserMsg.ghopRender = function(value, p, record)
{
	var st = "";
	st += "<div  title='ویرایش اطلاعات' class='edit' onclick='WarningMsgObject.gheditInfo();' " +
			"style='float:right;background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;width:50%;height:16'></div>";

	return st;
}

UserMsg.ghopRenderIC = function(value, p, record)
{
	var st = "";
	st += "<div  title='ویرایش اطلاعات' class='edit' onclick='WarningMsgObject.gheditICInfo();' " +
			"style='float:right;background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;width:50%;height:16'></div>";

	return st;
}

UserMsg.prototype.gheditInfo = function()
{

    var record = this.ghgrid.getSelectionModel().getLastSelected();

	framework.OpenPage(this.address_prefix + "../../writs/ui/view_writ.php", "مشاهده حکم",
		 {
			 WID : record.data.writ_id,
             STID : record.data.staff_id,
             WVER : record.data.writ_ver ,
			 PID : record.data.PersonID,
             ExeDate : record.data.execute_date
		 });
}

UserMsg.prototype.gheditICInfo = function()
{
	var record = this.ghigrid.getSelectionModel().getLastSelected();

	framework.OpenPage(this.address_prefix + "../../persons/ui/new_person.php", "مشاهده وابستگان",
		 {
			 Q0 : record.data.PersonID
		 });
}




</script>












