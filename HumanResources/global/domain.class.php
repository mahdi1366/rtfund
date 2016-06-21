<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	94.11
//---------------------------
require_once (inc_component);
require_once (inc_PDODataAccess);

class manage_domains
{
	public static function DRP_SalaryParam ($dropdownName, $selectedID = "", $extraRow = "",$style="")
	{
		$datasource = array(
            array("id"=>"NONE", "value" => "Nothing"),
			array("id"=>"STATE", "value" => "استان"),
			array("id"=>"CITY", "value" => "شهر"),
			array("id"=>"EDUCLEVEL", "value" => "مدرک تحصیلی"),
			array("id"=>"SCIENCELEVEL", "value" => "مرتبه علمی"),
			array("id"=>"SGROUP", "value" => "گروه شروع"),
            array("id"=>"EGROUP", "value" => "گروه پایان"),
            array("id"=>"MARITALS","value" => "وضعیت تاهل"),
            array("id"=>"DUTY_YEAR","value" => "سال خدمت"),
                    array("id"=>"GRADE","value" => "رتبه"),
                    array("id"=>"SUPERVISION" , "value" => "نوع سرپرستی" ));

		$obj = new DROPDOWN();

		$obj->datasource = $datasource;
		$obj->valuefield = "%id%";
		$obj->textfield = "%value%";
		$obj->Style = 'class="x-form-text x-form-field" style="'.$style.'"';
		$obj->id = $dropdownName;

		if(!empty($extraRow))
			$obj->datasource = array_merge(array(array("id" => "-1", "value" => $extraRow)),$obj->datasource);
		return $obj->bind_dropdown($selectedID);
        
	}
    
    public static function GETALL_SalaryParam()
    {
         $datasource = array(
            array("id"=>"NONE", "value" => "Nothing"),
			array("id"=>"STATE", "value" => "استان"),
			array("id"=>"CITY", "value" => "شهر"),
			array("id"=>"EDUCLEVEL", "value" => "مدرک تحصیلی"),
			array("id"=>"SCIENCELEVEL", "value" => "مرتبه علمی"),
			array("id"=>"SGROUP", "value" => "گروه شروع"),
            array("id"=>"EGROUP", "value" => "گروه پایان"),
            array("id"=>"MARITALS","value" => "وضعیت تاهل"),
            array("id"=>"DUTY_YEAR","value" => "سال خدمت"),
            array("id"=>"GRADE","value" => "رتبه"),
            array("id"=>"SUPERVISION" , "value" => "نوع سرپرستی" ));

        return $datasource ; 
        
    }

   
    public static function DRP_months($dropdownName, $selectedID = "", $extraRow = "",$style="")
	{
		$datasource = array(
			array("id"=>"1", "value" => "فروردین"),
			array("id"=>"2", "value" => "اردیبهشت"),
			array("id"=>"3", "value" => "خرداد"),
			array("id"=>"4", "value" => "تیر"),
			array("id"=>"5", "value" => "مرداد"),
			array("id"=>"6", "value" => "شهریور"),
			array("id"=>"7", "value" => "مهر"),
			array("id"=>"8", "value" => "آبان"),
			array("id"=>"9", "value" => "آذر"),
			array("id"=>"10", "value" => "دی"),
			array("id"=>"11", "value" => "بهمن"),
			array("id"=>"12", "value" => "اسفند"));

		$obj = new DROPDOWN();

		$obj->datasource = $datasource;
		$obj->valuefield = "%id%";
		$obj->textfield = "%value%";
		$obj->Style = 'class="x-form-text x-form-field" style="'.$style.'"';
		$obj->id = $dropdownName;

		if(!empty($extraRow))
			$obj->datasource = array_merge(array(array("id" => "-1", "value" => $extraRow)),$obj->datasource);
		return $obj->bind_dropdown($selectedID);
	}

