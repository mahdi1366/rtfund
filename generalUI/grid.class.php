<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	88.03.24
//---------------------------

class sadaf_datagrid
{
	private $id;
	//private $datasource;
	private $columns;
	private $divName;
	
	private $Url;
	private $Method;
	public $baseParams;
	
	private $buttons;
	
	public $remoteSort;
	public $DefaultSortField;
	/**
	 * "desc" is the default value
	 *
	 * @var string
	 */
	public $DefaultSortDir;
	/**
	 * Name of the column within the datasource that contains a record identifier value.
	 *
	 * @var string
	 */
	public $primaryKey;
	/**
	 * The title text to display in the panel header (defaults to '').
	 *
	 * @var string
	 */
	public $title;
	/**
	 * The width of this grid in pixels (defaults to auto).
	 *
	 * @var int
	 */
	public $width;
	/**
	 * The height of this grid in pixels (defaults to auto).
	 *
	 * @var int
	 */
    public $height;
	public $autoExpandColumn;
	/**
	 * If 'true',you can select several rows by 'ctrl' key.The default value is false.
	 *
	 * @var string
	 */
	public $multipleSelectRow;
	//---------------------------------
	/**
	 * set true for adding delete button
	 *
	 * @var boolean
	 */
	public $deleteButton;
	/**
	 * the name of javascript function for deleting
	 *
	 * @var string
	 */
	public $deleteHandler;
 	/** set true for adding add button
	 *
	 * @var boolean
	 */
	public $addButton;
	
	/**
	 * the name of javascript function for adding
	 *
	 * @var string
	 */
	public $addHandler;
	
	/** 
	* set true for adding option button
	*@var boolean
	*/
	public $optionButton;
	public $optionText;
	public $optionTooltip;
	public $optionHandler;
	
	/**
	 * If true,the rows of grid become unselectable,default value is false.
	 *
	 * @var bool
	 */
	public $disableSelection;
	public $rowSelectHandler;
	/**
	 * you can use the search panel in topbar of grid by setting this field "true",the default value is true
	 *
	 * @var bool
	 */
	public $EnableSearch;
	/**
	 * If you want your grid that  just maked and not loaded first,set this field true.
	 * Then you can render and load it by init function."Ext.onReady(init);".
	 * The default value is "false"
	 * 
	 *
	 * @var bool
	 */
	public $notRender;
	
	/**
	 * if set true the grid being a EditorGridPanel instead of GridPanel.default is false
	 *
	 * @var boolean
	 */
	public $editorGrid;
	
	public $remoteGroup;
	public $EnableGrouping;
	public $DefaultGroupField;
	public $startCollapsed; 
	/**
	 this function render the group header The return Text must be a template for example return "{values.text} : دارای {values.rs.length} رکورد"  Notice : if you want to write command in { } you must do it like {[]} for example: return '{values.rs.length} {[ values.rs.length > 1 ? "Items" : "Item" ]}' values =[group,groupId,startRow,rs,cls,style]
	 *
	 * @var unknown_type
	 */
	public $groupRenderer;
	
	
	public $EnableSummaryRow;
	/**
	 * Default value is true
	 *
	 * @var unknown_type
	 */
	public $EnablePaging;
	
	public $EnablePrintButton;
	public $printUrl;
	
	public $EnableRowNumber;
	
	public $collapsible;
	public $collapsed;
	
	public $pageSize;
	public $disableFooter = false;
	public $disableChangePageSize = false;
	
	public $enableRowEdit = false;
	public $rowEditOkHandler;

	public $HeaderMenu = true;
	
