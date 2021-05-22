<?php require_once('dbMaster.php'); ?>
<?php echo '<link rel="stylesheet" href="style.css">' ?>
<?php
  session_start();

  if(!isset($_SESSION["ulogin"])) {
    header("location: index.php");
  }

  $id = $_SESSION["ulogin"];
  $_SESSION["logout"] = time();
  if($_COOKIE["type"] == time()+300) {
    logout();
  } else if(isset($_GET["out"])) {
    logout();
  }?>

<?php

  if(isset($_POST["submit-form"])) {
    $name = strip_tags($_REQUEST["hotel_name"]);
    $country = strip_tags($_REQUEST["country"]);
    $city = strip_tags($_REQUEST["city"]);
    $address = strip_tags($_REQUEST["address"]);
    $phone = strip_tags($_REQUEST["phone"]);
    $rooms = strip_tags($_REQUEST["rooms"]);
    $desc = strip_tags($_REQUEST["graph_text"]);
    $id = $_SESSION["ulogin"];

    $f_name = $_FILES["images"]["name"];
    $f_size = $_FILES["images"]["size"];
    $f_type = $_FILES["images"]["type"];
    $f_temp = $_FILES["images"]["tmp_name"];

    ins_hotel($name, $country, $city, $address, $phone, $rooms, $desc, $id);
    uphotel_img($f_name, $f_size, $f_type, $f_temp, $name);
  }?>

<?php

  $db = db_connect();

  $sel_stmt = $db->prepare("SELECT * FROM userhotels");
  $sel_stmt->execute();

  while($row = $sel_stmt->fetch(PDO::FETCH_ASSOC)) {
    $temp = "update/".$row["h_id"];
    if (isset($_POST[$temp])) {
      $hotel_id = $row["h_id"];
      $name = strip_tags($_POST["edit_name"]);
      $country = strip_tags($_POST["edit_country"]);
      $city = strip_tags($_POST["edit_city"]);
      $address = strip_tags($_POST["edit_address"]);
      $phone = strip_tags($_POST["edit_phone"]);
      $rooms = strip_tags($_POST["edit_rooms"]);
      $desc = strip_tags($_POST["edit_descri"]);

      edit_hotel($name, $country, $city, $address, $phone, $rooms, $desc, $hotel_id);
      break;
    }
  }?>

<?php

  $db = db_connect();

  foreach ($_POST as $key => $value) {
     $photo_id = $key;
  }

  $sel_stmt = $db->prepare("SELECT * FROM hotels_photos WHERE id = :id");
  $sel_stmt->execute(array(':id' => $photo_id));
  $row = $sel_stmt->fetch(PDO::FETCH_ASSOC);

  $file_name = $row["p_name"];

  if(isset($_POST[$photo_id])) {

    $files = glob('image_temp/*');
    foreach($files as $file){
      $string =  basename($file);
      if($string === $file_name) {
        unlink($file);
        break;
      }
    }
    $del_stmt = $db->prepare("DELETE FROM hotels_photos WHERE id = :id");
    $del_stmt->execute(array(':id' => $photo_id));
  }
  ?>

<?php
    $db = db_connect();

    $sel_stmt = $db->prepare("SELECT * FROM userhotels");
    $sel_stmt->execute();

    while($row = $sel_stmt->fetch(PDO::FETCH_ASSOC)) {
      $temp = "upload_" .$row["h_id"];
      if(isset($_POST[$temp])) {
        $hotel_name = $row["name"];
        $f_name = $_FILES["n_photos"]["name"];
        $f_size = $_FILES["n_photos"]["size"];
        $f_type = $_FILES["n_photos"]["type"];
        $f_temp = $_FILES["n_photos"]["tmp_name"];

        uphotel_img($f_name, $f_size, $f_type, $f_temp, $hotel_name);
        exit();
        break;
      }
    }
  ?>

