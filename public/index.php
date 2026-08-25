<?php
/**
 * Le controleur d'entree.
 *
 * Tout passe par ici : une seule porte, un seul endroit ou le code HTTP est
 * decide. Un 404 doit VRAIMENT repondre 404 — une page d'erreur servie en 200
 * finit indexee, et sur un site de trois mille adresses generees ca se compte
 * en centaines.
 */

declare( strict_types = 1 );

$app = dirname( __DIR__ ) . '/app';
require_once $app . '/langues.php';
require_once $app . '/geo.php';
require_once $app . '/routes.php';
require_once $app . '/contenu.php';
require_once $app . '/seo.php';

$r = rec_router( $_SERVER['REQUEST_URI'] ?? '/' );

if ( 'racine' === $r['type'] ) {
	// La langue du navigateur si on la parle, l'anglais sinon.
	$l = rec_langue_defaut();
	$accepte = strtolower( (string) ( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '' ) );
	foreach ( array_keys( rec_langues() ) as $cle ) {
		if ( '' !== $accepte && 0 === strpos( $accepte, $cle ) ) {
			$l = $cle;
			break;
		}
	}
	header( 'Location: ' . rec_url( $l ), true, 302 );
	exit;
}

if ( 'plan' === $r['type'] ) {
	require dirname( __DIR__ ) . '/app/plan.php';
	exit;
}

if ( '404' === $r['type'] ) {
	http_response_code( 404 );
	$r['langue'] = $r['langue'] ?? rec_langue_defaut();
	require dirname( __DIR__ ) . '/vues/404.php';
	exit;
}

// La porte de qualite, avant tout affichage : c'est elle qui decide du
// noindex, donc elle doit etre connue au moment d'ecrire le <head>.
$qualite = null;
if ( in_array( $r['type'], array( 'ville', 'service' ), true ) ) {
	$detail  = rec_ville_detail( $r['pays']['iso'], $r['ville']['limace'] );
	$qualite = rec_qualite( $r['pays'], $r['ville'], $detail );
}

require dirname( __DIR__ ) . '/vues/page.php';
