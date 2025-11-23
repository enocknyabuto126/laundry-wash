<?php
$dbcon=mysqli_connect('localhost', 'root', '', 'contact_us',3306);
//a function to sanitize user data

function sanitize($data){
    $data =htmlspecialchars($data);
    $data = trim($data);
  $data = stripslashes($data);
$data = mysqli_real_escape_string($GLOBALS['dbcon'], $data);
return $data;
}
 
?>