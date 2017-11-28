<?php
if (class_exists('Phar')) {
    Phar::mapPhar('Portmone.phar');
    require 'phar://' . __FILE__ . '/Portmone.php';
}
__HALT_COMPILER();