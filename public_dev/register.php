<?php
//ファイルの読み込み
require_once "php/db_connect.php";
require_once "php/functions.php";


//セッションの開始
session_start();

//POSTされてきたデータを格納する変数の定義と初期化
$datas = [
    'name'  => '',
    'password'  => '',
    'confirm_password'  => '',
    'twitter_ID' => '',
    'discord_name' => '',
    'discord_number' => '',
    'email' => '',
    'level' => '1',
    'uuid' => '',
    'query_str' => ''
];

//GET通信だった場合はセッション変数にトークンを追加
if($_SERVER['REQUEST_METHOD'] != 'POST'){
    setToken();
    $query_str = $_SERVER['QUERY_STRING'];
}
//POST通信だった場合はDBへの新規登録処理を開始
if($_SERVER["REQUEST_METHOD"] == "POST"){
    //CSRF対策
    checkToken();

    $uuid = '';
    do{
	$uuid = generate_UUID();
	$sql = "SELECT id FROM TSC_users WHERE uuid = :uuid";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue('uuid',$datas['uuid'],PDO::PARAM_INT);
        $stmt->execute();
    }while($row = $stmt->fetch(PDO::FETCH_ASSOC));
    $datas['uuid'] = $uuid;

    // POSTされてきたデータを変数に格納
    foreach($datas as $key => $value) {
        if($value = filter_input(INPUT_POST, $key, FILTER_DEFAULT)) {
            $datas[$key] = $value;
        }
    }

    // バリデーション
    $errors = validation($datas);

    //データベースの中に同一ユーザー名が存在していないか確認
    if(empty($errors['name'])){
        $sql = "SELECT id FROM TSC_users WHERE name = :name";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue('name',$datas['name'],PDO::PARAM_INT);
        $stmt->execute();
        if($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            $errors['name'] = 'このユーザー名は使用済です。';
        }
    }
    if(empty($errors['email'])){
        $sql = "SELECT id FROM TSC_users WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue('email',$datas['email'],PDO::PARAM_INT);
        $stmt->execute();
        if($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            $errors['email'] = 'このメールアドレスは使用済です。';
        }
    }
    //エラーがなかったらDBへの新規登録を実行
    if(empty($errors)){
        $params = [
            'id' =>null,
            'name'=>$datas['name'],
            'password'=>password_hash($datas['password'], PASSWORD_DEFAULT),
            'created_at'=>null,
    	    'twitter_ID' => $datas['twitter_ID'],
    	    'discord_name' => $datas['discord_name'],
   	    'discord_number' => $datas['discord_number'],
    	    'email' => $datas['email'],
	    'level' => $datas['level'],
    	    'uuid' => $datas['uuid']
        ];

        $count = 0;
        $columns = '';
        $values = '';
        foreach (array_keys($params) as $key) {
            if($count > 0){
                $columns .= ',';
                $values .= ',';
            }
            $columns .= $key;
            $values .= ':'.$key;
            $count++;
        }

        $pdo->beginTransaction();//トランザクション処理
        try {
            $sql = 'insert into TSC_users ('.$columns .')values('.$values.')';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $pdo->commit();
            

	    $subject = "VERIFY";
	    $message = "TSCに本登録: https://e-na.space/TSC/verify?uuid={$datas['uuid']}&{$datas['query_str']}";
	    $headers = "From:totalsynthesiscontest@gmail.com";
	    mb_language("Japanese");
	    mb_internal_encoding("UTF-8");
	    mb_send_mail($datas['email'], $subject, $message, $headers); 

	    header("location: verify.php");

            exit;
        } catch (PDOException $e) {
            echo 'ERROR: Could not register.', $e;
	    $pdo->rollBack();
        }
    }
}

?>


<!DOCTYPE html>
<html lang="ja">
<meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title lang="ja">コンテスト  TSC 全合成グランプリ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" href="/TSC/css/common.css" />
    <link rel="shortcut icon" href="/TSC/assets/logo.png">
  <body>
    <header>
        <?php include($_SERVER['DOCUMENT_ROOT']."/TSC/template/header.php");?>
    </header>
    <div id="contents-wrapper">
        <div class="center">
            <div id="contents-title"><h2 class="center-title">TSCアカウントの新規作成</h2></div>
            <form name="register-input" class="center" action method="post">
                <div class="form-item flex">
                    <label class="item-label" for="input-name">ユーザー名</label>
                    <div class="item-content">
                        <div class="item-input">
                            <input type="text" pattern="^[a-zA-Z0-9_]{3,16}$" id="input-name" name="name" placeholder="3-16文字, 英数字と_のみ" required>
                        </div>
                        <span class="error"><?=$errors['name'] ?></span>
                    </div>
                </div>
                <div class="form-item flex">
                    <label class="item-label" for="input-email">メールアドレス</label>
                    <div class="item-content">
                        <div class="item-input">
                            <input type="email" id="input-email" name="email" placeholder="有効なもの" required>
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
                    <label class="item-label" for="input-password">パスワードの確認</label>
                    <div class="item-content">
                        <div class="item-input">
                            <input type="password" id="input-confirm-password" name="confirm_password" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{10,}" value="" placeholder="10文字以上で大,小英字および数字を含む" required>
                        </div>
                        <span class="error"><?=$errors['confirm_password'] ?></span>
                    </div>
                </div>
                <div class="form-item flex">
                    <label class="item-label" for="input-twitter">Twitter ID</label>
                    <div class="item-content">
                        <div class="item-input flex">
                            <span class="addon">@</span>
                            <input type="text" id="input-twitter" name="twitter_ID" value="">
                        </div>
                        <span class="error"><?=$errors['twitter_ID'] ?></span>
                    </div>
                </div>
                <div class="form-item flex">
                    <label class="item-label" for="input-discord-name">Discord ID</label>
                    <div class="item-content">
                        <div class="item-input flex">
                            <input type="text" id="input-discord-name" name="discord_name" value="">
                            <span class="addon">#</span>
                            <input type="text" pattern="^[0-9]{4}" id="input-discord-number" name="discord_number" value="" placeholder="数字4桁">
                        </div>
                        <span class="error"><?=$errors['discord_name'], $errors['discord_number'] ?></span>
                    </div>
                </div>
                <div class="form-item">
		    <input type="hidden" name="token" value="<?php echo h($_SESSION['token']); ?>">
		    <input type="hidden" name="query_str" value="<?php echo $query_str; ?>">
                    <div class="submit-button">
                        <input type="submit" id="submit" value="新規登録" onclick="submit();">
                    </div>
                </div>
            </form>
        </div>
    </div>
    <footer>
        <?php include($_SERVER['DOCUMENT_ROOT']."/TSC/template/footer.php");?>
    </footer>
    <script src="/TSC/js/common.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
  </body>
</html>
