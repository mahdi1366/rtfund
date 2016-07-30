<?php

	require_once('header.inc.php');
	require_once('pdodb.class.php');
	$UserID = $_SESSION['UserID'];
	$HrmsPersonID = $_SESSION["PersonID"];
    HTMLBegin();
if ($_SESSION['PersonID'] == 356)
        echo "<br>info<br>".$_SESSION['PersonID']."<br>";
?>
 
<script src="../../shares/General.js"></script>
<?
   
    function isAllowedExtension($fileName) {
       
     return in_array(strtolower(end(explode(".", $fileName))),array("jpg","jpeg"));
     
    }
    $PhotoSwitch = "";
    $imgsql = pdodb::getInstance();
	$ProfQuery = "SELECT *
					FROM photo.StaffPhotos 
					WHERE PersonID = ? ";
	$imgsql->prepare ($ProfQuery);
	$stmt = $imgsql->ExecuteStatement (array($HrmsPersonID));		
	$img_res = $stmt->fetchAll();
	
		
	if (isset($_POST["PhotoSubmit"]))
	{ 		
		//
		$info_sql = pdodb::getInstance();
		$qry = " update hrmstotal.persons set mobile_phone ='".$_POST['mobile_phone']."' , 
										 efname = '".$_POST['EFName']."' , 
		                                 elname = '".$_POST['ELName']."',home_phone1='".$_POST['home_phone1']."',
                                                         address1='".$_POST['address1']."',email='".$_POST['email']."'
									 where PersonID= ? " ;
		
		$info_sql->prepare ($qry);
		$info_sql->ExecuteStatement(array($_SESSION['PersonID']));		
		/*if( $_SESSION['UserID'] == 'bmahdipour') 
		{
		  	echo $qry."----".$_SESSION['PersonID'] ;	 die();
		}*/
		
		if (trim($_FILES['ProfPhoto']['name']) == '' ) 
		{   
			//$message=' نام فایل خالی است ';
			$PhotoSwitch = false;
		}
		elseif ( $_FILES['ProfPhoto']['error'] != 0 )
			$message=' خطا در ارسال فایل' . $_FILES['ProfPhoto']['error'];
		elseif 	($_FILES['ProfPhoto']['size'] > $_POST['MAX_FILE_SIZE'] )
			$message=' طول فایل بیش از 50 کیلو بایت است ';
	////////////
	  
	
       elseif(isAllowedExtension($_FILES['ProfPhoto']['name'])!=1) {
            $message= "فرمت عکس قابل قبول نمی باشد.";
       } 
	   	
	   
	
	/////////////		
			
		else
		{ 
			$_size = $_FILES['ProfPhoto']['size'];
			$_name = $_FILES['ProfPhoto']['tmp_name'];
			$data = addslashes((fread(fopen($_name, 'r' ),$_size)));
			$PhotoQuery = "";
			//اگر استاد قبلا عکس داشته است
			$Photosql = pdodb::getInstance("","","","photo","");
		
			if (count($img_res) > 0){
			  $PhotoQuery = "UPDATE photo.StaffPhotos SET picture='$data' WHERE PersonID=".$img_res[0]['PersonID'];
			  $auditmessage = 'بروز رسانی عکس';
			}
			else{
			  $PhotoQuery = "INSERT INTO photo.StaffPhotos (PersonID, picture) VALUES ($HrmsPersonID, '$data')";
			  $auditmessage = 'اضافه کردن عکس';
			}
			$Photo_res = $Photosql->ExecuteBinary($PhotoQuery);
	                $Photosql->audit($auditmessage);
			//Added by Bagheri (2013-Oct-23) -- pass is here temporary.
			$oaPhotoQuery = "UPDATE officeas.uni_pic SET picture='$data' WHERE uid='".$UserID."'";
	        $oamysql = pdodb::getInstance("172.20.20.36", "picuser", "sp#U12_oA", "officeas");
			$oamysql->ExecuteBinary($oaPhotoQuery);
			$auditmessage = 'ﺏﺭﻭﺯ ﺮﺳﺎﻧی ﻉکﺱ اتوماسیون';
			$Photosql->audit($auditmessage);
			//End of Bagheri.
			$PhotoSwitch =true;
		}
	}
	
	if( $PhotoSwitch == true)	{
		StartJavascript();
			echo "alert('تغییر عکس با موفقیت انجام شد');";
		EndJavascript();	
	
	}
	
	$mysql = pdodb::getInstance();
    $staffinfo_query = "select * from staff where PersonID= ? ";
    $mysql->prepare ($staffinfo_query);
	$stmt = $mysql->ExecuteStatement (array($_SESSION['PersonID']));		
	$staffinfo_result = $stmt->fetchAll();


	$PersonType = $staffinfo_result[0]['person_type'];

	if($PersonType=="2" || $PersonType=="3")
	 	    $hrmsDB = "hrms";
	else
			$hrmsDB = "hrms_sherkati";

	$query = "select       p.PersonID,
                               p.pfname,
                               p.plname,
                               p.efname,
                               p.elname,
                               p.father_name,
                               p.idcard_no,
                               p.birth_date,
                               p.birth_place,
                               p.issue_city_id,
                               p.issue_state_id,
                               p.country_id,
                               p.national_code,
                               p.sex,
                               p.marital_status,
                               p.locality_type,
                               p.address1,
                               p.postal_code1,
                               p.home_phone1,
                               p.address2,
                               p.postal_code2,
                               p.home_phone2,
                               p.work_phone,
                               p.work_int_phone,
                               p.mobile_phone,
                               p.email,
                               p.religion,
                               p.subreligion,
                               p.nationality,
                               p.insure_no,
                               p.military_status,
                               p.military_type,
                               p.military_from_date,
                               p.military_to_date,
                               p.military_duration,
                               p.military_comment,
                               s.person_type,
                               s.staff_id,
                               w.ouid,
                               w.sub_ouid, " ;
			
			
	if( $PersonType == 300 || $PersonType == 200 )
		{
			$query .= " o.ptitle org_unit_title , ";
		}
		  
    else  {
    	   $query .= " CONCAT(o.ptitle,'-',if(os.ptitle is null,'',os.ptitle)) org_unit_title, " ;
    	  }		
		
		$query .= "     po.title po_title,
                        o.ptitle o_ptitle
					 from hrmstotal.persons p
	                     LEFT OUTER JOIN hrmstotal.staff s
	                          ON (p.PersonID = s.PersonID )
	                     LEFT OUTER JOIN hrmstotal.writs w
	                          ON ((s.last_writ_id = w.writ_id) AND (s.last_writ_ver = w.writ_ver) AND 
	                              (s.staff_id = w.staff_id))
	                      ";
		
		
		if( $PersonType == 300 || $PersonType == 200 )		
		{
			$query .= " LEFT OUTER JOIN hrmstotal.org_new_units o
		                                  ON (s.unitcode = o.ouid)

		                             LEFT OUTER JOIN hrmstotal.position po
		                                  ON (w.post_id = po.post_id)
							 where   p.PersonID = ? ";
		}
		  
	    else  {
	    	   $query .= " 	 LEFT OUTER JOIN hrmstotal.org_new_units o
		                          ON (w.ouid = o.ouid)
		                     LEFT OUTER JOIN hrmstotal.org_new_units os
		                          ON ((w.ouid = os.ouid) AND (w.sub_ouid = os.parent_ouid))
		                     LEFT OUTER JOIN hrmstotal.position po
		                          ON (w.post_id = po.post_id)
							where
							   p.PersonID = ? " ;
	    	  }		
		
		
		
		$mysql->prepare ($query);
		$stmt = $mysql->ExecuteStatement (array($_SESSION['PersonID']));		
		$res = $stmt->fetchAll();
			
		if(count($res)==0)
		{
			echo "<p align=center><font face=tahoma size=4 color=red>با مشخصات وارد شده كارمندي پيدا نشد</font>";
			return;
		}
		$efname = $res[0]["efname"];
		$elname = $res[0]["elname"];
		
		$insure_query = " select  distinct  pd.PersonID,
						             case dependency when  1 then  'خود شخص'
						                             when  2 then 'پدر'
						                             when  3 then 'مادر'
						                             when  4 then 'همسر'
						                             when  5 then 'پسر'
						                             when  6 then 'دختر'
						                             when  7 then 'برادر'
						                             when  8 then 'خواهر'
						                             when  9 then 'ناپدري'
						                             when  10 then 'نامادري'
						                             when  11 then 'خواهر ناتني'
						                             when  12 then 'برادر ناتني'
						                             when  13 then 'پدر بزرگ'
						                             when  14 then 'مادر بزرگ'
						                             when  15 then 'عروس'
						                             when  16 then 'داماد'
						                             when  17 then 'نوه'
						                             when  18 then 'خاله'
						                             when  19 then 'پسر خاله'
						                             when  20 then 'دختر خاله'
						                             when  21 then 'شوهر خاله'
						                             when  22 then 'عروس خاله'
						                             when  23 then 'داماد خاله'
						                             when  24 then 'دايي'
						                             when  25 then 'پسر دايي'
						                             when  26 then 'دختر دايي'
						                             when  27 then 'همسر دايي'
						                             when  28 then 'عروس دايي'
						                             when  29 then 'داماد دايي'
						                             when  30 then 'عمو'
						                             when  31 then 'پسر عمو'
						                             when  32 then 'دختر عمو'
						                             when  33 then 'همسر عمو'
						                             when  34 then 'عروس عمو'
						                             when  35 then 'داماد عمو'
						                             when  36 then 'عمه'
						                             when  37 then 'پسر عمه'
						                             when  38 then 'دختر عمه'
						                             when  39 then 'شوهر عمه'
						                             when  40 then 'عروس عمه'
						                             when  41 then 'داماد عمه'
						                             when  42 then 'برادر زن'
						                             when  43 then 'خواهر زن'
						                             when  44 then 'مادر زن'
						                             when  45 then 'پدر زن'
						                             when  46 then 'مادر شوهر'
						                             when  47 then 'پدر شوهر'
						                             when  48 then 'خواهر شوهر'
						                             when  49 then 'برادر شوهر'
						                             when  50 then 'خواهر زاده'
						                             when  51 then 'برادر زاده'
						                             when  52 then 'عروس برادر'
						                             when  53 then 'داماد برادر'
						                             when  54 then 'عروس خواهر'
						                             when  55 then 'داماد خواهر'
						                             when  56 then 'همسر برادر'
						                             when  57 then 'شوهر خواهر'
						         end  dependency ,
						                               pd.row_no,
						                               pd.fname,
						                               pd.lname,
						                               pd.idcard_no,
						                               pd.birth_date,
						                               pd.father_name,
						                               s.staff_id
						                       from hrmstotal.person_dependents pd
						       						 INNER JOIN hrmstotal.persons p
						                             	  ON (pd.PersonID = p.PersonID)
						       						 INNER JOIN hrmstotal.staff s
						                             	  ON (p.PersonID = s.PersonID AND p.person_type = s.person_type )
						                       where  pd.PersonID = ".$HrmsPersonID."  AND if(dependency=4 , (separation_date IS NULL OR separation_date = '0000-00-00'),(1=1))
						                       group by pd.PersonID,pd.row_no " ;
	
		$insure_res = $mysql->Execute($insure_query);	
      
