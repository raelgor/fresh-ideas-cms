<?php

if($_NGPOST["request"] == "login-text"){

  include 'text.php';
  $response = $text["menu_text"]["en"];

}

if($_NGPOST["request"] == "login"){

  $filters = array(
    "username" => $_NGPOST["username"]
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
      "level",
      "id"
    ),array(),$_NGPOST["from"]);
    $response["message"] = "success";
  } else {
    $response["message"] = "bad_token";
  }
  
}

if($_NGPOST["request"] == "update-users"){
  
  if($user){
    
    $subject = $_NGPOST["user"];
    $prefix  = $config["prefix"];
    
    $data = array(
      "first_name" => $subject["first_name"],
      "last_name" => $subject["last_name"],
      "username" => $subject["username"],
      "email" => $subject["email"],
      "level" => $subject["level"],
      "lang" => $subject["lang"],
      "image_id" => $subject["image_id"]
    );
    
    if($subject["id"]){
      
      if($subject["password"]){
        
        $updatee = Fresh::fetch('cms_users',array("salt"),array(
          "id" => $subject["id"]
        ));
        
        $salt = $updatee[0]["salt"];
        $data["password"] = hash('sha256',$salt . $subject["password"]);
        
      }
      
      Fresh::update("cms_users",$data,array(
        "id" => $subject["id"]  
      ));
      
    } else {
      
      $subject["password"] = $subject["password"] ? $subject["password"] : '';
      $salt = uniqid(mt_rand(),true);
      
      $data["password"] = hash('sha256',$salt . $subject["password"]);
      $data["salt"]     = $salt;
      
      Fresh::insert('cms_users',$data);
      
      $query = $dbh->query("SELECT `id`,`image_id`,`username`,`first_name`,`last_name`,`username`,`email`,`lang`,`level` FROM $prefix"."_cms_users WHERE id = (SELECT max(id) FROM $prefix"."_cms_users)");
      $response["user"] = $query->fetch(PDO::FETCH_ASSOC);
    
    }
    
    $response["message"] = "success";
    
  } else {
    $response["message"] = "bad_token";
  };
  
}

if($_NGPOST["request"] == "delete-cms-users"){
  
  if($user){
    
    foreach($_NGPOST["ids"] as $id){
      
      Fresh::delete("cms_users",array("id"=>$id));
      Fresh::delete("cms_user_session",array("user_id"=>$id));
      
    }
    
    $response["message"] = "success";
  
  } else {
    $response["message"] = "bad_token";
  }
  
}

?>