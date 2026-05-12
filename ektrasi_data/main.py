from PIL import Image
import pytesseract
import re
import spacy
from spacy.lang.id import Indonesian
from spacy.pipeline import EntityRuler

# Tentukan path Tesseract (WAJIB DI WINDOWS)
pytesseract.pytesseract.tesseract_cmd = r'C:\Program Files\Tesseract-OCR\tesseract.exe'

# Tentukan path Poppler (WAJIB DI WINDOWS UNTUK BACA PDF)
POPPLER_PATH = r'C:\Program Files\poppler-24.08.0\Library\bin'

def get_text_from_file(file_path):
    """ Membaca teks baik dari Gambar (PNG/JPG) maupun PDF (hasil Scanner) """
    ext = file_path.lower().split('.')[-1]
    
    if ext in ['png', 'jpg', 'jpeg']:
        image = Image.open(file_path)
        return pytesseract.image_to_string(image)
        
    elif ext == 'pdf':
        try:
            from pdf2image import convert_from_path
            # Hanya membaca halaman pertama agar proses jauh lebih cepat
            images = convert_from_path(file_path, poppler_path=POPPLER_PATH, first_page=1, last_page=1)
            
            full_text = ""
            for i, img in enumerate(images):
                print(f"      ---> Membaca PDF Halaman {i+1}...")
                teks_hal = pytesseract.image_to_string(img)
                full_text += f"{teks_hal}"
                
            return full_text
        except ImportError:
             raise Exception("Library 'pdf2image' belum di-install. Install dulu dengan: pip install pdf2image")
        except Exception as e:
             raise Exception(f"Gagal membaca PDF: {e}\n⚠️ Pastikan Poppler terinstall di Windows.")
    else:
        raise Exception(f"Format file .{ext} tidak didukung!")

def cleaning_text(text):
    """ Membersihkan teks hasil OCR """
    # 1. Hapus noise simbol OCR yang sering muncul (____, ====, ||||)
    text = re.sub(r'[_=|]{2,}', ' ', text)
    
    # 2. Mengganti baris baru (\n) yang berlebihan menjadi spasi
    text = re.sub(r'\n+', ' ', text)
    
    # 3. Menghapus spasi ganda
    text = re.sub(r'\s+', ' ', text).strip()
    return text

def normalize_text(text):
    """ Normalisasi teks: lowercase dan standarisasi kata kunci umum """
    # 1. Lowercase
    text = text.lower()
    
    # 2. Standarisasi Singkatan Umum
    text = re.sub(r'\btgl\b', 'tanggal', text)
    text = re.sub(r'\bno\b', 'nomor', text)
    text = re.sub(r'\bnim\b', 'nomor induk mahasiswa', text)
    text = re.sub(r'\bnpm\b', 'nomor pokok mahasiswa', text)
    text = re.sub(r'\bprodi\b', 'program studi', text)
    text = re.sub(r'\buniv\b', 'universitas', text)
    text = re.sub(r'\bjur\b', 'jurusan', text)
    
    # 3. Hapus karakter non-alfanumerik kecuali spasi dan simbol penting untuk ekstraksi
    # Kita simpan: . / - ( ) : ~ , agar regex tanggal dan pembatas tetap jalan
    text = re.sub(r'[^a-z0-9\s\.\/\-\(\)\:\~\,]', ' ', text)
    
    # 4. Hapus spasi ganda
    text = re.sub(r'\s+', ' ', text).strip()
    return text


# ─────────────────────────────────────────────────────────────────────────────
# Setup pipeline spaCy sekali saja (bukan di dalam fungsi)
# ─────────────────────────────────────────────────────────────────────────────

# Kata-kata yang TIDAK boleh masuk sebagai bagian dari nama UNIV / JURUSAN
# (berfungsi sebagai pembatas agar entitas tidak "rakus"/greedy)
_BATAS_KATA = [
    "jurusan", "jalan", "jl", "program", "fakultas", "alamat", "banda",
    "aceh", "telp", "telepon", "fax", "kode", "pos", "email", "website",
    "kepada", "yang", "untuk", "dari", "di", "pada",
    "nomor", "no", "tanggal", "tgl", "perihal", "hal", "lampiran",
    "merdeka", "belajar", "mbkm", "kuliah",
]


