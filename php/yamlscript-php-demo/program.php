<?php
// pragram.php
require_once __DIR__ . '/vendor/autoload.php';

use YAMLScript\YAMLScript;

$ys = new YAMLScript();

// Load from file
$input = file_get_contents('config.yaml');
$config = $ys->load($input);

echo json_encode($config, JSON_PRETTY_PRINT);

?>
