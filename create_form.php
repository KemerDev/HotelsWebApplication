<?php echo '<link rel="stylesheet" href="style.css">' ?>
<?php require_once('dbMaster.php') ?>
<?php

  if(captcha_Val() == true){
    if(isset($_REQUEST["create"])) {

      $usr = strip_tags($_REQUEST["username"]);
      $pwd = strip_tags($_REQUEST["password"]);
      $eml = strip_tags($_REQUEST["email"]);

      $er_msg[] = create_acc($usr, $pwd, $eml);
    }
  } else {
    $er_cap[] = "Please tick the captcha";
  }
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
  <meta charset="utf-8">
  <title>login form</title>
  <meta http-equiv="refresh" content="300">
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>

<body>

  <header class="main-header">
    <div class="hder-container">
      <div class="hder-wrapp">
        <div class="hder-main">
          <a href="index.php" style="margin: 0">
            <img src="media/webHotels.png" alt="WebHotelsLogo">
          </a>
        </div>
      </div>
    </div>
  </header>

  <div class="main-form-bod">
    <div class="main-form-cont-bod">
      <div class="main-form-wrapp-bod">

        <form id="cre_get" class="main-form" action="create_form.php" method="post">
          <div class="text-cre"> Create Account</div>
          <?php
            if(isset($er_msg)) {
              foreach ($er_msg as $error) {
                ?>
                <div id="errno" style="text-align: center; margin: 10px 0 10px;">
                  <strong><?php echo $error; ?></strong>
                </div>
                <?php
              }
            }
           ?>
          <div id="inuse" style="text-align: center; margin: 10px 0 10px; display: none;"><strong>username already in use</strong></div>
          <div id="notinuse" style="text-align: center; margin: 10px 0 10px; display: none;"><strong>username available</strong></div>

          <div class="sign-data">
            <label>Username</label>
            <input id="uname" class="col-right-inp" type="text" name="username" maxlength="20" size="40" required>
          </div>
          <div class="sign-data">
            <label>Password</label>
            <input class="col-right-inp" type="password" name="password" maxlength="20" size="40"  required>
          </div>
          <div class="sign-data">
            <label>Email</label>
            <input class="col-right-inp" type="text" name="email" maxlength="100" size="40" required>
          </div>

          <?php
            if(isset($er_cap)) {
              foreach ($er_cap as $error) {
                ?>
                <div id="er_msg" style="margin: 10px 0 10px;">
                  <strong><?php echo $error; ?></strong>
                </div>
                <?php
              }
            }
           ?>
          <div class="g-recaptcha" data-sitekey="6LfrFbwaAAAAADXP9fQ_wDhwSNdaBmxja2PVLDvX"></div>
            <br/>
              <button class="sign-btn" type="submit" name="create">SignUp</button>
        </form>
      </div>
    </div>
  </div>
  <footer class="main-footer">
    <div class="main-footer-contain">
      <div class="main-footer-wrapp">
        <strong>Made by</strong>
        <strong>Kemeridis</strong>
      </div>
    </div>
  </footer>

  <script>
    var err = document.getElementById('errno');
    setTimeout(function(){
      err.style.display = "none";
    }, 5000);
  </script>

</body>
</html>
