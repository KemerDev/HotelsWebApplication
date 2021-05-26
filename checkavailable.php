<?php require_once('dbMaster.php') ?>
<?php
  $db = db_connect();

  if(isset($_POST["uname"])) {
    $username = $_POST["uname"];

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
?>
