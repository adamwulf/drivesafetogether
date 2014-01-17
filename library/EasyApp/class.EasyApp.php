<?

/**
 * The barebones application, using twitter oauth for
 * user management
 */
class EasyApp{

	// json to mysql
	private $db;
	
	// automatic app
	private $automatic;
	
	function __construct($db){
		$this->db = $db;
		$this->automatic = new Automatic(AUTOMATIC_CLIENT_ID, AUTOMATIC_CLIENT_SECRET);
		$this->initSession();
	}
	
	private function initSession(){
		// init a session id if we don't already have one
		if(!isset($_SESSION["uuid"])){
			// all our access will be through this uuid
			$_SESSION["uuid"] = gen_uuid();
		}
	}
	
	private function sessionId(){
		// init session if needed
		$this->initSession();
		// return id
		return $_SESSION["uuid"];
	}
	
	public function isLoggedIn(){
		if(!$this->automatic->isLoggedIn()){
			// check for session info
			$sessions = $this->db->table("sessions");
			$session_info = $sessions->find(array("session_id" => $this->sessionId()))->fetch_array();
			if($session_info){
				$user_id = $session_info["automatic_user_id"];
				$users_table = $this->db->table("automatic_users");
				$user_info = $users_table->find(array("user_id" => $user_id))->fetch_array();
				if($user_info){
					$this->automatic->setOAuthToken($user_info["access_token"]);
				}
			}
		}
		return $this->automatic->isLoggedIn();
	}
	
	public function logout(){
		$sessions = $this->db->table("sessions");
		$sessions->delete(array("session_id" => $_SESSION["uuid"]));
		$this->session_info = false;
		session_unset();
	}
	
	private function saveUser($user_obj){
		$users_table = $this->db->table("automatic_users");
		$users_table->validateTableFor($user_obj);
		$saved_info = $users_table->find(array("user_id" => $user_obj->user_id))->fetch_array();
		if($saved_info){
			$user_obj->id = $saved_info["id"];
		}
		$users_table->save($user_obj);
	}
	
	public function validateLoginForCode($code){
	    $user_obj = $this->automatic->getTokenForCode($_GET["code"]);
	    $this->automatic->setOAuthToken($user_obj->access_token);
	    
	    $user_obj->last_active = date("Y-m-d H:i:s");
	    $this->saveUser($user_obj);
	    
		$sess = array("session_id" => $_SESSION["uuid"],
					  "automatic_user_id" => $user_obj->user_id);
		$ret = $this->db->save($sess, "sessions");
	}
	
	public function automatic(){
		return $this->automatic;
	}

	

}

?>