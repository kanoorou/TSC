<?php
//XSS対策
function h($s){
    return htmlspecialchars($s, ENT_QUOTES, "UTF-8");
}

function generate_UUID(){
    return preg_replace_callback(
        '/x|y/',
        function($m) {
            return dechex($m[0] === 'x' ? random_int(0, 15) : random_int(8, 11));
        },
        'xxxxx_xxxxxxxxx_xxxxxxx'
   );
}

//セッションにトークンセット
function setToken(){
    $token = sha1(uniqid(mt_rand(), true));
    $_SESSION['token'] = $token;
}

//セッション変数のトークンとPOSTされたトークンをチェック
function checkToken(){
    if(empty($_SESSION['token']) || ($_SESSION['token'] != $_POST['token'])){
        echo 'Invalid POST', PHP_EOL;
        exit;
    }
}

//POSTされた値のバリデーション
function validation($datas,$confirm = true)
{
    $errors = [];

    //ユーザー名のチェック
    if(empty($datas['name'])) {
        $errors['name'] = 'Please enter username.';
    }else if(mb_strlen($datas['name']) > 20) {
        $errors['name'] = 'Please enter up to 20 characters.';
    }

    //パスワードのチェック（正規表現）
    if(empty($datas["password"])){
        $errors['password']  = "Please enter a password.";
    }
    //パスワード入力確認チェック（ユーザー新規登録時のみ使用）
    if($confirm){
        if(empty($datas["confirm_password"])){
            $errors['confirm_password']  = "Please confirm password.";
        }else if(empty($errors['password']) && ($datas["password"] != $datas["confirm_password"])){
            $errors['confirm_password'] = "パスワードが一致しません。";
        }
    }

    return $errors;
}

function console_log($data){
  echo '<script>';
  echo 'console.log('.json_encode($data).')';
  echo '</script>';
}

function random_password($length = 16, $candidates = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"){
    $password = "";
    for($i = 0; $i < $length; ++$i){
        $password .= $candidates[random_int(0, strlen($candidates)-1)];
    }
    return $password;
}

function copy_dir($dir, $new_dir){
    $dir     = rtrim($dir, '/').'/';
    $new_dir = rtrim($new_dir, '/').'/';

    // コピー元ディレクトリが存在すればコピーを行う
    if (is_dir($dir)) {
        // コピー先ディレクトリが存在しなければ作成する
        if (!is_dir($new_dir)) {
            mkdir($new_dir, 0755);
            chmod($new_dir, 0755);
        }

        // ディレクトリを開く
        if ($handle = opendir($dir)) {
            // ディレクトリ内のファイルを取得する
            while (false !== ($file = readdir($handle))) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                // 下の階層にディレクトリが存在する場合は再帰処理を行う
                if (is_dir($dir.$file)) {
                    copy_dir($dir.$file, $new_dir.$file);
                } else {
                    copy($dir.$file, $new_dir.$file);
                }
            }
            closedir($handle);
        }
    }
}

function remove_directory($dir) {
    $files = array_diff(scandir($dir), array('.','..'));
    foreach ($files as $file) {
        // ファイルかディレクトリによって処理を分ける
        if (is_dir("$dir/$file")) {
            // ディレクトリなら再度同じ関数を呼び出す
            remove_directory("$dir/$file");
        } else {
            // ファイルなら削除
            unlink("$dir/$file");
        }
    }
    // 指定したディレクトリを削除
    return rmdir($dir);
}

function sort_by_keyval($arr, $key){
    $index_with_val = [];
    foreach ($arr as $k => $v) {
        $index_with_val[$v[$key]] = $k;
    }

    ksort($index_with_val);

    $res = [];
    foreach($index_with_val as $k){
        $res[$k] = $arr[$k];
    }

    return $res;
}