<?php
/* ①　データベースの接続情報を定数に格納する */
// const DB_HOST = 'mysql:host=mysql573.phy.lolipop.jp;dbname=LAA0200389-ja7v8d;charset=utf8mb4';
// const DB_USER = 'LAA0200389';
// const DB_PASSWORD = 'O3N03ZT2imepXQLD';
const DB_HOST = 'mysql:host=127.0.0.1;dbname=LAA0200389-ja7v8d;charset=utf8mb4';
const DB_USER = 'root';
const DB_PASSWORD = '';

//②　例外処理を使って、DBにPDO接続する
try {
    $pdo = new PDO(DB_HOST,DB_USER,DB_PASSWORD,[
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES =>false
    ]);
} catch (PDOException $e) {
    echo 'ERROR: Could not connect.'.$e->getMessage()."\n";
    exit();
}