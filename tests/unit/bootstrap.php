<?php
$GLOBALS['woopoly_test_admin'] = false;
$GLOBALS['woopoly_test_locale'] = 'en_US';
function is_admin() { return $GLOBALS['woopoly_test_admin']; }
function pll_current_language($field = 'slug') { return $GLOBALS['woopoly_test_locale']; }
require dirname(__DIR__, 2) . '/src/Hyyan/WPI/LocaleNumbers.php';
