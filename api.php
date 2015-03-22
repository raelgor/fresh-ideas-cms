<?php

if($_NGPOST["request"] == "login-text"){

  include 'text.php';

  echo json_encode($text["menu_text"]["en"]);

}

if($_NGPOST["request"] == "login"){



}

?>