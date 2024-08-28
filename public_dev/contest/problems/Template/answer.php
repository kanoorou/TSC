<?php
/*
TODO:解答表示以降

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
        $level=0;
    }
    $level = (int) $user['level'] ;
}
$isguest = ($level == TSC::LEVEL_GUEST);


if($level < TSC::LEVEL_STAFF && $status < TSC::STATUS_ENDED){
    header("Location:/TSC/contest/problems/{$contest_name}/");
    exit();
}

$sql = "SELECT * FROM TSC_contest_answers WHERE contest_name = :name";
$stmt = $pdo->prepare($sql);
$stmt->bindValue('name',$contest_name);
$stmt->execute();

$count = 0;
$answers_htmls = [];
while($answer = $stmt->fetch(PDO::FETCH_ASSOC)){
    ++$count;
    if(!isset($answers_htmls[$answer["problem_name"]]))$answers_htmls[$answer["problem_name"]] = "";
    $answers_htmls[$answer["problem_name"]] .= 
    "<div class=\"hide-toggle\" style=\"width:100%\">
        <input type=\"checkbox\" id=\"label_ans{$count}\" style=\"width:100%\"/>
        <label for=\"label_ans{$count}\" style=\"width:100%;display:block;padding: 0;height:20px;\">クリック</label>
        <div class=\"hidden-by-checked\">
            <img src=\"/TSC/contest/problems/{$contest_name}/assets/{$contest["answer_password"]}/{$answer["file_name"]}\"  style=\"width:100%;\">
        </div>
    </div>";
}

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

$sql = "SELECT * FROM TSC_problem_uploads WHERE contest_name = :name";
$stmt = $pdo->prepare($sql);
$stmt->bindValue('name',$contest_name);
$stmt->execute();

$contents_html = "";
$problem_names = [];
while($problem = $stmt->fetch(PDO::FETCH_ASSOC)){
    if(!in_array($problem["name"], $problem_names, false))array_push($problem_names, $problem["name"]);
    $contents_html .= 
    "               <div id=\"{$problem["name"]}\" style=\"margin: 10px auto;padding-top: 90px;margin-top:-90px;\">
                        <div>
                            <h2 style=\"width: 100%;text-align: center;\">{$problem["title"]}</h2>
                            <div style=\"width: 100%;text-align: center;\">
                                {$problem["rule"]}
                                <img src=\"/TSC/contest/problems/{$problem["contest_name"]}/assets/{$contest["password"]}/{$problem["file_name"]}\" style=\"width:25%\">
                            </div>
                        </div>
                        <div>
                            <h2 style=\"width: 100%;text-align: center;\">模範解答</h2>
                            {$answers_htmls[$problem["name"]]}
                        </div>";

    if(!$isguest)$contents_html .=
    "			<div style=\"width: 100%;text-align: center;\">
                            <p>{$user["name"]}の提出</p>
                            <ul style=\"margin: 10px auto;width:80%;padding:0;\">
                                {$my_submit_htmls[$problem["name"]]}
                            </ul>
                        </div>";
    $contents_html .= "</div>";
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