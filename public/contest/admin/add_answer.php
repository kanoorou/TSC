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

$sql = 'SELECT * FROM TSC_problem_uploads';
$stmt = $pdo->query($sql);
$problems = $stmt->fetchAll(PDO::FETCH_ASSOC);

$default_problem_HTML = "";
$default_contest = "";
$options_HTML = '';
$problem_HTMLs = [];
foreach ($contests as $contest) {
    if($default_contest == "")$default_contest = $contest["contest_name"];
    $problem_HTMLs[$contest["contest_name"]] = "";
    $options_HTML .= "<option value='".$contest['contest_name']."'>".$contest['contest_name']."</option>\n";
}
foreach ($problems as $problem) {
    $problem_HTMLs[$problem["contest_name"]] .= "<option value='".$problem['name']."'>".$problem['name']."</option>\n";
    $json_array = json_encode($problem_HTMLs);
}

$default_problem_HTML = $problem_HTMLs[$default_contest];

$data = [
    'contest_name' => '',
    'problem_name' => '',
    'comment' => '',
    'img' => ''
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

    $answer_password = $contest["answer_password"];

    $dir = "{$_SERVER['DOCUMENT_ROOT']}/TSC/contest/problems/{$contest_name}/assets/{$answer_password}/";

    $sql = 'SELECT id FROM TSC_contest_answers WHERE contest_name = :contest_name AND problem_name=:name';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue('contest_name', $contest_name);
    $stmt->bindValue('name', $data["problem_name"]);
    $stmt->execute();

    $ids = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $ids[] = $row['id'];
    }
    foreach ($ids as $id) {
        $sql = 'DELETE FROM TSC_contest_answers WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue('id', $id);
        $stmt->execute();
    }

    if (isset($_FILES['img'])) {
        if ($_FILES['img']['error'] == UPLOAD_ERR_OK) {
            $tmp_name = $_FILES['img']['tmp_name'];
            $file_name = basename($_FILES['img']['name']);
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
            'problem_name' => $data["problem_name"],
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
            $sql = 'insert into TSC_contest_answers ('.$columns.')values('.$values.')';
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
        let HTMLs = <?=$json_array?>;
        function previewFile(hoge, id) {
            var fileData = new FileReader();
            fileData.onload = (function() {
                document.getElementById(id).src = fileData.result;
            });
            fileData.readAsDataURL(hoge.files[0]);
        }
        function reloadProblems(hoge, id) {
	    console.log(hoge.value);
	    console.log(HTMLs[hoge.value]);
            document.getElementById(id).innerHTML = HTMLs[hoge.value];
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
                <h2 class="center-title">模範解答の追加</h2>
            </div>
            <form enctype="multipart/form-data" name="problem-input" class="center" action method="post">
                <div class="form-item flex">
                    <label class="item-label" for="input-type">コンテスト名</label>
                    <div class="item-content">
                        <div class="item-input">
                            <select id="input-type" name="contest_name"  onchange="reloadProblems(this, 'input-subname');">
                                <?= $options_HTML ?>
                            </select>
                            <select id="input-subname" name="problem_name">
				<?= $default_problem_HTML ?>
                            </select>
                        </div>
                        <span class="error"><?=$errors['contest_name'] ?></span>
                    </div>
                </div>
                <div class="form-item flex">
                    <label class="item-label" for="input-type">コメント</label>
                    <div class="item-content">
                        <div class="item-input">
                            <input type="text" name="comment">
                        </div>
                    </div>
                </div>
                <div class="form-item flex">
                    <div class="item-content">
                        <div class="item-input">
                            <input type="file" name="img" onchange="previewFile(this, 'preview');" required>
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