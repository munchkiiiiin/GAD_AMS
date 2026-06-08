<?php
require 'public/index.php';
$db = \Config\Database::connect();
echo "Activity Design:\n";
print_r($db->getFieldNames('activity_design'));
echo "\nAccomplishment Report:\n";
print_r($db->getFieldNames('accomplishment_report'));
