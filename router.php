<?php

/*
** FreshCMS
** Copyright (c) 2015 Kosmas Papadatos
** License: MIT
*/

// Gzip all responses
ob_start("ob_gzhandler");

// Load framework
require_once 'ugly.php';
require_once 'cssmin.php';
require_once 'fresh.php';

// Check if configuration file is present
$config = file_get_contents('config.php');

// Parse request
$path = explode('/',$_SERVER["REQUEST_URI"]);

if($config){

  require_once 'config.php';

  $host = $config["db_host"];
  $user = $config["db_username"];
  $pass = $config["db_password"];

  $db_name = $config["db_name"];

  $dbh = new PDO("mysql:host=$host;dbname=$db_name", $user, $pass);
  $dbh->exec("SET NAMES utf8");
  $dbh->exec("SET time_zone = '+2:00'");

}

// Handle api call
if($path[count($path)-1] == 'api'){

  $_NGPOST = json_decode(file_get_contents('php://input'),true);

  $user = Fresh::authenticate();
  
  // If no module api is referenced, use core api
  if(!$_NGPOST["api"] && !$_REQUEST["api"]){
    include 'api.php';
  } else {

    // Search and load referenced module api
    $modules = scandir("modules");

    foreach($modules as &$folder){

      if($folder == "." || $folder == "..") continue;

      $module_files = scandir("modules/".$folder);
      $search = array_search('api.php',$module_files);

      if(!is_nan($search) && $search != null){
        include 'modules/' . $folder . '/config.php';
        if($module_config["api_namespace"] == $_REQUEST["api"]){
          include 'modules/' . $folder . '/api.php';
        }
      }

    }

  }

  Fresh::handle($_NGPOST);
  exit();
  
}

// If we don't have a configuration file, run installer
if(!$config){
  require_once 'modules/installer/index.html';
  exit();
}

// Cache all other content
header("Cache-Control: max-age=31536000");

// Bind all javascript
if($path[count($path)-1] == 'js.min'){

  header("Content-type: text/javascript");

  $js = array();
  $scripts = scandir('scripts');
  $modules = scandir('modules');

  // main.js should always be first
  array_push($js,file_get_contents('main.js'));

  foreach($modules as &$module_root){

    include 'modules/'.$module_root.'/config.php';

    if($module_config["js_exports"]){

      foreach($module_config["js_exports"] as &$js_file)
        array_push($js,file_get_contents('modules/'.$module_root.'/'.$js_file));

    }

  }

  foreach($scripts as &$filename)
                  array_push($js,file_get_contents('scripts/'.$filename));

  //foreach($js as &$u) $u = \JShrink\Minifier::minify($u);

  echo implode(' ; ',$js);

  exit();

}

// Bind all css
if($path[count($path)-1] == 'css.min'){

  header("Content-type: text/css");

  $scr = array();
  $css = scandir('css');
  $modules = scandir('modules');

  foreach($modules as &$module_root){

    include 'modules/'.$module_root.'/config.php';

    if($module_config["css_exports"]){

      foreach($module_config["css_exports"] as &$file)
        array_push($scr,file_get_contents('modules/'.$module_root.'/'.$file));

    }

  }

  foreach($css as &$filename)
                  array_push($scr,file_get_contents('css/'.$filename));

  foreach($scr as &$u) $u = CssMin::minify($u);

  echo implode(' ',$scr);

  exit();

}


include 'templates/client.html';

?>