<?php echo '<link rel="stylesheet" href="style.css">' ?>
<?php
  require_once('dbMaster.php');

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

  $db = db_connect();

  if(isset($_GET["s_city"])) {
    $city = strip_tags($_GET["s_city"]);

    if(!empty($city)) {
      $sel_stmt = $db->prepare("SELECT h_id, name, country, city, address, phone, rooms, descr FROM userhotels WHERE city = :city");
      $sel_stmt->execute(array(':city' => $city));

      while($row = $sel_stmt->fetch(PDO::FETCH_ASSOC)) {
        if(strtolower($row["city"]) !== strtolower($city)) {
          $erno_msg[] = "No matches found for that city";
          header("location: http://localhost/project/index.php");
        } else {
          $url = "http://localhost/project/search_result.php?s_city=$city";
          header("location: $url");
          break;
        }
      }
    } else {
      $erno_msg[] = "Type a city";
    }
  }
  ?>

<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>

  <meta charset="utf-8">
  <title>WebHotels</title>
  <link rel="stylesheet" href="style.css">
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
      <div id="error" style="width: 1103px;bottom:507px;position: absolute;text-align:center;"><strong><?php
              if(isset($erno_msg)){
                  foreach ($erno_msg as $error) {
                      echo $error;
                    }
                  } ?></strong></div>
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

  <footer class="main-footer">
    <div class="main-footer-contain">
      <div class="main-footer-wrapp">
        <strong>Made by</strong>
        <strong>Kemeridis</strong>
      </div>
    </div>
  </footer>

  <script>
    var error = document.getElementById('error');
    setTimeout(function(){
      error.style.display = "none";
    }, 5000);
  </script>

  <script>
    function login_form() {
      var elem = document.getElementById("show");
      if(elem.style.display === "none") {
        elem.style.display = "block";
        window.setTimeout(function(){
          elem.style.opacity = 1;
          elem.style.transform = "scale(1)";
        },10);
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
