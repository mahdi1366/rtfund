<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	96.05
//---------------------------
class FRW_reports extends OperationClass{
	
	const TableName = "FRW_reports";
	const TableKey = "ReportID";
	
	public $ReportID;
	public $title;
	public $MenuID;
}

class FRW_ReportItems extends OperationClass{
	
	const TableName = "FRW_ReportItems";
	const TableKey = "RowID";
	
	public $RowID;
	public $ReportID;
	public $ElemName;
	public $ElemValue;
}