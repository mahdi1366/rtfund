<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	89.02
//---------------------------
require_once inc_component;

class FGR_forms extends PdoDataAccess
{
	public $FormID;
	public $FormName;
	public $reference;
	public $FileInclude;
	
	function __construct($FormID = "")
	{
		if($FormID != "")
		{
			PdoDataAccess::FillObject($this, "FormID=?", array($FormID));
		}	
	}
	
	public static function select($where = "")
	{
		$query = "SELECT * FROM FGR_forms";
		$query .= ($where != "") ? " where " . $where : "";
		
		return PdoDataAccess::runquery_fetchMode($query);
	}
	
	public function AddForm()
	{
		if(!parent::insert("FGR_forms", $this))
			return false;
	 	
		$this->FormID = parent::InsertID();
		
	 	$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->FormID;
		$daObj->TableName = "FGR_forms";
		$daObj->execute();
		return true;	
	}
	
	public function EditForm()
	{
		if(!parent::update("FGR_forms", $this, " FormID=:f", array(":f" => $this->FormID)))
			return false;
	 	
	 	$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->FormID;
		$daObj->TableName = "FGR_forms";
		$daObj->execute();
		return true;	
	}
	
	public static function RemoveForm($FormID)
	{
		if(!parent::delete("FGR_forms", " FormID=?", array($FormID)))
			return false;
	 	
	 	$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $FormID;
		$daObj->TableName = "FGR_forms";
		$daObj->execute();
		return true;
	}
	
	
	function Drp_AllForms($drpName, $extraRow = "", $onChangeFn = "")
	{
		$obj = new DROPDOWN();
		$obj->datasource = PdoDataAccess::runquery("
		select *
			from fm_forms as f
			join fm_workflow as w on(w.FormID=f.FormID and w.StepID=1)
		    left join wfm_replacement as r on(r.src_PersonID=w.PersonID and StartDate <= now() and now() <= EndDate)
		
		    where w.PersonID=" . $_SESSION["PersonID"] . " or des_PersonID=" . $_SESSION["PersonID"] . "
			order by FormName
		");
		
		if($extraRow != "")
			$obj->datasource = array_merge(array(array("FormID"=>0, "FormName"=>$extraRow)) , $obj->datasource);
		
		$obj->valuefield = "%FormID%";
		$obj->textfield = "%FormName%";
		$obj->Style = 'class="x-form-text x-form-field" style="width:100%"';
		
		if($onChangeFn != "")
			$obj->Style .= ' onchange="' . $onChangeFn . '(this);"';
			
		$obj->id = $drpName;
		return $obj->bind_dropdown();
	}

	function GetSummery($PersonID)
	{
		$query = "select * from wfm_send where ToPersonID=" . $PersonID . " and ViewFlag=0";
		
		$temp = PdoDataAccess::runquery($query);
		$return['new'] =  count($temp);
		//--------------------------------------------------------
		$temp = PdoDataAccess::runquery("select distinct s.*
		    from wfm_send as s
          		join wfm_forms as f on(f.LetterID=s.LetterID)
				left join fm_workflow as wfm on(wfm.FormID=f.FormID and wfm.StepID=s.StepID)
        		left join wfm_replacement as r on(r.src_PersonID=wfm.PersonID and r.des_PersonID=$PersonID and
          			StartDate <= now() and now() <= EndDate)

			where s.ToPersonID=$PersonID and s.DeleteFlag=0 and s.ArchiveFlag=0 and SendStatus='raw'");
		$return['receive'] =  count($temp);
		
		return $return;
	}

}

class FGR_FormElements extends PdoDataAccess
{
	public $ElementID;
	public $FormID;
	public $ElTitle;
	public $ElType;
	public $ElValue;
	public $RefField;
	public $RefDesc;
	public $TypeID;
	public $ordering;
	public $width;
	
	public static function select($where = "", $param = array())
	{
		$query = "SELECT * FROM FGR_FormElements";
		$query .= ($where != "") ? " where " . $where : "";
		
		return PdoDataAccess::runquery($query, $param);
	}
	
	public static function selectWithWfmValues($FormID, $letterID, $PersonID, $RefID)
	{
		if($letterID == "")
			$query = "
			select f.*,'' as wfmElementValue,
				if(a.PersonID=" . $PersonID . " or r.des_PersonID=" . $PersonID . ",1,0) as access,
				1 as active

			from FGR_FormElements as f
				left join fm_element_access as a on(a.FormID=f.FormID and a.ElementID=f.ElementID and a.StepID=1)
			 	left join wfm_replacement as r on(a.PersonID=r.src_PersonID and des_PersonID=1002 and
        				StartDate <= now() and now() <= EndDate)

        	where f.FormID=" . $FormID;
		else 
		{
			$RefID = ($RefID == 0) ? " is null" : "=" . $RefID;
			$query = "
			select f.*,w.ElementValue as wfmElementValue,
				if(a.PersonID=$PersonID or des_PersonID=$PersonID, 1, 0) as access,
				if(s.RefID $RefID, 1, 0) as active

			from FGR_FormElements as f
				left join wFGR_FormElements as w on(f.ElementID=w.ElementID and LetterID=$letterID)
    			left join wfm_send as s on(s.LetterID=$letterID and s.SendStatus='raw')
				
    			left join fm_workflow as wf on(wf.FormID=f.FormID and wf.StepID=s.StepID)
				left join fm_element_access as a on(a.FormID=f.FormID and a.ElementID=f.ElementID and
  					a.StepID=if(s.StepID is null,1,s.StepID) and if(wf.PersonID is null,1=1,a.PersonID=wf.PersonID))
        		
  				left join wfm_replacement as r on(a.PersonID=r.src_PersonID and des_PersonID=$PersonID and
         			 StartDate <= now() and now() <= EndDate)

        	where f.FormID=" . $FormID;
		}
		
		return PdoDataAccess::runquery($query);
	}
	
	public function AddElement()
	{
		if(empty($this->ordering))
		{
			$order = PdoDataAccess::GetLastID("FGR_FormElements", "ordering", "FormID=?", array($this->FormID));
			$this->ordering = $order+1;
		}
		
		if(!PdoDataAccess::insert("FGR_FormElements",$this))
			return false;
		
		$this->ElementID = parent::InsertID();
		
	 	$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->ElementID;
		$daObj->SubObjectID = $this->FormID;
		$daObj->TableName = "FGR_FormElements";
		$daObj->execute();
		return true;	
	}
	
	public function EditElement()
	{
		if(!PdoDataAccess::update("FGR_FormElements",$this, "ElementID=:e", array(":e" => $this->ElementID)))
			return false;
		
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->ElementID;
		$daObj->SubObjectID = $this->FormID;
		$daObj->TableName = "FGR_FormElements";
		$daObj->execute();
		return true;	
	}
	
	public static function RemoveElement($ElementID)
	{
		PdoDataAccess::delete("FGR_FormElements","ElementID=?", array($ElementID));
		
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $ElementID;
		$daObj->TableName = "FGR_FormElements";
		$daObj->execute();
		return true;	
	}
	
}

class wfm_form extends PdoDataAccess
{
	public $FormID;
	public $wfm00;
	public $LetterID;
	public $PersonID;
	public $regDate;
	public $referenceID;
	public $BreakDuration;
	
	function select($where)
	{
		$query = "select w.*,concat(u.name,' ',u.family) as fullname,
				wf.StepTitle,
				wf.StepID,
				f.reference,
				f.FormName,
				concat('" . $_SESSION["AGENCY"] . "','/',
						(case f.reference when 'devotions' then '1'
									  	 when 'states' then '2'
									  	 when 'rents' then '3' end),'-',w.referenceID,'/',w.LetterID) as pursuitCode

			from wfm_forms as w
				join fm_forms as f using(FormID)
				join um_user as u using(PersonID)
				left join wfm_send as s on(s.LetterID=w.LetterID and SendStatus='raw')
				left join fm_workflow as wf on(wf.FormID=w.FormID and wf.StepID=if(s.StepID is null, 1, s.StepID))";
		
		$query .= ($where != "") ? " where " . $where : "";
		
		return PdoDataAccess::runquery($query);
	}
	
	public function AddForm()
	{
		PdoDataAccess::insert("wfm_forms",$this->MakeItemArray());
	}
	
	public function EditForm($where)
	{
		PdoDataAccess::update("wfm_forms", $this->MakeItemArray(), $where);
	}
	
	public static function RemoveForm($where)
	{
		PdoDataAccess::delete("wfm_forms",$where);
	}
	
	public static function LastID()
	{
		return PdoDataAccess::GetLastID("wfm_forms", "LetterID");
	}
	
	function MakeItemArray()
	{
		$arr = array();
		
		if(isset($this->FormID)) 	$arr[] = array("FormID" , $this->FormID);
		if(isset($this->wfm00)) 	$arr[] = array("wfm00" , $this->wfm00);
		if(isset($this->LetterID)) 	$arr[] = array("LetterID" , $this->LetterID);
		if(isset($this->PersonID)) 	$arr[] = array("PersonID" , $this->PersonID);
		if(isset($this->regDate)) 	$arr[] = array("regDate" , $this->regDate);
		if(isset($this->referenceID))$arr[] = array("referenceID" , $this->referenceID);
		if(isset($this->BreakDuration))$arr[] = array("BreakDuration" , $this->BreakDuration);
		
		return $arr;
	}
}

class wfm_form_detail extends PdoDataAccess
{
	public $LetterID;
	public $ElementID;
	public $ElementValue;
	
	function select($where)
	{
		$query = "select wFGR_FormElements.*,
			FGR_FormElements.ElementType,FGR_FormElements.ElementValue as fmElementValue
			from wFGR_FormElements join FGR_FormElements using(ElementID)";
		$query .= ($where != "") ? " where " . $where : "";
		
		return PdoDataAccess::runquery($query);
	}
	
	public function AddDetail()
	{
		PdoDataAccess::insert("wFGR_FormElements",$this->MakeItemArray());
	}
	
	public function EditDetail($where)
	{
		PdoDataAccess::update("wFGR_FormElements",$this->MakeItemArray(), $where);
	}
	
	public static function RemoveDetail($where)
	{
		PdoDataAccess::delete("wFGR_FormElements",$where);
	}
	
	function MakeItemArray()
	{
		$arr = array();
		
		if(isset($this->LetterID)) 		$arr[] = array("LetterID" , $this->LetterID);
		if(isset($this->ElementID)) 	$arr[] = array("ElementID" , $this->ElementID);
		if(isset($this->ElementValue)) 	$arr[] = array("ElementValue" , $this->ElementValue);
		
		return $arr;
	}
}

?>