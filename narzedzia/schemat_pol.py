"""Schemat grup pól ACF (nazwa, typ, format zwracany, gdzie się pojawia) – rysunek z plików acf-json.

  python narzedzia/schemat_pol.py [lab2|lab3]  -> sprawozdanie/zrzuty/<lab>_schemat_pol.png
"""
import json
import sys
from pathlib import Path

import matplotlib
matplotlib.use("Agg")
import matplotlib.pyplot as plt
from matplotlib.patches import FancyBboxPatch

ROOT = Path(__file__).resolve().parent.parent
LAB = sys.argv[1] if len(sys.argv) > 1 else "lab2"
GRUPY_LAB3 = {"group_bb_produkt", "group_bb_kategoria_baner"}
LOC = {"block": "Blok", "post_type": "Typ wpisu", "taxonomy": "Taksonomia"}


def rows(fields, prefix=""):
    for f in fields:
        ret = f.get("return_format", "") or "wartość"
        if f["type"] == "group":
            yield (prefix + f["name"], "group", "tablica")
            yield from rows(f.get("sub_fields", []), prefix + "  └ ")
        else:
            yield (prefix + f["name"], f["type"], ret)


groups = []
for p in sorted((ROOT / "beanbox" / "acf-json").glob("*.json")):
    g = json.loads(p.read_text(encoding="utf-8"))
    if (g["key"] in GRUPY_LAB3) != (LAB == "lab3"):
        continue
    loc = " lub ".join(f"{LOC.get(r['param'], r['param'])} = {r['value']}" for grp in g["location"] for r in grp)
    groups.append((g["title"], loc, list(rows(g["fields"]))))

cols = 3 if LAB == "lab2" else 2
H = [1.1 + 0.32 * len(r) for _, _, r in groups]
nrows = -(-len(groups) // cols)
row_h = [max(H[i * cols:(i + 1) * cols]) for i in range(nrows)]
fig_h = sum(row_h) + 0.4 * nrows + 0.8
fig, ax = plt.subplots(figsize=(6.2 * cols, fig_h))
ax.set_xlim(0, 6.2 * cols)
ax.set_ylim(0, fig_h)
ax.axis("off")
ax.text(0.1, fig_h - 0.45, "Grupy pól ACF motywu BeanBox" + (" – WooCommerce (lab 3)" if LAB == "lab3" else " (lab 2)"),
        fontsize=14, weight="bold")
y_top = fig_h - 0.9
for i, (title, loc, rr) in enumerate(groups):
    r, c = divmod(i, cols)
    y = y_top - sum(row_h[:r]) - 0.4 * r
    x = 0.15 + c * 6.2
    h = H[i]
    ax.add_patch(FancyBboxPatch((x, y - h), 5.8, h, boxstyle="round,pad=0.03", fc="#fffaf5", ec="#8a5a3b", lw=1.4))
    ax.add_patch(FancyBboxPatch((x, y - 0.75), 5.8, 0.75, boxstyle="round,pad=0.03", fc="#3b2418", ec="#3b2418"))
    ax.text(x + 0.15, y - 0.3, title, color="white", fontsize=10.5, weight="bold", va="center")
    ax.text(x + 0.15, y - 0.6, "gdzie: " + loc, color="#eadfd4", fontsize=8.5, va="center")
    ax.text(x + 0.15, y - 0.98, "nazwa pola", fontsize=8, color="#8a5a3b", weight="bold")
    ax.text(x + 2.9, y - 0.98, "typ", fontsize=8, color="#8a5a3b", weight="bold")
    ax.text(x + 4.3, y - 0.98, "format zwracany", fontsize=8, color="#8a5a3b", weight="bold")
    for j, (n, t, f) in enumerate(rr):
        yy = y - 1.28 - 0.32 * j
        ax.text(x + 0.15, yy, n, fontsize=8.5, family="monospace")
        ax.text(x + 2.9, yy, t, fontsize=8.5)
        ax.text(x + 4.3, yy, f, fontsize=8.5)
out = ROOT / "sprawozdanie" / "zrzuty" / f"{LAB}_schemat_pol.png"
out.parent.mkdir(parents=True, exist_ok=True)
fig.savefig(out, dpi=140, bbox_inches="tight")
print(out)
