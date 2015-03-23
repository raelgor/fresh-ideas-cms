<?php

if($_NGPOST["request"] == "login-text"){

  include 'text.php';
  $response = $text["menu_text"]["en"];

}

if($_NGPOST["request"] == "login"){

  $filters = array(
    array(
      "field" => "username",
      "value" => $_NGPOST["username"]
    )
  );

  $user = Fresh::fetch('cms_users',false,$filters);
  $user = $user[0];

  if(!$user){
    $response["message"] = "invalid_credentials";
  } else {

    $hash_rq = hash('sha256',$user["salt"] . $_NGPOST["password"]);
    $hash_db = $user["password"];

    if($hash_rq != $hash_db){
      $response["message"] = "invalid_credentials";
    } else {

      $sql = "(SELECT TIMESTAMPADD( MINUTE , 7200 , CURRENT_TIMESTAMP ))";
      $session_token = md5(uniqid(mt_rand(), true));

      Fresh::insert('cms_user_sessions',array(
        "user_id" => $user["id"],
        "session_token" => $session_token,
        "expires" => array( "sql" => $sql )
      ));

      $response["message"] = "success";
      $response["session_token"] = $session_token;

    }

  }

}

?>