<?php
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
    'name'  => '',
    'password'  => '',
    'confirm_password'  => '',
    'twitter_ID' => '',
    'discord_name' => '',
    'discord_number' => '',
    'email' => '',
    'query_str' => ''
];
$login_err = "";

//GET通信だった場合はセッション変数にトークンを追加
if($_SERVER['REQUEST_METHOD'] != 'POST'){
    setToken();
    $query_str = $_SERVER['QUERY_STRING'];
}

//POST通信だった場合はログイン処理を開始
if($_SERVER["REQUEST_METHOD"] == "POST"){
    ////CSRF対策
    checkToken();

    // POSTされてきたデータを変数に格納
    foreach($datas as $key => $value) {
        if($value = filter_input(INPUT_POST, $key, FILTER_DEFAULT)) {
            $datas[$key] = $value;
        }
    }

    parse_str($datas['query_str'], $query);

    // バリデーション
    $errors = validation($datas,false);

    if(empty($errors)){
	//ユーザーネームから該当するユーザー情報を取得
        $sql = "SELECT id,name,password FROM TSC_users WHERE name = :name";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue('name',$datas['name'],PDO::PARAM_INT);
        $stmt->execute();

        //ユーザー情報があれば変数に格納
        if($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            //パスワードがあっているか確認
            if (password_verify($datas['password'],$row['password'])) {
                //セッションIDをふりなおす
                session_regenerate_id(true);
                //セッション変数にログイン情報を格納
                $_SESSION["loggedin"] = true;
                $_SESSION["id"] = $row['id'];
                $_SESSION["name"] =  $row['name'];
                //ウェルカムページへリダイレクト
                $welcome_url = "welcome.php";
       		if(isset($query["transition"]))$welcome_url = urldecode($query["transition"]);
        
        	header("location:{$welcome_url}");
        	exit();
            } else {
                $login_err = '無効な入力';
            }
        }else {
            $login_err = '無効な入力';
        }
    }
}
?>


<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title lang="ja">ログイン  TSC 全合成グランプリ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" href="/TSC/css/common.css" />
    <link rel="shortcut icon" href="/TSC/assets/logo.png">
  </head>
  <body>
    <header>
        <?php include($_SERVER['DOCUMENT_ROOT']."/TSC/template/header.php");?>
    </header>
    <div id="contents-wrapper">
        <div class="center">
            <div id="contents-title"><h2 class="center-title">ログイン</h2></div>
            <form name="register-input" action method="post">
                <div class="form-item flex">
                    <label class="item-label">ユーザー名</label>
                    <div class="item-content">
                        <div class="item-input">
                            <input type="text" pattern="^[a-zA-Z0-9_]{3,16}$" id="input-name" name="name" value="" placeholder="3-16文字, 英数字と_のみ" required>
                        </div>
                        <span class="error"><?=$login_err ?></span>
                    </div>
                </div>
                <div class="form-item flex">
                    <label class="item-label" for="input-password">パスワード</label>
                    <div class="item-content">
                        <div class="item-input">
                            <input type="password" id="input-password" name="password" value="" placeholder="10文字以上" required>
                        </div>
                        <span class="error"></span>
                    </div>
                </div>
                <div class="form-item">
		    <input type="hidden" name="token" value="<?php echo h($_SESSION['token']); ?>">
		    <input type="hidden" name="query_str" value="<?php echo $query_str; ?>">
                    <div class="submit-button">
                        <input type="submit" id="submit" value="ログイン">
                    </div>
                </div>
            </form>
	    <div id="reset_password">
		    <p>パスワードまたはユーザー名をお忘れの方は<a href="password_reset.php">こちら</a>から</p>
	    </div>
        </div>
    </div>
    <footer>
        <?php include($_SERVER['DOCUMENT_ROOT']."/TSC/template/footer.php");?>
    </footer>
    <script src="/TSC/js/common.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
  </body>
</html>
