<?php
$hash = password_hash('123456', PASSWORD_BCRYPT);
echo "UPDATE users SET password = '$hash';";
