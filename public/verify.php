<?php
require_once "php/db_connect.php";
require_once "php/functions.php";

session_start();

if($uuid = $_GET["uuid"]){
if(!preg_match('/^[a-f0-9_]{23,23}$/i',$uuid))$error = '有効でないリンクです。登録をやり直してください。';
else{
    $sql = "SELECT id,name FROM TSC_users WHERE uuid = :uuid";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue('uuid',$uuid,);
    $stmt->execute();
    if(!$row = $stmt->fetch(PDO::FETCH_ASSOC)){
        $error =  '無効なリンクです。登録をやり直してください。';
    }
    else {
        $sql = "UPDATE TSC_users SET level = 2 WHERE uuid = :uuid";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue('uuid',$uuid);
        $stmt->execute();
        header("location: welcome.php");

        //セッションIDをふりなおす
        session_regenerate_id(true);
        //セッション変数にログイン情報を格納
        $_SESSION["loggedin"] = true;
        $_SESSION["id"] = $row['id'];
        $_SESSION["name"] =  $row['name'];
        //ウェルカムページへリダイレクト

        $welcome_url = "welcome.php";
        if(isset($_GET["transition"]))$welcome_url = urldecode($_GET["transition"]);
        
        header("location:{$welcome_url}");
        exit();
    }
}
}else $error = "";
?>

<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="UTF-8" />
    <title lang="ja">TSC-Total Synthesis Contest-</title>
    <link rel="stylesheet" href="css/common.css" />
    <link rel="shortcut icon" href="assets/logo.png">
  </head>
  <body>
    <header>
        <?php include($_SERVER['DOCUMENT_ROOT']."/TSC/template/header.php");?>
    </header>
    <div id="contents-wrapper">
        <h1>sumotech.re@gmail.comから届くVERIFYというメールにあるリンク先にアクセスしてください。</h1>
	<p>メールが届かない場合は迷惑メールボックスを確認してください。</p>
	<p><?= $error ?></p>
    </div>
    <footer>
        <?php include($_SERVER['DOCUMENT_ROOT']."/TSC/template/footer.php");?>
    </footer>
  </body>
</html>