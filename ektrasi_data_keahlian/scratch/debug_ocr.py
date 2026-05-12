from PIL import Image
import pytesseract
import os
from pdf2image import convert_from_path

# Tentukan path Tesseract
pytesseract.pytesseract.tesseract_cmd = r'C:\Program Files\Tesseract-OCR\tesseract.exe'
# Tentukan path Poppler
POPPLER_PATH = r'C:\Program Files\poppler-24.08.0\Library\bin'

def get_raw_text(file_path):
    images = convert_from_path(file_path, poppler_path=POPPLER_PATH)
    full_text = ""
    for i, img in enumerate(images):
        teks_hal = pytesseract.image_to_string(img)
        full_text += f"\n--- HALAMAN {i+1} ---\n{teks_hal}"
    return full_text

file_path = r"c:\jokiproyek\ektrasi data keahlian\pdf\Permohonan Praktik Kerja Lapangan an Fikri Hoeru Rozikin.pdf"
text = get_raw_text(file_path)
with open("scratch/ocr_output.txt", "w", encoding="utf-8") as f:
    f.write(text)
print("Done saving to scratch/ocr_output.txt")
