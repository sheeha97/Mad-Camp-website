<?php
include "config.php";

$con = new mysqli("localhost", $db_user, $db_passwd, "week5");
if ($con->connect_error)
{
  die("Connection failed: " . $con->connect_error);
}



//$query = "select distinct $column from $table";
$result = $con->query($query);

$items = array();
if ($result->num_rows > 0)
{
  while($row = $result->fetch_assoc())
  {

  }
}

$db_user = "root";
$db_passwd = "qlalfqjsgh";

//connect to sql
$conn = new mysqli("localhost", $db_user, $db_passwd, "week5");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

//query statement
//this is for inserting
//$sql = "insert into test values ('아이디', '비밀번호');";

  $sql = "SELECT distinct tag FROM scrum_tags";
  $result = $conn->query($sql);
  $noticeArray = array();

  if ($result->num_rows > 0) {
      // output data of each row
      while($row = $result->fetch_assoc()) {
          array_push($noticeArray, $row["tag"]);
      }
  } else {
      echo "0 results";
  }


  echo json_encode($noticeArray);

$conn->close();
?>
