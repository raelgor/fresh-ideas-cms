<?php

class Fresh
{

  function update($table,$array,$key){


  }

  function authenticate(){


  }

  function fetch($table,$fields,$filters){

    global $config;
    global $dbh;

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