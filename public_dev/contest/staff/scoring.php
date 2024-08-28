<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/TSC/php/db_connect.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/TSC/php/functions.php';

session_start();
// セッション変数 $_SESSION["loggedin"]を確認。未ログインだったらログインページへリダイレクト
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('location: /TSC/login');
    exit;
}
// セッション変数 $_SESSION["id"]を確認。未ログインだったらログインページへリダイレクト
if (!isset($_SESSION['id'])) {
    header('location: /TSC/login');
    exit;
}

$sql = 'SELECT level,uuid FROM TSC_users WHERE id = :id';
$stmt = $pdo->prepare($sql);
$stmt->bindValue('id', $_SESSION['id'], PDO::PARAM_INT);
$stmt->execute();

if (!($row = $stmt->fetch(PDO::FETCH_ASSOC))) {
    header('location:/TSC/login');
    exit;
}

if ((int) $row['level'] < 3) {
    header('location:/TSC/login');
    exit;
}



if(!isset($_GET["submit_id"])){
    if(!isset($_POST["sid"])){
	var_dump($_POST);
	$sid= 7;
        //header('location:/TSC/contest/staff/score');
        //exit;
    }
    else $sid = $_POST["sid"];
}
else $sid = $_GET["submit_id"];

$sql = 'SELECT * FROM TSC_submits WHERE id = :id';
$stmt = $pdo->prepare($sql);
$stmt->bindValue('id', $sid, PDO::PARAM_INT);
$stmt->execute();

if (!($s = $stmt->fetch(PDO::FETCH_ASSOC))) {
    header('location:/TSC/contest/staff?status=1');
    exit;
}

$sql = 'SELECT * FROM TSC_problem_uploads WHERE contest_name=:c AND name=:n';
$stmt = $pdo->prepare($sql);
$stmt->bindValue('c', $s["contest_name"]);
$stmt->bindValue('n', $s["submit_name"]);
$stmt->execute();

if (!($p = $stmt->fetch(PDO::FETCH_ASSOC))) {
    header('location:/TSC/contest/staff');
    exit;
}

$sql = 'SELECT * FROM TSC_contests WHERE contest_name=:c';
$stmt = $pdo->prepare($sql);
$stmt->bindValue('c', $s["contest_name"]);
$stmt->execute();

if (!($c = $stmt->fetch(PDO::FETCH_ASSOC))) {
    header('location:/TSC/contest/staff');
    exit;
}

$sql = 'SELECT * FROM TSC_contest_answers WHERE contest_name=:c AND problem_name=:n';
$stmt = $pdo->prepare($sql);
$stmt->bindValue('c', $s["contest_name"]);
$stmt->bindValue('n', $s["submit_name"]);
$stmt->execute();

if (!($a = $stmt->fetch(PDO::FETCH_ASSOC))) {
    header('location:/TSC/contest/staff');
    exit;
}

$sql = 'SELECT * FROM TSC_submit_check WHERE submit_id=:sid';
$stmt = $pdo->prepare($sql);
$stmt->bindValue('sid', $sid);
$stmt->execute();
$scs = $stmt->fetchAll();

if (!isset($scs)){
    $npass = random_password();
    $number = 0;
}
else if(count($scs) == 0){
    $npass = random_password();
    $number = 0;
} else {
    $scs = sort_by_keyval($scs, 'number');
    $number = count($scs);
    $npass = $scs[0]["password"];
}



//s: submit, p: problem, c: contest, a: answer, sc: submit_check

$cn = $s["contest_name"];	
$pn = $s["problem_name"];
$cpass = $c["password"];
$apass = $c["answer_password"];
$upass = $s["password"];
$puuid = $s["user_uuid"];
$suuid = $row["uuid"];

