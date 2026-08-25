<?php
// Routeur du serveur PHP integre : sert les fichiers reels, sinon index.php.
$f = __DIR__ . parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
if ( is_file( $f ) ) { return false; }
require __DIR__ . '/index.php';
