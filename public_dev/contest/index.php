<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT']."/TSC/php/db_connect.php";
require_once $_SERVER['DOCUMENT_ROOT']."/TSC/php/functions.php";

$sql = "SELECT level FROM TSC_users WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindValue('id',$_SESSION['id'],PDO::PARAM_INT);
$stmt->execute();

if( !($row = $stmt->fetch(PDO::FETCH_ASSOC)) )$level = 0;
else $level = (int)$row["level"];

$contests = [[],[],[]];
$title = ["開催中","開催予定","開催済み"];

$sql = "SELECT * FROM TSC_contests";
$stmt = $pdo->prepare($sql);
$stmt->execute();
if($list = $stmt->fetchAll(PDO::FETCH_ASSOC)){
    foreach($list as $row){
	$status = -1;//0:upcoming, 1:ongoing, 2:archived

	$now = time();
	$start = strtotime($row["starts_at"]);
	$end = strtotime($row["ends_at"]);

	if($now <= $start)$status = 0;
	else if($now <= $end)$status = 1;
	else $status = 2;

	if($status < 0 || $status > 2)continue;
	else if($status == 0)$index = 1;
	else if($status == 1)$index = 0;
	else $index = 2;

	$contests[$index][]=$row;
    }
}

$php = "<ul id='contests-wrapper'>\n";
$count = 0;

foreach($contests as $x){
    $php .= "    <li class='contests-list'>".$title[$count]."\n\t<ul id='contests".$count."'>\n";
    foreach($x as $y){
        $php .= "\t    <li class='contest'>\n";
        $php .= "\t\t<a href='/TSC/contest/problems/".$y["contest_name"]."'>".$y["contest_name"].": ";
	if($count == 0)$php .= $y["ends_at"]."終了</a>\n";
	else $php .= $y["starts_at"]."開始</a>\n";
        $php .= "\t    </li>\n";
    }
    $php .= "\t</ul>\n    </li>\n";
    $count ++;
}

$php .= "</ul>";
?>

<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title lang="ja">コンテスト  TSC 全合成グランプリ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" href="/TSC/css/common.css" />
    <link rel="shortcut icon" href="/TSC/assets/logo.png">
  </head>
  <body>
    <header>
        <?php include($_SERVER['DOCUMENT_ROOT']."/TSC/template/header.php");?>
    </header>
    <div id="contents-wrapper">
        <h1>コンテストってなに？</h1>
	<?= $php ?>
	<p><a href="/TSC/contest/staff/">STAFFの方はここをクリック</a></p>
	<p><a href="/TSC/contest/admin/">ADMINの方はここをクリック</a></p>
    </div>
    <footer>
        <?php include($_SERVER['DOCUMENT_ROOT']."/TSC/template/footer.php");?>
    </footer>
    <script src="/TSC/js/common.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
  </body>
</html>