"""
Simple test script untuk menguji API Classification
"""

import requests
import base64
import json
from pathlib import Path

# Konfigurasi
LARAVEL_API_BASE = "http://127.0.0.1:8000/api/classification"
PYTHON_API_BASE = "http://127.0.0.1:5000"

def test_python_api_health():
    """Test health check Python API"""
    print("\n" + "="*60)
    print("TEST 1: Python API Health Check")
    print("="*60)
    
    try:
        response = requests.get(f"{PYTHON_API_BASE}/health", timeout=5)
        
        if response.status_code == 200:
            data = response.json()
            print("✅ Python API berhasil dihubungi!")
            print(f"   Status: {data['status']}")
            print(f"   Model Loaded: {data['model_loaded']}")
            print(f"   Classes: {', '.join(data['classes'])}")
            return True
        else:
            print(f"❌ API responded with status {response.status_code}")
            return False
            
    except requests.exceptions.ConnectionError:
        print("❌ Tidak dapat menghubungi Python API")
        print(f"   Pastikan server berjalan di {PYTHON_API_BASE}")
        return False
    except Exception as e:
        print(f"❌ Error: {str(e)}")
        return False


def test_laravel_api_connection():
    """Test connection via Laravel API"""
    print("\n" + "="*60)
    print("TEST 2: Laravel API Connection Test")
    print("="*60)
    
    try:
        response = requests.get(f"{LARAVEL_API_BASE}/test", timeout=5)
        
        if response.status_code == 200:
            data = response.json()
            print("✅ Laravel API berhasil dihubungi!")
            print(f"   Success: {data['success']}")
            print(f"   Message: {data['message']}")
            if 'model_info' in data:
                print(f"   Model Info: {json.dumps(data['model_info'], indent=2)}")
            return True
        else:
            print(f"❌ API responded with status {response.status_code}")
            print(f"   Response: {response.text}")
            return False
            
    except requests.exceptions.ConnectionError:
        print("❌ Tidak dapat menghubungi Laravel API")
        print(f"   Pastikan Laravel server berjalan di http://127.0.0.1:8000")
        return False
    except Exception as e:
        print(f"❌ Error: {str(e)}")
        return False


def test_image_classification(image_path):
    """Test klasifikasi gambar via Laravel API"""
    print("\n" + "="*60)
    print(f"TEST 3: Image Classification - {Path(image_path).name}")
    print("="*60)
    
    if not Path(image_path).exists():
        print(f"❌ File gambar tidak ditemukan: {image_path}")
        return False
    
    try:
        with open(image_path, 'rb') as f:
            files = {'image': f}
            response = requests.post(
                f"{LARAVEL_API_BASE}/classify",
                files=files,
                timeout=30
            )
        
        if response.status_code == 200:
            data = response.json()
            if data['success']:
                result = data['data']
                print("✅ Klasifikasi berhasil!")
                print(f"   Predicted Class: {result['predicted_class']}")
                print(f"   Confidence: {result['confidence']}")
                print(f"   Disease: {result['disease_info']['name']}")
                print(f"\n   Gejala:")
                for symptom in result['disease_info']['symptoms']:
                    print(f"   - {symptom}")
                print(f"\n   Penanganan:")
                for treatment in result['disease_info']['treatment']:
                    print(f"   - {treatment}")
                return True
            else:
                print(f"❌ Klasifikasi gagal: {data['message']}")
                return False
        else:
            print(f"❌ API returned status {response.status_code}")
            print(f"   Response: {response.text}")
            return False
            
    except Exception as e:
        print(f"❌ Error: {str(e)}")
        return False


def test_classify_and_save(image_path):
    """Test klasifikasi dan simpan gambar"""
    print("\n" + "="*60)
    print(f"TEST 4: Classify & Save - {Path(image_path).name}")
    print("="*60)
    
    if not Path(image_path).exists():
        print(f"❌ File gambar tidak ditemukan: {image_path}")
        return False
    
    try:
        with open(image_path, 'rb') as f:
            files = {'image': f}
            data = {'notes': 'Test classification from test script'}
            response = requests.post(
                f"{LARAVEL_API_BASE}/classify-and-save",
                files=files,
                data=data,
                timeout=30
            )
        
        if response.status_code == 200:
            data = response.json()
            if data['success']:
                result = data['data']
                print("✅ Klasifikasi & Save berhasil!")
                print(f"   Image Path: {result['image_path']}")
                print(f"   Predicted Class: {result['predicted_class']}")
                print(f"   Confidence: {result['confidence']}")
                return True
            else:
                print(f"❌ Gagal: {data['message']}")
                return False
        else:
            print(f"❌ API returned status {response.status_code}")
            return False
            
    except Exception as e:
        print(f"❌ Error: {str(e)}")
        return False


def main():
    """Run all tests"""
    print("\n")
    print("#" * 60)
    print("# Rice Leaf Disease Classification API - Test Suite")
    print("#" * 60)
    
    # Test Python API
    python_ok = test_python_api_health()
    
    # Test Laravel API
    laravel_ok = test_laravel_api_connection()
    
    # Test dengan gambar contoh jika ada
    test_image_paths = [
        "test_image.jpg",
        "sample.png",
        "Bacterialblight/01.jpg",  # Dari dataset
        "../rice leaf diseases dataset/Bacterialblight/01.jpg"
    ]
    
    image_found = None
    for img_path in test_image_paths:
        if Path(img_path).exists():
            image_found = img_path
            break
    
    if image_found:
        test_image_classification(image_found)
        test_classify_and_save(image_found)
    else:
        print("\n" + "="*60)
        print("⚠️  Tidak ada file test image ditemukan")
        print("="*60)
        print("Untuk test klasifikasi, letakkan file gambar:")
        for img_path in test_image_paths[:2]:
            print(f"  - {img_path}")
    
    # Summary
    print("\n" + "="*60)
    print("TEST SUMMARY")
    print("="*60)
    print(f"Python API:  {'✅ OK' if python_ok else '❌ FAILED'}")
    print(f"Laravel API: {'✅ OK' if laravel_ok else '❌ FAILED'}")
    print("\nUntuk hasil lengkap, sediakan file gambar test.\n")


if __name__ == "__main__":
    main()