<?php

  $db = db_connect();

  $ho_stmt = $db->prepare("SELECT * FROM userhotels");
  $ho_stmt->execute();

  while($h_row = $ho_stmt->fetch(PDO::FETCH_ASSOC)) {
    $temp = "delete/".$h_row["h_id"];
    $po_stmt = $db->prepare("SELECT * FROM hotels_photos WHERE h_id = :pid");
    $po_stmt->execute(array(':pid' => $h_row["h_id"]));
    if(isset($_POST[$temp])) {
      $files = glob('image_temp/*');
      while($p_row = $po_stmt->fetch(PDO::FETCH_ASSOC)) {
        $file_name = $p_row["p_name"];
        foreach($files as $file){
          $string = basename($file);
          if($string === $file_name) {
            unlink($file);
            break;
          }
        }
      }
      $del_stmt = $db->prepare("DELETE FROM userhotels WHERE h_id = :hid");
      $del_stmt->execute(array(':hid' => $h_row["h_id"]));
    }
    break;
  }
  ?>

<?php
  $db = db_connect();

  if(isset($_POST["cha_photo"])) {
    $temp = $_FILES["photo"]["tmp_name"];
    $size = $_FILES["photo"]["size"];
    $photo = file_get_contents($temp);

    if($size < 307200) {
      $upd_stmt = $db->prepare("UPDATE users SET img = :img WHERE user_id = :id");
      $upd_stmt->execute(array(':img' => $photo, ':id' => $id));
      $succ_msg[] = "Profile picture changed";
    } else {
      $errno_ch[] = "image size too big";
    }

  }
    ?>

<?php
  $db = db_connect();

  $sel_stmt = $db->prepare("SELECT * FROM users WHERE user_id = :id");
  $sel_stmt->execute(array(':id' => $id));
  $row = $sel_stmt->fetch(PDO::FETCH_ASSOC);

  if(isset($_POST["cha_pass"])) {
    $old_pass = $_POST["old_pass"];
    $new_pass = $_POST["n_pass"];
    $verify_pass = $_POST["v_pass"];

    try {
      if(strcmp($new_pass, $verify_pass) == 0) {
        $hate = hash('sha512', $old_pass);
        if(hash_equals($hate, $row["password"])) {
          $hashed_new_pass = hash('sha512', $new_pass);
          $upd_stmt = $db->prepare("UPDATE users SET password = :pass WHERE user_id = :id");
          $upd_stmt->execute(array(':id' => $id, ':pass' => $hashed_new_pass));
          $succ_msg[] = "password succesfully changed";
        } else {
          $er_msg[] = "Old password is not right";
        }
      } else {
        $er_msg[] = "new password and verify does not match";
      }
    } catch(PDOEXCEPTION $e) {
      return $e -> getMessage();
    }
  }
 ?>

<?php
  $db = db_connect();

  if(isset($_POST["cha_name"])) {

  $uname = strip_tags($_POST["use_name"]);

  try {
        $upd_stmt = $db->prepare("UPDATE users SET username = :name WHERE user_id = :id");
        $upd_stmt->execute(array(':id' => $id, ':name' => $uname));
        $succ_msg[] = "username succesfully changed";
      } catch(PDOEXCEPTION $e) {
        return $e -> getMessage();
      }
  }
  ?>

<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
  <meta charset="utf-8">
  <title>WebHotels</title>
  <link id="stylelida" rel="stylesheet" href="style.css">
  <script src="jquery-3.5.1.min.js"></script>
  <meta http-equiv="refresh" content="300" >
