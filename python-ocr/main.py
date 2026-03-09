import re

bulan_map = {
    "januari": "01", "februari": "02", "maret": "03", "april": "04",
    "mei": "05", "juni": "06", "juli": "07", "agustus": "08",
    "september": "09", "oktober": "10", "november": "11", "desember": "12"
}

def parse_date(day, month, year):
    if month not in bulan_map:
        return None
    if not day:
        day = "01"
    return f"{year}-{bulan_map[month]}-{day.zfill(2)}"

def extract_duration(text):
    txt = text.lower()

    # POLA 1A — FULL DATE → FULL DATE (kedua punya tahun)
    pola1 = re.compile(
        r"(\d{1,2})\s*"
        r"(januari|februari|maret|april|mei|juni|juli|agustus|september|oktober|november|desember)"
        r"\s*(\d{4})"
        r".{0,8}?"
        r"(s\.?d\.?|s\.?d\.{1,3}|sd|s\/d|s-d|-)"
        r".{0,8}?"
        r"(\d{1,2})\s*"
        r"(januari|februari|maret|april|mei|juni|juli|agustus|september|oktober|november|desember)"
        r"\s*(\d{4})"
    )

    m = pola1.search(txt)
    if m:
        d1, m1, y1, _, d2, m2, y2 = m.groups()
        return parse_date(d1, m1, y1), parse_date(d2, m2, y2)

    # POLA 1B — DAY/MONTH → DAY/MONTH/YEAR
    # Contoh:
    # 03 maret s.d.. 03 april 2025
    pola1b = re.compile(
        r"(\d{1,2})\s*"           # start day
        r"(januari|februari|maret|april|mei|juni|juli|agustus|september|oktober|november|desember)"  # start month
        r"(?:\s*(\d{4}))?"        # optional start year
        r".{0,8}?"
        r"(s\.?d\.?|s\.?d\.{1,3}|sd|s\/d|s-d|-)"
        r".{0,8}?"
        r"(\d{1,2})\s*"          # end day
        r"(januari|februari|maret|april|mei|juni|juli|agustus|september|oktober|november|desember)"  # end month
        r"\s*(\d{4})"            # END YEAR (mandatory)
    )

    m = pola1b.search(txt)
    if m:
        d1, m1, y1, _, d2, m2, y2 = m.groups()

        if y1 is None:
            y1 = y2

        return parse_date(d1, m1, y1), parse_date(d2, m2, y2)

    # POLA 1C
    pola1c = re.compile(
        r"(\d{1,2})\s*"
        r"(januari|februari|maret|april|mei|juni|juli|agustus|september|oktober|november|desember)"
        r"\s*(\d{4})\s*"
        r"sampai\s+dengan\s*"
        r"(\d{1,2})\s*"
        r"(januari|februari|maret|april|mei|juni|juli|agustus|september|oktober|november|desember)"
        r"\s*(\d{4})"
    )

    m = pola1c.search(txt)
    if m:
        d1, m1, y1, d2, m2, y2 = m.groups()
        return parse_date(d1, m1, y1), parse_date(d2, m2, y2)

    # POLA 1D — FULL DATE → MONTH YEAR
    pola1d = re.compile(
        r"(\d{1,2})\s*"
        r"(januari|februari|maret|april|mei|juni|juli|agustus|september|oktober|november|desember)"
        r"\s*(\d{4})"
        r".{0,10}?"
        r"(s\.?d\.?|sd|s/d|s-d|-)"
        r".{0,10}?"
        r"(januari|februari|maret|april|mei|juni|juli|agustus|september|oktober|november|desember)"
        r"\s*(\d{4})"
    )

    m = pola1d.search(txt)
    if m:
        d1, m1, y1, m2, y2 = m.groups()
        return parse_date(d1, m1, y1), parse_date("01", m2, y2)


    # POLA 2 — MONTH - MONTH YEAR
    pola2 = re.compile(
        r"(januari|februari|maret|april|mei|juni|juli|agustus|september|oktober|november|desember)"
        r"\s*[-–]\s*"
        r"(januari|februari|maret|april|mei|juni|juli|agustus|september|oktober|november|desember)"
        r"\s*(\d{4})"
    )

    m = pola2.search(txt)
    if m:
        m1, m2, year = m.groups()
        return parse_date("01", m1, year), parse_date("01", m2, year)

    return None, None

# TEST
tests = [
    "duration: 01 september 2025.s.d.. 30 januari 2026",
    "duration: 03 maret s.d.. 03 april 2025. sehubung dengan itu",
    "duration: agustus-september 2025",
    "duration: 04 agustus 2025s.d..04 januari 2026",
    "duration: 4 agustus 2025 sampai dengan 4 september 2026"
]

for t in tests:
    print(t, "=>", extract_duration(t))
