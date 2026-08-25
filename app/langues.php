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

		'svc_corporate'     => array( 'en' => 'Corporate recruitment outsourcing', 'fr' => 'Externalisation du recrutement cadres', 'es' => 'Externalización de la selección corporativa' ),
		'svc_call_center'   => array( 'en' => 'Call center &amp; BPO outsourcing', 'fr' => 'Externalisation centres d’appels &amp; BPO', 'es' => 'Externalización de call center y BPO' ),
		'svc_bulk_hiring'   => array( 'en' => 'High-volume hiring outsourcing', 'fr' => 'Externalisation du recrutement en volume', 'es' => 'Externalización de la contratación masiva' ),

		// LE VERBE DE CHAQUE DESCRIPTION EST « NOUS OPERONS », PAS « NOUS
		// TROUVONS ». C'est la difference entre une agence, qui livre des
		// candidats, et une plateforme d'externalisation, qui tient le
		// processus. Le client a corrige exactement ce point.
		'svc_corporate_d'   => array(
			'en' => 'RPO for executives, managers and specialists: we run the sourcing, the assessment and the offer stage, you keep the hiring decision.',
			'fr' => 'RPO pour cadres, managers et spécialistes : nous tenons l’approche directe, l’évaluation et la phase d’offre ; la décision d’embauche reste chez vous.',
			'es' => 'RPO para directivos, mandos y especialistas: operamos la búsqueda, la evaluación y la fase de oferta; la decisión de contratar sigue siendo suya.',
		),
		'svc_call_center_d' => array(
			'en' => 'A customer service, telesales or support team recruited and staffed for you. Language, shift pattern and retention are the constraints, and they are local.',
			'fr' => 'Une équipe service client, télévente ou support recrutée et tenue pour vous. La langue, le rythme d’équipes et la rétention sont les contraintes, et elles sont locales.',
			'es' => 'Un equipo de atención al cliente, televenta o soporte reclutado y gestionado para usted. Idioma, turnos y retención son las restricciones, y son locales.',
		),
		'svc_bulk_hiring_d' => array(
			'en' => 'Fifty to several thousand hires on a fixed deadline. The pipeline, the recruiters and the weekly reporting sit on our side.',
			'fr' => 'De cinquante à plusieurs milliers d’embauches à date fixe. Le processus, les recruteurs et le reporting hebdomadaire sont chez nous.',
			'es' => 'De cincuenta a varios miles de contrataciones con fecha fija. El proceso, los reclutadores y el informe semanal están de nuestro lado.',
		),

		// Le titre pays recoit la forme « dans le pays » COMPLETE, article
		// compris — « in Canada », « au Canada », « en Canadá » — et non le nom
		// nu. C'est geo/noms.py qui la calcule et un humain qui l'a relue.
		'h1_pays'           => array( 'en' => 'Recruitment outsourcing %s', 'fr' => 'Externalisation du recrutement %s', 'es' => 'Externalización de la selección %s' ),
		'h1_ville'          => array( 'en' => 'Recruitment outsourcing in %s', 'fr' => 'Externalisation du recrutement à %s', 'es' => 'Externalización de la selección en %s' ),
		'h1_ville_svc'      => array( 'en' => '%s in %s', 'fr' => '%s à %s', 'es' => '%s en %s' ),

		'villes_du_pays'    => array( 'en' => 'The %d cities we cover %s', 'fr' => 'Les %d villes couvertes %s', 'es' => 'Las %d ciudades que cubrimos %s' ),
		'marche_local'      => array( 'en' => 'The local hiring picture', 'fr' => 'Le marché local', 'es' => 'El mercado local' ),
		'pourquoi_ici'      => array( 'en' => 'What this means for outsourcing here', 'fr' => 'Ce que cela change pour externaliser ici', 'es' => 'Qué implica para externalizar aquí' ),
		'nos_services_ici'  => array( 'en' => 'What we run in %s', 'fr' => 'Ce que nous opérons à %s', 'es' => 'Lo que operamos en %s' ),
		'villes_proches'    => array( 'en' => 'Other cities %s', 'fr' => 'Autres villes %s', 'es' => 'Otras ciudades %s' ),
		'questions'         => array( 'en' => 'Questions employers ask about %s', 'fr' => 'Ce que les employeurs demandent sur %s', 'es' => 'Lo que preguntan las empresas sobre %s' ),

		'cta_titre'         => array( 'en' => 'Outsource your hiring in %s', 'fr' => 'Externaliser votre recrutement à %s', 'es' => 'Externalizar su selección en %s' ),
		'cta_texte'         => array(
			'en' => 'Tell us the role, the volume and the deadline. We come back with the team we would put on it, a timeline and a cost — not a brochure.',
			'fr' => 'Dites-nous le poste, le volume et l’échéance. Nous revenons avec l’équipe que nous y mettrions, un calendrier et un coût — pas une plaquette.',
			'es' => 'Díganos el puesto, el volumen y el plazo. Respondemos con el equipo que asignaríamos, un calendario y un coste, no con un folleto.',
		),
		'cta_bouton'        => array( 'en' => 'Request an outsourcing plan', 'fr' => 'Demander un plan d’externalisation', 'es' => 'Solicitar un plan de externalización' ),

		'f_population'      => array( 'en' => 'Population', 'fr' => 'Population', 'es' => 'Población' ),
		'f_rang'            => array( 'en' => 'Rank %s', 'fr' => 'Rang %s', 'es' => 'Puesto %s' ),
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
		// Les six criteres de la porte de qualite. Ils sont AFFICHES, donc ils
		// se traduisent comme le reste.
		'q_population'      => array(
			'en' => 'Population figure for the city',
			'fr' => 'Population de la ville connue',
			'es' => 'Cifra de población de la ciudad',
		),
		'q_region'          => array(
			'en' => 'Administrative region known (used to rank within the region)',
			'fr' => 'Région administrative connue (sert au rang régional)',
			'es' => 'Región administrativa conocida (sirve para el orden regional)',
		),
		'q_fuseau'          => array(
			'en' => 'Time zone known (working hours, shift cover)',
			'fr' => 'Fuseau horaire connu (horaires, couverture d’équipes)',
			'es' => 'Zona horaria conocida (horarios, cobertura de turnos)',
		),
		'q_langues'         => array(
			'en' => 'Official languages of the country known',
			'fr' => 'Langues officielles du pays connues',
			'es' => 'Idiomas oficiales del país conocidos',
		),
		'q_voisines'        => array(
			'en' => 'At least three other ranked cities to link to',
			'fr' => 'Au moins trois autres villes classées à relier',
			'es' => 'Al menos otras tres ciudades clasificadas que enlazar',
		),
		'q_taille'          => array(
			'en' => 'City large enough to describe a labour market (25 000+)',
			'fr' => 'Ville assez grande pour décrire un marché du travail (25 000+)',
			'es' => 'Ciudad suficientemente grande para describir un mercado laboral (25 000+)',
		),

		'retour_pays'       => array( 'en' => 'All cities %s', 'fr' => 'Toutes les villes %s', 'es' => 'Todas las ciudades %s' ),
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
