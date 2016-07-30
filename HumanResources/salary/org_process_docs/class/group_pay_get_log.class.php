<?php
//---------------------------
// programmer:	Mahdipor
// create Date:	91.06
//---------------------------
	
class manage_group_pay_get_log
{	
	private $s_count = 0 ;
	private $u_count = 0 ;
	//..................
	private $s_content = "";
	private $u_content = "";
	
	function manage_group_pay_get_log()
	{                   
		$this->s_content = fopen("/tmp/s_content.txt", "w"); 
		$this->u_content = fopen("/tmp/u_content.txt", "w");     //fopen("D:\apache\htdocs\upload_dir\u_content.txt", "w");
		
		fwrite($this->s_content, "<center><table style='display:none; width:90%;border-collapse: collapse;' id='cancel_success'>");
		fwrite($this->u_content, "<center><table style='display:none; width:90%;border-collapse: collapse;' id='cancel_usuccess'>");
               
	}
	
	function finalize()
	{
		fwrite($this->s_content, '</table></center>');
		fclose($this->s_content);
		fwrite($this->u_content , '</table></center>');
		fclose($this->u_content);
	}
	
	function make_success_row($person_rec)
	{
		if(ExceptionHandler::GetExceptionCount() == 0)
		{
			$this->s_count++;
			fwrite($this->s_content, "
			<tr style='font-family:tahoma;font-size:11px;font-weight:bold;color:#1A58A6;border-bottom:solid 1px #1A58A6;'>
	            <td height=21px>شماره شناسايي :$person_rec[staff_id]</td>
	            <td>نام : $person_rec[pfname]</td>
	            <td>نام خانوادگي : $person_rec[plname]</td>
	            <td>شماره حکم : $person_rec[writ_id]</td>
	            </tr>");
		}
		else 
			self::make_unsuccess_rows($person_rec);
		
	}
	
	function make_unsuccess_rows($staffID , $FullName, $Msg ="")
	{
		$this->u_count++;
		fwrite($this->u_content, "
		    <tr style='font-family:tahoma;font-size:11px;font-weight:bold;color:#1A58A6;border-bottom:solid 1px #1A58A6;'>
		    <td width=500px >شماره شناسايي :  $staffID</td>
	            <td width=700px > نام و نام خانواگی :  $FullName </td>	           	          
		    <td height=21px width=900px>پیام : $Msg  </td>
	            </tr>");               
		
                 
	}

	function make_result($func="")
	{
			if($func=="")
				$onClick= " UploadFilesObj.expand();"  ; 
			else 
				$onClick= $func  ; 
		
            $res = 'افرادی که ثبت آنها با خطا مواجه شده است .';

            $res.= ($this->u_count == 0) ? '0 <br>' :  
                                "<a href='javascript:void(0);' onclick='".$onClick."'>" .
				$this->u_count.' مشاهده... '.'</a></b><br><br>' .file_get_contents("/tmp/u_content.txt").'<br><br>';
            return $res ; 
	}
}
?>