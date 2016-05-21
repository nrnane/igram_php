<?php
 	require_once("Rest.inc.php");


	class API extends REST {

		public $data = "";
		const DB_SERVER = "localhost";
		const DB_USER = "root";
		const DB_PASSWORD = "";
		const DB = "angularcode";


		private $db = NULL;
		private $mysqli = NULL;
		public function __construct(){
			parent::__construct();				// Init parent contructor
			$this->dbConnect();					// Initiate Database connection
		}

		/*
		 *  Connect to Database
		*/
		private function dbConnect(){
			$this->mysqli = new mysqli(self::DB_SERVER, self::DB_USER, self::DB_PASSWORD, self::DB);
		}

		/*
		 * Dynmically call the method based on the query string
		 */
		public function processApi(){
			$func = strtolower(trim(str_replace("/","",$_REQUEST['x'])));
			if((int)method_exists($this,$func) > 0)
				$this->$func();
			else
				$this->response('',404); // If the method not exist with in this class "Page not found".
		}

    //------------------ MobileApp start
	
    private function logincheck(){
		
	if($this->get_request_method() != "POST"){
			$this->response('',406);
		}
			
       $r = json_decode(file_get_contents("php://input"),true);
       $mobile = $r['mobile'];
       $time = time();
	   $data = array();
		if($mobile!=''){
		$sql1 = "SELECT * FROM usersmain WHERE mobile = $mobile";
		$quer1 = $this->mysqli->query($sql1);
		 if($quer1->num_rows > 0) {
			 $data =array("Name"=>"Test");
			 $resultArray = $quer1->fetch_assoc();
			 $data =  array(
				 'mobile' => $mobile,
				 'id'=>$resultArray['id']
				);
		 }else{ //End Num Rows get 1 row
		 	
			$sql2 = "insert into usersmain (mobile,created) VALUES ($mobile,$time)";
         	$quer2 = $this->mysqli->query($sql2);
				if($quer2){
				  $insert_id = $this->mysqli->insert_id;
				  $data =  array(
				  'mobile' => $mobile,
				  'id'=>$insert_id
				 );
				} // End if query success
		 		
		 } //End Num Rows get 0 row insert new record
		 
		 

		}//End if not empty mobile number
		
		$this->response($this->json($data), 200); // send user details 	 
	} //Login Check End
	
	
	
	private function profileSave(){
		if($this->get_request_method() != "POST"){
			$this->response('',406);
		}
		$user = json_decode(file_get_contents("php://input"),true);
		$user = $user['user'];
		$userid = $user['userid'];
		$name = $user['name'];
		$email = $user['email'];
		$bio = $user['bio'];
		
		$sql1 = "SELECT * FROM userdetails WHERE userid = $userid";
		$query1 = $this->mysqli->query($sql1);
		 if($query1->num_rows > 0) {
			//if Record Update SQL Query	
			$sql = "UPDATE userdetails SET name='$name',email='$email',bio='$bio' WHERE userid = $userid"; 
			$query3 = $this->mysqli->query($sql);
				if($query3){
					$msg = "Successfully Updated";
				}
			
		 }else{
			 // if no record insert new
			 $sql = "INSERT INTO userdetails (userid,`name`,`email`,`bio`) VALUES ($userid,'$name','$email','$bio')";
			 $query2 = $this->mysqli->query($sql);
				if($query2){
					$msg ="Successfully Saved";
				}
			 
		} //End Query Check
		
		$data = array(
			"sql"=>$sql,
			"msg"=>$msg
		);

		$this->response($this->json($data), 200); // send user details 	 
		
	}// End Profile Save
	
	
	private function getProfileDetails(){
		if($this->get_request_method() != "POST"){
			$this->response('',406);
		}
		$user = json_decode(file_get_contents("php://input"),true);
		$userid = $user['userid'];
		
		$sql1 = "SELECT * FROM userdetails WHERE userid = $userid";
		$query1 = $this->mysqli->query($sql1);
		 if($query1->num_rows > 0) {
			 $resultArray = $query1->fetch_assoc();
			
		 }else{
		 	$resultArray = array('userid'=>$userid);
		 }
		
		$this->response($this->json($resultArray), 200);
		
	}// End getProfileDetails
	
	
	
    //------------------ MobileApp End
	
	private function customers(){	
			if($this->get_request_method() != "GET"){
				$this->response('',406);
			}
			$query="SELECT distinct c.customerNumber, c.customerName, c.email, c.address, c.city, c.state, c.postalCode, c.country FROM angularcode_customers c order by c.customerNumber desc";
			$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);

			if($r->num_rows > 0){
				$result = array();
				while($row = $r->fetch_assoc()){
					$result[] = $row;
				}
				$this->response($this->json($result), 200); // send user details
			}
			$this->response('',204);	// If no records "No Content" status
		}

		/*
		 *	Encode array into JSON
		*/
		private function json($data){
			if(is_array($data)){
				return json_encode($data);
			}
		}
	}

	// Initiiate Library

	$api = new API;
	$api->processApi();
?>
