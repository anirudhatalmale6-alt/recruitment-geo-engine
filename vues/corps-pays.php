<?php
/** La page pays : la porte d'entree vers ses villes, classees. */
$p = $r['pays'];
?>
<section class="hero">
  <div class="wrap">
    <nav class="fil" aria-label="Breadcrumb">
      <a href="<?php echo rec_url( $l ); ?>"><?php echo rec_t( 'accueil', $l ); ?></a>
    </nav>
    <h1><?php echo htmlspecialchars( sprintf( rec_t( 'h1_pays', $l ), rec_de_pays( $p, $l ) ) ); ?></h1>
    <p class="chapo"><?php
      echo htmlspecialchars( sprintf( rec_t( 'villes_du_pays', $l ),
        count( $p['villes'] ), rec_de_pays( $p, $l ) ) );
      if ( ! $p['complet'] ) {
        // On le DIT quand le pays n'atteint pas le Top-19. Afficher « Top 19 »
        // au-dessus de onze villes est un mensonge que le lecteur compte.
        echo ' ';
        echo htmlspecialchars( array(
          'en' => 'The dataset knows fewer cities here than our target of 19, so this list is shorter — we would rather show what exists than pad it.',
          'fr' => 'Le jeu de données connaît ici moins de villes que notre cible de 19 : cette liste est donc plus courte. Nous préférons montrer ce qui existe plutôt que de la gonfler.',
          'es' => 'El conjunto de datos conoce aquí menos ciudades que nuestro objetivo de 19, así que esta lista es más corta: preferimos mostrar lo que existe antes que rellenarla.',
        )[ $l ] );
      }
    ?></p>
  </div>
</section>

<section class="bloc">
  <div class="wrap">
    <table class="classement">
      <thead><tr>
        <th>#</th>
        <th><?php echo rec_t( 'nav_pays', $l ) === 'Countries' ? 'City' : ( 'fr' === $l ? 'Ville' : 'Ciudad' ); ?></th>
        <th><?php echo rec_t( 'f_population', $l ); ?></th>
        <th>Score</th>
      </tr></thead>
      <tbody>
      <?php foreach ( $p['villes'] as $v ) : ?>
        <tr>
          <td class="rg"><?php echo (int) $v['rang']; ?></td>
          <td><a href="<?php echo rec_url( $l, $p['limace'], $v['limace'] ); ?>"><?php
            echo htmlspecialchars( $v['nom'] );
          ?></a><?php echo ! empty( $v['capitale'] ) ? ' <span class="cap">★</span>' : ''; ?></td>
          <td class="num"><?php echo rec_nombre( (int) $v['population'], $l ); ?></td>
          <td class="num"><?php echo number_format( (float) $v['score'], 3 ); ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

<section class="bloc gris">
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
