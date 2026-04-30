<?php
session_start();
session_unset();
session_destroy();
header("Location: /portal_pyme/index.php");
exit;
?>
