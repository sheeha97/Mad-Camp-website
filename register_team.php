<?php
include "config.php";

$con = new mysqli("localhost", $db_user, $db_passwd, "week5");
if ($con->connect_error)
{
  die("Connection failed: " . $con->connect_error);
}


// generate a teamid which is not in group_infos.
function gen_teamid($con, $id){
  $idQuery = "select * from group_infos where teamid=".$id;
  while ($con->query($idQuery)->num_rows > 0)
  {
    $id++;
    $idQuery = "select * from group_infos where teamid=".$id;
  }
  return $id;
}

$week = (rand() % 5) + 1;
$teams = array(array("alice", "bob", "chris"), array("don","eve"));
$teamid = 0;

foreach($teams as $team){
  $teamid = gen_teamid($con, $teamid);
  $query = "insert into group_infos (teamid, name, week) values";
  for ($i = 0; $i < sizeof($team); $i++)
  {
    if ($i == 0)
    {
      $query .= sprintf(" (%d, '%s', %d)", $teamid, $team[$i], $week);
    }
    else
    {
      $query .= sprintf(", (%d, '%s', %d)", $teamid, $team[$i], $week);
    }
  }
  if ($con->query($query) === false)
  {
    die("[INSERTION FAIL] query : $query");
  }
  else
  {
    echo ("insertion success!");
  }
}

$con->close();
exit;
?>