# Kata trigger yang menandai konteks tanggal magang
_TRIGGER_TANGGAL = {

    "antara", "dari", "tanggal", "mulai", "rentang", "terhitung",
    "selama", "periode", "sejak", "tmt", "pada", "tgl", "s.d", "sampai",
    "dengan", "hingga", "s/d",
}

def _build_spacy_nlp():
    """
    Membangun pipeline spaCy dengan EntityRuler untuk mendeteksi:
      - UNIV         : nama universitas / politeknik
      - JURUSAN      : nama jurusan / fakultas / program studi
      - DATE_TGL     : tanggal lengkap  → "DD Bulan YYYY" atau "Bulan YYYY"
      - DATE_PARTIAL : tanggal tanpa tahun → "DD Bulan" (misal: "01 Agustus")
    """
    nlp = Indonesian()  # Tokenizer bahasa Indonesia (tanpa model ML besar)
    ruler = nlp.add_pipe("entity_ruler", config={"overwrite_ents": True})

    # ── Pola UNIV ──────────────────────────────────────────────────────────────
    patterns_univ = [
        # "Politeknik Negeri <Nama 1-10 kata>"
        {"label": "UNIV", "pattern": [
            {"LOWER": "politeknik"},
            {"LOWER": "negeri"},
            {"IS_ALPHA": True, "LOWER": {"NOT_IN": _BATAS_KATA}, "OP": "+"},
        ]},
        # "Universitas <Nama 1-10 kata>"
        {"label": "UNIV", "pattern": [
            {"LOWER": "universitas"},
            {"IS_ALPHA": True, "LOWER": {"NOT_IN": _BATAS_KATA}, "OP": "+"},
        ]},
        # "UIN / IAIN / STMIK / dll <Nama opsional>"
        {"label": "UNIV", "pattern": [
            {"TEXT": {"REGEX": r"^(UIN|IAIN|STMIK|STIE|STIKES|AMIK|STKIP|UNTIRTA|UNTAN|UNDIP|UNPAD)$"}},
            {"IS_ALPHA": True, "LOWER": {"NOT_IN": _BATAS_KATA}, "OP": "*"},
        ]},
    ]

    # ── Pola JURUSAN ───────────────────────────────────────────────────────────
    _BATAS_JURUSAN = [
        "politeknik", "universitas", "uin", "iain", "jalan", "jl", "alamat",
        "nomor", "no", "telp", "email", "pada", "di", "kepada", "yang",
        "program", "studi", "prodi", "merdeka", "belajar", "mbkm", "kuliah"
    ]
    patterns_jurusan = [
        # "Jurusan <Nama 1-5 kata>"
        {"label": "JURUSAN", "pattern": [
            {"LOWER": "jurusan"},
            {"IS_ALPHA": True, "LOWER": {"NOT_IN": _BATAS_JURUSAN}, "OP": "+"},
        ]},
        # "Fakultas <Nama 1-10 kata>"
        {"label": "JURUSAN", "pattern": [
            {"LOWER": "fakultas"},
            {"IS_ALPHA": True, "LOWER": {"NOT_IN": _BATAS_JURUSAN}, "OP": "+"},
        ]},
        # "Departemen <Nama 1-10 kata>"
        {"label": "JURUSAN", "pattern": [
            {"LOWER": "departemen"},
            {"IS_ALPHA": True, "LOWER": {"NOT_IN": _BATAS_JURUSAN}, "OP": "+"},
        ]},
    ]

    # ── Pola PRODI (Program Studi) ─────────────────────────────────────────────
    # Label terpisah dari JURUSAN agar nama program studi bisa lebih panjang (s/d 5 kata)
    patterns_prodi = [
        # "Program Studi <Nama 1-8 kata>"
        {"label": "PRODI", "pattern": [
            {"LOWER": {"IN": ["program", "prodi"]}},
            {"LOWER": "studi", "OP": "?"},
            {"TEXT": ":", "OP": "?"},
            {"LOWER": {"IN": ["s1", "d3", "d4", "diploma", "strata"]}, "OP": "?"},
            {"IS_ALPHA": True, "LOWER": {"NOT_IN": _BATAS_JURUSAN}, "OP": "+"},
        ]},
        # Case: "S1 Teknik Informatika" (Tanpa kata 'Prodi')
        {"label": "PRODI", "pattern": [
            {"LOWER": {"IN": ["s1", "d3", "d4", "diploma", "strata"]}},
            {"IS_ALPHA": True, "LOWER": {"NOT_IN": _BATAS_JURUSAN}},
            {"IS_ALPHA": True, "LOWER": {"NOT_IN": _BATAS_JURUSAN}, "OP": "?"},
            {"IS_ALPHA": True, "LOWER": {"NOT_IN": _BATAS_JURUSAN}, "OP": "?"},
            {"IS_ALPHA": True, "LOWER": {"NOT_IN": _BATAS_JURUSAN}, "OP": "?"},
        ]},
    ]

    # ── Pola TANGGAL ────────────────────────────────────────────────────────────
    # Gunakan lowercase karena teks sudah dinormalisasi menjadi huruf kecil
    bulan_re = (
        r"^(januari|februari|maret|april|mei|juni|juli|agustus|"
        r"september|oktober|november|desember)$"
    )
    patterns_tgl = [
        # DATE_TGL  → "DD Bulan YYYY"  (lebih spesifik, diutamakan)
        {"label": "DATE_TGL", "pattern": [
            {"TEXT": {"REGEX": r"^\d{1,2}$"}},
            {"LOWER": {"REGEX": bulan_re}},
            {"TEXT": {"REGEX": r"^\d{4}$"}},
        ]},
        # DATE_TGL  → "Bulan YYYY" (tanpa hari)
        {"label": "DATE_TGL", "pattern": [
            {"LOWER": {"REGEX": bulan_re}},
            {"TEXT": {"REGEX": r"^\d{4}$"}},
        ]},
        # DATE_PARTIAL → "DD Bulan" (tanpa tahun)
        # Digunakan untuk kasus: "antara 01 Agustus s.d 31 Desember 2025"
        {"label": "DATE_PARTIAL", "pattern": [
            {"TEXT": {"REGEX": r"^\d{1,2}$"}},
            {"LOWER": {"REGEX": bulan_re}},
        ]},
    ]

    ruler.add_patterns(patterns_univ + patterns_jurusan + patterns_prodi + patterns_tgl)
    return nlp


