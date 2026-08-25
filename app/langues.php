<?php
/**
 * Les langues du site.
 *
 * TROIS LANGUES, ECRITES A LA MAIN : anglais, francais, espagnol. Pas
 * quarante. Le jeu de donnees connait les langues officielles de chaque pays,
 * et il serait facile d'afficher « disponible en 40 langues » en passant le
 * texte dans une machine — sauf qu'une page de recrutement mal traduite fait
 * fuir le client au lieu de le convertir, et qu'une balise hreflang qui pointe
 * vers une traduction inexistante est une erreur d'indexation, pas une
 * fonctionnalite.
 *
 * On declare donc EXACTEMENT les langues qu'on a ecrites. En ajouter une, c'est
 * ajouter un tableau ici et rien d'autre : le routage, les hreflang, le plan de
 * site et le selecteur suivent tout seuls.
 */

declare( strict_types = 1 );

function rec_langues(): array {
	return array(
		'en' => array( 'nom' => 'English',  'htmllang' => 'en', 'dir' => 'ltr' ),
		'fr' => array( 'nom' => 'Français', 'htmllang' => 'fr', 'dir' => 'ltr' ),
		'es' => array( 'nom' => 'Español',  'htmllang' => 'es', 'dir' => 'ltr' ),
	);
}

function rec_langue_defaut(): string {
	return 'en';
}

/**
 * Les chaines. Une cle, trois langues, cote a cote : une traduction qui manque
 * se voit a l'oeil nu en lisant le fichier, ce qui n'est pas le cas quand
 * chaque langue vit dans son propre fichier.
 *
 * Les chaines a trous utilisent %s / %d dans le MEME ORDRE partout, sinon une
 * langue afficherait la population la ou une autre affiche le nom de la ville.
 */
