<?php
//TODO: #main-title-button内のリンクを完成させる：discussion.php

$this_file = __FILE__;
require_once $_SERVER['DOCUMENT_ROOT']."/TSC/php/contest/init.php";
//initialization offers $status, time of $start, $end, $discussion, of this contest.
//raw data of the contest from "TSC_contests" is stored in $contest.
//$status represents, for now(2024/01/14 16:40), 0: upcoming, 1: ongoing, 2: ended, 3: discussion, 4: archived.

// Warning: $status is used in raw html; care of the definition.

$sql = "SELECT * FROM TSC_problem_uploads WHERE contest_name = :name";
$stmt = $pdo->prepare($sql);
$stmt->bindValue('name',$contest_name);
$stmt->execute();

$problem_list_html = "";
while($problem = $stmt->fetch(PDO::FETCH_ASSOC)){
    $problem_list_html .= "<li style=\"margin:auto 5px;font-size: 30px;\"><a href=\"./problems.php#{$problem["name"]}\" class=\"available\">{$problem["title"][0]}</a></li>\n";
}
?>

<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="UTF-8" />
    <title lang="ja">TSC-Total Synthesis Contest-</title>
    <link rel="stylesheet" href="/TSC/css/common.css" />
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
                <div id="title" style="margin: 10px auto;width:95%">
                    <div id="main-title" style="background-color: lightgray;">
                    <div id="main-title-upper" class="flex" style="height:140px;width: 100%;margin: auto;display: flex;justify-content: center;align-items: center;padding: 10px;box-sizing: border-box;">
                        <div id="contest-name" class="flex" style="height:120px;display: flex;justify-content: center;align-items: center;padding: 10px;box-sizing: border-box;">
                            <h1 class="contest-type" class="flex" style="font-size:100px;height:120px;"><?=$contest_name?></h1>
                        </div>
                        <ul id="title-diffs" class="flex" style="height:120px;display: flex;justify-content: center;align-items: center;padding: 10px;box-sizing: border-box;">
                            <?=$problem_list_html?>
                        </ul>
                    </div>
                    <div id="main-title-bottom" class="flex" style="height:40px;width: 100%;margin: auto;display: flex;justify-content: center;align-items: center;padding: 10px;box-sizing: border-box;">
                        <ul id="contest-status" class="flex" style="display: flex;justify-content: center;align-items: center;padding: 10px;box-sizing: border-box;">
                            <li style="display: <?=$status==TSC::STATUS_UPCOMING?"flex":"none" ?>;justify-content: space-between;align-items: center;box-sizing: border-box;"><h3>コンテスト開始</h3><h3 style="padding-left:10px;"><?=date("Y/m/d H:i:s", $start), "(JST)";?></h3></li>
                            <li style="display: <?=$status==TSC::STATUS_ONGOING?"flex":"none" ?>;justify-content: space-between;align-items: center;box-sizing: border-box;"><h3>コンテスト開催中</h3><h3 style="padding-left:80px;">終了</h3><h3 style="padding-left:10px;"><?=date("Y/m/d H:i:s", $end), "(JST)";?></h3></li>
                            <li style="display: <?=$status==TSC::STATUS_ENDED?"flex":"none" ?>;justify-content: space-between;align-items: center;box-sizing: border-box;"><h3>終了済み</h3><h3 style="padding-left:80px;">ディスカッション開始</h3><h3 style="padding-left:10px;"><?=date("Y/m/d H:i:s", $discussion), "(JST)-";?></h3></li>
                            <li style="display: <?=$status==TSC::STATUS_DISCUSSION?"flex":"none" ?>;justify-content: space-between;align-items: center;box-sizing: border-box;"><h3>ディスカッション中</h3></li>
                            <li style="display: <?=$status==TSC::STATUS_ARCHIVED?"flex":"none" ?>;justify-content: space-between;align-items: center;box-sizing: border-box;"><h3>終了済み</h3></li>
                        </ul>
                    </div>
                    <div id="main-title-button" class="flex" style="height:40px;width: 100%;margin: auto;display: flex;justify-content: center;align-items: center;padding: 10px;box-sizing: border-box;">
                        <ul id="util-button" class="flex" style="display: flex;justify-content: center;align-items: center;padding: 10px;box-sizing: border-box;">
                            <li style="display: <?=$status==TSC::STATUS_UPCOMING?"flex":"none" ?>;justify-content: space-between;align-items: center;box-sizing: border-box;"><a><h3>通知設定</h3></a></li>
                            <li style="display: <?=$status==TSC::STATUS_ONGOING?"flex":"none" ?>;justify-content: space-between;align-items: center;box-sizing: border-box;"><a href="problems"><h3>コンテストに参加する</h3></a></li>
                            <li style="display: <?=$status==TSC::STATUS_ENDED?"flex":"none" ?>;justify-content: space-between;align-items: center;box-sizing: border-box;"><a><h3>通知設定</h3></a></li>
                            <li style="display: <?=$status==TSC::STATUS_DISCUSSION?"flex":"none" ?>;justify-content: space-between;align-items: center;box-sizing: border-box;"><a href="discussion"><h3>ディスカッションに参加</h3></a></li>
                        </ul>
                    </div>
                    </div>
                    <div id="sub-title">
                    <div id="contest-info">
                        <ul class="flex" style="margin:0px;display: flex;justify-content: left;align-items: center;padding: 0px 10px;box-sizing: border-box;">
                            <li style="padding-right:80px;display: flex;justify-content: space-between;align-items: center;box-sizing: border-box;"><p style="margin:0px;">discussion:</p><p style="margin:0px;padding-left:10px;"><?=$discussion,"(JST)-"?></p></li>
                            <li style="padding-right:80px;display: flex;justify-content: space-between;align-items: center;box-sizing: border-box;"><p style="margin:0px;">rating:</p><p style="margin:0px;padding-left:10px;">True</p></li>
                            <li style="display: flex;justify-content: space-between;align-items: center;box-sizing: border-box;"><p style="margin:0px;">difficulity:</p><p style="margin:0px;padding-left:10px;">100</p></li>
                        </ul>
                    </div>
                </div>
                </div>
                <div class="textbox">
                    <div>
                        <h2>ルール</h2>
                        <ul>
                            <li><p>コンテスト期間中の回答掲載は禁止です。</p></li>
                            <li><div>
                                <h3>スキーム</h3>
                                <ul>
                                    <li><p>Sigma-Aldrich, TCIカタログ掲載の試薬を使用すること。</p></li>
                                </ul>
                            </div></li>
                        </ul>
                    </div>
                    <div>
                        <h2>提出方法</h2>
                        <ul>
                            <li><p>提出タブからどうぞ</p></li>
                        </ul>
                    </div>
                    <div>
                        <h2>コンテスト情報</h2>
                        <ul>
                            <li><p>24時間</p></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <footer>
        <?php include($_SERVER['DOCUMENT_ROOT']."/TSC/template/footer.php");?>
    </footer>
  </body>
</html>