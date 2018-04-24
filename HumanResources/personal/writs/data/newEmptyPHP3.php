<?php
//-----------------------------
//	Programmer	: S.Taghizadeh
//	Date		: 94.06
//-----------------------------

require_once '../../header.inc.php';
//require_once '../UserRole/UserRole.class.php';
require_once '../../global/Manage_Report.class.php';
require_once inc_dataReader;
require_once inc_dataGrid;
require_once inc_PDODataAccess;
require_once inc_reportGenerator;


$MaxCountTable = 25;
$title = "گزارش شکوفه های دانشگاه";

if (isset($_REQUEST["show"])) {
    $where = " WHERE 1=1";
    $param = array();
    if (!empty($_POST["from_date"])) {
        $where .= " AND  bs.RegDate >= :fdate";
        $param[":fdate"] = DateModules::shamsi_to_miladi($_POST["from_date"], "-");
    }
    if (!empty($_POST["to_date"])) {
        $where .= " AND  bs.RegDate <= :tdate";
        $param[":tdate"] = DateModules::shamsi_to_miladi($_POST["to_date"], "-");
    }
    
    if (!empty($_POST["InfoID"])) {
        
        if( $_POST["InfoID"] == 101 || $_POST["InfoID"] == 100 ) 
        {
            $where .= " AND bi1.InfoID in (1,2,3,5,10,300,200) ";           
        }
        else {        
            $where .= " AND bi1.InfoID=:PT";
            $param[":PT"] = $_POST["InfoID"];         
        }
    }
     if (!empty($_POST["unitId"])) {
             
        $where .= " AND  (  o.parent_path like :oud2 OR
                            o.parent_path like :oud3 OR
                            o.parent_path like :oud4 OR
                            o.ouid=:ouid OR
                            o.parent_ouid = :ouid 
                               )";

        $param[":ouid"] = $_REQUEST["unitId"];
        $param[":oud2"] = "%," . $_REQUEST["unitId"] . ",%";
        $param[":oud3"] = "%" . $_REQUEST["unitId"] . ",%";
        $param[":oud4"] = "%," . $_REQUEST["unitId"] . "%"; 
                        
    }

    if (!empty($_POST["PersonID"])) {
        $where .= " AND  s.PersonID = :PersonID";
        $param[":PersonID"] = $_POST["PersonID"];
    }

    if (!empty($_POST["from_grade"])) {
        $where .= " AND  bs.grade >= :from_grade";
        $param[":from_grade"] = $_POST["from_grade"];
    }

    if (!empty($_POST["to_grade"])) {
        $where .= " AND  bs.grade <= :to_grade";
        $param[":to_grade"] = $_POST["to_grade"];
    }



    if (!empty($_POST["Educval"])) {
        $where .= " AND  bi2.InfoID = :EducLevel";
        $param[":EducLevel"] = $_POST["Educval"];
    }
    if (!empty($_POST["BaseVal"])) {
        $where .= " AND  bi3.InfoID = :EducBase AND bi3.MasterID = :EducLevel ";
        $param[":EducBase"] = $_POST["BaseVal"];
    }

    if (!empty($_POST["sexVal"])) {
        $where .= " AND  bs.sex = :sex";
        $param[":sex"] = $_POST["sexVal"];
    }

 if (!empty($_POST["status"]) || $_POST["status"] == 0 ) {

        $where .= " AND  bs.status = :status";
        $param[":status"] = $_POST["status"];

    }


    $query = " select bs.* , p.pfname PFName , p.plname LFName  , bi1.title ptype , 
                     concat(o3.ptitle ,' - ', o4.ptitle ) FullUnitTitle , w.ouid ,
 p.mobile_phone,bs.CFName CFName, bs.CLName CLName,  
   case bs.sex when 1 then 'دختر' when 2 then 'پسر' end sexTitle ,
   bi2.title EducLevelTitle,bi3.title EducBaseTitle, bs.grade 
                          
			              from hrmstotal.BestStudent bs inner join persons p on bs.PersonID = p.PersonID 
                                                 inner join hrmstotal.staff s on s.personID = p.PersonID and s.Person_type = p.Person_type
                                                 inner join  hrmstotal.Basic_Info bi1 on bi1.TypeID = 16 and bi1.InfoID = p.person_type
                    left join hrmstotal.org_new_units o3 on  o3.ouid = s.UnitCode
                    left join hrmstotal.org_new_units o4 on o4.ouid = s.ouid 
    INNER JOIN writs w ON (s.staff_id = w.staff_id AND s.last_writ_id  = w.writ_id AND s.last_writ_ver  = w.writ_ver )
    LEFT  JOIN org_new_units o ON (o.ouid = w.ouid)
    LEFT  JOIN org_new_units parentu ON (parentu.ouid = o.parent_ouid)
                                                 inner join hrmstotal.Basic_Info bi2 on bi2.typeid = 58 and bi2.InfoID =  bs.EducLevel
                                                 left join hrmstotal.Basic_Info bi3 on bi3.typeid = 59 and bi3.InfoID =  bs.EducBase and bi3.MasterID = bi2.InfoID $where "
