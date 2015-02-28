<?php
require 'vendor/autoload.php';

try {
    $config = new Noodlehaus\Config('config/config.json');
} catch (Exception $e) {
    return $e->getMessage();
}
