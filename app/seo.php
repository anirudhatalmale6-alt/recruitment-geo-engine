<?php
/**
 * Le referencement : titres, canoniques, hreflang, donnees structurees.
 *
 * CABLE DES LE DEPART, pas ajoute apres coup. Sur un site dont la raison
 * d'etre est l'acquisition organique, poser les canoniques et les hreflang une
 * fois les pages en ligne, c'est reindexer trois mille adresses deux fois.
 *
 * DEUX REGLES QUI COUTENT CHER SI ON LES OUBLIE :
 *   1. Chaque page se declare canonique d'elle-meme. Une page neuve sans
 *      canonique se declare doublon de la premiere page qui lui ressemble.
 *   2. hreflang est reciproque et inclut x-default. Une variante qui pointe
 *      vers les autres sans qu'elles pointent vers elle est ignoree.
 */

declare( strict_types = 1 );

/** Le titre et la description d'une page, par type. */
function rec_meta( array $r ): array {
	$l = $r['langue'] ?? rec_langue_defaut();

	if ( 'accueil' === $r['type'] ) {
		return array(
			'titre' => rec_t( 'marque', $l ) . ' — ' . array(
				'en' => 'Corporate, call center and bulk hiring worldwide',
				'fr' => 'Recrutement cadres, centres d’appels et volume, dans le monde entier',
				'es' => 'Selección corporativa, call center y contratación masiva en todo el mundo',
			)[ $l ],
			'desc'  => array(
				'en' => 'Hiring plans for corporate roles, call center and BPO teams and high-volume campaigns, built city by city from local labour market data.',
				'fr' => 'Des plans de recrutement pour les postes cadres, les équipes de centres d’appels et les campagnes en volume, construits ville par ville à partir de données locales.',
				'es' => 'Planes de contratación para puestos corporativos, equipos de call center y campañas de volumen, construidos ciudad a ciudad con datos locales.',
			)[ $l ],
		);
	}

	if ( 'pays' === $r['type'] ) {
		$n = $r['pays']['nom'];
		return array(
			'titre' => sprintf( rec_t( 'h1_pays', $l ), $n ) . ' — ' . rec_t( 'marque', $l ),
			'desc'  => sprintf( array(
				'en' => 'The %d cities we cover %s, ranked by hiring potential, with corporate, call center and bulk hiring in each.',
				'fr' => 'Les %d villes couvertes %s, classées par potentiel de recrutement, avec cadres, centres d’appels et volume dans chacune.',
				'es' => 'Las %d ciudades que cubrimos %s, ordenadas por potencial de contratación, con selección corporativa, call center y volumen.',
			)[ $l ], count( $r['pays']['villes'] ), rec_de_pays( $n, $l ) ),
		);
	}

	$v = $r['ville'];
	$p = $r['pays'];

	if ( 'ville' === $r['type'] ) {
		return array(
			'titre' => sprintf( rec_t( 'h1_ville', $l ), $v['nom'] ) . ', ' . $p['nom']
				. ' — ' . rec_t( 'marque', $l ),
			'desc'  => sprintf( array(
				'en' => 'Hiring in %s, %s: %s residents, rank %d in the country. What the local market can supply, and how fast.',
				'fr' => 'Recruter à %s, %s : %s habitants, rang %d dans le pays. Ce que le marché local peut fournir, et à quelle vitesse.',
				'es' => 'Contratar en %s, %s: %s habitantes, puesto %d del país. Qué puede aportar el mercado local y a qué ritmo.',
			)[ $l ], $v['nom'], $p['nom'], rec_nombre( (int) $v['population'], $l ),
				(int) $v['rang_pays'] ),
		);
	}

	$svc = rec_t( rec_service_cle( $r['service'] ), $l );
	return array(
		'titre' => sprintf( rec_t( 'h1_ville_svc', $l ), strip_tags( $svc ), $v['nom'] )
			. ', ' . $p['nom'] . ' — ' . rec_t( 'marque', $l ),
		'desc'  => trim( strip_tags( rec_texte_service( $r['service'], $p, $v, $l ) ) ),
	);
}

/** Les variantes de langue d'une page, pour hreflang ET pour le selecteur. */
function rec_variantes( array $r ): array {
	$out = array();
	foreach ( array_keys( rec_langues() ) as $l ) {
		switch ( $r['type'] ) {
			case 'pays':
				$u = rec_url( $l, $r['pays']['limace'] );
				break;
			case 'ville':
				$u = rec_url( $l, $r['pays']['limace'], $r['ville']['limace'] );
				break;
			case 'service':
				$u = rec_url( $l, $r['pays']['limace'], $r['ville']['limace'], $r['service'] );
				break;
			default:
				$u = rec_url( $l );
		}
		$out[ $l ] = $u;
	}
	return $out;
}

