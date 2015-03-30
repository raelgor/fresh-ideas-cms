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

if($_NGPOST["request"] == "auth"){

  $response["message"] = $user ? "valid_token" : "bad_token";
  $response["lang"] = $user["lang"];
  $response["settings"] = Fresh::fetch('cms_variables',false);
  $response["userLevels"] = Fresh::fetch('cms_user_levels',false); 

}

if($_NGPOST["request"] == "shell-text"){

  include 'text.php';
  $response = $text["shell_text"][$_NGPOST["lang"]];

}

if($_NGPOST["request"] == "logout"){

  Fresh::delete('cms_user_sessions',array(
    "session_token" => $_NGPOST["session_token"],
    "user_id" => $user["id"] 
  ));
  
  $response["message"] = "success";

}

if($_NGPOST["request"] == "save-general-settings"){
  
  if($user){
    $settings = $_NGPOST["settings"];
    foreach($settings as $var){
      Fresh::update('cms_variables',$var,array("key"=>$var["key"]));
    }
    $response["message"] = "success";
  } else {
    $response["message"] = "bad_token";
  }
  
}

if($_NGPOST["request"] == "cms-users"){
  
  if($user){
    $response["users"] = Fresh::fetch('cms_users',array(
      "username",
      "first_name",
      "last_name",
      "email",
      "image_id",
      "lang",
      "level" 
    ),$_NGPOST["from"]);
    $response["message"] = "success";
  } else {
    $response["message"] = "bad_token";
  }
  
}

?>