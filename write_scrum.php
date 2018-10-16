<?
include "config.php";

function arrArgCheck($arr, $args){
  foreach($args as $arg){
    if (!isset($arr[$arg])) die("argument check failed : $arg required");
  }
}

function gen_groupid($con, $id){
  $idQuery = "select * from scrums_title where group_id=".$id;
  while ($con->query($idQuery)->num_rows > 0)
  {
    $id++;
    $idQuery = "select * from scrums_title where group_id=".$id;
  }
  return $id;
}


session_start();
if (!(isset($_SESSION)))
{
  echo "<script> alert('Login first'); history.back();</script>";
  exit;
}

if (!(isset($_SESSION["role"]) && isset($_SESSION["class"])))
{
  // only valid user can write scrum
  echo "<script> alert('You cannot write scrum.'); history.back();</script>";
  exit;
}





$input = json_decode(file_get_contents("php://input"), true);
//$input = json_decode('{"title":"테스트용~","week":5,"day":6,"scrums":[{"teamid":2,"tags":["Game","Android"],"content":"ㅎㅇㅎㅇ"},{"teamid":3,"tags":["Web","Unreal"],"content":"ㅋㅋㅋ"},{"teamid":2,"tags":["ML"],"content":"맞미ㅏㄱ날"}]}', true);

// false -> input_json이 stdClass(Object)
// true -> input_json이 array()
var_dump($input);

// arguments to check
$outerArgList = array("title", "week", "day", "scrums");
$innerArgList = array("teamid", "tags", "content");

// check outer argument
arrArgCheck($input, $outerArgList);
if (!is_array($input["scrums"])) {
  die("argument check failed : scrums should be array");
}

$con = new mysqli("localhost", $db_user, $db_passwd, "week5");
if ($con->connect_error)
{
  die("Connection failed: " . $con->connect_error);
}

$title = mysqli_real_escape_string($con, $input["title"]);
$day = mysqli_real_escape_string($con, $input["day"]);
$week = mysqli_real_escape_string($con, $input["week"]);

//$class = 3;
//$role = 2;
$class = $_SESSION["class"];

// insert scrum_group
$group_id = gen_groupid($con, 0);
$groupQuery = sprintf("insert into scrums_title (group_id, day, week, class, title) values (%d, %d, %d, %d, '%s')", $group_id,$day, $week, $class, $title);
if ($con->query($groupQuery) === false)
{
  echo "Insertion failed : scrum group not inserted - query = $groupQuery";
  exit;
}

echo "New scrum group inserted, _id : ".$group_id;
echo "<br>";

$scrums = $input["scrums"];
foreach($scrums as $scrum)
{
  arrArgCheck($scrum, $innerArgList);
  if (!is_array($scrum["tags"])) {
    die("argument check failed : tags should be array");
  }

  $teamid = mysqli_real_escape_string($con, $scrum["teamid"]);
  $content = mysqli_real_escape_string($con, $scrum["content"]);
  $tags = $scrum["tags"];
  for ($i = 0; $i < sizeof($tags); $i++){
    $tags[$i] = mysqli_real_escape_string($con, $tags[$i]);
  }

  $query = sprintf("insert into scrum_infos (group_id, day, week, teamid, class) values (%d,%d,%d,%d,%d);", $group_id, $day, $week, $teamid, $class);
  echo "$query";
  if ($con->query($query) === true)
  {
    $last_id = $con->insert_id;
    echo "New scrum info inserted, _id : ".$last_id;
    echo "<br>";

    // insert tags
    foreach($tags as $tag)
    {
      $query = "insert into scrum_tags (_id, tag) values ($last_id, '$tag')";
      if ($con->query($query) === true)
      {
        echo "New scrum tag inserted, _id : $last_id";
        echo "<br>";
      }
      else
      {
        echo "Failed to insert tag, _id : $last_id, tag : $tag";
        echo "<br>";
        exit;
      }
    }

    // insert content
    $query = "insert into scrum_contents (_id, content) values ($last_id, '$content')";
    if ($con->query($query) === true)
    {
      echo "New scrum content inserted, _id : $last_id";
      echo "<br>";
    }
    else
    {
      echo "Failed to insert content, _id : $last_id";
      echo "<br>";
      exit;
    }
  }
  else
  {
    echo "Failed to insert scrum_info, group_id : $group_id";
    exit;
  }
}

exit;
?>
