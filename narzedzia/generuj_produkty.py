"""Plik CSV do importu produktów WooCommerce (Produkty → Importuj) + grafiki produktów.

Źródło: 25 produktów z projektu APPDB (data/out/produkty.csv) – ten sam katalog co w arkuszu Google,
SKU = produkt_id (P001…), więc na zajęciach 4 SKU jest kluczem synchronizacji z arkuszem.
Produkt P001 jest WARIANTOWY: Kawa Etiopia Yirgacheffe 250 g / 1 kg.

  python narzedzia/generuj_produkty.py
    -> dane/produkty_woocommerce.csv
    -> dane/obrazy/P0xx.png (800×800, generowane – bez zdjęć z internetu)
Obrazy kopiujemy do wp-content/uploads/beanbox-import/, a CSV wskazuje ich adresy URL.
"""
import csv
from pathlib import Path

from PIL import Image, ImageDraw, ImageFont

ROOT = Path(__file__).resolve().parent.parent
APPDB = ROOT.parent / "APPDB - Założenia organizacyjne" / "data" / "out" / "produkty.csv"
OUT_CSV = ROOT / "dane" / "produkty_woocommerce.csv"
IMG = ROOT / "dane" / "obrazy"
IMG.mkdir(parents=True, exist_ok=True)
SITE = "http://beanbox.local"
IMG_URL = f"{SITE}/wp-content/uploads/beanbox-import"

KATEGORIE = {"kawa": "Kawa", "akcesoria": "Akcesoria", "sprzet": "Sprzęt", "subskrypcja": "Subskrypcje"}
KOLORY = {"kawa": ("#3b2418", "#c8862a"), "akcesoria": ("#8a5a3b", "#f6eee6"),
          "sprzet": ("#24150d", "#eadfd4"), "subskrypcja": ("#2f7d4f", "#f6eee6")}

# Dodatkowe dane do pól ACF (grupa „Produkt – dodatkowe”) – tylko dla kaw
KAWY = {
    "P001": ("Etiopia, Yirgacheffe (Gedeo)", "jasne", "jaśmin, bergamotka, brzoskwinia", "Arabika 100%, odmiany heirloom, obróbka myta. Wysokość uprawy 1900–2200 m n.p.m."),
    "P002": ("Brazylia, Santos (Minas Gerais)", "srednie", "czekolada, orzech laskowy, karmel", "Arabika 100% (Bourbon, Mundo Novo), obróbka naturalna."),
    "P003": ("Kolumbia, Huila", "srednie", "czerwone jabłko, panela, kakao", "Arabika 100% (Caturra, Castillo), obróbka myta, 1600–1800 m n.p.m."),
    "P004": ("Brazylia + Indie", "ciemne", "gorzka czekolada, przyprawy, melasa", "Mieszanka 80% arabiki i 20% robusty, mielona pod espresso."),
    "P005": ("Meksyk, Chiapas", "srednie", "mleczna czekolada, wanilia", "Arabika 100%, dekofeinizacja metodą Swiss Water (bez chemii)."),
    "P006": ("Kenia, Nyeri", "jasne", "czarna porzeczka, grejpfrut, trzcina", "Arabika 100% (SL28, SL34), klasa AA, obróbka myta."),
    "P007": ("Gwatemala, Antigua", "srednie", "kakao, migdał, śliwka", "Kawa w saszetkach drip – 10 × 11 g, do zaparzania bezpośrednio w kubku."),
    "P020": ("Etiopia, Kenia, Kolumbia, Brazylia", "jasne", "od owoców po czekoladę", "Cztery kawy po 100 g – zestaw do porównania regionów."),
}
OPISY = {
    "kawa": "Świeżo palona w małych partiach. Wysyłamy w ciągu 7 dni od palenia.",
    "akcesoria": "Sprawdzone akcesoria do alternatywnych metod parzenia.",
    "sprzet": "Sprzęt dla domowego baristy – z polską gwarancją 24 miesiące.",
    "subskrypcja": "Co miesiąc nowa kawa speciality pod Twoje drzwi.",
}


def font(size, bold=False):
    for name in (("arialbd.ttf" if bold else "arial.ttf"), "DejaVuSans.ttf"):
        try:
            return ImageFont.truetype(f"C:/Windows/Fonts/{name}", size)
        except OSError:
            continue
    return ImageFont.load_default()


