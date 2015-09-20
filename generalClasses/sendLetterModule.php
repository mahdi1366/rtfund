<?php

/*
 * DO NOT USE THIS CLASS
 * USE SAME CLASS IN SharedClasses INSTEAD
 * 
 * JAFARKHANI 93.10.15
 * 
 */

function SendLetterModule($Subject, $Content, $ReceiverUserID) {
    $strws = "";

    //کد salt را بر اساس usrname به صورت زیر دزیافت کنید
    //ini_set("log_errors" , "1");
    //ini_set("error_log" , "Errors.log.txt");
    $module = 'Login';
    $action = 'getSalt';
    $uname = 'users'; //نام کاربری را وارد کنید.
    $data = array(
        'module' => urlencode($module),
        'action' => urlencode($action),
        'uname' => urlencode($uname),
    );
    //echo $aws_fcontent;
    $fields = '';
    foreach ($data as $key => $value) {
        $fields .= $key . '=' . $value . '&';
    }
    rtrim($fields, '&');
    ////////////////////////////////////////
    //////////////////////////////////////////
    $post = curl_init();

    curl_setopt($post, CURLOPT_URL, 'http://fumdabir.um.ac.ir/OfficeAS/Runtime/process.php');
    curl_setopt($post, CURLOPT_POST, TRUE);
    curl_setopt($post, CURLOPT_HEADER, false);
    curl_setopt($post, CURLOPT_POSTFIELDS, $fields);
    curl_setopt($post, CURLOPT_RETURNTRANSFER, 1);

    $salt = curl_exec($post);
    $salt = trim($salt, '()');
    $salt = trim($salt, '""');
    //echo $salt . '<br/>';
    ///////////////////////////////////////end salt/////////////////////////////////////////////////////
    ////////////////////////////////////////get passtoken/////////////////////////////////////////////////
    //گرفتن token
    ini_set("log_errors", "1");
    ini_set("error_log", "Errors.log.txt");
    $module = 'Login';
    $action = 'getPassToken';
    $data = array(
        'module' => urlencode($module),
        'action' => urlencode($action),
    );
    $fields = '';
    foreach ($data as $key => $value) {
        $fields .= $key . '=' . $value . '&';
    }
    rtrim($fields, '&');
    //////////////////////////////////////////
    $post = curl_init();

    curl_setopt($post, CURLOPT_URL, 'http://fumdabir.um.ac.ir/OfficeAS/Runtime/process.php');
    curl_setopt($post, CURLOPT_POST, TRUE);
    curl_setopt($post, CURLOPT_HEADER, true);
    curl_setopt($post, CURLOPT_POSTFIELDS, $fields);
    curl_setopt($post, CURLOPT_RETURNTRANSFER, 1);

    $res = curl_exec($post);
    //////////////////////////////////////////////////
    $bodyStart = strpos($res, "\r\n\r\n");
    $header = substr($res, 0, $bodyStart);
    $body = substr($res, $bodyStart);

    preg_match_all('/Set-Cookie:\s*([^;]*)/', $header, $matches);
    $sessions = array();
    foreach ($matches[0] as $match) {
        $tmp = substr($match, strlen('Set-Cookie:'));
        $tmp = trim($tmp);
        $sessions[] = $tmp;
    }
//درsخواست login را حتما باید با این session بفرستید
    $sessions = implode('; ', $sessions);
    $connection = curl_init();
    //curl_setopt($conn, CURLOPT_COOKIE, $sessions);
    $token = trim($body, "(,),\",\n,\r");
   //echo $token . '<br/>';
    ///////////////////////////////////////////end pass token/////////////////////////////////////////
    ///////////////////////////////////////////start login////////////////////////////////////////////
    $module = 'Login';
    $action = 'login';
    $user = 'users';
    $token = $token;
    $pass = "FuM@Dabir39$#11"; //پسورد را در این قسمت ورد کنید
    //$pass = sha1(md5(md5($pass).$salt).$token);//پسورد را با salt وtoken hash میکنیم و در پسورد قرارمی دهید
    $pass = sha1(md5(sha1(md5($pass)) . $salt) . $token);
    $data = array(
        'module' => urlencode($module),
        'action' => urlencode($action),
        'username' => urlencode($user),
        'pass' => urlencode($pass)
    );

    $fields = '';
    foreach ($data as $key => $value) {
        $fields .= $key . '=' . $value . '&';
    }
    rtrim($fields, '&');
    $post = curl_init();

    curl_setopt($post, CURLOPT_URL, 'http://fumdabir.um.ac.ir/OfficeAS/Runtime/process.php');
    curl_setopt($post, CURLOPT_POST, TRUE);
    curl_setopt($post, CURLOPT_HEADER, true);
    curl_setopt($post, CURLOPT_POSTFIELDS, $fields);
    curl_setopt($post, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($post, CURLOPT_COOKIE, $sessions);

    $result = curl_exec($post);

    $bodyStart = strpos($result, "\r\n\r\n");
    $header = substr($result, 0, $bodyStart);
    $body = substr($result, $bodyStart);

    preg_match_all('/Set-Cookie:\s*([^;]*)/', $header, $matches);
    $sessions = array();
    foreach ($matches[0] as $match) {
        $tmp = substr($match, strlen('Set-Cookie:'));
        $tmp = trim($tmp);
        $sessions[] = $tmp;
    }
//درsخواست login را حتما باید با این session بفرستید
    $sessions = implode('; ', $sessions);
    curl_setopt($connection, CURLOPT_COOKIE, $sessions);
    $res = trim($body, "(,),\",\n,\r");
   // echo '<br>--------------------------' . $sessions . '----------------------------<br>';
   // echo $res;
    $strws .= $res;
    ///////////////////////////////////////////////end login////////////////////////////////////////

    /*
      در مراحل بعد جهت ارسال هر request احتیاجی به set کردن یوزر و پسورد نیست و کافیست session را در cookie set کنید
     */
   // echo "<br>-----End of get session---------<br>";
    $mysql = dbclass::getInstance("192.168.14.36", "researchuser", "rschapppass248", "officeas");

    $res = $mysql->Execute("select * from officeas.oa_user JOIN officeas.oa_dept_role using (UserID) where OldUserID='" . $ReceiverUserID . "' and IsDefault=1");
    $rec = $res->FetchRow();
    $ReceiverID = $rec["UserID"];
    $ReceiverRoleID = $rec["RoleID"];
    $query = "http://fumdabir.um.ac.ir/OfficeAS/Runtime/process.php?module=Compose&action=saveManifest";
    $query .= "&ltype=1"; // ﺏﺭﺍی ﻥﺎﻤﻫ ﺩﺎﺨﻟی ۱ ﺏﺭﺍی ﺐﻗیﻩ (ﺹﺍﺩﺮﻫ ﻭ ﻭﺍﺭﺪﻫ) ﻑﺮﻗ ﻡی کﻥﺩ
    $query .= "&template=1"; // ﺖﻣپیﻝیﺕ ﻥﺎﻤﻫ کﻩ ﺏﺭﺍی ﻢﺘﻧی ۱ ﺎﺴﺗ ﻭ ﺏﺭﺍی ﺎﺳکﻥی ۲ ﻭ ﺎﻟی ﺂﺧﺭ
    $query .= "&cat=1"; // ﻂﺒﻘﻫ ﺐﻧﺩی ﻥﺎﻤﻫ
    $query .= "&urg=1"; // ﻑﻭﺭیﺕ
    $query .= "&signers=0,0"; // ﺎﻤﺿﺍ کﻦﻧﺪﻫ کﻩ ﺏﺭﺍی ﻥﺎﻤﻫ ﻩﺍی ﺹﺍﺩﺮﻫ ﻢﻌﻧی ﺩﺍﺭﺩ کﻩ ﺍﻮﻟیﻥ ﻉﺩﺩ ﺶﻣﺍﺮﻫ کﺍﺮﺑﺭ ﻭ ﺩﻮﻣی ﺶﻣﺍﺮﻫ ﻦﻘﺷ ﺍﻭ ﻡی ﺏﺎﺷﺩ
    $query .= "&printer=0,0"; // ﻢﺳﻭﻮﻟ ﺖﻫیﻩ ﻦﺴﺨﻫ چﺍپی
    $query .= "&subject=" . urlencode($Subject);
    $query .= "&per_notes="; // یﺍﺩﺩﺎﺸﺗ ﺶﺨﺻی
    $query .= "&FlowType=0"; // گﺭﺪﺷ کﺎﻏﺫی ﺩﺍﺭﺩ/ﻥﺩﺍﺭﺩ
    curl_setopt($connection, CURLOPT_COOKIE, $sessions);
    curl_setopt($connection, CURLOPT_URL, $query);
    curl_setopt($connection, CURLOPT_RETURNTRANSFER, 1);
    $ret = curl_exec($connection);
    $strws .= $ret;
    //die("------->".$strws);
    $ret = preg_split('/,/', $ret);
    $did = preg_split('/:/', $ret[0]);
    $did = $did[1];
    $referID = preg_split('/:/', $ret[1]);
    $referID = substr($referID[1], 0, strlen($referID[1]) - 2);
    // ﺐﻫ ﺪﻟیﻝ ﺎﻣکﺎﻧ ﻮﺟﻭﺩ ﺩﺍﺪﻫ ﺏیﺶﺗﺭ ﺍﺯ ﺡﺩ ﺏﺍیﺩ ﺍﺯ ﻢﺗﺩ پﺲﺗ ﺎﺴﺘﻓﺍﺪﻫ ﻡی ﺵﺩ ﺐﻧﺎﺑﺭﺍیﻥ ﺦﻃﻮﻃ ﺯیﺭ کﺎﻤﻨﺗ ﺵﺩ ﻭ ﻢﺟﺩﺩ ﺏﺍ ﻢﺗﺩ پﺲﺗ ﻥﻮﺸﺘﻫ ﺵﺩ
    $PostVars = "http://fumdabir.um.ac.ir/OfficeAS/Runtime/process.php?";
    $PostVars .= "module=Compose&action=updateTypedContent";
    $PostVars .= "&content=" . urlencode($Content);      // letter content
    $PostVars .= "&did=" . $did;
    $PostVars .= "&referID=" . $referID;
    $PostVars .= "&start=1"; // یﻊﻧی ﻥﺎﻤﻫ ﻖﺑﻻ ﻢﺤﺗﻭﺍ ﻥﺩﺎﺸﺘﻫ ﻭ ﺍکﻥﻮﻧ ﻡی ﺥﻭﺎﻫیﻡ ﺐﻫ ﺂﻧ ﻢﺤﺗﻭﺍ ﺏﺪﻫیﻡ

    curl_setopt($connection, CURLOPT_COOKIE, $sessions);
    curl_setopt($connection, CURLOPT_URL, $PostVars);
    curl_setopt($connection, CURLOPT_RETURNTRANSFER, 1);
    $ret = curl_exec($connection);
    $strws .= $ret;
    $query = "http://fumdabir.um.ac.ir/OfficeAS/Runtime/process.php?module=Refer&action=refer";
    $query .= "&referID=" . $referID;
    $query .= "&receivers=" . $ReceiverID . "," . $ReceiverRoleID . ",0"; //  - ﺂﺧﺭیﻥ ﻉﺩﺩ ﻥﻮﻋ ﺭﺍ ﻢﺸﺨﺻ ﻡی کﻥﺩ کﻩ ﻒﻋﻻ ﺺﻓﺭ ﺏﺎﺷﺩ
    $query .= "&note-id="; // ﻥﻮﻋ ﺵﺮﺣ ﺪﺴﺗﻭﺭ
    $query .= "&note-desc="; // ﻢﺘﻧ ﺍﺮﺟﺎﻋ
    $query .= "&urg=1";
    $query .= "&timeout=";
    $query .= "&track=";
    $query .= "&endedit=1"; // ﺐﻋﺩ ﺍﺯ ﺍﺮﺳﺎﻟ ﺩیگﺭ کﺱی ﻢﺘﻨﺷ ﺭﺍ ﺖﻏییﺭ ﻥﺪﻫﺩ
    $query .= "&delete=1"; // ﺐﻋﺩ ﺍﺯ ﺍﺮﺳﺎﻟ ﺍﺯ پیﺵ ﻥﻭیﺲﻫﺍ ﺡﺬﻓ ﺵﻭﺩ
    curl_setopt($connection, CURLOPT_COOKIE, $sessions);
    curl_setopt($connection, CURLOPT_URL, $query);
    curl_setopt($connection, CURLOPT_RETURNTRANSFER, 1);
    $ret = curl_exec($connection);
    $strws .= $ret;
   
	if($_SESSION["UserID"] == "jafarkhani")
	{
		echo $strws;
		echo "----";
		die();
	}
    $sw = false;

    if (strpos($strws, "success") !== false)
        $sw = true;
    else
        $sw = false;

    if (strpos($strws, "true") !== false)
        $sw = true;
    else
        $sw = false;

    if (strpos($strws, "ok") !== false)
        $sw = true;
    else
        $sw = false;
    curl_close($post);
    return $sw;
}

function SendAttachedLetterModule($Subject, $Content, $ReceiverUserID, $FilePath, $RolID = "0") {

//*******************************************   Add New Login ***************************************************
    echo "Enter Login";
    $strws = "";
    //کد salt را بر اساس usrname به صورت زیر دزیافت کنید
    //ini_set("log_errors" , "1");
    //ini_set("error_log" , "Errors.log.txt");
    $module = 'Login';
    $action = 'getSalt';
    $uname = 'users'; //نام کاربری را وارد کنید.
    $data = array(
        'module' => urlencode($module),
        'action' => urlencode($action),
        'uname' => urlencode($uname),
    );
    //echo $aws_fcontent;
    $fields = '';
    foreach ($data as $key => $value) {
        $fields .= $key . '=' . $value . '&';
    }
    rtrim($fields, '&');
    ////////////////////////////////////////
    //////////////////////////////////////////
    $post = curl_init();

    curl_setopt($post, CURLOPT_URL, 'http://fumdabir.um.ac.ir/OfficeAS/Runtime/process.php');
    curl_setopt($post, CURLOPT_POST, TRUE);
    curl_setopt($post, CURLOPT_HEADER, false);
    curl_setopt($post, CURLOPT_POSTFIELDS, $fields);
    curl_setopt($post, CURLOPT_RETURNTRANSFER, 1);

    $salt = curl_exec($post);
    $salt = trim($salt, '()');
    $salt = trim($salt, '""');
    echo $salt . '<br/>';
    ///////////////////////////////////////end salt/////////////////////////////////////////////////////
    ////////////////////////////////////////get passtoken/////////////////////////////////////////////////
    //گرفتن token
    // ini_set("log_errors", "1");
    // ini_set("error_log", "Errors.log.txt");
    $module = 'Login';
    $action = 'getPassToken';
    $data = array(
        'module' => urlencode($module),
        'action' => urlencode($action),
    );
    $fields = '';
    foreach ($data as $key => $value) {
        $fields .= $key . '=' . $value . '&';
    }
    rtrim($fields, '&');
    //////////////////////////////////////////
    $post = curl_init();

    curl_setopt($post, CURLOPT_URL, 'http://fumdabir.um.ac.ir/OfficeAS/Runtime/process.php');
    curl_setopt($post, CURLOPT_POST, TRUE);
    curl_setopt($post, CURLOPT_HEADER, true);
    curl_setopt($post, CURLOPT_POSTFIELDS, $fields);
    curl_setopt($post, CURLOPT_RETURNTRANSFER, 1);

    $res = curl_exec($post);
    //////////////////////////////////////////////////
    $bodyStart = strpos($res, "\r\n\r\n");
    $header = substr($res, 0, $bodyStart);
    $body = substr($res, $bodyStart);

    preg_match_all('/Set-Cookie:\s*([^;]*)/', $header, $matches);
    $sessions = array();
    foreach ($matches[0] as $match) {
        $tmp = substr($match, strlen('Set-Cookie:'));
        $tmp = trim($tmp);
        $sessions[] = $tmp;
    }
//درsخواست login را حتما باید با این session بفرستید
    $sessions = implode('; ', $sessions);
    $connection = curl_init();
    //curl_setopt($conn, CURLOPT_COOKIE, $sessions);
    $token = trim($body, "(,),\",\n,\r");
    echo $token . '<br/>';
    ///////////////////////////////////////////end pass token/////////////////////////////////////////
    ///////////////////////////////////////////start login////////////////////////////////////////////
    $module = 'Login';
    $action = 'login';
    $user = 'users';
    $token = $token;
    $pass = "FuM@Dabir39$#11"; //پسورد را در این قسمت ورد کنید
    //$pass = sha1(md5(md5($pass).$salt).$token);//پسورد را با salt وtoken hash میکنیم و در پسورد قرارمی دهید
    $pass = sha1(md5(sha1(md5($pass)) . $salt) . $token);
    $data = array(
        'module' => urlencode($module),
        'action' => urlencode($action),
        'username' => urlencode($user),
        'pass' => urlencode($pass)
    );

    $fields = '';
    foreach ($data as $key => $value) {
        $fields .= $key . '=' . $value . '&';
    }
    rtrim($fields, '&');
    $post = curl_init();

    curl_setopt($post, CURLOPT_URL, 'http://fumdabir.um.ac.ir/OfficeAS/Runtime/process.php');
    curl_setopt($post, CURLOPT_POST, TRUE);
    curl_setopt($post, CURLOPT_HEADER, true);
    curl_setopt($post, CURLOPT_POSTFIELDS, $fields);
    curl_setopt($post, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($post, CURLOPT_COOKIE, $sessions);

    $result = curl_exec($post);
    echo "******* Login Result is $result \n";

    $bodyStart = strpos($result, "\r\n\r\n");
    $header = substr($result, 0, $bodyStart);
    $body = substr($result, $bodyStart);

    preg_match_all('/Set-Cookie:\s*([^;]*)/', $header, $matches);
    $sessions = array();
    foreach ($matches[0] as $match) {
        $tmp = substr($match, strlen('Set-Cookie:'));
        $tmp = trim($tmp);
        $sessions[] = $tmp;
    }
//درsخواست login را حتما باید با این session بفرستید
    $sessions = implode('; ', $sessions);
    curl_setopt($connection, CURLOPT_COOKIE, $sessions);
    $res = trim($body, "(,),\",\n,\r");
    echo '<br>--------------------------' . $sessions . '----------------------------<br>';
    echo "************** Result Session is  $res ************";
    $strws .= $res;
    ///////////////////////////////////////////////end login////////////////////////////////////////
    //curl_close($post);
    /*
      در مراحل بعد جهت ارسال هر request احتیاجی به set کردن یوزر و پسورد نیست و کافیست session را در cookie set کنید
     */
    echo "<br>-----End of get session---------<br>";


//===========================================================  End Login -----------------------------------------    
    // کﺩ کﺍﺮﺑﺭی ﻭ ﺲﻤﺗ پیﺵ ﻑﺮﺿ گیﺮﻧﺪﻫ ﺭﺍ ﺍﺯ ﺏﺎﻧک ﺎﻃﻼﻋﺎﺗی ﺎﺗﻮﻣﺎﺳیﻮﻧ ﺍﺩﺍﺭی ﺎﺴﺘﺧﺭﺎﺟ ﻡی کﻥﺩ

    $mysql = dbclass::getInstance("192.168.14.36", "researchuser", "rschapppass248", "officeas");
    $query = "";
    if ($RolID == "0") {
        $query = "select * from officeas.oa_user JOIN officeas.oa_dept_role using (UserID) where OldUserID='" . $ReceiverUserID . "' and IsDefault=1";
    } else {
        $query = "select * from officeas.oa_user JOIN officeas.oa_dept_role using (UserID) where OldUserID='" . $ReceiverUserID . "' and RoleID = '" . $RolID . "'";
    }
    $res = $mysql->Execute($query);
    $rec = $res->FetchRow();
    $ReceiverID = $rec["UserID"];
    $ReceiverRoleID = $rec["RoleID"];

    $query = "http://fumdabir.um.ac.ir/OfficeAS/Runtime/process.php?module=Compose&action=saveManifest";
    $query .= "&ltype=1"; // ﺏﺭﺍی ﻥﺎﻤﻫ ﺩﺎﺨﻟی ۱ ﺏﺭﺍی ﺐﻗیﻩ (ﺹﺍﺩﺮﻫ ﻭ ﻭﺍﺭﺪﻫ) ﻑﺮﻗ ﻡی کﻥﺩ
    $query .= "&template=1"; // ﺖﻣپیﻝیﺕ ﻥﺎﻤﻫ کﻩ ﺏﺭﺍی ﻢﺘﻧی ۱ ﺎﺴﺗ ﻭ ﺏﺭﺍی ﺎﺳکﻥی ۲ ﻭ ﺎﻟی ﺂﺧﺭ
    $query .= "&cat=1"; // ﻂﺒﻘﻫ ﺐﻧﺩی ﻥﺎﻤﻫ
    $query .= "&urg=1"; // ﻑﻭﺭیﺕ
    $query .= "&signers=0,0"; // ﺎﻤﺿﺍ کﻦﻧﺪﻫ کﻩ ﺏﺭﺍی ﻥﺎﻤﻫ ﻩﺍی ﺹﺍﺩﺮﻫ ﻢﻌﻧی ﺩﺍﺭﺩ کﻩ ﺍﻮﻟیﻥ ﻉﺩﺩ ﺶﻣﺍﺮﻫ کﺍﺮﺑﺭ ﻭ ﺩﻮﻣی ﺶﻣﺍﺮﻫ ﻦﻘﺷ ﺍﻭ ﻡی ﺏﺎﺷﺩ
    $query .= "&printer=0,0"; // ﻢﺳﻭﻮﻟ ﺖﻫیﻩ ﻦﺴﺨﻫ چﺍپی
    $query .= "&subject=" . urlencode($Subject);
    $query .= "&per_notes="; // یﺍﺩﺩﺎﺸﺗ ﺶﺨﺻی
    $query .= "&FlowType=0"; // گﺭﺪﺷ کﺎﻏﺫی ﺩﺍﺭﺩ/ﻥﺩﺍﺭﺩ
    curl_setopt($connection, CURLOPT_COOKIE, $sessions);
    curl_setopt($connection, CURLOPT_URL, $query);
    curl_setopt($connection, CURLOPT_RETURNTRANSFER, 1);
    $ret = curl_exec($connection);
    echo"\n Compose Action saveManifest Result is $ret \n"; // Added by Hesam 
    $strws .= $ret;

    $ret = preg_split('/,/', $ret);
    $did = preg_split('/:/', $ret[0]);
    $did = $did[1];
    $referID = preg_split('/:/', $ret[1]);
    $referID = substr($referID[1], 0, strlen($referID[1]) - 2);
    $PostVars = "module=Compose&action=updateTypedContent";
    $PostVars .= "&content=" . urlencode($Content);      // letter content
    $PostVars .= "&did=" . $did;
    $PostVars .= "&referID=" . $referID;
    $PostVars .= "&start=1"; // یﻊﻧی ﻥﺎﻤﻫ ﻖﺑﻻ ﻢﺤﺗﻭﺍ ﻥﺩﺎﺸﺘﻫ ﻭ ﺍکﻥﻮﻧ ﻡی ﺥﻭﺎﻫیﻡ ﺐﻫ ﺂﻧ ﻢﺤﺗﻭﺍ ﺏﺪﻫیﻡ
    curl_setopt($connection, CURLOPT_COOKIE, $sessions);
    curl_setopt($connection, CURLOPT_URL, "http://fumdabir.um.ac.ir/OfficeAS/Runtime/process.php");
    curl_setopt($connection, CURLOPT_POST, 1);
    curl_setopt($connection, CURLOPT_POSTFIELDS, $PostVars);
    curl_setopt($connection, CURLOPT_RETURNTRANSFER, 1);  // RETURN THE CONTENTS OF THE CALL
    $ret = curl_exec($connection);
    echo"\n Compose Letter Content Result is $ret \n"; //added by Hesam 
    $strws .= $ret;
    /* ------------------------------ */

//Added by Hamid Sajadi
    $file_name_with_full_path = $FilePath;
    $module = 'DocAttachs';
    $action = 'addAttach';
    $header = array('Content-Type: multipart/form-data');
    curl_setopt($connection, CURLOPT_COOKIE, $sessions);
    curl_setopt($connection, CURLOPT_URL, "http://fumdabir.um.ac.ir/OfficeAS/Runtime/process.php");
    curl_setopt($connection, CURLOPT_HTTPHEADER, $header);
    curl_setopt($connection, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible;)');
    curl_setopt($connection, CURLOPT_POST, 1);
    curl_setopt($connection, CURLOPT_HEADER, 0);
    curl_setopt($connection, CURLOPT_VERBOSE, 0);
    curl_setopt($connection, CURLOPT_RETURNTRANSFER, true);


    for ($i = 0; $i < count($FilePath); $i++) {
        $post = array('module' => $module, 'action' => $action, 'file1' => '@' . $file_name_with_full_path[$i], 'did' => $did); //, 'user' => $UserID, 'pass' => $UserPasswd
        curl_setopt($connection, CURLOPT_POSTFIELDS, $post);
        $ret = curl_exec($connection);
        echo("\n Doc[$i] Attachment Result is $ret \n"); //Added By Hesam 
        $strws .= $ret;
    }

    /* ------------------------------ */

    $query = "http://fumdabir.um.ac.ir/OfficeAS/Runtime/process.php?module=Refer&action=refer";
    $query .= "&referID=" . $referID;
    $query .= "&receivers=" . $ReceiverID . "," . $ReceiverRoleID . ",0"; //  - ﺂﺧﺭیﻥ ﻉﺩﺩ ﻥﻮﻋ ﺭﺍ ﻢﺸﺨﺻ ﻡی کﻥﺩ کﻩ ﻒﻋﻻ ﺺﻓﺭ ﺏﺎﺷﺩ
    $query .= "&note-id="; // ﻥﻮﻋ ﺵﺮﺣ ﺪﺴﺗﻭﺭ
    $query .= "&note-desc="; // ﻢﺘﻧ ﺍﺮﺟﺎﻋ
    $query .= "&urg=1";
    $query .= "&timeout=";
    $query .= "&track=";
    $query .= "&endedit=1"; // ﺐﻋﺩ ﺍﺯ ﺍﺮﺳﺎﻟ ﺩیگﺭ کﺱی ﻢﺘﻨﺷ ﺭﺍ ﺖﻏییﺭ ﻥﺪﻫﺩ
    $query .= "&delete=1"; // ﺐﻋﺩ ﺍﺯ ﺍﺮﺳﺎﻟ ﺍﺯ پیﺵ ﻥﻭیﺲﻫﺍ ﺡﺬﻓ ﺵﻭﺩ
    curl_setopt($connection, CURLOPT_POST, 0);
    curl_setopt($connection, CURLOPT_COOKIE, $sessions);
    curl_setopt($connection, CURLOPT_URL, $query);
    curl_setopt($connection, CURLOPT_RETURNTRANSFER, 1);
    $ret = curl_exec($connection);
    echo("Refer Module Result is $ret"); // Added By Hesam 
    $strws .= $ret;

    $sw = false;

    if (strpos($strws, "success") !== false)
        $sw = true;
    else
        $sw = false;

    if (strpos($strws, "true") !== false)
        $sw = true;
    else
        $sw = false;

    if (strpos($strws, "ok") !== false)
        $sw = true;
    else
        $sw = false;
    echo("****** Final SW is : $sw *************");


    return $sw;
}

?>
