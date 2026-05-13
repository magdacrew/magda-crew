<?php
session_start();

session_destroy();

header("Location: /magda-crew/public/index.php");
exit;