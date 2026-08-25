# -*- coding: utf-8 -*-
"""Le classement Top-19 des villes, pays par pays.

LA FORMULE DU CAHIER DES CHARGES, TELLE QUELLE :

  CityScore = population + importance economique + volume d'emploi
            + presence entreprises/BPO + demande de recrutement
            + opportunite de recherche + disponibilite des donnees

Les sept signaux sont ici, chacun avec son poids reglable par marche. Mais
QUATRE D'ENTRE EUX N'ONT AUCUNE SOURCE dans les donnees dont on dispose :
volume d'emploi, presence BPO, demande de recrutement, opportunite de
recherche. Ils sortent donc a zero, avec la mention « aucune source », et leur
poids par defaut est zero.

C'est volontaire. Un score qui melange trois mesures et quatre suppositions
donne un classement qui a l'air scientifique et qui ne l'est pas — et c'est ce
classement qui decide quelles pages existent. Le jour ou une source arrive
(une API d'offres d'emploi, un export de volumes de recherche), il suffit de
la brancher sur le signal correspondant et de lui donner un poids : rien
d'autre ne bouge.

DETERMINISTE ET VERSIONNE. Le meme jeu de donnees et les memes poids donnent
toujours le meme classement, et chaque revision est datee par une empreinte
des reglages : deux classements differents ne peuvent pas se faire passer
l'un pour l'autre.

Usage : python3 score.py [dossier-data]
"""
import hashlib
import json
import math
import os
import sys

DATA = sys.argv[1] if len(sys.argv) > 1 else os.path.join(
    os.path.dirname(os.path.abspath(__file__)), '..', 'data')

# Les poids par defaut. Un signal sans source garde un poids de zero : le
# mettre a autre chose ne ferait qu'ajouter du bruit constant.
POIDS = {
    'population': 0.55,
    'importance_economique': 0.30,
    'disponibilite_donnees': 0.15,
    'volume_emploi': 0.0,
    'presence_bpo': 0.0,
    'demande_recrutement': 0.0,
    'opportunite_recherche': 0.0,
}

SANS_SOURCE = ('volume_emploi', 'presence_bpo', 'demande_recrutement',
               'opportunite_recherche')


def signaux(ville, pays):
    """Les sept signaux d'une ville, chacun entre 0 et 1."""
    pop = max(1, ville['population'])

    # La population, en echelle logarithmique. Sur une echelle lineaire une
    # capitale de 20 millions ecrase tout et les rangs 2 a 19 se valent tous.
    s_pop = min(1.0, math.log10(pop) / 7.3)

    # L'importance economique, faute de PIB par ville : le statut de capitale,
    # le rang national, le rang dans la region et le poids dans la population
    # urbaine du pays. Ce sont des donnees reelles, pas des estimations.
    s_eco = (
        (0.40 if ville['capitale'] else 0.0)
        + 0.25 * (1.0 / ville['rang_pays']) ** 0.5
        + 0.15 * (1.0 if ville['rang_region'] == 1 else 0.0)
        + 0.20 * min(1.0, ville['part_urbaine'] * 4)
    )

    # La disponibilite des donnees : ce qu'on sait vraiment de cette ville.
    champs = [ville.get('region_code'), ville.get('fuseau'),
              ville.get('lat') is not None, ville.get('geonameid'),
              pays.get('langues'), pays.get('monnaie')]
    s_don = sum(1 for c in champs if c) / len(champs)

    return {
        'population': round(s_pop, 4),
        'importance_economique': round(min(1.0, s_eco), 4),
        'disponibilite_donnees': round(s_don, 4),
        'volume_emploi': None,
        'presence_bpo': None,
        'demande_recrutement': None,
        'opportunite_recherche': None,
    }


def score(sig, poids):
    total = 0.0
    for cle, p in poids.items():
        v = sig.get(cle)
        if v is None or not p:
            continue
        total += p * v
    return round(total, 5)


def main():
    geo = json.load(open(os.path.join(DATA, 'geo.json'), encoding='utf-8'))
    top = geo['top']

    reglages_path = os.path.join(DATA, 'reglages.json')
    reglages = {'poids': POIDS, 'top': top, 'exclusions': {}, 'inclusions': {}}
    if os.path.exists(reglages_path):
        # L'administrateur peut inclure, exclure ou reprioriser a la main :
        # ses decisions vivent dans ce fichier et survivent a toute
        # reconstruction du jeu de donnees.
        reglages.update(json.load(open(reglages_path, encoding='utf-8')))
    poids = reglages['poids']

    empreinte = hashlib.sha256(
        json.dumps({'poids': poids, 'top': reglages['top'],
                    'exclusions': reglages['exclusions'],
                    'inclusions': reglages['inclusions']},
                   sort_keys=True).encode('utf-8')
    ).hexdigest()[:12]

    sortie = {'revision': empreinte, 'top': reglages['top'],
              'poids': poids, 'sans_source': list(SANS_SOURCE), 'pays': {}}

    retenues = 0
    for iso, p in geo['pays'].items():
        exclues = set(reglages['exclusions'].get(iso, []))
        forcees = list(reglages['inclusions'].get(iso, []))

        classees = []
        for v in p['villes']:
            if v['limace'] in exclues:
                continue
            sig = signaux(v, p)
            classees.append({
                'limace': v['limace'], 'nom': v['nom'],
                'population': v['population'], 'region_code': v['region_code'],
                'capitale': v['capitale'], 'fuseau': v['fuseau'],
                'rang_pays': v['rang_pays'], 'part_urbaine': v['part_urbaine'],
                'signaux': sig, 'score': score(sig, poids),
            })

        classees.sort(key=lambda x: (-x['score'], x['nom']))

        # Les villes imposees a la main passent devant, dans l'ordre donne.
        if forcees:
            index = {c['limace']: c for c in classees}
            devant = [index[s] for s in forcees if s in index]
            reste = [c for c in classees if c['limace'] not in set(forcees)]
            classees = devant + reste

        garde = classees[:reglages['top']]
        for i, c in enumerate(garde, 1):
            c['rang'] = i
        retenues += len(garde)

        sortie['pays'][iso] = {
            'nom': p['nom'], 'limace': p['limace'], 'langues': p['langues'],
            'villes_connues': p['villes_connues'],
            'complet': p['villes_connues'] >= reglages['top'],
            'villes': garde,
        }

    with open(os.path.join(DATA, 'classement.json'), 'w', encoding='utf-8') as f:
        json.dump(sortie, f, ensure_ascii=False, separators=(',', ':'))

    complets = sum(1 for x in sortie['pays'].values() if x['complet'])
    print('revision du classement : %s' % empreinte)
    print('pays classes           : %d  (dont %d avec %d villes pleines)'
          % (len(sortie['pays']), complets, reglages['top']))
    print('villes retenues        : %d' % retenues)
    print('signaux sans source    : %s' % ', '.join(SANS_SOURCE))
    print()
    for iso in ('CA', 'FR', 'GB', 'MA'):
        if iso in sortie['pays']:
            d = sortie['pays'][iso]
            noms = ', '.join('%s (%.3f)' % (c['nom'], c['score']) for c in d['villes'][:6])
            print('%s %s : %s ...' % (iso, d['nom'], noms))


if __name__ == '__main__':
    main()
