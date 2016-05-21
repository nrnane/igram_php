<?php

class DbHandler {

    private $conn;

    function __construct() {
        require_once 'dbConnect.php';
        // opening db connection
        $db = new dbConnect();
        $this->conn = $db->connect();
    }
    /**
     * Fetching single record
     */
    public function getOneRecord($query) {
        $r = $this->conn->query($query.' LIMIT 1') or die($this->conn->error.__LINE__);
        return $result = $r->fetch_assoc();
    }

    /**
     * Fetching ALl record
     */
    public function getRecords($query) {

        $r = $this->conn->query($query) or die($this->conn->error.__LINE__);
        while($data = $r->fetch_assoc()){

          if (isset($data['time'])) {
            $data['time']= $this->time_stamp($data['time']);
          }
          if(isset($data['last_status_time'])){
            $data['last_status_time']= $this->time_stamp($data['last_status_time']);
          }

          if(isset($data['time_to_date'])){
            $data['time_to_date']= $this->time_to_date($data['time_to_date']);
          }

          $result[] = $data;
      }
      if(empty($result)){

      }else{
          return $result;
      }

    }



function time_stamp($session_time)
{
  $time_difference = time() - $session_time ;

  $seconds = $time_difference ;
  $minutes = round($time_difference / 60 );
  $hours = round($time_difference / 3600 );
  $days = round($time_difference / 86400 );
  $weeks = round($time_difference / 604800 );
  $months = round($time_difference / 2419200 );
  $years = round($time_difference / 29030400 );
  // Seconds
  if($seconds <= 60)
  {
  return "$seconds seconds ago";
  }
  //Minutes
  else if($minutes <=60)
  {

     if($minutes==1)
    {
     return "1 minute ago";
     }
     else
     {
    return "$minutes minutes ago";
     }

  }
  //Hours
  else if($hours <=24)
  {

     if($hours==1)
    {
     return "1 hour ago";
    }
    else
    {
     return "$hours hours ago";
    }

  }
  //Days
  else if($days <= 7)
  {

    if($days==1)
    {
     return "1 day ago";
    }
    else
    {
     return "$days days ago";
     }

  }
  //Weeks
  else if($weeks <= 4)
  {

     if($weeks==1)
    {
     return "1 week ago";
     }
    else
    {
     return "$weeks weeks ago";
    }

  }
  //Months
  else if($months <=12)
  {

     if($months==1)
    {
     return "1 month ago";
     }
    else
    {
     return "$months months ago";
     }

  }
  //Years
  else
  {

     if($years==1)
     {
    return "1 year ago";
     }
     else
    {
    return "$years years ago";
     }

  }

} //End Timestamp//


function time_to_date($session_time)
{
  return date('Y-m-d H:i:s',$session_time);
} //End Timestamp//


function time_elapsed_A($secs){
    $bit = array(
        'y' => $secs / 31556926 % 12,
        'w' => $secs / 604800 % 52,
        'd' => $secs / 86400 % 7,
        'h' => $secs / 3600 % 24,
        'm' => $secs / 60 % 60,
        's' => $secs % 60
        );

    foreach($bit as $k => $v)
        if($v > 0)$ret[] = $v . $k;


    return join(' ', $ret);
    }


function time_elapsed_B($secs){
    $bit = array(
        ' year'        => $secs / 31556926 % 12,
        ' week'        => $secs / 604800 % 52,
        ' day'        => $secs / 86400 % 7,
        ' hour'        => $secs / 3600 % 24,
        ' minute'    => $secs / 60 % 60,
        ' second'    => $secs % 60
        );

    foreach($bit as $k => $v){
        if($v > 1)$ret[] = $v . $k . 's';
        if($v == 1)$ret[] = $v . $k;
        }
    array_splice($ret, count($ret)-1, 0, 'and');
    $ret[] = 'ago.';

    return join(' ', $ret);
    }
    /**
     * Creating new record
     */
    public function insertIntoTable($obj, $column_names, $table_name) {

        $c = (array) $obj;
        $keys = array_keys($c);
        $columns = '';
        $values = '';
        foreach($column_names as $desired_key){ // Check the obj received. If blank insert blank into the array.
           if(!in_array($desired_key, $keys)) {
                $$desired_key = '';
            }else{
                $$desired_key = $this->conn->real_escape_string($c[$desired_key]);
            }
            $columns = $columns.$desired_key.',';
            $values = $values."'".$$desired_key."',";
        }
        $query = "INSERT INTO ".$table_name." (".trim($columns,',').") VALUES (".trim($values,',').")";
        $r = $this->conn->query($query) or die($this->conn->error.__LINE__);

        if ($r) {
            $new_row_id = $this->conn->insert_id;
            return $new_row_id;
            } else {
            return NULL;
        }
    }



