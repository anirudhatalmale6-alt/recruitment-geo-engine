<?php
/**
 * L'acces au jeu de donnees geographique et au classement.
 *
 * Les deux fichiers sont produits par geo/construire.py et geo/score.py. Ils
 * sont lus UNE FOIS par requete et gardes en memoire : classement.json fait
 * plusieurs mega-octets et le relire par page coute plus cher que tout le
 * reste de la page reunie.
 *
 * Le moteur geographique ne connait rien du site : il ne sait ni afficher, ni
 * traduire, ni router. C'est ce qui permet de regenerer les classements sans
 * toucher au reste, et de brancher un autre affichage par-dessus.
 */

declare( strict_types = 1 );

function rec_data_dir(): string {
	return dirname( __DIR__ ) . '/data';
}

function rec_classement(): array {
	static $c = null;
	if ( null === $c ) {
		$f = rec_data_dir() . '/classement.json';
		$c = is_readable( $f ) ? json_decode( (string) file_get_contents( $f ), true ) : null;
		if ( ! is_array( $c ) ) {
			$c = array( 'revision' => 'absent', 'top' => 19, 'poids' => array(),
				'sans_source' => array(), 'pays' => array() );
		}
	}
	return $c;
}

function rec_geo(): array {
	static $g = null;
	if ( null === $g ) {
		$f = rec_data_dir() . '/geo.json';
		$g = is_readable( $f ) ? json_decode( (string) file_get_contents( $f ), true ) : null;
		if ( ! is_array( $g ) ) {
			$g = array( 'top' => 19, 'pays' => array() );
		}
	}
	return $g;
}

/** Tous les pays classes, tries par nom. */
function rec_pays_tous(): array {
	$c = rec_classement();
	$p = $c['pays'];
	uasort( $p, function ( $a, $b ) { return strcmp( $a['nom'], $b['nom'] ); } );
	return $p;
}

/** Un pays par sa limace d'URL, ou null. */
function rec_pays( string $limace ): ?array {
	foreach ( rec_classement()['pays'] as $iso => $p ) {
		if ( $p['limace'] === $limace ) {
			$p['iso'] = $iso;
			return $p;
		}
	}
	return null;
}

/** Une ville dans un pays, ou null. */
function rec_ville( array $pays, string $limace ): ?array {
	foreach ( $pays['villes'] as $v ) {
		if ( $v['limace'] === $limace ) {
			return $v;
		}
	}
	return null;
}

/** Le detail complet d'une ville, depuis geo.json (au-dela du Top-19). */
function rec_ville_detail( string $iso, string $limace ): ?array {
	$g = rec_geo();
	if ( ! isset( $g['pays'][ $iso ] ) ) {
		return null;
	}
	foreach ( $g['pays'][ $iso ]['villes'] as $v ) {
		if ( $v['limace'] === $limace ) {
			return $v;
		}
	}
	return null;
}

/** Les villes voisines d'une ville, dans le meme pays, hors elle-meme. */
function rec_voisines( array $pays, string $limace, int $combien = 6 ): array {
	$out = array();
	foreach ( $pays['villes'] as $v ) {
		if ( $v['limace'] !== $limace ) {
			$out[] = $v;
		}
		if ( count( $out ) >= $combien ) {
			break;
		}
	}
	return $out;
}

/* -------------------------------------------------------------------------- */
/* LA PORTE DE QUALITE                                                        */
/* -------------------------------------------------------------------------- */
/*
 * Le cahier des charges est explicite : « A page is published and indexed only
 * after meeting defined data and quality thresholds », et « noindex when local
 * data is insufficient ».
 *
 * Ce n'est pas une precaution decorative. Onze mille pages construites sur le
 * meme gabarit avec le nom de la ville change, c'est la definition d'une page
 * satellite, et Google ne sanctionne pas la page : il sanctionne le domaine.
 * La porte est donc CALCULEE PAR PAGE et son verdict est visible, pas cache.
 */

/** Les criteres de qualite d'une page ville, avec leur verdict. */
function rec_qualite( array $pays, array $ville, ?array $detail ): array {
	$criteres = array();

	$criteres['population'] = array(
		'libelle' => 'Population figure for the city',
		'ok'      => ! empty( $ville['population'] ),
	);
	$criteres['region'] = array(
		'libelle' => 'Administrative region known (used to rank within the region)',
		'ok'      => '' !== (string) ( $ville['region_code'] ?? '' ),
	);
	$criteres['fuseau'] = array(
		'libelle' => 'Time zone known (working hours, shift cover)',
		'ok'      => '' !== (string) ( $ville['fuseau'] ?? '' ),
	);
	$criteres['langues'] = array(
		'libelle' => 'Official languages of the country known',
		'ok'      => ! empty( $pays['langues'] ),
	);
	$criteres['voisines'] = array(
		'libelle' => 'At least three other ranked cities to link to',
		'ok'      => count( $pays['villes'] ) >= 4,
	);
	$criteres['taille'] = array(
		'libelle' => 'City large enough to describe a labour market (25 000+)',
		'ok'      => (int) ( $ville['population'] ?? 0 ) >= 25000,
	);

	$verts = 0;
	foreach ( $criteres as $c ) {
		if ( $c['ok'] ) {
			$verts++;
		}
	}
	$total = count( $criteres );
	$note  = $total ? $verts / $total : 0.0;

	// Cinq criteres sur six. Une page qui n'en a que quatre existe, se lit et
	// se relie, mais elle n'entre pas dans l'index tant qu'il lui manque de la
	// matiere locale.
	$indexable = $verts >= 5;

	return array(
		'criteres'  => $criteres,
		'verts'     => $verts,
		'total'     => $total,
		'note'      => round( $note, 3 ),
		'indexable' => $indexable,
	);
}
