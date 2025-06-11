<?php
session_start();
session_unset();
session_destroy();
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <script>
        window.location.replace("login.php");
    </script>
</head>
<body>
    <noscript>
        <meta http-equiv="refresh" content="0;url=login.php">
    </noscript>
</body>
</html>