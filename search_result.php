<?php echo '<link rel="stylesheet" href="style.css">' ?>
<?php require_once('dbMaster.php'); ?>


<?php

  session_start();

  if(isset($_SESSION["ulogin"])) {
    header("location: wel_login.php");
  }

  if(isset($_REQUEST["login"])) {

    $username = strip_tags($_REQUEST["username"]);
    $password = strip_tags($_REQUEST["password"]);

    $er_msg[] = login_acc($username, $password);
  }
?>

<?php

  if(isset($_GET["s_city"])) {
    $city = $_GET["s_city"];
  }
 ?>

<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>

  <meta charset="utf-8">
  <title>WebHotels</title>
  <link rel="stylesheet" href="style.css">
  <meta http-equiv="refresh" content="300" >
</head>
<body>

  <header class="main-header">
    <div class="hder-container">
      <div class="hder-wrapp">
        <div class="left-col">
          <a href="index.php" style="margin: 0">
            <img class="imglogo" src="media/webHotels.png" alt="WebHotelsLogo">
          </a>
        </div>
        <div class="right-col">
          <button type="button" name="button" onclick="login_form()">Login</button>
        </div>
      </div>
    </div>
    </div>
  </header>

  <div id="show" class="main-f-login" style="display: none;">
    <div class="main-form-cont-login">
      <div class="main-form-wrapp-login">
        <?php
          if(isset($er_msg)) {
            foreach ($er_msg as $error) {
              ?>
                <div id="alert_error" class="alert_error" style="text-align: center; margin-bottom: 10px; padding-top: 30px;">
                  <strong><?php echo $error ?></strong>
                </div>
                <?php
            }
          }
          ?>
          <div class="text">Login Form</div>
            <form id="logn_form" class="main-form-login" method="post">
              <div class="login-data">
                <label>Username</label>
                <input type="text" name="username" maxlength="20" size="25" required>
              </div>
              <div class="login-data">
                <label>Password</label>
                <input type="password" name="password" maxlength="20" size="25" required>
              </div>
              <div class="login-button">
                <button class="login-btn" type="submit" name="login">Login</button>
              </div>
              <div class="signup">
                <label>Not a member?</label>
                <a href="create_form.php">Signup now</a>
              </div>
        </form>
      </div>
    </div>
  </div>

  <div class="main-search-div">
    <div class="main-search-cont">
      <div class="text-search"><strong>Choose city</strong></div>
        <div class="main-search-wrapp">
          <form method="get">
            <div class="main-search-input">
              <input type="text" name="s_city" placeholder="e.g. Athens">
            </div>
            <div class="main-search-but">
              <button type="submit">Search</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div id="photoModal" class="modal">
      <span class="close">&times;</span>
      <img class="modal-content" id="imgMod">
      <div id="caption"></div>
    </div>
    <div style="padding: 0 20px 150px">
      <?php search($city); ?>
    </div>

  <footer class="main-footer">
    <div class="main-footer-contain">
      <div class="main-footer-wrapp">
        <strong>Made by</strong>
        <strong>Kemeridis</strong>
      </div>
    </div>
  </footer>

  

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

  <script>
    $('.photo_btn_div').on('click', 'button', function(e){
      var img = document.getElementById(e.target.id);

      var modal = document.getElementById('photoModal');
      var modal_img = document.getElementById('imgMod');

      img.onclick = function() {
        modal.style.display = "block";
        modal_img.src = this.src;
      }
      var span = document.getElementsByClassName('close')[0];
      span.onclick = function() {
        modal.style.display = "none";
      }
    })
    </script>

  <script>
    function show_info(s) {
      var sh = document.getElementById('more-'+s);
      if(sh.style.display == "none") {
        sh.style.display = "flex";
        window.setTimeout(function(){
          sh.style.opacity = 1;
          sh.style.transform = "scale(1)";
        },10);
      } else {
        sh.style.display = "none";
        sh.style.opacity = 0;
        sh.style.transform = 'scale(0)';
        window.setTimeout(function(){
          sh.style.display = "none";
        },700);
      }
    }
  </script>

  <script>
    function login_form() {
      var elem = document.getElementById("show");
      if(elem.style.display === "none") {
        elem.style.display = "block";
        window.setTimeout(function(){
          elem.style.opacity = 1;
          elem.style.transform = "scale(1)";
        },30);
      } else if(elem.style.display === "block"){
        elem.style.opacity = 0;
        elem.style.transform = 'scale(0)';
        window.setTimeout(function(){
          elem.style.display = "none";
        },700);
      }
    }
  </script>
</body>

</html>
