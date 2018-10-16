<!-- Navbar (sit on top) -->
<?php session_start()?>
<div class="w3-top">
  <div class="w3-bar w3-white w3-card" id="myNavbar">
    <a href="index.html" class="w3-bar-item w3-button w3-wide">CS496</a>
    <!-- Right-sided navbar links -->
    <div class="w3-right w3-hide-small">
      <a href="mainpage.html" class="w3-bar-item w3-button"><i class="fa fa-info-circle"></i> About CS496</a>
      <a href="application.html" class="w3-bar-item w3-button"><i class="fa fa-pencil"></i> Application</a>
      <a href="space.html" class="w3-bar-item w3-button"><i class="fa fa-clipboard"></i> Space</a>
      <a href="past.html" class="w3-bar-item w3-button"><i class="fa fa-calendar"></i> Past CS496</a>
      <?php
        if(isset($_SESSION["role"]) && isset($_SESSION["class"]))
        {
          echo '<a href="logout.php" class="w3-bar-item w3-button"><i class="fa fa-sign-out"></i> Logout</a>'; // TODO: 아이콘 바꾸기
        }
      ?>
    </div>
    <!-- Hide right-floated links on small screens and replace them with a menu icon -->

    <a href="javascript:void(0)" class="w3-bar-item w3-button w3-right w3-hide-large w3-hide-medium" onclick="w3_open()">
      <i class="fa fa-bars"></i>
    </a>
  </div>
</div>
