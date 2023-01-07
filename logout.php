<?php

require_once 'utils/session.php';

$requireAuth = true;
$session = new AuthSession($requireAuth);
$session->logout();

?>
