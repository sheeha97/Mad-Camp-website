<?php

include "config.php";

$con = new mysqli("localhost", $db_user, $db_passwd, "week5");
if ($con->connect_error)
{
  die("Connection failed: " . $con->connect_error);
}

$class = (int)$_GET["class"];

if ($class < 0 || $class > $max_class)
  die("Wrong class");
// return notices for given class.
// 0 -> entire notice

$noticeArray = array();

$query = "select * from notice where type='$class' order by timestamp desc";
$result = $con->query($query);
if ($result->num_rows > 0)
{
  while ($row = $result->fetch_assoc())
  {
    $notice = array();
    $notice["title"] = $row["title"];
    $notice["content"] = $row["content"];
    $notice["writer"] = $row["writer"];
    array_push($noticeArray, $notice);
  }
}

echo json_encode($noticeArray);
exit;

?>
