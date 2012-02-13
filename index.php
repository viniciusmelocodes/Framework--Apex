<?php
error_reporting(E_ALL);
define ('SYS','sys');
define ('APP','app');
include SYS."/core.php";
hook('framework_loaded');
apex::go();