def obraz(pid, nazwa, kat):
    bg, fg = KOLORY[kat]
    im = Image.new("RGB", (800, 800), bg)
    d = ImageDraw.Draw(im)
    if kat in ("kawa", "subskrypcja"):  # torebka kawy
        d.rounded_rectangle((250, 170, 550, 620), 28, fill=fg)
        d.polygon([(250, 200), (300, 140), (500, 140), (550, 200)], fill=fg)
        d.ellipse((365, 300, 435, 400), outline=bg, width=10)
        d.line((400, 305, 400, 395), fill=bg, width=6)
    else:  # filiżanka
        d.rounded_rectangle((250, 300, 510, 560), 40, fill=fg)
        d.ellipse((480, 360, 590, 470), outline=fg, width=24)
        d.rounded_rectangle((200, 570, 600, 600), 15, fill=fg)
        for x in (320, 380, 440):
            d.arc((x - 20, 180, x + 20, 280), 90, 270, fill=fg, width=8)
    f = font(34, True)
    lines, line = [], ""
    for w in nazwa.split():
        if d.textlength(f"{line} {w}".strip(), font=f) > 680:
            lines.append(line)
            line = w
        else:
            line = f"{line} {w}".strip()
    lines.append(line)
    y = 650
    for ln in lines[:2]:
        d.text((400, y), ln, fill="#ffffff", font=f, anchor="mm")
        y += 44
    d.text((40, 40), "BeanBox", fill="#ffffff", font=font(28, True))
    d.text((760, 40), pid, fill="#ffffff", font=font(24), anchor="ra")
    im.save(IMG / f"{pid}.png", optimize=True)


rows = list(csv.DictReader(open(APPDB, encoding="utf-8-sig")))
COLS = ["ID", "Type", "SKU", "Name", "Published", "Is featured?", "Visibility in catalog", "Short description",
        "Description", "Tax status", "In stock?", "Stock", "Regular price", "Categories", "Tags", "Images", "Parent",
        "Attribute 1 name", "Attribute 1 value(s)", "Attribute 1 visible", "Attribute 1 global",
        "Attribute 1 default",
        "Meta: sklad", "Meta: _sklad", "Meta: pochodzenie", "Meta: _pochodzenie",
        "Meta: stopien_palenia", "Meta: _stopien_palenia", "Meta: nuty_smakowe", "Meta: _nuty_smakowe",
        "Meta: czas_realizacji", "Meta: _czas_realizacji"]
out = []
for r in rows:
    pid, nazwa, kat = r["produkt_id"], r["nazwa"], r["kategoria"]
    obraz(pid, nazwa.replace(" 250g", "") if pid == "P001" else nazwa, kat)
    base = {c: "" for c in COLS}
    base.update({"Type": "simple", "SKU": pid, "Name": nazwa, "Published": 1, "Is featured?": 1 if pid in ("P001", "P006", "P017", "P025") else 0,
                 "Visibility in catalog": "visible", "Short description": OPISY[kat],
                 "Description": f"<p>{nazwa} – {OPISY[kat].lower()}</p>", "Tax status": "none",
                 "In stock?": 1, "Stock": r["stan_magazynowy"], "Regular price": f"{float(r['cena_brutto']):.2f}",
                 "Categories": KATEGORIE[kat], "Images": f"{IMG_URL}/{pid}.png",
                 "Meta: czas_realizacji": 2 if kat != "sprzet" else 4, "Meta: _czas_realizacji": "field_bb_produkt_czas_realizacji"})
    if pid in KAWY:
        poch, pal, nuty, sklad = KAWY[pid]
        base.update({"Meta: sklad": f"<p>{sklad}</p>", "Meta: _sklad": "field_bb_produkt_sklad",
                     "Meta: pochodzenie": poch, "Meta: _pochodzenie": "field_bb_produkt_pochodzenie",
                     "Meta: stopien_palenia": pal, "Meta: _stopien_palenia": "field_bb_produkt_stopien_palenia",
                     "Meta: nuty_smakowe": nuty, "Meta: _nuty_smakowe": "field_bb_produkt_nuty_smakowe",
                     "Tags": "speciality, świeżo palona"})
    if pid == "P001":  # produkt wariantowy: 250 g / 1 kg
        base.update({"Type": "variable", "Name": "Kawa ziarnista Etiopia Yirgacheffe", "Regular price": "", "Stock": "",
                     "Attribute 1 name": "Waga", "Attribute 1 value(s)": "250 g, 1 kg", "Attribute 1 visible": 1,
                     "Attribute 1 global": 0, "Attribute 1 default": "250 g"})
        out.append(base)
        for waga, cena, stan, sfx in (("250 g", "49.00", r["stan_magazynowy"], "250G"), ("1 kg", "169.00", 20, "1KG")):
            v = {c: "" for c in COLS}
            v.update({"Type": "variation", "SKU": f"P001-{sfx}", "Name": f"Kawa ziarnista Etiopia Yirgacheffe – {waga}",
                      "Published": 1, "Visibility in catalog": "visible", "Tax status": "none", "In stock?": 1, "Stock": stan, "Regular price": cena,
                      "Parent": "P001", "Attribute 1 name": "Waga", "Attribute 1 value(s)": waga, "Attribute 1 global": 0})
            out.append(v)
        continue
    out.append(base)

with open(OUT_CSV, "w", newline="", encoding="utf-8") as f:
    w = csv.DictWriter(f, fieldnames=COLS)
    w.writeheader()
    w.writerows(out)
print(f"{OUT_CSV.name}: {len(out)} wierszy ({len(rows)} produktów, w tym 1 wariantowy z 2 wariantami); obrazy: {len(list(IMG.glob('*.png')))}")
