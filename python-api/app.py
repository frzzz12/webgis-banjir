"""
app.py - Flask API Server untuk Prediksi Potensi Banjir
WebGIS Banjir Kecamatan Kendari Barat - Tesis Alpin (G2S123004)

Endpoint:
  GET  /health     - cek status server & akurasi model
  POST /prediksi   - prediksi potensi banjir berdasarkan parameter input
"""

import os
import pickle
import json
import numpy as np
from flask import Flask, request, jsonify
from flask_cors import CORS

# ── Konfigurasi ─────────────────────────────────────────────────
BASE_DIR  = os.path.dirname(os.path.abspath(__file__))
MODEL_DIR = os.path.join(BASE_DIR, 'models')

app = Flask(__name__)
CORS(app, origins="*")   # izinkan request dari Laravel (semua origin)

# ── Load artefak model saat startup ─────────────────────────────
print("[*] Loading model...")

try:
    with open(os.path.join(MODEL_DIR, "model_rf_banjir.pkl"),  "rb") as f:
        model_rf = pickle.load(f)
    with open(os.path.join(MODEL_DIR, "model_svm_banjir.pkl"), "rb") as f:
        model_svm = pickle.load(f)
    with open(os.path.join(MODEL_DIR, "scaler_banjir.pkl"),    "rb") as f:
        scaler = pickle.load(f)
    with open(os.path.join(MODEL_DIR, "model_metadata.json"))  as f:
        meta = json.load(f)

    FEATURES  = meta["features"]
    INV_LABEL = {int(k): v for k, v in meta["inv_label"].items()}

    WARNA_MAP = {
        "sangat rendah": "#27ae60",
        "rendah"       : "#f1c40f",
        "sedang"       : "#e67e22",
        "tinggi"       : "#e74c3c",
    }
    ICON_MAP = {
        "sangat rendah": "fa-shield-halved",
        "rendah"       : "fa-triangle-exclamation",
        "sedang"       : "fa-circle-exclamation",
        "tinggi"       : "fa-circle-radiation",
    }

    print(f"[OK] Model loaded - Best: {meta['best_model']} | Akurasi RF: {meta['accuracy_rf']*100:.2f}%")

except FileNotFoundError as e:
    print(f"[ERROR] Model file tidak ditemukan: {e}")
    print(f"        Pastikan file .pkl ada di folder: {MODEL_DIR}")
    model_rf = model_svm = scaler = meta = None


# ── Health Check ─────────────────────────────────────────────────
@app.route("/health", methods=["GET"])
def health():
    if meta is None:
        return jsonify({"status": "error", "message": "Model belum dimuat"}), 500
    return jsonify({
        "status"      : "ok",
        "best_model"  : meta["best_model"],
        "accuracy_rf" : round(meta["accuracy_rf"] * 100, 2),
        "accuracy_svm": round(meta["accuracy_svm"] * 100, 2),
        "f1_rf"       : round(meta["f1_rf"] * 100, 2),
        "f1_svm"      : round(meta["f1_svm"] * 100, 2),
        "features"    : FEATURES,
    })


# ── Prediksi ─────────────────────────────────────────────────────
@app.route("/prediksi", methods=["POST"])
def prediksi():
    """
    Body JSON yang diterima dari Laravel:
    {
        "slope"               : 15.5,       -- kemiringan lereng (derajat)
        "curah_hujan"         : 1991,       -- mm/tahun
        "tanah_skor"          : 2,          -- 1=Dystrudepts, 2=Hapludults, 3=Endoaquepts
        "lahan_skor"          : 5,          -- 1=Hutan ... 5=Kawasan Terbangun
        "kemiringan_grid_code": 2,          -- 1=Datar ... 5=Sangat Curam
        "jarak_drainase_m"    : 350.0,      -- jarak ke drainase terdekat (meter)
        "flood_hist_biner"    : 1,          -- 0=tidak pernah, 1=pernah banjir
        "longitude"           : 122.523,
        "latitude"            : -3.937,
        "model"               : "rf"        -- "rf" atau "svm" (opsional, default: rf)
    }
    """
    if model_rf is None:
        return jsonify({"status": "error", "message": "Model belum dimuat di server"}), 500

    try:
        data = request.get_json(force=True)

        # Pilih model
        use_model = model_svm if data.get("model", "rf") == "svm" else model_rf
        model_name = "SVM" if data.get("model", "rf") == "svm" else "Random Forest"

        # Ambil nilai fitur sesuai urutan FEATURES
        values = [float(data.get(f, 0)) for f in FEATURES]
        X_input = np.array(values).reshape(1, -1)
        X_scaled = scaler.transform(X_input)

        # Prediksi
        pred_kode = int(use_model.predict(X_scaled)[0])
        proba     = use_model.predict_proba(X_scaled)[0].tolist()
        label     = INV_LABEL.get(pred_kode, "unknown")

        # Susun probabilitas berdasarkan kelas 0-3
        prob_dict = {
            "sangat_rendah": round(proba[0], 4) if len(proba) > 0 else 0,
            "rendah"       : round(proba[1], 4) if len(proba) > 1 else 0,
            "sedang"       : round(proba[2], 4) if len(proba) > 2 else 0,
            "tinggi"       : round(proba[3], 4) if len(proba) > 3 else 0,
        }

        # Probabilitas kelas yang diprediksi
        prob_utama = proba[pred_kode] if pred_kode < len(proba) else 0

        return jsonify({
            "status"           : "success",
            "model_digunakan"  : model_name,
            "prediksi_kode"    : pred_kode,
            "prediksi_label"   : label,
            "prediksi_display" : label.title(),
            "probabilitas_utama": round(prob_utama * 100, 2),
            "probabilitas"     : prob_dict,
            "warna"            : WARNA_MAP.get(label, "#64748b"),
            "icon"             : ICON_MAP.get(label, "fa-water"),
            "fitur_input"      : dict(zip(FEATURES, values)),
        })

    except KeyError as e:
        return jsonify({"status": "error", "message": f"Parameter tidak lengkap: {e}"}), 400
    except Exception as e:
        return jsonify({"status": "error", "message": str(e)}), 500


# ── Referensi mapping (untuk dipakai form Laravel) ───────────────
@app.route("/mapping", methods=["GET"])
def mapping():
    """Kembalikan semua mapping nilai yang dibutuhkan form prediksi."""
    if meta is None:
        return jsonify({"status": "error"}), 500
    return jsonify({
        "tanah_map"   : meta["tanah_map"],
        "lahan_risiko": meta["lahan_risiko"],
        "ch_map"      : meta["ch_map"],
        "kemiringan"  : {
            "1": "0-8% (Datar)",
            "2": "8-15% (Landai)",
            "3": "15-25% (Agak Curam)",
            "4": "25-45% (Curam)",
            "5": ">45% (Sangat Curam)"
        }
    })


# ── Entry point ──────────────────────────────────────────────────
if __name__ == "__main__":
    port = int(os.environ.get("PORT", 5000))
    debug = os.environ.get("FLASK_DEBUG", "false").lower() == "true"
    print(f"[START] Flask API berjalan di http://0.0.0.0:{port}")
    app.run(host="0.0.0.0", port=port, debug=debug)
