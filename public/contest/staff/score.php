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

if ((int) $row['level'] < 3) {
    header('location:/TSC/login');
    exit;
}

$sql = 'SELECT * FROM TSC_submits WHERE status BETWEEN 0 AND 9';
$stmt = $pdo->query($sql);
$submits = $stmt->fetchAll(PDO::FETCH_ASSOC);

$submits_resolved = [];
foreach ($submits as $row) {
    if(!isset($submits_resolved[$row["status"]]))$submits_resolved[$row["status"]] = [];
    $submits_resolved[$row["status"]][] = $row;
}

$options_html = "<ul>";
foreach ($submits_resolved as $s) {
    $row = sort_by_keyval($s, "submitted_at");
    $options_html .= "<li><ul>";
    foreach ($row as $row) {
        $options_html .= "<li><a href=\"scoring?submit_id={$row["id"]}\"><div class=\"flex scorable\" style=\"padding-right:50px;justify-content:space-between;\"><p>Status: {$row["status"]}</p><p>Contest: {$row["contest_name"]}</p><p>Problem: {$row["submit_name"]}</p><p>User: {$row["user_name"]}</p><p>Time: {$row["submitted_at"]}</p></div></a></li>";
    }
    $options_html .= "</ul></li>";
}
$options_html .= "</ul>";
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
    <link rel="shortcut icon" href="/TSC/assets/logo.png">
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
            <div>
                <?=$options_html?>
            </div>
        </div>
    </div>
    <footer>
        <?php include($_SERVER['DOCUMENT_ROOT']."/TSC/template/footer.php"); ?>
    </footer>
</body>
</html>