</head>
<body onload="onloadputstyle()">
  <header class="main-header-wel">
    <div class="hder-container-wel">
      <div class="hder-wrapp-wel">
        <div class="left-col-wel">
          <a href="index.php" style="margin: 0">
            <img class="imglogo" src="media/webHotels.png" alt="WebHotelsLogo">
          </a>
          <div id="suc_msg" class="success_message" style="margin-left: 500px;padding-top:40px;display: inline;position:absolute">
            <strong><?php
              if(isset($succ_msg)) {
                foreach ($succ_msg as $success) {
                  echo $success;
                }
              }
            ?></strong>
          </div>
        </div>
        <div class="right-col-wel">
          <div class="right-col-img">
            <button onclick="but_prof()"><img src="data:image/png;base64,<?php get_img($id) ?>" width="100" height="100" alt="user"></button>
          </div>
          <div id="show-set" class="prof-menu-main">
            <div class="prof-menu-contain">
              <div class="prof-menu-set">
                <a href="index.php">
                  Home
                </a>
              </div>
              <div class="prof-menu-set">
                <a href="wel_login.php">
                  Account
                </a>
              </div>
              <div class="prof-menu-set">
                <div>
                  <button onclick="cha_mode('dark.css')" name="button">Dark</button>
                  <button onclick="cha_mode('style.css')" name="button">light</button>
                </div>
              </div>
              <div class="prof-menu-set">
                <form method="get">
                  <button type="submit" name="out">Logout</button>
                </form>
              </div>
            </div>
          </div>
        </div>
    </div>
  </div>
  </header>

  <div class="main-body-cont">
    <p id="wel_mes">
      <?php
          $db = db_connect();

          $sel_stmt = $db->prepare("SELECT * FROM users WHERE user_id=:id");
          $sel_stmt->execute(array(":id" => $id));

          $row = $sel_stmt->fetch(PDO::FETCH_ASSOC);
          if(isset($_SESSION["ulogin"])) {
              echo "welcome " .$row['username'];
            }
          ?></p>
    <div class="main-body-wrapp">
      <div class="left-col-up">
        <div class="left-col-contain-up">
          <div>
            <button onclick="up_show()"><strong>Upload Hotel</strong></button>
          </div>
          <div>
            <button onclick="ed_show()"><strong>Edit Hotel</strong></button>
          </div>
          <div>
            <button onclick="ac_show()"><strong>Edit Account</strong></button>
          </div>
        </div>
      </div>
      <div class="right-col-up">
        <div class="right-col-contain-up">
          <div class="right-col-upload">
            <form id="up-show" enctype="multipart/form-data" method="post" style="display: flex;">
              <div>
                <input type="text" name="hotel_name" maxlength="30" size="40" placeholder="hotel name" required>
              </div>
              <div>
                <input type="text" name="country" maxlength="30" size="30" placeholder="country" required>
              </div>
              <div>
                <input type="text" name="city" maxlength="30" size="30" placeholder="city" required>
              </div>
              <div>
                <input type="text" name="address" maxlength="30" size="40" placeholder="address" required>
              </div>
              <div>
                <input type="text" name="phone" maxlength="10" size="40" placeholder="phone" required>
              </div>
              <div>
                <input type="text" name="rooms" maxlength="3" size="20" placeholder="available rooms" required>
              </div>
              <div id="paragraph">
                <textarea name="graph_text" cols="50" rows="10" placeholder="description" required></textarea>
              </div>
              <div>
                <strong>Upload photos</strong>
                <input type="file" name="images[]" multiple>
              </div>
              <div id="button">
                <button type="submit" name="submit-form">Submit</button>
              </div>
            </form>
            <div id="ed-show" style="display: none;">
              <?php
                get_user_h($id);
              ?>
            </div>
            <div style="width:100%;text-align:center;">
              <strong><?php
                if(isset($errno_ch)) {
                  foreach ($errno_ch as $errnos) {
                    echo $errnos;
                  }
                }
              ?></strong>
            </div>
            <div id="ac-show" style="display:none;">
              <form method="post" enctype="multipart/form-data" action="">
                <div style="width:100%;"><strong>Change profile picture</strong></div>
                <div>
                  <input type="file" name="photo" maxlength="30" size="30">
                </div>
                <div id="button-photo">
                  <button type="submit" name="cha_photo">Change</button>
                </div>
              </form>
              <form method="post">
                <div style="width:100%;"><strong>Change password</strong></div>
                <div>
                  <input type="text" name="old_pass" maxlength="30" size="30" placeholder="old password" required/>
                </div>
                <div>
                  <input type="text" name="n_pass" maxlength="30" size="30" placeholder="new password" required/>
                </div>
                <div>
                  <input type="text" name="v_pass" maxlength="30" size="30" placeholder="verify password" required/>
                </div>
                <div id="button-password">
                  <button type="submit" name="cha_pass">Change</button>
                </div>
              </form>
              <form method="post" style="margin-left: 50px;">
                <div style="width:100%;"><strong>Change username</strong></div>
                <div>
                  <input type="text" name="use_name" maxlength="30" size="30" placeholder="new username" required>
                </div>
                <div id="button-username">
                  <button type="submit" name="cha_name">Change</button>
                </div>
              </form>';
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  </div>

  <footer class="main-footer-wel">
    <div class="main-footer-contain-wel">
      <div class="main-footer-wrapp-wel">
        <strong>Made by</strong>
        <strong>Kemeridis</strong>
      </div>
    </div>
  </footer>

  <script>
    function accessCookie(cookieName) {
        var name = cookieName + "=";
        var allCookieArray = document.cookie.split(';');
        for(var i=0; i<allCookieArray.length; i++)
        {
          var temp = allCookieArray[i].trim();
          if (temp.indexOf(name)==0)
            return temp.substring(name.length,temp.length);
        }
        return "";
      }
  </script>

  <script>
    function onloadputstyle() {
    var styl = document.getElementById('stylelida').href;
    var styl_split = styl.split("/").pop();
    var value = accessCookie(styl_split);
    console.log(value);
    if (value === 'style.css') {
      document.getElementById('stylelida').href = value;
    } else {
      document.getElementById('stylelida').href = value;
      var allCookieArray = document.cookie.split(';');
    }
  }</script>

  <script>
    function cha_mode(sheet){
      document.getElementById('stylelida').href = sheet;
      var value = accessCookie(sheet);
      if(sheet == 'dark.css') {
        let date = new Date(Date.now() + 86400e3);
        date = date.toUTCString();
        document.cookie = "style.css=;expires=Thu, 01 Jan 1970 00:00:00 UTC;SameSite=None; Secure;";
        document.cookie = "dark.css=dark.css; expires=" + date +";SameSite=None; Secure;";
      } else {
        let date = new Date(Date.now() + 86400e3);
        date = date.toUTCString();
        document.cookie = "dark.css=;expires=Thu, 01 Jan 1970 00:00:00 UTC;SameSite=None; Secure;";
        document.cookie = "style.css=style.css; expires=" + date + ";SameSite=None; Secure;";
      }
    }
  </script>

  <script>
    var oldURL = document.referrer;
    if(oldURL === "http://localhost/project/wel_login.php?edit") {
      var edit = document.getElementById("ed-show");
      var upload = document.getElementById("up-show");
      var account = document.getElementById("ac-show");

      if(edit.style.display === "none") {
        upload.style.display = "none";
        account.style.display = "none";
        edit.style.display = "inline-block";
      }
    } else if(oldURL === "http://localhost/project/wel_login.php?upload") {
      var upload = document.getElementById("up-show");
      var edit = document.getElementById("ed-show");
      var account = document.getElementById("ac-show");

      if(upload.style.display === "none") {
        edit.style.display = "none";
        account.style.display = "none";
        upload.style.display = "flex";
      }
    } else {
      var edit = document.getElementById("ed-show");
      var upload = document.getElementById("up-show");
      var account = document.getElementById("ac-show");

      if(account.style.display === "none") {
        upload.style.display = "none";
        edit.style.display = "none";
        account.style.display = "flex";
      }
    }
  </script>

  <script>
    function up_show() {
      var upload = document.getElementById("up-show");
      var edit = document.getElementById("ed-show");
      var account = document.getElementById("ac-show");

      if(upload.style.display === "none") {
        edit.style.display = "none";
        account.style.display = "none";
        upload.style.display = "flex";
        window.history.pushState(null,null , 'http://localhost/project/wel_login.php?upload');
      }

    }

    function ed_show() {
      var edit = document.getElementById("ed-show");
      var upload = document.getElementById("up-show");
      var account = document.getElementById("ac-show");

      if(edit.style.display === "none") {
        upload.style.display = "none";
        account.style.display = "none";
        edit.style.display = "inline-block";
        window.history.pushState(null,null, 'http://localhost/project/wel_login.php?edit');
      }
    }

    function ac_show() {
      var edit = document.getElementById("ed-show");
      var upload = document.getElementById("up-show");
      var account = document.getElementById("ac-show");

      if(account.style.display === "none") {
        upload.style.display = "none";
        edit.style.display = "none";
        account.style.display = "flex";
        window.history.pushState(null,null, 'http://localhost/project/wel_login.php?account');
      }
    }
  </script>

  <script>
    var success = document.getElementById('suc_msg');
    setTimeout(function(){
      success.style.display = "none";
    }, 5000);
  </script>

  <script>
      var elem = document.getElementById("wel_mes");
      setTimeout(function(){
        elem.style.display = "none";
      }, 7600);
  </script>

  <script>
    function but_prof() {
      var elem = document.getElementById("show-set");
      if(elem.style.display == "none") {
        elem.style.display = "block";
      } else {
        elem.style.display = "none";
      }
    }
  </script>
</body>
</html>