	private $formID;
	private $CurrencyStringRow;
	private $CurrencyStringRowDataIndex;
	private $plugins = array();
	//-------------------------------------------------------------------------
	/**
	 * get instance of sadaf_datagrid class
	 *
	 * @param string $gridName the name of grid
	 * @return sadaf_datagrid
	 */
	function sadaf_datagrid($ObjectName, $Url, $divID, $formID="")
	{
		$this->id = $ObjectName;
		$this->divName = $divID;
		$this->formID = $formID;
		
		$this->Url = $Url;
		$this->Method = "POST";
		$this->baseParams = "";		

		$this->remoteSort = true;
		$this->DefaultSortDir = "desc";		
		
		$this->title = '';
		$this->width = "";
		$this->height = "";
		
		$this->deleteButton = false;
		//$this->deleteHandler = "emptyFn";
		$this->addButton = false;
		$this->optionButton = false;
		$this->optionTooltip = "";
		
		$this->EnableSearch = true;
		$this->multipleSelectRow = false;
		
		$this->notRender = false;
		$this->disableSelection = false;
		
		$this->editorGrid = false;
		
		$this->remoteGroup = true;
		$this->EnableGrouping = false;
		$this->DefaultGroupField = "";
		$this->groupRenderer = "";
		$this->startCollapsed = false;
		
		$this->EnableSummaryRow = false;
		
		$this->EnablePaging = true;
		$this->EnableRowNumber = false;
		
		$this->EnablePrintButton = "false";
		$this->printUrl = "";
		
		$this->collapsed = true;
		$this->collapsible = false;
		
		$this->pageSize = 25;

		$this->CurrencyStringRow = "";
	}
	//-------------------------------------------------------------------------
	/**
	 * add column to your grid
	 *
	 * @param String $header
	 * @param String $dataIndex
	 * @param String $type  {'string','int','float','boolean','date'}
	 * @return GridColumn
	 */
	function addColumn($header,$dataIndex,$type="",$hidden=false)
	{
		$obj = new GridColumn($header,$dataIndex,$type,$hidden);

		$this->columns[count($this->columns)] = $obj;

		return $obj;
	}
    //-------------------------------------------------------------------------
    

