"""Składa sprawozdanie/<plik>.md w sprawozdanie/<plik>.docx
(nagłówki, akapity, listy, tabele, obrazy, **pogrubienia**, `kod`, *kursywa*)."""
import re
from pathlib import Path

from docx import Document
from docx.enum.table import WD_TABLE_ALIGNMENT
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.oxml import OxmlElement
from docx.oxml.ns import qn
from docx.shared import Cm, Pt, RGBColor

import sys

ROOT = Path(__file__).resolve().parent.parent / "sprawozdanie"  # python narzedzia/sprawozdanie_docx.py Sprawozdanie_lab2.md Sprawozdanie_lab2.docx
# python scripts/build_report_docx.py [źródło.md wynik.docx]  (domyślnie sprawozdanie)
SRC, DST = ((ROOT / sys.argv[1], ROOT / sys.argv[2]) if len(sys.argv) > 2
            else (ROOT / "Sprawozdanie_lab2.md", ROOT / "Sprawozdanie_lab2.docx"))

doc = Document()
sec = doc.sections[0]
sec.left_margin = sec.right_margin = Cm(2)
sec.top_margin = sec.bottom_margin = Cm(2)
st = doc.styles["Normal"]
st.font.name = "Calibri"
st.font.size = Pt(10.5)
TOKEN = re.compile(r"(\*\*[^*]+\*\*|`[^`]+`|\*[^*\s][^*]*\*)")


def runs(par, text):
    for part in TOKEN.split(text):
        if not part:
            continue
        if part.startswith("**"):
            par.add_run(part[2:-2]).bold = True
        elif part.startswith("`"):
            r = par.add_run(part[1:-1])
            r.font.name = "Consolas"
            r.font.size = Pt(9.5)
            r.font.color.rgb = RGBColor(0x8B, 0x1A, 0x1A)
        elif part.startswith("*"):
            par.add_run(part[1:-1]).italic = True
        else:
            par.add_run(part)


def shade(cell, hex_):
    tcPr = cell._tc.get_or_add_tcPr()
    s = OxmlElement("w:shd")
    s.set(qn("w:val"), "clear")
    s.set(qn("w:fill"), hex_)
    tcPr.append(s)


def table(rows):
    data = [[c.strip() for c in r.strip().strip("|").split("|")] for r in rows]
    data = [r for r in data if not all(set(c) <= set("-: ") for c in r)]
    t = doc.add_table(rows=len(data), cols=len(data[0]))
    t.style = "Table Grid"
    t.alignment = WD_TABLE_ALIGNMENT.CENTER
    for i, r in enumerate(data):
        for j, c in enumerate(r[: len(data[0])]):
            cell = t.cell(i, j)
            cell.text = ""
            p = cell.paragraphs[0]
            runs(p, c)
            for run in p.runs:
                run.font.size = Pt(9)
                if i == 0:
                    run.bold = True
            if i == 0:
                shade(cell, "D9E2F3")
    doc.add_paragraph()


lines = SRC.read_text(encoding="utf-8").splitlines()
i = 0
fig = 0
while i < len(lines):
    ln = lines[i]
    if ln.strip().startswith("```"):
        i += 1
        while i < len(lines) and not lines[i].strip().startswith("```"):
            p = doc.add_paragraph()
            p.paragraph_format.left_indent = Cm(0.5)
            p.paragraph_format.space_after = Pt(0)
            p.paragraph_format.space_before = Pt(0)
            p.paragraph_format.line_spacing = 1.0
            r = p.add_run(lines[i])
            r.font.name = "Consolas"
            r.font.size = Pt(9)
            i += 1
        i += 1
        continue
    if ln.startswith("|"):
        block = []
        while i < len(lines) and lines[i].startswith("|"):
            block.append(lines[i])
            i += 1
        table(block)
        continue
    m = re.match(r"!\[(.*?)\]\((.*?)\)", ln.strip())
    if m:
        fig += 1
        img = ROOT / m.group(2)
        doc.add_picture(str(img), width=Cm(16.5))
        doc.paragraphs[-1].alignment = WD_ALIGN_PARAGRAPH.CENTER
        cap = doc.add_paragraph()
        cap.alignment = WD_ALIGN_PARAGRAPH.CENTER
        r = cap.add_run(f"Rys. {fig}. {m.group(1)}")
        r.italic = True
        r.font.size = Pt(9)
    elif ln.startswith("# "):
        doc.add_heading(ln[2:], 0)
    elif ln.startswith("## "):
        doc.add_heading(ln[3:], 1)
    elif ln.startswith("#### "):
        doc.add_heading(ln[5:], 3)
    elif ln.startswith("### "):
        doc.add_heading(ln[4:], 2)
    elif ln.strip() == "---":
        pass
    elif re.match(r"^\s*- ", ln):
        level = (len(ln) - len(ln.lstrip())) // 2
        p = doc.add_paragraph(style="List Bullet 2" if level else "List Bullet")
        runs(p, ln.strip()[2:])
    elif re.match(r"^\d+\. ", ln):
        p = doc.add_paragraph(style="List Number")
        runs(p, re.sub(r"^\d+\. ", "", ln))
    elif ln.strip():
        p = doc.add_paragraph()
        runs(p, ln.strip())
    i += 1

doc.save(DST)
print(DST, f"({fig} rysunków)")
