<?php
include "config.php";


$input = json_decode(file_get_contents("php://input"), true);

$con = new mysqli("localhost", $db_user, $db_passwd, "week5");
if ($con->connect_error)
{
  die("Connection failed: " . $con->connect_error);
}


foreach($input as $student)
{
  $query = sprintf("insert into students (name, email, class) values ('%s', '%s', %d)", $student["name"], $student["email"], $student["class"]);
  if ($con->query($query) === false)
  {
    die("[INSERTION FAIL] query : $query");
  }

}

// change name.json file

?>
