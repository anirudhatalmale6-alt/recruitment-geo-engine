# -*- coding: utf-8 -*-
"""Les noms de pays dans les trois langues du site, et l'article qui va avec.

POURQUOI CE FICHIER EXISTE. Le jeu de donnees geographique ne connait qu'un
nom par pays, en anglais. Sur un site dont l'argument est « nous parlons la
langue du marche », un titre espagnol qui annonce « Externalizacion de la
seleccion en Morocco » se disqualifie tout seul.

D'OU VIENNENT LES TRADUCTIONS. De la norme ISO 3166-1 elle-meme, via le
paquet pycountry, qui embarque les catalogues gettext officiels. Ce ne sont
pas des traductions automatiques : c'est la liste normalisee. Hors ligne,
aucun appel reseau.

DEUX PIEGES, MESURES ET CORRIGES ICI.

1. LE NOM ADMINISTRATIF N'EST PAS LE NOM COURANT. La norme dit « Coree,
   Republique de », « Bolivie, etat plurinational de », « Saint-Siege (etat de
   la cite du Vatican) ». Aucun de ces trois ne peut servir de titre a une
   page. On prend donc le nom courant (common_name) quand la norme en fournit
   un, et pour les 18 cas restants — comptes, pas estimes — on ecrit le nom a
   la main dans NOMS_MANUELS.

2. L'ARTICLE EST PLUS DUR QUE LE NOM.
     - anglais : « in Morocco », mais « in THE Netherlands ».
     - espagnol : « en » partout ; omettre l'article est correct. Rien a faire.
     - francais : « au Maroc », « en France », « aux Pays-Bas », « a Cuba ».
       Quatre formes, dont le choix depend du genre, du nombre et de
       l'initiale — dont AUCUN n'est dans le jeu de donnees.

   La regle francaise appliquee, dans cet ordre :
     1. exception explicite (FR_FORCE)              -> la forme ecrite a la main
     2. nom commencant par « Iles »                 -> aux Iles X
     3. nom commencant par « Ile »                  -> a l'Ile X
     4. nom commencant par « Republique »           -> en Republique X
     5. pluriel (liste explicite)                   -> aux X
     6. feminin d'iles et d'Etats sans article      -> a la X / a X
     7. initiale voyelle                            -> en X    (en Iran)
     8. terminaison en -e, hors masculins connus    -> en X    (en France)
     9. le reste                                    -> au X    (au Canada)

CE SCRIPT SE VERIFIE LUI-MEME. Il imprime les 243 pays groupes par article.
C'est le seul moyen honnete de valider deux cent cinquante lignes de
grammaire : une regle qui a l'air juste et une liste qu'on a lue ne sont pas
la meme chose. Les erreurs trouvees a la premiere lecture — « au Guinee-
Bissau », « au Republique du Congo », « aux Bonaire » — venaient toutes de la
regle, pas des donnees.

Usage : python3 noms.py [dossier-de-sortie]
"""
import gettext
import json
import os
import sys

import pycountry

SORTIE = sys.argv[1] if len(sys.argv) > 1 else os.path.join(
    os.path.dirname(os.path.abspath(__file__)), '..', 'data')

