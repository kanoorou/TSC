<?php
$root_dir = dirname(dirname(dirname(__FILE__)));//TSC??????

require_once $root_dir."/php/db_connect.php";
require_once $root_dir."/php/functions.php";
$before_content = "<?php\n".
"session_start();\n".
"?>\n".
"<!DOCTYPE html>\n".
"<html lang=\"ja\">\n".
"  <head>\n".
"    <meta charset=\"UTF-8\" />\n".
"    <title lang=\"ja\">TSC-Total Synthesis Contest-</title>\n".
"    <link rel=\"stylesheet\" href=\"/TSC/css/common.css\" />\n".
"    <link rel=\"shortcut icon\" href=\"/TSC/assets/logo.png\">\n".
"  </head>\n".
"  <body>\n".
"    <header>\n".
"        <?php include(\$_SERVER['DOCUMENT_ROOT'].\"/TSC/template/header.php\");?>\n".
"    </header>\n".
"    <div id=\"contents-wrapper\">\n";

$after_content = "    </div>\n".
"    <footer>\n".
"        <?php include(\$_SERVER['DOCUMENT_ROOT'].\"/TSC/template/footer.php\");?>\n".
"    </footer>\n".
"  </body>\n".
"</html>\n";

function set_content($html, $content){
    return $before_content.$content.$after_content;
}

$sql = 'SELECT id,starts_at,ends_at FROM TSC_problems WHERE status=0 OR status=1';
$stmt = $pdo->query($sql);

if($list = $stmt->fetchAll(PDO::FETCH_ASSOC)){
    $now = time();
    foreach($list as $row){
	    var_dump($row);
	    $status=0;
	    echo $now."/".strtotime($row["starts_at"])."/".strtotime($row["ends_at"])."\n";
	    if(strtotime($row["ends_at"]) <= $now){
	        //status: 1->2
	        $sql = 'SELECT name FROM TSC_problems WHERE id='.$row["id"];
	        $stmt = $pdo->query($sql);
	        $row = array_merge($row, $stmt->fetch());

	        $content = "<h1>".$row["name"]."</h1><p>このコンテストは終了しました。</p>";
	        $status = 2;
	    }
	    else if(strtotime($row["starts_at"]) <= $now){
	        //status: 0->1
	        $sql = 'SELECT target,rule,name FROM TSC_problems WHERE id='.$row["id"];
	        $stmt = $pdo->query($sql);
	        $row = array_merge($row, $stmt->fetch());

	        $content = "<h1>".$row["name"]."</h1><p>終了時刻: ".date("Y-m-d H:i:s \J\S\T", strtotime($row["ends_at"]))."</p><img src='./images/".$row["target"]."'/><br /><p>".$row["rule"]."</p>";
	        $status = 1;
	    }
	    echo $status."\n";
	    echo $content."\n";
	    if($status > 0){
	        $path = $root_dir."/contest/problems/".$row["name"].".php";

	        $sql = "UPDATE TSC_problems SET status = :status WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue('status',$status);
	        $stmt->bindValue('id',$row["id"]);
                $stmt->execute();

	        echo "file(".$path."):".file_exists($path);

	        $fp = fopen($path, "w+");
	        if (flock($fp, LOCK_EX)) {  // 排他ロックを確保します
		    $html = $before_content.$content.$after_content;
    		    fwrite($fp, $html);
    		    fflush($fp);            // 出力をフラッシュしてからロックを解放します
    		    flock($fp, LOCK_UN);    // ロックを解放します
	        } else {
    		    echo "ファイルを取得できません!";
	        }

	        fclose($fp);
	    }
    } 
}