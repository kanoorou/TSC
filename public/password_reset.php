<?php
$duration = 600;//seconds
$now = time();

//ファイルの読み込み
require_once "php/db_connect.php";
require_once "php/functions.php";
//セッション開始
session_start();

// セッション変数 $_SESSION["loggedin"]を確認。ログイン済だったらウェルカムページへリダイレクト
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: welcome.php");
    exit;
}

//POSTされてきたデータを格納する変数の定義と初期化
$datas = [
    'email' => '',
    'query_str' => ''
];
$login_err = "";

//GET通信だった場合はセッション変数にトークンを追加
if($_SERVER['REQUEST_METHOD'] != 'POST'){
    setToken();
    $query_str = $_SERVER['QUERY_STRING'];
}

if($_SERVER["REQUEST_METHOD"] == "POST"){
    ////CSRF対策
    checkToken();

    // POSTされてきたデータを変数に格納
    foreach($datas as $key => $value) {
        if($value = filter_input(INPUT_POST, $key, FILTER_DEFAULT)) {
            $datas[$key] = $value;
        }
    }

    var_dump($datas);
    var_dump($_POST);
    var_dump($errors);

    if(empty($errors)){
	$sql = "SELECT uuid,email FROM TSC_users WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue('email',$datas['email']);
        $stmt->execute();

        //ユーザー情報があれば変数に格納
	$success = false;
	if($user = $stmt->fetch(PDO::FETCH_ASSOC)){
	    $new_table = [
	        "user_uuid" => $user["uuid"],
		"reset_token" => generate_UUID(),
	        "exp_time" => $now + $duration
	    ];

    	    $sql = "SELECT * FROM TSC_password_reset_token";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            //$table = $stmt->fetch(PDO::FETCH_ASSOC);

	    $filter = function ($item) {
	        if($item["user_uuid"] == $user["uuid"])return false;
		if($item["exp_time"] < $now)return false;
		return true;
	    }
	    
            //$table = array_filter($table,$filter);
	    

            
	    
	    $success = true;
	}else {
            $login_err = '存在しないメールアドレスです';
        }
    }
}
?>


<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="UTF-8" />
    <title lang="ja">TSC-Total Synthesis Contest-</title>
    <link rel="stylesheet" href="css/common.css" />
    <link rel="stylesheet" href="css/login.css" />
    <link rel="shortcut icon" href="assets/logo.png">
  </head>
  <body>
    <header>
        <?php include($_SERVER['DOCUMENT_ROOT']."/TSC/template/header.php");?>
    </header>
    <div id="contents-wrapper">
        <div class="center">
            <div id="contents-title"><h2 class="center-title">パスワードリセット</h2></div>
            <form name="register-input" action method="post">
                <div class="form-item flex">
                    <label class="item-label" for="input-email">メールアドレス</label>
                    <div class="item-content">
                        <div class="item-input">
                            <input type="text" id="input-email" name="email" value="" required>
                        </div>
                        <span class="error"><?=$login_err?></span>
                    </div>
                </div>
                <div class="form-item">
		    <input type="hidden" name="token" value="<?php echo h($_SESSION['token']); ?>">
		    <input type="hidden" name="query_str" value="<?php echo $query_str; ?>">
                    <div class="submit-button">
                        <input type="submit" id="submit" value="再設定">
                    </div>
		    <span class="error"><?php if($success == true) echo "メールを送信しました。"; ?></span>
                </div>
            </form>
        </div>
    </div>
    <footer>
        <?php include($_SERVER['DOCUMENT_ROOT']."/TSC/template/footer.php");?>
    </footer>
  </body>
</html>