    /**
     * Creating new record
     */
    public function updateTable($obj, $column_names, $table_name) {

        $c = (array) $obj;
        $keys = array_keys($c);
        $columns = '';
        $values = '';
        foreach($column_names as $desired_key){ // Check the obj received. If blank insert blank into the array.
           if(!in_array($desired_key, $keys)) {
                $$desired_key = '';
            }else{
                $$desired_key = $this->conn->real_escape_string($c[$desired_key]);
            }
            $columns = $columns.$desired_key.',';
            $values = $values."'".$$desired_key."',";
        }
        $query = "UPDATE ".$table_name." (".trim($columns,',').") VALUES (".trim($values,',').")";
        $r = $this->conn->query($query) or die($this->conn->error.__LINE__);

        if ($r) {
            $new_row_id = $this->conn->insert_id;
            return $new_row_id;
            } else {
            return NULL;
        }
    }


    public function processInstagramURL($url)
     {
         $ch = curl_init();
         curl_setopt_array($ch, array(
         CURLOPT_URL => $url,
         CURLOPT_RETURNTRANSFER => true,
         CURLOPT_SSL_VERIFYPEER => false,
         CURLOPT_SSL_VERIFYHOST => 2
         ));
         $result = curl_exec($ch);
         curl_close($ch);
         return $result;

     }


    /*
      Delete Record Based on Query Return True
    */
    public function checkQuery($sql,$return) {


        $query = $sql;
        $r = $this->conn->query($query) or die($this->conn->error.__LINE__);

        if ($r) {
              return $r[$return];
            } else {
            return NULL;
        }
    }


    /*
      Run Query Return True
    */
	  public function runQuery($query) {

        $r = $this->conn->query($query) or die($this->conn->error.__LINE__);

          if ($r) { return true; }
    }

    /*
      Run Query Return True
    */
	  public function runQueryReturnData($query) {

        $r = $this->conn->query($query) or die($this->conn->error.__LINE__);

          if ($r) { return $r; }
    }

    /*
      Delete Record Based on Query Return True
    */
    public function deleteQuery($sql) {


        $query = $sql;
        $r = $this->conn->query($query) or die($this->conn->error.__LINE__);

        if ($r) {
              return true;
            } else {
            return NULL;
        }
    }






public function getInstagramDetails(){
  $query = "SELECT * FROM settings WHERE id = 1";
      $r = $this->conn->query($query) or die($this->conn->error.__LINE__);
      $data = $r->fetch_assoc();

      if(empty($data)){
        return false;
      }else{
          return $data;
      }
}

public function getSession(){
    if (!isset($_SESSION)) {
        session_start();
    }
    $sess = array();
    if(isset($_SESSION['uid']))
    {
        $sess["uid"] = $_SESSION['uid'];
        $sess["name"] =$_SESSION['name'];
        $sess["email"] = $_SESSION['email'];
        $sess["isAdmin"] = $_SESSION['isAdmin'];
    }
    else
    {
        $sess["uid"] = '';
        $sess["name"] = 'Guest';
        $sess["email"] = '';

    }
    return $sess;
}

public function destroySession(){
    if (!isset($_SESSION)) {
    session_start();
    }
    if(isSet($_SESSION['uid']))
    {
        unset($_SESSION['uid']);
        unset($_SESSION['name']);
        unset($_SESSION['email']);

        $info='info';
        if(isSet($_COOKIE[$info]))
        {
            setcookie ($info, '', time() - $cookie_time);
        }
        $msg="Logged Out Successfully...";
    }
    else
    {
        $msg = "Not logged in...";
    }
    return $msg;
}

}

?>
