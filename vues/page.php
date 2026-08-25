<?php
/**
 * Le gabarit unique : accueil, pays, ville, ville+service.
 *
 * Un seul fichier plutot que quatre, parce que l'entete, le pied, le
 * selecteur de langue et le fil d'Ariane sont les memes partout et qu'un
 * quadruplicata finit toujours par diverger. Ce qui change entre les quatre,
 * c'est le CORPS, et il est isole plus bas.
 */

$l   = $r['langue'];
$L   = rec_langues()[ $l ];
$ind = $qualite ? (bool) $qualite['indexable'] : true;
$c   = rec_classement();
?><!doctype html>
<html lang="<?php echo $L['htmllang']; ?>" dir="<?php echo $L['dir']; ?>">
<head>
<?php echo rec_head( $r, $ind ); ?>
<link rel="stylesheet" href="/style.css?v=1">
</head>
<body>

<header class="top">
  <div class="wrap bar">
    <a class="marque" href="<?php echo rec_url( $l ); ?>">
      <span class="pastille" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
             stroke-linecap="round"><path d="M3 20V9l9-5 9 5v11"/><path d="M9 20v-6h6v6"/></svg>
      </span>
      <span><?php echo rec_t( 'marque', $l ); ?></span>
    </a>
    <nav class="nav">
      <a href="<?php echo rec_url( $l ); ?>"><?php echo rec_t( 'nav_pays', $l ); ?></a>
      <?php
      // Le selecteur de langue pointe sur LA MEME page dans l'autre langue,
      // pas sur l'accueil. Renvoyer le lecteur a la racine parce qu'il a
      // change de langue est la facon la plus rapide de le perdre.
      $vars = rec_variantes( $r );
      foreach ( $vars as $lg => $u ) :
        if ( $lg === $l ) { continue; } ?>
        <a class="lang" href="<?php echo htmlspecialchars( $u, ENT_QUOTES ); ?>"
           hreflang="<?php echo $lg; ?>"><?php echo rec_langues()[ $lg ]['nom']; ?></a>
      <?php endforeach; ?>
    </nav>
  </div>
</header>

<?php if ( ! $ind ) : ?>
<div class="bandeau-noindex"><div class="wrap">
  <?php echo htmlspecialchars( rec_t( 'pas_indexee', $l ) ); ?>
  <?php if ( $qualite ) : ?>
    <span class="note"><?php echo (int) $qualite['verts']; ?>/<?php echo (int) $qualite['total']; ?></span>
  <?php endif; ?>
</div></div>
<?php endif; ?>

<main>
<?php
switch ( $r['type'] ) {
	case 'accueil':
		require __DIR__ . '/corps-accueil.php';
		break;
	case 'pays':
		require __DIR__ . '/corps-pays.php';
		break;
	case 'ville':
		require __DIR__ . '/corps-ville.php';
		break;
	case 'service':
		require __DIR__ . '/corps-service.php';
		break;
}
?>
</main>

<footer class="foot">
  <div class="wrap foot-in">
    <span><?php echo rec_t( 'marque', $l ); ?></span>
    <span class="note">
      <?php
      // La revision du classement est ecrite sur chaque page : deux captures
      // d'ecran prises a deux moments differents ne peuvent pas se faire
      // passer pour le meme classement.
      // echo, PAS printf : le texte est deja formate, et un « % » dedans
      // ferait avaler la fin de la phrase par printf.
      echo htmlspecialchars( rec_t( 'donnees_texte', $l, $c['revision'] ) );
      ?>
    </span>
  </div>
</footer>
</body>
</html>
