import re

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
    
    # 3. Hapus karakter non-alfanumerik kecuali spasi dan simbol dasar
    text = re.sub(r'[^a-z0-9\s\.\/\-\(\)]', ' ', text)
    
    # 4. Hapus spasi ganda
    text = re.sub(r'\s+', ' ', text).strip()
    return text

test_text = "NIM: 12345, TGL: 01 Jan 2025, Prodi: Teknik, No: 1. Univ: PNL."
print(f"Original: {test_text}")
print(f"Normalized: {normalize_text(test_text)}")
