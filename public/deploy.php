<?php
chdir('..');
$output = shell_exec('git pull'); 
echo "<pre>$output</pre>";
echo "finish1";
die();
