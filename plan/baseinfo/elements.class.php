<?php
//---------------------------
// programmer:	Jafarkhani
// create Date: 95.02
//---------------------------


class PLN_groups extends OperationClass
{
	const TableName = "PLN_groups";
	const TableKey = "GroupID";
	
	public $FormType;
	public $GroupID;
	public $ParentID;
	public $GroupDesc;
	public $ScopeID;
	public $CustomerRelated;
	public $IsMandatory;
	
	function Remove($pdo = null) {
		
		$dt = parent::runquery("select * from PLN_Elements where GroupID=?", array($this->GroupID));
		if(count($dt) > 0)
			return false;
		
		return parent::Remove($pdo);
	}
}

class PLN_Elements extends OperationClass
{
	const TableName = "PLN_Elements";
	const TableKey = "ElementID";
	
	public $ElementID;
	public $ParentID;
	public $GroupID;
	public $ElementTitle;
	public $ElementType;
	public $properties;
	public $EditorProperties;
	public $ElementValues;
	public $IsActive;
	
	function Remove($pdo = null) {
		
		$this->IsActive = "NO";
		return $this->Edit($pdo);
	}
}

?>
