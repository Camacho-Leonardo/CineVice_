<?php
session_start();
session_destroy();
header("Location: formularios.php?inicio");
exit;
