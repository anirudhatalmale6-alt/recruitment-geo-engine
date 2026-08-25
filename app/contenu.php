<?php
/**
 * LES MODULES EDITORIAUX. C'est ici que se joue tout le projet.
 *
 * Le cahier des charges le dit lui-meme : « create useful local recruitment
 * resources—not doorway pages or thin content ». Trois mille pages qui
 * different par le nom de la ville sont des pages satellites, et la sanction
 * ne tombe pas sur la page : elle tombe sur le domaine.
 *
 * REGLE ABSOLUE DE CE FICHIER : chaque phrase produite ici est DERIVEE d'une
 * donnee reelle de la ville — sa population, son rang, sa region, son fuseau,
 * les langues de son pays, sa part de la population urbaine. Aucune phrase
 * n'est tiree au sort dans une liste de formules interchangeables, et aucun
 * chiffre n'est estime. Deux villes ont un texte different parce qu'elles SONT
 * differentes, pas parce qu'un generateur a pioche une autre variante.
 *
 * Consequence assumee : une ville dont on ne sait presque rien produit une
 * page courte. C'est le bon comportement — la porte de qualite la garde alors
 * hors de l'index au lieu de la gonfler avec du remplissage.
 */

declare( strict_types = 1 );

/** La tranche de taille d'une ville. Sert de charniere a plusieurs modules. */
function rec_tranche( int $pop ): string {
	if ( $pop >= 5000000 ) { return 'megapole'; }
	if ( $pop >= 1000000 ) { return 'grande'; }
	if ( $pop >= 300000 )  { return 'moyenne'; }
	if ( $pop >= 100000 )  { return 'petite'; }
	return 'minuscule';
}

/**
 * Le decalage d'une ville avec l'heure de reference des donneurs d'ordre.
 * Utile pour de vrai : un centre d'appels se vend sur la couverture horaire.
 */
function rec_decalage( string $fuseau, string $reference = 'Europe/Paris' ): ?int {
	if ( '' === $fuseau ) {
		return null;
	}
	try {
		$a = new DateTimeZone( $fuseau );
		$b = new DateTimeZone( $reference );
		$q = new DateTime( 'now', $b );
		return (int) round( ( $a->getOffset( $q ) - $b->getOffset( $q ) ) / 3600 );
	} catch ( Exception $e ) {
		return null;
	}
}

/**
 * « -1 heure », « +3 heures ». Le pluriel se decide a 1, pas a 0 : « -1 heures »
 * est le genre de detail qui fait lire une page comme une page traduite a la
 * machine, et il y en a trois mille.
 */
function rec_heures( int $dec, string $langue ): string {
	$n   = sprintf( '%+d', $dec );
	$mot = array(
		'en' => abs( $dec ) <= 1 ? 'hour' : 'hours',
		'fr' => abs( $dec ) <= 1 ? 'heure' : 'heures',
		'es' => abs( $dec ) <= 1 ? 'hora' : 'horas',
	)[ $langue ];
	return $n . ' ' . $mot;
}

/**
 * Le chapo de la page ville. Assemble a partir de faits, phrase par phrase.
 * Chaque phrase n'apparait que si la donnee qui la justifie existe.
 */
