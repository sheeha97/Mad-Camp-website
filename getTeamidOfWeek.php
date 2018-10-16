<?php
include "config.php";

session_start();
if ($_SERVER["REQUEST_METHOD"] === 'GET')
{
  if (!isset($_GET["week"]))
  {
    die("GET argument failed: usage : /getTeamidOfWeek.php?week=1");
  }

  $week = $_GET["week"]; // string (아니면 array<string>)

  if (!isset($_SESSION["class"]))
  {
    die("Login first");
  }
  $class = $_SESSION["class"];

  $con = new mysqli("localhost", $db_user, $db_passwd, "week5");
  if ($con->connect_error)
  {
    die("Connection failed: " . $con->connect_error);
  }

  $query = sprintf("select distinct teamid from group_infos where week=%s and class=%s", $week, $class);
  $result = $con->query($query);
  $tidArray = Array();
  if ($result->num_rows > 0)
  {
    while($row = $result->fetch_assoc())
    {
      array_push($tidArray, $row["teamid"]);
    }
  }

  $jsonArray = Array();
  foreach($tidArray as $teamid)
  {
    $assocArray = Array(); // {teamid : 0, name : ["asdf", "qwer"]}

    $query = "select name from group_infos where teamid=".$teamid;
    $result = $con->query($query);
    $nameArray = Array();
    if ($result->num_rows > 0)
    {
      while($row = $result->fetch_assoc())
      {
        array_push($nameArray, $row["name"]);
      }
    }
    $assocArray["teamid"] = $teamid;
    $assocArray["name"] = $nameArray;
    array_push($jsonArray, $assocArray);
  }

  echo json_encode($jsonArray);
  exit;
}
else
{
  die("Protocol fail: only GET method allowed");
}
?>
