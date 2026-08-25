# -*- coding: utf-8 -*-
"""Presse la plateforme de recrutement dans un vrai navigateur.

CE QUI EST VERIFIE, ET POURQUOI CELA D'ABORD.

Sur un site dont la raison d'etre est l'acquisition organique, les defauts qui
coutent cher ne se voient pas a l'oeil : une canonique absente, un hreflang non
reciproque, une page d'erreur servie en 200, une page mince indexee. Aucun de
ces quatre ne change un pixel a l'ecran, et chacun se paie en milliers
d'adresses mal indexees.

  1. LES REGLES SEO, sur des pages reelles : titre unique, canonique sur
     soi-meme, hreflang reciproque avec x-default, donnees structurees valides.
  2. LA PORTE DE QUALITE : une page qui echoue porte noindex ET n'apparait pas
     dans le plan de site. Les deux, sinon les ordres se contredisent.
  3. L'UNICITE DU CONTENU : deux villes differentes ne doivent pas produire le
     meme texte. C'est LE risque du referencement programmatique, et il se
     mesure, il ne se decrete pas.
  4. Les codes HTTP : un 404 repond 404.

Usage : python3 tests.py [base]
"""
import json
import re
import sys
from difflib import SequenceMatcher

from playwright.sync_api import sync_playwright

BASE = sys.argv[1] if len(sys.argv) > 1 else 'http://127.0.0.1:8850'
ok = ko = 0


def t(nom, cond, detail=''):
    global ok, ko
    if cond:
        ok += 1
        print('  OK    %s' % nom)
    else:
        ko += 1
        print('  ECHEC %s   %s' % (nom, detail))


def texte_visible(pg):
    """Le texte editorial de la page, sans l'entete ni le pied."""
    return pg.evaluate("""() => {
        const n = document.querySelector('main');
        return n ? n.innerText.replace(/\\s+/g, ' ').trim() : '';
    }""")


