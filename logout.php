<?php
session_start();
if (isset($_SESSION["role"]) && isset($_SESSION["class"])) {
  session_destroy();
  //header("location: ".$_SERVER["HTTP_REFERER"]);
  echo "<script>alert('로그아웃이 완료되었습니다.')</script>";
  echo "<script>document.location.href = 'mainpage.html'</script>";
  exit;
}
else {
  //header("location: space_login.html");
  echo "<script>alert('You're not logged in')</script>";
  echo "<script>document.location.href = 'space_login.html'</script>";
  exit;
}

?>
