<?php
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

if (isset($_GET["classNum"]))
{
  $classNum = $_GET["classNum"];
  $sql = "select * from scrums_title where class=$classNum order by timestamp desc";
  $result = $conn->query($sql);
  $scrumArray = array();
  // echo($scrumArray);
  if ($result->num_rows > 0) {
      // output data of each row
      while($row = $result->fetch_assoc()) {
          //array_push($scrumArray, $row["title"]);
          $itemArray = array();
          $itemArray["title"] = $row["title"];
          $itemArray["week"] = $row["week"];
          $itemArray["day"] = $row["day"];
          $itemArray["group_id"] = $row["group_id"];
          array_push($scrumArray, $itemArray);
      }
  }


  echo json_encode($scrumArray);
}
$conn->close();
?>
