<?php

function db_connect() {
  $host = "localhost";
  $user = "root";
  $pass = "";
  $db_name = "useraccounts";

  try {
    $db = new PDO("mysql:host={$host};dbname={$db_name}", $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $db;
  } catch(PDOEXCEPTION $e) {
    $e->getMessage();
  }
}

function create_acc($usr, $pwd, $eml) {

  $db = db_connect();

  if(strlen($pwd) < 8) {
    return $er_msg[] = "Password must be atleast 8 characters long";
  }
  else if(preg_match("~[^a-z0-9_]+~i", $pwd)) {
    return $er_msg[] = "Password must have Uppercase, lowercase, numbers or _";
  }
  else if(!filter_var($eml, FILTER_VALIDATE_EMAIL)) {
    return $er_msg[] = "Email must be valid";
  }
  else {
    try {
      $sel_stmt = $db->prepare("SELECT username, email from users
        WHERE username = :usname OR email = :email");
      $sel_stmt->execute(array(':usname' => $usr,
                         ':email' => $eml));
      $row = $sel_stmt->fetch(PDO::FETCH_ASSOC);

      if(strcmp($row["username"], $usr) == 0) {
        return $er_msg[] = "Username already taken";
      }
      else if(strcmp($row["email"], $eml) == 0) {
        return $er_msg[] = "Email already exists";
      }
      else if(!isset($er_msg)) {
        $hash_pass = hash('sha512', $pwd);

        $verify_hash = md5(rand(0,2500));

        $img_url = 'media\default.png';
        $de_img = file_get_contents($img_url);

        $ins_stmt = $db->prepare("INSERT INTO users (username, password, email, ver_hash, img) VALUES
                                      (:usname, :pass, :email, :verhash, :img)");
        if($ins_stmt->execute(array(':usname' => $usr,
                                 ':pass' => $hash_pass,
                                 ':email' => $eml,
                                 ':verhash' => $verify_hash,
                                 ':img' => $de_img))) {

          send_mail($eml, $verify_hash);

          return header("Location: create_verify.php");
        }
      }
    }
  catch(PDOEXCEPTION $e) {
    return $e -> getMessage();
    }
  }
  $db -> close();
}

function send_mail($eml, $verify_hash) {

  $subject = "Signup Verification";

  $message ="
  Thanks for signing up to web hotels!
  Your account has been created and you will be able to login after you have activated your account!

  Please click to this link to activate your account:
  http://localhost/project/create_verify.php?hash=$verify_hash
  ";
  $headers = "From: pteste644@gmail.com";

  if(mail($eml, $subject, $message, $headers)) {
    echo "<script>console.log('email send');</script>";
  } else {
    echo "<script>console.log('email not send');</script>";
  }
}

function verify_acc($ver_hash) {
  $db = db_connect();
  $stat = "active";

  try {
    $sel_stmt = $db->prepare("SELECT * FROM users WHERE ver_hash = :verhash");
    $sel_stmt->execute(array(':verhash' => $ver_hash));
    $row = $sel_stmt->fetch(PDO::FETCH_ASSOC);

    if(strcmp($ver_hash, $row["ver_hash"]) == 0) {
      $ins_stmt = $db->prepare("UPDATE users SET status=:status, ver_hash= 'NULL' WHERE ver_hash= :verhash");
      $ins_stmt->execute(array(':status' => $stat, ':verhash' => $ver_hash ));
      return true;
    } else {
      echo "error activating the account";
    }
  }
  catch(PDOEXCEPTION $e) {
    $e -> getMessage();
  }
  $db -> close();

}

function login_acc($username, $password) {

  $db = db_connect();
  $inactive = "inactive";

  try {
    $sel_stmt = $db->prepare("SELECT * FROM users WHERE username = :uname");
    $sel_stmt->execute(array(':uname' => $username));
    $row = $sel_stmt->fetch(PDO::FETCH_ASSOC);

    $hate = hash('sha512', $password);

    if($sel_stmt->rowCount() > 0) {
      if(strcmp($username, $row["username"]) == 0){
        if(hash_equals($hate, $row["password"])) {
          if(strcmp($inactive, $row["status"]) == 0) {
            return $er_msg[] = "You must activate your account to login";
          } else {
            $_SESSION["ulogin"] = $row["user_id"];
            header("refresh:2; wel_login.php");
          }
        } else {
          return $er_msg[] = "Wrong username or password";
        }
      } else {
        return $er_msg[] = "Wrong username or password";
      }
    }
  }
  catch(PDOEXCEPTION $e) {
    return $e -> getMessage();
  }
}

function logout() {
  setcookie($_SESSION["logout"], '', 100);
  session_unset();
  session_destroy();
  header("location: index.php");
}

function captcha_Val() {

  if(!empty($_POST['g-recaptcha-response']))
  {
        $secret = '6LfrFbwaAAAAAA7pza1jMI1ge1s5GaPw4oFzqZL0';
        $verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$secret.'&response='.$_POST['g-recaptcha-response']);
        $responseData = json_decode($verifyResponse);
        if($responseData->success)
            return true;
        else
            return false;
   }
 }

function get_img($id) {
  $db = db_connect();

  $sel_stmt = $db->prepare("SELECT * FROM users WHERE user_id = :id");
  $sel_stmt->execute(array(':id' => $id));
  $row = $sel_stmt->fetch(PDO::FETCH_ASSOC);

  if($row["img"] === '') {
    $img_url = 'media\default.png';
    $de_img = file_get_contents($img_url);
    echo base64_encode($de_img);
  } else {
    echo base64_encode($row["img"]);
  }
}

function ins_hotel($name, $country, $city, $address, $phone, $rooms, $descri, $id) {
  $db = db_connect();

  $ins_stmt = $db->prepare("INSERT INTO userhotels (user_id,name, country, city, address, phone, rooms, descr) VALUES
                                (:id, :name, :country, :city, :address, :phone, :rooms, :descri)");

  $ins_stmt->execute(array(':id' => $id,
                           ':name' => $name,
                           ':country' => $country,
                           ':city' => $city,
                           ':address' => $address,
                           ':phone' => $phone,
                           ':rooms' => $rooms,
                           ':descri' => $descri));
}

function uphotel_img($f_name, $f_size, $f_type, $f_temp, $h_name) {
  $db = db_connect();

  $sel_stmt = $db->prepare("SELECT * FROM userhotels WHERE name = :name");
  $sel_stmt->execute(array(':name' => $h_name));
  $row = $sel_stmt->fetch(PDO::FETCH_ASSOC);

  for($i = 0; $i < count($f_name); $i++) {
    if($f_size[$i] < 307200){
      $up_temp = 'image_temp/'.basename($f_name[$i]);

      $ins_stmt = $db->prepare("INSERT into hotels_photos (h_id, p_name, size, type)
                                                          VALUES (:id, :name, :size, :type)");
      $ins_stmt->execute(array(':id' => $row["h_id"],
                                ':name' => $f_name[$i],
                                ':size' => $f_size[$i],
                                ':type' => $f_type[$i]));
      move_uploaded_file($f_temp[$i], $up_temp);
      header('location: http://localhost/project/wel_login.php?edit');

      } else {
          echo "photo must be smaller than 5mb";
      }
    }
  }

function get_user_h($id) {
  $db = db_connect();

  $sel_stmt = $db->prepare("SELECT * FROM userhotels WHERE user_id = :id");
  $sel_stmt->execute(array(':id' => $id));

  $full_stmt = $db->prepare("SELECT * FROM hotels_photos LEFT JOIN userhotels ON hotels_photos.h_id = userhotels.h_id");
  $full_stmt->execute();

  $photos = array();

  while ($full_row = $full_stmt->fetch(PDO::FETCH_ASSOC)) {
    $photos[] = $full_row;
  }

  while ($row = $sel_stmt->fetch(PDO::FETCH_ASSOC)) {
    echo '<div style="margin-bottom: 10px;text-align: center;"><strong>Edit Hotel: '.$row["name"].'</strong></div>';
    echo '<div style="display:flex;border:solid;border-width:thin;text-align:center">';
    foreach ($photos as $photo){
      if($row["h_id"] === $photo["h_id"]) {
        echo '<div style="flex-wrap:wrap;justify-content:center">';
          echo '<div>';
            echo '<strong>'.pathinfo($photo["p_name"], PATHINFO_FILENAME).'</strong>';
            echo '<form id="'.$photo["id"].'" method="post" style="margin-bottom:0;margin-left:20px;padding:0;display: inline;">';
              echo '<button type="submit" name="'.$photo["id"].'" title="delete '.$photo["p_name"].'?" style="padding:0;background:none;border:none;cursor: pointer;"><img src="media/del_x.png" style="width:20;height:20;"/></button>';
            echo '</form>';
          echo '</div>';
          echo '<img src="image_temp/'.$photo["p_name"].'"style="margin:12px 0 12px;width:125;height:75;"/>';
        echo '</div>';
      }
    }
    echo '</div>';
    echo '<div style="margin-bottom:20px;width:100%;border:solid;border-width:thin;box-shadow: 5px 10px 8px #888888;display: flex;">';
    echo '<div>';
      echo '<div>';
        echo '<form enctype="multipart/form-data" method="post">';
          echo '<strong>Upload photos</strong>';
          echo '<input type="file" name="n_photos[]" style="margin:10px 0 10px;" multiple>';
          echo '<button type="submit"
           name="upload_'.$row["h_id"].'" >Upload Photos</button>';
        echo '</form>';
      echo '</div>';
    echo '</div>';
        echo '<form method="post" style="margin-top:6px;margin-left:860px;padding:0;position:absolute;">';
          echo '<button type="submit" "value="ok" name="delete/'.$row["h_id"].'" title="Delete '.$row["name"].'?" style="margin-left:10px;padding:0;background:none;border:none;cursor: pointer;"><img src="media/del_x.png" style="width:20;height:20;"></button>';
        echo '</form>';
        echo  '<form method="post" style="margin:0;flex-wrap: wrap; justify-content: left;">';
          echo    '<input style="margin-top:10px;margin-left:10px;padding:0;" type="text" name="edit_name" maxlength="50" size="30" placeholder="Name:'.$row["name"].'">';
          echo    '<input style="margin-top:10px;margin-left:10px;padding:0;" type="text" name="edit_country" maxlength="50" size="30" placeholder="Country:'.$row["country"].'">';
          echo    '<input style="margin-top:10px;margin-left:10px;padding:0;" type="text" name="edit_city" maxlength="50" size="25" placeholder=City:'.$row["city"].'>';
          echo    '<input style="margin-top:10px;margin-left:10px;padding:0;" type="text" name="edit_address" maxlength="50" size="30" placeholder="Address:'.$row["address"].'">';
          echo    '<input style="margin-top:10px;margin-left:10px;padding:0;" type="text" name="edit_phone" maxlength="50" size="30" placeholder="Phone:'.$row["phone"].'">';
          echo    '<input style="margin-top:10px;margin-left:10px;padding:0;" type="text" name="edit_rooms" maxlength="50" size="30" placeholder="Rooms:'.$row["rooms"].'">';
          echo    '<textarea style="margin-top:10px;margin-left:10px;padding:0;width:90%;resize: none;" name="edit_descri" cols="50" rows="10" placeholder="Description:'.$row["descr"].'"></textarea>';
          echo    '<button style="margin-left:10px" type="submit" enctype="multipart/form-data" name="update/'.$row["h_id"].'">Update Information</button>';
        echo  '</form>';
    echo '</div>';
  }
}

function edit_hotel($name, $country, $city, $address, $phone, $rooms, $descri, $hotel_id) {

  $db = db_connect();

  $sel_stmt = $db->prepare("SELECT * FROM userhotels WHERE h_id = :id");
  $sel_stmt->execute(array(':id' => $hotel_id));
  $row = $sel_stmt->fetch(PDO::FETCH_ASSOC);

  if($name == ""){
    $name = $row["name"];
  }

  if ($country == "") {
    $country = $row["country"];
  }

  if ($city == "") {
    $city = $row["city"];
  }

  if ($address == "") {
    $address = $row["address"];
  }

  if ($phone == "") {
    $phone = $row["phone"];
  }

  if ($rooms == "") {
    $rooms = $row["rooms"];
  }

  if ($descri == "") {
    $descri = $row["descr"];
  }

  try {
    $up_stmt = $db->prepare("UPDATE userhotels SET name = :name, country = :country, city = :city, address = :address, phone = :phone, rooms = :rooms, descr = :descr WHERE h_id = :id");
    $up_stmt->execute(array(':name' => $name,
                            ':country' => $country,
                            ':city' => $city,
                            ':address' => $address,
                            ':phone' => $phone,
                            ':rooms' => $rooms,
                            ':descr' => $descri,
                            ':id' => $hotel_id));
    }
    catch (Exception $e) {
      echo 'Caught exception: ',  $e->getMessage(), "\n";
    }
}

function search($city) {

  $db = db_connect();

  $limit = 4;
  $page = $_GET['page'];
  $start = ($page) * $limit;
  $previous = $page - 1;
  $next = $page + 1;

  $sel_stmt = $db->prepare("SELECT h_id, name, country, city, address, phone, rooms, descr FROM userhotels WHERE city = :city LIMIT $start, $limit");
  $sel_stmt->execute(array(':city' => $city));

  $count_stmt = $db->prepare("SELECT city FROM userhotels WHERE city = :city");
  $count_stmt->execute(array(':city' => $city));

  $total = 0;
  while($c_row = $count_stmt->fetch(PDO::FETCH_ASSOC)) {
    $total += 1;
  }

  $full_stmt = $db->prepare("SELECT * FROM hotels_photos LEFT JOIN userhotels ON hotels_photos.h_id = userhotels.h_id");
  $full_stmt->execute();

  $photos = array();

  $hotel_count = 0;
  while ($full_row = $full_stmt->fetch(PDO::FETCH_ASSOC)) {
    $photos[] = $full_row;
  }

  while($row = $sel_stmt->fetch(PDO::FETCH_ASSOC)) {
    echo '<div class="main-search-results" style="margin:50px 400px 0;display:flex;border-radius:10px;border:solid;border-width:thin;background:white;box-shadow: 5px 10px 8px #888888;">';
      echo '<div style="width:20%;border-right:solid;border-width:thin;">';
        foreach ($photos as $photo){
          if($row["h_id"] === $photo["h_id"]) {
            echo '<button onclick="show_info('.$row["h_id"].')" style="margin:0;padding:0;background:none;border:none;cursor:pointer;"><img src="image_temp/'.$photo["p_name"].'"style="max-width:100%"/></button>';
              break;
            }
          }
    echo '</div>';
    echo '<div style="margin-left:10px;width:30%;">';
      echo '<div style="width:100%;height:100%;">';
      echo '<div style="margin-bottom:15px;font-size:15pt;">';
        echo '<strong>'.$row["name"].'</strong>';
      echo '</div>';
      echo '<div style="margin-bottom:15px;">';
        echo '<strong>Country: '.$row["country"].'</strong>';
      echo '</div>';
      echo '<div style="margin-bottom:15px;">';
        echo '<strong>City: '.$row["city"].'</strong>';
      echo '</div>';
      echo '<div style="margin-bottom:15px;">';
        echo '<strong>Address: '.$row["address"].'</strong>';
      echo '</div>';
      echo '<div style="margin-bottom:15px;">';
        echo '<strong>Phone: '.$row["phone"].'</strong>';
      echo '</div>';
      echo '</div>';
    echo '</div>';
    echo '<div style="width:50%;height:100%;">';
    echo '<div style="text-align:center;">';
      echo '<strong>Description</strong>';
    echo '</div>';
      echo '<div>';
        echo ''.$row["descr"].'';
        echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '<div id="more-'.$row["h_id"].'" class="photo_btn_div" style="margin:5px 400px 0;display:none;border-radius:10px;border:solid;border-width:thin;background:white;box-shadow: 5px 10px 8px #888888;transform: scale(0);transition: .6s ease opacity,.6s ease transform;">';
    foreach ($photos as $photo){
      if($row["h_id"] === $photo["h_id"]) {
        echo '<button type="button" style="margin:5px;padding:0;background:none;border:none;width:125px;cursor:pointer;"><img id="img_'.$photo["id"].'" class="re_img" src="image_temp/'.$photo["p_name"].'"style="width:100%;"/></button>';
        }
      }
    echo '</div>';
  }
  echo '<div style="margin-top:20px;display:inline-block;width:100%;text-align:center;">';
      echo '<a href="search_result.php?s_city=athens&page='.$previous.'" style="margin:0 10px 0;width:20px;height:20px;text-decoration:none;color:black;"><< Previous</a>';

      echo '<a href="search_result.php?s_city=athens&page='.$page.'" style="margin:0 10px 0;width:20px;height:20px;text-decoration:none;color:black;">'.$page.'</a>';

      echo '<a href="search_result.php?s_city=athens&page='.$next.'" style="margin:0 10px 0;width:20px;height:20px;text-decoration:none;color:black;">Next >></a>';
  echo '</div>';


}