;

$query .= " order by bs.sex ,bs.EducLevel ,bs.EducBase , bs.CLName  " ;
$dataTable = PdoDataAccess::runquery_fetchMode($query, $param);



if($_SESSION["UserID"]=="bmahdipour")
      { 
  
 /*echo(PdoDataAccess::GetLatestQueryString());
 die();   */
    }
     
    $rpg = new ReportGenerator();
    $rpg->excel = !empty($_POST["excel"]);
//$rpg->excel = true;
    $rpg->mysql_resource = $dataTable;
    $rpg->paging = true;
    $rpg->rowNumber = true;
    $rpg->page_size = $MaxCountTable;
    if (!$rpg->excel) {
        Manage_Report::BeginReport();
        $rpg->headerContent = Manage_Report::MakeHeader($title, $_POST["from_date"], $_POST["to_date"], "", true);
    }

    function Remove1Render($row, $val) {
        $BSID = $row["BSID"];
        $extension = $row["PicFileType"];
//$t="<img src='/mystorage/BestStuDocument/PicDoc/".$BSID.".".$extension."'>";
//echo $t;
        // return "<img src='/mystorage/BestStuDocument/PicDoc/".$BSID.".".$extension."'>";
        return "<img  height=42 width=42 src='/ServiceManagement/StaffUtility/ui/showImage.php?BSID=" . $BSID . "'>";
    }

    $rpg->addColumn("نام ولی", "PFName");
    $rpg->addColumn("نام خانوادگی ولی", "LFName");
    $rpg->addColumn("نام فرزند", "CFName");
    $rpg->addColumn("نام خانوادگی فرزند", "CLName");
    $rpg->addColumn("جنسیت", "sexTitle");
    $rpg->addColumn("دوره تحصیلی", "EducLevelTitle");
    $rpg->addColumn("پایه", "EducBaseTitle");
    $col = $rpg->addColumn("معدل کل", "grade");
    $rpg->addColumn("عکس فرزند", 'PicFileType', 'Remove1Render');
    $rpg->generateReport();
    die();
}
?>
<script type="text/javascript">

    ReportBestStu.prototype = {
        TabID : '<?= $_REQUEST["ExtTabID"] ?>',
        address_prefix : "<?= $js_prefix_address ?>",
	
        get : function(elementID){
            return findChild(this.TabID, elementID);
        }
    };

    function ReportBestStu()
    {
        this.PersonStore =  new Ext.data.Store({
            proxy: {type: 'jsonp',
                url: this.address_prefix + '../data/BestChildren.data.php?task=GetPersons',
                reader: {root: 'rows',totalProperty: 'totalCount'}
            },
            fields : ['PersonID','staff_id','pfname','plname','unit_name'],
            pageSize: 10
        });
        var SexTypes = Ext.create('Ext.data.ArrayStore', {
            fields: ['sexVal', 'title'],
            data : [
                ['1','دختر'],                               
                ['2','پسر'],                                                      
            ]
        });  
        var EducLevelTypes = Ext.create('Ext.data.ArrayStore', {
            fields: ['Educval', 'title'],
            data : [
                ['1','ابتدایی'],                               
                ['2','متوسط مرحله اول'],
                ['3','متوسط مرحله دوم']                                                         
            ]
        });  
        
							 
        var EducBaseTypes = Ext.create('Ext.data.ArrayStore', {
            fields: ['parentID','BaseVal', 'title'],
            data : [
                ['1','1','اول'],                               
                ['1','2','دوم'],
                ['1','3','سوم'],
                ['1','4','چهارم'],
                ['1','5','پنجم'],
                ['1','6','ششم']                                                     ,
                ['2','7','اول'],
                ['2','8','دوم'],
						
                ['3','9','اول'],
                ['3','10','دوم'],
                ['3','11','سوم'],
                ['3','12','پیش دانشگاهی'],
						
						
            ]
        });  
        this.filterPanel = new Ext.form.Panel({
            renderTo : this.get('DivInfo'),
            width : 600,
            titleCollapse : true,
            frame : true,
            collapsible : true,
		 
            title : "فیلتر شکوفه ها",
            fieldDefaults: {
                labelWidth: 120
            },
            layout: {
                type: 'table',
                columns: 2
            },
            items : [{
			
                    xtype: "combo",
                    itemId: "PersonID",
                    name: "PersonID",
                    store: this.PersonStore,
                    //displayField: 'fullname',
                    valueField: 'PersonID',
                    hiddenName: 'PersonID',
                    pageSize: 10,
                    width: 530,
                    typeAhead: false,
                    fieldLabel:  "انتخاب فرد",
                    allowBlank : false,	
                    //beforeLabelTextTpl: required,
                    labelWidth: 120,
                    colspan: 2,
                    // emptyText: 'Ø¬Ø³ØªØ¬ÙˆÙŠ Ù�Ø±Ø¯ ...',
                    listConfig: {
                        loadingText: 'در حال جستجو...',
                        emptyText: 'فاقد اطلاعات',
                        itemCls : "search-item"
                    },
                    displayTpl: new Ext.XTemplate('<tpl for=".">{PersonID}</tpl>'),
                    tpl: new Ext.XTemplate(
                    '<table cellspacing="0" width="100%"><tr class="x-grid3-header">'
                    , '<td height="23px">کد پرسنلی</td>'
                    ,'<td>کد شخص</td>'
                    , '<td height="23px">نام</td>'
                    , '<td>نام خانوادگی</td>'
                    ,'<td>واحد محل خدمت</td>'
                    , '</tr>',
                    '<tpl for=".">',
                    '<tr class="search-item">'
                    ,'<td> {PersonID}</td>'
                    ,'<td>{staff_id}</td>'
                    , '<td>{pfname}</td>'
                    , '<td>{plname}</td>'
                    , '<td>{unit_name}</td>'
                    , '</tr>'
                    , '</tpl>'
                    , '</table>'),
                    listeners: {
                        select: function (combo, records) {
                            Ext.getCmp("PersonID").setValue(records[0].data.PersonID);

                        }
                    }



                }, {
                    xtype : "combo",
                    store: new Ext.data.Store({
                        pageSize: 10,
                        proxy:{
                            type: 'jsonp',
                            url: this.address_prefix + '../data/BestChildren.data.php?task=GetPersonType',
                            reader: {root: 'rows',totalProperty: 'totalCount'}
                        },
                        fields : ['Title','InfoID']
                    }),
                    displayField: 'Title',
                    valueField : "InfoID",
                    hiddenName : "InfoID",
                    itemId : "InfoID" , 
                    fieldLabel : "نوع فرد",
                    typeAhead: false,
                    listConfig: {
                        loadingText: 'در حال جستجو...',
                        emptyText: 'فاقد اطلاعات',
                        itemCls : "search-item"
                    },
                    pageSize:10,
                    width : 280,
                    colspan : 2
                }, {
                    xtype : "trigger",
                    name : "unitId",
                    inputId:"unitId",
                    width:300,
                    colspan: 2,
                    fieldLabel : "واحد محل خدمت",
											
                    onTriggerClick : function(){

                        var retVal = showLOV("/ServiceManagement/global/LOV/OrgUnitLOV.php", 900, 550);
												
                        if(retVal != '')
                        {
                            this.setValue(retVal);
                        }
                    } ,											
                    width:200,
                    triggerCls:'x-form-search-trigger'
                },
				{
                    xtype : "shdatefield",
                    name : "from_date",
                    fieldLabel : "تاریخ درخواست"
                },{
                    xtype : "shdatefield",
                    name : "to_date",
                    fieldLabel : "تا"
                },
               
                
                {
                    xtype : "numberfield",
                    name : "from_grade",
                    fieldLabel : "معدل",
                    hideTrigger : true
                },{
                    xtype : "numberfield",
                    name : "to_grade",
                    fieldLabel : "تا",
                    hideTrigger : true				
                }, {
                    xtype : "combo",
                    colspan: 2,                                          
                    hiddenName:"Educval",
                    fieldLabel : "دوره تحصیلی",
                    store: EducLevelTypes,
                    valueField: 'Educval',

                    displayField: 'title',
                    listeners : {
                        select : function(combo,records){
                            var record = records[0];
                            var elem = this.up('form').down('[hiddenName=BaseVal]');
                            elem.setValue();
                            elem.getStore().clearFilter();
                            elem.getStore().filter('parentID',record.data.Educval)
																									
                        }
                    }
                },
                {
                    xtype : "combo",
                    colspan: 2,
                    hiddenName:"BaseVal",                                    
                    fieldLabel : "پایه تحصیلی",
                    store: EducBaseTypes,
                    valueField: 'BaseVal',
                    displayField: 'title'
                },  {
                    xtype : "combo",
                    colspan: 2,
                    // name:"sex",                                    
                    fieldLabel :"جنسیت",
                    store: SexTypes,
                    valueField: 'sexVal',
                    displayField: 'title',
                    hiddenName:'sexVal'

                },{
			xtype: 'radiogroup',                                           
			fieldLabel: 'وضعیت',
			allowBlank : false ,
                        //hiddenName:'status12',
                        //name:'status142',
                        itemId : 'cmp_status',
			colspan : 2,    
			width : 400 , 
			items:
				[ {boxLabel: 'بررسی نشده', name: 'status', inputValue: 0,
                                	listeners:{change: function(){if(this.checked) this.up('form').down('[itemId=status]').setValue(this.getSubmitValue());}}
                                    },
                                {boxLabel: 'تایید', name: 'status', inputValue: 1,
                                listeners:{change: function(){if(this.checked) this.up('form').down('[itemId=status]').setValue(this.getSubmitValue());}}},
                                {boxLabel: 'عدم تایید', name: 'status', inputValue: 2,
                                listeners:{change: function(){if(this.checked) this.up('form').down('[itemId=status]').setValue(this.getSubmitValue());}}}]
		},{xtype : 'hidden' , name :'status',itemId :'status',value : 'aa' }],
            buttons :  [{
                    text : "مشاهده گزارش",
                    //handler : Ext.bind(ManageRequestObject.showReport,this),
                    handler : function(){ReportBestStuObject.showReport()},
		
                    iconCls : "report"
                },{
                    text : "خروجی excel",
                    //handler : Ext.bind(this.showReport,this),
                    handler : function(){ReportBestStuObject.showReport()},
                    listeners : {
                        click : function(){
                            ReportBestStuObject.get('excel').value = "true";
                        }
                    },
                    iconCls : "excel"
                },{
                    iconCls : "clear",
                    text : "پاک کردن فرم",
                    handler : function(){
                        this.up("form").getForm().reset();
                        ReportBestStuObject.get("mainForm").reset();
                    }
                }]
        });
	
	
    }

    var ReportBestStuObject = new ReportBestStu();

    ReportBestStu.prototype.showReport = function(btn, e)
    {
	
        this.form = this.get("mainForm");
        this.form.target = "_blank";
        this.form.method = "POST";
        this.form.action =  this.address_prefix + "RptBestStudent.php?show=true";
        this.form.submit();
        this.get("excel").value ="";
        return;
    }
                                             
</script>
<br>
<center>
    <form id="mainForm">
        <div id="DivInfo"></div><br>
        <input type="hidden" name="excel" id="excel">
    </form>
</center>

