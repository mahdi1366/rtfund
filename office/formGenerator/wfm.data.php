<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	89.02
//---------------------------
require_once 'header.php';
require_once '../formGenerator/form.class.php';

$task = isset($_POST["task"]) ? $_POST["task"] : (isset($_GET["task"]) ? $_GET["task"] : "");

switch ($task)
{
	case "AllLetters":
		AllLetters();

	//........................
	
	case "CreatedFormsSelect":
		CreatedFormsSelect();
		
	case "ReceivedForms":
		ReceivedForms();
	//........................
	
	case "LetterSave":
		LetterSave();
		
	case "SendLetter":
		SendLetter();
		
	case "ReturnLetter":
		ReturnLetter();
		
	case "ChangeView":
		ChangeView();
		
	case "DeleteLetter" :
		DeleteLetter();
		
	case "ArchiveLetter":
		ArchiveLetter();
		
	case "DeleteRef":
		DeleteRef();
		
	case "ResponseLetter":
		ResponseLetter();
		
	case "SaveArchiveLevelID":
		SaveArchiveLevelID();
		
	case "GetArchiveLevelID":
		GetArchiveLevelID();	

	case "ApplyChanges":
		ApplyChanges();
	//.......................
	
	case "SelectAttach":
		SelectAttach();
		
	case "SaveAttach":
		SaveAttach();
		
	case "DeleteAttach":
		DeleteAttach();
		
	//.......................
	
	case "ReplacementSelect":
		ReplacementSelect();
		
	case "ReplacementSave":
		ReplacementSave();
		
	case "ReplacementDelete":
		ReplacementDelete();
}

function AllLetters()
{
	$where = "1=1";
	
	if(isset($_GET["fields"]))
	{
		switch ($_GET["fields"])
		{
			case "LetterID" :
				$where .= " and LetterID=" . $_GET["query"];
				break;
			case "FormName":
				$where .= " and FormName like '%" . $_GET["query"] . "%'";
				break;
			case "fullname":
				$where .= " and concat(fname,' ',lname) like '%" . $_GET["query"] . "%'";
				break;
			case "regDate":
				$where .= " and regDate='" . CommenModules::Shamsi_to_Miladi($_GET["query"]) . "'";
				break;
		}
	}
	
	if(isset($_GET["ArchiveLevelID"]))
		$where .= " and ArchiveLevelID=" . $_GET["ArchiveLevelID"];
	
	$no = count(wfm_form::select($where));
	
	$temp = wfm_form::select($where . " order by " . $_GET["sort"] . " " . $_GET["dir"] . 
		" limit " . $_GET["start"] . "," . $_GET["limit"]);
	
	echo dataReader::getJsonData($temp, $no, $_GET["callback"]);
	die();
}

//...................................................................

function CreatedFormsSelect()
{
	$where ="w.PersonID=" . $_SESSION["PersonID"];
	$no = count(wfm_form::select($where));
	
	$temp = wfm_form::select($where . " order by " . $_GET["sort"] . " " . $_GET["dir"] . 
		" limit " . $_GET["start"] . "," . $_GET["limit"]);
	
	echo dataReader::getJsonData($temp, $no, $_GET["callback"]);
	die();
}

