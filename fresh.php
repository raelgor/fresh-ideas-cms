<?php

class Fresh
{

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

      foreach($filters as $filter){

        $str = '`' . $filter["field"] . '` = :' . $filter["field"];
        array_push($filter_string,$str);

        $params[":".$filter["field"]] = $filter["value"];

      }

      $query .= implode(' AND ',$filter_string);

    }

    $sql .= "LIMIT $from,$toFetch;";

    $query_obj = $dbh->prepare($query);

    $query_obj->execute($params);

    return $query_obj->fetchall(PDO::FETCH_ASSOC);

  }

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

}

?>