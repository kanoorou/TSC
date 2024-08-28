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