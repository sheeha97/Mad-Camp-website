<?php
include "config.php";


$group_id = $_GET["group_id"];
$week = $_GET["week"];
$day = $_GET["day"];

/*
$group_id = "11";
$week = "5";
$day = "6";
*/
$con = new mysqli("localhost", $db_user, $db_passwd, "week5");
if ($con->connect_error)
{
  die("Connection failed: " . $con->connect_error);
}

// get teamid
$tidArray = array();
$query = sprintf("select distinct teamid from group_infos where group_id=%s", $group_id);
$result = $con->query($query);
if ($result->num_rows > 0)
{
  while($row = $result->fetch_assoc())
  {
    array_push($tidArray, $row["teamid"]);
  }
}
else
{
  echo "No teamid found for group_id $group_id";
  exit;
}
//echo $tidArray;

// get scrum ids
$scrum_ids = array();
foreach($tidArray as $tid)
{
  $query = sprintf("select distinct _id from scrum_infos where teamid=%s and week=%s and day=%s", $tid, $week, $day);
  $result = $con->query($query);
  if ($result->num_rows > 0)
  {
    while ($row = $result->fetch_assoc())
    {
      array_push($scrum_ids, $row["_id"]);
    }
  }
}

// get all scrum infos
$searchResult = array();
foreach($scrum_ids as $sid)
{
  // scrum_info :
  // {"title" : "이게필요할까","class" : 3, "day" : 3, "week" : 2, "group_id" : 5, "teamid" : 2, "names" : ["asdf", "qwer", "zxcv"], "content" : "asdfzxcvqwerasdf" , "tags" : ["web", "service"]}

  $scrum_info = array();
  $query = sprintf("select * from scrum_infos where _id=%s", $sid);
  $result = $con->query($query);
  if ($result->num_rows === 1)
  {
    $row = $result->fetch_assoc();
    $scrum_info["week"] = $row["week"];
    $scrum_info["day"] = $row["day"];
    $scrum_info["group_id"] = $row["group_id"];
    $scrum_info["class"] = $row["class"];
    $scrum_info["teamid"] = $row["teamid"];
  }
  else
  {
    die("Query failed : $query returned more than or less than 1");
  }

  $query = sprintf("select * from scrum_tags where _id=%s", $sid);
  $result = $con->query($query);
  $tags = array();
  if ($result->num_rows > 0)
  {
    while ($row = $result->fetch_assoc())
    {
      array_push($tags, $row["tag"]);
    }
  }
  $scrum_info["tags"] = $tags;

  // content
  $query = sprintf("select * from scrum_contents where _id=%s", $sid);
  $result = $con->query($query);
  $content = "";
  if ($result->num_rows > 0)
  {
    while ($row = $result->fetch_assoc())
    {
      $content .= $row["content"];
    }
  }
  $scrum_info["content"] = $content;

  // names
  $query = sprintf("select * from group_infos where teamid=%s", $scrum_info["teamid"]);
  $result = $con->query($query);
  $names = array();
  if ($result->num_rows > 0)
  {
    while ($row = $result->fetch_assoc())
    {
      array_push($names, $row["name"]);
    }
  }
  $scrum_info["names"] = $names;

  // title
  $query = sprintf("select * from scrums_title where group_id=%s and week=%s and day=%s", $scrum_info["group_id"], $scrum_info["week"], $scrum_info["day"]);
  $result = $con->query($query);
  if ($result->num_rows === 1)
  {
    $row = $result->fetch_assoc();
    $scrum_info["title"] = $row["title"];
  }
  else
  {
    die("Query failed : $query returned more than or less than 1");
  }

  array_push($searchResult, $scrum_info);
}

echo json_encode($searchResult);
exit;

?>
