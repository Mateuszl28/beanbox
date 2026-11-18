"""Generuje definicje grup pól ACF (Local JSON) do beanbox/acf-json/.

ACF przy każdym zapisie grupy w panelu i tak nadpisze te pliki – skrypt służy do pierwszego
utworzenia spójnych kluczy. Po `git pull` druga osoba widzi w ACF → Grupy pól „Dostępna synchronizacja”.

  python narzedzia/generuj_acf_json.py
"""
import json
import time
from pathlib import Path

OUT = Path(__file__).resolve().parent.parent / "beanbox" / "acf-json"
OUT.mkdir(parents=True, exist_ok=True)
NOW = int(time.time())


def f(group, name, label, type_, **kw):
    d = {"key": f"field_bb_{group}_{name}", "label": label, "name": name, "type": type_,
         "instructions": kw.pop("instructions", ""), "required": kw.pop("required", 0),
         "conditional_logic": 0, "wrapper": {"width": kw.pop("width", ""), "class": "", "id": ""}}
    d.update(kw)
    return d


def group(key, title, fields, location, order=0, desc=""):
    data = {"key": f"group_bb_{key}", "title": title, "fields": fields, "location": location,
            "menu_order": order, "position": "normal", "style": "default", "label_placement": "top",
            "instruction_placement": "label", "hide_on_screen": "", "active": True, "description": desc,
            "show_in_rest": 0, "modified": NOW}
    (OUT / f"group_bb_{key}.json").write_text(json.dumps(data, ensure_ascii=False, indent=4), encoding="utf-8")
    print(f"group_bb_{key}.json – {title} ({len(fields)} pól)")


def block(name):
    return [[{"param": "block", "operator": "==", "value": f"acf/{name}"}]]


ICONS = {"bean": "☕ Kawa", "truck": "🚚 Dostawa", "fire": "🔥 Świeże palenie", "leaf": "🌱 Ekologia",
         "gift": "🎁 Prezent", "return": "↩ Zwrot"}

group("blok_hero", "Blok: Hero", [
    f("hero", "title", "Tytuł", "text", required=1, maxlength=80),
    f("hero", "text", "Opis", "textarea", rows=3, maxlength=240, new_lines=""),
    f("hero", "image", "Zdjęcie w tle", "image", return_format="id", preview_size="medium", library="all",
      instructions="Maks. 2000 px szerokości."),
    f("hero", "button", "Przycisk", "link", return_format="array"),
], block("hero"))

usp_fields = [f("usp", "title", "Nagłówek sekcji (opcjonalny)", "text")]
for i in range(1, 5):
    usp_fields.append(f("usp", f"usp_{i}", f"Przewaga {i}", "group", layout="block", width="50", sub_fields=[
        f(f"usp{i}", "icon", "Ikona", "select", choices=ICONS, default_value="bean", return_format="value", ui=0),
        f(f"usp{i}", "title", "Tytuł", "text", maxlength=40),
        f(f"usp{i}", "text", "Opis", "textarea", rows=2, maxlength=140, new_lines=""),
    ]))
group("blok_usp", "Blok: Przewagi (USP)", usp_fields, block("usp"))

group("blok_opinie", "Blok: Opinie klientów", [
    f("opinie", "title", "Nagłówek sekcji", "text", default_value="Opinie klientów"),
    f("opinie", "count", "Liczba opinii", "number", min=1, max=12, default_value=3),
    f("opinie", "min_rating", "Minimalna ocena", "select", choices={str(i): f"{i}★" for i in range(1, 6)},
      default_value="4", return_format="value"),
], block("opinie"))

group("blok_faq", "Blok: FAQ", [
    f("faq", "title", "Nagłówek sekcji", "text", default_value="Najczęstsze pytania"),
    f("faq", "count", "Liczba pytań", "number", min=1, max=20, default_value=6),
], block("faq"))

group("opinia", "Opinia – szczegóły", [
    f("opinia", "ocena", "Ocena", "select", required=1, choices={str(i): f"{i}★" for i in range(1, 6)},
      default_value="5", return_format="value"),
    f("opinia", "miasto", "Miasto klienta", "text"),
    f("opinia", "produkt", "Produkt", "post_object", post_type=["product"], return_format="id", allow_null=1,
      multiple=0, ui=1),
], [[{"param": "post_type", "operator": "==", "value": "opinia"}]])