function rec_chapo( array $pays, array $ville, string $langue ): string {
	$pop     = (int) $ville['population'];
	$tranche = rec_tranche( $pop );
	$rang    = (int) $ville['rang_pays'];
	$part    = (float) $ville['part_urbaine'];
	$n       = rec_nombre( $pop, $langue );
	$vn      = $ville['nom'];
	$p = array();

	// 1. Ce qu'elle pese, et par rapport a quoi.
	$taille = array(
		'megapole'  => array(
			'en' => '%s is one of the largest labour markets %s, with %s residents.',
			'fr' => '%s est l’un des plus grands bassins d’emploi %s, avec %s habitants.',
			'es' => '%s es uno de los mayores mercados laborales %s, con %s habitantes.',
		),
		'grande'    => array(
			'en' => '%s is a major hiring centre %s, with %s residents.',
			'fr' => '%s est un pôle de recrutement majeur %s, avec %s habitants.',
			'es' => '%s es un centro de contratación importante %s, con %s habitantes.',
		),
		'moyenne'   => array(
			'en' => '%s is a mid-sized city %s, with %s residents.',
			'fr' => '%s est une ville moyenne %s, avec %s habitants.',
			'es' => '%s es una ciudad mediana %s, con %s habitantes.',
		),
		'petite'    => array(
			'en' => '%s is a smaller market %s, with %s residents.',
			'fr' => '%s est un marché de taille réduite %s, avec %s habitants.',
			'es' => '%s es un mercado pequeño %s, con %s habitantes.',
		),
		'minuscule' => array(
			'en' => '%s is a small town %s, with %s residents.',
			'fr' => '%s est une petite ville %s, avec %s habitants.',
			'es' => '%s es una población pequeña %s, con %s habitantes.',
		),
	);
	$p[] = sprintf( $taille[ $tranche ][ $langue ], $vn, rec_de_pays( $pays, $langue ), $n );

	// 2. Sa place dans le pays : capitale, premiere ville, ou rang.
	if ( ! empty( $ville['capitale'] ) ) {
		$p[] = array(
			'en' => 'It is the national capital, which concentrates public bodies, head offices and the administrative functions that go with them.',
			'fr' => 'C’est la capitale nationale, ce qui y concentre administrations, sièges sociaux et les fonctions support qui vont avec.',
			'es' => 'Es la capital del país, lo que concentra administración, sedes centrales y las funciones de apoyo asociadas.',
		)[ $langue ];
	} elseif ( 1 === $rang ) {
		$p[] = array(
			'en' => 'It is the country’s largest city without being its capital — often the sign of an economic centre distinct from the political one.',
			'fr' => 'C’est la plus grande ville du pays sans en être la capitale — souvent le signe d’un centre économique distinct du centre politique.',
			'es' => 'Es la ciudad más grande del país sin ser la capital, señal habitual de un centro económico distinto del político.',
		)[ $langue ];
	} else {
		$p[] = sprintf( array(
			'en' => 'It ranks %d among the cities we cover %s.',
			'fr' => 'Elle occupe le %de rang parmi les villes que nous couvrons %s.',
			'es' => 'Ocupa el puesto %d entre las ciudades que cubrimos %s.',
		)[ $langue ], $rang, rec_de_pays( $pays, $langue ) );
	}

	// 3. Son poids relatif — seulement s'il est reellement notable, ET
	// seulement si le pays compte assez de villes connues pour que la part
	// veuille dire quelque chose. Sur un territoire dont le jeu de donnees ne
	// connait qu'une ville, la part vaut mecaniquement 100 % : ce n'est pas un
	// fait sur le pays, c'est un fait sur notre couverture.
	$assez_de_villes = count( $pays['villes'] ) >= 3;
	if ( ! $assez_de_villes ) {
		return implode( ' ', $p );
	}
	if ( $part >= 0.20 ) {
		$p[] = sprintf( array(
			'en' => 'Roughly %d%% of the country’s urban population lives here, so an outsourcing plan for this country usually starts in this city.',
			'fr' => 'Environ %d %% de la population urbaine du pays y vit : un plan d’externalisation sur ce pays commence en général ici.',
			'es' => 'Aquí vive alrededor del %d%% de la población urbana del país, por lo que un plan de externalización en este país suele empezar en esta ciudad.',
		)[ $langue ], (int) round( $part * 100 ) );
	} elseif ( $part <= 0.02 && 'minuscule' !== $tranche ) {
		$p[] = array(
			'en' => 'It holds a small share of the country’s urban population, so it works better as a secondary delivery site than as a first location.',
			'fr' => 'Elle ne représente qu’une faible part de la population urbaine du pays : elle fonctionne mieux en site secondaire qu’en première implantation.',
			'es' => 'Representa una parte pequeña de la población urbana del país: funciona mejor como sede secundaria que como primera ubicación.',
		)[ $langue ];
	}

	return implode( ' ', $p );
}

