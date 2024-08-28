<?php
/*
TODO:

*/

$this_file = __FILE__;
require_once $_SERVER['DOCUMENT_ROOT']."/TSC/php/contest/init.php";
//initialization offers $status, time of $start, $end, $discussion, of this contest.
//raw data of the contest from "TSC_contests" is stored in $contest.
//$status represents, for now(2024/01/14 16:40), 0: upcoming, 1: ongoing, 2: ended, 3: discussion, 4: archived.

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
                <div class="textbox">
                    Good!!!
                </div>
            </div>
        </div>
    </div>
    <footer>
        <?php include($_SERVER['DOCUMENT_ROOT']."/TSC/template/footer.php");?>
    </footer>
  </body>
</html>