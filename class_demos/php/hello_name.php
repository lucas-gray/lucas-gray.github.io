<?php
$name = $_GET["name"] ?? "visitor";
$safe_name = htmlspecialchars($name, ENT_QUOTES, "UTF-8");
?>

<!doctype html>
<h1>Hello, <?= $safe_name ?></h1>
<p>Generated at <?= date("H:i:s")?> </p>
