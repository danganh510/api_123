<?php
$content = file_get_contents("php://input");
$json    = json_decode($content, true);
file_put_contents("/test.txt",$json);
chdir('..');
$output = shell_exec('git pull'); 
echo "<pre>$output</pre>";
echo "finish";
die();