function rec_chaines(): array {
	return array(
		'marque'            => array( 'en' => 'Hiring Grid', 'fr' => 'Hiring Grid', 'es' => 'Hiring Grid' ),
		'nav_pays'          => array( 'en' => 'Countries', 'fr' => 'Pays', 'es' => 'Países' ),
		'nav_services'      => array( 'en' => 'Services', 'fr' => 'Services', 'es' => 'Servicios' ),
		'nav_employeurs'    => array( 'en' => 'For employers', 'fr' => 'Employeurs', 'es' => 'Empresas' ),

		'svc_corporate'     => array( 'en' => 'Corporate recruitment', 'fr' => 'Recrutement cadres', 'es' => 'Selección corporativa' ),
		'svc_call_center'   => array( 'en' => 'Call center &amp; BPO', 'fr' => 'Centres d’appels &amp; BPO', 'es' => 'Call center y BPO' ),
		'svc_bulk_hiring'   => array( 'en' => 'Bulk hiring', 'fr' => 'Recrutement en volume', 'es' => 'Contratación masiva' ),

		'svc_corporate_d'   => array(
			'en' => 'Executives, managers and specialists. Search, assessment and offer management.',
			'fr' => 'Cadres, managers et specialistes. Approche directe, evaluation et gestion des offres.',
			'es' => 'Directivos, mandos y especialistas. Búsqueda, evaluación y gestión de ofertas.',
		),
		'svc_call_center_d' => array(
			'en' => 'Customer service, telesales and support. Language, schedule and retention are the constraints.',
			'fr' => 'Service client, televente et support. La langue, les horaires et la retention sont les contraintes.',
			'es' => 'Atención al cliente, televenta y soporte. Idioma, turnos y retención son las limitaciones.',
		),
		'svc_bulk_hiring_d' => array(
			'en' => 'Fifty to several thousand hires on a fixed deadline, with an industrialised pipeline.',
			'fr' => 'De cinquante a plusieurs milliers d’embauches a date fixe, avec un processus industrialise.',
			'es' => 'De cincuenta a varios miles de contrataciones con fecha fija y un proceso industrializado.',
		),

		'h1_pays'           => array( 'en' => 'Recruitment in %s', 'fr' => 'Recrutement %s', 'es' => 'Selección de personal en %s' ),
		'h1_ville'          => array( 'en' => 'Recruitment in %s', 'fr' => 'Recrutement à %s', 'es' => 'Selección de personal en %s' ),
		'h1_ville_svc'      => array( 'en' => '%s in %s', 'fr' => '%s à %s', 'es' => '%s en %s' ),

		'villes_du_pays'    => array( 'en' => 'The %d cities we cover %s', 'fr' => 'Les %d villes couvertes %s', 'es' => 'Las %d ciudades que cubrimos %s' ),
		'marche_local'      => array( 'en' => 'The local hiring picture', 'fr' => 'Le marché local', 'es' => 'El mercado local' ),
		'pourquoi_ici'      => array( 'en' => 'What this means for hiring here', 'fr' => 'Ce que cela change pour recruter ici', 'es' => 'Qué implica para contratar aquí' ),
		'nos_services_ici'  => array( 'en' => 'What we run in %s', 'fr' => 'Ce que nous opérons à %s', 'es' => 'Lo que operamos en %s' ),
		'villes_proches'    => array( 'en' => 'Other cities in %s', 'fr' => 'Autres villes %s', 'es' => 'Otras ciudades de %s' ),
		'questions'         => array( 'en' => 'Questions employers ask about %s', 'fr' => 'Ce que les employeurs demandent sur %s', 'es' => 'Lo que preguntan las empresas sobre %s' ),

		'cta_titre'         => array( 'en' => 'Hire in %s', 'fr' => 'Recruter à %s', 'es' => 'Contratar en %s' ),
		'cta_texte'         => array(
			'en' => 'Tell us the role, the volume and the deadline. We come back with a plan and a cost, not a brochure.',
			'fr' => 'Dites-nous le poste, le volume et l’echeance. Nous revenons avec un plan et un cout, pas une plaquette.',
			'es' => 'Díganos el puesto, el volumen y el plazo. Respondemos con un plan y un coste, no con un folleto.',
		),
		'cta_bouton'        => array( 'en' => 'Request a hiring plan', 'fr' => 'Demander un plan de recrutement', 'es' => 'Solicitar un plan de contratación' ),

		'f_population'      => array( 'en' => 'Population', 'fr' => 'Population', 'es' => 'Población' ),
		'f_rang'            => array( 'en' => 'Rank in %s', 'fr' => 'Rang %s', 'es' => 'Puesto en %s' ),
		'f_region'          => array( 'en' => 'Region', 'fr' => 'Région', 'es' => 'Región' ),
		'f_fuseau'          => array( 'en' => 'Time zone', 'fr' => 'Fuseau horaire', 'es' => 'Zona horaria' ),
		'f_langues'         => array( 'en' => 'Working languages', 'fr' => 'Langues de travail', 'es' => 'Idiomas de trabajo' ),
		'f_part'            => array( 'en' => 'Share of the country’s urban population', 'fr' => 'Part de la population urbaine du pays', 'es' => 'Parte de la población urbana del país' ),
		'f_capitale'        => array( 'en' => 'National capital', 'fr' => 'Capitale nationale', 'es' => 'Capital nacional' ),

		'donnees_titre'     => array( 'en' => 'Where these figures come from', 'fr' => 'D’où viennent ces chiffres', 'es' => 'De dónde vienen estas cifras' ),
		'donnees_texte'     => array(
			'en' => 'Population, region and time zone come from the open GeoNames dataset. Ranking revision %s. Nothing on this page is estimated: a figure we do not have is not shown.',
			'fr' => 'Population, région et fuseau horaire viennent du jeu de données ouvert GeoNames. Révision du classement %s. Rien n’est estimé ici : un chiffre que nous n’avons pas n’est pas affiché.',
			'es' => 'Población, región y zona horaria proceden del conjunto abierto GeoNames. Revisión del ranking %s. Nada se estima aquí: una cifra que no tenemos no se muestra.',
		),
		'pas_indexee'       => array(
			'en' => 'This page is not indexed yet: it is missing local data.',
			'fr' => 'Cette page n’est pas encore indexée : il lui manque des données locales.',
			'es' => 'Esta página aún no está indexada: le faltan datos locales.',
		),
		'retour_pays'       => array( 'en' => 'All cities in %s', 'fr' => 'Toutes les villes %s', 'es' => 'Todas las ciudades de %s' ),
		'accueil'           => array( 'en' => 'Home', 'fr' => 'Accueil', 'es' => 'Inicio' ),
	);
}

/** Une chaine traduite. Une cle inconnue rend la cle : ca se voit, ca ne casse pas. */
function rec_t( string $cle, string $langue, ...$args ): string {
	$c = rec_chaines();
	if ( ! isset( $c[ $cle ] ) ) {
		return '[' . $cle . ']';
	}
	$s = $c[ $cle ][ $langue ] ?? $c[ $cle ][ rec_langue_defaut() ];
	return $args ? vsprintf( $s, $args ) : $s;
}

/** Un nombre lisible dans la langue courante. */
function rec_nombre( int $n, string $langue ): string {
	$sep = ( 'en' === $langue ) ? ',' : ( 'es' === $langue ? '.' : ' ' );
	return number_format( $n, 0, '.', $sep );
}
