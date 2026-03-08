"""
Flask API Server untuk Rice Leaf Disease Classification
Menjalankan model TensorFlow dan melayani request dari Laravel
"""

import os
import json
import base64
import numpy as np
from PIL import Image
from io import BytesIO
from pathlib import Path
import warnings
warnings.filterwarnings('ignore')

# TensorFlow & Keras
try:
    import tensorflow as tf
    from tensorflow import keras
    from tensorflow.keras.preprocessing.image import img_to_array
    TENSORFLOW_AVAILABLE = True
except ImportError as e:
    print(f"⚠️  Warning: TensorFlow not available: {e}")
    print("   Using mock predictions for testing")
    TENSORFLOW_AVAILABLE = False
    keras = None

# Flask
from flask import Flask, request, jsonify
from flask_cors import CORS

# Inisialisasi Flask
app = Flask(__name__)
CORS(app)

# Konfigurasi
MODEL_PATH = None  # Akan diset saat startup
MODEL = None
IMG_SIZE = (224, 224)
CLASS_NAMES = ['Bacterialblight', 'Brownspot', 'Leafsmut']

def load_model():
    """Load model dari file yang tersedia"""
    global MODEL_PATH, MODEL
    
    if not TENSORFLOW_AVAILABLE:
        print("⚠️  TensorFlow not available - Using mock mode for testing")
        MODEL_PATH = "MOCK_MODEL"
        return True
    
    # Cari model file yang tersedia
    possible_models = [
        'rice_leaf_disease_model.keras',
        'rice_leaf_disease_model.h5',
        'rice_leaf_disease_model.json'
    ]
    
    for model_name in possible_models:
        if os.path.exists(model_name):
            MODEL_PATH = model_name
            print(f"✓ Model ditemukan: {MODEL_PATH}")
            
            try:
                if model_name.endswith('.keras'):
                    MODEL = keras.models.load_model(model_name)
                elif model_name.endswith('.h5'):
                    MODEL = keras.models.load_model(model_name)
                elif model_name.endswith('.json'):
                    # Load model dari JSON + weights
                    with open(model_name, 'r') as f:
                        model_json = f.read()
                    MODEL = keras.models.model_from_json(model_json)
                    
                    # Cari weights file
                    weights_base = model_name.replace('.json', '')
                    weights_files = [
                        f"{weights_base}.h5",
                        f"{weights_base}_weights.h5"
                    ]
                    for weights_file in weights_files:
                        if os.path.exists(weights_file):
                            MODEL.load_weights(weights_file)
                            print(f"✓ Weights dimuat: {weights_file}")
                            break
                
                print(f"✓ Model berhasil dimuat!")
                print(f"  Model input shape: {MODEL.input_shape}")
                print(f"  Number of layers: {len(MODEL.layers)}")
                return True
            except Exception as e:
                print(f"✗ Error loading model: {str(e)}")
                return False
    
    print("✗ Model tidak ditemukan!")
    print("   Letakkan salah satu dari ini di folder yang sama dengan script ini:")
    print("   - rice_leaf_disease_model.keras")
    print("   - rice_leaf_disease_model.h5")
    print("   - rice_leaf_disease_model.json (+ .h5 weights)")
    return False


def preprocess_image(image_data):
    """
    Preprocessing gambar dari base64 atau bytes
    
    Args:
        image_data: base64 string atau bytes
        
    Returns:
        Preprocessed image array atau None jika error
    """
    try:
        # Jika string base64, decode dulu
        if isinstance(image_data, str):
            image_bytes = base64.b64decode(image_data)
        else:
            image_bytes = image_data

        # Convert bytes ke image menggunakan PIL
        image = Image.open(BytesIO(image_bytes))
        
        # Convert to RGB jika diperlukan
        if image.mode != 'RGB':
            image = image.convert('RGB')
        
        # Resize ke ukuran yang diharapkan model
        image = image.resize(IMG_SIZE, Image.Resampling.LANCZOS)
        
        # Convert ke numpy array
        img_array = np.array(image, dtype='float32')
        
        # Normalize pixel values ke range [0, 1]
        img_array = img_array / 255.0
        
        # Add batch dimension
        img_array = np.expand_dims(img_array, axis=0)
        
        return img_array
        
    except Exception as e:
        print(f"Error preprocessing image: {str(e)}")
        return None


