<?php
$l = $r['langue'];
?><!doctype html>
<html lang="<?php echo $l; ?>"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>404</title><meta name="robots" content="noindex"><link rel="stylesheet" href="/style.css?v=1">
</head><body>
<main><section class="hero"><div class="wrap">
<h1>404</h1>
<p class="chapo"><?php echo array(
  'en' => 'No page at this address.',
  'fr' => 'Aucune page à cette adresse.',
  'es' => 'No hay página en esta dirección.',
)[ $l ]; ?></p>
<p><a class="btn" href="<?php echo rec_url( $l ); ?>"><?php echo rec_t( 'accueil', $l ); ?></a></p>
</div></section></main>
</body></html>
