<?php
/*
TODO:すべて

*/

$this_file = __FILE__;
require_once $_SERVER['DOCUMENT_ROOT']."/TSC/php/contest/init.php";
//initialization offers $status, time of $start, $end, $discussion, of this contest.
//raw data of the contest from "TSC_contests" is stored in $contest.
//$status represents, for now(2024/01/14 16:40), 0: upcoming, 1: ongoing, 2: ended, 3: discussion, 4: archived.


// セッション変数 $_SESSION["loggedin"]を確認。
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['id'])) $level=0;
else{
    $sql = 'SELECT * FROM TSC_users WHERE id = :id';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue('id', $_SESSION['id'], PDO::PARAM_INT);
    $stmt->execute();

    if (!($user = $stmt->fetch(PDO::FETCH_ASSOC))) {
        $level=TSC::LEVEL_GUEST;
    }
    $level = (int) $user['level'] ;
}
$isguest = ($level == TSC::LEVEL_GUEST);


if(!$isguest){
$uuid = $user['uuid'];

$sql = "SELECT * FROM TSC_submits WHERE contest_name = :name AND user_uuid = :uuid";
$stmt = $pdo->prepare($sql);
$stmt->bindValue('name',$contest_name);
$stmt->bindValue('uuid',$uuid);
$stmt->execute();

$count = 0;
$my_submit_counts = [];
$my_submit_htmls = [];
while($my_submit = $stmt->fetch(PDO::FETCH_ASSOC)){
    $current_submit = $my_submit;
    $count ++;
    if(!isset($my_submit_counts[$my_submit["submit_name"]]))$my_submit_counts[$my_submit["submit_name"]] = 0;
    $my_submit_counts[$my_submit["submit_name"]] ++;
    if(!isset($my_submit_htmls[$my_submit["submit_name"]]))$my_submit_htmls[$my_submit["submit_name"]] = "";
    $my_submit_htmls[$my_submit["submit_name"]] .= 
    "<li style=\"border-style: solid;\">
        <ul class=\"flex\" style=\"justify-content: space-between;padding:0;\">
            <li><p>{$my_submit_counts[$my_submit["submit_name"]]}</p></li>
            <li><p>{$my_submit["submitted_at"]}</p></li>
            <li><p>{$my_submit["file_name"]}</p></li>
            <li><p>{$my_submit["user_name"]}</p></li>
            <li><p>{$my_submit["status"]}</p></li>
        </ul>
        <div class=\"hide-toggle\" style=\"width:100%\">
            <input type=\"checkbox\" id=\"label{$count}\" style=\"width:100%\"/>
            <label for=\"label{$count}\" style=\"width:100%;display:block;padding: 0;height:20px;\">クリック</label>
            <div class=\"hidden-by-checked\">
                <img src=\"/TSC/contest/problems/{$my_submit["contest_name"]}/users/{$my_submit["user_uuid"]}/temp/{$my_submit["password"]}/{$my_submit["file_name"]}\"  style=\"width:100%;\">
            </div>
        </div>
    </li>";
}
}