# --- 1. les noms que la norme ne donne pas sous une forme utilisable -------
# Ecrits a la main, un par un. Ils sont 18 sur 243 : la liste est courte parce
# qu'elle a ete constituee en imprimant tous les noms contenant une virgule ou
# une parenthese, pas en devinant lesquels poseraient probleme.
NOMS_MANUELS = {
    'AX': {'fr': 'Îles Åland',            'es': 'Islas Åland'},
    'BQ': {'fr': 'Bonaire, Saint-Eustache et Saba', 'es': 'Bonaire, San Eustaquio y Saba'},
    'CC': {'fr': 'Îles Cocos',            'es': 'Islas Cocos'},
    'CD': {'fr': 'République démocratique du Congo', 'es': 'República Democrática del Congo'},
    'CX': {'fr': 'Île Christmas',         'es': 'Isla de Navidad'},
    'FK': {'fr': 'Îles Malouines',        'es': 'Islas Malvinas'},
    'FM': {'fr': 'Micronésie',            'es': 'Micronesia'},
    'KM': {'fr': 'Comores',               'es': 'Comoras'},
    'MF': {'fr': 'Saint-Martin',          'es': 'San Martín'},
    'PS': {'fr': 'Palestine',             'es': 'Palestina'},
    'RE': {'fr': 'La Réunion',            'es': 'La Reunión'},
    'RU': {'fr': 'Russie',                'es': 'Rusia'},
    'SB': {'fr': 'Îles Salomon',          'es': 'Islas Salomón'},
    'SH': {'fr': 'Sainte-Hélène',         'es': 'Santa Elena'},
    'SX': {'fr': 'Sint Maarten',          'es': 'Sint Maarten'},
    'MO': {'fr': 'Macao',                 'es': 'Macao'},
    'VA': {'fr': 'Vatican',               'es': 'Vaticano'},
    'VG': {'fr': 'Îles Vierges britanniques', 'es': 'Islas Vírgenes Británicas'},
    'VI': {'fr': 'Îles Vierges américaines',  'es': 'Islas Vírgenes de los Estados Unidos'},
    # Deux coquilles du catalogue, corrigees : « Marfíl » (accent en trop) et
    # « Äland » (trema au lieu du rond en chef).
    'CI': {'fr': 'Côte d’Ivoire',         'es': 'Costa de Marfil'},
    # Sans correspondance ISO : le jeu de donnees geographique le connait, la
    # norme non.
    'XK': {'fr': 'Kosovo',                'es': 'Kosovo'},
    'SJ': {'fr': 'Svalbard et Jan Mayen', 'es': 'Svalbard y Jan Mayen'},
    'BN': {'fr': 'Brunéi',                'es': 'Brunéi'},
    'GL': {'fr': 'Groenland',             'es': 'Groenlandia'},
    'GS': {'fr': 'Géorgie du Sud',        'es': 'Georgia del Sur'},
}

# --- 2. le francais --------------------------------------------------------

# La forme complete, quand aucune regle ne la produit correctement.
FR_FORCE = {
    'KR': 'en %s',   # Coree du Sud : finale en -d, la regle dirait « au »
    'KP': 'en %s',
    'MK': 'en %s',   # Macedoine du Nord
    'GW': 'en %s',   # Guinee-Bissau
    'GS': 'en %s',
    'RE': 'à %s',    # a La Reunion : l'article est deja dans le nom
    'SJ': 'à %s',
}

# Pluriels : « aux ».
FR_PLURIEL = {
    'US', 'NL', 'AE', 'PH', 'KM', 'MV', 'SC', 'BS', 'FJ', 'TO', 'BM', 'AS',
    'TF', 'PN', 'UM',
}

# Feminins qui prennent « a la ».
FR_A_LA = {'BB', 'DM', 'GD', 'JM'}

# Pays employes sans article : « a ».
FR_SANS_ARTICLE = {
    'CU', 'MG', 'HT', 'MT', 'CY', 'MC', 'SG', 'BH', 'DJ', 'OM', 'LK', 'TW',
    'SM', 'MU', 'LC', 'VC', 'AG', 'KN', 'TT', 'MO', 'HK', 'ST', 'GI', 'JE',
    'GG', 'AW', 'CW', 'SX', 'BL', 'MF', 'PM', 'WF', 'NU', 'TK', 'GU', 'PR',
    'NR', 'KI', 'WS', 'PW', 'VU', 'TV', 'AQ', 'YT', 'MS', 'AI', 'BQ', 'SH',
}

# Masculins qui se terminent par -e : la regle du -e les donnerait feminins.
FR_MASCULIN_EN_E = {'MX', 'MZ', 'ZW', 'KH', 'BZ', 'SR'}

VOYELLES = 'AEIOUYÀÂÄÉÈÊËÎÏÔÖÙÛÜ'

# --- 3. l'anglais ----------------------------------------------------------
# « in the Netherlands », « in the Philippines ». La regle couvre les noms
# collectifs ; la liste ne sert qu'aux quatre qui y echappent.
# « Island » au singulier est volontairement absent : on dit « on Christmas
# Island », pas « in the Christmas Island ».
EN_THE_FINS = ('Islands', 'Republic', 'Kingdom', 'States',
               'Emirates', 'Territory', 'Territories')
