<?php
 	require_once("Rest.inc.php");
  require_once("dbHandler.php");
  require 'PHPMailerAutoload.php';

  require 'Lib/Embera/Autoload.php';


	class API extends REST {

		public $data = "";
		private $db = NULL;
		private $mysqli = NULL;
		private $site_url = "";


		public function __construct(){
			parent::__construct();				// Init parent contructor
			//$this->dbConnect();					// Initiate Database connection

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

    private function session(){

      if($this->get_request_method() != "POST"){
          $this->response('',406);
        }
       $r = json_decode(file_get_contents("php://input"),true);
       $db = new DbHandler();
       $session_id = $r['session_id'];
       $sql = "SELECT session_id FROM users WHERE session_id = '$session_id'";
       $dq = $db->runQuery($sql);
       if($dq){
          $response["code"] = 1;
       }else{
         $response["code"] = 0;
       }

  		$this->response($this->json($response), 200); // send user details
  	} //Session Check End

    private function logout(){

      if($this->get_request_method() != "POST"){
          $this->response('',406);
        }
       $r = json_decode(file_get_contents("php://input"),true);
       $db = new DbHandler();
       $session_id = $r['session_id'];
       $uid = $r['uid'];

       $sql = "UPDATE users SET session_id='' WHERE uid = $uid";
       $dq = $db->runQuery($sql);
       if($dq){
          $response["code"] = 1;
       }else{
         $response["code"] = 0;
       }

      $this->response($this->json($response), 200); // send user details
    } //Session Check End


    private function login(){

      if($this->get_request_method() != "POST"){
          $this->response('',406);
        }
       $r = json_decode(file_get_contents("php://input"),true);

       $response['json'] = $r;
       $db = new DbHandler();

       $password = $r['password'];
       $username = $r['username'];
       $sql = "select * from users where email='$username' or mobile = '$username' or username = '$username'";
       $user = $db->getOneRecord($sql);
       if ($user != NULL) {
          $response['userData']=$user;
       }else {
               $response['status'] = "error";
               $response['message'] = 'No such user is registered';
           }


  		$this->response($this->json($response), 200); // send user details
  	} //Login Check End

    private function post(){
      if($this->get_request_method() != "POST"){
          $this->response('',406);
        }
       $r = json_decode(file_get_contents("php://input"),true);
       /*$search = $r['search'];

       $response = array();
       //$response = $r;
       $db = new DbHandler();
       //$session = $db->getSession();
       //$uid = $session['uid'];
        $column_names = array('one_uid', 'two_uid', 'chat_id');
        $result = $db->insertIntoTable($colum_data, $column_names, $tabble_name);

       $sql = "SELECT uid,name,email,mobile FROM users WHERE name LIKE '%".$search."%'  OR email LIKE '%".$search."%' OR mobile LIKE '%".$search."%' AND uid!=$uid LIMIT 0,10";
       $response = $db->getRecords($sql);*/


         $this->response($this->json($r), 200); // send user details
    }

    private function post_media(){
      if($this->get_request_method() != "POST"){
          $this->response('',406);
        }
       $r = json_decode(file_get_contents("php://input"),true);


         $this->response($this->json($r), 200); // send user details
    }


    //GetPrivateChatID Start
    private function gpchatid(){
      if($this->get_request_method() != "POST"){
          $this->response('',406);
        }
       $r = json_decode(file_get_contents("php://input"),true);
       //$response = array();
       $db = new DbHandler();

       $tabble_name = "chat_ids";
       $colum_data['one_uid'] = $one_uid = $r['uid'];
       $colum_data['two_uid'] = $two_uid = $r['other_chat_uid'];
       $chat_id = $colum_data['chat_id'] =$one_uid.'_'.$two_uid;

       $column_names = array('one_uid', 'two_uid', 'chat_id');

       //Check if already Exist Chat ID
         $sql = "SELECT * FROM $tabble_name WHERE (one_uid = $one_uid  AND two_uid = $two_uid) OR (one_uid = $two_uid  AND two_uid = $one_uid)";
         $checkAlreadyExist = $db->getRecords($sql);
         if($checkAlreadyExist){
           $response['chatID']=$checkAlreadyExist[0]['id'];
           $response['status'] = "success";

         }else{
           $result = $db->insertIntoTable($colum_data, $column_names, $tabble_name);
           //$intrest_uid
           $response['chatID']=$result;
           $response['status'] = "success";
         }

         $sql2 = "SELECT uid,email,mobile,name FROM users WHERE uid= $two_uid";
         $TwouserDetails = $db->getRecords($sql2);
         $response['ChatOtherUser'] = $TwouserDetails[0];

         $sql3 = "SELECT * FROM chat_messages WHERE chat_id=".$response['chatID'];
         $chatHistory = $db->getRecords($sql3);
         $response['ChatHistory'] = $chatHistory;


       $this->response($this->json($response), 200);

    } //GetPrivateChatID End

    //Search Start
    private function searchUsers(){

      if($this->get_request_method() != "POST"){
          $this->response('',406);
        }
       $r = json_decode(file_get_contents("php://input"),true);
        $uid = $r['c_uid'];
       $search = $r['search'];

       $response = array();
       //$response = $r;
       $db = new DbHandler();
       //$session = $db->getSession();
       //$uid = $session['uid'];
       $sql = "SELECT uid,name,email,mobile FROM users WHERE name LIKE '%".$search."%'  OR email LIKE '%".$search."%' OR mobile LIKE '%".$search."%' AND uid!=$uid LIMIT 0,10";
       $response = $db->getRecords($sql);


         $this->response($this->json($response), 200); // send user details


    } //Search End


    // INsertChat Message start
    private function insertChatMsg(){

      if($this->get_request_method() != "POST"){
          $this->response('',406);
        }

        $r = json_decode(file_get_contents("php://input"),true);
        $db = new DbHandler();

        $tabble_name = "chat_messages";
        $cData['from_uid'] = $r['from_uid'];
        $cData['to_uid'] = $r['ouid'];
        $cData['chat_id'] = $r['chatID'];
        $cData['message'] = $r['message'];

        $column_names = array( 'from_uid', 'to_uid','chat_id', 'message');
        $result = $db->insertIntoTable($cData, $column_names, $tabble_name);
        if($result){
           $this->response($this->json($r), 200); // send user details
        }

      } //INsertChat End

      // getUnSeenChatMsgs start
      private function getUnSeenChatMsgs(){

        if($this->get_request_method() != "POST"){
            $this->response('',406);
          }

          $r = json_decode(file_get_contents("php://input"),true);
          $db = new DbHandler();

          $tabble_name = "chat_messages";
          $uid = $r['uid'];

          $sql = "SELECT
          	cm.id,cm.from_uid,cm.to_uid,cm.chat_id,cm.message,cm.seen,cm.date,u.name, COUNT(cm.id) as msg_count
          FROM
          	chat_messages cm
            LEFT JOIN  users u on cm.from_uid = u.uid
          WHERE cm.to_uid = $uid AND cm.seen = 0 GROUP BY cm.from_uid";
          $response = $db->getRecords($sql);


            $this->response($this->json($response), 200); // send user details

        } //getUnSeenChatMsgs End





    // //Signup start
    private function signup(){

    	if($this->get_request_method() != "POST"){
    			$this->response('',406);
    		}
       $r = json_decode(file_get_contents("php://input"),true);
       $db = new DbHandler();
       $name = $r['name'];
       $mobile = $r['mobile'];
       $email = $r['email'];
       $email = $r['username'];
       $password = $r['password'];
       $isUserExists = $db->getOneRecord("select 1 from users where mobile='$mobile' or email='$email'");
       if (!$isUserExists) {
         $tabble_name = "users";

         $column_names = array( 'name', 'email','mobile','username','password');
         $result = $db->insertIntoTable($r, $column_names, $tabble_name);
         if ($result != NULL) {

             $response["status"] = "success";
             $response["code"]=1;
             $response["message"] = "Your account created successfully";
             $response["user_id"] = $result;

         } else {
             $response["status"] = "error";
             $response["code"]=0;
             $response["message"] = "Failed to create customer. Please try again";

         }
       }else{
               $response['status'] = "error";
               $response["code"]=0;
               $response['message'] = 'User already registered';
          }

  		$this->response($this->json($response), 200); // send user details
  	} //Signup End


    // //User Online start
    private function updateUserOnline(){

    	if($this->get_request_method() != "POST"){
    			$this->response('',406);
    		}
       $r = json_decode(file_get_contents("php://input"),true);
       $db = new DbHandler();
       $uid = $r['uid'];
       $time = time();
       $sql = "UPDATE users SET lastOnlineTime = $time WHERE uid = $uid";
       if($db->runQuery($sql)){
         $response['status']="success";
       }

  		$this->response($this->json($response), 200); // send user details
  	} //User Online End

    // //User Online start
    private function CheckUserOnline(){

    	if($this->get_request_method() != "POST"){
    			$this->response('',406);
    		}
       $r = json_decode(file_get_contents("php://input"),true);
       $db = new DbHandler();
       $uid = $r['uid'];
       $time = time();
       $sql = "SELECT uid,lastOnlineTime FROM users WHERE uid = $uid";
       if($result = $db->getOneRecord($sql)){

         if($result['lastOnlineTime']>($time-25)){
           $response['online']=1;
         }else{
           $response['online']=0;
         }
         $response['status']="success";
         $response['lastOnlineTime']=date('Y-m-d H:i:s',$result['lastOnlineTime']);

       }

  		$this->response($this->json($response), 200); // send user details
  	} //User Online End


    //Get ProfilePic Start
    private function get_profile_pic(){

      if($this->get_request_method() != "POST"){
          $this->response('',406);
        }
      $r = json_decode(file_get_contents("php://input"),true);

       $response = array();
       $db = new DbHandler();
       $session = $db->getSession();
       if(isset($r['uid']) && $r['uid']=='session_id'){
         $uid = $session['uid'];
       }else{
         $uid = $r['uid'];
       }

       $sql = "SELECT if(profilePic='',if(gender=1,'male.png','female.png'),profilePic) as profilePic
FROM users WHERE uid = $uid";
       $response = $db->getRecords($sql);


       if ($response != NULL) {
         $this->response($this->json($response), 200); // send user details
       }

    } //Get ProfilePic End

    //Get Profile By Session ID Start
    private function Get_Profile_by_Id(){

      if($this->get_request_method() != "POST"){
          $this->response('',406);
        }
      $r = json_decode(file_get_contents("php://input"),true);

       $response = array();
       $db = new DbHandler();
       $session = $db->getSession();
       if(isset($r['uid']) && $r['uid']=='session_id'){
         $uid = $session['uid'];
       }else{
         $uid = $r['uid'];
       }

       $sql = "SELECT
uid,name,email,phone,username,gender,designation,bio,dobDate,dobMonth,dobYear,zipcode,city,date,instagram_username,instagram_userid,i_male,i_female,
if(profilePic='',if(gender=1,'male.png','female.png'),profilePic) as profilePic, i_male,i_female
FROM users WHERE uid = $uid";
       $response = $db->getRecords($sql);


       $sql_count_friends = "SELECT count(fri_id)  as friends,f.accept FROM friends f
WHERE  (f.friend_one = $uid OR f.friend_two = $uid) AND f.accept = 1";
       $fc = $db->getRecords($sql_count_friends);
       $response['friendsCount'] = (isset($fc[0]['friends']))?$fc[0]['friends']:0;

       $sql_count_likes = "SELECT count(ui_id)  as count, CASE type
 WHEN 1 THEN 'LIKE'
 WHEN 2 THEN 'VISIT'
 END AS itype FROM user_intrests WHERE intrest_uid = $uid  AND type = 1";
       $likes = $db->getRecords($sql_count_likes);
       $response['likesCount'] = (isset($likes[0]['itype']) && $likes[0]['itype']=='LIKE')?$likes[0]['count']:0;

       $sql_count_visit = "SELECT count(ui_id)  as count, CASE type
 WHEN 1 THEN 'LIKE'
 WHEN 2 THEN 'VISIT'
END AS itype FROM user_intrests WHERE intrest_uid = $uid  AND type = 2";
       $visit = $db->getRecords($sql_count_visit);
       $response['visitCount'] = (isset($visit[0]['itype']) && $visit[0]['itype']=='VISIT')?$visit[0]['count']:0;




       if ($response != NULL) {
         $this->response($this->json($response), 200); // send user details
       }

    } //Get Profile By Session ID End


    //Get Profile By User ID Start
    private function Get_Profile_by_Username(){

      if($this->get_request_method() != "POST"){
          $this->response('',406);
        }
        $r = json_decode(file_get_contents("php://input"),true);
        $r = $r['name'];
       $response = array();
       $db = new DbHandler();
       $session = $db->getSession();

         //$uid = $session['uid'];

       $sql = "SELECT
uid,name,email,phone,username,gender,bio,dobDate,dobMonth,dobYear,zipcode,city,date,i_male,i_female,
if(profilePic='',if(gender=1,'male.png','female.png'),profilePic) as profilePic
FROM users WHERE username LIKE '%".$r."%'";
       $response = $db->getRecords($sql);

       $uid = $response[0]['uid'];
       $response['uid']= $uid;



       $sql_count_friends = "SELECT count(fri_id)  as friends,f.accept FROM friends f
WHERE  (f.friend_one = $uid OR f.friend_two = $uid) AND f.accept = 1";
       $fc = $db->getRecords($sql_count_friends);
       $response['friendsCount'] = (isset($fc[0]['friends']))?$fc[0]['friends']:0;

       $sql_count_likes = "SELECT count(ui_id)  as count, CASE type
 WHEN 1 THEN 'LIKE'
 WHEN 2 THEN 'VISIT'
 END AS itype FROM user_intrests WHERE intrest_uid = $uid  AND type = 1";
       $likes = $db->getRecords($sql_count_likes);
       $response['likesCount'] = (isset($likes[0]['itype']) && $likes[0]['itype']=='LIKE')?$likes[0]['count']:0;

       $sql_count_visit = "SELECT count(ui_id)  as count, CASE type
 WHEN 1 THEN 'LIKE'
 WHEN 2 THEN 'VISIT'
END AS itype FROM user_intrests WHERE intrest_uid = $uid  AND type = 2";
       $visit = $db->getRecords($sql_count_visit);
       $response['visitCount'] = (isset($visit[0]['itype']) && $visit[0]['itype']=='VISIT')?$visit[0]['count']:0;


       if ($response != NULL) {
         $this->response($this->json($response), 200); // send user details
       }

    } //Get Profile By User ID End



      //get_instagram_photos Start
      private function get_instagram_photos(){

        if($this->get_request_method() != "POST"){
            $this->response('',406);
          }
          $r = json_decode(file_get_contents("php://input"),true);
          //$r = $r['name'];
         $response = array();
         $db = new DbHandler();
         $session = $db->getSession();

         if(isset($r['uid']) && $r['uid']=='session_id'){
           $uid = $session['uid'];
         }else{
           $uid = $r['uid'];
         }
         $response['uid'] = $uid;
         $sql = "SELECT instagram_username, instagram_userid FROM users WHERE uid = $uid"; // WHERE username LIKE '%".$r."%'
         $in = $db->getRecords($sql);

         /*--Instagra Photos Start---*/

         if(isset($in[0]['instagram_userid']) && $in[0]['instagram_userid']!=0 && $in[0]['instagram_username']!=''){
             $instagramDetails = $db->getInstagramDetails();
             //$url = 'https://api.instagram.com/v1/users/search?q='.$instagram_username.'&client_id='.$instagramDetails['instagram_client_id'];
             $url = 'https://api.instagram.com/v1/users/'.$response[0]['instagram_userid'].'/media/recent/?access_token='.$instagramDetails['instagram_access_token'];
             $instagram= $db->processInstagramURL($url);
             //$response['id'] = $instagram;
             $instagram_json = json_decode($instagram, true);
             $instagram_photos = [];
             $response['data'] = $instagram_json;
               foreach($instagram_json['data'] as $item){
                   $thumb_link = $item['images']['low_resolution']['url'];
                   $large_link = $item['images']['standard_resolution']['url'];
                   $instagram_photos[] = array('thumb' => $thumb_link, 'url'=>$large_link );
               }
               $response = $instagram_photos;
       }

       /*--Instagra Photos End---*/

         if ($response != NULL) {
           $this->response($this->json($response), 200); // send user details
         }

      } //get_instagram_photos  End



      //GetPhotos Start
      private function GetPhotos(){

        if($this->get_request_method() != "POST"){
            $this->response('',406);
          }
          $r = json_decode(file_get_contents("php://input"),true);
          //$r = $r['name'];
         $response = array();
         $db = new DbHandler();
         $session = $db->getSession();

         if(isset($r['uid']) && $r['uid']=='session_id'){
           $uid = $session['uid'];
         }else{
           $uid = $r['uid'];
         }

         $sql = "SELECT * FROM photos WHERE uid = $uid";
         $response = $db->getRecords($sql);

         if ($response != NULL) {
           $this->response($this->json($response), 200); // send user details
         }

      } //GetPhotos  End


    //Get Peoples Start
    private function GetPeoples(){

      if($this->get_request_method() != "POST"){
          $this->response('',406);
        }
        $r = json_decode(file_get_contents("php://input"),true);
        //$r = $r['name'];
       $response = array();
       $db = new DbHandler();
       $session = $db->getSession();

         $uid = $session['uid'];

       $sql = "SELECT u.uid,u.name,u.email,u.phone,u.password,u.username,u.gender,u.dobDate,u.dobMonth,u.dobYear,u.zipcode,u.city,
if(u.profilePic='',if(u.gender=1,'male.png','female.png'),u.profilePic) as profilePic,
(SELECT 1 FROM user_intrests WHERE uid = $uid and intrest_uid = u.uid and type = 1) as loved,
(SELECT
 CASE
 	WHEN accept IS NULL THEN 'Add as Friend'
	WHEN accept = 0 THEN 'Friend Request Sent'
 	WHEN accept = 1 THEN 'Friend'
 	WHEN accept = 2 THEN 'Friend Request Declined'
END
 FROM friends WHERE friend_one = u.uid and friend_two = $uid OR friend_one = $uid and friend_two = u.uid LIMIT 1) as friend_accept
FROM
users u
WHERE u.uid != $uid"; // WHERE username LIKE '%".$r."%'
       $response = $db->getRecords($sql);

       if ($response != NULL) {
         $this->response($this->json($response), 200); // send user details
       }

    } //Get Peoples End


    //Get Peoples Start
    private function GetPeoples_loadMore(){

      if($this->get_request_method() != "GET"){
          $this->response('',406);
        }

        $after = $_GET['after'];
        $r = json_decode(file_get_contents("php://input"),true);
        //$r = $r['name'];
       $response = array();
       $db = new DbHandler();
       $session = $db->getSession();

         $uid = $session['uid'];

       $sql = "SELECT u.uid,u.name,u.email,u.phone,u.password,u.username,u.gender,u.dobDate,u.dobMonth,u.dobYear,u.zipcode,u.city,
if(u.profilePic='',if(u.gender=1,'male.png','female.png'),u.profilePic) as profilePic,
(SELECT 1 FROM user_intrests WHERE uid = $uid and intrest_uid = u.uid and type = 1) as loved,
(SELECT
 CASE
 	WHEN accept IS NULL THEN 'Add as Friend'
	WHEN accept = 0 THEN 'Friend Request Sent'
 	WHEN accept = 1 THEN 'Friend'
 	WHEN accept = 2 THEN 'Friend Request Declined'
END
 FROM friends WHERE friend_one = u.uid and friend_two = $uid OR friend_one = $uid and friend_two = u.uid LIMIT 1) as friend_accept
FROM
users u
WHERE u.uid != $uid and u.uid > $after LIMIT 20"; // WHERE username LIKE '%".$r."%'
       $response = $db->getRecords($sql);

       if ($response != NULL) {
         $this->response($this->json($response), 200); // send user details
       }

    } //Get Peoples End

    //Get Friends Start
    /*
      Pass uid
    */
    private function GetFriends(){

      if($this->get_request_method() != "POST"){
          $this->response('',406);
        }
        $r = json_decode(file_get_contents("php://input"),true);
        //$r = $r['name'];
       $response = array();
       $db = new DbHandler();
       $session = $db->getSession();

       if(isset($r['uid']) && $r['uid']=='session_id'){
         $uid = $session['uid'];
       }else{
         $uid = $r['uid'];
       }

       $sql = "SELECT u.uid,u.name,u.email,u.phone,u.password,u.username,u.gender,u.dobDate,u.dobMonth,u.dobYear,u.zipcode,u.city,f.fri_id,f.friend_one,f.friend_two,f.accept as accept,
       if(f.friend_one = $uid,1,0) as you,
CASE
 	WHEN accept IS NULL THEN 'Add as Friend'
	WHEN accept = 0 THEN if(f.friend_two = $uid,'Friend Request Came','Friend Request Sent')
 	WHEN accept = 1 THEN 'Friends'
 	WHEN accept = 2 THEN 'Friend Request Declined'
END as friend_accept,
if(u.profilePic='',if(u.gender=1,'male.png','female.png'),u.profilePic) as profilePic,
(SELECT 1 FROM user_intrests WHERE uid = $uid and intrest_uid = u.uid and type = 1) as loved
FROM
users u, friends f
WHERE u.uid = f.friend_two AND f.friend_one = $uid OR u.uid = f.friend_one AND f.friend_two = $uid"; // WHERE username LIKE '%".$r."%'
       $response = $db->getRecords($sql);

       if ($response != NULL) {
         $this->response($this->json($response), 200); // send user details
       }

    } //Get Peoples End


    //UserIntrest Start
    private function UserIntrests(){

      if($this->get_request_method() != "POST"){
          $this->response('',406);
        }
        $r = json_decode(file_get_contents("php://input"),true);
        //$r = $r['name'];
       $response = array();
       $db = new DbHandler();
       $session = $db->getSession();

         $uid = $session['uid'];
         $type = $r['type'];
         $tabble_name = "user_intrests";
         $r['uid']=$uid;
         $r['time']=time();
         $time = $r['time'];
         $intrest_uid = $r['intrest_uid'];
         $column_names = array('uid', 'type', 'intrest_uid', 'time');

//Check if already Intrest
      $sql = "SELECT * FROM $tabble_name WHERE uid = $uid AND type = $type AND intrest_uid = $intrest_uid";
      $checkAlreadyIntrest = $db->getRecords($sql);
      if($checkAlreadyIntrest==NULL){
        $result = $db->insertIntoTable($r, $column_names, $tabble_name);

        //Notification Insert Start
        $notifications = array('uid' => $uid,
          'to_uid'=>$intrest_uid,
          'n_type'=>4, //Liked Your Profile
          'message'=>'',
          'related_row_id'=>$result,
          'time'=>$time
       );
       $tabble_name2 = 'user_notifications';
        $column_names2 = array('uid', 'to_uid', 'n_type','message','related_row_id','time');
        $result2 = $db->insertIntoTable($notifications, $column_names2, $tabble_name2);


        if ($result != NULL) {
            $response["status"] = "success";
            $response["message"] = "You Loved";
          }
      }else{
        $response['data']=$checkAlreadyIntrest;
        $ui_id = $checkAlreadyIntrest[0]['ui_id'];
        $sql = "DELETE FROM $tabble_name WHERE ui_id = $ui_id";
        $dq = $db->deleteQuery($sql);
        if($dq){
          $response["status"] = "success";
          $response["message"] = "You un Loved";
        }
      }

       if ($response != NULL) {
         $this->response($this->json($response), 200); // send user details
       }

    } //UserIntrest End


    //FriendRequest Start
    private function FriendRequest(){

      if($this->get_request_method() != "POST"){
          $this->response('',406);
        }
        $r = json_decode(file_get_contents("php://input"),true);
        //$r = $r['name'];
       $response = array();
       $db = new DbHandler();
       $session = $db->getSession();

         $uid = $session['uid'];

         $tabble_name = "friends";
         $r['friend_one']=$uid;
         $friend_two = $r['friend_two'];
         $accept = $r['accept'];
         $r['time']=time();
         $time = $r['time'];

         $column_names = array('friend_one', 'friend_two', 'accept', 'time');

//Check if already Friend
      $sql = "SELECT * FROM $tabble_name WHERE friend_one = $uid AND friend_two = $friend_two OR friend_one = $friend_two AND friend_two = $uid";
      $checkAlreadyIntrest = $db->getRecords($sql);
      if($checkAlreadyIntrest==NULL){
        $result = $db->insertIntoTable($r, $column_names, $tabble_name);

        if ($result != NULL) {

          //Notification Insert Start
          $notifications = array('uid' => $uid,
            'to_uid'=>$friend_two,
            'n_type'=>1,
            'message'=>'',
            'related_row_id'=>$result,
            'time'=>$time
         );
         $tabble_name2 = 'user_notifications';
          $column_names2 = array('uid', 'to_uid', 'n_type','message','related_row_id','time');
          $result2 = $db->insertIntoTable($notifications, $column_names2, $tabble_name2);
          //Notification Insert End
            $response["status"] = "success";
            $response["message"] = "Friend Request Sent";
            $response['inlineText']="Friend Request Sent";
          }
      }else{
        $response['data']=$checkAlreadyIntrest;
        $ui_id = $checkAlreadyIntrest[0]['fri_id'];
        $sql = "DELETE FROM $tabble_name WHERE fri_id = $ui_id";
        $dq = $db->deleteQuery($sql);
        if($dq){
          $response["status"] = "success";
          $response["message"] = "Freind Removed";
          $response['inlineText']="Add as Friend";
        }
      }

       if ($response != NULL) {
         $this->response($this->json($response), 200); // send user details
       }

    } //FriendRequest End


    //FriendAccept Start
    private function FriendAccept(){

      if($this->get_request_method() != "POST"){
          $this->response('',406);
        }
        $r = json_decode(file_get_contents("php://input"),true);
        //$r = $r['name'];
       $response = array();
       $db = new DbHandler();
       $session = $db->getSession();

         $uid = $session['uid'];

         $tabble_name = "friends";
         $r['friend_one']=$uid;
         $fri_id = $r['fri_id'];
         //$friend_two = $r['friend_two'];
         $accept = $r['accept'];
         $r['time']=time();
         $time = $r['time'];
         $snto = $r['snto'];



        $sql = "UPDATE $tabble_name SET accept=$accept WHERE fri_id = $fri_id";
        $dq = $db->runQuery($sql);
        if($dq){
          if($accept==1){

            //Notification Insert Start
            $notifications = array('uid' => $uid,
              'to_uid'=>$snto,
              'n_type'=>2,
              'message'=>'',
              'related_row_id'=>'',
              'time'=>$time
           );
           $tabble_name2 = 'user_notifications';
            $column_names2 = array('uid', 'to_uid', 'n_type','message','related_row_id','time');
            $result2 = $db->insertIntoTable($notifications, $column_names2, $tabble_name2);
            //Notification Insert End

            $response["status"] = "success";
            $response["message"] = "Freind Request Accepted";
            //$response['inlineText']="Add as Friend";
          }elseif ($accept==2) {
            $response["status"] = "success";
            $response["message"] = "Freind Request Declined";
          }
        }


       if ($response != NULL) {
         $this->response($this->json($response), 200); // send user details
       }

    } //FriendAccept End



    /*
      Get All User Notifications
    */
    private function GetUserNotifications(){

      if($this->get_request_method() != "GET"){
          $this->response('',406);
        }
       //$r = json_decode(file_get_contents("php://input"),true);

       $response = array();
       //$response = $r;
       $db = new DbHandler();
       $session = $db->getSession();


       $uid = $session['uid'];

       $sql = "SELECT UN.un_id,UN.uid,UN.to_uid,UN.n_type,UN.message,UN.related_row_id,UN.seen,UN.time,U.username,U.profilePic,
       F.accept,
CASE UN.n_type
 WHEN 1 THEN 'send you a friend request'
 WHEN 2 THEN 'accepted your friend request'
 WHEN 4 THEN 'liked your profile'
END as type,U.name
FROM user_notifications UN LEFT JOIN users U on UN.uid = U.uid
LEFT JOIN friends F on UN.to_uid = F.friend_two
 WHERE to_uid = $uid ";
  $output = array();
       $queryResult = $db->getRecords($sql);
       if($queryResult!=NULL){

           //$response = $queryResult;
           for ($i=0; $i < count($queryResult) ; $i++) {

               if($queryResult[$i]['accept']!=1 && $queryResult[$i]['n_type']==1 || $queryResult[$i]['n_type']==2 || $queryResult[$i]['n_type']==4){
                 $output[] = array("message" =>$queryResult[$i]['type'],
                 "person"=>$queryResult[$i]['name'],
                 "time"=>$queryResult[$i]['time'],
                 "table"=>"user_notifications",
                 'un_id'=>$queryResult[$i]['un_id'],
                 'snto'=>$queryResult[$i]['uid'],
                 'n_type'=>$queryResult[$i]['n_type'],
                 'fri_id'=>$queryResult[$i]['related_row_id'],
                 'profilePic'=>$queryResult[$i]['profilePic']
                );
              }
             }
         }

         $response = $output;

         $this->response($this->json($response), 200); // send user details


    } //Get All User Notifications End



    //seenRowOfTable Start
    private function seenRowOfTable(){

      if($this->get_request_method() != "POST"){
          $this->response('',406);
        }
        $r = json_decode(file_get_contents("php://input"),true);
        //$r = $r['name'];
       $response = array();
       $db = new DbHandler();
       $session = $db->getSession();

         $uid = $session['uid'];

         $tabble_name = $r['table'];
         $rowid = $r['rowid'];
         $wcolum = $r['wcolum'];

        $sql = "UPDATE $tabble_name SET seen=1 WHERE un_id = $rowid";
        $dq = $db->runQuery($sql);
        if($dq){
            $response['data'] = $r;
            $response["status"] = "success";
            $response["message"] = "Seen Update";
            //$response['inlineText']="Add as Friend";
          }



       if ($response != NULL) {
         $this->response($this->json($response), 200); // send user details
       }

    } //seenRowOfTable End



    private function createwallpost(){

      if($this->get_request_method() != "POST"){
          $this->response('',406);
        }
       $r = json_decode(file_get_contents("php://input"),true);
       $r = $r['data'];

       $response = array();
       //$response = $r;
       $db = new DbHandler();
       $session = $db->getSession();

       $r['time'] = time();
       $r['uid'] = $session['uid'];
       $post_message = $r['post_message'];
       $embera = new \Embera\Embera();
       $r['post_message'] = $embera->autoEmbed($post_message);

       //$uid = $session['uid'];
       if($_SESSION['att_yes']==1){
         $r['gallery_id'] = $_SESSION['att_ses_id'];
       }else{
         $r['gallery_id'] = 0;
       }


       $tabble_name = "user_posts";
       $column_names = array('uid', 'post_message', 'privacy', 'time','gallery_id');
       $result = $db->insertIntoTable($r, $column_names, $tabble_name);
       if ($result != NULL) {
           $response["status"] = "success";
           $response["message"] = "Your Post created successfully";

           $sql = "SELECT * FROM user_posts WHERE postid = $result";

           $response['post'] = $db->getRecords($sql);

         }

         $_SESSION['att_yes']==0;

         $this->response($this->json($response), 200); // send user details


    } //Create Wall Post End


      // getAllPostsUser
      private function getAllPostsUser(){

        if($this->get_request_method() != "POST"){
            $this->response('',406);
          }
         $r = json_decode(file_get_contents("php://input"),true);
         $response = array();
         //$response = $r;
         $db = new DbHandler();
         $session = $db->getSession();
         if (isset($r['uid'])) { //is pass user id get data based on user id
             $uid = $r['uid'];
         }else{
           $uid = $session['uid']; // if not pass user id get session user id data
         }

         $sql = "SELECT
p.postid,p.uid,p.post_message,p.privacy,p.date,p.time,u.name,
if(u.profilePic='',if(u.gender=1,'male.png','female.png'),u.profilePic) as profilePic, p.gallery_id
FROM user_posts p
	LEFT JOIN users as u on p.uid = u.uid
WHERE p.uid = $uid ORDER by p.postid DESC";

         $totalData = $db->getRecords($sql);

         //------------------------
         if ($totalData!=NULL) {

           foreach ($totalData as $key => $value) {
             # code...

             $gallery_id = $value['gallery_id'];
             $sql2 = "SELECT * FROM photos WHERE gallery_id = '$gallery_id'";
             $gallery_photos = $db->getRecords($sql2);
             $value['gallery_photos'] =  $gallery_photos;
              $response[] = $value;
           }
           # code...
         }

         //$sql2 = "SELECT * FROM photos WHERE gallery_id = $gallery_id";
         //$response['gal_id'] = $gal_id;
         if ($response != NULL) {
           $this->response($this->json($response), 200); // send user details
         }

      } //getAllPostsUser End









    private function getAllRecords(){

    	if($this->get_request_method() != "POST"){
    			$this->response('',406);
    		}
       $r = json_decode(file_get_contents("php://input"),true);

       $table_name = $r['table'];
       $orderby = $r['oby'];
       $orderbyPos = $r['obyp'];

       $response = array();
       $db = new DbHandler();
       $sql = "SELECT * FROM ".$table_name;
       if(isset($r['where'])){
         $where = $r['where'];
         $id = $r['id'];
         $sql.=" WHERE ".$where." = ".$id;
       }

       $sql.=" ORDER BY ".$orderby." ".$orderbyPos;



       $response = $db->getRecords($sql);

       if ($response != NULL) {
         $this->response($this->json($response), 200); // send user details
       }

  	} //Login Check End


	private function sendmail($touser,$tomails = 0,$touname,$fname,$femail,$sub,$title,$msg,$status,$link,$isSingle=0){
				$db = new DbHandler();
				$session = $db->getSession();
				//$adminUsers = $db->getRecords("SELECT * FROM users WHERE usertype=$tousers");
				$to = $touser;
        $cc ='';


			   //$subject = $subject." , by ".$fromname." - Email:".$fromemail;
			   $subject = $sub;

			   $message= "Dear ".$touname.",<br><br><br>";
         $message.=$title."<br><br>";
         $message.=$msg."<br><br>";
         $message.=$status."<br><br>";
			   $message.="<br>Click <a href='".$link."'>here</a> to view details.";
         $message.="<br><br><br><br>Thanks<br><b>".$fname."</b>";
			   //$header = "From:webmaster@havafun.com \r\n";
			   $header = "From:".$femail." \r\n";


			   $header .= "MIME-Version: 1.0\r\n";
			   $header .= "Content-type: text/html\r\n";

         $mail = new PHPMailer(); // create a new object
         $mail->IsSMTP(); // enable SMTP
         $mail->SMTPDebug = 0; // debugging: 1 = errors and messages, 2 = messages only
         $mail->SMTPAuth = true; // authentication enabled
         //$mail->SMTPSecure = 'tls'; // secure transfer enabled REQUIRED for GMail
         $mail->Host = "mail.vaazu.com";
         $mail->Port = 25; // or 587
         $mail->IsHTML(true);
         $mail->Username = "support@vaazu.com";
         $mail->Password = "Member123";
         /*$mail->Username = "vaazuemail@gmail.com";
         $mail->Password = "Vaazu@2012";*/
         /*$mail->From = $femail;
         $mail->FromName = $fname;*/
         $mail->setFrom($femail,$fname);
         $mail->Subject = $subject;
         $mail->Body = $message;

         if($isSingle==0){

           switch ($tomails) {
             case 0:
             $mail->AddAddress($to);
             break;


              case 1: //1 CreteTicekt - Send to admin cc all support and created user
                $AllToUsers = $db->getRecords("SELECT * FROM users WHERE usertype = 0");
                //$cc.="Cc:";
                foreach($AllToUsers as $au){
                 $too=$au['email'];
                $mail->AddAddress($too);
               }

                $AllCCUsers = $db->getRecords("SELECT * FROM users WHERE usertype = 2");
                //$cc.="Cc:";
                foreach($AllCCUsers as $au){
                 $too=$au['email'];
                 $mail->AddCC($too);
               }
               $mail->AddCC($femail);

                  break;



              case 2: //2 ticketApproved - admin approve ticket send mails to support team in this we can take ticket user email and send as cc


               $AllCCUsers = $db->getRecords("SELECT * FROM users WHERE usertype = 2");
               //$cc.="Cc:";
               foreach($AllCCUsers as $au){
                $too=$au['email'];
                $mail->AddAddress($too);
              }
              $mail->AddCC($femail);


                 $AllToUsers = $db->getRecords("SELECT * FROM users WHERE usertype = 0");
                 //$cc.="Cc:";
                 foreach($AllToUsers as $au){
                  $too=$au['email'];
                 $mail->AddCC($too);
                }

                $mail->AddCC($to);



                  break;



              case 3: //3 ticketReplay  - admin sending reply to manager - in this we can take ticket user email and send as to waiting for approve (waiting for inistiator)

              $mail->AddAddress($to);

              $AllCCUsers = $db->getRecords("SELECT * FROM users WHERE usertype = 0 OR usertype = 2");
              //$cc.="Cc:";
              foreach($AllCCUsers as $au){
               $too=$au['email'];
              $mail->AddCC($too);
             }




                  break;
              case 4: //ticketReplay - manager sending reply to admin - in this we cant take ticket user email as cc when ticket status 4



              $AllCCUsers = $db->getRecords("SELECT * FROM users WHERE usertype = 0");
              //$cc.="Cc:";
              foreach($AllCCUsers as $au){
               $too=$au['email'];
              $mail->AddAddress($too);
             }

               $mail->AddCC($to);

             $AllCCUsers = $db->getRecords("SELECT * FROM users WHERE usertype = 2");
             //$cc.="Cc:";
             foreach($AllCCUsers as $au){
              $too=$au['email'];
             $mail->AddCC($too);
            }

                  break;



              case 5: //5 ticketReplay - support team sending mail to manager reply status we can take ticekt user email as to
                   $mail->AddAddress($to);
                   $AllCCUsers = $db->getRecords("SELECT * FROM users WHERE usertype = 0 OR usertype = 2");
                   //$cc.="Cc:";
                   foreach($AllCCUsers as $au){
                    $too=$au['email'];
                   $mail->AddCC($too);
                  }
                  break;

              case 6: //6 ticketReplay in this manager sending to support team, we can take ticekt user email as cc
              $AllCCUsers = $db->getRecords("SELECT * FROM users WHERE usertype = 2");
              //$cc.="Cc:";
              foreach($AllCCUsers as $au){
               $too=$au['email'];
              $mail->AddAddress($too);
             }

               $mail->AddCC($to);

             $AllCCUsers = $db->getRecords("SELECT * FROM users WHERE usertype = 0");
             //$cc.="Cc:";
             foreach($AllCCUsers as $au){
              $too=$au['email'];
             $mail->AddCC($too);
            }
                  break;

              case 7: //7 ticketReplay in this admin or support team sending mail to manager ticket user take it as to
              $mail->AddAddress($to);

              $AllCCUsers = $db->getRecords("SELECT * FROM users WHERE usertype = 0 OR usertype = 2");
              //$cc.="Cc:";
              foreach($AllCCUsers as $au){
               $too=$au['email'];
              $mail->AddCC($too);
             }
                  break;
              default:
                $mail->AddAddress($to);
          } //switch end


         }

          $mail->Send();

			   //$retval = mail ($to,$subject,$message,$header);


	}



  /*
  FakeUserGenerate Start
  */
  private function FakeUserGenerate(){

    if($this->get_request_method() != "POST"){
        $this->response('',406);
      }
     $r = json_decode(file_get_contents("php://input"),true);
     $r = $r['data']['results'];

     $db = new DbHandler();
     $session = $db->getSession();
     $MainData = array();
     for ($i=0; $i < count($r) ; $i++) {
       $name = $r[$i]['user']['name']['first']." ".$r[$i]['user']['name']['last'];
       $username = $r[$i]['user']['name']['first'].".".$r[$i]['user']['name']['last'];
       $gender = ($r[$i]['user']['gender']=='male')?1:0;
       $time = $r[$i]['user']['dob'];
       $date = date('m/d/Y',$time);
       $dates = explode("/",$date);
       $data = array("name"=>$name,
              "email"=>$r[$i]['user']['email'],
              "password"=>"user",
              "phone"=>$r[$i]['user']['phone'],
              "gender"=>$gender,
              "city"=>$r[$i]['user']['location']['city'],
              "state"=>$r[$i]['user']['location']['state'],
              "street"=>$r[$i]['user']['location']['street'],
              "zipcode"=>$r[$i]['user']['location']['zip'],
              "profilePic"=>$r[$i]['user']['picture']['large'],
              "username"=>$username,
              "dobDate"=>$dates[1],
              "dobMonth"=>$dates[0],
              "dobYear"=>$dates[2],
              "fake"=>1
              );
              $MainData[] = $data;
         $tabble_name = "users";
         $column_names = array('name', 'email', 'password', 'phone','gender', 'city', 'state','street','zipcode',
         'profilePic','username','dobDate','dobMonth','dobYear','fake'
       );
         $result = $db->insertIntoTable($data, $column_names, $tabble_name);

       } // End For Loop

        $response['data'] = $MainData;
         $response["status"] = "success";
         $response["message"] = "User accounts created successfully";
         //$response["uid"] = $result;

         $this->response($this->json($response), 200); // send user details

  } //FakeUserGenerate End

    private function createUser(){

    	if($this->get_request_method() != "POST"){
    			$this->response('',406);
    		}
       $r = json_decode(file_get_contents("php://input"),true);
       $r = $r['user'];

       $db = new DbHandler();
	   $session = $db->getSession();

       $email = $r['email'];

       if(isset($r['instagram_username']) && $r['instagram_username'] !=''){
         $instagram_username = $r['instagram_username'];
         $instagram_userid = $r['instagram_userid'] = '';
         $instagramDetails = $db->getInstagramDetails();
         $url = 'https://api.instagram.com/v1/users/search?q='.$instagram_username.'&client_id='.$instagramDetails['instagram_client_id'];
         $instagram= $db->processInstagramURL($url);
         $instagram_json = json_decode($instagram, true);
         $instagram_userid = $r['instagram_userid'] = $instagram_json['data'][0]['id'];
         $response['instagram'] = $instagram_json;
       }else{
         $instagram_username = $r['instagram_username'] = '';
         $instagram_userid = $r['instagram_userid'] = '';
       }


       $isUserExists = $db->getOneRecord("select 1 from users where email='$email'");
       if(!$isUserExists){
           //$r->customer->password = passwordHash::hash($password);
           $tabble_name = "users";
           $column_names = array('phone', 'name', 'email', 'password', 'city', 'profilePic','instagram_username','instagram_userid');
           $result = $db->insertIntoTable($r, $column_names, $tabble_name);
           if ($result != NULL) {
               $response["status"] = "success";
               $response["message"] = "User account created successfully";
               $response["uid"] = $result;

              // $sqll = "select 1 from users where uid=".$r['uid'];
                //$getTicketUser = $db->getOneRecord($sqll);
                 ////-$this->sendmail($r['email'],0,$r['name'],$session['name'],$session['email'],"Your Account Details","Find below credentials of your account","<b>Email:</b>".$r['email']."<br><b>Password:</b>".$r['password'],"",$this->site_url."/#/login",1);

               $this->response($this->json($response), 200); // send user details
           } else {
               $response["status"] = "error";
               $response["message"] = "Failed to create User. Please try again";
               $this->response($this->json($response), 200); // send user details
           }
       }else{
           $response["status"] = "error";
           $response["message"] = "An user with the provided phone or email exists!";
           $this->response($this->json($response), 200); // send user details
       }



  	} //Login Check End



     /**
     * Summary of recordProfileVisit
      */
    private function recordProfileVisit(){

    	if($this->get_request_method() != "POST"){
    			$this->response('',406);
    		}
       $r = json_decode(file_get_contents("php://input"),true);


       $db = new DbHandler();
	      $session = $db->getSession();
        $uid = $r['uid'] = $session['uid'];
        $time = $r['time'] = time();
        $r['type'] = 2; // 2 is visited

        $intrest_uid = $r['intrest_uid'];
        $ctime = $time-20000;

        $isUserExists = $db->getOneRecord("SELECT time from user_intrests WHERE uid = $uid and intrest_uid = $intrest_uid and type=2 and time > $ctime");
        if(!$isUserExists){
          $tabble_name = "user_intrests";
          $column_names = array('uid', 'type', 'intrest_uid', 'time');
          $result = $db->insertIntoTable($r, $column_names, $tabble_name);
          if ($result != NULL) {
              $response["status"] = "success";
              $response["message"] = "User visited logged successfully";

              $this->response($this->json($response), 200); // send user details
          } else {
              $response["status"] = "error";
              $response["message"] = "Failed to create User visit log. Please try again";
              $this->response($this->json($response), 200); // send user details
          }

        }



    } //recordProfileVisit End


    private function updateUser(){

    	if($this->get_request_method() != "POST"){
    			$this->response('',406);
    		}
       $r = json_decode(file_get_contents("php://input"),true);
       $r = $r['user'];

       $db = new DbHandler();
       $session = $db->getSession();
       $session_uid = $session['uid'];


       $email = $r['email'];
       $uid = $r['uid'];
       $profilePic = $r['profilePic'];
       $password = $r['password'];
       $instagram_username = $r['instagram_username'];
       $i_male = (isset($r['male']))?$r['male']:'false';
       $i_female = (isset($r['female']))?$r['female']:'false';

       if($session_uid == $uid ){
           $isUserExists = $db->getOneRecord("select 1 from users where email='$email' AND NOT uid =".$uid);
           if(!$isUserExists){
               //$r->customer->password = passwordHash::hash($password);

               if(isset($r['instagram_username']) && $r['instagram_username'] !=''){
                 $instagram_username = $r['instagram_username'];
                 $instagram_userid = $r['instagram_userid'] = '';
                 $instagramDetails = $db->getInstagramDetails();
                 $url = 'https://api.instagram.com/v1/users/search?q='.$instagram_username.'&client_id='.$instagramDetails['instagram_client_id'];
                 $instagram= $db->processInstagramURL($url);
                 $instagram_json = json_decode($instagram, true);
                 $instagram_userid = $r['instagram_userid'] = $instagram_json['data'][0]['id'];
                 $response['instagram'] = $instagram_json;
               }else{
                 $instagram_username = $r['instagram_username'] = '';
                 $instagram_userid = $r['instagram_userid'] = '';
               }


               $tabble_name = "users";
               $column_names = array('phone', 'name', 'email', 'password', 'city', 'designation','profilePic','instagram_username','instagram_userid','i_male','i_female');
               //$result = $db->insertIntoTable($r, $column_names, $tabble_name);
               extract($r);
               $sql = "UPDATE users SET phone='$phone',name='$name',email='$email',";
               if($password!=''){
               $sql.="password='$password',";
               }
               $sql.="city='$city',designation='$designation',bio='$bio',profilePic='$profilePic',instagram_username='$instagram_username',instagram_userid='$instagram_userid',i_male='$i_male',i_female='$i_female' WHERE uid = $uid";
               $result = $db->runQuery($sql);
               if ($result) {
                   $response["status"] = "success";
                   $response["message"] = "User account Updated successfully";
                   //$response["uid"] = $result;

                   $this->response($this->json($response), 200); // send user details
               } else {
                   $response["status"] = "error";
                   $response["message"] = "Failed to Update User. Please try again";
                   $this->response($this->json($response), 200); // send user details
               }
           }
       }else{
           $response["status"] = "error";
           $response["message"] = "An user with the provided email exists!";
           $this->response($this->json($response), 200); // send user details
       }



  	} //updateUser End



    private function deleteRecord(){

      if($this->get_request_method() != "POST"){
          $this->response('',406);
        }
       $r = json_decode(file_get_contents("php://input"),true);
       $db = new DbHandler();
       $table = $r['table'];
       $where = $r['where'];
       $id = $r['id'];
       $sql = "DELETE FROM $table WHERE $where = $id";
       $sql1 = "DELETE FROM tickets WHERE $where = $id";
       $sql2 = "DELETE FROM tickets_status WHERE $where = $id";
       $aaa = $db->runQuery($sql);
       $aaa1 = $db->runQuery($sql1);
       $aaa2 = $db->runQuery($sql2);
       if($aaa){
         $response["status"] = "success";
         $response["message"] = "Deleted successfully";
         $this->response($this->json($response), 200); // send user details
       }


    } //Login Check End


    /*
      getChatFriends Return User Friends
    */
    //getChat Start
    private function getChatFriends(){

      if($this->get_request_method() != "GET"){
          $this->response('',406);
        }
        $r = json_decode(file_get_contents("php://input"),true);
        //$r = $r['name'];
       $response = array();
       $db = new DbHandler();
       $session = $db->getSession();

         $uid = $session['uid'];

        $sql = "
SELECT u.uid,u.name,u.email,u.phone,u.username,u.gender,u.dobDate,u.dobMonth,u.dobYear,u.zipcode,u.city,
if(u.profilePic='',if(u.gender=1,'male.png','female.png'),u.profilePic) as profilePic
FROM
users u, friends f
WHERE u.uid = f.friend_two AND f.friend_one = $uid OR u.uid = f.friend_one AND f.friend_two = $uid";

      $queryResult = $db->getRecords($sql);
      if($queryResult!=NULL){
          $response = $queryResult;
      }

       if ($response != NULL) {
         $this->response($this->json($response), 200); // send user details
       }

    } //getChat End


    /*
      insertChatMsg
    */
    //insertChatMsg Start
    private function insertChatMsg2(){

      if($this->get_request_method() != "POST"){
          $this->response('',406);
        }
       $r = json_decode(file_get_contents("php://input"),true);


       $response = array();
       //$response = $r;
       $db = new DbHandler();
       $session = $db->getSession();

       $r['time'] = time();
       $r['uid'] = $session['uid'];
       $r['seen'] = 0;
       if(isset($r['group_id']) && $r['group_id']!=''){
         $roup_id = $r['group_id'] = $r['group_id'];
       }else{
         $r['group_id'] = 0;
       }



       //$uid = $session['uid'];

       $tabble_name = "messages";
       $column_names = array('uid','to_uid', 'message', 'seen','group_id','time');
       $result = $db->insertIntoTable($r, $column_names, $tabble_name);
       if ($result != NULL) {
           $response["status"] = "success";
           $response["message"] = "Your Message Sent successfully";

           //$sql = "SELECT * FROM user_posts WHERE postid = $result";

           //$response['post'] = $db->getRecords($sql);

         }

         $this->response($this->json($response), 200); // send user details


    } // insertChatMsg End


    /*
      getChatGroupId
    */
    //getChatGroupId Start
    private function getChatGroupId(){

      if($this->get_request_method() != "POST"){
          $this->response('',406);
        }
       $r = json_decode(file_get_contents("php://input"),true);


       $response = array();
       //$response = $r;
       $db = new DbHandler();
       $session = $db->getSession();

       $time = $r['time'] = time();
       $uid = $r['uid'] = $session['uid'];
       $to_uid = $r['to_uid'];


       $sql = "SELECT * FROM messages WHERE uid = $uid AND to_uid = $to_uid OR to_uid = $uid AND uid = $to_uid";
       $queryResult = $db->getRecords($sql);

     if($queryResult!=NULL){
         $response['group_id'] = $queryResult[0]['group_id'];
     }else{

        $data  = array('uid' => $uid,
                      'to_uid'=>$to_uid,
                      'message'=>'',
                      'seen'=>1,
                      'group_id'=>'',
                      'time'=>$time
                      );
       $tabble_name2 = 'messages';
        $column_names2 = array('uid', 'to_uid', 'message','seen','group_id','time');
        $result2 = $db->insertIntoTable($data, $column_names2, $tabble_name2);

        $sql = "UPDATE $tabble_name2 SET group_id=$result2 WHERE mid = $result2";
        $dq = $db->runQuery($sql);
        if($dq){
        }
        $response['group_id'] = $result2;

     }



         $this->response($this->json($response), 200); // send user details


    } // getChatGroupId End


    /*
      GetChatList
    */
    //GetChatList Start
    private function GetChatList(){

      if($this->get_request_method() != "POST"){
          $this->response('',406);
        }
       $r = json_decode(file_get_contents("php://input"),true);


       $response = array();
       //$response = $r;
       $db = new DbHandler();
       $session = $db->getSession();

       $uid = $r['uid'] = $session['uid'];
       $to_uid = $r['to_uid'];




       //$sql = "SELECT * FROM messages WHERE uid = $uid AND to_uid = $to_uid OR to_uid = $uid AND uid = $to_uid";

       if(isset($r['seen']) && $r['seen']==0){
         $seen = 0;
         $sql = "SELECT *, time as time_to_date FROM messages WHERE (uid = $uid AND to_uid = $to_uid  AND seen =  0) OR (to_uid = $uid AND uid = $to_uid AND seen =  0)";
       }else{
         $sql = "SELECT *, time as time_to_date FROM messages WHERE (uid = $uid AND to_uid = $to_uid) OR (to_uid = $uid AND uid = $to_uid)";
       }

       $queryResult = $db->getRecords($sql);

       $response =$queryResult ;

      $this->response($this->json($response), 200); // send user details

    } // GetChatList End


    /*
      GetChatList
    */
    //GetChatListRecent Start
    private function GetChatListRecent(){

      if($this->get_request_method() != "POST"){
          $this->response('',406);
        }
       $r = json_decode(file_get_contents("php://input"),true);


       $response = array();
       //$response = $r;
       $db = new DbHandler();
       $session = $db->getSession();

       $uid = $r['uid'] = $session['uid'];
       $to_uid = $r['to_uid'];
       $last_row_id = $r['last_row_id'];
       //$sql = "SELECT * FROM messages WHERE uid = $uid AND to_uid = $to_uid OR to_uid = $uid AND uid = $to_uid";


        $sql = "SELECT * FROM
	messages
WHERE
(uid = $uid OR uid = $to_uid)  AND (to_uid = $to_uid OR to_uid = $uid) AND mid > $last_row_id";


       $queryResult = $db->getRecords($sql);

       $response =$queryResult ;

      $this->response($this->json($response), 200); // send user details

    } // GetChatListRecent End


    private function photos_upload_session(){

    	if($this->get_request_method() != "POST"){
    			$this->response('',406);
    		}
       $r = json_decode(file_get_contents("php://input"),true);
       $db = new DbHandler();

       $session = $db->getSession();
       $uid = $session['uid'];
       $time = time();

       if (!isset($_SESSION)) {
           session_start();
       }
       $_SESSION['att_ses_id'] = $uid.'_'.$time;


  	} //Login Check End


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