def classify_image(image_data):
    """
    Klasifikasi gambar menggunakan model
    
    Args:
        image_data: base64 string atau bytes
        
    Returns:
        Dict dengan hasil klasifikasi atau None jika error
    """
    # Mock mode - jika TensorFlow tidak tersedia
    if not TENSORFLOW_AVAILABLE:
        import random
        predictions = [random.uniform(0.1, 0.9) for _ in CLASS_NAMES]
        max_pred = max(predictions)
        idx = predictions.index(max_pred)
        
        all_predictions = {}
        for i, class_name in enumerate(CLASS_NAMES):
            all_predictions[class_name] = round(predictions[i], 4)
        
        return {
            'predicted_class': CLASS_NAMES[idx],
            'confidence': round(max_pred, 4),
            'all_predictions': all_predictions
        }
    
    if MODEL is None:
        return None
    
    try:
        # Preprocess image
        img_array = preprocess_image(image_data)
        
        if img_array is None:
            return None
        
        # Prediction
        predictions = MODEL.predict(img_array, verbose=0)
        
        # Get predicted class dan confidence
        predicted_idx = np.argmax(predictions[0])
        predicted_class = CLASS_NAMES[predicted_idx]
        confidence = float(predictions[0][predicted_idx])
        
        # Build all predictions
        all_predictions = {}
        for idx, class_name in enumerate(CLASS_NAMES):
            all_predictions[class_name] = float(predictions[0][idx])
        
        return {
            'predicted_class': predicted_class,
            'confidence': confidence,
            'all_predictions': all_predictions
        }
        
    except Exception as e:
        print(f"Error during classification: {str(e)}")
        return None


# ============================================================================
# ROUTES
# ============================================================================

@app.route('/health', methods=['POST', 'GET'])
def health_check():
    """Check apakah API berjalan dan model tersedia"""
    if not TENSORFLOW_AVAILABLE:
        return jsonify({
            'status': 'ok',
            'message': 'API running in MOCK MODE (TensorFlow not available)',
            'model_loaded': False,
            'mock_mode': True,
            'classes': CLASS_NAMES
        }), 200
    
    if MODEL is None:
        return jsonify({
            'status': 'error',
            'message': 'Model not loaded',
            'model_loaded': False
        }), 503
    
    return jsonify({
        'status': 'ok',
        'message': 'API is running',
        'model_loaded': True,
        'model_path': MODEL_PATH,
        'classes': CLASS_NAMES,
        'input_shape': str(MODEL.input_shape)
    }), 200


@app.route('/classify', methods=['POST'])
def classify():
    """
    API endpoint untuk klasifikasi gambar
    
    Expected request:
    {
        "image": "base64_encoded_image_string",
        "filename": "optional_filename.jpg"
    }
    
    Response:
    {
        "success": true,
        "predicted_class": "Bacterialblight",
        "confidence": 0.95,
        "all_predictions": {
            "Bacterialblight": 0.95,
            "Brownspot": 0.04,
            "Leafsmut": 0.01
        }
    }
    """
    try:
        data = request.get_json()
        
        if data is None:
            return jsonify({
                'success': False,
                'message': 'Request harus JSON'
            }), 400
        
        # Validasi input
        if 'image' not in data:
            return jsonify({
                'success': False,
                'message': 'Field "image" (base64) diperlukan'
            }), 400
        
        image_data = data['image']
        filename = data.get('filename', 'unknown')
        
        # Klasifikasi
        result = classify_image(image_data)
        
        if result is None:
            return jsonify({
                'success': False,
                'message': 'Gagal memproses gambar'
            }), 400
        
        # Log hasil
        print(f"✓ Classification done: {filename} -> {result['predicted_class']} ({result['confidence']:.2%})")
        
        return jsonify({
            'success': True,
            'predicted_class': result['predicted_class'],
            'confidence': result['confidence'],
            'all_predictions': result['all_predictions'],
            'filename': filename
        }), 200
        
    except Exception as e:
        print(f"✗ Error in classify endpoint: {str(e)}")
        return jsonify({
            'success': False,
            'message': f'Error: {str(e)}'
        }), 500


