<?php
chdir('..');
$output = shell_exec('git pull'); 
var_dump($output);
echo "<pre>$output</pre>";
echo "finish1";
die();
