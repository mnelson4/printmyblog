<?php
// Run code that PHP 5.2 and 5.3 can't parse

require PMB_VENDOR_DIR . 'autoload.php';
$init = new PrintMyBlog\system\Init();
$init->setHooks();