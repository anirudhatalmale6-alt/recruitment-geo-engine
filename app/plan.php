<?php
/**
 * Le plan de site.
 *
 * IL N'ANNONCE QUE LES PAGES INDEXABLES. Lister une page qu'on marque noindex
 * envoie deux ordres contradictoires au robot, et c'est celui qui coute une
 * exploration inutile qui gagne. La porte de qualite est donc la meme ici que
 * dans la page — la MEME fonction, pas une copie de la regle.
 */
header( 'Content-Type: application/xml; charset=utf-8' );
$base = rec_base();
$geo  = rec_geo();

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' .
     ' xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";

$langues = array_keys( rec_langues() );
$sortie  = function ( array $urls ) use ( $base ) {
	// Chaque adresse est publiee avec ses variantes de langue : hreflang doit
	// etre reciproque, sinon les variantes sont ignorees.
	foreach ( $urls as $lg => $u ) {
		echo "<url><loc>" . htmlspecialchars( $base . $u, ENT_XML1 ) . "</loc>\n";
		foreach ( $urls as $lg2 => $u2 ) {
			echo '  <xhtml:link rel="alternate" hreflang="' . $lg2 . '" href="'
				. htmlspecialchars( $base . $u2, ENT_XML1 ) . "\"/>\n";
		}
		echo "</url>\n";
	}
};

$urls = array();
foreach ( $langues as $lg ) { $urls[ $lg ] = rec_url( $lg ); }
$sortie( $urls );

$publiees = 0;
$retenues = 0;
foreach ( rec_classement()['pays'] as $iso => $p ) {
	$urls = array();
	foreach ( $langues as $lg ) { $urls[ $lg ] = rec_url( $lg, $p['limace'] ); }
	$sortie( $urls );
	$publiees += count( $langues );

	foreach ( $p['villes'] as $v ) {
		$detail = rec_ville_detail( $iso, $v['limace'] );
		$q = rec_qualite( $p, $v, $detail );
		if ( ! $q['indexable'] ) {
			$retenues += count( $langues ) * 4;
			continue;
		}
		$urls = array();
		foreach ( $langues as $lg ) { $urls[ $lg ] = rec_url( $lg, $p['limace'], $v['limace'] ); }
		$sortie( $urls );
		$publiees += count( $langues );

		foreach ( rec_services() as $svc ) {
			$urls = array();
			foreach ( $langues as $lg ) {
				$urls[ $lg ] = rec_url( $lg, $p['limace'], $v['limace'], $svc );
			}
			$sortie( $urls );
			$publiees += count( $langues );
		}
	}
}
echo '</urlset>' . "\n";
echo '<!-- ' . $publiees . ' publiees, ' . $retenues . ' retenues par la porte de qualite -->' . "\n";
