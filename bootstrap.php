<?php
/**
 * Run code that PHP 5.2 and 5.3 can't parse. So this only gets included once we've checked PHP can handle it.
*/
require PMB_VENDOR_DIR . 'autoload.php';
$init = new PrintMyBlog\system\Init();
$init->setHooks();
