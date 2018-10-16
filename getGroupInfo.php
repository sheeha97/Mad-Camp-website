<?php
include "config.php";

$con = new mysqli("localhost", $db_user, $db_passwd, "week5");
if ($con->connect_error)
{
  die("Connection failed: " . $con->connect_error);
}

session_start();
if (!isset($_SESSION["class"]))
{
  die("login first");
}

if ($_SERVER['REQUEST_METHOD'] !== "GET")
{
  die("only GET method is allowed");
}

$class = $_SESSION["class"];
$week = $_GET["week"];

$query = "select distinct group_id from group_infos where week=$week and class=$class";
$result = $con->query($query);

$gidArray = array();
if ($result->num_rows > 0)
{
  while ($row = $result->fetch_assoc())
  {
    array_push($gidArray, $row["group_id"]);
  }
}

$resArray = array(); // [ {"group_id" : 1, "teams" : [{"teamid" : 1, "names" : ["asdf", "qwer", "zxcv"]}] ]
foreach($gidArray as $gid)
{
  $query = "select distinct teamid from group_infos where week=$week and class=$class and group_id=$gid";
  $result = $con->query($query);

  $pair = array();
  $pair["group_id"] = $gid;
  $pair["teams"] = array();
  if ($result->num_rows > 0)
  {
    while ($row = $result->fetch_assoc())
    {
      $teamid = $row["teamid"];
      $query2 = "select name from group_infos where week=$week and class=$class and teamid=$teamid";
      $result2 = $con->query($query2);

      $teams = array();
      $teams["teamid"] = $teamid;
      $teams["names"] = array();

      if ($result2->num_rows > 0)
      {
        while ($row2 = $result2->fetch_assoc())
        {
          array_push($teams["names"], $row2["name"]);
        }
      }
      array_push($pair["teams"], $teams);
    }
  }

  array_push($resArray, $pair);
}

echo json_encode($resArray);
exit;
?>
