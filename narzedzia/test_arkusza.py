"""Przygotowanie do zajęć 4 – test połączenia z arkuszem Google przez konto usługi (Sheets API).

Klucz JSON konta usługi NIE trafia do repozytorium (.gitignore) – leży poza projektem:
  BEANBOX_SA_KEY   ścieżka do klucza (domyślnie %USERPROFILE%\\.secrets\\beanbox-n8n-sa.json)
  BEANBOX_SHEET_ID ID arkusza z zakładkami Zamowienia i Produkty (udostępnionego kontu usługi jako Edytor)

  python narzedzia/test_arkusza.py

Sprawdza: dostęp do arkusza, obecność zakładek, nagłówki oraz zgodność SKU w zakładce Produkty
z SKU produktów w WooCommerce (dane/produkty_woocommerce.csv) – SKU jest kluczem synchronizacji.
"""
import csv
import os
import sys
from pathlib import Path

import google.auth.transport.requests
from google.oauth2 import service_account

ROOT = Path(__file__).resolve().parent.parent
KEY = os.environ.get("BEANBOX_SA_KEY", str(Path.home() / ".secrets" / "beanbox-n8n-sa.json"))
SHEET = os.environ.get("BEANBOX_SHEET_ID")
if not SHEET:
    sys.exit("Ustaw BEANBOX_SHEET_ID (ID arkusza z adresu docs.google.com/spreadsheets/d/<ID>/edit).")

creds = service_account.Credentials.from_service_account_file(
    KEY, scopes=["https://www.googleapis.com/auth/spreadsheets"])
s = google.auth.transport.requests.AuthorizedSession(creds)
base = f"https://sheets.googleapis.com/v4/spreadsheets/{SHEET}"

meta = s.get(base + "?fields=properties.title,sheets.properties.title")
meta.raise_for_status()
tabs = [t["properties"]["title"] for t in meta.json()["sheets"]]
print(f"Arkusz: {meta.json()['properties']['title']}")
print(f"Konto usługi: {creds.service_account_email.split('@')[0]}@… (dostęp: Edytor)")
for need in ("Zamowienia", "Produkty"):
    print(f"  zakładka {need}: {'OK' if need in tabs else 'BRAK'}")

for tab in ("Zamowienia", "Produkty"):
    v = s.get(f"{base}/values/{tab}!1:1").json().get("values", [[]])[0]
    print(f"  nagłówki {tab}: {', '.join(v)}")

sheet_sku = {r[0] for r in s.get(f"{base}/values/Produkty!A2:A").json().get("values", []) if r}
woo_sku = {r["SKU"] for r in csv.DictReader(open(ROOT / "dane" / "produkty_woocommerce.csv", encoding="utf-8"))
           if r["Type"] in ("simple", "variable")}
print(f"SKU w arkuszu: {len(sheet_sku)}, w WooCommerce: {len(woo_sku)}, wspólne: {len(sheet_sku & woo_sku)}")
if sheet_sku ^ woo_sku:
    print("  różnice:", sorted(sheet_sku ^ woo_sku))
else:
    print("  zbiory SKU identyczne – można synchronizować po SKU")
