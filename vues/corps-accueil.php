<?php
/** L'accueil : les trois metiers, puis tous les pays couverts. */
$tous = rec_pays_tous();
$c = rec_classement();
$villes = 0; foreach ( $tous as $x ) { $villes += count( $x['villes'] ); }
?>
<section class="hero grand">
  <div class="wrap">
    <h1><?php echo array(
      'en' => 'Hire in the city, not in the country.',
      'fr' => 'Recruter dans la ville, pas dans le pays.',
      'es' => 'Contratar en la ciudad, no en el país.',
    )[ $l ]; ?></h1>
    <p class="chapo"><?php echo htmlspecialchars( sprintf( array(
      'en' => 'A hiring plan is built on one local labour market at a time. We cover %s cities across %s countries, each ranked on what its market can actually supply.',
      'fr' => 'Un plan de recrutement se construit sur un marché local à la fois. Nous couvrons %s villes dans %s pays, chacune classée sur ce que son marché peut réellement fournir.',
      'es' => 'Un plan de contratación se construye sobre un mercado local cada vez. Cubrimos %s ciudades en %s países, cada una clasificada por lo que su mercado puede aportar realmente.',
    )[ $l ], rec_nombre( $villes, $l ), rec_nombre( count( $tous ), $l ) ) ); ?></p>
  </div>
</section>

<section class="bloc">
  <div class="wrap">
    <h2><?php echo rec_t( 'nav_services', $l ); ?></h2>
    <div class="cartes">
      <?php foreach ( rec_services() as $svc ) : ?>
        <div class="carte">
          <h3><?php echo rec_t( rec_service_cle( $svc ), $l ); ?></h3>
          <p><?php echo rec_t( rec_service_cle( $svc ) . '_d', $l ); ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="bloc gris">
  <div class="wrap">
    <h2><?php echo rec_t( 'nav_pays', $l ); ?></h2>
    <ul class="pays-liste">
      <?php foreach ( $tous as $iso => $p ) : ?>
        <li><a href="<?php echo rec_url( $l, $p['limace'] ); ?>"><?php
          echo htmlspecialchars( $p['nom'] );
        ?></a> <span class="note"><?php echo count( $p['villes'] ); ?></span></li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