echo "<form name='PhotoForm' id='PhotoForm' method='post' enctype='multipart/form-data'>";
?>

<table width=90% align=center border=1 cellspacing=0 cellpadding=5>
<tr class="HeaderOfTable">
	<td align="center">مشخصات فردي</td>
</tr>
<tr>
	<td>
	<table width=100%>
	 <tr>
	   <td align="center" colspan="2">
	      <?
	      echo '<img width="100" height="133" border="0" src="../../profs/ShowProfPic.php?PersonID=' . 
	      		$_SESSION['PersonID'] . "&" .
	      		implode('',getdate()) .
	      		
	      		'"/>';	      		
			?>     
	      
	    
	    <td>
	 </tr>
		<tr>
			<td>
			شماره شناسایی
			</td>
			<td>
			<?= $res[0]["staff_id"] ?>
			</td>  
			
		</tr>
		<tr>
			<td>
			نام و نام خانوادگي
			</td>
			<td>
			<?= ($res[0]["pfname"]." ".$res[0]["plname"]) ?>
			</td>
		</tr>
		<tr>
			<td>
			نام پدر
			</td>
			<td>
			<?= ($res[0]["father_name"]) ?>
			</td>
		</tr>
		<tr>
			<td>
			شماره شناسنامه
			</td>
			<td>
			<?= $res[0]["idcard_no"] ?>
			</td>
		</tr>
		<tr>
			<td>
			كد ملي
			</td>
			<td>
			<?= $res[0]["national_code"] ?>
			</td>
		</tr>
		<tr>
			<td>
			آدرس
			</td>
			<td>
			  <input type="text" id="address1" name="address1" size=50 maxlength=100 
				   value="<?= ($res[0]["address1"]) ?>">
			</td>
		</tr>
		<tr>
			<td>
			تلفن
			</td>
			<td>
			<input type="text" id="home_phone1" name="home_phone1" style="width: 100px" 
				   value="<?= $res[0]["home_phone1"] ?>">
			</td>
		</tr>
		<tr>
			<td>
			شماره موبایل 
			</td>
			<td>
			<input type="text" id="mobile_phone" name="mobile_phone" style="width: 100px" 
				   value="<?= $res[0]["mobile_phone"] ?>">
			
			</td>
		</tr>
  <tr>
			<td>
			ایمیل
			</td>
			<td>
			<input type="text" id="email" name="email" size=25 maxlength=50
				   value="<?= $res[0]["email"] ?>">
			
			</td>
		</tr>
		<tr>
			<td>
				نام لاتین: 
			</td>
			<td>
				<input dir=ltr name=EFName id=EFName type=text size=25 maxlength=200 value='<?php echo $efname ?>'>
			</td>
		</tr>
		<tr> 
			<td>
				نام خانوادگی لاتین: 
			</td>
			<td>
				<input dir=ltr name=ELName id=ELName type=text size=25 maxlength=200  value='<?php echo $elname ?>'>
			</td>
		</tr>
		<? if($PersonType=="2" || $PersonType=="5") { ?>
			<tr>
				<td>
				پست سازماني
				</td>
				<td>
				<?= ($res[0]["po_title"]) ?>
				</td>
			</tr>
			<tr>
				<td>
				واحد محل خدمت
				</td>
				<td>
				<?= ($res[0]["org_unit_title"]) ?>
				</td>
			</tr>
		<? } else { ?>
			<tr>
				<td>
				واحد محل خدمت
				</td>
				<td>
				<?= ($res[0]["org_unit_title"]) ?>
				</td>
			</tr>
		<? } ?>
		<tr>
		<td colspan=2>

		</td>
		</tr>
		<tr>
			<td>
			<input type="hidden" name="MAX_FILE_SIZE" value="50000" /> 
			 مسیر فایل عکس: 
			</td>
			<td>
				<input type="file" name="ProfPhoto" />
			</td>
		</tr>
		<tr>
			<td colspan=2>
				<font face=tahoma size=2 color="Red">
				<b> توجه :</b>
				 کاربر گرامی سایز عکس حداکثر 50  کیلو بایت با فرمت JPEG یا JPG می باشد.
				</font>	
			</td>
		</tr>
		
		<tr class=FooterOfTable>
			<td colspan=2 align="center">
				<input type="submit" name="PhotoSubmit" value="ذخیره" onclick='javascript: ChkForm()' />
		    </td>
		</tr>
	</table>