if(!$isguest){
//POSTされてきたデータを格納する変数の定義と初期化
$data = [
    'submit_name' => '',
    'user_name' => ''
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    foreach ($data as $key => $value) {
        $data[$key] = h($_POST[$key]);
    }
    $submit_password = $current_submit?$current_submit["password"]:random_password();

    $dir = $_SERVER['DOCUMENT_ROOT']."/TSC/contest/problems/{$contest_name}/users/{$user["uuid"]}/temp/{$submit_password}/";

    //$my_submitはid順に取り出されるので現在id最大値のはず
    $id = $current_submit["id"]+1;

    //エラーがなかったらDBへの新規登録を実行
    if (isset($_FILES['upload_'.$data["submit_name"]])) {
        if ($_FILES['upload_'.$data["submit_name"]]['error'] == UPLOAD_ERR_OK) {
            $tmp_name = $_FILES['upload_'.$data["submit_name"]]['tmp_name'];
            $file_name = basename( $_FILES['upload_'.$data["submit_name"]]['name']);
        } else {
		    echo "Upload error";
            exit;
        }
    } else {
	    echo "Empty file";
        exit;
    }

    $raw = pathinfo($file_name, PATHINFO_FILENAME);
    $ext = pathinfo($file_name, PATHINFO_EXTENSION);

    $copy_count = 0;
    while(file_exists($dir.$file_name)){
        $copy_count ++;
        $file_name = "{$raw}_copy{$copy_count}.{$ext}";
    }

    $file_path=$dir.$file_name;
    

    $params = [
        //'id' => $id,
        'contest_name' => $contest_name,
        'submit_name' => $data['submit_name'],
        'user_uuid' => $user["uuid"],
        'user_name' => $data['user_name'],
        'password' => $submit_password,
        'file_name' => $file_name,
        'status' => 0
    ];

    $count = 0;
    $columns = '';
    $values = '';
    foreach (array_keys($params) as $key) {
        if ($count > 0) {
            $columns .= ',';
            $values .= ',';
        }
        $columns .= $key;
        $values .= ':'.$key;
        ++$count;
    }

    $pdo->beginTransaction();//トランザクション処理
    try {
        $sql = 'insert into TSC_submits ('.$columns.')values('.$values.')';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        if(!is_dir($dir))mkdir($dir);
        
        if(!move_uploaded_file($tmp_name, $file_path)){
            throw new Exception( "file move error:from {$tmp_name} to {$file_path}");
        }
        
        $pdo->commit();
        header("Location:/TSC/contest/problems/{$contest_name}/problems.php");
	    exit();
    } catch (PDOException $e) {
        echo 'ERROR: Could not register.', $e;
        $pdo->rollBack();
        exit;
    } catch (Exception $e) {
        echo 'ERROR: Could not process.', $e;
        $pdo->rollBack();
        exit;
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
    <link rel="stylesheet" href="/TSC/css/util.css" />
    <link rel="stylesheet" href="/TSC/css/contest/common.css" />
    <link rel="shortcut icon" href="/TSC/assets/logo.png">
    <script>
        function previewFile(hoge, id){
            var fileData = new FileReader();
            fileData.onload = (function() {
                document.getElementById(id).src = fileData.result;
            });
            fileData.readAsDataURL(hoge.files[0]);
        }
    </script>
  </head>
  <body>
    <header style="position:relative;">
        <?php include($_SERVER['DOCUMENT_ROOT']."/TSC/template/header.php");?>
    </header>
    <div id="contents-wrapper" style="width: 95%;margin:0 auto;">
        <?php include($_SERVER['DOCUMENT_ROOT']."/TSC/template/contest/title.php");?>
        <div id="contest-main" style="margin:0 auto;border-style: solid;">
            <?php include($_SERVER['DOCUMENT_ROOT']."/TSC/template/contest/tab.php");?>
            <div id="contest-content" style="margin: 0 auto;">
                <div class="textbox" style="margin: 0px auto;width:80%;padding-top: 90px;">
                    <div style="margin: 0px auto;text-align:center;display:<?=$isguest ? "" : "none"?>;">
                        <h1>注意:ゲストモード</h1>
                        <p>コンテストの参加にはログインが必要です。ゲストモードでは問題の閲覧のみが可能です。
                        <a href="/TSC/login?transition=<?=urlencode("/TSC/contest/problems/{$contest_name}/problems")?>"><span>ログインはこちらから</span></a>
                        または
                        <a href="/TSC/register?transition=<?=urlencode("/TSC/contest/problems/{$contest_name}/problems")?>"><span>新規登録はこちらから</span></a>
			            </p>
                    </div>
                    <?=$contents_html?>
                </div>
            </div>
        </div>
    </div>
    <footer>
        <?php include($_SERVER['DOCUMENT_ROOT']."/TSC/template/footer.php");?>
    </footer>
  </body>
</html>