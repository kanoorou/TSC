<?php
require __DIR__."/../db_connect.php";
require __DIR__."/../functions.php";

require __DIR__."/constant.php";

if(!$this_file)$this_file = __FILE__;

session_start();
$contest_name = basename(dirname($this_file));
$tab_name = pathinfo($this_file, PATHINFO_FILENAME);

$sql = "SELECT * FROM TSC_contests WHERE contest_name = :name";
$stmt = $pdo->prepare($sql);
$stmt->bindValue('name',$contest_name);
$stmt->execute();

if( !($contest = $stmt->fetch(PDO::FETCH_ASSOC)) ){
    echo "error: ",$contest_name," does not exist.";
}

$status = -1;//0:upcoming, 1:ongoing, 2:archived, 3:discussion

$now = time();
$start = strtotime($contest["starts_at"]);
$end = strtotime($contest["ends_at"]);
$discussion = strtotime($contest["discussion_at"]);

if($now <= $start)$status = TSC::STATUS_UPCOMING;
else if($now <= $end)$status = TSC::STATUS_ONGOING;
else if($now <= $discussion)$status = TSC::STATUS_ENDED;
else $status = TSC::STATUS_DISCUSSION;
