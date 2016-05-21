<?php
require 'Slim/Slim.php';
\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();

$app->get('/hello/:firstname/:lastname',function($first,$last)
{
  # code...
  echo 'Hellow '.$first.' '.$last;
});

$app->post('/mobile',function(){
  $response = array("Name"=>"Nagaraju","LastName"=>"Adepu");
  $app->contentType('application/json');
  header("Access-Control-Allow-Origin: *");
  header("Access-Control-Allow-Headers: Authorization");
  header("Access-Control-Allow-Methods: GET,HEAD,PUT,PATCH,POST,DELETE");
  echo json_encode($response);
});

function echoResponse($status_code, $response) {
    $app = \Slim\Slim::getInstance();
    // Http response code
    $app->status($status_code);

    // setting response content type to json
    $app->contentType('application/json');

    echo json_encode($response);
}

$app->run();
?>
