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

$sql = 'SELECT * FROM TSC_contest_type';
$stmt = $pdo->query($sql);
$types = $stmt->fetchAll(PDO::FETCH_ASSOC);

$options_HTML = '';
$nums = [];
foreach ($types as $type) {
    $options_HTML .= "<option value='".$type['name']."'>".$type['name']."</option>\n";
    $nums[] = $type['number'];
}

//POSTされてきたデータを格納する変数の定義と初期化
$datas = [
    'type' => '',
    'number' => '',
    'start' => '',
    'length' => '',
    'discussion_at' => ''
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    foreach ($datas as $key => $value) {
        $datas[$key] = $_POST[$key];
    }

    $type_id = null;
    foreach ($types as $type) {
        if($type["name"] == $datas["type"])$type_id = $type["id"];
    }
    
    $len = strlen($datas['number']);
    if ($len == 1) {
        $datas['number'] = '00'.$datas['number'];
    } elseif ($len == 2) {
        $datas['number'] = '0'.$datas['number'];
    }

    
    $contest_name = $datas['type'].$datas['number'];
    $dir = "{$_SERVER['DOCUMENT_ROOT']}/TSC/contest/problems/{$contest_name}/";
    
    
    $sql = 'SELECT id,password FROM TSC_contests WHERE contest_name = :contest_name';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue('contest_name', $contest_name);
    $stmt->execute();

    $ids = [];
    $password = random_password();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $ids[] = $row['id'];
        $password = $row["password"];
    }
    foreach ($ids as $id) {
        $sql = 'DELETE FROM TSC_contests WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue('id', $id);
        $stmt->execute();
    }
    //エラーがなかったらDBへの新規登録を実行
    if (empty($errors)) {
        $params = [
            'id' => null,
            'posted_at' => null,
            'status' => 0,
            'type' => $type_id,
            'number' => $datas['number'],
            'starts_at' => date("Y-m-d H", strtotime($datas['start'])),
            'length' => $datas['length'],
            'discussion_at' => date("Y-m-d H", strtotime($datas['discussion_at'])),
            'ends_at' => date("Y-m-d H", strtotime("{$datas['start']} + {$datas['length']} hours")),
	        'contest_name' => $datas['type'].$datas['number'],
            'password'=> $password
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

        $pdo->beginTransaction();//トランザクション処理
        try {
            $sql = 'insert into TSC_contests ('.$columns.')values('.$values.')';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $pdo->commit();

            $old_dir = $_SERVER['DOCUMENT_ROOT']."/TSC/contest/problems/Template/";
	          $new_dir = $_SERVER['DOCUMENT_ROOT']."/TSC/contest/problems/{$params["contest_name"]}/";
            copy_dir($old_dir, $new_dir);

            mkdir("{$new_dir}assets/{$password}/", 0755, true);
        } catch (PDOException $e) {
            echo 'ERROR: Could not register.', $e;
            $pdo->rollBack();
            exit;
        }
        $index = array_search($datas['type'], array_column($types, 'name'));
        if ($types[$index]['number'] < $datas['number']) {
            $sql = 'UPDATE TSC_contest_type SET number = :number WHERE name = :name';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue('number', (int)$datas['number']);
            $stmt->bindValue('name', $datas['type']);
            $stmt->execute();
        }
        header("Location: /TSC/contest/admin/add_problem");
    }
    
}
?>


<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="UTF-8" />
    <title lang="ja">TSC-Total Synthesis Contest-</title>
    <link rel="stylesheet" href="/TSC/css/common.css" />
    <link rel="shortcut icon" href="/TSC/assets/logo.png">
  </head>
  <body>
    <header>
        <?php include $_SERVER['DOCUMENT_ROOT'].'/TSC/template/header.php';?>
    </header>
    <div id="contents-wrapper">
        <a href="/TSC/contest/admin/"><h1>ようこそ、ADMIN様</h1></a>
        <div class="center">
            <div id="contents-title"><h2 class="center-title">コンテストの追加</h2></div>
            <form enctype="multipart/form-data" name="problem-input" class="center" action method="post">
                <div class="form-item flex">
                    <label class="item-label" for="input-type">コンテスト名</label>
                    <div class="item-content">
                        <div class="item-input">
                            <select id="input-type" name="type" onchange="reload_number(this);">
			        <?= $options_HTML ?>
			    </select>
                            <input type="number" id="input-number" name="number" value="" required>
                        </div>
                        <span class="error"><?=$errors['type'] ?></span>
                    </div>																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																			
                </div>
                <div class="form-item flex">
                    <label class="item-label" for="input-start">開始日時</label>
                    <div class="item-content">
                        <div class="item-input">
                            <input type="datetime-local" id="input-start" name="start" required>
			                <span class="addon">(分以下切り捨て)</span>
                        </div>
                        <span class="error"><?=$errors['start'] ?></span>
                    </div>
                </div>
                <div class="form-item flex">
                    <label class="item-label" for="input-length">コンテストの長さ</label>
                    <div class="item-content">
                        <div class="item-input">
                            <input type="number" id="input-length" name="length" value=24 required>
			                <span class="addon">時間</span>
                        </div>
                        <span class="error"><?=$errors['length'] ?></span>
                    </div>
                </div>
                <div class="form-item flex">
                    <label class="item-label" for="input-discussion">ディスカッション開始予定時間</label>
                    <div class="item-content">
                        <div class="item-input">
                            <input type="datetime-local" id="input-discussion" name="discussion_at" required>
                        </div>
                        <span class="error"><?=$errors['discussion_at'] ?></span>
                    </div>
                </div>
                <div class="form-item">
		            <input type="hidden" name="token" value="<?php echo h($_SESSION['token']); ?>">
                    <div class="submit-button">
                        <input type="submit" id="submit" value="追加" onclick="submit();">
                    </div>
                </div>
            </form>
        </div>
    </div>
    <footer>
        <?php include($_SERVER['DOCUMENT_ROOT']."/TSC/template/footer.php");?>
    </footer>
  </body>
  <script  type="text/javascript">
    function reload_number(obj){
	let nums = <?=json_encode($nums) ?>;
	let num = nums[obj.selectedIndex];
	document.getElementById("input-number").value = (num-0)+1;
    }
    let obj = document.getElementById("input-type");
    reload_number(obj);
  </script>
</html>