<?php
//---------------------------
// programmer:	Jafarkhani
// create Date: 95.02
//---------------------------


class PLN_groups extends OperationClass
{
	const TableName = "PLN_groups";
	const TableKey = "GroupID";
		
	public $GroupID;
	public $ParentID;
	public $GroupDesc;
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
	public $values;
}

?>
