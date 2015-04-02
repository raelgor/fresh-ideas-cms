<?php
/*
** Fresh Framework 0.1.0
** Copyright (c) 2015 Kosmas Papadatos
** Licence: MIT
**
** A tiny PHP-MySQL-Auth framework for small jobs.
**
** WARNING: This code is part of a project and does not work independently. The
** Fresh class expects a $dbh variable with a PDO connection to a MySQL 
** database as well as a $config array with settings.
*/

class Fresh
{
  
  public static $API_HANDLERS = array(); 
  public static $API_RESPONSE = array("api_version"=>"0.1.0");
  
  // Update data in table with or without keys
  // TODO: Add custom SQL option
  function update($table,$data,$keys){
    
    global $config;
    global $dbh;
    
    $sql = "UPDATE `".$config["prefix"]."_$table` SET ";
    $params = array();
    $data_array = array();
    $keys_array = array();
    
    foreach($data as $key => $value){
      
      array_push($data_array,"`$key` = :$key");
      $params[":$key"] = $value;
      
    }
    
    $sql .= implode(" , ",$data_array) . " WHERE ";
    
    foreach($keys as $key => $value){
      
      array_push($keys_array,"`$key` = :$key");
      $params[":$key"] = $value;
      
    }
    
    $sql .= implode(" AND ",$keys_array);
    
    $query = $dbh->prepare($sql);
    $query->execute($params);
    
    return true;

  }
  
  // Delete data from table with keys
  // TODO: Add safety and custom SQL option
  function delete($table,$conditions){
    
    global $config;
    global $dbh; 
    
    $params = array();
    $sql = "DELETE FROM `".$config["prefix"]."_".$table."` WHERE ";
    $str = array();
    
    foreach($conditions as $key => $value){
      
      array_push($str,"`$key` = :$key");
      $params[":$key"] = $value; 
      
    };
    
    $sql .= implode(' AND ',$str); 
    
    $query = $dbh->prepare($sql);
    $query->execute($params); 
    
    return true;
    
  }

  // Authenticates a session token
  function authenticate(){

    global $config;
    global $dbh;
    global $_NGPOST;

    $sql = "SELECT * FROM `".$config["prefix"]."_cms_users` WHERE id = (
      SELECT user_id FROM `".$config["prefix"]."_cms_user_sessions`
      WHERE session_token = :session_token
      AND `expires` > CURRENT_TIMESTAMP
    )";
    
    $query = $dbh->prepare($sql);
    $query->execute(array(":session_token"=>$_NGPOST["session_token"]));

    return $query->fetch(PDO::FETCH_ASSOC);

  }

  // Makes a select call and returns data from table
  // TODO: Custon SQL option in filters and fields
  function fetch($table,$fields,$filters,$from,$toFetch){

    global $config;
    global $dbh;
    
    $from    = $from    ? $from    : 0;
    $toFetch = $toFetch ? $toFetch : 50; 

    $query = 'SELECT ';
    $params = array();

    if($fields && count($fields)){

      $fields = '`' . implode('`,`',$fields) . '`';

    } else $fields = '*';

    $query .= $fields . ' FROM ' . $config["prefix"] . '_' . $table;

    if($filters && count($filters)){

      $query .= ' WHERE ';
      $filter_string = array();

      foreach($filters as $field => $value){

        $str = "`$field` = :$field";
        array_push($filter_string,$str);

        $params[":$field"] = $value; 

      }

      $query .= implode(' AND ',$filter_string);

    }

    $sql .= "LIMIT $from,$toFetch;";
    
    $query_obj = $dbh->prepare($query);

    $query_obj->execute($params);

    return $query_obj->fetchall(PDO::FETCH_ASSOC);

  }
  
  // Inserts data in table from array
  function insert($table,$data){

    global $config;
    global $dbh;

    $query = 'INSERT INTO ' . $config["prefix"] . '_' . $table . ' (`';

    $fields  = array();
    $aliases = array();
    $params  = array();

    foreach($data as $key => $value){

      array_push($fields,$key);

      if(!is_array($value)){

        array_push($aliases,':'.$key);
        $params[':'.$key] = $value;

      } else {

        array_push($aliases,$value["sql"]);

      }

    }

    $query .= implode('`,`',$fields) . '`) VALUES (' . implode(',',$aliases) . ')';

    $query_obj = $dbh->prepare($query);

    $query_obj->execute($params);

    return true;

  }
  
  // Registers a new API call handler
  function api($api,$options,$handler){ 
    
    $API_HANDLERS =& static::$API_HANDLERS;
    $options      = $options ? $options : array();
    
    $API_HANDLERS[$api] = $API_HANDLERS[$api] ? $API_HANDLERS[$api] : array();
    
    $options["handler"]  = $handler;
    
    if(!$options["request"]){ 
      
      $options["requests"] = array();
      $API_HANDLERS[$api] = $options;
    
    }
    else $API_HANDLERS[$api]["requests"][$options["request"]] = $options;
    
  }
  
  // Handles an API call or returns an error JSON if no handler has been 
  // assigned
  function handle($post_request){ 
    
    global $user;
    
    $API_HANDLERS =& static::$API_HANDLERS;
    
    $api      = $post_request["api"] ? 
                $post_request["api"] : $post_request["request"];
    $request  = $post_request["api"] && $post_request["request"] ? 
                $post_request["request"] : false;
    $response =& static::$API_RESPONSE;
    
    $API = $api && $request ? 
      $API_HANDLERS[$api]["requests"][$request]
      : $api ? $API_HANDLERS[$api]
      : false;
      
    if($API){
      
      if(!$API["auth"] || ($API["auth"] && $user)){ 
        if(!$API["level"] || ($API["level"] >= intval($user["level"]))) $API["handler"]($post_request,&$response);
        else $response["message"] = "api_access_denied";
      } else $response["message"] = "bad_token";
      
      echo json_encode($response);
      
    } else {
      $response["message"] = "invalid_api_call";
    }
    
  }

}

?>