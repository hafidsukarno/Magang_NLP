from fastapi import FastAPI, UploadFile, File
from fastapi.responses import JSONResponse
import uvicorn
from paddleocr import PaddleOCR
from pdf2image import convert_from_path
import tempfile
import shutil
import os
import re
from datetime import datetime
import calendar

app = FastAPI(title="OCR Magang API")

# Load OCR sekali
ocr = PaddleOCR(
    use_angle_cls=True,
    lang="latin",
    show_log=False
)

# Mapping Bulan
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

# Extract Duration - IMPROVED WITH ALL PATTERNS
def extract_duration(text):
    txt = text.lower()

    # POLA 1A — FULL DATE → FULL DATE
    pola1 = re.compile(
        r"(\d{1,2})\s*"
        r"(januari|februari|maret|april|mei|juni|juli|agustus|september|oktober|november|desember)"
        r"\s*(\d{4})"
        r".{0,8}?"
        r"(s\.?d\.?|s\.?d\.{1,3}|sd|s\/d|s-d|-|sampai dengan)"
        r".{0,8}?"
        r"(\d{1,2})\s*"
        r"(januari|februari|maret|april|mei|juni|juli|agustus|september|oktober|november|desember)"
        r"\s*(\d{4})"
    )
    m = pola1.search(txt)
    if m:
        d1, m1, y1, _, d2, m2, y2 = m.groups()
        return parse_date(d1, m1, y1), parse_date(d2, m2, y2)

    # POLA 1B — DAY+MONTH → DAY+MONTH+YEAR
    pola1b = re.compile(
        r"(\d{1,2})\s*"
        r"(januari|februari|maret|april|mei|juni|juli|agustus|september|oktober|november|desember)"
        r"(?:\s*(\d{4}))?"
        r".{0,8}?"
        r"(s\.?d\.?|s\.?d\.{1,3}|sd|s\/d|s-d|-|sampai dengan)"
        r".{0,8}?"
        r"(\d{1,2})\s*"
        r"(januari|februari|maret|april|mei|juni|juli|agustus|september|oktober|november|desember)"
        r"\s*(\d{4})"
    )
    m = pola1b.search(txt)
    if m:
        d1, m1, y1, _, d2, m2, y2 = m.groups()
        if y1 is None:
            y1 = y2
        return parse_date(d1, m1, y1), parse_date(d2, m2, y2)

    # POLA 1C - sampai dengan
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

    # POLA 1D - DAY MONTH YEAR s.d MONTH YEAR
    pola1d = re.compile(
        r"(\d{1,2})\s+"
        r"(januari|februari|maret|april|mei|juni|juli|agustus|september|oktober|november|desember)"
        r"\s+(\d{4})\s*"
        r"(?:s\.d\.?|s/d|s-d|sd)\s*"
        r"(januari|februari|maret|april|mei|juni|juli|agustus|september|oktober|november|desember)"
        r"\s+(\d{4})"
    )
    m = pola1d.search(txt)
    if m:
        d1, m1, y1, m2, y2 = m.groups()
        return parse_date(d1, m1, y1), parse_date("01", m2, y2)

    # POLA 1E — DAY MONTH - DAY MONTH YEAR (untuk "16 Juni - 16 Juli 2025")
    pola1e = re.compile(
        r"(\d{1,2})\s+"
        r"(januari|februari|maret|april|mei|juni|juli|agustus|september|oktober|november|desember)"
        r"\s*[-–—]\s*"
        r"(\d{1,2})\s+"
        r"(januari|februari|maret|april|mei|juni|juli|agustus|september|oktober|november|desember)"
        r"\s+(\d{4})"
    )
    m = pola1e.search(txt)
    if m:
        d1, m1, d2, m2, year = m.groups()
        return parse_date(d1, m1, year), parse_date(d2, m2, year)

    # POLA 2 — MONTH - MONTH YEAR (untuk "Agustus - September 2025")
    pola2 = re.compile(
        r"(januari|februari|maret|april|mei|juni|juli|agustus|september|oktober|november|desember)"
        r"\s*[-–—]\s*"
        r"(januari|februari|maret|april|mei|juni|juli|agustus|september|oktober|november|desember)"
        r"\s*(\d{4})"
    )
    m = pola2.search(txt)
    if m:
        m1, m2, year = m.groups()
        return parse_date("01", m1, year), parse_date("01", m2, year)

    # POLA 3 — MONTH YEAR - MONTH YEAR
    pola3 = re.compile(
        r"(januari|februari|maret|april|mei|juni|juli|agustus|september|oktober|november|desember)"
        r"\s+(\d{4})\s*"
        r"[-–—]\s*"
        r"(januari|februari|maret|april|mei|juni|juli|agustus|september|oktober|november|desember)"
        r"\s+(\d{4})"
    )
    m = pola3.search(txt)
    if m:
        m1, y1, m2, y2 = m.groups()
        return parse_date("01", m1, y1), parse_date("01", m2, y2)

    # POLA 4 — SINGLE MONTH YEAR (untuk "Agustus 2025")
    pola4 = re.compile(
        r"\b(januari|februari|maret|april|mei|juni|juli|agustus|september|oktober|november|desember)"
        r"\s+(\d{4})\b"
    )
    m = pola4.search(txt)
    if m:
        month, year = m.groups()

        month_num = int(bulan_map[month])
        year_num = int(year)

        start_date = f"{year}-{bulan_map[month]}-01"
        last_day = calendar.monthrange(year_num, month_num)[1]
        end_date = f"{year}-{bulan_map[month]}-{str(last_day).zfill(2)}"

        return start_date, end_date

    return None, None