# Inisialisasi pipeline sekali saat modul di-load
_NLP = _build_spacy_nlp()


def _extract_with_spacy(text):
    """
    Jalankan teks lewat pipeline spaCy.

    Logika tanggal:
    1. Kumpulkan semua DATE_TGL (valid tahun 2015-2035) dan DATE_PARTIAL,
       beserta flag `ada_trigger` (ada kata antara/dari/tanggal/dll dalam
       10 token sebelum entitas).
    2. Prioritaskan tanggal yang punya trigger context.
    3. Untuk DATE_PARTIAL (tanpa tahun), pinjam tahun dari DATE_TGL berikutnya.
    4. Jika tidak ada yang ber-trigger, fallback ke semua tanggal valid.
    """
    hasil = {
        "UNIV": None,
        "JURUSAN": None,
        "PRODI": None,   # Program Studi (dari 'Program Studi X' / 'Prodi X')
        "DATE_TGL": [],  # hasil akhir: [tgl_masuk, tgl_keluar, ...]
        "tokens": [],
        "entities": []
    }
    doc = _NLP(text)

    # Simpan token dan entitas untuk keperluan debug/display
    hasil["tokens"] = [token.text for token in doc]
    hasil["entities"] = [{"text": ent.text, "label": ent.label_} for ent in doc.ents]

    # Kumpulkan semua entitas tanggal dengan metadata konteksnya
    semua_tgl = []  # list of dict
    for ent in doc.ents:
        label = ent.label_
        teks  = ent.text.strip()

        if label == "UNIV" and hasil["UNIV"] is None:
            hasil["UNIV"] = teks.title()

        elif label == "JURUSAN" and hasil["JURUSAN"] is None:
            # Hanya Jurusan/Fakultas/Departemen — buang kata trigger
            nama = re.sub(
                r'^(?:Jurusan|Fakultas|Departemen)\s+', '', teks, flags=re.IGNORECASE
            ).strip().title()
            if nama:
                hasil["JURUSAN"] = nama

        elif label == "PRODI" and hasil["PRODI"] is None:
            # Program Studi — buang kata trigger, pertahankan nama lengkap
            nama = re.sub(
                r'^(?:Program\s+Studi|Prodi)\s+', '', teks, flags=re.IGNORECASE
            ).strip().title()
            # Perbaiki angka romawi (Iii→III, Iv→IV, dll)
            nama = re.sub(
                r'\b(Iii|Iiv|Ii|Iv|Vi|Vii|Viii|Ix|Xi)\b',
                lambda m: m.group().upper(), nama
            )
            if nama:
                hasil["PRODI"] = nama

        elif label == "DATE_TGL":
            # Filter tahun: hanya 2015-2035
            tahun_m = re.search(r'\b(20[12]\d|2030|2031|2032|2033|2034|2035)\b', teks)
            if not tahun_m:
                continue
            # Cek apakah ada trigger keyword dalam 10 token sebelumnya
            konteks = doc[max(0, ent.start - 10): ent.start]
            ada_trigger = any(t.lower_ in _TRIGGER_TANGGAL for t in konteks)
            semua_tgl.append({
                "teks": teks.title(),
                "has_year": True,
                "ada_trigger": ada_trigger,
                "start": ent.start,
            })

        elif label == "DATE_PARTIAL":
            # Tanggal tanpa tahun: hanya pakai jika ada trigger
            konteks = doc[max(0, ent.start - 10): ent.start]
            ada_trigger = any(t.lower_ in _TRIGGER_TANGGAL for t in konteks)
            if ada_trigger:
                semua_tgl.append({
                    "teks": teks.title(),
                    "has_year": False,
                    "ada_trigger": True,
                    "start": ent.start,
                })

    # Urutkan berdasarkan posisi kemunculan di teks
    semua_tgl.sort(key=lambda x: x["start"])

    # Prioritaskan yang ber-trigger; fallback ke semua jika tidak ada
    kandidat = [t for t in semua_tgl if t["ada_trigger"]] or semua_tgl

    # Bangun daftar tanggal final; untuk DATE_PARTIAL, pinjam tahun dari next full
    hasil_tgl = []
    seen = set()
    for i, e in enumerate(kandidat):
        if e["has_year"]:
            tgl_str = e["teks"]
        else:
            # Cari DATE_TGL berikutnya yang punya tahun
            next_full = next((x for x in kandidat[i + 1:] if x["has_year"]), None)
            if next_full:
                tahun = re.search(r'\b\d{4}\b', next_full["teks"])
                tgl_str = f"{e['teks']} {tahun.group()}" if tahun else e["teks"]
            else:
                tgl_str = e["teks"]  # tetap masukkan walau tanpa tahun

        if tgl_str not in seen:
            seen.add(tgl_str)
            hasil_tgl.append(tgl_str)

    hasil["DATE_TGL"] = hasil_tgl
    return hasil


