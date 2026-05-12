import os
import re
import spacy
import pytesseract
from PIL import Image
from spacy.lang.id import Indonesian

# 1. KONFIGURASI PATH (WAJIB DI WINDOWS)
pytesseract.pytesseract.tesseract_cmd = r'C:\Program Files\Tesseract-OCR\tesseract.exe'
POPPLER_PATH = r'C:\Program Files\poppler-24.08.0\Library\bin'

# 2. SETUP SPACY PIPELINE (Arsitektur Pipeline NER)
def _build_skill_nlp():
    # Menggunakan tokenizer Indonesia agar pemotongan kata lebih akurat
    nlp = Indonesian() 
    
    # Menambahkan EntityRuler untuk mendeteksi keahlian secara spesifik
    if "entity_ruler" not in nlp.pipe_names:
        ruler = nlp.add_pipe("entity_ruler")
    else:
        ruler = nlp.get_pipe("entity_ruler")

    # Daftar pola untuk Identify Entity (Skill)
    patterns = [
        {"label": "SKILL", "pattern": [{"LOWER": "excel"}]},
        {"label": "SKILL", "pattern": [{"LOWER": "ms"}, {"LOWER": "excel"}]},
        {"label": "SKILL", "pattern": [{"LOWER": "microsoft"}, {"LOWER": "excel"}]},
        {"label": "SKILL", "pattern": [{"LOWER": "python"}]},
        {"label": "SKILL", "pattern": [{"LOWER": "word"}]},
        {"label": "SKILL", "pattern": [{"LOWER": "ms"}, {"LOWER": "word"}]},
        {"label": "SKILL", "pattern": [{"LOWER": "powerpoint"}]},
        {"label": "SKILL", "pattern": [{"LOWER": "accounting"}]},
        {"label": "SKILL", "pattern": [{"LOWER": "audit"}]},
        {"label": "SKILL", "pattern": [{"LOWER": "perpajakan"}]},
        {"label": "SKILL", "pattern": [{"LOWER": "canva"}]},
        {"label": "SKILL", "pattern": [{"LOWER": "photoshop"}]},
        {"label": "SKILL", "pattern": [{"LOWER": "adobe"}, {"LOWER": "photoshop"}]},
        {"label": "SKILL", "pattern": [{"LOWER": "marketing"}, {"LOWER": "strategy"}]},
        {"label": "SKILL", "pattern": [{"LOWER": "digital"}, {"LOWER": "marketing"}]},
        {"label": "SKILL", "pattern": [{"LOWER": "spss"}]},
        {"label": "SKILL", "pattern": [{"LOWER": "manajemen"}, {"LOWER": "sdm"}]},
        {"label": "SKILL", "pattern": [{"LOWER": "java"}]},
    ]
    ruler.add_patterns(patterns)
    return nlp

# Inisialisasi NLP sekali saja
_NLP = _build_skill_nlp()

# Dictionary untuk Normalisasi Nama Skill (Final Output)
SKILL_MAP = {
    "excel": "Microsoft Excel",
    "ms excel": "Microsoft Excel",
    "microsoft excel": "Microsoft Excel",
    "word": "Microsoft Word",
    "ms word": "Microsoft Word",
    "python": "Python Programming",
    "canva": "Canva Design",
    "photoshop": "Adobe Photoshop",
    "adobe photoshop": "Adobe Photoshop",
    "spss": "SPSS Statistics",
    "marketing strategy": "Digital Marketing",
    "digital marketing": "Digital Marketing",
    "manajemen sdm": "HR Management",
}

# 3. FUNGSI TAHAPAN PROSES
def get_text_from_file(file_path):
    """ TAHAP 1: HASIL NORMAL OCR (MENTAH) """
    ext = file_path.lower().split('.')[-1]
    if ext in ['png', 'jpg', 'jpeg']:
        return pytesseract.image_to_string(Image.open(file_path))
    elif ext == 'pdf':
        from pdf2image import convert_from_path
        images = convert_from_path(file_path, poppler_path=POPPLER_PATH)
        full_text = ""
        for i, img in enumerate(images):
            teks_hal = pytesseract.image_to_string(img)
            full_text += f"\n{teks_hal}"
        return full_text
    return ""

def cleaning_text(text):
    """ TAHAP 2: HASIL PREPROCESSING (CLEANING) """
    # Hapus noise simbol OCR (____, ====, ||||)
    text = re.sub(r'[_=|]{2,}', ' ', text)
    # Mengganti baris baru (\n) yang berlebihan menjadi spasi
    text = re.sub(r'\n+', ' ', text)
    # Menghapus spasi ganda
    text = re.sub(r'\s+', ' ', text).strip()
    return text

def normalize_text(text):
    """ TAHAP 3: HASIL NORMALISASI (TEXT NORMALIZATION) """
    text = text.lower()
    # Standarisasi singkatan umum
    text = re.sub(r'\btgl\b', 'tanggal', text)
    text = re.sub(r'\bnim\b', 'nomor induk mahasiswa', text)
    text = re.sub(r'\bprodi\b', 'program studi', text)
    # Hapus karakter non-alfanumerik kecuali spasi dan titik/koma
    text = re.sub(r'[^a-z0-9\s\.\,]', ' ', text)
    return re.sub(r'\s+', ' ', text).strip()

