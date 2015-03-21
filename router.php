<?php

/*
** FreshCMS
** Copyright (c) 2015 Kosmas Papadatos
** License: MIT
*/

// Gzip all responses
ob_start("ob_gzhandler");

// Load framework
require_once 'fresh.php';

// Check if configuration file is present
$config = file_get_contents('config.php');

// Parse request
$path = explode('/',$_SERVER["REQUEST_URI"]);

// Handle api call
if($path[count($path)-1] == 'api'){

  // If no module api is referenced, use core api
  if(!$_REQUEST["api"]){
    include 'api.php';
    exit();
  }

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

  exit();

}

// If we don't have a configuration file, run installer
if(!$config){
  require_once 'modules/installer/index.html';
  exit();
}

// Cache all other content
header("Cache-Control: max-age=31536000");

require_once 'config.php';

?>