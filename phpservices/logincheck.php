<?php
	//http://stackoverflow.com/questions/18382740/cors-not-working-php
	if (isset($_SERVER['HTTP_ORIGIN'])) {
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');    // cache for 1 day
    }

    // Access-Control headers are received during OPTIONS requests
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
            header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
            header("Access-Control-Allow-Headers:        {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

        exit(0);
    }

		$mysqli = new mysqli("localhost", "root", "", "angularcode");
		if (mysqli_connect_errno()) {
		    printf("Connect failed: %s\n", mysqli_connect_error());
		    exit();
		}

    //http://stackoverflow.com/questions/15485354/angular-http-post-to-php-and-undefined
    $postdata = file_get_contents("php://input");
	if (isset($postdata)) {
		$request = json_decode($postdata);
		$mobile = $request->mobile;
		//$sql1 = "SELECT * FROM usersmain WHERE mobile = $mobile";
		$time = time();
		$sql2 = "insert into usersmain (mobile,created) VALUES ($mobile,$time)";
			 $quer2 = $mysqli->query($sql2);
			 if($quer2){
				 $insert_id = $mysqli->insert_id;
				 $data =  array(
				 'mobile' => $mobile,
				 'id'=>$insert_id
			 );
		 }

		echo $insert_id;
		//End $postData
	}
	else {
		echo "Not called properly with username parameter!";
	}
?>
