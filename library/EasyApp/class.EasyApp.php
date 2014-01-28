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
	
	public function getAutomaticUserId(){
		$sessions = $this->db->table("sessions");
		$session_info = $sessions->find(array("session_id" => $this->sessionId()))->fetch_array();
		if($session_info){
			return $session_info["automatic_user_id"];
		}
		return false;
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
	
	private function saveTrip($trip_obj){
		$trips_table = $this->db->table("automatic_trips");
		$trips_table->validateTableFor($trip_obj);
		$saved_info = $trips_table->find(array("trip_id" => $trip_obj->trip_id))->fetch_array();
		if($saved_info){
			$trip_obj->id = $saved_info["id"];
		}
		$trips_table->save($trip_obj);
	}
	
	private function saveVehicle($vehicle_obj){
		$vehicle_table = $this->db->table("automatic_vehicles");
		$vehicle_table->validateTableFor($vehicle_obj);
		$saved_info = $vehicle_table->find(array("vehicle_id" => $vehicle_obj->vehicle_id))->fetch_array();
		if($saved_info){
			$vehicle_obj->id = $saved_info["id"];
		}
		$vehicle_table->save($vehicle_obj);
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

	
	public function getTripDataBetween($startdt, $enddt){
		$user_id = $this->getAutomaticUserId();
		if($this->isLoggedIn()){
			$sql = "SELECT YEARWEEK(startdt,1) as dt, SUM(hard_accels) AS hard_accels, SUM(hard_brakes) AS hard_brakes, SUM(distance_meters) AS distance, "
				 . " AVG(average_mpg) AS average_mpg, SUM(duration_over_80_s + duration_over_75_s) AS duration_speeding, "
				 . " SUM(fuel_cost_usd * fuel_volume_gal) AS fuel_cost "
				 . " FROM automatic_trips WHERE enddt > '" . addslashes($startdt) . "' AND startdt < '" . addslashes($enddt) . "' "
			     . " AND user_id = '" . addslashes($user_id) . "' "
			     . " GROUP BY dt ORDER BY dt DESC";
			$results = $this->db->mysql()->query($sql);
			
			$out = array();
			while($row = $results->fetch_array()){
				$out[] = (object) $row;
			}
			return $out;
		}
		return array();
	}
	
	
	public function cronImportTrips($force_load_all = false){
		$all_users = $this->db->table("automatic_users")->find();
		while($user = $all_users->fetch_array()){
			$this->automatic()->setOAuthToken($user["access_token"]);
			$page = 1;
			
			do{
				$trips = $this->automatic()->getTrips($page,50);
				if(!$trips){
					// invalid token, or otherwise unable to refresh trips
					break;
				}else{
					foreach($trips["result"] as $tr){
						$trip_start = date("Y-m-d H:i:s", $tr["start_time"] / 1000);
						$past_24_hours = date("Y-m-d H:i:s", time() - 24*60*60);
						
						if(!$force_load_all && $trip_start < $past_24_hours){
							// only import last 24 hours per user
							break;
						}
					
						$vehicle = object();
						$vehicle->vehicle_id = $tr["vehicle"]["id"];
						$vehicle->display_name = $tr["vehicle"]["display_name"];
						$vehicle->year = $tr["vehicle"]["year"];
						$vehicle->make = $tr["vehicle"]["make"];
						$vehicle->model = $tr["vehicle"]["model"];
						
						$this->saveVehicle($vehicle);
					
						$trip = object();
						$trip->trip_id = $tr["id"];
						$trip->user_id = $tr["user"]["id"];
						$trip->vehicle_id = $tr["vehicle"]["id"];
						$trip->startdt = date("Y-m-d H:i:s", $tr["start_time"] / 1000);
						$trip->enddt = date("Y-m-d H:i:s", $tr["end_time"] / 1000);
						$trip->distance_meters = $tr["distance_m"];
						$trip->fuel_cost_usd = $tr["fuel_cost_usd"];
						$trip->fuel_volume_gal = $tr["fuel_volume_gal"];
						$trip->average_mpg = $tr["average_mpg"];
						$trip->hard_accels = $tr["hard_accels"];
						$trip->hard_brakes = $tr["hard_brakes"];
						$trip->duration_over_80_s = $tr["duration_over_80_s"];
						$trip->duration_over_75_s = $tr["duration_over_75_s"];
						$trip->duration_over_70_s = $tr["duration_over_70_s"];
	
						$this->saveTrip($trip);
						echo "imported.\n";
					}
				}
				$page++;
			}while($force_load_all && count($trips["result"]));
		}
	}
}

?>