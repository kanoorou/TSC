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

$sql = 'SELECT * FROM TSC_contests';
$stmt = $pdo->query($sql);
$contests = $stmt->fetchAll(PDO::FETCH_ASSOC);

$options_HTML = '';
foreach ($contests as $contest) {
    $options_HTML .= "<option value='".$contest['contest_name']."'>".$contest['contest_name']."</option>\n";
}

//POSTされてきたデータを格納する変数の定義と初期化
$data = [
    'contest_name' => '',
    'title' => '',
    'rule' => '',
    'name' => ''
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    foreach ($data as $key => $value) {
        $data[$key] = $_POST[$key];
    }

    $contest_name = $data['contest_name'];

    $sql = 'SELECT * FROM TSC_contests WHERE contest_name = :contest_name';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue('contest_name', $contest_name);
    $stmt->execute();

    if (!($contest = $stmt->fetch(PDO::FETCH_ASSOC))) {
        header("Location:/TSC/contest/admin/create");
        exit;
    }

    $password = $contest["password"];
    $dir = "{$_SERVER['DOCUMENT_ROOT']}/TSC/contest/problems/{$contest_name}/assets/{$password}/";

    $sql = 'SELECT id FROM TSC_problem_uploads WHERE contest_name = :contest_name AND name=:name';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue('contest_name', $contest_name);
    $stmt->bindValue('name', $daya["name"]);
    $stmt->execute();

    $ids = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $ids[] = $row['id'];
    }
    foreach ($ids as $id) {
        $sql = 'DELETE FROM TSC_problem_uploads WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue('id', $id);
        $stmt->execute();
    }

    if (isset($_FILES['target'])) {
        if ($_FILES['target']['error'] == UPLOAD_ERR_OK) {
            $tmp_name = $_FILES['target']['tmp_name'];
            $file_name = basename($_FILES['target']['name']);
        } else {
            echo "Upload error";
            exit;
        }
    } else {
        echo "Empty file";
        exit;
    }

    $raw = pathinfo($file_name, PATHINFO_FILENAME);
    $ext = pathinfo($file_name, PATHINFO_EXTENSION);

    $copy_count = 0;
    while (file_exists($dir.$file_name)) {
        $copy_count ++;
        $file_name = "{$raw}_copy{$copy_count}.{$ext}";
    }

    $file_path = $dir.$file_name;
    //エラーがなかったらDBへの新規登録を実行
    if (empty($errors)) {
        $params = [
            'id' => null,
            'contest_name' => $data["contest_name"],
            'title' => $data["title"],
            'rule' => $data["rule"],
            'name' => $data["name"],
            'status' => 0,
            'file_name' => $file_name
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


        $pdo->beginTransaction(); //トランザクション処理
        try {
            $sql = 'insert into TSC_problem_uploads ('.$columns.')values('.$values.')';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $pdo->commit();

            if (!is_dir($dir))mkdir($dir, 0755, true);

            if (!move_uploaded_file($tmp_name, $file_path)) {
                throw new Exception("file move error:from {$tmp_name} to {$file_path}");
            }
        } catch (PDOException $e) {
            echo 'ERROR: Could not register.',
            $e;
            $pdo->rollBack();
            exit;
        }

    }

}
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
    <script>
        function previewFile(hoge, id) {
            var fileData = new FileReader();
            fileData.onload = (function() {
                document.getElementById(id).src = fileData.result;
            });
            fileData.readAsDataURL(hoge.files[0]);
        }
    </script>
</head>
<body>
    <header>
        <?php include $_SERVER['DOCUMENT_ROOT'].'/TSC/template/header.php'; ?>
    </header>
    <div id="contents-wrapper">
        <a href="/TSC/contest/admin/"><h1>ようこそ、ADMIN様</h1></a>
        <div class="center">
            <div id="contents-title">
                <h2 class="center-title">problemの追加</h2>
            </div>
            <form enctype="multipart/form-data" name="problem-input" class="center" action method="post">
                <div class="form-item flex">
                    <label class="item-label" for="input-type">コンテスト名</label>
                    <div class="item-content">
                        <div class="item-input">
                            <select id="input-type" name="contest_name">
                                <?= $options_HTML ?>
                            </select>
                        </div>
                        <span class="error"><?=$errors['contest_name'] ?></span>
                    </div>
                </div>
                <div class="form-item flex">
                    <label class="item-label" for="input-start">title</label>
                    <div class="item-content">
                        <div class="item-input">
                            <input type="text" id="input-start" name="title" required>
                        </div>
                        <span class="error"><?=$errors['title'] ?></span>
                    </div>
                </div>
                <div class="form-item flex">
                    <label class="item-label" for="input-length">id</label>
                    <div class="item-content">
                        <div class="item-input">
                            <input type="text" id="input-length" name="name" required>
                        </div>
                        <span class="error"><?=$errors['name'] ?></span>
                    </div>
                </div>
                <div class="form-item flex">
                    <label class="item-label" for="input-discussion">rule</label>
                    <div class="item-content">
                        <div class="item-input">
                            <textarea id="input-discussion" name="rule" style="height:200px;width:100%;" required>
<p>以下の化合物の合成経路を提案しなさい。<br>ただし、試薬の炭素数は6以下である必要があります。立体化学は問いません。</p> 
			    </textarea>
                        </div>
                        <span class="error"><?=$errors['rule'] ?></span>
                    </div>
                </div>
                <div class="form-item flex">
                    <div class="item-content">
                        <div class="item-input">
                            <input type="file" name="target" onchange="previewFile(this, 'preview');" required>
                            <p>
                                プレビュー
                            </p>
                            <img id=
                            "preview" style="width:60%;">
                        </div>
                        <span class="error"><?=$errors['target'] ?></span>
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
        <?php include($_SERVER['DOCUMENT_ROOT']."/TSC/template/footer.php"); ?>
    </footer>
</body>
</html>