function ReceivedForms()
{
	$ArchiveFlag = isset($_GET["archiveFlag"]) ? "1" : "0";
	
	$query = "select distinct s.*,
					f.referenceID,
					f.FormID,
					ff.FormName,
					concat(u.name,' ',u.family) as sender,
					po.title as postTitle,
					un.title as unitTitle,
          			wf.StepTitle,
          			tbl.maxStep as maxStep,
					if(fa.ElementID is null, 0, 1) as ApplyAccess,
					if(fa2.ElementID is null, 0, 1) as CopyAccess,
					DATEDIFF(ADDDATE(s.SendDate,wfm.BreakDuration),now()) as TimeOut,
					concat('" . $_SESSION["AGENCY"] . "','/',
						(case ff.reference when 'devotions' then '1'
									  	 when 'states' then '2'
									  	 when 'rents' then '3' end),'-',f.referenceID,'/',s.LetterID) as pursuitCode
					
		    from wfm_send as s
		      	join wfm_forms as f on(f.LetterID=s.LetterID)
				
		      	join um_user as u on(s.FromPersonID=u.PersonID)
				left join org_posts as po on(u.PersonID=po.PersonID)
				left join org_units as un on(po.ouid=un.ouid)
				
				join fm_forms as ff on(ff.FormID=f.FormID)
		      	join wfm_send as s2 on(s.LetterID=s2.LetterID and s2.SendStatus='raw')
		      	join fm_workflow as wf on(f.FormID=wf.FormID and wf.StepID=s2.StepID)
				join (select max(StepID)as maxStep,FormID from fm_workflow group by FormID)as tbl on(tbl.FormID=f.FormID)
				
				left join fm_workflow as wfm on(wfm.FormID=f.FormID and wfm.StepID=s.StepID)
        		left join wfm_replacement as r on(r.src_PersonID=wfm.PersonID and 
        			r.des_PersonID=" . $_SESSION["PersonID"] . " and
          			StartDate <= now() and now() <= EndDate)
          
				left join fm_element_access as fa 
					on(fa.PersonID in(" . $_SESSION["PersonID"] . ",r.src_PersonID) and 
						fa.FormID=f.FormID and fa.ElementID=2000)
				left join fm_element_access as fa2 
					on(fa.PersonID in(" . $_SESSION["PersonID"] . ",r.src_PersonID) and 
						fa.FormID=f.FormID and fa.ElementID=2001)
				
		where s.ToPersonID=" . $_SESSION["PersonID"] . " and s.DeleteFlag=0 and s.ArchiveFlag=" . $ArchiveFlag ;
	
	$where = "";
	if(isset($_GET["fields"]))
	{
		switch ($_GET["fields"])
		{
			case "sender" :
				$where = " and concat(u.name,' ',u.family) like '%" . $_GET["query"] . "%'"; 
				break;
			case "LetterID" :
				$where = " and s.LetterID=" . $_GET["query"]; 
				break;
			case "FormName" :
				$where = " and ff.FormName like '%" . $_GET["query"] . "%'"; 
				break;
			case "SendDate" :
				$where = " and s.SendDate >= '" . 
					str_replace('/','-',CommenModules::Shamsi_to_Miladi($_GET["query"])) . " 00:00:00'"; 
				break;
		}
	}
	if(!empty($_GET["fromDate"]))
		$query .= " and s.SendDate >='" . 
			str_replace('/','-',CommenModules::Shamsi_to_Miladi($_GET["fromDate"])) . " 00:00:00'";
			
	if(!empty($_GET["toDate"]))
		$query .= " and s.SendDate <='" . 
			str_replace('/','-',CommenModules::Shamsi_to_Miladi($_GET["toDate"])) . " 24:60:60'";
	
	if(!empty($_GET["form"]))
		$query .= " and f.FormID =" . $_GET["form"];
		
	if(!empty($_GET["PersonID"]))
		$query .= " and s.FromPersonID =" . $_GET["PersonID"];
			
	$query .= $where;
	$no = count(dataAccess::RUNQUERY($query));
	
	$temp = dataAccess::RUNQUERY($query . " order by " . $_GET["sort"] . " " . $_GET["dir"] . 
		" limit " . $_GET["start"] . "," . $_GET["limit"]);
	
	echo dataReader::getJsonData($temp, $no, $_GET["callback"]);
	die();
}

//...................................................................

