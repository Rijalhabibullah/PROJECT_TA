"""
API Client Library - Python
Untuk mengakses API Classification dari Python
"""

import requests
import base64
from pathlib import Path
from typing import Dict, List, Optional, Tuple
import json


class RiceLeafClassificationAPI:
    """Client untuk Rice Leaf Disease Classification API"""
    
    def __init__(self, base_url: str = "http://127.0.0.1:5000"):
        """
        Initialize API client
        
        Args:
            base_url: Base URL untuk API endpoint (default: Flask API on port 5000)
        """
        self.base_url = base_url
        self.session = requests.Session()
        self.timeout = 30
    
    def test_connection(self) -> Tuple[bool, str]:
        """
        Test koneksi ke API
        
        Returns:
            Tuple (success, message)
        """
        try:
            response = self.session.get(f"{self.base_url}/health", timeout=10)
            if response.status_code == 200:
                data = response.json()
                if 'status' in data:  # Flask API response
                    return data['status'] == 'ok', data.get('message', 'Connection OK')
                else:
                    return data.get('success', True), data.get('message', 'Connection OK')
            else:
                return False, f"HTTP {response.status_code}"
        except Exception as e:
            return False, str(e)
    
    def classify_image(
        self,
        image_path: str,
        save: bool = False,
        notes: Optional[str] = None
    ) -> Optional[Dict]:
        """
        Klasifikasi gambar
        
        Args:
            image_path: Path ke file gambar
            save: Jika True, simpan gambar ke server
            notes: Catatan tambahan (hanya jika save=True)
            
        Returns:
            Dict dengan hasil klasifikasi atau None jika error
        """
        # Validasi file
        image_file = Path(image_path)
        if not image_file.exists():
            print(f"Error: File tidak ditemukan: {image_path}")
            return None
        
        if not image_file.suffix.lower() in ['.jpg', '.jpeg', '.png', '.gif']:
            print(f"Error: Format file tidak didukung: {image_file.suffix}")
            return None
        
        try:
            # Tentukan endpoint
            endpoint = "classify" if not save else "classify"
            url = f"{self.base_url}/{endpoint}"
            
            # Baca dan kirim file
            with open(image_file, 'rb') as f:
                files = {'image': f}
                data = {}
                if save and notes:
                    data['notes'] = notes
                
                response = self.session.post(
                    url,
                    files=files,
                    data=data,
                    timeout=self.timeout
                )
            
            if response.status_code == 200:
                response_data = response.json()
                if response_data.get('success'):
                    return response_data.get('data')
                else:
                    print(f"Error: {response_data.get('message')}")
                    return None
            else:
                print(f"Error: HTTP {response.status_code}")
                return None
                
        except Exception as e:
            print(f"Error during classification: {str(e)}")
            return None
    
    def classify_from_base64(
        self,
        base64_image: str,
        filename: str = "image.jpg"
    ) -> Optional[Dict]:
        """
        Klasifikasi dari base64 string
        
        Args:
            base64_image: Base64 encoded image string
            filename: Nama file (opsional)
            
        Returns:
            Dict dengan hasil klasifikasi
        """
        try:
            url = f"{self.base_url}/classify"
            payload = {
                "image": base64_image,
                "filename": filename
            }
            
            response = self.session.post(
                url,
                json=payload,
                timeout=self.timeout
            )
            
            if response.status_code == 200:
                response_data = response.json()
                if response_data.get('success'):
                    return response_data.get('data')
                else:
                    print(f"Error: {response_data.get('message')}")
                    return None
            else:
                print(f"Error: HTTP {response.status_code}")
                return None
                
        except Exception as e:
            print(f"Error: {str(e)}")
            return None
    
    def batch_classify(
        self,
        image_paths: List[str],
        save: bool = False
    ) -> List[Dict]:
        """
        Klasifikasi multiple gambar sekaligus
        
        Args:
            image_paths: List path-ke-gambar
            save: Simpan ke server
            
        Returns:
            List hasil klasifikasi
        """
        results = []
        total = len(image_paths)
        
        for idx, path in enumerate(image_paths, 1):
            print(f"Processing {idx}/{total}: {Path(path).name}...", end=" ")
            result = self.classify_image(path, save=save)
            
            if result:
                print("✓")
                results.append({
                    'image': path,
                    'result': result
                })
            else:
                print("✗")
                results.append({
                    'image': path,
                    'result': None
                })
        
        return results
    
    def print_result(self, result: Dict):
        """Print hasil klasifikasi dalam format yang dapat dibaca"""
        print("\n" + "="*60)
        print("KLASIFIKASI HASIL")
        print("="*60)
        
        print(f"\n🎯 DIAGNOSIS: {result['disease_info']['name']}")
        print(f"   Confidence: {result['confidence']}")
        print(f"   Severity: {result['disease_info']['severity']}")
        
        print(f"\n📊 PREDIKSI SEMUA KELAS:")
        for class_name, score in result['all_predictions'].items():
            percentage = f"{score*100:.2f}%"
            bar = "█" * int(score * 20)
            print(f"   {class_name:20} {percentage:>8} {bar}")
        
        print(f"\n🔬 GEJALA:")
        for symptom in result['disease_info']['symptoms']:
            print(f"   • {symptom}")
        
        print(f"\n💊 PENANGANAN:")
        for treatment in result['disease_info']['treatment']:
            print(f"   • {treatment}")
        
        print("\n" + "="*60 + "\n")


# ============================================================================
# CONTOH PENGGUNAAN
# ============================================================================

if __name__ == "__main__":
    # Initialize client
    api = RiceLeafClassificationAPI()
    
    # Test connection
    print("Testing API connection...")
    success, message = api.test_connection()
    if success:
        print(f"✓ {message}\n")
    else:
        print(f"✗ Failed: {message}\n")
        exit(1)
    
    # Contoh 1: Klasifikasi single image
    print("Example 1: Klasifikasi single image")
    print("-" * 60)
    image_path = "path/to/rice_leaf.jpg"  # Ganti dengan path asli
    result = api.classify_image(image_path, save=True, notes="Test dari script")
    if result:
        api.print_result(result)
    
    # Contoh 2: Batch classification
    print("\nExample 2: Batch classification")
    print("-" * 60)
    image_list = [
        "path/to/image1.jpg",
        "path/to/image2.jpg",
        "path/to/image3.jpg",
    ]
    results = api.batch_classify(image_list, save=True)
    
    # Summary
    successful = sum(1 for r in results if r['result'] is not None)
    print(f"\nBatch Summary: {successful}/{len(results)} berhasil")
    
    # Contoh 3: Klasifikasi dari base64
    print("\nExample 3: Klasifikasi dari base64")
    print("-" * 60)
    with open("image.jpg", "rb") as f:
        base64_image = base64.b64encode(f.read()).decode('utf-8')
    
    result = api.classify_from_base64(base64_image)
    if result:
        api.print_result(result)
