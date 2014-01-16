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
				$this->automatic->setOAuthToken($session_info["automatic_token"]);
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
	
	public function validateLoginForCode($code){
	    $response_token = $this->automatic->getTokenForCode($_GET["code"]);
	    $this->automatic->setOAuthToken($response_token);
	    
		$sess = array("session_id" => $_SESSION["uuid"],
					  "automatic_token" => $response_token,
					  "last_active" => date("Y-m-d H:i:s"));
		$ret = $this->db->save($sess, "sessions");
	}
	
	public function automatic(){
		return $this->automatic;
	}

	

}

?>