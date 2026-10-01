<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; } // Script CLI uniquement : jamais exécutable via le web
/**
 * @deprecated Utiliser database/fix_encoding.php
 * Usage : php database/fix_utf8_seed_strings.php
 */
require __DIR__ . '/fix_encoding.php';
