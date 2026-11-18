# BeanBox – autorski motyw WordPress + WooCommerce (CMS)

**Autorzy:** Mateusz Łagocki, Yaroslaw Pankewytch

Sklep internetowy **BeanBox** (kawa speciality, akcesoria, sprzęt) – ten sam scenariusz co w projekcie APPDB
(n8n + AI). Katalog produktów pochodzi z APPDB (`data/out/produkty.csv`), SKU = `P001…P025`, więc na zajęciach 4
SKU jest kluczem synchronizacji z arkuszem Google.

```
beanbox/                    motyw (kopiowany / linkowany do wp-content/themes/beanbox)
├── acf-json/               definicje pól ACF (Local JSON) – w repozytorium
├── blocks/                 bloki ACF: hero, usp, opinie (z CPT), faq (z CPT + JSON-LD)
├── inc/                    setup, assets, customizer (dane globalne), post-types (opinia, faq),
│                           blocks (rejestracja + filtr dozwolonych bloków), woocommerce (hooki – lab 3),
│                           performance (meta description, bez skryptu emoji – poprawki po Lighthouse)
├── woocommerce/loop/       jedyny nadpisany szablon Woo: no-products-found.php
├── template-parts/         karta wpisu, karta opinii
└── *.php                   header, footer, front-page, page, single, index, 404, archive-opinia, single-opinia
dane/                       produkty_woocommerce.csv (import), obrazy/ (grafiki produktów)
narzedzia/                  generatory (pola ACF, produkty, schemat pól), skrypty wp-cli (konfiguracja Woo,
                            import, treść startowa, zamówienie testowe), wp.sh, zrzut.ps1
sprawozdanie/               sprawozdania 2 i 3 (.md/.docx/.pdf), zrzuty, raporty Lighthouse
```

## Wymagania
WordPress 6.5+, PHP 8.1+, wtyczki: **Secure Custom Fields** (SCF – darmowy fork ACF z WordPress.org; bloki ACF w zwykłym ACF wymagają wersji PRO, API `get_field()` i `acf-json` są identyczne), **WooCommerce**, Query Monitor (do szukania hooków).

## Uruchomienie (Local)
1. Local → *Create a new site* → nazwa `BeanBox`, domena `beanbox.local`.
2. Skopiuj (lub podlinkuj) katalog `beanbox/` do `wp-content/themes/` i włącz motyw.
3. Zainstaluj i włącz Secure Custom Fields, WooCommerce, Query Monitor.
4. ACF → Grupy pól → „Dostępna synchronizacja” → zsynchronizuj grupy z `acf-json`.
5. Ustawienia → Bezpośrednie odnośniki → Zapisz (adres `/opinie/`).
6. Produkty → Importuj → `dane/produkty_woocommerce.csv` (obrazy z `dane/obrazy/` skopiuj wcześniej do
   `wp-content/uploads/beanbox-import/`).

## Konfiguracja z wiersza poleceń (wp-cli z Local)
```
bash narzedzia/wp.sh eval-file narzedzia/konfiguracja-woo.php
bash narzedzia/wp.sh eval-file narzedzia/import-produktow.php "dane/produkty_woocommerce.csv" --user=admin
bash narzedzia/wp.sh eval-file narzedzia/daty-i-banery.php --user=admin
bash narzedzia/wp.sh eval-file narzedzia/konfiguracja-strony.php --user=admin
```

## Zasady w kodzie
- każda wyświetlana wartość przechodzi przez `esc_html()` / `esc_attr()` / `esc_url()` / `wp_kses_post()`;
  nigdzie nie ma `the_field()`;
- zmiany w WooCommerce przez hooki; nadpisanie szablonu tylko tam, gdzie nie ma akcji;
- CSS mobile first (`min-width`), cele dotykowe ≥ 44 px.
