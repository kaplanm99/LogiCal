<?php

require ('getTasks.php');

$tasks = getTasks();

$json = json_encode($tasks);
        
print $json;

?>