with sync_playwright() as p:
    nav = p.chromium.launch()
    pg = nav.new_page(viewport={'width': 1280, 'height': 900})
    erreurs = []
    pg.on('pageerror', lambda e: erreurs.append(str(e)))

    print('\n-- 1. les regles de referencement --')

    pages = [
        '/en/', '/fr/', '/es/',
        '/en/recruitment-outsourcing/canada/',
        '/en/recruitment-outsourcing/canada/toronto/',
        '/fr/externalisation-recrutement/canada/toronto/',
        '/en/recruitment-outsourcing/canada/toronto/call-center/',
        '/es/externalizacion-reclutamiento/morocco/casablanca/',
    ]
    titres = {}
    for u in pages:
        r = pg.goto(BASE + u, wait_until='domcontentloaded')
        t('%s repond 200' % u, r.status == 200, r.status)

        titre = pg.title()
        titres[u] = titre
        t('%s a un titre' % u, len(titre) > 15, titre)

        canon = pg.locator('link[rel=canonical]').first.get_attribute('href')
        t('%s est canonique d\'elle-meme' % u, canon == BASE + u, canon)

        # hreflang : les trois langues plus x-default, et la variante de la
        # langue courante doit pointer sur la page elle-meme.
        alts = pg.locator('link[rel=alternate]').all()
        codes = sorted(a.get_attribute('hreflang') for a in alts)
        t('%s declare en/es/fr/x-default' % u,
          codes == ['en', 'es', 'fr', 'x-default'], codes)

        langue = u.strip('/').split('/')[0]
        moi = [a.get_attribute('href') for a in alts
               if a.get_attribute('hreflang') == langue]
        t('%s : son propre hreflang pointe sur elle' % u, moi and moi[0] == BASE + u, moi)

    t('les titres des 8 pages sont tous differents',
      len(set(titres.values())) == len(titres),
      [x for x in titres.values() if list(titres.values()).count(x) > 1])

    print('\n-- 2. hreflang reciproque --')
    # Aller en francais depuis l'anglais, puis revenir : la boucle doit fermer.
    pg.goto(BASE + '/en/recruitment-outsourcing/canada/toronto/', wait_until='domcontentloaded')
    vers_fr = [a.get_attribute('href') for a in pg.locator('link[rel=alternate]').all()
               if a.get_attribute('hreflang') == 'fr'][0]
    pg.goto(vers_fr, wait_until='domcontentloaded')
    retour_en = [a.get_attribute('href') for a in pg.locator('link[rel=alternate]').all()
                 if a.get_attribute('hreflang') == 'en'][0]
    t('en -> fr -> en revient a la page de depart',
      retour_en == BASE + '/en/recruitment-outsourcing/canada/toronto/', retour_en)
    t('la page francaise est bien en francais',
      pg.locator('html').get_attribute('lang') == 'fr')

    print('\n-- 3. donnees structurees --')
    pg.goto(BASE + '/en/recruitment-outsourcing/canada/toronto/', wait_until='domcontentloaded')
    blocs = [json.loads(x) for x in pg.locator('script[type="application/ld+json"]')
             .all_text_contents()]
    types = [b.get('@type') for b in blocs]
    t('un fil d\'Ariane est declare', 'BreadcrumbList' in types, types)
    t('une FAQ est declaree', 'FAQPage' in types, types)
    faq = [b for b in blocs if b.get('@type') == 'FAQPage'][0]
    questions_page = pg.locator('.faq details summary').all_text_contents()
    questions_json = [q['name'] for q in faq['mainEntity']]
    t('la FAQ declaree est EXACTEMENT celle affichee',
      sorted(questions_page) == sorted(questions_json),
      '%d affichees, %d declarees' % (len(questions_page), len(questions_json)))

    print('\n-- 4. la porte de qualite --')
    # Une ville minuscule : le jeu de donnees en a dans les petits territoires.
    pg.goto(BASE + '/en/recruitment-outsourcing/canada/toronto/', wait_until='domcontentloaded')
    t('une grande ville n\'est pas en noindex',
      pg.locator('meta[name=robots]').count() == 0)

    # On cherche une page reellement retenue par la porte.
    retenue = None
    pg.goto(BASE + '/en/', wait_until='domcontentloaded')
    for lien in pg.locator('.pays-liste a').all()[:40]:
        href = lien.get_attribute('href')
        p2 = nav.new_page()
        p2.goto(BASE + href, wait_until='domcontentloaded')
        villes = [a.get_attribute('href') for a in p2.locator('.classement td a').all()]
        p2.close()
        for vu in villes[-3:]:
            p3 = nav.new_page()
            p3.goto(BASE + vu, wait_until='domcontentloaded')
            if p3.locator('meta[name=robots]').count() > 0:
                retenue = vu
                p3.close()
                break
            p3.close()
        if retenue:
            break

    if retenue:
        pg.goto(BASE + retenue, wait_until='domcontentloaded')
        rb = pg.locator('meta[name=robots]').first.get_attribute('content')
        t('une page trop mince porte noindex (%s)' % retenue, 'noindex' in rb, rb)
        t('elle le dit aussi au lecteur', pg.locator('.bandeau-noindex').count() == 1)
        t('elle reste canonique d\'elle-meme',
          pg.locator('link[rel=canonical]').first.get_attribute('href') == BASE + retenue)

        plan = nav.new_page()
        plan.goto(BASE + '/sitemap.xml', wait_until='domcontentloaded')
        xml = plan.content()
        plan.close()
        t('elle est ABSENTE du plan de site', (BASE + retenue) not in xml)
    else:
        print('  (aucune page retenue trouvee dans l\'echantillon parcouru)')

    print('\n-- 5. l\'unicite du contenu --')
    echantillon = [
        '/en/recruitment-outsourcing/canada/toronto/',
        '/en/recruitment-outsourcing/canada/calgary/',
        '/en/recruitment-outsourcing/france/paris/',
        '/en/recruitment-outsourcing/france/nantes/',
        '/en/recruitment-outsourcing/morocco/casablanca/',
        '/en/recruitment-outsourcing/japan/tokyo/',
    ]
    textes = {}
    for u in echantillon:
        pg.goto(BASE + u, wait_until='domcontentloaded')
        textes[u] = texte_visible(pg)

    pire = 0.0
    couple = None
    cles = list(textes)
    for i in range(len(cles)):
        for j in range(i + 1, len(cles)):
            r = SequenceMatcher(None, textes[cles[i]], textes[cles[j]]).ratio()
            if r > pire:
                pire, couple = r, (cles[i], cles[j])
    # Deux pages de la meme famille partagent forcement les intitules et la
    # navigation. Au-dela de 85 % de similarite, ce sont des pages satellites.
    t('deux villes ne produisent pas la meme page (max %.0f %%)' % (pire * 100),
      pire < 0.85, '%s vs %s' % couple if couple else '')

    print('\n-- 5b. le segment d\'adresse est dans la langue de la page --')
    # Le positionnement corrige par le client — externalisation, et non
    # recrutement — porte sur le mot le plus recherche de toute l'adresse. Il
    # doit donc etre traduit, ET les anciennes adresses ne doivent pas mourir.
    req = pg.context.request

    for u, attendu in (
        ('/en/recruitment-outsourcing/canada/toronto/', 'en'),
        ('/fr/externalisation-recrutement/canada/toronto/', 'fr'),
        ('/es/externalizacion-reclutamiento/canada/toronto/', 'es'),
    ):
        rr = req.get(BASE + u, max_redirects=0)
        t('%s repond 200 directement' % u, rr.status == 200, rr.status)

    for u, cible in (
        # L'ancien segment, celui d'avant la correction.
        ('/en/recruitment/canada/toronto/', '/en/recruitment-outsourcing/canada/toronto/'),
        ('/fr/recruitment/canada/toronto/', '/fr/externalisation-recrutement/canada/toronto/'),
        # Le segment d'une AUTRE langue : un lien mal recopie ne doit pas
        # produire un 404 alors que la page existe.
        ('/es/externalisation-recrutement/canada/toronto/',
         '/es/externalizacion-reclutamiento/canada/toronto/'),
    ):
        rr = req.get(BASE + u, max_redirects=0)
        t('%s redirige en 301' % u, rr.status == 301, rr.status)
        t('  ... vers %s' % cible, rr.headers.get('location') == cible,
          rr.headers.get('location'))

    # Et le plan de site ne doit contenir QUE les adresses courantes.
    plan = nav.new_page()
    plan.goto(BASE + '/sitemap.xml', wait_until='domcontentloaded')
    xml_seg = plan.content()
    plan.close()
    t('le plan de site ne contient plus l\'ancien segment',
      '/recruitment/' not in xml_seg)
    for seg in ('recruitment-outsourcing', 'externalisation-recrutement',
                'externalizacion-reclutamiento'):
        t('le plan de site publie le segment %s' % seg, seg in xml_seg)

    print('\n-- 5c. les pays portent leur nom dans la langue de la page --')
    # « Externalizacion de la seleccion en Morocco » disqualifie une page
    # espagnole a la premiere ligne. Le nom vient de la norme ISO 3166, et
    # l'article francais — au / en / aux / a — est calcule puis relu a la main.
    for u, attendus, interdits in (
        ('/fr/externalisation-recrutement/morocco/casablanca/', ['Maroc'], ['Morocco']),
        ('/es/externalizacion-reclutamiento/japan/', ['Japón'], ['>Japan<']),
        ('/fr/externalisation-recrutement/the-netherlands/', ['aux Pays-Bas'], ['Netherlands<']),
        ('/fr/externalisation-recrutement/canada/', ['au Canada'], ['en Canada']),
        ('/fr/externalisation-recrutement/united-states/', ['aux États-Unis'], ['au États']),
        ('/fr/externalisation-recrutement/ivory-coast/', ['en Côte d’Ivoire'], ['Ivory Coast<']),
    ):
        pg.goto(BASE + u, wait_until='domcontentloaded')
        html = pg.content()
        for a in attendus:
            t('%s contient « %s »' % (u, a), a in html)
        for i in interdits:
            t('%s ne contient PAS « %s »' % (u, i), i not in html)

    # Le bloc « d'ou viennent ces chiffres » est affiche : il se traduit aussi.
    pg.goto(BASE + '/es/externalizacion-reclutamiento/morocco/casablanca/',
            wait_until='domcontentloaded')
    criteres = pg.locator('.criteres li').all_text_contents()
    t('les criteres de qualite sont traduits (%d)' % len(criteres),
      criteres and not any('Population figure' in c or 'Time zone known' in c
                           for c in criteres), criteres[:2])

    print('\n-- 6. codes HTTP et plan de site --')
    r = pg.goto(BASE + '/en/recruitment-outsourcing/atlantis/', wait_until='domcontentloaded')
    t('un pays inconnu repond 404', r.status == 404, r.status)
    r = pg.goto(BASE + '/en/recruitment-outsourcing/canada/atlantis/', wait_until='domcontentloaded')
    t('une ville inconnue repond 404', r.status == 404, r.status)
    r = pg.goto(BASE + '/en/recruitment-outsourcing/canada/toronto/plumbing/', wait_until='domcontentloaded')
    t('un metier inconnu repond 404', r.status == 404, r.status)

    r = pg.goto(BASE + '/sitemap.xml', wait_until='domcontentloaded')
    t('le plan de site repond 200', r.status == 200, r.status)
    xml = pg.content()
    t('le plan contient des adresses', xml.count('<loc>') > 100, xml.count('<loc>'))
    t('le plan declare les variantes de langue', 'hreflang' in xml)

    t('aucune erreur JavaScript', not erreurs, erreurs[:3])
    nav.close()

print('\n%d OK, %d ECHEC' % (ok, ko))
sys.exit(1 if ko else 0)
