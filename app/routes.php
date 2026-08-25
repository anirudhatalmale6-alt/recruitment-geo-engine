<?php
/**
 * Le routage et les URL.
 *
 * L'architecture d'adresses vient du cahier des charges, avec la langue en
 * tete parce que le site est international des le premier jour :
 *
 *   /{langue}/recruitment/{pays}/
 *   /{langue}/recruitment/{pays}/{ville}/
 *   /{langue}/recruitment/{pays}/{ville}/{service}/
 *
 * UNE SEULE FONCTION FABRIQUE LES ADRESSES. Les liens internes, les canoniques,
 * les hreflang et le plan de site passent tous par rec_url() : le jour ou la
 * structure change, elle change partout d'un coup. Deux fabricants d'URL, c'est
 * la garantie qu'un jour l'un des deux produira une adresse que l'autre ne
 * reconnait pas.
 */

declare( strict_types = 1 );

function rec_services(): array {
	return array( 'corporate', 'call-center', 'bulk-hiring' );
}

function rec_service_cle( string $service ): string {
	return 'svc_' . str_replace( '-', '_', $service );
}

/** L'adresse d'une page. Le seul endroit ou une URL est ecrite. */
function rec_url( string $langue, ?string $pays = null, ?string $ville = null,
	?string $service = null ): string {
	$p = '/' . $langue;
	if ( null === $pays ) {
		return $p . '/';
	}
	$p .= '/recruitment/' . $pays . '/';
	if ( null === $ville ) {
		return $p;
	}
	$p .= $ville . '/';
	if ( null === $service ) {
		return $p;
	}
	return $p . $service . '/';
}

/** La base absolue du site, deduite de la requete. */
function rec_base(): string {
	$https = ( ! empty( $_SERVER['HTTPS'] ) && 'off' !== $_SERVER['HTTPS'] )
		|| ( ( $_SERVER['SERVER_PORT'] ?? '' ) === '443' );
	$hote  = $_SERVER['HTTP_HOST'] ?? 'localhost';
	return ( $https ? 'https://' : 'http://' ) . $hote;
}

/**
 * Analyse le chemin demande.
 *
 * Rend toujours un tableau avec une cle « type » :
 *   racine | accueil | pays | ville | service | plan | 404
 */
function rec_router( string $chemin ): array {
	$chemin = parse_url( $chemin, PHP_URL_PATH ) ?: '/';
	$bouts  = array_values( array_filter( explode( '/', $chemin ), 'strlen' ) );

	if ( ! $bouts ) {
		// Sans langue dans l'adresse, on redirige : une page servie a deux
		// adresses differentes est un doublon, meme quand c'est la racine.
		return array( 'type' => 'racine' );
	}

	if ( 'sitemap.xml' === $bouts[0] ) {
		return array( 'type' => 'plan' );
	}

	$langue = $bouts[0];
	if ( ! isset( rec_langues()[ $langue ] ) ) {
		return array( 'type' => 'racine' );
	}
	if ( 1 === count( $bouts ) ) {
		return array( 'type' => 'accueil', 'langue' => $langue );
	}

	if ( 'recruitment' !== $bouts[1] ) {
		return array( 'type' => '404', 'langue' => $langue );
	}
	if ( 2 === count( $bouts ) ) {
		return array( 'type' => 'accueil', 'langue' => $langue );
	}

	$pays = rec_pays( $bouts[2] );
	if ( ! $pays ) {
		return array( 'type' => '404', 'langue' => $langue );
	}
	if ( 3 === count( $bouts ) ) {
		return array( 'type' => 'pays', 'langue' => $langue, 'pays' => $pays );
	}

	$ville = rec_ville( $pays, $bouts[3] );
	if ( ! $ville ) {
		return array( 'type' => '404', 'langue' => $langue );
	}
	if ( 4 === count( $bouts ) ) {
		return array( 'type' => 'ville', 'langue' => $langue,
			'pays' => $pays, 'ville' => $ville );
	}

	if ( 5 === count( $bouts ) && in_array( $bouts[4], rec_services(), true ) ) {
		return array( 'type' => 'service', 'langue' => $langue, 'pays' => $pays,
			'ville' => $ville, 'service' => $bouts[4] );
	}

	return array( 'type' => '404', 'langue' => $langue );
}
