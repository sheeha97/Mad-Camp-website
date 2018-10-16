<?
include "config.php";

function addSidFromTags($con, $sidArray, $tagArray)
{
  $tagQuery = "select * from scrum_tags where";
  for ($i = 0; $i < sizeof($tagArray); $i++)
  {
    if ($i == 0)
      $tagQuery .= sprintf(" (tag='%s')", $tagArray[$i]);
    else
      $tagQuery .= sprintf(" or (tag='%s')", $tagArray[$i]);
  }

  $result = $con->query($tagQuery);
  if ($result->num_rows > 0)
  {
    while($row = $result->fetch_assoc())
    {
      if (!in_array($row["_id"], $sidArray))
        array_push($sidArray, $row["_id"]);
    }
  }
  return $sidArray;
}

// infoArray : {"date" : [], "group_id" : [], "class" : [], "teamid" : []}
function addSidFromInfos($con, $sidArray, $infoArray)
{
  if (!(isset($infoArray["date"]) && isset($infoArray["group_id"]) && isset($infoArray["class"]) && isset($infoArray["teamid"])))
  {
    die("infoArray argument check failed");
  }
  $subQueryLst = array();

  // date info

  // create date subqueries : (week=4 and day=2), (week=4), ...
  $dateQueryLst = array();
  for ($i = 0; $i < sizeof($infoArray["date"]); $i++)
  {
    $date = $infoArray["date"][$i];
    if (empty($date))
      continue;
    if (!isset($date["week"])) die("fail : wrong argument format");

    if (isset($date["day"]) && !empty($date["day"]))
      $subQuery = sprintf("(week=%d&&day=%d)", $date["week"], $date["day"]);
    else
      $subQuery = sprintf("(week=%d)", $date["week"]);
    array_push($dateQueryLst, $subQuery);
  }
  // join
  if (sizeof($dateQueryLst) > 0) array_push($subQueryLst, "(".join('||', $dateQueryLst).")");


  // class info

  // create class subqueries : class=1, class=3, ...
  $classQueryLst = array();
  foreach($infoArray["class"] as $class)
  {
    if (empty($class))
      continue;
    array_push($classQueryLst, "class=$class");
  }
  // join
  if (sizeof($classQueryLst) > 0) array_push($subQueryLst, "(".join('||', $classQueryLst).")");


  // group_id info
  $gidQueryLst = array();
  foreach($infoArray["group_id"] as $gid)
  {
    if (empty($gid))
      continue;
    array_push($gidQueryLst, "group_id=$gid");
  }
  if (sizeof($gidQueryLst) > 0) array_push($subQueryLst, "(".join('||', $gidQueryLst).")");


  // teamid info
  $tidQueryLst = array();
  foreach($infoArray["teamid"] as $tid)
  {
    if (empty($tid))
      continue;
    array_push($tidQueryLst, "teamid=$tid");
  }
  if (sizeof($tidQueryLst) > 0) array_push($subQueryLst, "(".join('||', $tidQueryLst).")");


  // no info given -> do nothing
  if (sizeof($subQueryLst) <= 0)
  {
    return $sidArray;
  }

  $query = "select * from scrum_infos where ".join('&&', $subQueryLst);

  $result = $con->query($query);
  if ($result->num_rows > 0)
  {
    while($row = $result->fetch_assoc())
    {
      if (!in_array($row["_id"], $sidArray))
        array_push($sidArray, $row["_id"]);
    }
  }
  return $sidArray;
}
// 이름들 주어지면 그 이름들이 각각 포함되는 모든 팀 id의 배열을 반환.
function getTeamsFromNames($con, $nameArray)
{
  $query = "select distinct teamid from group_infos where";
  for ($i = 0; $i < sizeof($nameArray); $i++)
  {
    if ($i == 0)
    {
      $query .= sprintf(" name='%s'", $nameArray[$i]);
    }
    else
    {
      $query .= sprintf(" or name='%s'", $nameArray[$i]);
    }
  }
  $result = $con->query($query);
  $tidArray = Array();
  if ($result->num_rows > 0)
  {
    while($row = $result->fetch_assoc())
    {
      //if (!in_array($row['teamid'], $tidArray, true))
      array_push($tidArray, $row['teamid']);
    }
  }
  return $tidArray;
}

$input = json_decode(file_get_contents("php://input"), true);
//$input = json_decode('{"date":[],"tag":[],"name":[],"group_id":[],"class":[3]}', true);



$con = new mysqli("localhost", $db_user, $db_passwd, "week5");
if ($con->connect_error)
{
  die("Connection failed: " . $con->connect_error);
}


if (!isset($input["date"]) || !isset($input["tag"]) || !isset($input["group_id"]) || !isset($input["name"]) || !isset($input["class"]))
{
  die("argument fail : date, tag, group_id, name, class are needed");
}

$tagArr = $input["tag"];
$infoArr = array();
$infoArr["date"] = $input["date"];
$infoArr["group_id"] = $input["group_id"];
$infoArr["class"] = $input["class"];
$infoArr["teamid"] = getTeamsFromNames($con, $input["name"]);
//$infoArr["teamid"] = $input["teamid"];

// get all scrum_ids!
$sidArr = array();
$tagsidArr = addSidFromTags($con, array(), $tagArr);
$infosidArr = addSidFromInfos($con, array(), $infoArr);

if (sizeof($tagsidArr) === 0)
{
  $sidArr = $infosidArr;
}
else if (sizeof($infosidArr) === 0)
{
  $sidArr = $tagsidArr;
}
else
{
  foreach($tagsidArr as $sid)
  {
    if (in_array($sid, $infosidArr)) array_push($sidArr, $sid);
  }
}





// get contents for all scrum_ids!
// get all scrum infos
$searchResult = array();
foreach($sidArr as $sid)
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
