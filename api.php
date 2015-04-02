<?php

// Grab login text
Fresh::api("login-text",false,function($request,$response){

  include 'text.php';
  $response["text"] = $text["menu_text"]["en"];

});

// Grab shell text
Fresh::api("shell-text",false,function($request,$response){

  include 'text.php';
  $response["text"] = $text["shell_text"][$request["lang"]];

});

// Validate login credentials and generate session token
Fresh::api("login",false,function($request,$response){

  $filters = array(
    "username" => $request["username"]
  );
  
  $user = Fresh::fetch('cms_users',false,$filters);
  $user = $user[0];

  if(!$user){
    $response["message"] = "invalid_credentials";
  } else {

    $hash_rq = hash('sha256',$user["salt"] . $request["password"]);
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

});

// Authenticate session token and fetch user data
Fresh::api("auth",false,function($request,$response){
  
  global $user;
  
  $response["message"]    = $user ? "success" : "bad_token";
  $response["lang"]       = $user["lang"];
  $response["settings"]   = Fresh::fetch('cms_variables',false);
  $response["userLevels"] = Fresh::fetch('cms_user_levels',false); 

});

// Delete session with token
Fresh::api("logout",false,function($request,$response){

  Fresh::delete('cms_user_sessions',array(
    "session_token" => $request["session_token"],
    "user_id" => $user["id"] 
  ));
  
  $response["message"] = "success";

});

// Save general CMS settings
Fresh::api("save-general-settings",array(
  "auth"=>true,
  "level"=>2
  ),function($request,$response){
    
    $settings = $request["settings"];
    foreach($settings as $var)
      Fresh::update('cms_variables',$var,array("key"=>$var["key"]));
    
    $response["message"] = "success";
  
});

// Fetch a list of CMS user info
Fresh::api("cms-users",array(
  "auth"=>true,
  "level"=>1
  ),function($request,$response){
  
  $response["users"] = Fresh::fetch('cms_users',array(
    "username",
    "first_name",
    "last_name",
    "email",
    "image_id",
    "lang",
    "level",
    "id"
  ),array(),$request["from"]);
    
  $response["message"] = "success";
  
});

// Update CMS user info or create new user
Fresh::api("update-users",array(
  "auth"=>true,
  "level"=>1
  ),function($request,$response){
    
  $subject = $request["user"];
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
  
});

// Delete CMS users
// TODO: Add safety
Fresh::api("delete-cms-users",array(
  "auth"=>true,
  "level"=>1
  ),function($request,$response){
    
  foreach($request["ids"] as $id){
    
    Fresh::delete("cms_users",array("id"=>$id));
    Fresh::delete("cms_user_session",array("user_id"=>$id));
    
  }
  
  $response["message"] = "success";
  
});

?>