/**
 * « in Canada », « au Canada », « en Canadá ».
 *
 * La forme complete est PRE-CALCULEE par geo/noms.py, pas devinee ici. Le
 * francais demande quatre articles — au / en / aux / a — dont le choix depend
 * du genre, du nombre et de l'initiale du nom, et aucun des trois n'est dans
 * le jeu de donnees geographique. La regle plus ses exceptions vit donc dans
 * un script qui imprime ses 243 resultats pour relecture, et le site se
 * contente de lire le resultat.
 */
function rec_de_pays( array $pays, string $langue ): string {
	$iso = (string) ( $pays['iso'] ?? '' );
	$n   = rec_noms_pays();
	if ( isset( $n[ $iso ]['dans'][ $langue ] ) ) {
		return $n[ $iso ]['dans'][ $langue ];
	}
	$nom = rec_nom_pays( $pays, $langue );
	return ( 'en' === $langue ? 'in ' : 'en ' ) . $nom;
}

/**
 * « Ce que cela change pour recruter ici » : des consequences, pas des
 * adjectifs. Chaque point sort d'une donnee mesuree.
 */
function rec_consequences( array $pays, array $ville, string $langue ): array {
	$out  = array();
	$pop  = (int) $ville['population'];
	$tr   = rec_tranche( $pop );

	// Profondeur du vivier.
	$out[] = array(
		'megapole'  => array(
			'en' => 'Deep talent pool, and deep competition for it: pay benchmarks move fast and counter-offers are common.',
			'fr' => 'Vivier profond, concurrence profonde elle aussi : les niveaux de salaire bougent vite et les contre-offres sont fréquentes.',
			'es' => 'Cantera amplia y competencia igual de amplia: los salarios se mueven rápido y las contraofertas son habituales.',
		),
		'grande'    => array(
			'en' => 'Enough volume to fill most roles locally, without the salary escalation of the very largest cities.',
			'fr' => 'Assez de volume pour pourvoir la plupart des postes localement, sans l’emballement salarial des très grandes villes.',
			'es' => 'Volumen suficiente para cubrir la mayoría de puestos localmente, sin la escalada salarial de las grandes urbes.',
		),
		'moyenne'   => array(
			'en' => 'Specialist roles usually need sourcing from outside the city; generalist and volume roles fill locally.',
			'fr' => 'Les postes spécialisés demandent en général un sourcing hors de la ville ; les postes généralistes et de volume se pourvoient sur place.',
			'es' => 'Los perfiles especializados suelen requerir búsqueda fuera de la ciudad; los generalistas y de volumen se cubren en local.',
		),
		'petite'    => array(
			'en' => 'Plan on relocation or remote for anything specialist. Volume hiring is possible but slower to reach target.',
			'fr' => 'Prévoir mobilité ou télétravail pour tout poste spécialisé. Le recrutement en volume est possible mais met plus de temps à atteindre la cible.',
			'es' => 'Cuente con reubicación o teletrabajo para perfiles especializados. La contratación masiva es posible, pero tarda más en llegar al objetivo.',
		),
		'minuscule' => array(
			'en' => 'Too small for volume hiring on its own. It works as a satellite site next to a larger city.',
			'fr' => 'Trop petite pour un recrutement en volume à elle seule. Elle fonctionne en site satellite d’une ville plus grande.',
			'es' => 'Demasiado pequeña para contratación masiva por sí sola. Funciona como sede satélite de una ciudad mayor.',
		),
	)[ $tr ][ $langue ];

	// Couverture horaire — un argument reel pour un centre d'appels.
	$dec = rec_decalage( (string) ( $ville['fuseau'] ?? '' ) );
	if ( null !== $dec ) {
		if ( 0 === $dec ) {
			$out[] = array(
				'en' => 'Same working hours as Western Europe, so a team here covers a European day with no shift gymnastics.',
				'fr' => 'Mêmes horaires que l’Europe de l’Ouest : une équipe ici couvre une journée européenne sans gymnastique d’horaires.',
				'es' => 'Mismo horario que Europa occidental: un equipo aquí cubre la jornada europea sin malabares de turnos.',
			)[ $langue ];
		} elseif ( abs( $dec ) <= 3 ) {
			$out[] = sprintf( array(
				'en' => 'At %s from Western Europe, a single shift still overlaps most of a European working day.',
				'fr' => 'À %s de l’Europe de l’Ouest, une seule équipe recouvre encore l’essentiel d’une journée européenne.',
				'es' => 'A %s de Europa occidental, un solo turno todavía solapa la mayor parte de la jornada europea.',
			)[ $langue ], rec_heures( $dec, $langue ) );
		} else {
			$out[] = sprintf( array(
				'en' => 'At %s from Western Europe, this is a night-shift or follow-the-sun location rather than a same-hours one.',
				'fr' => 'À %s de l’Europe de l’Ouest, c’est un site de nuit ou de relais horaire plutôt qu’un site en horaires alignés.',
				'es' => 'A %s de Europa occidental, es una ubicación de turno de noche o de relevo horario, no de horario alineado.',
			)[ $langue ], rec_heures( $dec, $langue ) );
		}
	}

	// Les langues, telles que le jeu de donnees les connait.
	$langues = $pays['langues'] ?? array();
	if ( count( $langues ) > 1 ) {
		$out[] = sprintf( array(
			'en' => 'The country records %d official or widely used languages, which matters directly for multilingual call-center roles.',
			'fr' => 'Le pays compte %d langues officielles ou largement pratiquées, ce qui compte directement pour les postes multilingues en centre d’appels.',
			'es' => 'El país registra %d idiomas oficiales o de uso extendido, algo decisivo para los puestos multilingües de call center.',
		)[ $langue ], count( $langues ) );
	} elseif ( 1 === count( $langues ) ) {
		$out[] = sprintf( array(
			'en' => 'One main working language (%s): a second language is a recruitment constraint here, not a given.',
			'fr' => 'Une langue de travail principale (%s) : une seconde langue est ici une contrainte de recrutement, pas un acquis.',
			'es' => 'Un idioma de trabajo principal (%s): un segundo idioma es aquí una restricción de contratación, no algo dado.',
		)[ $langue ], strtoupper( $langues[0] ) );
	}

	// La densite du reseau autour d'elle.
	$autres = max( 0, count( $pays['villes'] ) - 1 );
	if ( $autres >= 10 ) {
		$out[] = sprintf( array(
			'en' => '%d other cities in the same country are covered, so overflow volume can be split rather than forced into one site.',
			'fr' => '%d autres villes du même pays sont couvertes : un volume excédentaire peut être réparti au lieu d’être forcé sur un seul site.',
			'es' => 'Se cubren otras %d ciudades del país, así que el exceso de volumen puede repartirse en lugar de forzarse en una sola sede.',
		)[ $langue ], $autres );
	}

	return $out;
}