	//-------------------------------------------------------------------------
	function addButton($id,$text,$iconCls,$handler)
	{
		$this->buttons[] = "{id: '$id',text: '$text',iconCls: '$iconCls',handler: $handler}";
	}
	//-------------------------------------------------------------------------
	function addObject($object)
	{
		$this->buttons[] = $object;
	}
	//-------------------------------------------------------------------------
	function addPlugin($object)
	{
		$this->plugins[] = $object;
	}
	//-------------------------------------------------------------------------
	function makeGrid()
	{
		echo '<script type="text/javascript">';
		echo $this->getJsClasses();
		echo "</script>";
	}
	function makeGrid_returnObjects()
	{
		return $this->getJsClasses_returnObject();
		
	}
	//-------------------------------------------------------------------------
	private function getJsClasses()
	{
		$str = "";
		
		//************************************************
		$str .= "var " . $this->id . "_reader = " . $this->setExtReader() . ";";
        //************************************************
        $str .= "var " . $this->id . "_store = new ";
        $str .= ($this->EnableGrouping) ? "Ext.data.GroupingStore({" : "Ext.data.Store({";
		$str .=	"proxy: new Ext.data.ScriptTagProxy({
							url: '" . $this->Url . "',
							method: '" . $this->Method . "'";
		$str .= ($this->formID != "") ? 
							",form: '" . $this->formID . "'" : "";
		$str .=			"}),
				baseParams:{" . $this->baseParams . "},reader: " . $this->id . "_reader,";
		
        $str .= ($this->EnableGrouping && $this->DefaultGroupField != "") ? 
        	"groupField:'" . $this->DefaultGroupField . "'," : "";
        $str .= ($this->EnableGrouping) ? ($this->remoteGroup) ? 
        	"remoteGroup: true," : "remoteGroup: false," : "";
        $str .= ($this->remoteSort) ? "remoteSort: true});" : "remoteSort: false});";

		$str .= isset($this->DefaultSortField) ? 
					$this->id . "_store.setDefaultSort('" . $this->DefaultSortField . "', '" .
					$this->DefaultSortDir . "');" : "";
		//************************************************
		$str .= ($this->enableRowEdit) ? $this->id . "_store.on('update', " . $this->rowEditOkHandler . ", " . $this->id . "_store);" : "";
		//************************************************
		$str .= "var " . $this->id . "_colModel = new Ext.grid.ColumnModel([";
		if($this->EnableRowNumber)
			$str .= "new Ext.grid.RowNumberer(),";
		$str .= $this->setExtColumnModel() . "]);" . $this->id . "_colModel.defaultSortable = true;";
				
				 
		//************************************************
		$str .= "\n var " . $this->id . "_grid = new Ext.grid.";
		
		$str .= ($this->editorGrid) ? "EditorGridPanel" : "GridPanel";  
		$str .=	"({\n
					el:'" . $this->divName . "',
					store: ". $this->id . "_store,\n
					cm: ". $this->id . "_colModel,\n
					title:'" . $this->title . "',\n";
		$str .= ($this->width == "") ? "autoWidth:true," : "width: " . $this->width . ",\n";
		$str .= ($this->height == "") ? "autoHeight:true," : "height: " . $this->height . ",\n";
		$str .= 	"loadMask: true,					
					frame:true,";
		
		if($this->collapsible)
		{
			$str .= "collapsible : true,collapsed: ";
			$str .= ($this->collapsed) ? "true," : "false,";
		}		
		$str .= "iconCls:'icon-grid',";					
					
		$str .= ($this->editorGrid) ? "clicksToEdit:1," : "";
		
		if($this->disableSelection)
			$str .= "disableSelection: true,";
		else 
		{
			$str .= "sm: new Ext.grid.RowSelectionModel({";
		
			$str .= ($this->multipleSelectRow) ? "singleSelect: false" : "singleSelect: true";
		
			$str .= isset($this->rowSelectHandler)?
					",listeners: { rowselect: " . $this->rowSelectHandler . "}" : "";
			$str .= 	"})," ;
		}
					
		$str .= (isset($this->autoExpandColumn)) ? "autoExpandColumn: '" . $this->autoExpandColumn . "'," : "";
		
		$str .= $this->HeaderMenu ? "" : "enableHdMenu: false,";

		$str .= "	stripeRows: true,
			
					viewConfig: {
			            forceFit:true
			        },";
		//...........................................................
			$str .= "plugins: [ ";
			$str .= ($this->enableRowEdit) ? "new Ext.ux.grid.RowEditor()," : "";
			$str .= ($this->EnableSearch) ?	 "new Ext.ux.grid.Search({mode:'remote'})," : "";
			$str .= ($this->EnableSummaryRow) ? "new Ext.ux.grid.GridSummary()," : "";
			
			$str .= implode(",", $this->plugins) . ",";
			

			$str = substr($str,0,strlen($str)-1);
			$str .= "],";
		//...........................................................
		if($this->EnablePaging)
		{
			$str .=    	"bbar: new Ext.PagingToolbar({";
			$str .= (!$this->disableChangePageSize) ? "plugins: [new Ext.ux.Andrie.pPageSize({})]," : "";
			$str .= "   pageSize: $this->pageSize,
			            store:  ". $this->id . "_store,
			            grid: '" . $this->id . "_grid',
			            printMode: " . $this->EnablePrintButton . ",
			        	printUrl: '" . $this->printUrl . "', 
			        	style : 'text-align:right;',
			            displayInfo: true			            
			       		}),";
		}
		else if(!$this->disableFooter)
			$str .=    	"bbar: new Ext.ExtraBar({
			        	store:  ". $this->id . "_store,
			        	grid: '" . $this->id . "_grid',
			        	printMode: " . $this->EnablePrintButton . ",
			        	printUrl: '" . $this->printUrl . "', 
			            displayInfo: true			            
			       		}),";
		$str .=    	"tbar:[";

		$str .= ($this->optionButton) ? 
							"'-',{
								id: 'optionButton',
								text: '" . $this->optionText . "',
								tooltip:'" . $this->optionTooltip . "',
								iconCls: 'option',
								handler: " . $this->optionHandler . "}," : "";
								
		$str .= ($this->addButton) ? 
							"'-',{
								id: 'addButton',
								text: 'ايجاد',
								tooltip:'ايجاد ركورد جديد',
								iconCls: 'add',
								handler: " . $this->addHandler . "}," : "";
		
		$str .= ($this->deleteButton) ? 
							"'-',{
                    			id: 'deleteButton',
								text: 'حذف',
                    			tooltip: 'حذف رديفهاي انتخابي',
                    			iconCls:'remove',
                    			handler: " . $this->deleteHandler . "}," : "" ;
		
		for($i=0; $i < count($this->buttons); $i++)
			$str .= "'-'," . $this->buttons[$i] . ",";
	
		$str = ($str[strlen($str)-1] == ",") ? substr($str,0,strlen($str)-1) : $str;
		
		$str .= "   ]";

        if ($this->EnableGrouping)
        {
        	$str .= ",view: new Ext.grid.GroupingView({forceFit:true,startCollapsed:"; 
			$str .= ($this->startCollapsed) ? "true," : "false,";
	    	$str .= ($this->groupRenderer != "") ? "groupHeaderRenderer: '" . $this->groupRenderer . "'," : "";
	    	$str .= "groupTextTpl: '{text} ({[values.rs.length]} رکورد)'})" ;
        }
		$str .=  "});";
        //***************************************************
		$str .= "function " .$this->id . "_init(){" .										
					$this->id . "_grid.render();" .
					$this->id . "_store.load(";
		$str .= ($this->EnablePaging) ? "{params:{start:0, limit:$this->pageSize}}" : "";
		$str .= ");}";

		if(!$this->notRender)
			$str .= "Ext.onReady(" .$this->id . "_init);";		

		$str .= $this->CurrencyStringRow;

		return $str;
	}
	//-------------------------------------------------------------------------
	private function getJsClasses_returnObject()
	{
		//************************************************
        $store = "new ";
        $store .= ($this->EnableGrouping) ? "Ext.data.GroupingStore({" : "Ext.data.Store({";
		$store .=	"proxy: new Ext.data.ScriptTagProxy({
							url: '" . $this->Url . "',
							method: '" . $this->Method . "'";
		$store .= ($this->formID != "") ? ",form: '" . $this->formID . "'" : "";
		$store .= "}),baseParams:{" . $this->baseParams . "}";
		//$store .= $this->baseParams != "" ? $this->baseParams . "," : "";
		//$store .= ($this->EnablePaging) ? "start:0, limit:$this->pageSize}" : "}";
		$store .= ",reader: " . $this->setExtReader();

        $store .= ($this->EnableGrouping && $this->DefaultGroupField != "") ?
					",groupField:'" . $this->DefaultGroupField . "'" : "";
        $store .= ($this->EnableGrouping) ? (($this->remoteGroup) ?
					",remoteGroup: true" : ",remoteGroup: false") : "";
        $store .= ($this->remoteSort) ? 
					",remoteSort: true" : ",remoteSort: false";
		$store .= isset($this->DefaultSortField) ?
					",sortInfo : {field:'". $this->DefaultSortField . "',direction:'" . $this->DefaultSortDir . "'}" : "";
		$store .= ($this->enableRowEdit) ?
					",listeners : {update : " . $this->rowEditOkHandler . "}" : "";
		$store .= "})";
		//************************************************
		$colModel = "new Ext.grid.ColumnModel({
			defaults: {sortable: true},
			columns: [";
		if($this->EnableRowNumber)
			$colModel .= "new Ext.grid.RowNumberer(),";
		$colModel .= $this->setExtColumnModel() . "]})";


		//************************************************
		$grid = "new Ext.grid." . (($this->editorGrid) ? "EditorGridPanel" : "GridPanel") .
			"({
				store: ".$store.",
				cm: " .$colModel. ",
				title:'" . $this->title . "'," .
				(($this->width == "") ? "autoWidth:true," : "width: " . $this->width . ",") .
				(($this->height == "") ? "autoHeight:true," : "height: " . $this->height . ",") .
				"loadMask: true,
				frame:true,";

		if($this->collapsible)
		{
			$grid .= "collapsible : true,collapsed: ";
			$grid .= ($this->collapsed) ? "true," : "false,";
		}
		$grid .= "iconCls:'icon-grid',";

		$grid .= ($this->editorGrid) ? "clicksToEdit:1," : "";

		if($this->disableSelection)
			$grid .= "disableSelection: true,";
		else
		{
			$grid .= "sm: new Ext.grid.RowSelectionModel({";

			$grid .= ($this->multipleSelectRow) ? "singleSelect: false" : "singleSelect: true";

			$grid .= isset($this->rowSelectHandler)?
					",listeners: { rowselect: " . $this->rowSelectHandler . "}" : "";
			$grid .= 	"})," ;
		}

		$grid .= (isset($this->autoExpandColumn)) ? "autoExpandColumn: '" . $this->autoExpandColumn . "'," : "";

		$grid .= $this->HeaderMenu ? "" : "enableHdMenu: false,";

		$grid .= "	stripeRows: true,

					viewConfig: {
			            forceFit:true
			        },";
		//...........................................................
			$grid .= "plugins: [ ";
			$grid .= ($this->enableRowEdit) ? "new Ext.ux.grid.RowEditor()," : "";
			$grid .= ($this->EnableSearch) ?	 "new Ext.ux.grid.Search({mode:'remote'})," : "";
			$grid .= ($this->EnableSummaryRow) ? "new Ext.ux.grid.GridSummary()," : "";
			$grid .= (count($this->plugins) != 0) ? implode(",", $this->plugins) . "," : "";

			$grid = substr($grid,0,strlen($grid)-1);
			$grid .= "],";
		//...........................................................
		if($this->EnablePaging)
		{
			$grid .= "bbar: new Ext.PagingToolbar({";
			$grid .= (!$this->disableChangePageSize) ? "plugins: [new Ext.ux.Andrie.pPageSize({})]," : "";
			$grid .= "  pageSize: $this->pageSize,
						printMode: " . $this->EnablePrintButton . ",
			        	printUrl: '" . $this->printUrl . "',
			        	style : 'text-align:right;',
			            displayInfo: true
			       	}),";
		}
		else if(!$this->disableFooter)
			$grid .= "bbar: new Ext.ExtraBar({
						printMode: " . $this->EnablePrintButton . ",
			        	printUrl: '" . $this->printUrl . "',
						displayInfo: true
			       	}),";
		//...........................................................
		$grid .= "tbar:[";

		$grid .= ($this->optionButton) ?
							"'-',{
								text: '" . $this->optionText . "',
								tooltip:'" . $this->optionTooltip . "',
								iconCls: 'option',
								handler: " . $this->optionHandler . "}," : "";

		$grid .= ($this->addButton) ?
							"'-',{
								text: 'ايجاد',
								tooltip:'ايجاد ركورد جديد',
								iconCls: 'add',
								handler: " . $this->addHandler . "}," : "";

		$grid .= ($this->deleteButton) ?
							"'-',{
								text: 'حذف',
                    			tooltip: 'حذف رديفهاي انتخابي',
                    			iconCls:'remove',
                    			handler: " . $this->deleteHandler . "}," : "" ;

		for($i=0; $i < count($this->buttons); $i++)
			$grid .= "'-'," . $this->buttons[$i] . ",";

		$grid = ($grid[strlen($grid)-1] == ",") ? substr($grid,0,strlen($grid)-1) : $grid;

		$grid .= "   ]";

        if ($this->EnableGrouping)
        {
        	$grid .= ",view: new Ext.grid.GroupingView({forceFit:true,startCollapsed:";
			$grid .= ($this->startCollapsed) ? "true," : "false,";
	    	$grid .= ($this->groupRenderer != "") ? "groupHeaderRenderer: '" . $this->groupRenderer . "'," : "";
	    	$grid .= "groupTextTpl: '{text} ({[values.rs.length]} رکورد)'})" ;
        }

		if($this->CurrencyStringRowDataIndex != "")
		{
			$grid .= "
				,faSummeryEl : ''
				,listeners : {
				viewready : function(){
					var sums = this.plugins[0].getSummaryValuesArray();
					var value = sums[".$this->CurrencyStringRowDataIndex."];
					this.faSummeryEl = {
						tag: 'div',
						cls: 'x-grid3-summary-row',
						html: '&nbsp;<br>' + CurrencyToString(value) + '<br>&nbsp'};
					this.faSummeryEl = Ext.DomHelper.insertAfter(this.plugins[0].getSummaryNode(),this.faSummeryEl,true);

					this.getStore().on('load',function(grid){
						var sums = grid.plugins[0].getSummaryValuesArray();
						var value = sums[".$this->CurrencyStringRowDataIndex."];
						grid.faSummeryEl.update('&nbsp;<br>' + CurrencyToString(value) + '<br>&nbsp;');
					}.createDelegate(this,[this]));

					
				}
			";
		}

		$grid .= ($this->CurrencyStringRowDataIndex == "") ? ",listeners : {" : ",";
		$grid .= "afterrender : function(){this.getStore().load(";
		$grid .= ($this->EnablePaging) ? "{params:{start:0, limit:$this->pageSize}}" : "";
		$grid .= ");}";

		$grid .= ",beforerender : function(){if(this.bottomToolbar)this.bottomToolbar.bind(this.store);}";

		$grid .=  "}})";
        //***************************************************
		return $grid;
	}
	//-------------------------------------------------------------------------
	private function setExtColumnModel()
	{
		$str = "";
		
		for($i = 0; $i < count($this->columns); $i++)
		{
			$str .= "{id: '" . $this->id . "_cl$i', ";

			$tempArr = get_object_vars($this->columns[$i]);
			$tempKeyArr = array_keys($tempArr);

			for($j = 0; $j <count($tempArr); $j++)
			{
				if(isset($tempArr[$tempKeyArr[$j]]))
				{
					switch($tempKeyArr[$j])
					{
						case "renderer":
						case "summaryRenderer":
						case "width":
						case "dblclick":
							$str .= $tempKeyArr[$j] . ": " . $tempArr[$tempKeyArr[$j]] . ", ";
							break;
						case "type":
							if($tempArr[$tempKeyArr[$j]] == "")
								$str .= "type:'string', ";
							$str .= "type: '" . $tempArr[$tempKeyArr[$j]] . "', ";
							if($tempArr[$tempKeyArr[$j]] == GridColumn::ColumnType_date)
								$str .= "renderer: function(v){return MiladiToShamsi(v,'Y/m/d');}, ";
							break;

						case "editor":
							$st = $tempArr[$tempKeyArr[$j]];
							$st = str_replace("@@",  $tempArr["dataIndex"], $st);
							$str .= "editor: " . $st . ", ";
							if(strpos($st, "ComboBox") != false)
								$str .= "renderer: function(v,p,r){
											var store = this.editor.getStore();
											store.clearFilter();
											var index = store.findExact(this.editor.valueField, v);
											var record = store.getAt(index);
											return (record) ? record.get(this.editor.displayField) : '';
										}, ";
							break;
							
						case "hidden":
						case "searchable":
							$flag = $tempArr[$tempKeyArr[$j]] ? "true" : "false";
							$str .= $tempKeyArr[$j] . ": " . $flag . ", ";
							break;

						default:
							$str .= $tempKeyArr[$j] . ": '" . $tempArr[$tempKeyArr[$j]] . "', ";
					}
				}
			}

			$str = substr($str, 0, strlen($str) - 2) . "},";
		}
		$str = substr($str, 0, strlen($str) - 1);
		
		return $str;
	}
	//-------------------------------------------------------------------------
	private function setExtReader()
	{

		$str = " new Ext.data.JsonReader({
					root: 'rows',
					totalProperty: 'totalCount',";
		$str .= (isset($this->primaryKey)) ? "id: '" . $this->primaryKey . "'," : "";

		$str .= "fields:[";

		for ($i=0; $i < count($this->columns); $i++)
			$str .= "{name: '" . $this->columns[$i]->dataIndex . "'},";

		$str = substr($str, 0, strlen($str) - 1) . "]})";

		return $str;

	}
	//-------------------------------------------------------------------------
	function AddCurrencyStringRow($summaryColumnIndex)
	{
		$this->CurrencyStringRow = "Ext.onReady(function(){" .
			$this->id."_grid.on('viewready',function(){
				var sums = ".$this->id."_grid.plugins[0].getSummaryValuesArray();
				var value = sums[".$summaryColumnIndex."];
				Ext.DomHelper.insertAfter(".$this->id."_grid.plugins[0].getSummaryNode(),
					'<div id=\"".$this->id."_fa_summary\" style=\"font-weight:bold\" class=\"x-grid3-summary-row\">'+
					'&nbsp;<br>' + CurrencyToString(value) + '<br>&nbsp;</div>',true);
			});

			".$this->id."_store.on('load',function(){
				var sums = ".$this->id."_grid.plugins[0].getSummaryValuesArray();
				var value = sums[".$summaryColumnIndex."];
				document.getElementById('".$this->id."_fa_summary').innerHTML = '&nbsp;<br>' + CurrencyToString(value) + '<br>&nbsp;';
			});
		});";
		$this->CurrencyStringRowDataIndex = $summaryColumnIndex;
	}

}

