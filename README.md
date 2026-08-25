# Global Recruitment Platform — geographic SEO engine

Corporate, call-center/BPO and bulk hiring. International programmatic SEO,
built from a real geographic dataset rather than a page template with the city
name swapped in.

## What is here

```
geo/construire.py     builds data/geo.json from the open GeoNames dataset
geo/score.py          the Top-19 CityScore ranking, versioned
app/geo.php           data access + THE QUALITY GATE
app/contenu.php       the editorial modules — every sentence derived from data
app/langues.php       the three hand-written languages
app/routes.php        the URL architecture — the only place a URL is built
app/seo.php           titles, canonicals, hreflang, structured data
app/plan.php          the sitemap — indexable pages only
public/index.php      front controller
vues/                 templates
tests.py              59 checks in a real browser
```

## The numbers, measured

| | |
|---|---|
| Countries with at least one city | 243 |
| Countries with a full Top-19 | 136 |
| Countries with fewer than 19 cities | 107 |
| **City pages actually possible** | **3,119** |
| City pages the spec assumed (195 x 19) | 3,705 |
| Total pages incl. 3 verticals and country pages | 9,600 |

The gap is not a bug. 107 countries simply do not have 19 cities of meaningful
size in the dataset. Their country page lists what exists instead of padding to
a round number.

## The CityScore

All seven signals from the specification are implemented. **Three have a real
data source** — population, economic weight (capital status, national rank,
regional dominance, share of urban population) and data completeness.

**Four have no source** in this dataset: employment volume, BPO presence,
recruitment demand, search opportunity. They return `null`, carry a weight of
zero, and are listed as `sans_source` in the output. They are not estimated.

A score that blends three measurements with four guesses looks scientific and
is not — and this score decides which pages exist. Connect a jobs API or a
search-volume export to a signal, give it a weight, and nothing else changes.

Every ranking revision is stamped with a hash of the weights and manual
overrides, and that stamp is printed on every page. Two rankings cannot be
mistaken for each other.

## The quality gate

Six data checks per city page. Five must pass to be indexed.

A page that fails carries `noindex,follow`, tells the reader so, and is
**excluded from the sitemap** — using the same function, not a second copy of
the rule. A sitemap that advertises a page you told robots to ignore sends two
contradictory orders, and the wasteful one wins.

## Running it

```bash
pip install geonamescache
python3 geo/construire.py            # -> data/geo.json
python3 geo/score.py                 # -> data/classement.json
php -S 127.0.0.1:8850 -t public public/router.php
python3 tests.py
```

`data/reglages.json` holds the manual overrides — weights, `exclusions`,
`inclusions` per country. It survives any rebuild of the dataset.

## Deployment

Plain PHP, no framework, no Node, no database. It runs on shared hosting as is.
The geographic engine is independent of the site and emits plain JSON, so
moving the front end to Next.js later carries the data, the scoring and the
rankings across untouched.