	public static function DRP_State_City($formName, $stateFieldName, $cityFieldName
		, $stateSelectedID = "", $citySelectedID = ""
		, $stateExtraRow = "", $cityExraRow = "", $stateWidth = "")
	{
		$obj = new MaserDetail_DROPDOWN();
		
		$obj->Master_datasource = PdoDataAccess::runquery("select state_id,ptitle from states");
		if(!empty($stateExtraRow))
			$obj->Master_datasource = array_merge(array(array("state_id" => "-1", "ptitle" => $stateExtraRow)),$obj->Master_datasource);
		$obj->Master_id = $stateFieldName;
		$obj->Master_valuefield = "%state_id%";
		$obj->Master_textfield = "%ptitle%";
		$obj->Master_formName = $formName;
		
		$obj->Detail_datasource = PdoDataAccess::runquery("select * from cities");
		if(!empty($cityExraRow))
			$obj->Detail_datasource = array_merge(array(array("state_id" => "-1", "ptitle" => $cityExraRow)),$obj->Detail_datasource);
		$obj->Detail_id = $cityFieldName;
		$obj->Detail_valuefield = "%city_id%";
		$obj->Detail_textfield = "%ptitle%";
		$obj->Detail_masterfield = "state_id";
		
		return $obj->bind_dropdown_returnObjects($stateSelectedID, $citySelectedID);
	}
	public static function DRP_country_university($formName, $countryFieldName, $universityFieldName
		, $countrySelectedID = "", $universitySelectedID = ""
		, $countryExtraRow = "", $universityExraRow = "")
	{
		$obj = new MaserDetail_DROPDOWN();
		
		$obj->Master_formName = $formName;
		
		$obj->Master_datasource = PdoDataAccess::runquery("select country_id,ptitle from countries");
		if(!empty($countryExtraRow))
			$obj->Master_datasource = array_merge(array(array("country_id" => "-1", "ptitle" => $countryExtraRow)),$obj->Master_datasource);
		$obj->Master_id = $countryFieldName;
		$obj->Master_valuefield = "%country_id%";
		$obj->Master_textfield = "%ptitle%";
		$obj->Master_Width = "200";
		
		/*$obj->Detail_datasource = PdoDataAccess::runquery("select university_id,ptitle,country_id from universities");
		if(!empty($universityExraRow))
			$obj->Detail_datasource = array_merge(array(array("university_id" => "-1", "ptitle" => $universityExraRow)),$obj->Detail_datasource);*/
		$obj->Detail_storeUrl = "global/domain.data.php?task=searchuniversities";
		$obj->Detail_id = $universityFieldName;
		$obj->Detail_valuefield = "%university_id%";
		$obj->Detail_textfield = "%ptitle%";
		$obj->Detail_masterfield = "country_id";
		
		
		return $obj->bind_dropdown_returnObjects($countrySelectedID, $universitySelectedID);
	}
    public static function DRP_Universities($dropdownName, $selectedID = "", $extraRow = "", $width = "", $slaveMode = true)
	{
		$dt = PdoDataAccess::runquery("select university_id,ptitle,country_id
		                                from universities");
				
		$obj = new DROPDOWN();
		
		$obj->datasource = $dt;
		$obj->valuefield = "%university_id%";
		$obj->textfield = "%ptitle%";
		$obj->width = $width;
		$obj->Style = 'class="x-form-text x-form-field" style="width:'.$width.'" ';
		$obj->id = $dropdownName;
		$obj->masterfield = "country_id";

		if(!empty($extraRow))
			$obj->datasource = array_merge(array(array("university_id" => "-1", "ptitle" => $extraRow)),$obj->datasource);

		return $obj->bind_dropdown($selectedID);
		
	}
	
	public static function DRP_PersonTypeNoAccess($dropdownName, $selectedID = "", $style="", $extraRow = "", $event = "")
	{
		$query = "
			select *
			from Basic_Info 
			where TypeID = 16
			order by InfoID ";
		
		$obj = new DROPDOWN();

		$temp = PdoDataAccess::runquery($query);
		if($extraRow != "")
		$temp = array_merge(array(array("InfoID" => -1,"Title" => $extraRow)), $temp);
		$obj->datasource = $temp;
		$obj->valuefield = "%InfoID%";
		$obj->textfield = "%Title%";
		$obj->Style = 'class="x-form-text x-form-field" style="'.$style.'" ';
		$obj->Event = $event;
		$obj->id = $dropdownName;

		return $obj->bind_dropdown($selectedID);

	}

	 public static function DRP_TaxType($dropdownName, $selectedID = "", $extraRow = "", $width = "", $personTyp )
	{
		$dt = PdoDataAccess::runquery(" select tax_table_type_id , title , person_type
											 from tax_table_types where person_type in (100 , ".$personTyp." ) ");

		$obj = new DROPDOWN();

		$obj->datasource = $dt;
		$obj->valuefield = "%tax_table_type_id%";
		$obj->textfield = "%title%";
		$obj->width = $width;
		$obj->Style = 'class="x-form-text x-form-field" style="width:'.$width.'" ';
		$obj->id = $dropdownName;
		

		if(!empty($extraRow))
			$obj->datasource = array_merge(array(array("tax_table_type_id" => "-1", "title" => $extraRow)),$obj->datasource);

		return $obj->bind_dropdown($selectedID);

	}

    public static function getAll_TaxType($personTyp)
	 {

         return  PdoDataAccess::runquery(" select tax_table_type_id , title , person_type
											 from tax_table_types where person_type in (100 , ".$personTyp." ) ");

	 }
     
	public static function DRP_Dependencies($dropdownName, $selectedID = "", $extraRow = "",$style="")
	{
		$query = "select * from BaseInfo where TypeID=54";
		
		$obj = new DROPDOWN();

		$temp = PdoDataAccess::runquery($query);
		
		$obj->datasource = $temp;
		$obj->valuefield = "%InfoID%";
		$obj->textfield = "%InfoDesc%";
		$obj->Style = 'class="x-form-text x-form-field" style="'.$style.'"';
		$obj->id = $dropdownName;

		if(!empty($extraRow))
			$obj->datasource = array_merge(array(array("country_id" => "-1", "ptitle" => $extraRow)),$obj->datasource);
		return $obj->bind_dropdown($selectedID);		
	}
	public static function DRP_EmpState($dropdownName, $selectedID = "", $extraRow = "",$style="")
	{
		$query = "select * from Basic_Info where TypeID=3";

		$obj = new DROPDOWN();

		$temp = PdoDataAccess::runquery($query);

		$obj->datasource = $temp;
		$obj->valuefield = "%InfoID%";
		$obj->textfield = "%Title%";
		$obj->Style = 'class="x-form-text x-form-field" style="'.$style.'"';
		$obj->id = $dropdownName;

		if(!empty($extraRow))
			$obj->datasource = array_merge(array(array("InfoID" => "-1", "Title" => $extraRow)),$obj->datasource);
		return $obj->bind_dropdown($selectedID);

	}
    public static function DRP_State($dropdownName, $selectedID = "", $extraRow = "",$style="")
	{
		$query = "select * from states ";

		$obj = new DROPDOWN();

		$temp = PdoDataAccess::runquery($query);

		$obj->datasource = $temp;
		$obj->valuefield = "%state_id%";
		$obj->textfield = "%ptitle%";
		$obj->Style = 'class="x-form-text x-form-field" style="'.$style.'"';
		$obj->id = $dropdownName;

		if(!empty($extraRow))
			$obj->datasource = array_merge(array(array("state_id" => "-1", "ptitle" => $extraRow)),$obj->datasource);
		return $obj->bind_dropdown($selectedID);

	}
    public static function GETALL_State()
    {
        $query = "select * from states ";
        return PdoDataAccess::runquery($query);
    }
    public static function DRP_Science_Level($dropdownName, $selectedID = "", $extraRow = "",$style="")
	{
		$query = "select * from Basic_Info where TypeID=8";

		$obj = new DROPDOWN();

		$temp = PdoDataAccess::runquery($query);

		$obj->datasource = $temp;
		$obj->valuefield = "%InfoID%";
		$obj->textfield = "%Title%";
		$obj->Style = 'class="x-form-text x-form-field" style="'.$style.'"';
		$obj->id = $dropdownName;

		if(!empty($extraRow))
			$obj->datasource = array_merge(array(array("InfoID" => "-1", "Title" => $extraRow)),$obj->datasource);
		return $obj->bind_dropdown($selectedID);

	}
	
	public static function DRP_Post_Type($dropdownName, $selectedID = "", $extraRow = "",$style="")
	{
		$query = "select * from Basic_Info where TypeID=27 ";

		$obj = new DROPDOWN();

		$temp = PdoDataAccess::runquery($query);

		$obj->datasource = $temp;
		$obj->valuefield = "%InfoID%";
		$obj->textfield = "%Title%";
		$obj->Style = 'class="x-form-text x-form-field" style="'.$style.'"';
		$obj->id = $dropdownName;

		if(!empty($extraRow))
			$obj->datasource = array_merge(array(array("InfoID" => "-1", "Title" => $extraRow)),$obj->datasource);
		return $obj->bind_dropdown($selectedID);
	}
	
    public static function GETALL_Science_Level()
    {   
        $query = "select * from Basic_Info where TypeID=8 ";
        return PdoDataAccess::runquery($query);
    }
    public static function DRP_Educ_Level($dropdownName, $selectedID = "", $extraRow = "",$style="")
	{
		$query = "select * from Basic_Info where TypeID=35";

		$obj = new DROPDOWN();

		$temp = PdoDataAccess::runquery($query);

		$obj->datasource = $temp;
		$obj->valuefield = "%InfoID%";
		$obj->textfield = "%Title%";
		$obj->Style = 'class="x-form-text x-form-field" style="'.$style.'"';
		$obj->id = $dropdownName;

		if(!empty($extraRow))
			$obj->datasource = array_merge(array(array("InfoID" => "-1", "Title" => $extraRow)),$obj->datasource);
		return $obj->bind_dropdown($selectedID);

	}
    public static function GETALL_Educ_Level()
    {
        $query = "select * from Basic_Info where TypeID=35 ";
        return PdoDataAccess::runquery($query);
    }   
     public static function GETALL_Grade()
    {
        $query = "select * from Basic_Info where TypeID=44 ";
        return PdoDataAccess::runquery($query);
    } 
    public static function GETALL_Supervision()
    {
        $query = "select * from Basic_Info where TypeID=42 ";
        return PdoDataAccess::runquery($query);
    }
    public static function GETALL_Payment_Type()
    {    ///echo "sdsd"  ; die();
         $query = " select * from Basic_Info where TypeID= 50 and InfoID in (".manage_access::getValidPayments().")";
		
		/* if($_SESSION['UserID'] == 'darvish-re' || $_SESSION['UserID'] == 'jafarkhani' ) 
		 {
			$query = " select * from Basic_Info where TypeID= 50  AND InfoID = 9 " ; 
		 }*/
			
         return PdoDataAccess::runquery($query);        
    }
	public static function DRP_Marital_Status($dropdownName, $selectedID = "", $extraRow = "",$style="")
	{
		$query = "select * from Basic_Info where TypeID=15";

		$obj = new DROPDOWN();

		$temp = PdoDataAccess::runquery($query);

		$obj->datasource = $temp;
		$obj->valuefield = "%InfoID%";
		$obj->textfield = "%Title%";
		$obj->Style = 'class="x-form-text x-form-field" style="'.$style.'"';
		$obj->id = $dropdownName;

		if(!empty($extraRow))
			$obj->datasource = array_merge(array(array("InfoID" => "-1", "Title" => $extraRow)),$obj->datasource);
		return $obj->bind_dropdown($selectedID);

	}
    public static function GETALL_Marital_Status()
    {
        $query = "select * from Basic_Info where TypeID=15 ";
        return PdoDataAccess::runquery($query);
    }
	public static function DRP_WorkTimeType($dropdownName, $selectedID = "", $extraRow = "",$style="")
	{
		$query = "select * from BaseInfo where TypeID= 62 ";
		
		$obj = new DROPDOWN();

		$temp = PdoDataAccess::runquery($query);
		
		$obj->datasource = $temp;
		$obj->valuefield = "%InfoID%";
		$obj->textfield = "%InfoDesc%";
		$obj->Style = 'class="x-form-text x-form-field" style="'.$style.'"';
		$obj->id = $dropdownName;

		if(!empty($extraRow))
			$obj->datasource = array_merge(array(array("InfoID" => "-1", "InfoDesc" => $extraRow)),$obj->datasource);
		return $obj->bind_dropdown($selectedID);
		
	}	
	public static function DRP_EmpMode($dropdownName, $selectedID = "", $extraRow = "",$style="")
	{
		$query = "select * from Basic_Info where TypeID=4";
		
		$obj = new DROPDOWN();

		$temp = PdoDataAccess::runquery($query);
		
		$obj->datasource = $temp;
		$obj->valuefield = "%InfoID%";
		$obj->textfield = "%Title%";
		$obj->Style = 'class="x-form-text x-form-field" style="'.$style.'"';
		$obj->id = $dropdownName;

		if(!empty($extraRow))
			$obj->datasource = array_merge(array(array("InfoID" => "-1", "Title" => $extraRow)),$obj->datasource);
		return $obj->bind_dropdown($selectedID);
		
	}
	public static function DRP_staff_groups($dropdownName, $selectedID = "", $extraRow = "",$style="")
	{
		$query = " select * from staff_groups ";
		
		$obj = new DROPDOWN();

		$temp = PdoDataAccess::runquery($query);
		
		$obj->datasource = $temp;
		$obj->valuefield = "%staff_group_id%";
		$obj->textfield = "%title%";
		$obj->Style = 'class="x-form-text x-form-field" '.$style.'';
		$obj->id = $dropdownName;

		if(!empty($extraRow))
			$obj->datasource = array_merge(array(array("staff_group_id" => "-1", "title" => $extraRow)),$obj->datasource);
		return $obj->bind_dropdown($selectedID);
		
	}
	public static function CHK_employee_states($checkboxPrefix, $selectedArray = array(), $columnCount = 1, $enableAllCheck = true)
	{
		$obj = new CHECKBOXLIST();
		
		$obj->datasource = PdoDataAccess::runquery("select * from Basic_Info where TypeID=3");
		$obj->idfield = $checkboxPrefix . "%InfoID%";
		$obj->valuefield = $checkboxPrefix . "%InfoID%";
		$obj->textfield = "%Title%";
		$obj->columnCount = $columnCount;
		$obj->Allchecked = true;
		$obj->EnableCheckAllButton = $enableAllCheck;
		
		return $obj->bind_checkboxlist($selectedArray);
	}
	public static function CHK_employee_modes($checkboxPrefix, $selectedArray = array(), $columnCount = 1, $enableAllCheck = true)
	{
		$obj = new CHECKBOXLIST();
		
		$obj->datasource = PdoDataAccess::runquery("select * from Basic_Info where TypeID=4");
		$obj->idfield = $checkboxPrefix . "%InfoID%";
		$obj->valuefield = $checkboxPrefix . "%InfoID%";
		$obj->textfield = "%Title%";
		$obj->columnCount = $columnCount;
		$obj->Allchecked = true;
		$obj->EnableCheckAllButton = $enableAllCheck;
		
		return $obj->bind_checkboxlist($selectedArray);
	}
	public static function DRP_MaritalStatus($dropdownName, $selectedID = "", $style="")
	{
		$query = "select * from Basic_Info where TypeID = 15 ";

		$obj = new DROPDOWN();

		$temp = PdoDataAccess::runquery($query);

		$obj->datasource = $temp;
		$obj->valuefield = "%InfoID%";
		$obj->textfield = "%Title%";
		$obj->Style = 'class="x-form-text x-form-field" style="'.$style.'"';
		$obj->id = $dropdownName;

		return $obj->bind_dropdown($selectedID);

	}
	public static function DRP_Religion_Subreligion($formName, $ReligionFieldName, $SubreligionFieldName
		, $ReligionSelectedID = "", $SubreligionSelectedID = ""
		, $ReligionExtraRow = "", $SubreligionExraRow = "")
	{
		$obj = new MaserDetail_DROPDOWN();
		
		$obj->Master_datasource = PdoDataAccess::runquery("select * from Basic_Info where TypeID = 38");
		if(!empty($ReligionExtraRow))
			$obj->Master_datasource = array_merge(array(array("InfoID" => "-1", "Title" => $ReligionExtraRow)),$obj->Master_datasource);
		$obj->Master_id = $ReligionFieldName;
		$obj->Master_valuefield = "%InfoID%";
		$obj->Master_textfield = "%Title%";
		$obj->Master_formName = $formName;
		//---------------------------------------------------------------------------
		$obj->Detail_datasource = PdoDataAccess::runquery("select * from Basic_Info where TypeID = 39");
		if(!empty($SubreligionExraRow))
			$obj->Detail_datasource = array_merge(array(array("InfoID" => "-1", "Title" => $SubreligionExraRow)),$obj->Detail_datasource);
		$obj->Detail_id = $SubreligionFieldName;
		$obj->Detail_valuefield = "%InfoID%";
		$obj->Detail_textfield = "%Title%";
		$obj->Detail_masterfield = "MasterID";
		
		return $obj->bind_dropdown_returnObjects($ReligionSelectedID, $SubreligionSelectedID);
	}

    public static function DRP_ComputeType_Multiplicand($formName, $ComputeTypeFieldName, $MultiplicandFieldName
		, $ComputeTypeSelectedID = "", $MultiplicandSelectedID = ""
		, $ComputeTypeExtraRow = "", $MultiplicandExraRow = "")
	{
    
		$obj = new MaserDetail_DROPDOWN();

		$obj->Master_datasource = PdoDataAccess::runquery(" select * from Basic_Info where TypeID = 19 ");
		if(!empty($ComputeTypeExtraRow))
			$obj->Master_datasource = array_merge(array(array("InfoID" => "-1", "Title" => $ComputeTypeExtraRow)),$obj->Master_datasource);
		$obj->Master_id = $ComputeTypeFieldName;
		$obj->Master_valuefield = "%InfoID%";
		$obj->Master_textfield = "%Title%";
		$obj->Master_formName = $formName;
		//---------------------------------------------------------------------------
		$obj->Detail_datasource = PdoDataAccess::runquery("select * from Basic_Info where TypeID = 40 ");
		if(!empty($MultiplicandExraRow))
			$obj->Detail_datasource = array_merge(array(array("InfoID" => "-1", "Title" => $MultiplicandExraRow)),$obj->Detail_datasource);
		$obj->Detail_id = $MultiplicandFieldName;
		$obj->Detail_valuefield = "%InfoID%";
		$obj->Detail_textfield = "%Title%";
		$obj->Detail_masterfield = "MasterID";

		return $obj->bind_dropdown_returnObjects($ComputeTypeSelectedID, $MultiplicandSelectedID);
	}
    
	public static function DRP_Military_MilitaryType($formName, $MilitaryFieldName, $SubMilitaryFieldName
		, $MilitarySelectedID = "", $SubMilitarySelectedID = ""
		, $MilitaryExtraRow = "", $SubMilitaryExraRow = "")
	{
		$obj = new MaserDetail_DROPDOWN();
		
		$obj->Master_datasource = PdoDataAccess::runquery("select * from Basic_Info where TypeID = 9");
		if(!empty($MilitaryExtraRow))
			$obj->Master_datasource = array_merge(array(array("InfoID" => "-1", "Title" => $MilitaryExtraRow)),$obj->Master_datasource);
		$obj->Master_id = $MilitaryFieldName;
		$obj->Master_valuefield = "%InfoID%";
		$obj->Master_textfield = "%Title%";
		$obj->Master_formName = $formName;
		//---------------------------------------------------------------------------
		$obj->Detail_datasource = PdoDataAccess::runquery("select * from Basic_Info where TypeID = 10");
		if(!empty($SubMilitaryExraRow))
			$obj->Detail_datasource = array_merge(array(array("InfoID" => "-1", "Title" => $SubMilitaryExraRow)),$obj->Detail_datasource);
		$obj->Detail_id = $SubMilitaryFieldName;
		$obj->Detail_valuefield = "%InfoID%";
		$obj->Detail_textfield = "%Title%";
		$obj->Detail_masterfield = "MasterID";
		
		return $obj->bind_dropdown_returnObjects($MilitarySelectedID, $SubMilitarySelectedID);
	}
	public static function DRP_Devotions($dropdownName, $selectedID = "", $style="")
	{
		$query = "select * from Basic_Info where TypeID = 2 ";

		$obj = new DROPDOWN();

		$temp = PdoDataAccess::runquery($query);

		$obj->datasource = $temp;
		$obj->valuefield = "%InfoID%";
		$obj->textfield = "%Title%";
		$obj->Style = 'class="x-form-text x-form-field" style="'.$style.'"';
		$obj->id = $dropdownName;

		return $obj->bind_dropdown($selectedID);

	}
	public static function DRP_SalaryPayProc($dropdownName, $selectedID = "", $style="", $className = "")
	{
		$query = "select * from Basic_Info where TypeID = 12 ";
		
		$obj = new DROPDOWN();

		$temp = PdoDataAccess::runquery($query);
		
		$obj->datasource = $temp;
		$obj->valuefield = "%InfoID%";
		$obj->textfield = "%Title%";
		$obj->Style = 'class="' . $className . ' x-form-text x-form-field" style="'.$style.'"';
		$obj->id = $dropdownName;
		
		return $obj->bind_dropdown($selectedID);
		
	}
	public static function DRP_Annual_Effect($dropdownName, $selectedID = "", $style="", $className = "")
	{
		$query = "select * from Basic_Info where TypeID = 13 ";
		
		$obj = new DROPDOWN();

		$temp = PdoDataAccess::runquery($query);
		
		$obj->datasource = $temp;
		$obj->valuefield = "%InfoID%";
		$obj->textfield = "%Title%";
		$obj->Style = 'class="' . $className . ' x-form-text x-form-field" style="'.$style.'"';
		$obj->id = $dropdownName;
		
		return $obj->bind_dropdown($selectedID);
		
	}
	
	public static function DRP_Grade($dropdownName, $selectedID = "", $style="", $className = "")
	{
		$query = "select * from Basic_Info where TypeID = 44 ";
		
		$obj = new DROPDOWN();

		$temp = PdoDataAccess::runquery($query);
		
		$obj->datasource = $temp;
		$obj->valuefield = "%InfoID%";
		$obj->textfield = "%Title%";
		$obj->Style = 'class="' . $className . ' x-form-text x-form-field" style="'.$style.'"';
		$obj->id = $dropdownName;
		
		return $obj->bind_dropdown($selectedID);
		
	}
	
	public static function DRP_OrgType($dropdownName, $selectedID = "", $style="")
	{
		$query = "select * from Basic_Info where TypeID = 32";

		$obj = new DROPDOWN();

		$temp = PdoDataAccess::runquery($query);

		$obj->datasource = $temp;
		$obj->valuefield = "%InfoID%";
		$obj->textfield = "%Title%";
		$obj->Style = 'class="x-form-text x-form-field" style="'.$style.'"';
		$obj->id = $dropdownName;

		return $obj->bind_dropdown($selectedID);

	}
		
	public static function DRP_OtherPersonType($dropdownName, $selectedID = "", $style="", $extraRow = "", $event = "")
	{
		$query = "
			select *
			from Basic_Info 
			where TypeID = 58 
			order by InfoID ";
		
		$obj = new DROPDOWN();

		$temp = PdoDataAccess::runquery($query);
		if($extraRow != "")
		$temp = array_merge(array(array("InfoID" => -1,"Title" => $extraRow)), $temp);
		$obj->datasource = $temp;
		$obj->valuefield = "%InfoID%";
		$obj->textfield = "%Title%";
		$obj->Style = 'class="x-form-text x-form-field" style="'.$style.'" ';
		$obj->Event = $event;
		$obj->id = $dropdownName;

		return $obj->bind_dropdown($selectedID);

	}
	
	public static function DRP_SalaryItems($dropdownName, $selectedID = "", $style="", $extraRow = "", $event = "" ,$type ="")
	{
		if($type ==1 )
		    $available_for = ITEM_AVAILABLE_BENEFIT ; 
		else if($type ==2 )
		     $available_for = ITEM_AVAILABLE_FRACTION_CASE ; 
		
		$query = " select salary_item_type_id,full_title from salary_item_types 
					where user_defined = 1 AND user_data_entry = 1 AND 
					      compute_place = ".SALARY_ITEM_COMPUTE_PLACE_PAYMENT." AND available_for = ".$available_for." AND
					      effect_type =".$type	;
		
		$obj = new DROPDOWN();

		$temp = PdoDataAccess::runquery($query);
		if($extraRow != "")
		$temp = array_merge(array(array("salary_item_type_id" => -1,"full_title" => $extraRow)), $temp);
		$obj->datasource = $temp;
		$obj->valuefield = "%salary_item_type_id%";
		$obj->textfield = "%full_title%";
		$obj->Style = 'class="x-form-text x-form-field" style="'.$style.'" ';
		$obj->Event = $event;
		$obj->id = $dropdownName;

		return $obj->bind_dropdown($selectedID);

	}
	public static function DRP_EMP_STATE_WST($dropdownName, $selectedID = "", $style="")
	{
			$query = "select * from BaseInfo where TypeID = 58 ";

			$obj = new DROPDOWN();

			$temp = PdoDataAccess::runquery($query);
            $arr = array(array('TypeID' => '3' ,
                               '0' => '3' ,
                               'InfoID' => '0' ,
                               '1' => '0' ,
                               'InfoDesc' => 'كپي از حكم قبلي' ,
                               '2' => '0' ,
                               'param1' => '0' ,
                               '3' => '0',
                               'param2' => '0',
                               '4' => '0'
                        ));
            $result = array_merge($arr, $temp);
           
			$obj->datasource = $result;
			$obj->valuefield = "%InfoID%";
			$obj->textfield = "%InfoDesc%";
			$obj->Style = 'class="x-form-text x-form-field" style="'.$style.'"';
			$obj->id = $dropdownName;

			return $obj->bind_dropdown($selectedID);

		}
    public static function DRP_EMP_MODE_WST($dropdownName, $selectedID = "", $style="" ,$event )
	{
		$query = "select * from BaseInfo where TypeID = 61 ";

		$obj = new DROPDOWN();

		$temp = PdoDataAccess::runquery($query);
		$arr = array(array('TypeID' => '4' ,
						   '0' => '4' ,
						   'InfoID' => '0' ,
						   '1' => '0' ,
						   'InfoDesc' => 'كپي از حكم قبلي' ,
						   '2' => '0' ,
						   'param1' => '0' ,
						   '3' => '0',
						   'param2' => '0',
						   '4' => '0'
					));
		$result = array_merge($arr, $temp);

		$obj->datasource = $result;
		$obj->valuefield = "%InfoID%";
		$obj->textfield = "%InfoDesc%";
		$obj->Style = 'class="x-form-text x-form-field" style="'.$style.'"';
		$obj->id = $dropdownName;
		$obj->Event = $event;

		return $obj->bind_dropdown($selectedID);

	}
	public static function DRP_WTT($dropdownName, $selectedID = "", $style="")
	{
		$query = "select * from BaseInfo where TypeID = 62 ";

		$obj = new DROPDOWN();

		$temp = PdoDataAccess::runquery($query);
		$arr = array(array('TypeID' => '14' ,
						   '0' => '14' ,
						   'InfoID' => '0' ,
						   '1' => '0' ,
						   'InfoDesc' => 'كپي از حكم قبلي' ,
						   '2' => '0' ,
						   'param1' => '0' ,
						   '3' => '0',
						   'param2' => '0',
						   '4' => '0'
					));
		$result = array_merge($arr, $temp);

		$obj->datasource = $result;
		$obj->valuefield = "%InfoID%";
		$obj->textfield = "%InfoDesc%";
		$obj->Style = 'class="x-form-text x-form-field" style="'.$style.'"';
		$obj->id = $dropdownName;

		return $obj->bind_dropdown($selectedID);

	}
	
	public static function WRT_TYP($dropdownName, $selectedID = "", $style="")
	{		
		
		$query = " SELECT * FROM HRM_writ_types  ";

		$obj = new DROPDOWN();

		$temp = PdoDataAccess::runquery($query);
		
		$result = $temp; 
		$obj->datasource = $result;
		$obj->valuefield = "%writ_type_id%";
		$obj->textfield = "%title%";
		$obj->Style = 'class="x-form-text x-form-field" style="'.$style.'"';
		$obj->id = $dropdownName;

		return $obj->bind_dropdown($selectedID);

	}
	
	
	public static function DRP_PAY_PROC_WST($dropdownName, $selectedID = "", $style="")
	{
		$query = "select * from BaseInfo where TypeID = 59 ";

		$obj = new DROPDOWN();

		$temp = PdoDataAccess::runquery($query);
		$arr = array(array('TypeID' => '12' ,
						   '0' => '12' ,
						   'InfoID' => '0' ,
						   '1' => '0' ,
						   'InfoDesc' => 'كپي از حكم قبلي' ,
						   '2' => '0' ,
						   'param1' => '0' ,
						   '3' => '0',
						   'param2' => '0',
						   '4' => '0'
					));
		$result = array_merge($arr, $temp);

		$obj->datasource = $result;
		$obj->valuefield = "%InfoID%";
		$obj->textfield = "%InfoDesc%";
		$obj->Style = 'class="x-form-text x-form-field" style="'.$style.'"';
		$obj->id = $dropdownName;

		return $obj->bind_dropdown($selectedID);
	}
	public static function DRP_POST_EFF_WST($dropdownName, $selectedID = "", $style="")
	{
		$query = "select * from BaseInfo where TypeID = 69 ";

		$obj = new DROPDOWN();

		$temp = PdoDataAccess::runquery($query);
		$arr = array(array('TypeID' => '34' ,
						   '0' => '34' ,
						   'InfoID' => '0' ,
						   '1' => '0' ,
						   'InfoDesc' => 'كپي از حكم قبلي' ,
						   '2' => '0' ,
						   'param1' => '0' ,
						   '3' => '0',
						   'param2' => '0',
						   '4' => '0'
					));
		$result = array_merge($arr, $temp);

		$obj->datasource = $result;
		$obj->valuefield = "%InfoID%";
		$obj->textfield = "%InfoDesc%";
		$obj->Style = 'class="x-form-text x-form-field" style="'.$style.'"';
		$obj->id = $dropdownName;

		return $obj->bind_dropdown($selectedID);
	}
	public static function DRP_ANN_EFF_WST($dropdownName, $selectedID = "", $style="")
	{
		$query = "select * from BaseInfo where TypeID = 60 ";

		$obj = new DROPDOWN();

		$temp = PdoDataAccess::runquery($query);
		$arr = array(array('TypeID' => '13' ,
						   '0' => '13' ,
						   'InfoID' => '0' ,
						   '1' => '0' ,
						   'InfoDesc' => 'كپي از حكم قبلي' ,
						   '2' => '0' ,
						   'param1' => '0' ,
						   '3' => '0',
						   'param2' => '0',
						   '4' => '0'
					));
		$result = array_merge($arr, $temp);

		$obj->datasource = $result;
		$obj->valuefield = "%InfoID%";
		$obj->textfield = "%InfoDesc%";
		$obj->Style = 'class="x-form-text x-form-field" style="'.$style.'"';
		$obj->id = $dropdownName;

		return $obj->bind_dropdown($selectedID);
	}
	public static function DRP_EffectType($dropdownName, $selectedID = "", $style="")
	{
		$arr =  array(array('caption'=> 'حقوق و مزايا',      'value'=>1),
					  array('caption'=> 'کسور',  'value'=>2));

		$obj = new DROPDOWN();

		$obj->datasource = $arr;
		$obj->valuefield = "%value%";
		$obj->textfield = "%caption%";
		$obj->Style = 'class="x-form-text x-form-field" style="'.$style.'"';
		$obj->id = $dropdownName;

		return $obj->bind_dropdown($selectedID);

	}

	public static function DRP_Is_Valid()
	{
		$arr =  array(array('caption'=> 'نمی باشد',      'value'=>"0"),
					  array('caption'=> 'می باشد',  'value'=>"1"));

		

		return $arr;

	}

    public static function DRP_Doc_State2($dropdownName="" , $selectedID = "", $extraRow = "",$style="")
	{
		
         $arr =   array(array('caption'=> 'پیش نویس',      'value'=>"1"),
                    	  array('caption'=> 'تایید واحد مرکزی',  'value'=>"3"));

		$obj = new DROPDOWN();

		$obj->datasource = $arr;
		$obj->valuefield = "%value%";
		$obj->textfield = "%caption%";
		$obj->Style = 'class="x-form-text x-form-field" style="'.$style.'"';
		$obj->id = $dropdownName;

		return $obj->bind_dropdown($selectedID);


	}

     public static function DRP_Doc_State()
	{

       $arr =   array(array('caption'=> 'پیش نویس',      'value'=>"1"),
                    	  array('caption'=> 'تایید واحد مرکزی',  'value'=>"3"));

        return $arr;
        
	}

	public static function DRP_Type_Retired()
	{
		$arr =  array(array('caption'=>' نمی باشد',  'value'=> "0.0" ) ,
					  array('caption'=> 'نیمه می باشد',      'value'=>"0.5"),
					  array('caption'=> 'می باشد',      'value'=>"1.0")
					  );

		
		return $arr;

	}
	
	
	public static function DRP_SalaryItemAvailableFor($dropdownName, $selectedID = "", $style="")
	{
		$arr =  array(array('caption'=> 'وام',							'value'=>1),
					  array('caption'=> 'پس انداز',	'value'=>2),
					  array('caption'=> 'مزاياي ثابت، موردي و گروهي',	'value'=>3),	    
					  array('caption'=> 'كسور موردي و گروهي',			'value'=>4),
					  array('caption'=> 'كسور ثابت',					'value'=>5),
					  array('caption'=> 'هيچكدام',						'value'=>6));

		$obj = new DROPDOWN();

		$obj->datasource = $arr;
		$obj->valuefield = "%value%";
		$obj->textfield = "%caption%";
		$obj->Style = 'class="x-form-text x-form-field" style="'.$style.'"';
		$obj->id = $dropdownName;

		return $obj->bind_dropdown($selectedID);

	}
	public static function DRP_Credit_topic($dropdownName, $selectedID = "", $style="")
	{

		$arr =  array(array('caption'=> 'فصل 1',      'value'=>1),
					  array('caption'=> 'ساير فصول',  'value'=>99));

		$obj = new DROPDOWN();

		$obj->datasource = $arr;
		$obj->valuefield = "%value%";
		$obj->textfield = "%caption%";
		$obj->Style = 'class="x-form-text x-form-field" style="'.$style.'"';
		$obj->id = $dropdownName;

		return $obj->bind_dropdown($selectedID);

	}
	/*public static function DRP_ComputeType_Multiplicand($formName, $ComputeTypeFieldName, $MultiplicandFieldName
		, $ComputeTypeSelectedID = "", $MultiplicandSelectedID = ""
		, $ComputeTypeExtraRow = "", $MultiplicandExraRow = "")
	{
		$obj = new MaserDetail_DROPDOWN();
		
		$obj->Master_formName = $formName;
		
		$obj->Master_datasource = array(array('caption'=> 'مبلغ ثابت',      'value'=>1),
	                     				array('caption'=> 'ضريب',  'value'=>2),
	                      				array('caption'=> 'تابع محاسباتي',  'value'=>3));
		if(!empty($ComputeTypeExtraRow))
			$obj->Master_datasource = array_merge(array(array("value" => "-1", "caption" => $ComputeTypeExtraRow)),$obj->Master_datasource);
		$obj->Master_id = $ComputeTypeFieldName;
		$obj->Master_valuefield = "%value%";
		$obj->Master_textfield = "%caption%";
		$obj->Master_Style = "style= width:100px";
		
		$obj->Detail_datasource = array(array('caption'=> 'حقوق مبنا',      'value'=>1 , 'master'=>2),
	                     				array('caption'=> 'حقوق',  'value'=>2, 'master'=>2),
	                      				array('caption'=> 'حقوق و مزاياي مستمر',  'value'=>3, 'master'=>2));
		if(!empty($MultiplicandExraRow))
			$obj->Detail_datasource = array_merge(array(array("value" => "-1", "caption" => $MultiplicandExraRow)),$obj->Detail_datasource);
		$obj->Detail_id = $MultiplicandFieldName;
		$obj->Detail_valuefield = "%value%";
		$obj->Detail_textfield = "%caption%";
		$obj->Detail_masterfield = "master";
		
		return $obj->bind_dropdown_returnObjects($ComputeTypeSelectedID, $MultiplicandSelectedID);
	}*/
	public static function DRP_UnEmpCause($dropdownName, $selectedID = "", $style="")
	{
		$query = "select * from Basic_Info where TypeID = 33";
		
			$obj = new DROPDOWN();
	
			$temp = PdoDataAccess::runquery($query);
			
			$obj->datasource = $temp;
			$obj->valuefield = "%InfoID%";
			$obj->textfield = "%Title%";
			$obj->Style = 'class="x-form-text x-form-field" style="'.$style.'"';
			$obj->id = $dropdownName;
	
			return $obj->bind_dropdown($selectedID);
		
	}	
	public static function DRP_EducLevel($dropdownName, $selectedID = "", $style="")
	{
		$query = "select * from Basic_Info where TypeID = 6 ";
		
		$obj = new DROPDOWN();

		$temp = PdoDataAccess::runquery($query);
		
		$obj->datasource = $temp;
		$obj->valuefield = "%InfoID%";
		$obj->textfield = "%Title%";
		$obj->Style = 'class="x-form-text x-form-field" style="'.$style.'"';
		$obj->id = $dropdownName;

		return $obj->bind_dropdown($selectedID);
	}
	public static function DRP_RetiredType($dropdownName, $selectedID = "", $style="" , $extraRow = "" )
	{
		$query = "select InfoID,Title from Basic_Info where TypeID = 28 ";

		$obj = new DROPDOWN();

		$temp = PdoDataAccess::runquery($query);

		$obj->datasource = $temp;
		$obj->valuefield = "%InfoID%";
		$obj->textfield = "%Title%";
		$obj->Style = 'class="x-form-text x-form-field" style="'.$style.'"';
		$obj->id = $dropdownName;
        if(!empty($extraRow))
			$obj->datasource = array_merge(array(array("InfoID" => "-1", "Title" => $extraRow)),$obj->datasource);
		return $obj->bind_dropdown($selectedID);

	}

    public static function DRP_banks($dropdownName, $selectedID = "", $style="" , $extraRow = "" )
	{
		$query = "select bank_id,name from HRM_banks ";

		$obj = new DROPDOWN();

		$temp = PdoDataAccess::runquery($query);

		$obj->datasource = $temp;
		$obj->valuefield = "%bank_id%";
		$obj->textfield = "%name%";
		$obj->Style = 'class="x-form-text x-form-field" style="'.$style.'"';
		$obj->id = $dropdownName;
        if(!empty($extraRow))
			$obj->datasource = array_merge(array(array("bank_id" => "-1", "name" => $extraRow)),$obj->datasource);
		return $obj->bind_dropdown($selectedID);

	}
    
    public static function DRP_Countries($dropdownName, $slaveDropName = "", $selectedID = "", 
    	$extraRow = "", $width = "", $formName = "")
	{
		$query = "select country_id,ptitle from HRM_countries";
		
		$obj = new AutoComplete_DROPDOWN();

		$temp = PdoDataAccess::runquery($query);
				
		$obj->datasource = $temp;
		$obj->valuefield = "%country_id%";
		$obj->textfield = "%ptitle%";
		$obj->width = $width;
		$obj->Style = 'class="x-form-text x-form-field" style="width:'.$width.'" ';
		$obj->id = $dropdownName;
		$obj->slaveID = $slaveDropName;
		$obj->formName = $formName;

		if(!empty($extraRow))
			$obj->datasource = array_merge(array(array("country_id" => "-1", "ptitle" => $extraRow)),$obj->datasource);
		$return = $obj->bind_dropdown($selectedID);
		
		return $return;		
		
	}
	public static function DRP_StudyFieldes($dropdownName, $selectedID = "", $extraRow = "",$style="")
	{
		$query = "select sfid,ptitle from study_fields";

		$obj = new DROPDOWN();

		$temp = PdoDataAccess::runquery($query);

		$obj->datasource = $temp;
		$obj->valuefield = "%sfid%";
		$obj->textfield = "%ptitle%";
		$obj->Style = 'class="x-form-text x-form-field" style="'.$style.'"';
		$obj->id = $dropdownName;

		if(!empty($extraRow))
			$obj->datasource = array_merge(array(array("sfid" => "-1", "ptitle" => $extraRow)),$obj->datasource);
		return $obj->bind_dropdown($selectedID);

	}
	public static function DRP_StudyField_StudyBranch($formName,$StudyFieldFieldName, $StudyBranchFieldName
		, $StudyFieldSelectedID = "", $StudyBranchSelectedID = ""
		, $StudyFieldExtraRow = "", $StudyBranchExraRow = "")
	{
		$obj = new MaserDetail_DROPDOWN();
		
		$obj->Master_datasource = PdoDataAccess::runquery("select sfid,ptitle from study_fields order by ptitle ");
		if(!empty($StudyFieldExtraRow))
			$obj->Master_datasource = array_merge(array(array("country_id" => "-1", "ptitle" => $StudyFieldExtraRow)),$obj->Master_datasource);
		$obj->Master_id = $StudyFieldFieldName;
		$obj->Master_valuefield = "%sfid%";
		$obj->Master_textfield = "%ptitle%";
		$obj->Master_Width = "200";
		$obj->Master_formName = $formName;
		
		/*$obj->Detail_datasource = PdoDataAccess::runquery("select sbid,ptitle,sfid from study_branchs");
		if(!empty($StudyBranchExraRow))
			$obj->Detail_datasource = array_merge(array(array("university_id" => "-1", "ptitle" => $StudyBranchExraRow)),$obj->Detail_datasource);*/
		$obj->Detail_storeUrl = "global/domain.data.php?task=searchStudyBranches";
		$obj->Detail_id = $StudyBranchFieldName;
		$obj->Detail_valuefield = "%sbid%";
		$obj->Detail_textfield = "%ptitle%";
		$obj->Detail_masterfield = "sfid";
		return $obj->bind_dropdown_returnObjects($StudyFieldSelectedID, $StudyBranchSelectedID);
	}
	public static function DRP_CostCenters($dropdownName, $selectedID = "", $extraRow = "",$style="")
	{
		$query = "select * from cost_centers ";
		
		$obj = new DROPDOWN();

		$temp = PdoDataAccess::runquery($query);
		
		$obj->datasource = $temp;
		$obj->valuefield = "%cost_center_id%";
		$obj->textfield = "%title%";
		$obj->Style = 'class="x-form-text x-form-field" style="'.$style.'"';
		$obj->id = $dropdownName;

		if(!empty($extraRow))
			$obj->datasource = array_merge(array(array("cost_center_id" => "-1", "title" => $extraRow)),$obj->datasource);
		return $obj->bind_dropdown($selectedID);
		
	}
	public static function DRP_CostCenterPlan($dropdownName, $selectedID = "", $extraRow = "",$style="")
	{
		$query = "select * from CostCenterPlan  " ;			                  
		
		$obj = new DROPDOWN();

		$temp = PdoDataAccess::runquery($query);
		
		$obj->datasource = $temp;
		$obj->valuefield = "%CostCenterID%";
		$obj->textfield = "%Title%";
		$obj->Style = 'class="x-form-text x-form-field" style="'.$style.'"';
		$obj->id = $dropdownName;

		if(!empty($extraRow))
			$obj->datasource = array_merge(array(array("CostCenterID" => "-1", "Title" => $extraRow)),$obj->datasource);
		
		return $obj->bind_dropdown($selectedID);		
		
	}
	public static function DRP_JobFields($dropdownName, $selectedID = "", $extraRow = "", $width = "")
	{
		$query = "select jfid,title from job_fields";
		
		$obj = new AutoComplete_DROPDOWN();

		$temp = PdoDataAccess::runquery($query);
				
		$obj->datasource = $temp;
		$obj->valuefield = "%jfid%";
		$obj->textfield = "%title%";
		$obj->width = $width;
		$obj->Style = 'class="x-form-text x-form-field" style="width:'.$width.'" ';
		$obj->id = $dropdownName;

		if(!empty($extraRow))
			$obj->datasource = array_merge(array(array("jfid" => "-1", "title" => $extraRow)),$obj->datasource);
		$return = $obj->bind_dropdown($selectedID);
		
		return $return;		
	}
	public static function GETALL_SupporCause()
	{
		$query = "select * from Basic_Info where TypeID = 29";
		return PdoDataAccess::runquery($query);
	}
	public static function GETALL_InsureType()
	{
		$query = "select * from Basic_Info where TypeID = 30";
		return PdoDataAccess::runquery($query);
	}
    public static function GETALL_PersonType($extraRow = "")
	{
		$query = "select * from Basic_Info where TypeID = 16";
		$temp = PdoDataAccess::runquery($query);
		
		if(!empty($extraRow))
			$temp = array_merge(array(array("InfoID" => "-1", "Title" => $extraRow)),$temp);
		
		return $temp;
	}
	
	public static function GETALL_PlanItem($extraRow = "")
	{
		$query = "select * from Basic_Info where TypeID = 49";
		$temp = PdoDataAccess::runquery($query);
		
		if(!empty($extraRow))
			$temp = array_merge(array(array("InfoID" => "-1", "Title" => $extraRow)),$temp);
		
		return $temp;
	}
        
    public static function GETALL_beneficiary()
	{
		$query = "select * from beneficiary  ";
		return PdoDataAccess::runquery($query);
	}
        
    public static function GETALL_costCenter()
	{
		$query = "select * from cost_centers  ";
		return PdoDataAccess::runquery($query);
	}
	
	public static function GETALL_plancostCenter($extraRow = "")
	{
		$query = "select * from CostCenterPlan  ";
		$temp = PdoDataAccess::runquery($query);
		
		if(!empty($extraRow))
			$temp = array_merge(array(array("CostCenterID" => "-1", "Title" => $extraRow)),$temp);
		
		return $temp;
	}
	
    public static function GETALL_salaryItem($type="")
	{
		if($type ==1 )
		    $available_for = ITEM_AVAILABLE_BENEFIT ; 
		else if($type ==2 )
		     $available_for = ITEM_AVAILABLE_FRACTION_CASE ; 
		
		$query = " select salary_item_type_id,full_title from salary_item_types 
					where user_defined = 1 AND user_data_entry = 1 AND 
					      compute_place = ".SALARY_ITEM_COMPUTE_PLACE_PAYMENT." AND available_for = ".$available_for." AND
					      effect_type =".$type	;
				
		return PdoDataAccess::runquery($query);
	}
    
    public static function GETALL_Country()
	{
		$query = " select country_id , ptitle  from countries  ";
                
		$tmp = PdoDataAccess::runquery($query);
              
                return $tmp ;
	}
        
     public static function GETALL_BankType()
	{
		$query = " SELECT *
                                FROM Basic_Info
                                        where typeID = 46  ";

		$tmp = PdoDataAccess::runquery($query);
              
                return $tmp ;
	}
    
    public static function DRP_HR_TYPE($dropdownName, $selectedID = "", $style="")
	{
		
		$obj = new DROPDOWN();

		$temp = array(array('caption' => 'کلیه کارکنان هیئت علمی ، رسمی ، روزمزد' , 'value' => '1'),
                      array('caption' => 'کلیه کارکنان قراردادی' , 'value' => '2'));

		$obj->datasource = $temp;
		$obj->valuefield = "%value%";
		$obj->textfield = "%caption%";
		$obj->Style = 'class="x-form-text x-form-field" style="'.$style.'"';
		$obj->id = $dropdownName;

		return $obj->bind_dropdown($selectedID);

	}
	public static function DRP_DependentSupportStatus($dropdownName, $selectedID = "", $style="")
	{
		$query = "select * from Basic_Info where TypeID = 31";

		$obj = new DROPDOWN();

		$temp = PdoDataAccess::runquery($query);

		$obj->datasource = $temp;
		$obj->valuefield = "%InfoID%";
		$obj->textfield = "%Title%";
		$obj->Style = 'class="x-form-text x-form-field" style="'.$style.'"';
		$obj->id = $dropdownName;

		return $obj->bind_dropdown($selectedID);

	}
	public static function DRP_writType_writSubType($formName, $typeFieldName, $subtypeFieldName
		, $typeSelectedID = "", $subtypeSelectedID = ""
		, $typeExtraRow = "", $subtypeExraRow = "", $masterWidth = "" , $person_type = "" )
	{
		$obj = new MaserDetail_DROPDOWN();

		$where = ($person_type != "") ? $person_type : manage_access::getValidPersonTypes() ;
		$obj->Master_datasource = PdoDataAccess::runquery("
			select writ_type_id,title,person_type
			from writ_types
			where person_type in(" .$where. ")
			order by title");
		if(!empty($typeExtraRow))
			$obj->Master_datasource = array_merge(array(array("writ_type_id" => "-1", "title" => $typeExtraRow)),$obj->Master_datasource);
		$obj->Master_id = $typeFieldName;
		$obj->Master_valuefield = "writ_type_id";
		$obj->Master_valuefield2 = "person_type";
		$obj->Master_textfield = "title";
		$obj->Master_Width = $masterWidth;
		$obj->Master_formName = $formName;

		$obj->Detail_storeUrl = "global/domain.data.php?task=searchWritSubTypes&extraRowID=-1&extraRowText=$subtypeExraRow";
		$obj->Detail_id = $subtypeFieldName;
		$obj->Detail_valuefield = "writ_subtype_id";
		$obj->Detail_textfield = "title";
		$obj->Detail_masterfield = "writ_type_id";
		$obj->Detail_masterfield2 = "person_type";

		return $obj->bind_dropdown_returnObjects($typeSelectedID, $subtypeSelectedID);
	}
	public static function DRP_Units($formName, $dropdownName, $selectedID = "", $extraRow = "", $width = "", $where = "")
	{
		$query = "select ouid,ptitle from org_new_units ";
		$query .= ($where != "") ? " where " . $where : "";

		$obj = new AutoComplete_DROPDOWN();

		$temp = PdoDataAccess::runquery($query);

		$obj->datasource = $temp;
		$obj->valuefield = "%ouid%";
		$obj->textfield = "%ptitle%";
		$obj->width = $width;
		$obj->Style = 'class="x-form-text x-form-field" style="width:'.$width.'" ';
		$obj->id = $dropdownName;
		$obj->formName = $formName;
		
		if(!empty($extraRow))
		   $obj->datasource = array_merge(array(array("ouid" => "-1","0" => "-1", "ptitle" => $extraRow ,"1" => $extraRow )),$obj->datasource);

		$return = $obj->bind_dropdown_returnObjects($selectedID);

		return $return;
	}
	public static function DRP_Unit_SubUnit($formName, $UnitFieldName, $SubUnitFieldName
															 , $UnitSelectedID = "", $SubUnitSelectedID = ""
															 , $UnitExtraRow = "", $SubUnitExraRow = "" , $masterWidth = "")
	{
		$obj = new MaserDetail_DROPDOWN();

		$obj->Master_formName = $formName;
		$obj->Master_datasource = PdoDataAccess::runquery("select ouid,ptitle from org_new_units where parent_ouid is null");
		if(!empty($UnitExtraRow))
			$obj->Master_datasource = array_merge(array(array("1" => "-1","ouid" => "-1","2" => $UnitExtraRow,"ptitle" => $UnitExtraRow)),$obj->Master_datasource);
		$obj->Master_id = $UnitFieldName;
		$obj->Master_valuefield = "ouid";
		$obj->Master_textfield = "ptitle";
		$obj->Master_Width = $masterWidth;

		$obj->Detail_datasource = PdoDataAccess::runquery("select ouid  , ptitle , parent_ouid
																from org_new_units
		                                                     	where parent_ouid is not null");
		if(!empty($SubUnitExraRow ))
		{
			for($i=0; $i<count($obj->Master_datasource); $i++)
				$obj->Detail_datasource = array_merge(array(array(
					"ouid" => "-1",
					"ptitle" => $SubUnitExraRow ,
					"parent_ouid" => $obj->Master_datasource[$i]["ouid"] )),$obj->Detail_datasource);

		}
		$obj->Detail_id = $SubUnitFieldName;
		$obj->Detail_valuefield = "%ouid%";
		$obj->Detail_textfield = "%ptitle%";
		$obj->Detail_masterfield = "parent_ouid";

		return $obj->bind_dropdown_returnObjects($UnitSelectedID, $SubUnitSelectedID);
	}
	public static function DRP_Jobs($dropdownName, $selectedID = "", $formName = "", $style="")
	{
		
		$query = "select job_id,title,job_group from HRM_jobs";

		$obj = new AutoComplete_DROPDOWN();

		$temp = PdoDataAccess::runquery($query);

		$obj->datasource = $temp;
		$obj->valuefield = "%job_id%";
		$obj->textfield = "%title%";
		$obj->formName = $formName;
		$obj->withStoreObject = true;
		$obj->Style = 'class="x-form-text x-form-field" style="'.$style.'"';
		$obj->id = $dropdownName;

		return $obj->bind_dropdown_returnObjects($selectedID);
	}

	public static function DRP_Fac_Group($formName, $FacFieldName, $GroupFieldName
															 , $FacSelectedID = "", $GroupSelectedID = ""
															 , $masterWidth = "")
	{
		$obj = new MaserDetail_DROPDOWN();

		$obj->Master_formName = $formName;
		$obj->Master_datasource = PdoDataAccess::runquery("select ouid,ptitle from org_new_units where parent_ouid is null or parent_ouid =0");
		$obj->Master_id = $FacFieldName;
		$obj->Master_valuefield = "ouid";
		$obj->Master_textfield = "ptitle";
		$obj->Master_Width = $masterWidth;

		$obj->Detail_datasource = PdoDataAccess::runquery("select EduGrpCode,PEduName,FacCode from baseinfo.EducationalGroups");
		$obj->Detail_id = $GroupFieldName;
		$obj->Detail_valuefield = "%EduGrpCode%";
		$obj->Detail_textfield = "%PEduName%";
		$obj->Detail_masterfield = "FacCode";

		return $obj->bind_dropdown_returnObjects($FacSelectedID, $GroupSelectedID);
	}


	public static function DRP_jcid_jfid($formName, &$jcid, &$jfid, $FacFieldName, $GroupFieldName
															 , $FacSelectedID = "", $GroupSelectedID = ""
															 , $masterWidth = "")
	{
		$obj = new MaserDetail_DROPDOWN();

		$obj->Master_formName = $formName;
		$obj->Master_datasource = PdoDataAccess::runquery("select jcid,title from job_category");
		$obj->Master_id = $FacFieldName;
		$obj->Master_valuefield = "jcid";
		$obj->Master_textfield = "title";
		$obj->Master_Width = $masterWidth;

		$obj->Detail_datasource = PdoDataAccess::runquery("select jfid,title,jcid from job_fields");
		$obj->Detail_id = $GroupFieldName;
		$obj->Detail_valuefield = "%jfid%";
		$obj->Detail_textfield = "%title%";
		$obj->Detail_masterfield = "jcid";

		return $obj->bind_dropdown($jcid, $jfid, $FacSelectedID, $GroupSelectedID);
	}
        
        public static function DRP_poid_kindid($formName, &$poid, &$kindid, $PostFieldName, $KindFieldName
                                            , $PostSelectedID = "", $KindSelectedID = ""
                                            , $masterWidth = "")
	{
		$obj = new MaserDetail_DROPDOWN();

		$obj->Master_formName = $formName;
		$obj->Master_datasource = PdoDataAccess::runquery("SELECT InfoID ,Title FROM Basic_Info where typeid = 27");
		$obj->Master_id = $FacFieldName;
		$obj->Master_valuefield = "InfoID";
		$obj->Master_textfield = "Title";
		$obj->Master_Width = $masterWidth;

		$obj->Detail_datasource = array(array("InfoID" => "5","sid" => "1" , "title" => "مدیر" ),
                                                array("InfoID" => "5","sid" => "2" , "title" => "معاون مدیر" ),
                                                array("InfoID" => "5","sid" => "3" , "title" => "رئیس اداره" ),
                                                array("InfoID" => "5","sid" => "4" , "title" => "معاون اداره" ),
                                                array("InfoID" => "5","sid" => "5" , "title" => "کارشناس مسئول" )
                                                ) ; 
		$obj->Detail_id = $GroupFieldName;
		$obj->Detail_valuefield = "%jfid%";
		$obj->Detail_textfield = "%title%";
		$obj->Detail_masterfield = "jcid";

		return $obj->bind_dropdown($jcid, $jfid, $PostSelectedID, $KindSelectedID);
	}

}

?>