@app.route('/classify-from-url', methods=['POST'])
def classify_from_url():
    """
    Alternative endpoint untuk klasifikasi dari URL gambar
    
    Expected request:
    {
        "image_url": "http://example.com/image.jpg"
    }
    """
    try:
        data = request.get_json()
        
        if data is None or 'image_url' not in data:
            return jsonify({
                'success': False,
                'message': 'Field "image_url" diperlukan'
            }), 400
        
        image_url = data['image_url']
        
        import requests
        response = requests.get(image_url, timeout=10)
        
        if response.status_code != 200:
            return jsonify({
                'success': False,
                'message': f'Gagal download image dari URL'
            }), 400
        
        # Klasifikasi
        result = classify_image(response.content)
        
        if result is None:
            return jsonify({
                'success': False,
                'message': 'Gagal memproses gambar'
            }), 400
        
        return jsonify({
            'success': True,
            'predicted_class': result['predicted_class'],
            'confidence': result['confidence'],
            'all_predictions': result['all_predictions'],
            'url': image_url
        }), 200
        
    except Exception as e:
        return jsonify({
            'success': False,
            'message': f'Error: {str(e)}'
        }), 500


@app.route('/info', methods=['GET'])
def model_info():
    """Get informasi tentang model"""
    if MODEL is None:
        return jsonify({
            'status': 'error',
            'message': 'Model not loaded'
        }), 503
    
    return jsonify({
        'model_loaded': True,
        'model_path': MODEL_PATH,
        'classes': CLASS_NAMES,
        'number_of_classes': len(CLASS_NAMES),
        'input_shape': str(MODEL.input_shape),
        'number_of_layers': len(MODEL.layers),
        'total_parameters': int(MODEL.count_params())
    }), 200


@app.route('/', methods=['GET'])
def index():
    """Root endpoint dengan informasi API"""
    return jsonify({
        'name': 'Rice Leaf Disease Classification API',
        'version': '1.0',
        'description': 'API untuk klasifikasi penyakit daun padi menggunakan CNN',
        'endpoints': {
            'POST /classify': 'Klasifikasi gambar (base64)',
            'POST /classify-from-url': 'Klasifikasi gambar dari URL',
            'GET /health': 'Health check',
            'GET /info': 'Informasi model',
            'GET /': 'Info API ini'
        },
        'model_status': 'Loaded' if MODEL is not None else 'Not loaded',
        'classes': CLASS_NAMES
    }), 200


@app.errorhandler(404)
def not_found(error):
    return jsonify({
        'success': False,
        'message': 'Endpoint tidak ditemukan'
    }), 404


@app.errorhandler(500)
def server_error(error):
    return jsonify({
        'success': False,
        'message': 'Internal server error'
    }), 500


# ============================================================================
# STARTUP
# ============================================================================

if __name__ == '__main__':
    print("\n" + "="*60)
    print("Rice Leaf Disease Classification API Server")
    print("="*60 + "\n")
    
    # Pindah ke folder yang sama dengan script
    script_dir = os.path.dirname(os.path.abspath(__file__))
    os.chdir(script_dir)
    print(f"Working directory: {os.getcwd()}\n")
    
    # Load model
    print("Loading model...")
    if not load_model():
        print("\n⚠️  WARNING: Model tidak dapat dimuat!")
        print("   API akan berjalan tapi endpoint /classify akan gagal.\n")
    
    print("\n" + "="*60)
    print("Starting Flask API Server...")
    print("="*60)
    print("Server berjalan di http://127.0.0.1:5000/")
    print("Tekan CTRL+C untuk menghentikan.\n")
    
    # Run Flask app
    app.run(
        host='127.0.0.1',
        port=5000,
        debug=False,  # Set ke True jika development
        use_reloader=False
    )
