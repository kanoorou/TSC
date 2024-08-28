<?php
//ファイルの読み込み
require_once "php/db_connect.php";
require_once "php/functions.php";


//セッションの開始
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}
if(!isset($_SESSION["id"])){
    header("location: login.php");
    exit;
}

//POSTされてきたデータを格納する変数の定義と初期化
$datas = [
    'name'  => '',
    'password'  => '',
    'confirm_password'  => '',
    'twitter_ID' => '',
    'discord_name' => '',
    'discord_number' => '',
    'email' => ''
];

$errors = [];

//GET通信だった場合はセッション変数にトークンを追加
if($_SERVER['REQUEST_METHOD'] != 'POST'){
    setToken();
   
     $sql = "SELECT name,email,twitter_ID,discord_name,discord_number,level FROM TSC_users WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue('id',$_SESSION['id'],PDO::PARAM_INT);
    $stmt->execute();

    if( !($row = $stmt->fetch(PDO::FETCH_ASSOC)) ){
        $errors["id"] = "不正なユーザーです。ログインしなおしてください。";
        header("location:login.php");
        exit;
    }

    $user = [
	'id' => $_SESSION["id"],
	'name'  => $row["name"],
    	'twitter_ID' => $row["twitter_ID"],
    	'discord_name' => $row["discord_name"],
    	'discord_number' => $row["discord_number"],
    	'email' => $row["email"]
    ];
}
//POST通信だった場合はDBへの新規登録処理を開始
if($_SERVER["REQUEST_METHOD"] == "POST"){
    //CSRF対策
    checkToken();

    // POSTされてきたデータを変数に格納
    foreach($datas as $key => $value) {
        if($value = filter_input(INPUT_POST, $key, FILTER_DEFAULT)) {
            $datas[$key] = $value;
        }
    }

    if(empty($datas["confirm_password"])){
	$datas["confirm_password"]=$datas["password"];
    }
    
    // バリデーション
    $errors = validation($datas, false);

    $sql = "SELECT password FROM TSC_users WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue('id',$_SESSION['id'],PDO::PARAM_INT);
    $stmt->execute();
    
    if( !($row = $stmt->fetch(PDO::FETCH_ASSOC)) ){
        $errors["id"] = "不正なユーザーです。ログインしなおしてください。";
        header("location:login.php");
        //exit;
    }
    if( !password_verify($datas['password'],$row['password']) ){
        $errors["password"] = "パスワードが間違っています。";
        //exit;
    }

    $sql = "SELECT id FROM TSC_users WHERE name = :name AND id != :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue('name',$datas['name'],PDO::PARAM_INT);
    $stmt->bindValue('id',$_SESSION['id'],PDO::PARAM_INT);
    $stmt->execute();
    if($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        $errors['name'] = 'このユーザー名は使用済です。';
    }
    $sql = "SELECT id FROM TSC_users WHERE email = :email AND id != :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue('email',$datas['email'],PDO::PARAM_INT);
    $stmt->bindValue('id',$_SESSION['id'],PDO::PARAM_INT);
    $stmt->execute();
    if($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        $errors['email'] = 'このメールアドレスは使用済です。';
    }
    
    //エラーがなかったらDBへの新規登録を実行
    if(empty($errors)){
        $params = [
	        'id'=>$_SESSION['id'],
            'name'=>$datas['name'],
            'password'=>password_hash($datas["confirm_password"], PASSWORD_DEFAULT),
            'twitter_ID' => $datas['twitter_ID'],
    	    'discord_name' => $datas['discord_name'],
   	        'discord_number' => $datas['discord_number'],
    	    'email' => $datas['email'],
	        'uuid' => $datas['uuid']
        ];

        $count = 0;
        $buf = '';
        foreach (array_keys($params) as $key) {
		if($key == "id")continue;
        	if($count > 0)$buf .= ', ';
        	$buf .= $key.' = :'.$key;
        	$count++;
        }

        $pdo->beginTransaction();//トランザクション処理
        try {
            $sql = 'UPDATE TSC_users SET '.$buf .' WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $pdo->commit();

	    $user = [
		'id' => $params["id"],
		'name'  => $params["name"],
    		'twitter_ID' => $params["twitter_ID"],
    		'discord_name' => $params["discord_name"],
    		'discord_number' => $params["discord_number"],
    	   	'email' => $params["email"]
    	   ];
	    $_SESSION["name"] = $params["name"];
        } catch (PDOException $e) {
            echo 'ERROR: Could not register.', $e;
	        $pdo->rollBack();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="UTF-8" />
    <title lang="ja">TSC-Total Synthesis Contest-</title>
    <link rel="stylesheet" href="/TSC/css/common.css" />
    <link rel="stylesheet" href="/TSC/css/mypage.css" />
    <link rel="shortcut icon" href="/TSC/assets/logo.png">
  </head>
  <body>
    <header>
        <?php include($_SERVER['DOCUMENT_ROOT']."/TSC/template/header.php");?>
    </header>
    <div id="contents-wrapper">
	<div class="center">
        <div id="contents-title"><h2 class="center-title"><?= $user["name"]?>のマイページ</h2></div>
        <div id="mypage-menu"><a href="/TSC/logout">ログアウト</a></div>

	<form name="register-input" class="center" action method="post">
                <div class="form-item flex">
                    <label class="item-label" for="input-name">ユーザー名</label>
                    <div class="item-content">
                        <div class="item-input">
                            <input type="text" pattern="^[a-zA-Z0-9_]{3,16}$" id="input-name" name="name" placeholder="3-16文字, 英数字と_のみ" value="<?= $user["name"]?>" required>
                        </div>
                        <span class="error"><?=$errors['name'] ?></span>
                    </div>
                </div>
                <div class="form-item flex">
                    <label class="item-label" for="input-email">メールアドレス</label>
                    <div class="item-content">
                        <div class="item-input">
                            <input type="email" id="input-email" name="email" placeholder="有効なもの" value="<?= $user["email"]?>" required>
                        </div>
                        <span class="error"><?=$errors['email'] ?></span>
                    </div>
                </div>
                <div class="form-item flex">
                    <label class="item-label" for="input-password">パスワード</label>
                    <div class="item-content">
                        <div class="item-input">
                            <input type="password" id="input-password" name="password" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{10,}" placeholder="10文字以上で大,小英字および数字を含む" required>
                        </div>
                        <span class="error"><?=$errors['password'] ?></span>
                    </div>
                </div>
		<div class="form-item flex">
                    <label class="item-label" for="input-password">変更後のパスワード</label>
                    <div class="item-content">
                        <div class="item-input">
                            <input type="password" id="input-confirm-password" name="confirm_password" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{10,}" value="" placeholder="10文字以上で大,小英字および数字を含む">
                        </div>
                        <span class="error"><?=$errors['confirm_password'] ?></span>
                    </div>
                </div>
                <div class="form-item flex">
                    <label class="item-label" for="input-twitter">Twitter ID</label>
                    <div class="item-content">
                        <div class="item-input flex">
                            <span class="addon">@</span>
                            <input type="text" id="input-twitter" name="twitter_ID" value="<?= $user["twitter_ID"]?>">
                        </div>
                        <span class="error"><?=$errors['twitter_ID'] ?></span>
                    </div>
                </div>
                <div class="form-item flex">
                    <label class="item-label" for="input-discord-name">Discord ID</label>
                    <div class="item-content">
                        <div class="item-input flex">
                            <input type="text" id="input-discord-name" name="discord_name" value="<?= $user["discord_name"]?>">
                            <span class="addon">#</span>
                            <input type="text" pattern="^[0-9]{4}" id="input-discord-number" name="discord_number" value="<?= $user["discord_number"]?>" placeholder="数字4桁">
                        </div>
                        <span class="error"><?=$errors['discord_name'], $errors['discord_number'] ?></span>
                    </div>
                </div>
                <div class="form-item">
		    <input type="hidden" name="token" value="<?php echo h($_SESSION['token']); ?>">
                    <div class="submit-button">
                        <input type="submit" id="submit" value="変更" onclick="submit();">
                    </div>
                </div>
            </form>
</div>
    </div>
    <footer>
        <?php include($_SERVER['DOCUMENT_ROOT']."/TSC/template/footer.php");?>
    </footer>
  </body>
</html>