# -*- coding: utf-8 -*-
"""Construit la base geographique de la plateforme de recrutement.

D'OU VIENNENT LES DONNEES. De GeoNames, via le paquet geonamescache : un jeu
de donnees ouvert, embarque, hors ligne. Rien n'est aspire d'un site tiers.
252 pays, 34 006 villes de plus de 15 000 habitants, avec pour chacune la
population, la region, le fuseau horaire et les coordonnees.

CE QUE LE FICHIER CONTIENT ET CE QU'IL NE CONTIENT PAS. Le cahier des charges
demande un score construit sur sept signaux : population, importance
economique, volume d'emploi, presence des entreprises et des centres d'appels,
demande de recrutement, opportunite de recherche, disponibilite des donnees.

Trois de ces sept se calculent avec ce qu'on a vraiment :
  - la population de la ville ;
  - son rang dans son pays et dans sa region ;
  - son statut de capitale nationale.
Les quatre autres demandent des sources qui ne sont pas dans ce jeu de donnees
— volume d'emploi, presence BPO, demande de recrutement, volume de recherche.
ELLES SORTENT DONC AVEC UN POIDS DE ZERO ET SONT MARQUEES « aucune source ».
Elles ne sont pas inventees, pas approchees, pas remplies au hasard : un score
qui a l'air complet alors qu'il est devine est pire qu'un score qui manque.

Usage : python3 construire.py [dossier-de-sortie]
"""
import json
import os
import re
import sys
import unicodedata
from collections import defaultdict

import geonamescache

SORTIE = sys.argv[1] if len(sys.argv) > 1 else os.path.join(
    os.path.dirname(os.path.abspath(__file__)), '..', 'data')

# Le cahier des charges dit « Top 19 » : c'est un reglage, pas une constante
# eparpillee dans le code.
TOP = 19


def limace(nom):
    """Un identifiant d'URL stable, sans accent et sans surprise."""
    s = unicodedata.normalize('NFKD', nom)
    s = s.encode('ascii', 'ignore').decode('ascii').lower()
    s = re.sub(r"[^a-z0-9]+", '-', s).strip('-')
    return s or 'x'


def main():
    g = geonamescache.GeonamesCache()
    pays_brut = g.get_countries()
    villes_brut = g.get_cities()

    par_pays = defaultdict(list)
    for v in villes_brut.values():
        if not v.get('population'):
            continue
        par_pays[v['countrycode']].append(v)

    pays = {}
    doublons_limace = 0

    for iso, p in sorted(pays_brut.items()):
        villes = sorted(par_pays.get(iso, []),
                        key=lambda x: (-x['population'], x['name']))
        if not villes:
            # Un pays sans une seule ville dans le jeu de donnees n'a pas de
            # page : mieux vaut ne pas exister que d'exister vide.
            continue

        capitale = (p.get('capital') or '').strip().lower()
        total_urbain = sum(v['population'] for v in villes)

        # Le rang dans la region administrative : une ville qui domine sa
        # region n'a pas le meme poids qu'une banlieue de meme taille.
        par_region = defaultdict(list)
        for v in villes:
            par_region[v.get('admin1code') or '?'].append(v)
        for liste in par_region.values():
            liste.sort(key=lambda x: -x['population'])

        vues = set()
        sortie_villes = []
        for rang, v in enumerate(villes, 1):
            s = limace(v['name'])
            if s in vues:
                # Deux villes homonymes dans le meme pays : on suffixe par la
                # region, sinon la seconde ecrase la premiere en silence.
                s = '%s-%s' % (s, limace(str(v.get('admin1code') or rang)))
                doublons_limace += 1
            vues.add(s)

            region = par_region[v.get('admin1code') or '?']
            sortie_villes.append({
                'nom': v['name'],
                'limace': s,
                'population': v['population'],
                'rang_pays': rang,
                'rang_region': region.index(v) + 1,
                'region_code': v.get('admin1code') or '',
                'part_urbaine': round(v['population'] / total_urbain, 6),
                'capitale': v['name'].strip().lower() == capitale,
                'fuseau': v.get('timezone') or '',
                'lat': v.get('latitude'),
                'lon': v.get('longitude'),
                'geonameid': v.get('geonameid'),
            })

        langues = [x.split('-')[0] for x in (p.get('languages') or '').split(',') if x]
        pays[iso] = {
            'iso': iso,
            'iso3': p.get('iso3'),
            'nom': p['name'],
            'limace': limace(p['name']),
            'continent': p.get('continentcode'),
            'capitale': p.get('capital'),
            'population': p.get('population'),
            'monnaie': p.get('currencycode'),
            'telephone': p.get('phone'),
            'langues': langues,
            'villes_connues': len(villes),
            'villes': sortie_villes[:TOP * 3],   # marge pour les exclusions
        }

    os.makedirs(SORTIE, exist_ok=True)
    chemin = os.path.join(SORTIE, 'geo.json')
    with open(chemin, 'w', encoding='utf-8') as f:
        json.dump({'top': TOP, 'source': 'GeoNames (geonamescache), hors ligne',
                   'pays': pays}, f, ensure_ascii=False, separators=(',', ':'))

    assez = [p for p in pays.values() if p['villes_connues'] >= TOP]
    pages = sum(min(TOP, p['villes_connues']) for p in pays.values())

    print('pays retenus                  : %d' % len(pays))
    print('pays avec au moins %d villes   : %d' % (TOP, len(assez)))
    print('pays avec moins de %d villes   : %d' % (TOP, len(pays) - len(assez)))
    print('limaces desambiguisees        : %d' % doublons_limace)
    print()
    print('PAGES VILLE REELLEMENT POSSIBLES : %d' % pages)
    print('  (le cahier des charges annonce 195 x %d = %d)' % (TOP, 195 * TOP))
    print('  x 3 metiers                     : %d' % (pages * 3))
    print('  + une page par pays              : %d' % (pages * 3 + len(pays)))
    print()
    print('ecrit : %s (%.1f Mo)' % (chemin, os.path.getsize(chemin) / 1e6))


if __name__ == '__main__':
    main()
