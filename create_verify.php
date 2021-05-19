<?php echo '<link rel="stylesheet" href="style.css">' ?>
<?php require_once('dbMaster.php'); ?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>verify</title>
    <meta http-equiv="refresh" content="10" >
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

    <div class="ver_cont">
      <div class="ver_wrapp">
        <?php
          if(isset($_GET["hash"])) {
            $ver_hash = $_GET["hash"];
            if(verify_acc($ver_hash)){
              header("refresh:5; wel_login.php");
              ?>
              <div>
                <strong>VERIFICATION COMPLETE YOU CAN NOW LOGIN</strong>
              </div>
              <div>
                <strong>YOU WILL BE REDIRECTED SHORTLY</strong>
              </div>
              <?php
            }
          }
         ?>
        <div>
          <strong>THANK YOU FOR SIGNING UP</strong>
        </div>
        <div>
          <strong>PLEASE VERIFY YOUR ACCOUNT VIA EMAIL</strong>
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
  </body>
</html>
