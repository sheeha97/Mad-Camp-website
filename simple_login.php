<?php
// POST passwd : sha1(original passwd)
  include ("config.php");
  $role = -1;
  $class = -1;
  if (isset($_POST["passwd"]))
  {
    $con = new mysqli("localhost", $db_user, $db_passwd, "week5");
    $passwd = mysqli_real_escape_string($con, $_POST["passwd"]);

    $query = "select class, role from users where pw='$passwd';";
    $result = $con->query($query);

    if ($result->num_rows > 0) {
        // echo one row
        $row = $result->fetch_assoc();
        $role = $row["role"];
        $class = $row["class"];
        $position;
        
        if($class == 0){
          $class = "관리자";
        }
        if($role == 0){
          $position = "관리자";
        }
        if($role == 1){
          $position = "교수님";
        }
        if($role == 2){
          $position = "조교";
        }
        if($role == 3){
          $position = "학생";
        }
        echo "<script>alert('" . $class . "분반에 오신 걸 환영합니다. " . $position . " 권한 입니다.');</script>";

    } else {
        echo "<script>alert('password no no')</script>";
    }
  }
  if ($role === -1 || $class === -1)
  {
    echo "<script>alert('password no no');history.back();</script>";
    exit;
  }
  session_start();
  $_SESSION["role"] = $role;
  $_SESSION["class"] = $class;
  echo "<script>alert('".$role."분반에 오신 것을 환영합니다'');</script>";
  //header('location: space.html');
  echo "<meta http-equiv='refresh' content='0;url=space.html'>"
?>