function LetterSave()
{
	$obj = new wfm_form();
	
	if($_POST["LetterID"] == "")
	{
		$obj->FormID = $_POST["FormID"];
		$obj->referenceID = $_POST["referenceID"];
		$obj->wfm00 = $_SESSION["AGENCY"];
		$obj->PersonID = $_SESSION["PersonID"];
		$obj->regDate = date("Y-m-d");
	
		$obj->insert();
		$obj->LetterID = $obj->LastID();
		dataAccess::AUDIT("ایجاد فرم کد[" . $obj->LetterID . "] ");
	}
	else 
		$obj->LetterID = $_POST["LetterID"];
	//...........................................................

	dataAccess::RUNQUERY("update wfm_form_details as w join fm_form_details as f using(ElementID)
		 set w.ElementValue='' where LetterID=" . $obj->LetterID . 
		 " and w.ElementID=f.ElementID and f.ElementType='check'");
	
	$post_keys = array_keys($_POST);
	for($i=0; $i<count($post_keys); $i++)
	{
		if(strpos($post_keys[$i], "elem_") !== false)
		{
			$elemObj = new wfm_form_detail();
			
			$st = split("_", $post_keys[$i]);
			$elemObj->ElementID = $st[1];
			//..............................................
			$elemInfo = dataAccess::RUNQUERY("select * from fm_form_details where ElementID=" . 
				$elemObj->ElementID);
			
			if($elemInfo[0]["ElementType"] == "date")
				$elemObj->ElementValue = CommenModules::Shamsi_to_Miladi($_POST[$post_keys[$i]]);
			else 
				$elemObj->ElementValue = $_POST[$post_keys[$i]];
			//..............................................
			
			$query = "insert into wfm_form_details values('" . $obj->LetterID . "',
				" . $elemObj->ElementID . ",'" . $elemObj->ElementValue . "')
				on duplicate key update";
			//---------------------- save check values ------------------------------
			if(isset($st[2]))
			{
				$query .= " ElementValue = if(ElementValue='','" . $elemObj->ElementValue  . "',
							concat(ElementValue,':','" . $elemObj->ElementValue . "'))";
			}
			else 
				$query .= " ElementValue = '" . $elemObj->ElementValue . "'";
			//-----------------------------------------------------------------------
			dataAccess::RUNQUERY($query);
			
			dataAccess::AUDIT("ویرایش اجزاء فرم کد فرم[" . $obj->LetterID . "] و کد جزء[" . 
				$elemObj->ElementID . "] ");
		}
	}
	echo "true";
	die();
}