class GridColumn
{
	public $id;
	public $align;
	
	public $header;
	public $dataIndex;
	
	public $width ;    //Number
	public $sortable ; //Boolean
	
	const ColumnType_date = "date";
	const ColumnType_boolean = "boolean";
	const ColumnType_string = "string";
	public $type;
	
	public $fixed;    //Boolean
	public $hidden = false;    //Boolean
	
	/**
	   * The function to use to process the cell's raw data to
	   *      return HTML markup for the grid view.
	   * <script type="text/javascript">
	   * function renderTopic(value, p, record){
	   *     return String.format(
	   *             '<b><a href="showthread.php?t={2}" target="_blank">{0}</a></b><a href="forumdisplay.php?f={3}" target="_blank">{1} Forum</a>',
	   *             value, record.data.forumtitle, record.id, record.data.forumid);
	   * }
	   * </script>
	   * $Col = new GridColumn();
	   * @var unknown_type
	   */
	public $renderer;  //Function
	   
	/**
	   * if the 'editorGrid' property of grid set true,you can define the column in editor mode
	   * for example "new Ext.form.NumberField({allowBlank: false,allowNegative: false,maxValue: 100})" 
	   *
	   * @var string
	   */
	public $editor;
	
	const SummeryType_sum = "sum";
	const SummeryType_count = "count";
	const SummeryType_min = "min";
	const SummeryType_max = "max";
	const SummeryType_average = "average";
	public $summaryType;
	public $summaryRenderer;
	
