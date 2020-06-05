<?php
// Run code that PHP 5.2 and 5.3 can't parse

require PMB_VENDOR_DIR . 'autoload.php';
(new PrintMyBlog\system\Init())->setHooks();