/**
 * Les questions. Elles portent sur CETTE ville et se repondent avec ses
 * donnees : une FAQ dont les reponses seraient vraies partout ne sert a rien,
 * ni au lecteur ni au referencement.
 */
function rec_questions( array $pays, array $ville, string $langue ): array {
	$q   = array();
	$vn  = $ville['nom'];
	$pop = (int) $ville['population'];
	$tr  = rec_tranche( $pop );

	$q[] = array(
		'q' => sprintf( array(
			'en' => 'How big is the hiring pool in %s?',
			'fr' => 'Quelle est la taille du vivier à %s ?',
			'es' => '¿Qué tamaño tiene la cantera en %s?',
		)[ $langue ], $vn ),
		'r' => sprintf( array(
			'en' => '%s has %s residents and ranks %d among the cities we cover %s. That places it in the "%s" band we use to size a campaign.',
			'fr' => '%s compte %s habitants et occupe le rang %d parmi les villes couvertes %s. Cela la place dans la catégorie « %s » que nous utilisons pour dimensionner une campagne.',
			'es' => '%s tiene %s habitantes y ocupa el puesto %d entre las ciudades que cubrimos %s. Eso la sitúa en la banda «%s» que usamos para dimensionar una campaña.',
		)[ $langue ], $vn, rec_nombre( $pop, $langue ), (int) $ville['rang_pays'],
			rec_de_pays( $pays, $langue ), rec_tranche_nom( $tr, $langue ) ),
	);

	$dec = rec_decalage( (string) ( $ville['fuseau'] ?? '' ) );
	if ( null !== $dec && '' !== (string) ( $ville['fuseau'] ?? '' ) ) {
		$q[] = array(
			'q' => sprintf( array(
				'en' => 'Can a team in %s cover European business hours?',
				'fr' => 'Une équipe à %s peut-elle couvrir les horaires européens ?',
				'es' => '¿Puede un equipo en %s cubrir el horario europeo?',
			)[ $langue ], $vn ),
			'r' => sprintf( array(
				'en' => 'The city is on %s, %s from Western Europe. %s',
				'fr' => 'La ville est sur %s, soit %s par rapport à l’Europe de l’Ouest. %s',
				'es' => 'La ciudad está en %s, %s respecto a Europa occidental. %s',
			)[ $langue ], $ville['fuseau'], rec_heures( $dec, $langue ),
				abs( $dec ) <= 3
					? array( 'en' => 'A single day shift covers most of it.',
						'fr' => 'Une seule équipe de jour en couvre l’essentiel.',
						'es' => 'Un solo turno de día cubre la mayor parte.' )[ $langue ]
					: array( 'en' => 'Full coverage needs a second shift.',
						'fr' => 'Une couverture complète demande une seconde équipe.',
						'es' => 'La cobertura completa requiere un segundo turno.' )[ $langue ]
			),
		);
	}

	$langues = $pays['langues'] ?? array();
	if ( $langues ) {
		$q[] = array(
			'q' => sprintf( array(
				'en' => 'Which languages can we hire for in %s?',
				'fr' => 'Dans quelles langues peut-on recruter à %s ?',
				'es' => '¿En qué idiomas se puede contratar en %s?',
			)[ $langue ], $vn ),
			'r' => sprintf( array(
				'en' => 'The dataset records %s for %s. Anything beyond that is a sourcing constraint we size before committing to a volume.',
				'fr' => 'Le jeu de données enregistre %s pour %s. Au-delà, c’est une contrainte de sourcing que nous chiffrons avant de nous engager sur un volume.',
				'es' => 'El conjunto de datos registra %s para %s. Más allá, es una restricción de búsqueda que dimensionamos antes de comprometer un volumen.',
			)[ $langue ], strtoupper( implode( ', ', $langues ) ), rec_nom_pays( $pays, $langue ) ),
		);
	}

	if ( 'minuscule' === $tr || 'petite' === $tr ) {
		$q[] = array(
			'q' => sprintf( array(
				'en' => 'Is %s big enough for a bulk hiring campaign?',
				'fr' => '%s est-elle assez grande pour une campagne de recrutement en volume ?',
				'es' => '¿Es %s suficientemente grande para una campaña de contratación masiva?',
			)[ $langue ], $vn ),
			'r' => array(
				'en' => 'On its own, not for large volumes. We would pair it with a larger city in the same country and split the target rather than promise a number this market cannot supply.',
				'fr' => 'Seule, pas pour de gros volumes. Nous l’associerions à une ville plus grande du même pays et répartirions la cible, plutôt que de promettre un chiffre que ce marché ne peut pas fournir.',
				'es' => 'Por sí sola, no para grandes volúmenes. La combinaríamos con una ciudad mayor del mismo país y repartiríamos el objetivo, en lugar de prometer una cifra que este mercado no puede dar.',
			)[ $langue ],
		);
	}

	return $q;
}