def extract_details(raw_text, norm_text):
    """ TAHAP 4 & 5: TOKEN & IDENTIFY ENTITY (DENGAN LOGIKA LAMA YANG LEBIH KUAT) """
    doc = _NLP(norm_text)
    
    tokens = [t.text for t in doc]
    entities = [{"text": ent.text, "label": ent.label_} for ent in doc.ents]
    
    # A. Ambil skill dari hasil SpaCy (Exact Keywords)
    found_skills = []
    for ent in doc.ents:
        name = ent.text.lower()
        norm_name = SKILL_MAP.get(name, name.title())
        if norm_name not in found_skills:
            found_skills.append(norm_name)
            
    # B. LOGIKA LAMA: Cari di bagian Pengalaman / Aktivitas (Fallback)
    # Kita gunakan raw_text agar format label tetap terbaca
    pola_pengalaman = r'Pengalaman\s*/\s*Aktivitas\s*:\s*(.*?)(?:\n\n|\Z|---)'
    match_pengalaman = re.search(pola_pengalaman, raw_text, re.IGNORECASE | re.DOTALL)
    if match_pengalaman:
        lines = match_pengalaman.group(1).split('\n')
        for line in lines:
            if ',' in line:
                skill_part = line.split(',')[-1].strip().strip('.- ')
                if 5 < len(skill_part) < 60:
                    skill_title = skill_part.title()
                    if skill_title not in found_skills:
                        found_skills.append(skill_title)
                        entities.append({"text": skill_title, "label": "SKILL_AUTO"})

    # C. LOGIKA LAMA: Cari dari Konsentrasi Akademik (Fallback)
    if not found_skills:
        pola_bidang = r'(?:bidang\s+ilmu|konsentrasi)\s+.*?(?:yaitu|adalah|:|meliputi)\s*([A-Za-z0-9\s,&\/\(\)\-]{10,200})'
        match_bidang = re.search(pola_bidang, raw_text, re.IGNORECASE)
        if match_bidang:
            teks = match_bidang.group(1).strip()
            teks = re.split(r'\.|\sUntuk\s|\sDemikian\s', teks)[0].strip()
            items = re.split(r',|dan', teks)
            for item in items:
                clean_item = item.strip().title()
                if len(clean_item) > 3 and clean_item not in found_skills:
                    found_skills.append(clean_item)
                    entities.append({"text": clean_item, "label": "SKILL_AUTO"})
            
    return {
        "norm_text": norm_text,
        "tokens": tokens,
        "entities": entities,
        "final_skills": found_skills
    }

# 4. MAIN PROGRAM
def main():
    folder_path = "pdf"
    if not os.path.exists(folder_path):
        os.makedirs(folder_path)
        print(f"Folder '{folder_path}' dibuat. Masukkan file Anda di sana.")
        return

    daftar_file = [f for f in os.listdir(folder_path) if f.lower().endswith(('.pdf', '.png', '.jpg', '.jpeg'))]
    
    if not daftar_file:
        print(f"Tidak ada file di folder '{folder_path}'.")
        return
        
    print(f"Ditemukan {len(daftar_file)} dokumen. Memulai pipeline ekstraksi...\n")
    
    for nama_file in daftar_file:
        file_path = os.path.join(folder_path, nama_file)
        print("\n" + "="*80)
        print(f"PROSES DOKUMEN: {nama_file}")
        print("="*80)

        try:
            # 1. OCR (Mentah)
            raw_text = get_text_from_file(file_path)
            print("\n--- [1] HASIL NORMAL OCR (MENTAH) ---")
            print(raw_text if raw_text else "(Gagal membaca teks)")

            # 2. Cleaning
            clean_text = cleaning_text(raw_text)
            print("\n--- [2] HASIL PREPROCESSING (CLEANING) ---")
            print(clean_text)

            # 3. Normalisasi Teks
            norm_text = normalize_text(clean_text)
            print("\n--- [3] HASIL NORMALISASI ---")
            print(norm_text)

            # 4, 5, 6. NLP Processing
            result = extract_details(raw_text, norm_text)

            print("\n--- [4] OUTPUT TOKEN ---")
            print(" | ".join(result["tokens"]))

            print("\n--- [5] IDENTIFY ENTITY ---")
            if result["entities"]:
                for ent in result["entities"]:
                    print(f"[{ent['label']}] {ent['text']}")
            else:
                print("Tidak ada entitas ditemukan.")

            print("\n--- [6] HASIL KEAHLIAN (FINAL) ---")
            print(", ".join(result["final_skills"]) if result["final_skills"] else "-")
            print("\n" + "="*80)

        except Exception as e:
            print(f"❌ Error pada file {nama_file}: {e}")
            continue

if __name__ == "__main__":
    main()