def extract_information(raw_text, clean_text):
    """ Mencari entitas penting menggunakan spaCy (NER) + Rule-based """
    info = {
        "Universitas": "Tidak ditemukan",
        "Jurusan": "Tidak ditemukan",
        "Program Studi": "Tidak ditemukan",
        "Tanggal Masuk": "Tidak ditemukan",
        "Tanggal Keluar": "Tidak ditemukan",
        "Daftar Mahasiswa": [],
        "tokens": [],
        "entities": []
    }
    
    # Gunakan seluruh teks hasil OCR
    spacy_result = _extract_with_spacy(clean_text)

    # Masukkan token dan entitas ke dalam info
    info["tokens"] = spacy_result["tokens"]
    info["entities"] = spacy_result["entities"]

    # 1. [spaCy] Universitas
    if spacy_result["UNIV"]:
        info["Universitas"] = spacy_result["UNIV"]

    # --- KHUSUS USK (Universitas Syiah Kuala) ---
    is_usk = info["Universitas"] and "Syiah Kuala" in info["Universitas"]

    # 2. [spaCy] Jurusan
    if spacy_result["JURUSAN"]:
        info["Jurusan"] = spacy_result["JURUSAN"]

    # 2b. [spaCy] Program Studi
    if spacy_result["PRODI"]:
        # Bersihkan noise di awal (seperti titik dua atau spasi berlebih)
        prodi_raw = spacy_result["PRODI"]
        prodi_raw = re.sub(r'^[:\.\s]+', '', prodi_raw)
        
        # Bersihkan noise di akhir prodi (seperti "para", "mahasiswa", dll)
        prodi_clean = re.sub(r'\s+(?:Jurusan|Fakultas|Politeknik|Universitas|para|mahasiswa).*$', '', prodi_raw, flags=re.IGNORECASE).strip()
        info["Program Studi"] = prodi_clean
        
    if is_usk:
        # User request: Untuk USK, Jurusan dan Prodi disamakan jika terkait Teknik Mesin
        if "Teknik Mesin" in (info["Jurusan"] or "") or "Teknik Mesin" in (info["Program Studi"] or ""):
            info["Jurusan"] = "Teknik Mesin Dan Industri"
            info["Program Studi"] = "Teknik Mesin Dan Industri"
        # General USK fallback: Jika prodi tidak ada, pakai nama jurusan
        elif info["Program Studi"] == "Tidak ditemukan" and info["Jurusan"] != "Tidak ditemukan":
            info["Program Studi"] = info["Jurusan"]

    # 3. [spaCy] Tanggal Masuk & Keluar
    #    Strategi: Prioritaskan rentang eksplisit, lalu ambil tanggal setelah "Dengan Hormat".
    
    # --- LOGIKA A: Cari batas awal isi surat agar tidak mengambil tanggal surat di atas ---
    # Jika "Dengan Hormat" tidak terbaca, coba cari kata kunci pembuka lainnya
    start_patterns = [r'dengan\s+hormat', r'assalamu', r'upaya\s+mendapatkan', r'rangka\s+pelaksanaan', r'mohon\s+izin']
    idx_hormat = 0
    for pat in start_patterns:
        m_start = re.search(pat, clean_text, re.IGNORECASE)
        if m_start:
            idx_hormat = m_start.start()
            break
            
    teks_utama = clean_text[idx_hormat:]

    # --- LOGIKA B: Deteksi Rentang Tanggal Eksplisit (Prioritas Utama) ---
    found_range = False
    range_kw = r'(?:s\.d|s/d|sampai dengan|sampai|s\.d\.|ù|—|-|~)'
    
    # 1. Full Range (04 Agustus 2025 s.d. 02 Januari 2026)
    full_pattern = re.search(fr'(\d{{1,2}}\s+[A-Za-z]+\s+\d{{4}})\s*{range_kw}\s*(\d{{1,2}}\s+[A-Za-z]+\s+\d{{4}})', teks_utama, re.IGNORECASE)
    if full_pattern:
        info["Tanggal Masuk"] = full_pattern.group(1).title()
        info["Tanggal Keluar"] = full_pattern.group(2).title()
        found_range = True
    
    # 2. DD Month s.d DD Month Year (01 Agustus s.d 30 November 2025)
    if not found_range:
        mid_pattern = re.search(fr'(\d{{1,2}})\s+([A-Za-z]+)\s*{range_kw}\s*(\d{{1,2}})\s+([A-Za-z]+)\s+(\d{{4}})', teks_utama, re.IGNORECASE)
        if mid_pattern:
            d1, m1, d2, m2, y = mid_pattern.groups()
            info["Tanggal Masuk"] = f"{d1} {m1.title()} {y}"
            info["Tanggal Keluar"] = f"{d2} {m2.title()} {y}"
            found_range = True

    # 3. Single Month Range (01 s.d 30 Agustus 2025)
    if not found_range:
        day_pattern = re.search(fr'(\d{{1,2}})\s*{range_kw}\s*(\d{{1,2}})\s+([A-Za-z]+)\s+(\d{{4}})', teks_utama, re.IGNORECASE)
        if day_pattern:
            d1, d2, m, y = day_pattern.groups()
            info["Tanggal Masuk"] = f"{d1} {m.title()} {y}"
            info["Tanggal Keluar"] = f"{d2} {m.title()} {y}"
            found_range = True

    # 4. Month Range (Agustus s.d Desember 2025)
    if not found_range:
        month_pattern = re.search(fr'([A-Za-z]+)\s*{range_kw}\s*([A-Za-z]+)\s+(\d{{4}})', teks_utama, re.IGNORECASE)
        if month_pattern:
            m1, m2, y = month_pattern.groups()
            info["Tanggal Masuk"] = f"{m1.title()} {y}"
            info["Tanggal Keluar"] = f"{m2.title()} {y}"
            found_range = True

    # --- LOGIKA C: Fallback ke spaCy jika rentang tidak ditemukan ---
    if not found_range:
        tanggal_unik = []
        seen_tgl = set()
        # Ambil tanggal dari spaCy, prioritaskan yang ada setelah batas pembuka
        for t in spacy_result["DATE_TGL"]:
            if t in teks_utama and t not in seen_tgl:
                seen_tgl.add(t)
                tanggal_unik.append(t)
        
        if len(tanggal_unik) >= 2:
            info["Tanggal Masuk"]  = tanggal_unik[0]
            info["Tanggal Keluar"] = tanggal_unik[1]
        elif len(tanggal_unik) == 1:
            # Kasus khusus: "Agustus 2025" -> 1 Agustus s.d 31 Agustus
            t = tanggal_unik[0]
            if re.match(r'^[A-Za-z]+\s+\d{4}$', t):
                month_name, year = t.split()
                info["Tanggal Masuk"] = f"01 {month_name} {year}"
                info["Tanggal Keluar"] = t # Akan dinormalisasi di Logika D
            else:
                info["Tanggal Masuk"] = t

    # --- LOGIKA D: Normalisasi Akhir Bulan untuk Tanggal Keluar ---
    # Jika Tanggal Keluar hanya "Bulan Tahun", ubah ke tanggal terakhir bulan tersebut
    def normalize_to_month_end(date_str):
        import calendar
        months_id = {
            "Januari": 1, "Februari": 2, "Maret": 3, "April": 4, "Mei": 5, "Juni": 6,
            "Juli": 7, "Agustus": 8, "September": 9, "Oktober": 10, "November": 11, "Desember": 12
        }
        # Regex untuk pola: "September 2025" atau "Agustus 2025"
        m = re.match(r'^([A-Za-z]+)\s+(\d{4})$', date_str)
        if m:
            m_name, y_val = m.groups()
            m_idx = months_id.get(m_name.title())
            if m_idx:
                last_day = calendar.monthrange(int(y_val), m_idx)[1]
                return f"{last_day} {m_name.title()} {y_val}"
        return date_str

    if info["Tanggal Keluar"] and info["Tanggal Keluar"] != "Tidak ditemukan":
        info["Tanggal Keluar"] = normalize_to_month_end(info["Tanggal Keluar"])
    if info["Tanggal Masuk"] and info["Tanggal Masuk"] != "Tidak ditemukan":
        info["Tanggal Masuk"] = normalize_to_month_end(info["Tanggal Masuk"])

    # 4. Anggota Tim/Mahasiswa
    # Prodi untuk semua mahasiswa diambil dari spaCy (info["Program Studi"])
    prodi_global = info["Program Studi"]

    # --- LOGIKA BARU EKSTRAKSI MAHASISWA (LEBIH ROBUST) ---
    mahasiswa_list = []
    seen_nim = set()
    
    lines = raw_text.split('\n')
    # NIM biasanya 9-20 digit. (Filter NIP/Nomor HP/Noise)
    nim_regex = re.compile(r'\b(?!08|62)(\d{9,20})\b')
    
    bad_keywords = {
        "nip", "nim", "ketua", "dosen", "pembina", "koordinator", "manajer", "hrd",
        "jurusan", "fakultas", "halaman", "kementrian", "pendidikan", "universitas",
        "politeknik", "lhokseumawe", "aceh", "telp", "email", "laman", "nomor", "lamp",
        "pimpinan", "yth", "kepada", "dengan", "hormat", "maka", "kami", "mohon", "perihal", "tembusan",
        "tempat", "lahir", "tanggal", "tgl", "alamat", "semester", "jenis", "kelamin", "agama", "status", "telepon",
        "npm", "departemen", "studi", "prodi"
    }

    sampah_prodi = [
        "teknologi", "rekayasa", "industri", "mesin", "elektro", "akuntansi", 
        "bisnis", "informatika", "komputer", "diploma", "sarjana", "terapan",
        "pembangkit", "energi", "instrumentasi", "kontrol", "manufaktur",
        "manajemen", "perbankan", "syariah"
    ]

    def clean_name_string(s, nim_to_remove=None):
        if not s: return ""
        s_upper = s.upper()
        # Buang baris yang mengandung informasi kontak/identitas lain
        if any(w in s_upper for w in [
            "NIP", "HP:", "TELP", "WA:", "PHONE", "EMAIL", "WWW.", "HTTP",
            "M.T", "S.T", "S.S.T", "M.KOM", "S.KOM", "M.SI", "S.SI", "PH.D", "DR.", "PROF."
        ]): 
            return ""
        
        if nim_to_remove:
            s = s.replace(nim_to_remove, "")
        
        # 1. Bersihkan noise OCR dan karakter non-alfabet di awal/akhir
        s = re.sub(r'^[ \t\d\.\-\|\_\#\(\)\[\]\+\*\,\?]+', '', s)
        s = re.sub(r'[ \t\d\.\-\|\_\#\(\)\[\]\+\*\,\?]+$', '', s)
        
        # 2. Hapus sisa-sisa simbol tabel yang mungkin nempel di tengah (tapi jangan l atau I)
        s = re.sub(r'[\|\_:\-]{2,}', ' ', s)
        
        # 3. Pisahkan berdasarkan gap kolom (2+ spasi)
        chunks = re.split(r'\s{2,}', s)
        
        for chunk in chunks:
            chunk = chunk.strip()
            # Hapus label "Nama:" atau "Nama mahasiswa:" jika ada di awal chunk
            chunk = re.sub(r'^(?:Nama|Mahasiswa|Mhs)(?:\s+Mahasiswa)?\s*[:\-\.]\s*', '', chunk, flags=re.IGNORECASE).strip()
            
            # Bersihkan angka dari chunk (kecuali angka romawi atau bagian dari nama yang valid)
            chunk_clean = re.sub(r'\d+', '', chunk).strip()
            
            if not chunk_clean or len(chunk_clean) < 3:
                continue
            
            # Cek jika chunk hanyalah label tabel (Nama, NIM, Prodi, dll)
            if chunk_clean.lower() in ["nama", "nim", "prodi", "program studi", "studi", "no", "nomor"]:
                continue
                
            words = chunk_clean.split()
            if len(words) > 7: continue # Nama terlalu panjang biasanya baris kalimat
            
            # Cek jika mengandung kata terlarang
            if any(w.lower() in bad_keywords for w in words):
                continue
                
            # Hapus prodi_global jika ada di dalam chunk
            if prodi_global and prodi_global != "Tidak ditemukan":
                chunk_clean = re.sub(re.escape(prodi_global), '', chunk_clean, flags=re.IGNORECASE).strip()
            
            # Hapus keyword prodi umum
            for word in sampah_prodi:
                chunk_clean = re.sub(r'\s+' + word + r'.*$', '', chunk_clean, flags=re.IGNORECASE).strip()
            
            if len(chunk_clean) >= 3:
                # Pembersihan akhir: pastikan tidak ada simbol tertinggal di ujung nama
                chunk_clean = re.sub(r'^[^a-zA-Z]+', '', chunk_clean) # Hapus non-huruf di awal
                chunk_clean = re.sub(r'[^a-zA-Z]+$', '', chunk_clean) # Hapus non-huruf di akhir (seperti | _ -)
                return chunk_clean.strip()
        
        return ""

    for i, line in enumerate(lines):
        # Stop jika sudah sampai bagian Tembusan (biasanya akhir dokumen)
        if "tembusan" in line.lower():
            break
            
        nim_match = nim_regex.search(line)
        if nim_match:
            nim = nim_match.group(1)
            if nim in seen_nim: continue
            
            # Cari nama di sekitar NIM (Prioritaskan baris yang mengandung kata "Nama")
            potential = ""
            candidates = []
            for offset in [0, -1, 1, -2, 2]:
                target_idx = i + offset
                if 0 <= target_idx < len(lines):
                    line_text = lines[target_idx]
                    p = clean_name_string(line_text, nim if offset == 0 else None)
                    if p:
                        # Skor prioritas: sangat tinggi jika ada kata "Nama"
                        priority = 0
                        if re.search(r'\bnama\b', line_text, re.IGNORECASE): 
                            priority = 2
                            # Penalti keras jika baris header (Nama + NPM/Departemen/NIM)
                            if re.search(r'\b(?:npm|departemen|nim|no\.)\b', line_text, re.IGNORECASE):
                                priority = -5 # Discard
                        elif re.search(r'\bmahasiswa\b', line_text, re.IGNORECASE): 
                            priority = 1
                        
                        if priority >= 0:
                            candidates.append((p, priority, abs(offset)))
            
            if candidates:
                # Urutkan berdasarkan priority (desc) lalu jarak offset (asc)
                candidates.sort(key=lambda x: (-x[1], x[2]))
                potential = candidates[0][0]
            
            if potential:
                nama_fix = potential.title()
                mahasiswa_list.append({
                    "Nama": nama_fix,
                    "NIM": nim,
                    "Prodi": prodi_global
                })
                # Tambahkan ke debug entities agar muncul di IDENTIFY ENTITY
                info["entities"].append({"text": nama_fix, "label": "NAMA"})
                info["entities"].append({"text": nim, "label": "NIM"})
                
                seen_nim.add(nim)

    info["Daftar Mahasiswa"] = mahasiswa_list
    return info

