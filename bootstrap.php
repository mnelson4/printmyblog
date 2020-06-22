<?php
// Run code that PHP 5.2 and 5.3 can't parse

require PMB_VENDOR_DIR . 'autoload.php';
$context = PrintMyBlog\system\Context::instance();
$init = $context->reuse('PrintMyBlog\system\Init');
$init->setHooks();