/**
 * Le bloc <head>.
 *
 * @param array $r         la route
 * @param bool  $indexable le verdict de la porte de qualite
 */
function rec_head( array $r, bool $indexable = true ): string {
	$l    = $r['langue'] ?? rec_langue_defaut();
	$m    = rec_meta( $r );
	$base = rec_base();
	$vars = rec_variantes( $r );
	$moi  = $vars[ $l ];

	$h  = '<meta charset="utf-8">' . "\n";
	$h .= '<meta name="viewport" content="width=device-width,initial-scale=1">' . "\n";
	$h .= '<title>' . htmlspecialchars( $m['titre'], ENT_QUOTES ) . '</title>' . "\n";
	$h .= '<meta name="description" content="' . htmlspecialchars( $m['desc'], ENT_QUOTES ) . '">' . "\n";

	// La canonique de la page, sur elle-meme. Toujours.
	$h .= '<link rel="canonical" href="' . htmlspecialchars( $base . $moi, ENT_QUOTES ) . '">' . "\n";

	if ( ! $indexable ) {
		// La porte de qualite a dit non. La page reste lisible et reliee, elle
		// n'entre simplement pas dans l'index.
		$h .= '<meta name="robots" content="noindex,follow">' . "\n";
	}

	foreach ( $vars as $lg => $u ) {
		$h .= '<link rel="alternate" hreflang="' . $lg . '" href="'
			. htmlspecialchars( $base . $u, ENT_QUOTES ) . '">' . "\n";
	}
	$h .= '<link rel="alternate" hreflang="x-default" href="'
		. htmlspecialchars( $base . $vars[ rec_langue_defaut() ], ENT_QUOTES ) . '">' . "\n";

	$h .= '<meta property="og:type" content="website">' . "\n";
	$h .= '<meta property="og:title" content="' . htmlspecialchars( $m['titre'], ENT_QUOTES ) . '">' . "\n";
	$h .= '<meta property="og:description" content="' . htmlspecialchars( $m['desc'], ENT_QUOTES ) . '">' . "\n";
	$h .= '<meta property="og:url" content="' . htmlspecialchars( $base . $moi, ENT_QUOTES ) . '">' . "\n";

	$h .= rec_schema( $r, $base );
	return $h;
}

/** Le fil d'Ariane et la FAQ en donnees structurees. */
function rec_schema( array $r, string $base ): string {
	$l     = $r['langue'] ?? rec_langue_defaut();
	$fil   = array();
	$pos   = 1;
	$pousse = function ( $nom, $url ) use ( &$fil, &$pos, $base ) {
		$fil[] = array( '@type' => 'ListItem', 'position' => $pos++,
			'name' => $nom, 'item' => $base . $url );
	};

	$pousse( rec_t( 'accueil', $l ), rec_url( $l ) );
	if ( isset( $r['pays'] ) ) {
		$pousse( $r['pays']['nom'], rec_url( $l, $r['pays']['limace'] ) );
	}
	if ( isset( $r['ville'] ) ) {
		$pousse( $r['ville']['nom'], rec_url( $l, $r['pays']['limace'], $r['ville']['limace'] ) );
	}
	if ( isset( $r['service'] ) ) {
		$pousse( strip_tags( rec_t( rec_service_cle( $r['service'] ), $l ) ),
			rec_url( $l, $r['pays']['limace'], $r['ville']['limace'], $r['service'] ) );
	}

	$blocs = array( array(
		'@context' => 'https://schema.org', '@type' => 'BreadcrumbList',
		'itemListElement' => $fil,
	) );

	// La FAQ n'est declaree QUE si elle est reellement sur la page.
	if ( 'ville' === $r['type'] ) {
		$qs = rec_questions( $r['pays'], $r['ville'], $l );
		if ( $qs ) {
			$blocs[] = array(
				'@context' => 'https://schema.org', '@type' => 'FAQPage',
				'mainEntity' => array_map( function ( $x ) {
					return array( '@type' => 'Question', 'name' => $x['q'],
						'acceptedAnswer' => array( '@type' => 'Answer', 'text' => $x['r'] ) );
				}, $qs ),
			);
		}
	}

	$out = '';
	foreach ( $blocs as $b ) {
		$out .= '<script type="application/ld+json">'
			. json_encode( $b, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
			. '</script>' . "\n";
	}
	return $out;
}
