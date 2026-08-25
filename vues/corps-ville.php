<?php
/**
 * La page ville. C'est LA page du projet : il y en aura trois mille et c'est
 * elle qui decide si le domaine est vu comme une ressource ou comme une ferme
 * a pages.
 *
 * Elle n'affiche rien qu'on n'ait pas : un champ vide ne sort pas une ligne
 * « — », il ne sort pas de ligne du tout.
 */

$p = $r['pays'];
$v = $r['ville'];
$q = $qualite;
$pop = (int) $v['population'];
?>

<section class="hero">
  <div class="wrap">
    <nav class="fil" aria-label="Breadcrumb">
      <a href="<?php echo rec_url( $l ); ?>"><?php echo rec_t( 'accueil', $l ); ?></a>
      <span>·</span>
      <a href="<?php echo rec_url( $l, $p['limace'] ); ?>"><?php echo htmlspecialchars( $p['nom'] ); ?></a>
    </nav>
    <h1><?php echo htmlspecialchars( sprintf( rec_t( 'h1_ville', $l ), $v['nom'] ) ); ?></h1>
    <p class="chapo"><?php echo htmlspecialchars( rec_chapo( $p, $v, $l ) ); ?></p>
  </div>
</section>

<section class="bloc">
  <div class="wrap">
    <h2><?php echo rec_t( 'marche_local', $l ); ?></h2>
    <dl class="faits">
      <div><dt><?php echo rec_t( 'f_population', $l ); ?></dt>
        <dd><?php echo rec_nombre( $pop, $l ); ?></dd></div>
      <div><dt><?php echo htmlspecialchars( sprintf( rec_t( 'f_rang', $l ), $p['nom'] ) ); ?></dt>
        <dd><?php echo (int) $v['rang_pays']; ?></dd></div>
      <?php
      // PAS DE LIGNE « Region » ICI. Le jeu de donnees ne fournit qu'un code
      // administratif — « 08 » pour l'Ontario — et afficher « Region : 08 » a
      // un responsable RH, c'est afficher du bruit en pretendant informer. Le
      // code reste utilise en interne pour le rang regional ; il ressortira le
      // jour ou on aura la table des noms de regions.
      ?>
      <?php if ( '' !== (string) $v['fuseau'] ) : ?>
      <div><dt><?php echo rec_t( 'f_fuseau', $l ); ?></dt>
        <dd><?php echo htmlspecialchars( $v['fuseau'] ); ?></dd></div>
      <?php endif; ?>
      <?php if ( ! empty( $p['langues'] ) ) : ?>
      <div><dt><?php echo rec_t( 'f_langues', $l ); ?></dt>
        <dd><?php echo htmlspecialchars( strtoupper( implode( ', ', $p['langues'] ) ) ); ?></dd></div>
      <?php endif; ?>
      <div><dt><?php echo rec_t( 'f_part', $l ); ?></dt>
        <dd><?php echo number_format( (float) $v['part_urbaine'] * 100, 1 ); ?> %</dd></div>
      <?php if ( ! empty( $v['capitale'] ) ) : ?>
      <div><dt><?php echo rec_t( 'f_capitale', $l ); ?></dt><dd>✓</dd></div>
      <?php endif; ?>
    </dl>
  </div>
</section>

<section class="bloc gris">
  <div class="wrap">
    <h2><?php echo rec_t( 'pourquoi_ici', $l ); ?></h2>
    <ul class="consequences">
      <?php foreach ( rec_consequences( $p, $v, $l ) as $x ) : ?>
        <li><?php echo htmlspecialchars( $x ); ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>

<section class="bloc">
  <div class="wrap">
    <h2><?php echo htmlspecialchars( sprintf( rec_t( 'nos_services_ici', $l ), $v['nom'] ) ); ?></h2>
    <div class="cartes">
      <?php foreach ( rec_services() as $svc ) : ?>
        <a class="carte" href="<?php echo rec_url( $l, $p['limace'], $v['limace'], $svc ); ?>">
          <h3><?php echo rec_t( rec_service_cle( $svc ), $l ); ?></h3>
          <p><?php echo rec_t( rec_service_cle( $svc ) . '_d', $l ); ?></p>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php $qs = rec_questions( $p, $v, $l ); if ( $qs ) : ?>
<section class="bloc gris">
  <div class="wrap">
    <h2><?php echo htmlspecialchars( sprintf( rec_t( 'questions', $l ), $v['nom'] ) ); ?></h2>
    <div class="faq">
      <?php foreach ( $qs as $x ) : ?>
        <details><summary><?php echo htmlspecialchars( $x['q'] ); ?></summary>
          <p><?php echo htmlspecialchars( $x['r'] ); ?></p></details>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php $voisines = rec_voisines( $p, $v['limace'], 8 ); if ( $voisines ) : ?>
<section class="bloc">
  <div class="wrap">
    <h2><?php echo htmlspecialchars( sprintf( rec_t( 'villes_proches', $l ), $p['nom'] ) ); ?></h2>
    <ul class="puces">
      <?php foreach ( $voisines as $o ) : ?>
        <li><a href="<?php echo rec_url( $l, $p['limace'], $o['limace'] ); ?>">
          <?php echo htmlspecialchars( $o['nom'] ); ?></a></li>
      <?php endforeach; ?>
    </ul>
    <p><a class="lien-retour" href="<?php echo rec_url( $l, $p['limace'] ); ?>">
      <?php echo htmlspecialchars( sprintf( rec_t( 'retour_pays', $l ), $p['nom'] ) ); ?></a></p>
  </div>
</section>
<?php endif; ?>

<section class="cta">
  <div class="wrap">
    <h2><?php echo htmlspecialchars( sprintf( rec_t( 'cta_titre', $l ), $v['nom'] ) ); ?></h2>
    <p><?php echo rec_t( 'cta_texte', $l ); ?></p>
    <p><a class="btn" href="#"><?php echo rec_t( 'cta_bouton', $l ); ?></a></p>
  </div>
</section>

<?php if ( $q ) : ?>
<section class="bloc porte">
  <div class="wrap">
    <h2><?php echo rec_t( 'donnees_titre', $l ); ?></h2>
    <ul class="criteres">
      <?php foreach ( $q['criteres'] as $cr ) : ?>
        <li class="<?php echo $cr['ok'] ? 'ok' : 'ko'; ?>">
          <?php echo $cr['ok'] ? '✓' : '·'; ?> <?php echo htmlspecialchars( $cr['libelle'] ); ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
<?php endif; ?>