$count = 0;
$HTML = "<ul><li>回答:<img src=\"/TSC/contest/problems/{$cn}/users/{$puuid}/temp/{$upass}/{$s["file_name"]}\"  style=\"width:100%;\"></li>";
$HTML .= "<li>問題:<img src=\"/TSC/contest/problems/{$cn}/assets/{$cpass}/{$p["file_name"]}\"  style=\"width:100%;\"></li>";
$HTML .= "<li>解答:<img src=\"/TSC/contest/problems/{$cn}/assets/{$apass}/{$a["file_name"]}\"  style=\"width:100%;\"></li>";
foreach($scs as $sc){
    $count ++;
    $HTML .= "<li>第{$count}添削by{$sc["scorer_uuid"]}:<textarea name=\"c_c_{$count}\">{$sc["content"]}</textarea>";
    if($sc["file_name"] != null) $HTML .= "<img src=\"/TSC/contest/problems/{$cn}/users/{$puuid}/corr/{$npass}/{$sc["file_name"]}\"  style=\"width:50%;\">";
    if($suuid==$sc["scorer_uuid"])$HTML .= "<input type=\"submit\" id=\"c_s_{$count}\" name=\"c_s_{$count}\" value=\"変更\" onclick=\"submit2();\">";
    $HTML .= "</li>";
}
$HTML .= "</ul>";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //$sid = 0;
    if($sid == $_POST["sid"]){
        $comment = $_POST["comment"];

        $dir = "/TSC/contest/problems/{$cn}/users/{$puuid}/corr/{$npass}/";
        if (isset($_FILES['img'])) {
            if ($_FILES['img']['error'] == UPLOAD_ERR_OK) {
                $tmp_name = $_FILES['img']['tmp_name'];
                $file_name = basename($_FILES['img']['name']);
		$no_img = false;
            } else if ($_FILES['img']['error'] == UPLOAD_ERR_NO_FILE){
		$no_img = true;
	    } else {
                echo $_FILES['img']['error'];
                exit;
            }
        } else {
            $no_img = true;
        }
    	
	$file_name=null;
	if(!$no_img){
        $raw = pathinfo($file_name, PATHINFO_FILENAME);
        $ext = pathinfo($file_name, PATHINFO_EXTENSION);
    
        $copy_count = 0;
        while (file_exists($dir.$file_name)) {
            $copy_count ++;
            $file_name = "{$raw}_copy{$copy_count}.{$ext}";
        }
    
        $file_path = $dir.$file_name;
	}

        $params = [
            'id' => null,
            'submit_id' => $sid,
            'scorer_uuid' => $suuid,
            'number' => $number,
            'content' => $comment,
            'file_name' => $file_name,
            'password' => $npass
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

        $pdo->beginTransaction(); //トランザクション処理
        try {
            $sql = 'insert into TSC_submit_check ('.$columns.')values('.$values.')';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $pdo->commit();

	    if(!$no_img){
            if (!is_dir($dir))mkdir($dir, 0755, true);

            if (!move_uploaded_file($tmp_name, $file_path)) {
                throw new Exception("file move error:from {$tmp_name} to {$file_path}");
            }
	    }

	    header("Location: " . $_SERVER['PHP_SELF']);
	    exit;
        } catch (Exception $e) {
            echo 'ERROR: Could not register.',
            $e;
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
    <link rel="stylesheet" href="/TSC/css/common.css" />
    <link rel="stylesheet" href="/TSC/css/util.css" />
    <link rel="stylesheet" href="/TSC/css/contest/common.css" />
    <link rel="shortcut icon" href="/TSC/assets/logo.png" />
    <script>
        function previewFile(hoge, id) {
            var fileData = new FileReader();
            fileData.onload = (function() {
                document.getElementById(id).src = fileData.result;
            });
            fileData.readAsDataURL(hoge.files[0]);
        }
    </script>
</head>
<body>
    <header>
        <?php include $_SERVER['DOCUMENT_ROOT'].'/TSC/template/header.php'; ?>
    </header>
    <div id="contents-wrapper">
        <a href="/TSC/contest/admin/"><h1>ようこそ、STAFF様</h1></a>
        <div class="center">
            <div id="contents-title">
                <h2 class="center-title">採点を行う</h2>
            </div>
<form enctype="multipart/form-data" name="problem-input" class="center" action method="post">
            <div>
                <?=$HTML?>
            </div>
            <div>
                <div class="form-item flex">
                    <label class="item-label" for="comment">コメント</label>
                    <div class="item-content">
                        <div class="item-input">
                            <textarea id="comment" name="comment"></textarea>
                        </div>
                    </div>
                </div>
                <div class="form-item flex">
                    <div class="item-content">
                        <div class="item-input">
                            <input type="file" name="img" onchange="previewFile(this, 'preview');">
                            <p>プレビュー</p>
                            <img id="preview" style="width:60%;">
                        </div>
                    </div>
                </div>
                <div class="form-item">
                    <input type="text" name="sid" id="sid" value="<?=$sid?>">
                    <div class="submit-button">
                        <input type="submit" id="submit" value="追加" onclick="submit();">
                    </div>
                </div>
            </div>
</form>
        </div>
    </div>
    <footer>
        <?php include($_SERVER['DOCUMENT_ROOT']."/TSC/template/footer.php"); ?>
    </footer>
</body>
</html>