def main():
    import os
    
    # Membaca seluruh file PDF di dalam folder 'pdf'
    folder_path = "pdf"
    
    if not os.path.exists(folder_path):
        print(f"Folder '{folder_path}' tidak ditemukan! Buat dulu foldernya dan masukkan file PDF ke sana.")
        return
        
    daftar_file = [f for f in os.listdir(folder_path) if f.lower().endswith('.pdf') or f.lower().endswith('.png') or f.lower().endswith('.jpg')]
    
    if not daftar_file:
        print(f"Tidak ada file PDF atau Gambar di dalam folder '{folder_path}'")
        return
        
    print(f"Ditemukan {len(daftar_file)} dokumen dalam folder '{folder_path}'. Memulai proses otomatis...\n")
    
    for nama_file in daftar_file:
        file_path = os.path.join(folder_path, nama_file)
        
        print("\n" + "="*60)
        print(f"MEMPROSES DOKUMEN: {nama_file}")
        print("="*60)
        
        # 2. Proses OCR / Membaca Scanner
        print("[1] Membaca teks dari dokumen...")
        try:
            hasil_ocr = get_text_from_file(file_path)
            # Menampilkan Teks Asli (Mentah) agar user bisa melakukan inspeksi (debug)
            print("\n--- Teks Asli (Mentah) ---")
            print(hasil_ocr) 
        except Exception as e:
            print(f"❌ Error saat membaca dokumen {nama_file}: {e}")
            continue
        
        # 3. Proses Cleaning
        print("\n\n[2] Membersihkan Teks (Preprocessing)...")
        teks_bersih = cleaning_text(hasil_ocr)
        print("\n--- Teks Bersih ---")
        print(teks_bersih)
        
        # 3b. Proses Normalisasi
        print("\n--- Teks Normalisasi ---")
        teks_normal = normalize_text(teks_bersih)
        print(teks_normal)
        
        # 4. Analisis Terfokus (Ekstraksi Aturan / Regex)
        print("\n\n[3] Mengekstrak Informasi Detail...")
        info_penting = extract_information(raw_text=hasil_ocr, clean_text=teks_normal)
        
        # --- OUTPUT TOKEN ---
        print("\n--- OUTPUT TOKEN ---")
        if info_penting["tokens"]:
            print(" | ".join(info_penting["tokens"]))
        else:
            print("Tidak ada token yang terdeteksi.")

        # --- IDENTIFY ENTITY ---
        print("\n--- IDENTIFY ENTITY ---")
        if info_penting["entities"]:
            for ent in info_penting["entities"]:
                print(f"[{ent['label']}] {ent['text']}")
        else:
            print("Tidak ada entitas yang terdeteksi.")

        # --- OUTPUT NORMAL ---
        print("\n--- OUTPUT NORMAL ---")
        print(f"Universitas     : {info_penting['Universitas']}")
        print(f"Jurusan         : {info_penting['Jurusan']}")
        print(f"Program Studi   : {info_penting['Program Studi']}")
        print(f"Tanggal Masuk   : {info_penting['Tanggal Masuk']}")
        print(f"Tanggal Keluar  : {info_penting['Tanggal Keluar']}")

        print("\nDAFTAR MAHASISWA:")
        
        if len(info_penting["Daftar Mahasiswa"]) == 0:
            print("  - Tidak ada data tabel mahasiswa yang terbaca dengan sempurna.")
        else:
            for idx, mhs in enumerate(info_penting["Daftar Mahasiswa"], 1):
                print(f"  {idx}. Nama  : {mhs['Nama']}")
                print(f"     NIM   : {mhs['NIM']}")
        print("="*60 + "\n")

if __name__ == "__main__":
    main()