function SendLetter()
{
	if($_POST["SendType"] == "ref")
	{
		$StepID = isset($_POST["StepID"]) ? $_POST["StepID"] : 1;
		{
			$temp = dataAccess::RUNQUERY("
				select if(r.des_PersonID is null, w.PersonID, r.des_PersonID) as ToPersonID
	
				from fm_workflow as w
				left join wfm_replacement as r 
					on(src_PersonID=w.PersonID and StartDate <= now() and now() <= EndDate)
				
				where StepID=" . $StepID . "+1 and FormID=" . $_POST["FormID"]);
			
			if(count($temp) == 0)
			{
				echo "EndError";
				die();
			}
			$FromPersonID = $_SESSION["PersonID"];
			$ToPersonID = $temp[0]["ToPersonID"];
		}
	}
	else 
	{
		$FromPersonID = $_SESSION["PersonID"];
		$ToPersonID = $_POST["PersonID"];
	}
	//....................................
	$db = dataAccess::GET_DB();
	/*@var $db ADODB*/
	$db->BeginTrans();
	
	dataAccess::RUNQUERY("insert into wfm_send(LetterID,ToPersonID,SendDate,
		SendType,SendComment,ViewFlag,FromPersonID,StepID)
		values(" . $_POST["LetterID"] . ",".
					$ToPersonID . ",now(),'" .
					$_POST["SendType"] . "','" .
					$_POST["SendComment"] . "',0," . 
					$FromPersonID . "," . 
					$StepID . "+1)");

	dataAccess::AUDIT("ارسال نامه کد[" . $_POST["LetterID"] . "] به [" . $ToPersonID . "] توسط [" .
	 	$_SESSION["PersonID"]. "]");
	 	
	 if($_POST["RefID"] != "")	
		dataAccess::RUNQUERY("update wfm_send set SendStatus='send' where RefID=" . $_POST["RefID"]);
		
	$db->CommitTrans();
	//....................................
	
	echo "true";
	die();
}

function ReturnLetter()
{
	$temp = dataAccess::RUNQUERY("
		select if(r.des_PersonID is null, w.PersonID, r.des_PersonID) as ToPersonID

		from fm_workflow as w
		left join wfm_replacement as r on(src_PersonID=w.PersonID and StartDate <= now() and now() <= EndDate)
		
		where StepID=" . $StepID . "-1 and FormID=" . $_POST["FormID"]);
	
	$FromPersonID = $_SESSION["PersonID"];
	$ToPersonID = $temp[0]["PersonID"];
	//....................................
	$db = dataAccess::GET_DB();
	/*@var $db ADODB*/
	$db->BeginTrans();
	
	dataAccess::RUNQUERY("insert into wfm_send(LetterID,ToPersonID,SendDate,
		SendType,SendComment,ViewFlag,FromPersonID,StepID)
		values(" . $_POST["LetterID"] . ",".
					$ToPersonID . ",now(),'" .
					$_POST["SendType"] . "','" .
					$_POST["SendComment"] . "',0," . 
					$FromPersonID . "," . 
					$_POST["StepID"] . "-1)");

	dataAccess::AUDIT("برگشت نامه کد[" . $_POST["LetterID"] . "] به [" . $ToPersonID . "] توسط [" .
	 	$FromPersonID . "]");
	 	
	 dataAccess::RUNQUERY("update wfm_send set SendStatus='return' where RefID=" . $_POST["RefID"]);
	 $db->CommitTrans();
	//....................................
	
	echo "true";
	die();
}

function ChangeView()
{
	dataAccess::RUNQUERY("update wfm_send set ViewFlag=1 where RefID=" . $_POST["RefID"]);
	echo "true";
	die();
}

function DeleteLetter()
{
	wfm_form::delete("LetterID = " . $_POST["LetterID"]);
	dataAccess::AUDIT("حذف نامه کد[" . $_POST["LetterID"] . "]");
	echo "true";
	die();
}

function ArchiveLetter()
{
	dataAccess::RUNQUERY("update wfm_send set ArchiveFlag=" . $_POST["ArchiveFlag"] . 
		" where RefID = " . $_POST["RefID"]);
	
	$st = ($_POST["ArchiveFlag"] == "0") ? "خروج از " : "";
	$st .= "بایگانی نامه کد[" . $_POST["LetterID"] . "] و شماره ارجاع[" . $_POST["RefID"] . "]";
	dataAccess::AUDIT($st);
	echo "true";
	die();
}

function DeleteRef()
{
	dataAccess::RUNQUERY("update wfm_send set DeleteFlag=1 where RefID = " . $_POST["RefID"]);
	
	dataAccess::AUDIT("حذف نامه کد[" . $_POST["LetterID"] . "] و شماره ارجاع[" . $_POST["RefID"] . "]");
	echo "true";
	die();
}

function ResponseLetter()
{
	dataAccess::RUNQUERY("update wfm_send set Response='" . $_POST["Response"] . 
		"', ResponseDate=now() where RefID = " . $_POST["RefID"]);
	
	dataAccess::AUDIT("پاسخ به نامه کد[" . $_POST["LetterID"] . "] و شماره ارجاع[" . $_POST["RefID"] . "]");
	echo "true";
	die();
}

function SaveArchiveLevelID()
{
	dataAccess::RUNQUERY("update wfm_forms set ArchiveLevelID=" . $_POST["ArchiveLevelID"] . 
		" where LetterID=" . $_POST["LetterID"]);
	dataAccess::AUDIT("بایگانی فرم کد[" . $_POST["LetterID"] . "] در سطح بایگانی سازمانی[" . 
		$_POST["ArchiveLevelID"] . "]");
		
	echo "true";
	die();
}

function GetArchiveLevelID()
{
	$temp = dataAccess::RUNQUERY("select archive_levels.title as title
		from wfm_forms left join archive_levels on(levelID=ArchiveLevelID)");
	
	echo $temp[0]["title"];
	die();
}

function ApplyChanges()
{
	$query = "select w.ElementValue,referenceField,wf.referenceID,ff.reference

		from wfm_form_details as w
		join wfm_forms as wf on(w.LetterID=wf.LetterID)
		join fm_forms as ff on(ff.FormID=wf.FormID)
		join fm_form_details as f on(wf.FormID=f.FormID and w.ElementID=f.ElementID)
		
		where referenceField<>'0' and w.LetterID=" . $_POST["LetterID"];
	
	$temp = dataAccess::RUNQUERY($query);
	
	if(count($temp) == 0)
	{
		echo "EmptyError";
		die();
	}
	
	$query = "update " . $temp[0]["reference"] . " set ";
	
	for($i=0; $i<count($temp); $i++)
		$query .= $temp[$i]["referenceField"] . "='" . $temp[$i]["ElementValue"] . "',";
		
	$query = substr($query, 0, strlen($query)-1);
	
	switch ($temp[0]["reference"])
	{
		case "states" :
			$query .= " where sta02=" . $temp[0]["referenceID"] . " and sta00=" . $_SESSION["AGENCY"];
			break;
		case "devotions" :
			$query .= " where dvt01=" . $temp[0]["referenceID"] . " and dvt00=" . $_SESSION["AGENCY"];
			break;
		case "rents" :
			$query .= " where rnt02=" . $temp[0]["referenceID"] . " and rnt00=" . $_SESSION["AGENCY"];
			break;
	}
	
	dataAccess::RUNQUERY($query);
	echo "true";
	die();
	
}

//...................................................................

function SelectAttach()
{
	$temp = dataAccess::RUNQUERY("select wfm_attach.*, concat(name,' ',family)as PersonName 
		from wfm_attach join um_user using(PersonID) where LetterID=" . $_REQUEST["LetterID"]);
	
	echo dataReader::getJsonData($temp, count($temp), $_GET["callback"]);
	die();
}

function SaveAttach()
{
	$fileName = $_FILES['AttachFile']['name'];
	$i = 1;
	while(file_exists("../../" . AttachImagePath . $fileName))
	{
		$fileName = $i++ . "_" . $fileName; 
	}
	
	$fp = fopen("../../" . AttachImagePath . $fileName, "w");
	fwrite ($fp, fread(fopen($_FILES['AttachFile']['tmp_name'], 'r'), $_FILES['AttachFile']['size']));
	fclose ($fp);
	
	dataAccess::RUNQUERY("insert into wfm_attach(LetterID,PersonID,title,RegDate,FileName) 
		values(" . $_POST["LetterID"] . "," . $_SESSION["PersonID"] . ",'" . $_POST["AttachTitle"] . "',
			now(),'" . $fileName . "')");
	dataAccess::AUDIT("ایجاد پیوست برای نامه کد[" . $_POST["LetterID"] . "]");
	
	echo "true";
	die();
}

function DeleteAttach()
{
	unlink("../../" . AttachImagePath . $_POST["FileName"]);
	dataAccess::RUNQUERY("delete from wfm_attach where AttachID=" . $_POST["AttachID"]);
	dataAccess::AUDIT("حذف پیوست کد[" . $_POST["AttachID"] . "] و نامه کد[" . $_POST["LetterID"] . "]");
	
	echo "true";
	die();
}

//...................................................................

function GetHistoryTreeNodes()
{
	$letter_dt = dataAccess::RUNQUERY("select 0 as id,concat(u.name,' ',u.family) as user,'true' as leaf,
			'user_red' as iconCls,'' as SendDate,'col' as uiProvider,f.PersonID,'' as SendComment,
			f.PersonID as FromPersonID,'' as Response,'' as ResponseDate,
			'' as ArchiveFlag,'' as DeleteFlag,'' as ViewFlag
		from wfm_forms as f join um_user as u on(f.PersonID=u.PersonID) where LetterID=" . $_GET["LetterID"]);
	
	$nodes = array();
	$nodes[0] = $letter_dt[0];
		
	$ref_Persons = array();
	$ref_Persons[$nodes[0]["PersonID"]] = & $nodes[0];
	
	$temp = dataAccess::RUNQUERY("select RefID as id,concat(u.name,' ',u.family) as user,'true' as leaf
			,'user_red' as iconCls,s.SendComment,s.SendDate,'col' as uiProvider,
			s.FromPersonID,s.ToPersonID,s.Response,s.ResponseDate,s.ArchiveFlag,s.DeleteFlag,s.ViewFlag
		from wfm_send as s join um_user as u on(s.ToPersonID=u.PersonID) where LetterID=" . $_GET["LetterID"] . 
		" order by SendDate");
	
	if(count($temp) != 0)
	{
		for($i=0; $i<count($temp); $i++)
		{
			if(!isset($ref_Persons[$temp[$i]["FromPersonID"]]["children"]))
			{
				$ref_Persons[$temp[$i]["FromPersonID"]]["children"] = array();
				$ref_Persons[$temp[$i]["FromPersonID"]]["leaf"] = "false";
				$ref_Persons[$temp[$i]["FromPersonID"]]["iconCls"] = "user";
			}
			$ref_Persons[$temp[$i]["FromPersonID"]]["children"][] = $temp[$i];
			
			//------------------- add ToPersonID to ref array ----------------
			$new_node = & $ref_Persons[$temp[$i]["FromPersonID"]]["children"]
						[count($ref_Persons[$temp[$i]["FromPersonID"]]["children"])-1];
						
			if(isset($ref_Persons[$temp[$i]["ToPersonID"]]))
				$ref_Persons[$temp[$i]["ToPersonID"]] = & $new_node;
			else 
				$ref_Persons[$temp[$i]["ToPersonID"]] = & $new_node;
				
			//................................................................				
			
		}
	}
	
	$return_str = '{"text":"سابقه گردش فرم شماره' . $_GET["LetterID"] . '",SendDate:"","id":"source","children":';
	$return_str .= json_encode($nodes);
	$return_str .= '}';
	
	return $return_str;
}

//...................................................................

function ReplacementSelect()
{
	$query = "select *, 
		concat(u1.fname,' ',u1.lname) as src_fullName, 
		concat(u2.fname,' ',u2.lname) as des_fullName 
		
		from wfm_replacement as w
		join um_user as u1 on(u1.PersonID=w.src_PersonID)
		join um_user as u2 on(u2.PersonID=w.des_PersonID)";
	
	$no = count(dataAccess::RUNQUERY($query));
	
	$temp = dataAccess::RUNQUERY($query . " order by " . $_GET["sort"] . " " . $_GET["dir"] . 
		" limit " . $_GET["start"] . "," . $_GET["limit"]);
	
	echo dataReader::getJsonData($temp, $no, $_GET["callback"]);
	die();
}

function ReplacementSave()
{
	$startDate = CommenModules::Shamsi_to_Miladi($_POST["StartDate"]);
	$endDate = CommenModules::Shamsi_to_Miladi($_POST["EndDate"]);
	
	$temp = dataAccess::RUNQUERY("select * from wfm_replacement where src_PersonID=" . $_POST["src_PersonID"] . 
		" and (('$startDate'>=StartDate and '$startDate'<=EndDate) or 
				('$endDate'>=StartDate and '$endDate'<=EndDate))");
	
	if(count($temp) != 0)
	{
		echo "ConflictError";
		die();
	}
	
	//...........................
	
	$query = "insert into wfm_replacement(src_PersonID,des_PersonID,StartDate,EndDate) 
			values(" . $_POST["src_PersonID"] . "," . $_POST["des_PersonID"] . ",'" . 
			$startDate . "','" . $endDate . "')";
			
	dataAccess::RUNQUERY($query);
	dataAccess::AUDIT("ایجاد ردیف جایگزینی با فرد اصلی[" . $_POST["src_PersonID"] . 
		"] و فرد جایگزین[" . $_POST["des_PersonID"] . 
		"] با تاریخ شروع[" . $startDate . "]و تاریخ پایان[" . $endDate . "]");

	echo "true";
	die();
}

function ReplacementDelete()
{
	dataAccess::RUNQUERY("delete from wfm_replacement where RowID=" . $_POST["RowID"]);
	dataAccess::AUDIT("حذف ردیف جایگزینی برای فرد[" . $_POST["src_PersonID"] . "]");
	echo "true";
	die();
}

?>