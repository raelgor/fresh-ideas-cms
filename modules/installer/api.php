<?php

if($_REQUEST["request"]!="initialize"||$config) exit();

$variables = json_decode($_REQUEST["vars"]);

$host = $variables->DB_HOST;
$user = $variables->DB_USERNAME;
$pass = $variables->DB_PASSWORD;

$db_name = $variables->DB_NAME;

$dbh = new PDO("mysql:host=$host;dbname=$db_name", $user, $pass);
$dbh->exec("SET NAMES utf8");
$dbh->exec("SET time_zone = '+2:00'");

$query = $dbh->query('select 1 + 1 as test');
$result = $query->fetch(PDO::FETCH_ASSOC);

if($result["test"]!=2) die("Unable to connect to database or
                            insufficient permissions.");

$modules = scandir('modules');

$prefix = "";
$chars = explode(" ",
"q w e r t y u i o p a s d f g h j k l z x c v b n m 1 2 3 4 5 6 7 8 9 0");

for($i = 0; $i < 5; $i++) $prefix .= $chars[rand(0,count($chars)-1)];

$salt = mcrypt_create_iv(16,MCRYPT_DEV_URANDOM);
$hash = hash('sha256',$salt + $variables->CMS_PASSWORD);

// Create core tables
$dbh->exec("

CREATE TABLE `".$prefix."_cms_users` (
  `id` int not null primary key auto_increment,
  `username` varchar(100),
  `password` varchar(300),
  `first_name` varchar(50),
  `last_name` varchar(50),
  `email` varchar(100),
  `image_id` int,
  `salt` varchar(300)
);

CREATE TABLE `".$prefix."_cms_variables` (
  `key` varchar(100) primary key,
  `value` varchar(200)
);

INSERT INTO `".$prefix."_cms_users` (`username`,`password`,`salt`) values
('".$variables->CMS_USERNAME."','".$hash."','".$salt."');

");

foreach($modules as &$folder){



}

$php = '<?php

$config = array(
  "domain"      => "'.$variables->DOMAIN_NAME.'",
  "cms_root"    => "'.$variables->CMS_ROOT.'",
  "db_host"     => "'.$variables->DB_HOST.'",
  "db_username" => "'.$variables->DB_USERNAME.'",
  "db_password" => "'.$variables->DB_PASSWORD.'",
  "db_name"     => "'.$variables->DB_NAME.'",
  "prefix"      => "'.$prefix.'"
);

?>';

$config_file = fopen("config.php","w");
fwrite($config_file,$php);
fclose($config_file);

?>