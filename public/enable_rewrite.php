<?php
echo "<pre>";
echo "Trying to enable rewrite module...\n";
$output = shell_exec('a2enmod rewrite 2>&1');
echo $output . "\n";
echo "Trying to restart Apache...\n";
$output2 = shell_exec('apache2ctl graceful 2>&1');
echo $output2 . "\n";
echo "</pre>";
?>
