<?php
$host = $_SERVER['HTTP_HOST'];
$uri = $_SERVER['REQUEST_URI'];
if (strpos($uri, '/public/') === false) {
    header("Location: https://" . $host . "/public/index.php", true, 301);
    exit();
}
?>