from flask import Flask, request, jsonify
from flask_cors import CORS
import os
import tempfile
import traceback

# Import fungsi-fungsi dari main.py (Pastikan main.py ada di folder yang sama)
try:
    from main import get_text_from_file, cleaning_text, extract_information, normalize_text
except ImportError:
    print("❌ ERROR: File main.py tidak ditemukan atau fungsi gagal diimport!")

app = Flask(__name__)
CORS(app)  # Izinkan request dari Laravel

ALLOWED_EXTENSIONS = {'pdf', 'png', 'jpg', 'jpeg'}

def allowed_file(filename):
    return '.' in filename and filename.rsplit('.', 1)[1].lower() in ALLOWED_EXTENSIONS

@app.route('/extract', methods=['POST'])
def extract():
    """
    Endpoint utama: menerima upload file dari Laravel,
    kembali hasil ekstraksi, raw text, dan cleaned text.
    """
    if 'file' not in request.files:
        return jsonify({"success": False, "message": "Tidak ada file yang dikirim."}), 400

    file = request.files['file']

    if file.filename == '':
        return jsonify({"success": False, "message": "Nama file kosong."}), 400

    if not allowed_file(file.filename):
        return jsonify({"success": False, "message": "Format file tidak didukung."}), 415

    # ── Simpan file sementara ─────────────────────────────────────────────────
    ext = file.filename.rsplit('.', 1)[1].lower()
    tmp_file = tempfile.NamedTemporaryFile(delete=False, suffix=f'.{ext}')
    
    try:
        file.save(tmp_file.name)
        tmp_file.close()

        print(f"\n📂 Sedang memproses file: {file.filename}")

        # ── 1. Proses OCR (Raw Text) ─────────────────────────────────────────
        hasil_ocr = get_text_from_file(tmp_file.name)
        print(f"📝 Teks Terbaca (Raw): {len(hasil_ocr)} karakter")

        # ── 2. Cleaning Text (Pre-processed) ──────────────────────────────────
        teks_bersih = cleaning_text(hasil_ocr)
        print(f"✨ Teks Setelah Cleaning: {len(teks_bersih)} karakter")

        # ── 3. Normalization Text ─────────────────────────────────────────────
        teks_normal = normalize_text(teks_bersih)
        print(f"🧩 Teks Setelah Normalisasi: {len(teks_normal)} karakter")

        # ── 4. Ekstraksi Informasi ───────────────────────────────────────────
        info = extract_information(raw_text=hasil_ocr, clean_text=teks_normal)
        print(f"✅ Ekstraksi selesai untuk: {info.get('Universitas', 'Tidak ditemukan')}")

        # ── 4. Susun response JSON untuk Laravel ──────────────────────────────
        return jsonify({
            "success": True,
            "message": "Ekstraksi berhasil.",
            "raw_text": hasil_ocr,      # Mengirim teks asli
            "clean_text": teks_normal,  # Mengirim teks yang sudah dinormalisasi (agar tampil rapi di web)
            "data": {
                "universitas"      : info.get("Universitas", "Tidak ditemukan"),
                "jurusan"          : info.get("Jurusan", "Tidak ditemukan"),
                "program_studi"    : info.get("Program Studi", "Tidak ditemukan"),
                "tanggal_masuk"    : info.get("Tanggal Masuk", "Tidak ditemukan"),
                "tanggal_keluar"   : info.get("Tanggal Keluar", "Tidak ditemukan"),
                "daftar_mahasiswa" : info.get("Daftar Mahasiswa", []),
            }
        }), 200

    except Exception as e:
        print(f"❌ ERROR: {str(e)}")
        return jsonify({
            "success": False,
            "message": "Terjadi error saat memproses dokumen.",
            "error"  : str(e),
            "trace"  : traceback.format_exc()
        }), 500

    finally:
        if os.path.exists(tmp_file.name):
            os.remove(tmp_file.name)

@app.route('/health', methods=['GET'])
def health():
    return jsonify({"success": True, "message": "Python OCR API aktif."}), 200

if __name__ == '__main__':
    print("=" * 50)
    print("  Python OCR Extraction API")
    print("  Running on http://127.0.0.1:5000")
    print("  Endpoint: POST /extract")
    print("=" * 50)
    app.run(host='0.0.0.0', port=5000, debug=True)
