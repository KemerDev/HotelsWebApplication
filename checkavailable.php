<?php require_once('dbMaster.php') ?>
<?php
  $db = db_connect();

  if(isset($_POST["uname"])) {
    $username = strip_tags($_POST["uname"]);

    $response = "";

    $sel_stmt = $db->prepare("SELECT count(*) as c_uname FROM users WHERE username = :uname");
    $sel_stmt->execute(array(':uname' => $username));
    $row = $sel_stmt->fetch(PDO::FETCH_ASSOC);

    $count = $row["c_uname"];

    if($count == 1) {
      $response = "<strong>username taken</strong>";
    } else {
      $response = "<strong>username available</strong>";
    }
    echo $response;
  }

  if(isset($_POST["umail"])) {
    $email = $_POST["umail"];

    $response = "";

    $sel_stmt = $db->prepare("SELECT count(*) as c_umail FROM users WHERE email = :umail");
    $sel_stmt->execute(array(':umail' => $email));
    $row = $sel_stmt->fetch(PDO::FETCH_ASSOC);

    $count = $row["c_umail"];

    if($count == 1) {
      $response = "<strong>email taken</strong>";
    } else {
      $response = "<strong>email available</strong>";
    }
    echo $response;
  }
?>
