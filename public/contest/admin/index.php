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

$sql = 'SELECT level FROM TSC_users WHERE id = :id';
$stmt = $pdo->prepare($sql);
$stmt->bindValue('id', $_SESSION['id'], PDO::PARAM_INT);
$stmt->execute();

if (!($row = $stmt->fetch(PDO::FETCH_ASSOC))) {
    header('location:/TSC/login');
    exit;
}

if ((int) $row['level'] < 4) {
    header('location:/TSC/login');
    exit;
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $delcs = [];
    $delps = [];
    $dir = $_SERVER['DOCUMENT_ROOT']."/TSC/contest/problems/";

    foreach($_POST as $key => $value) {
        if($value != 'on') continue;
        $keys = explode('_separate_', $key);
        var_dump($keys);
        echo "\n";
        if($key[3] == 'c') {$delcs[] = $keys[1];}
        if($key[3] == 'p') {$delps[$keys[1]] = $keys[2];}
    }

    try{
    foreach($delps as $c => $p) {
        echo($c.' from '.$p."\n");
        $sql = 'DELETE FROM TSC_problem_uploads WHERE contest_name = :c AND name = :p';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue('c', $c);
        $stmt->bindValue('p', $p);
        $stmt->execute();
        $sql = 'DELETE FROM TSC_submits WHERE contest_name = :c AND submit_name = :p';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue('c', $c);
        $stmt->bindValue('p', $p);
        $stmt->execute();
    }
    foreach($delcs as $c) {
        echo($c."\n");
        $sql = 'DELETE FROM TSC_problem_uploads WHERE contest_name = :c';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue('c', $c);
        $stmt->execute();
        $sql = 'DELETE FROM TSC_submits WHERE contest_name = :c';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue('c', $c);
        $stmt->execute();
        $sql = 'DELETE FROM TSC_contests WHERE contest_name = :c';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue('c', $c);
        $stmt->execute();

        remove_directory($dir.$c);
    }
    } catch(PDOException $e) {echo $e;}
}


$dashboard = [];

$sql = 'SELECT *,TSC_submits.id FROM TSC_submits LEFT OUTER JOIN TSC_users ON TSC_submits.user_uuid=TSC_users.uuid';
$stmt = $pdo->query($sql);
$submits = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sql = 'SELECT  title,file_name,name,contest_name FROM TSC_problem_uploads';
$stmt = $pdo->query($sql);
$problems = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sql = 'SELECT type,contest_name,starts_at,ends_at,discussion_at,status FROM TSC_contests';
$stmt = $pdo->query($sql);
$contests = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sql = 'SELECT id,name FROM TSC_contest_type';
$stmt = $pdo->query($sql);
$types = $stmt->fetchAll(PDO::FETCH_ASSOC);

$problem_resolved = [];
foreach ($problems as $problem){
    if(!isset($problem_resolved[$problem["contest_name"]]))$problem_resolved[$problem["contest_name"]] = [];
    $problem_resolved[$problem["contest_name"]][$problem["name"]] = array("name" => $problem["name"], "title" => $problem["title"], "file_name" => $problem["file_name"], "users" => []);
}
foreach ($submits as $submit){
    $prob = &$problem_resolved[$submit["contest_name"]][$submit["submit_name"]]["users"];
    if(!isset($prob[$submit["name"]]))$prob[$submit["name"]] = array("name" => $submit["name"], "uuid" => $submit["uuid"], "level" => $submit["level"], "rate" => $submit["rate"], "submits" => []);
    $prob[$submit["name"]]["submits"][] = array("user_name" => $submit["user_name"], "file_name" => $submit["file_name"], "submitted_at" => $submit["submitted_at"], "status" => $submit["status"], "id" => $submit["id"]);
}
$typeid2name = [];
foreach ($types as $type) {
    $typeid2name[$type["id"]] = $type["name"];
	$dashboard[$type["name"]] = array("name" => $type["name"], "contests" => []);
}
foreach ($contests as $contest) {
    $dashboard[$typeid2name[$contest["type"]]]["contests"][$contest["contest_name"]] = array("name" => $contest["contest_name"], "start" => $contest["starts_at"], "end" => $contest["ends_at"], "discussion" => $contest["discussion_at"], "status" => $contest["status"], "problems" => $problem_resolved[$contest["contest_name"]]);
}

$html = "<ul style=\"margin-right:20px;\">";
foreach ($dashboard as $t) {
    $html .= "<li><div class=\"flex\" style=\"justify-content:space-between;\"><p>{$t["name"]}</p></div><ul>";
    foreach ($t["contests"] as $c) {
        $html .= "<li><div class=\"flex\" style=\"justify-content:space-between;\"><p>{$c["status"]}</p><p>{$c["name"]}</p><p>{$c["start"]}~{$c["end"]}</p><p>discussion:{$c["discussion"]}</p><p><input type=\"checkbox\" id=\"delc_separate_{$c["name"]}\" name=\"delc_separate_{$c["name"]}\" /><label for=\"delc_separate_{$c["name"]}\">削除</label></p></div><ul>";
        foreach ($c["problems"] as $p) {
            $html .= "<li><div class=\"flex\" style=\"justify-content:space-between;\"><p>{$p["title"]}(id: {$p["name"]})</p><p>target:{$p["file_name"]}</p><p><input type=\"checkbox\" id=\"delp_separate_{$c["name"]}_separate_{$p["name"]}\" name=\"delp_separate_{$c["name"]}_separate_{$p["name"]}\" /><label for=\"delp_separate_{$c["name"]}_separate_{$p["name"]}\">削除</label></p></div><ul>";
            foreach ($p["users"] as $u) {
                $html .= "<li><div class=\"flex\" style=\"justify-content:space-between;\"><p>{$u["name"]}</p><p>uuid: {$u["uuid"]}</p><p>level: {$u["level"]}</p><p>rate: {$u["rate"]}</p></div><ul>";
                foreach ($u["submits"] as $s) {
                    $html .= "<li><a href=\"..\staff\scoring?submit_id={$s["id"]}\"><div class=\"flex\" style=\"justify-content:space-between;\"><p>{$s["status"]}</p><p>{$s["user_name"]}</p><p>{$s["file_name"]}</p><p>{$s["submitted_at"]}</p></div></a></li>";
                }
                $html .= "</ul></li>";
            }
            $html .= "</ul></li>";
        }
        $html .= "</ul></li>";
    }
    $html .= "</ul></li>";
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
	<a href="/TSC/contest/admin/"><h1>ようこそ、ADMIN様</h1></a>
	<a href="/TSC/contest/admin/create">コンテストの新規作成</a>
        <a href="/TSC/contest/admin/add_problem">問題の新規作成</a>
	<a href="/TSC/contest/admin/add_answer">解答の新規追加</a>
	<a href="/TSC/contest/staff/score">採点する</a>
	<div>
        <form method="POST">
            <?=$html?>
            <input type="submit" value="削除する" />
        </form>
	</div>
    </div>
    <footer>
        <?php include($_SERVER['DOCUMENT_ROOT']."/TSC/template/footer.php");?>
    </footer>
  </body>
</html>