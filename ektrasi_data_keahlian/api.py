from flask import Flask, request, jsonify
from flask_cors import CORS
import os
import uuid
import shutil
from main import get_text_from_file, extract_details, cleaning_text, normalize_text

app = Flask(__name__)
CORS(app) # Mengizinkan akses dari domain lain (seperti Laravel)

# Setup folder temporary untuk processing
UPLOAD_DIR = "temp_uploads"
os.makedirs(UPLOAD_DIR, exist_ok=True)

@app.route("/", methods=["GET"])
def read_root():
    return jsonify({"message": "Welcome to the Skill Extractor API (Flask). Use /extract-skills/ endpoint to process files or text."})

@app.route("/extract-skills/", methods=["POST"])
def extract_skills_endpoint():
    # 1. Cek apakah ada teks langsung (optimasi agar tidak OCR ulang)
    direct_text = request.form.get('text') or request.json.get('text') if request.is_json else None
    
    if direct_text:
        print("⚡ Menggunakan teks langsung (Tanpa OCR ulang)")
        # Pipeline: Clean -> Normalize -> Extract
        cleaned = cleaning_text(direct_text)
        norm = normalize_text(cleaned)
        details = extract_details(direct_text, norm)
        
        return jsonify({
            "keahlian": ", ".join(details['final_skills']),
            "tokens": details['tokens'],
            "entities": details['entities'],
            "normalized": details['final_skills'],
            "clean_text": details['norm_text'],
            "status": "success",
            "method": "direct_text"
        })

    # 2. Jika tidak ada teks, proses via File (OCR)
    if 'file' not in request.files:
        return jsonify({"error": "No file part or text field provided", "status": "error"}), 400
    
    file = request.files['file']
    
    if file.filename == '':
        return jsonify({"error": "No selected file", "status": "error"}), 400

    # Validasi ekstensi file
    ext = file.filename.lower().split('.')[-1]
    if ext not in ['pdf', 'png', 'jpg', 'jpeg']:
        return jsonify({"error": f"File extension .{ext} is not supported. Use PDF, PNG, or JPG.", "status": "error"}), 400

    # Generate nama file unik agar tidak bentrok
    file_id = str(uuid.uuid4())
    temp_file_path = os.path.join(UPLOAD_DIR, f"{file_id}_{file.filename}")

    try:
        # Simpan file sementara
        file.save(temp_file_path)

        # Proses file menggunakan logika pipeline dari main.py
        print(f"🔍 Memproses OCR untuk file: {file.filename}")
        raw_text = get_text_from_file(temp_file_path)
        cleaned = cleaning_text(raw_text)
        norm = normalize_text(cleaned)
        details = extract_details(raw_text, norm)

        return jsonify({
            "filename": file.filename,
            "keahlian": ", ".join(details['final_skills']),
            "tokens": details['tokens'],
            "entities": details['entities'],
            "normalized": details['final_skills'],
            "raw_text": raw_text,
            "clean_text": details['norm_text'],
            "status": "success",
            "method": "ocr_file"
        })

    except Exception as e:
        return jsonify({
            "filename": file.filename,
            "error": str(e),
            "status": "error"
        }), 500
    finally:
        # Cleanup: Hapus file sementara setelah diproses
        if os.path.exists(temp_file_path):
            os.remove(temp_file_path)

if __name__ == "__main__":
    import sys
    
    # Gunakan port 5005 secara default (agar tidak tabrakan dengan Laravel 8000)
    port = 5005
    if len(sys.argv) > 1:
        try:
            port = int(sys.argv[1])
        except ValueError:
            pass
            
    print(f"Starting Flask API on port: {port}")
    app.run(host="0.0.0.0", port=port, debug=True)