</td>
</tr>
</table>
</form>
<?php 
	echo "<br>";
    echo "<table width=90% border=1 cellspacing=0 align='center' >";
	echo "<tr class=HeaderOfTable>
	                              <td width=10%>نام</td>
	                              <td width=15%>نام خانوادگی</td>
	                              <td width=10%>وابستگی</td>
	                              <td width=13%>شماره شناسنامه</td>
	                              <td width=10%>تاریخ تولد</td>
	                              <td width=10%>نام پدر</td>
	                              <td width=12%>نوع بیمه</td></tr>";
	$i = 0;
	while($rec = $insure_res->fetch())
	{
		$i++;
		$typeInsure_query = " select case insure_type
		                                 when 1 then 'عادي' 
		                                 when 2 then 'مازاد1'
		                                 when 3 then 'مازاد2'
		                                 when 4 then 'تامين اجتماعي'
		                              end insureTyp    
		 
		                      from   hrmstotal.person_dependent_supports  pds 
		                      where  PersonID = ".$HrmsPersonID." AND
						        	 master_row_no = ".$rec['row_no']." AND
						        	   (pds.from_date <='".date('Y-m-d')."' AND
						        	   (pds.to_date >= '".date('Y-m-d')."' OR pds.to_date IS  NULL) ) and 
						        	   pds.status IN (1,2) ";
								//	   echo $typeInsure_query ; die();
		
		$typeInsure_res = $mysql->Execute($typeInsure_query);	
        $typeInsure_rec = $typeInsure_res->fetch(); 
		if($i%2==0)
			echo "<tr class=OddRow>";
		else
			echo "<tr class=EvenRow>";
			  
			   echo "<td>".$rec['fname']."</td>";
			   echo "<td>".$rec['lname']."</td>";
			   echo "<td>".$rec['dependency']."</td>";	   
			   echo "<td>".$rec['idcard_no']."</td>";	   
			   echo "<td>".shdate($rec['birth_date'])."</td>";
			   echo "<td>".$rec['father_name']."</td>";
			   echo "<td>".$typeInsure_rec['insureTyp']."</td>";
			
		    echo "</tr>";
        }
        
	echo "</table>"; echo "<br><br>";
?>
<script>
	validChars = ' abcdefghijklmnopqrstuvwxyz';
	function JustAlphaNBlank(str) {
		for(var i = 0; i < str.length; ++i)
			if(validChars.indexOf(str.charAt(i).toLowerCase()) == -1) {
				alert('تنها حروف الفباي انگليسي و فضاي خالي مجاز است');
				return false;
			}
		return true;
	}
	function checkLength(str, len) {
		if(str.length < len) {
			alert('طول اين فيلد بايد حداقل ' + len + ' حرف باشد');
			return false;
		}
		return true;
	}
	function ChkForm() {
		FormObj = document.getElementById('PhotoForm');
		if(!checkLength(FormObj.EFName.value, 2) || !JustAlphaNBlank(FormObj.EFName.value)) {
			FormObj.EFName.focus();
			return;
		}
		if(!checkLength(FormObj.ELName.value, 2) || !JustAlphaNBlank(FormObj.ELName.value)) {
			FormObj.ELName.focus();
			return;
		}
		FormObj.submit();
	}
</script>
</body>
</html>