EN_THE = {'PH', 'BS', 'MV', 'KM', 'SC', 'GM', 'CD', 'CG'}


def nom_fr_es(iso, c, tr):
    if iso in NOMS_MANUELS:
        return NOMS_MANUELS[iso]
    src = getattr(c, 'common_name', None) or c.name
    return {'fr': tr['fr'].gettext(src), 'es': tr['es'].gettext(src)}


def dans_fr(iso, nom):
    if iso in FR_FORCE:
        return FR_FORCE[iso] % nom
    if nom.startswith('Îles '):
        return 'aux %s' % nom
    if nom.startswith('Île '):
        return 'à l’%s' % nom
    if nom.startswith('République'):
        return 'en %s' % nom
    if iso in FR_PLURIEL:
        return 'aux %s' % nom
    if iso in FR_A_LA:
        return 'à la %s' % nom
    if iso in FR_SANS_ARTICLE:
        return 'à %s' % nom
    if nom[0].upper() in VOYELLES:
        return 'en %s' % nom
    if nom.endswith('e') and iso not in FR_MASCULIN_EN_E:
        return 'en %s' % nom
    return 'au %s' % nom


def dans_en(iso, nom):
    if nom.lower().startswith('the '):
        return 'in %s' % nom
    if iso in EN_THE or nom.endswith(EN_THE_FINS):
        return 'in the %s' % nom
    return 'in %s' % nom


def main():
    dossier = os.path.join(os.path.dirname(pycountry.__file__), 'locales')
    tr = {lg: gettext.translation('iso3166-1', dossier, languages=[lg])
          for lg in ('fr', 'es')}

    # On part des pays que le site connait vraiment, pas de la liste ISO
    # entiere : un nom traduit pour un pays sans page ne sert a rien.
    with open(os.path.join(SORTIE, 'geo.json'), encoding='utf-8') as f:
        geo = json.load(f)

    out = {}
    sans_iso = []
    for iso, p in geo['pays'].items():
        c = pycountry.countries.get(alpha_2=iso)
        if c is None and iso not in NOMS_MANUELS:
            sans_iso.append('%s/%s' % (iso, p['nom']))
            noms = {'fr': p['nom'], 'es': p['nom']}
        else:
            noms = nom_fr_es(iso, c, tr) if c is not None else NOMS_MANUELS[iso]
        noms = {'en': p['nom'], 'fr': noms['fr'], 'es': noms['es']}
        out[iso] = {
            'noms': noms,
            'dans': {
                'en': dans_en(iso, noms['en']),
                'fr': dans_fr(iso, noms['fr']),
                'es': 'en %s' % noms['es'],
            },
        }

    chemin = os.path.join(SORTIE, 'noms-pays.json')
    with open(chemin, 'w', encoding='utf-8') as f:
        json.dump(out, f, ensure_ascii=False, separators=(',', ':'))

    # --- la verification, qui est le vrai produit de ce script -------------
    groupes = {}
    for x in out.values():
        art = x['dans']['fr'].split(' ', 1)[0]
        groupes.setdefault(art, []).append(x['noms']['fr'])

    print('ecrit : %s (%d pays)' % (chemin, len(out)))
    if sans_iso:
        print('sans correspondance ISO : %s' % ', '.join(sans_iso))
    restants = [(i, x['noms']['fr'], x['noms']['es']) for i, x in out.items()
                if any(ch in x['noms']['fr'] + x['noms']['es'] for ch in ',(')]
    print('noms contenant encore une virgule ou une parenthese : %d' % len(restants))
    for r in restants:
        print('   %s  %s | %s' % r)
    print()
    for art in sorted(groupes):
        noms = sorted(groupes[art])
        print('--- %s (%d) ---' % (art, len(noms)))
        print('  ' + ', '.join(noms))
        print()
    print('--- anglais : les « the » (%d) ---' % sum(
        1 for x in out.values() if ' the ' in x['dans']['en']))
    print('  ' + ', '.join(sorted(
        x['noms']['en'] for x in out.values() if ' the ' in x['dans']['en'])))


if __name__ == '__main__':
    main()