function rec_tranche_nom( string $tr, string $langue ): string {
	return array(
		'megapole'  => array( 'en' => 'very large', 'fr' => 'très grande', 'es' => 'muy grande' ),
		'grande'    => array( 'en' => 'large', 'fr' => 'grande', 'es' => 'grande' ),
		'moyenne'   => array( 'en' => 'mid-sized', 'fr' => 'moyenne', 'es' => 'mediana' ),
		'petite'    => array( 'en' => 'small', 'fr' => 'petite', 'es' => 'pequeña' ),
		'minuscule' => array( 'en' => 'very small', 'fr' => 'très petite', 'es' => 'muy pequeña' ),
	)[ $tr ][ $langue ];
}

/** Le texte propre a un metier, dans cette ville. */
function rec_texte_service( string $service, array $pays, array $ville, string $langue ): string {
	$vn  = $ville['nom'];
	$tr  = rec_tranche( (int) $ville['population'] );
	$dec = rec_decalage( (string) ( $ville['fuseau'] ?? '' ) );

	if ( 'call-center' === $service ) {
		$base = sprintf( array(
			'en' => 'Outsourcing a call-center or BPO team to %s is decided by three things: the languages available locally, the shift pattern the time zone allows, and retention. ',
			'fr' => 'Externaliser une équipe de centre d’appels ou BPO à %s se joue sur trois choses : les langues disponibles sur place, le rythme d’équipes que permet le fuseau horaire, et la rétention. ',
			'es' => 'Externalizar un equipo de call center o BPO en %s se decide por tres cosas: los idiomas disponibles, el patrón de turnos que permite la zona horaria y la retención. ',
		)[ $langue ], $vn );
		if ( null !== $dec ) {
			$base .= sprintf( array(
				'en' => 'At %s from Western Europe, %s',
				'fr' => 'À %s de l’Europe de l’Ouest, %s',
				'es' => 'A %s de Europa occidental, %s',
			)[ $langue ], rec_heures( $dec, $langue ), abs( $dec ) <= 3
				? array( 'en' => 'one day shift already covers the client day.',
					'fr' => 'une équipe de jour couvre déjà la journée du client.',
					'es' => 'un turno de día ya cubre la jornada del cliente.' )[ $langue ]
				: array( 'en' => 'the site earns its place on out-of-hours cover.',
					'fr' => 'le site tire sa valeur de la couverture hors horaires.',
					'es' => 'la sede aporta valor en la cobertura fuera de horario.' )[ $langue ] );
		}
		return $base;
	}

	if ( 'bulk-hiring' === $service ) {
		return sprintf( array(
			'en' => 'Outsourced high-volume hiring in %s is a throughput problem, not a search problem. The question is how many qualified candidates this market can put in front of you per week, and for a %s market that number sets the deadline — not the other way round.',
			'fr' => 'Le recrutement en volume externalisé à %s est un problème de débit, pas de recherche. La question est le nombre de candidats qualifiés que ce marché peut présenter par semaine ; sur un marché %s, c’est ce nombre qui fixe l’échéance, pas l’inverse.',
			'es' => 'La contratación masiva externalizada en %s es un problema de caudal, no de búsqueda. La pregunta es cuántos candidatos cualificados puede presentar este mercado por semana; en un mercado %s, esa cifra fija el plazo, y no al revés.',
		)[ $langue ], $vn, rec_tranche_nom( $tr, $langue ) );
	}

	return sprintf( array(
		'en' => 'Outsourced corporate hiring in %s means a small number of decisions that each matter, which is why the decision stays with you and only the process moves. In a %s market the shortlist is built by direct approach rather than by advertising, because the people worth hiring are already employed.',
		'fr' => 'Le recrutement de cadres externalisé à %s, c’est un petit nombre de décisions qui comptent chacune : la décision reste chez vous, seul le processus se délègue. Sur un marché %s, la liste courte se construit par approche directe plutôt que par annonce — les personnes qui valent la peine sont déjà en poste.',
		'es' => 'La selección corporativa externalizada en %s son pocas decisiones, y todas importan: la decisión sigue siendo suya y solo se delega el proceso. En un mercado %s, la lista corta se construye por aproximación directa y no por anuncio, porque quien merece la pena ya está empleado.',
	)[ $langue ], $vn, rec_tranche_nom( $tr, $langue ) );
}
