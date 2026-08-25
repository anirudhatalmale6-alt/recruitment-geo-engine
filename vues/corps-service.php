<?php
/** Une ville x un metier. Le texte est propre au couple, pas au gabarit. */
$p = $r['pays'];
$v = $r['ville'];
$svc = $r['service'];
?>
<section class="hero">
  <div class="wrap">
    <nav class="fil" aria-label="Breadcrumb">
      <a href="<?php echo rec_url( $l ); ?>"><?php echo rec_t( 'accueil', $l ); ?></a>
      <span>·</span>
      <a href="<?php echo rec_url( $l, $p['limace'] ); ?>"><?php echo htmlspecialchars( rec_nom_pays( $p, $l ) ); ?></a>
      <span>·</span>
      <a href="<?php echo rec_url( $l, $p['limace'], $v['limace'] ); ?>"><?php echo htmlspecialchars( $v['nom'] ); ?></a>
    </nav>
    <h1><?php echo htmlspecialchars( sprintf( rec_t( 'h1_ville_svc', $l ),
      strip_tags( rec_t( rec_service_cle( $svc ), $l ) ), $v['nom'] ) ); ?></h1>
    <p class="chapo"><?php echo htmlspecialchars( rec_texte_service( $svc, $p, $v, $l ) ); ?></p>
  </div>
</section>

<section class="bloc">
  <div class="wrap">
    <h2><?php echo rec_t( 'pourquoi_ici', $l ); ?></h2>
    <ul class="consequences">
      <?php foreach ( rec_consequences( $p, $v, $l ) as $x ) : ?>
        <li><?php echo htmlspecialchars( $x ); ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>

<section class="bloc gris">
  <div class="wrap">
    <h2><?php echo rec_t( 'nav_services', $l ); ?></h2>
    <div class="cartes">
      <?php foreach ( rec_services() as $autre ) : if ( $autre === $svc ) { continue; } ?>
        <a class="carte" href="<?php echo rec_url( $l, $p['limace'], $v['limace'], $autre ); ?>">
          <h3><?php echo rec_t( rec_service_cle( $autre ), $l ); ?></h3>
          <p><?php echo rec_t( rec_service_cle( $autre ) . '_d', $l ); ?></p>
        </a>
      <?php endforeach; ?>
      <a class="carte" href="<?php echo rec_url( $l, $p['limace'], $v['limace'] ); ?>">
        <h3><?php echo htmlspecialchars( sprintf( rec_t( 'h1_ville', $l ), $v['nom'] ) ); ?></h3>
        <p><?php echo rec_t( 'marche_local', $l ); ?></p>
      </a>
    </div>
  </div>
</section>

<section class="cta">
  <div class="wrap">
    <h2><?php echo htmlspecialchars( sprintf( rec_t( 'cta_titre', $l ), $v['nom'] ) ); ?></h2>
    <p><?php echo rec_t( 'cta_texte', $l ); ?></p>
    <p><a class="btn" href="#"><?php echo rec_t( 'cta_bouton', $l ); ?></a></p>
  </div>
</section>