# Normalisasi
def normalize(text: str) -> str:
    t = text.lower()
    t = t.replace("_", ".")
    t = re.sub(r"s\s*[\.\-_/]?\s*d", "s.d.", t)
    t = t.replace("\n", " ")
    t = re.sub(r"\s+", " ", t)
    return t.strip()

# Ekstraksi Field - FULLY IMPROVED
def extract_structured_fields(original_text: str):

    clean = normalize(original_text)
    fields = {}

    # 1. University - IMPROVED
    university_patterns = [
        r"(universitas\s+syiah\s+kuala[^\n]*fakultas[^\n]*)",  # USK specific
        r"(universitas\s+[a-z\s]+fakultas[^\n]+)",
        r"(universitas\s*islam\s*negeri\s*[a-z\s]+)",
        r"(uin\s+[a-z\s]+)",
        r"(politeknik\s+negeri\s+[a-z\s]+)",
        r"(politekniknegeri[a-z\s]+)",
        r"(universitas\s+[a-z\s]+)",
    ]

    for pat in university_patterns:
        m = re.search(pat, clean, flags=re.IGNORECASE)
        if m:
            uni_text = m.group(1).upper().strip()
            # Clean up extra whitespace
            uni_text = re.sub(r'\s+', ' ', uni_text)
            fields["university"] = uni_text
            break

    # 2. Major - IMPROVED with more patterns
    major_patterns = [
        # Pattern khusus untuk USK format
        r"program\s+studi\s+s[i1]\s+([a-z\s]+?)(?=departemen|fakultas|teknik kebumian|\n)",
        r"program\s+studi\s+([a-z\s]+?)(?=departemen|fakultas|\n)",

        # Pattern umum
        r"jurusan\s+([a-z\s]+?)\s*(?=program studi)",
        r"program\s+studi\s*:?\s*([a-z\s]+?)(?=\n|jalan|telepon|website)",
        r"prodi\s*:?\s*([a-z\s]+)",
        r"jurusan\s+([a-z\s]+?)(?=\n|jalan|telepon)",
        r"mahasiswa\s+jurusan\s+([a-z\s]+)",

        # Fallback: cari setelah "program studi"
        r"program\s+studi\s+([a-z\s]{5,40})",
    ]

    detected_major = None

    for pat in major_patterns:
        m = re.search(pat, clean, flags=re.IGNORECASE)
        if m:
            major = m.group(1).strip()

            # Clean up
            major = re.sub(r"[0-9]{6,}", "", major)  # Remove long numbers
            major = re.sub(r"\b(nama|no|npm|nim|departemen|fakultas|teknik)\b.*", "", major, flags=re.IGNORECASE).strip()
            major = re.sub(r"\s+", " ", major)  # Clean multiple spaces

            # Filter: minimal 4 karakter, maksimal 8 kata
            words = major.split()
            if len(major) >= 4 and len(words) <= 8:
                detected_major = major
                break

    # Fallback: cari dari list major yang umum
    if not detected_major:
        possible_majors = [
            r"teknik\s+geofisika", r"teknik\s+informatika", r"teknik\s+industri",
            r"teknik\s+kimia", r"teknik\s+mesin", r"teknik\s+elektro",
            r"teknik\s+sipil", r"teknik\s+komputer", r"teknik\s+lingkungan",
            r"sistem\s+informasi", r"ilmu\s+hukum", r"ilmu\s+komputer",
            r"ilmu\s+komunikasi", r"ilmu\s+administrasi", r"ilmu\s+politik",
            r"kimia", r"fisika", r"matematika", r"biologi",
            r"hukum", r"akuntansi", r"manajemen", r"farmasi",
            r"teknologi\s+elektronika", r"teknologi\s+informasi"
        ]

        for p in possible_majors:
            m = re.search(r"\b" + p + r"\b", clean, flags=re.IGNORECASE)
            if m:
                detected_major = m.group(0)
                break

    if detected_major:
        fields["major"] = detected_major.title()

    # 3. Duration - IMPROVED with all date patterns
    duration_patterns = [
        # Pattern dengan tanggal lengkap full
        r"([0-9]{1,2}\s+[a-z]+\s+\d{4}\s*s\.d\.\s*[0-9]{1,2}\s+[a-z]+\s+\d{4})",
        r"([0-9]{1,2}\s+[a-z]+\s+\d{4}\s*[\.\-_]*s\.d\.[\.\-_]*\s*[0-9]{1,2}\s+[a-z]+\s+\d{4})",
        r"([0-9]{1,2}\s+[a-z]+\s+\d{4}s\.d\.[0-9]{1,2}\s+[a-z]+\s+\d{4})",

        # Pattern "sampai dengan"
        r"(mulai\s+\d{1,2}\s+[a-z]+\s+\d{4}\s+sampai\s+dengan\s+\d{1,2}\s+[a-z]+\s+\d{4})",
        r"(\d{1,2}\s+[a-z]+\s+\d{4}\s+sampai\s+dengan\s+\d{1,2}\s+[a-z]+\s+\d{4})",
        r"(\d{1,2}\s+[a-z]+\s+sampai\s+dengan\s+\d{1,2}\s+[a-z]+\s+\d{4})",

        # Pattern dengan s.d
        r"(\d{1,2}\s+[a-z]+\s*s\.d\.\s*\d{1,2}\s+[a-z]+\s+\d{4})",
        r"(\d{1,2}\s+[a-z]+\s+\d{4}\s*s\.d\.\s*[A-Za-z]+\s+\d{4})",
        r"(\d{1,2}\s+[a-z]+\s+\d{4}\s*s\.d\s+[A-Za-z]+\s+\d{4})",

        # Pattern DAY MONTH - DAY MONTH YEAR untuk "16 Juni - 16 Juli 2025"
        r"(\d{1,2}\s+(?:januari|februari|maret|april|mei|juni|juli|agustus|september|oktober|november|desember)\s*[-–—]\s*\d{1,2}\s+(?:januari|februari|maret|april|mei|juni|juli|agustus|september|oktober|november|desember)\s+\d{4})",

        # Pattern dengan "tanggal" keyword
        r"tanggal\s+(\d{1,2}\s+(?:januari|februari|maret|april|mei|juni|juli|agustus|september|oktober|november|desember)\s*[-–—]\s*\d{1,2}\s+(?:januari|februari|maret|april|mei|juni|juli|agustus|september|oktober|november|desember)\s+\d{4})",

        # Pattern BULAN - BULAN TAHUN untuk "Agustus - September 2025"
        r"((?:januari|februari|maret|april|mei|juni|juli|agustus|september|oktober|november|desember)\s*[-–—]\s*(?:januari|februari|maret|april|mei|juni|juli|agustus|september|oktober|november|desember)\s*\d{4})",

        # Pattern BULAN TAHUN - BULAN TAHUN
        r"((?:januari|februari|maret|april|mei|juni|juli|agustus|september|oktober|november|desember)\s+\d{4}\s*[-–—]\s*(?:januari|februari|maret|april|mei|juni|juli|agustus|september|oktober|november|desember)\s+\d{4})",

        # Pattern SINGLE MONTH untuk "Jadwal KKP : Agustus 2025"
        r"jadwal\s+(?:kkp|magang|praktek|kerja praktek)\s*:?\s*((?:januari|februari|maret|april|mei|juni|juli|agustus|september|oktober|november|desember)\s+\d{4})",

        # Pattern SINGLE MONTH umum
        r"\b((?:januari|februari|maret|april|mei|juni|juli|agustus|september|oktober|november|desember)\s+\d{4})\b",

        # Pattern umum dengan "rencana tanggal"
        r"rencana tanggal\s*([0-9\sA-Za-z\.]+s\.d\.[0-9\sA-Za-z\.]+)",
    ]

    duration_text = None
    for pat in duration_patterns:
        m = re.search(pat, clean, flags=re.IGNORECASE)
        if m:
            duration_text = m.group(1).strip()
            fields["duration"] = duration_text
            break

    # Jika duration tidak ditemukan tapi ada text alternatif
    if not duration_text:
        alternative_patterns = [
            r"(atau\s+mengikuti\s+jadwal\s+yang\s+dikeluarkan\s+perusahaan)",
            r"(sesuai\s+jadwal\s+perusahaan)",
            r"(menyesuaikan\s+dengan\s+kebutuhan\s+perusahaan)",
        ]

        for pat in alternative_patterns:
            m = re.search(pat, clean, flags=re.IGNORECASE)
            if m:
                fields["duration"] = m.group(1).strip()
                fields["duration_flexible"] = True
                break

    start = None
    end = None
    if duration_text:
        start, end = extract_duration(duration_text)
        if start:
            fields["duration_start"] = start
        if end:
            fields["duration_end"] = end

    if start and end:
        y1, m1, d1 = map(int, start.split("-"))
        y2, m2, d2 = map(int, end.split("-"))

        start_date = datetime(y1, m1, d1)
        end_date = datetime(y2, m2, d2)

        diff_days = (end_date - start_date).days
        months = round(diff_days / 30)

        if months < 1:
            months = 1

        fields["duration_months"] = months

    # 4. Purpose - IMPROVED with more patterns
    purpose_patterns = [
        # Pattern khusus USK
        r"(dengan\s+hormat[^\n]+(?:mahasiswa\s+diwajibkan|mengajukan)[^\n\.]+)",
        r"(sesuai\s+dengan\s+kurikulum[^\n]+kerja\s+praktek[^\n\.]+)",

        # Pattern umum
        r"(dalam\s+rangka[^\n\.]+)",
        r"(bermaksud\s+melakukan\s+magang[^\n\.]+)",
        r"(dalam\s+upaya[^\n\.]+)",
        r"(sehubungan\s+dengan\s+surat\s+permohonan\s+magang[^\n\.]+)",
        r"(mohon\s+kesediaan[^\n\.]+magang[^\n\.]+)",
        r"(mengajukan\s+permohonan[^\n\.]+magang[^\n\.]+)",

        # Pattern untuk format "dengan ini kami mohon"
        r"(dengan\s+ini\s+kami\s+mohon[^\n\.]+)",
    ]

    for pat in purpose_patterns:
        m = re.search(pat, clean, flags=re.IGNORECASE)
        if m:
            purpose_text = m.group(1).strip()

            # Extend purpose sampai titik atau newline berikutnya
            start_pos = clean.find(purpose_text)
            if start_pos != -1:
                end_pos = clean.find('.', start_pos)
                if end_pos == -1:
                    end_pos = clean.find('\n\n', start_pos)
                if end_pos != -1:
                    purpose_text = clean[start_pos:end_pos].strip()

            # Clean up
            purpose_text = re.sub(r'\s+', ' ', purpose_text)

            fields["purpose"] = purpose_text
            break

    return fields


# ======================================================
# Endpoint OCR PDF
# ======================================================
@app.post("/ocr")
async def ocr_pdf(file: UploadFile = File(...)):

    with tempfile.NamedTemporaryFile(delete=False, suffix=".pdf") as tmp:
        shutil.copyfileobj(file.file, tmp)
        pdf_path = tmp.name

    try:
        images = convert_from_path(pdf_path, dpi=300)

        full_text = ""
        for img in images:
            with tempfile.NamedTemporaryFile(delete=False, suffix=".jpg") as img_tmp:
                img.save(img_tmp.name, "JPEG")
                result = ocr.ocr(img_tmp.name, cls=True)

                for line in result:
                    for part in line:
                        full_text += part[1][0] + "\n"

        fields = extract_structured_fields(full_text)

        return JSONResponse({
            "success": True,
            "extracted_text": full_text,
            "fields": fields
        })

    except Exception as e:
        return JSONResponse({"success": False, "error": str(e)}, status_code=500)

    finally:
        os.remove(pdf_path)


if __name__ == "__main__":
    uvicorn.run(app, host="127.0.0.1", port=8500)
