<?php

class session{
	
	static function sec_session_start() {
		$session_name = 'sec_session_id';   // Set a custom session name
		$secure = false;
		// This stops JavaScript being able to access the session id.
		$httponly = true;
		// Forces sessions to only use cookies.
		if (ini_set('session.use_only_cookies', 1) === FALSE) {
			echo "Could not initiate a safe session (ini_set)";
			exit();
		}
		// Gets current cookies params.
		$cookieParams = session_get_cookie_params();
		session_set_cookie_params($cookieParams["lifetime"],
			$cookieParams["path"], 
			$cookieParams["domain"], 
			$secure,
			$httponly);
		// Sets the session name to the one set above.
		session_name($session_name);
		session_start();  	// Start the PHP session 
		//session_regenerate_id(true);    // regenerated the session, delete the old one. 
		
	}

	static function login($user, $pass){

		$temp = PdoDataAccess::runquery("select * from BSC_persons where UserName=?", array($user));
		if(count($temp) == 0)
		{
			return "WrongUserName";
		}
		else
		{
			$dt = PdoDataAccess::runquery("select AttemptTime from FRW_LoginAttempts 
				where PersonID=? AND AttemptTime > ?",	array($temp[0]["PersonID"], 
				time() - (10*60*60))); // second
			if(count($dt) > 5)
			{
				return "TooMuchAttempt";
			}
			// Base-2 logarithm of the iteration count used for password stretching
			$hash_cost_log2 = 8;	
			$hasher = new PasswordHash($hash_cost_log2, true);
			if (!$hasher->CheckPassword($pass, $temp[0]["UserPass"])) {

				PdoDataAccess::runquery("insert into FRW_LoginAttempts values(?, ?)",
					array($temp[0]["PersonID"], time()));
				return "WrongPassword";
			}
			else
			{
				if($temp[0]["IsActive"] == "NO")
				{
					return "InActiveUser";
				} 

				$_SESSION['USER'] = $temp[0];
				$_SESSION['USER']["fullname"] = $temp[0]["fname"] . " " . $temp[0]["lname"];

				$_SESSION['login_string'] = hash('sha512', $temp[0]["UserPass"] . $_SERVER['HTTP_USER_AGENT']);
				$_SESSION['last_activity'] = time();
				//..........................................................
				if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
					if ( strlen($_SERVER['HTTP_X_FORWARDED_FOR']) > 15 )
						$_SESSION['LIPAddress'] = substr($_SERVER['HTTP_X_FORWARDED_FOR'] , 0,strpos($_SERVER['HTTP_X_FORWARDED_FOR'],','));
					else
						$_SESSION['LIPAddress'] = ($_SERVER['HTTP_X_FORWARDED_FOR']);
				else
					$_SESSION['LIPAddress'] = $_SERVER['REMOTE_ADDR'];
				//..........................................................
				PdoDataAccess::runquery("delete from FRW_LoginAttempts where PersonID=?",
					array($temp[0]["PersonID"]));
				return true;
			}
		}
	}
	
	static function checkLogin(){

		if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 10*60)) {
			$_SESSION = array();
			// get session parameters 
			$params = session_get_cookie_params();
			// Delete the actual cookie. 
			setcookie(session_name(),
					'', time() - 42000, 
					$params["path"], 
					$params["domain"], 
					$params["secure"], 
					$params["httponly"]);
			session_destroy();
			return false;
		}
		$_SESSION['last_activity'] = time();
		if(empty($_SESSION['USER']) || empty($_SESSION['login_string']))
		{
			return false;
		}
		$dt = PdoDataAccess::runquery("select * from BSC_persons where PersonID=?", 
			array($_SESSION['USER']["PersonID"]));
		$login_check = hash('sha512', $dt[0]["UserPass"] . $_SERVER['HTTP_USER_AGENT']);
		if ($login_check != $_SESSION['login_string']) 
		{
			return false;
		}

		return true;
	}

	static function logout(){
		
		$_SESSION = array();
		// get session parameters 
		$params = session_get_cookie_params();
		// Delete the actual cookie. 
		setcookie(session_name(),
				'', time() - 42000, 
				$params["path"], 
				$params["domain"], 
				$params["secure"], 
				$params["httponly"]);
		session_destroy();
	}
	
	static function register($user, $pass){
		
		$temp = PdoDataAccess::runquery("select * from BSC_persons where UserName=?", array($user));
		if(count($temp) > 0)
		{
			return "DuplicateUserName";
		}
		else
		{
			$hash_cost_log2 = 8;	
			$hasher = new PasswordHash($hash_cost_log2, true);

			$obj = new BSC_persons();
			PdoDataAccess::FillObjectByArray($obj, $_POST);
			$obj->UserPass = $hasher->HashPassword($pass);
			$obj->IsCustomer = "YES";
			$obj->AddPerson();

			$temp = PdoDataAccess::runquery("select * from BSC_persons where UserName=?", array($user));
			$_SESSION['USER'] = $temp[0];
			$_SESSION['USER']["framework"] = true;
			$_SESSION['USER']["portal"] = true;
			//..........................................................
			if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
				if ( strlen($_SERVER['HTTP_X_FORWARDED_FOR']) > 15 )
					$_SESSION['LIPAddress'] = substr($_SERVER['HTTP_X_FORWARDED_FOR'] , 0,strpos($_SERVER['HTTP_X_FORWARDED_FOR'],','));
				else
					$_SESSION['LIPAddress'] = ($_SERVER['HTTP_X_FORWARDED_FOR']);
			else
				$_SESSION['LIPAddress'] = $_SERVER['REMOTE_ADDR'];
			//..........................................................
			return true;

		}
	}
	
	static function getEmail($userName, $coded = false){
		
		$temp = PdoDataAccess::runquery("select * from BSC_persons where UserName=?", array($userName));
		
		if(count($temp) == 0)
		{
			return "WrongUserName";
		}
		
		$email = $temp[0]["email"];
		if($email == "")
			return "EmptyEmail";
		
		$arr = preg_split("/@/", $email);
		
		if($coded)
			return $arr[0][0] . $arr[0][1] . "****" . $arr[0][strlen($arr[0])-2] . 
				$arr[0][strlen($arr[0])-1] . "@" . $arr[1];
		
		return $email;
	}

	static function SendNewPass($userName){
		
		require_once 'email.php';
		
		$temp = PdoDataAccess::runquery("select * from BSC_persons where UserName=?", array($userName));
		if(count($temp) == 0)
		{
			return "WrongUserName";
		}
		$PersonRecord = $temp[0];
		$email = $PersonRecord["email"];
		if($email == "")
			return "EmptyEmail";
		
		$newPass = rand(111111, 999999);
		
		$hash_cost_log2 = 8;	
		$hasher = new PasswordHash($hash_cost_log2, true);
		PdoDataAccess::runquery("update BSC_persons set UserPass=? where PersonID=?", array(
			$hasher->HashPassword(md5($newPass)), $PersonRecord["PersonID"]
		)); 
		
		$subject = "تغییر رمز عبور";
		$body = "کاربر گرامی <b>" . ($PersonRecord["IsReal"] ? $PersonRecord["fname"] . " " . 
				$PersonRecord["lname"] : $PersonRecord["CompanyName"]) .
			"</b><br><br>رمز عبور جدید شما  <b>" . $newPass . "</b> می باشد." . 
			"<br> لطفا پس از ورود به پرتال نسبت به تغییر رمز عبور خود اقدام نمایید." . 
			"<br><br> صندوق نوآوری و شکوفایی";
		echo SendEmail($email, $subject, $body) ? "true" : "false";
		die();
	}
}
/*class session {
	function __construct() {
		// set our custom session functions.
		session_set_save_handler(
				array($this, 'open'), 
				array($this, 'close'), 
				array($this, 'read'),
				array($this, 'write'), 
				array($this, 'destroy'), 
				array($this, 'gc'));

		// This line prevents unexpected effects when using objects as save handlers.
		register_shutdown_function('session_write_close');
	}
	
	function start_session($session_name, $secure) {
		// Make sure the session cookie is not accessible via javascript.
		$httponly = true;

		// Hash algorithm to use for the session. (use hash_algos() to get a list of available hashes.)
		$session_hash = 'sha512';

		// Check if hash is available
		if (in_array($session_hash, hash_algos())) {
			// Set the has function.
			ini_set('session.hash_function', $session_hash);
		}
		// How many bits per character of the hash.
		// The possible values are '4' (0-9, a-f), '5' (0-9, a-v), and '6' (0-9, a-z, A-Z, "-", ",").
		ini_set('session.hash_bits_per_character', 5);

		// Force the session to only use cookies, not URL variables.
		ini_set('session.use_only_cookies', 1);

		// Get session cookie parameters 
		$cookieParams = session_get_cookie_params(); 
		// Set the parameters
		session_set_cookie_params($cookieParams["lifetime"], $cookieParams["path"], 
				$cookieParams["domain"], $secure, $httponly); 
		// Change the session name 
		session_name($session_name);
		// Now we cat start the session
		session_start();
		// This line regenerates the session and delete the old one. 
		// It also generates a new encryption key in the database. 
		session_regenerate_id(true); 
	}
	
	function open() {
		$host = sys_config::$db_server["host"];
		$user = sys_config::$db_server["user"];
		$pass = sys_config::$db_server["pass"];
		$name = sys_config::$db_server["database"];
		
		$mysqli = new mysqli($host, $user, $pass, $name);
		$this->db = $mysqli;
		return true;
	}
	
	function close() {
		$this->db->close();
		return true;
	}
	
	function read($PersonID) {
		if(!isset($this->read_stmt)) {
			$this->read_stmt = $this->db->prepare("SELECT data FROM USR_sessions 
				WHERE PersonID = ? LIMIT 1");
		}
		$this->read_stmt->bind_param('s', $PersonID);
		$this->read_stmt->execute();
		$this->read_stmt->store_result();
		$this->read_stmt->bind_result($data);
		$this->read_stmt->fetch();
		$key = $this->getkey($PersonID);
		$data = $this->decrypt($data, $key);
		return $data;
	}
	
	function write($PersonID, $data) {
		// Get unique key
		$key = $this->getkey($PersonID);
		// Encrypt the data
		$data = $this->encrypt($data, $key);

		$time = time();
		if(!isset($this->w_stmt)) {
			$this->w_stmt = $this->db->prepare("REPLACE INTO USR_sessions(PersonID, 
				LoginTime, data, SessionKey) VALUES (?, ?, ?, ?)");
		}

		$this->w_stmt->bind_param('siss', $PersonID, $time, $data, $key);
		$this->w_stmt->execute();
		return true;
	}
	
	function destroy($PersonID) {
		if(!isset($this->delete_stmt)) {
			$this->delete_stmt = $this->db->prepare("DELETE FROM USR_sessions WHERE PersonID = ?");
		}
		$this->delete_stmt->bind_param('s', $PersonID);
		$this->delete_stmt->execute();
		return true;
	}
	
	function gc($max) {
		if(!isset($this->gc_stmt)) {
			$this->gc_stmt = $this->db->prepare("DELETE FROM USR_sessions WHERE LoginTime < ?");
		}
		$old = time() - $max;
		$this->gc_stmt->bind_param('s', $old);
		$this->gc_stmt->execute();
		return true;
	}
		
	private function getkey($PersonID) {
		if(!isset($this->key_stmt)) {
			$this->key_stmt = $this->db->prepare("SELECT SessionKey FROM USR_sessions 
				WHERE PersonID = ? LIMIT 1");
		}
		$this->key_stmt->bind_param('s', $PersonID);
		$this->key_stmt->execute();
		$this->key_stmt->store_result();
		if($this->key_stmt->num_rows == 1) { 
			$this->key_stmt->bind_result($key);
			$this->key_stmt->fetch();
			return $key;
		} else {
			$random_key = hash('sha512', uniqid(mt_rand(1, mt_getrandmax()), true));
			return $random_key;
		}
	}
	
	private function encrypt($data, $key) {
		$salt = 'cH!swe!retReGu7W6bEDRup7usuDUh9THeD2CHeGE*ewr4n39=E@rAsp7c-Ph@pH';
		$key = substr(hash('sha256', $salt.$key.$salt), 0, 32);
		$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
		$encrypted = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $data, MCRYPT_MODE_ECB, $iv));
		return $encrypted;
	}
	
	private function decrypt($data, $key) {
		$salt = 'cH!swe!retReGu7W6bEDRup7usuDUh9THeD2CHeGE*ewr4n39=E@rAsp7c-Ph@pH';
		$key = substr(hash('sha256', $salt.$key.$salt), 0, 32);
		$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
		$decrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, base64_decode($data), MCRYPT_MODE_ECB, $iv);
		return $decrypted;
	}
}*/
?>