	public $dblclick;

	public $searchable = true;
	/**
   * Enter description here...
   *
   * @param String $header
   * @param String $dataIndex
   * @param String $type  {'string','int','float','boolean','date'}
   * @return GridColumn
   */
	public function  __construct($header, $dataIndex, $type = GridColumn::ColumnType_string, $hidden = false)
	{
		$this->header= $header;
		$this->dataIndex = $dataIndex;
		$this->type = $type;
		$this->hidden = $hidden;
		$this->sortable = true;
	}
}

class ColumnEditor
{
	static function ComboBox($comboBoxID, $extComboID = "", $listeners = "")
	{
		$str = "new Ext.form.ComboBox({";
		$str .= ($extComboID != "") ? "id: '$extComboID'," : "";
        $str .= "typeAhead: true,
                triggerAction: 'all',
				transform: '$comboBoxID',
				lazyRender: true,
                listClass: 'x-combo-list-small',
				listeners: {" . $listeners . "}
            })";
		return $str;
	}

	static function SlaveComboBox($masterExtComboID, $datasource, $displayField, $valueField, $masterField)
	{
		$str = "new Ext.form.ComboBox({
			id: Ext.id(),
			typeAhead: true,
            triggerAction: 'all',
			store: new Ext.data.JsonStore({
		        data: " . common_component::PHPArray_to_JSArray($datasource) . ",
		        fields: ['$valueField', '$displayField', '$masterField']
		    	}),
			mode: 'local',
			displayField:'$displayField',
			valueField:'$valueField',
			lazyRender: true,
            listClass: 'x-combo-list-small',
			listeners:{
				'beforeSetValue': function(combo,record){
					Ext_BindDropDown('$masterExtComboID',this.id,'$masterField');
				},
				'afterSetValue': function(){
					Ext_BindDropDown('$masterExtComboID',this.id,'$masterField');
				},
				'expand': function(){
					Ext_BindDropDown('$masterExtComboID',this.id,'$masterField');
				}
			}
        })";
		return $str;
		
	}

	static function SHDateField($allowBlank = false)
	{
		$st = "new Ext.form.SHDateField({allowBlank:";
		$st .= $allowBlank ? "true," : "false,";
		$st .= "format: 'Y/m/d'})";
		return $st;
	}

	static function NumberField($allowBlank = false, $maxValue = "")
	{
		$st = "new Ext.form.NumberField({allowBlank: ";
		$st .= ($allowBlank) ? "true" : "false";
		$st .= ($maxValue != "") ? ",maxValue: " . $maxValue  : "";
		$st .= "})";
		return $st;
	}

    static function CheckField()
	{
		$st = " new Ext.form.Checkbox({
                    inputValue: 1
                })";
		return $st;
	}
	static function TextField($allowBlank = false)
	{
		$st = "new Ext.form.TextField({allowBlank: " ;
		$st .= ($allowBlank) ? "true" : "false";
		$st .= "})";
		return $st;
	}
}
?>
