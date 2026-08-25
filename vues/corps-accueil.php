<?php
/** L'accueil : les trois metiers, puis tous les pays couverts. */
$tous = rec_pays_tous( $l );
$c = rec_classement();
$villes = 0; foreach ( $tous as $x ) { $villes += count( $x['villes'] ); }
?>
<section class="hero grand">
  <div class="wrap">
    <h1><?php echo array(
      'en' => 'Outsource your recruitment to the city, not to the country.',
      'fr' => 'Externalisez votre recrutement vers la ville, pas vers le pays.',
      'es' => 'Externalice su selección hacia la ciudad, no hacia el país.',
    )[ $l ]; ?></h1>
    <p class="chapo"><?php echo htmlspecialchars( sprintf( array(
      'en' => 'You hand over the hiring process; we run it in a market chosen for what it can actually supply. %s cities across %s countries, each ranked, each with its own languages, time zone and depth of talent pool.',
      'fr' => 'Vous nous confiez le processus de recrutement ; nous l’opérons sur un marché choisi pour ce qu’il peut réellement fournir. %s villes dans %s pays, chacune classée, chacune avec ses langues, son fuseau horaire et la profondeur de son vivier.',
      'es' => 'Usted delega el proceso de selección; nosotros lo operamos en un mercado elegido por lo que puede aportar realmente. %s ciudades en %s países, cada una clasificada, con sus idiomas, su zona horaria y la profundidad de su cantera.',
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
          echo htmlspecialchars( rec_nom_pays( $p, $l ) );
        ?></a> <span class="note"><?php echo count( $p['villes'] ); ?></span></li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
