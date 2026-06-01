"""
Diagnostic script untuk memeriksa kesehatan sistem klasifikasi
"""
import os
import sys
import json
import psutil
import subprocess
from pathlib import Path

def get_system_memory():
    """Get current system memory usage"""
    memory = psutil.virtual_memory()
    return {
        "total_gb": round(memory.total / (1024**3), 2),
        "available_gb": round(memory.available / (1024**3), 2),
        "used_gb": round(memory.used / (1024**3), 2),
        "percent_used": memory.percent,
    }

def check_tensorflow():
    """Check TensorFlow installation"""
    try:
        import tensorflow as tf
        return {
            "installed": True,
            "version": tf.__version__,
            "gpu_available": len(tf.config.list_physical_devices('GPU')) > 0,
        }
    except ImportError as e:
        return {
            "installed": False,
            "error": str(e),
        }

def check_model_file(model_dir):
    """Check if model files exist"""
    candidates = [
        "rice_leaf_disease_model.keras",
        "rice_leaf_disease_model.h5",
        "rice_leaf_disease_model.json",
    ]
    
    results = {}
    for candidate in candidates:
        path = os.path.join(model_dir, candidate)
        results[candidate] = {
            "exists": os.path.isfile(path),
            "size_mb": round(os.path.getsize(path) / (1024**2), 2) if os.path.isfile(path) else 0,
        }
    return results

def test_model_load(script_path, model_dir):
    """Test if model can be loaded"""
    try:
        # Try health check via Python script
        result = subprocess.run(
            [sys.executable, script_path, "health", "--model-dir", model_dir],
            capture_output=True,
            text=True,
            timeout=60
        )
        
        output = result.stdout.strip()
        if output:
            try:
                data = json.loads(output)
                return data
            except json.JSONDecodeError:
                return {
                    "error": "Invalid JSON response",
                    "output": output[:200],
                }
        else:
            return {
                "error": "No output from health check",
                "stderr": result.stderr[:200],
            }
    except subprocess.TimeoutExpired:
        return {"error": "Health check timeout (>60s)"}
    except Exception as e:
        return {"error": str(e)}

def main():
    # Get arguments
    model_dir = sys.argv[1] if len(sys.argv) > 1 else "../rice leaf diseases dataset"
    script_path = sys.argv[2] if len(sys.argv) > 2 else "rice_inference.py"
    
    diagnostics = {
        "timestamp": str(__import__('datetime').datetime.now()),
        "python_executable": sys.executable,
        "python_version": sys.version,
        "model_directory": model_dir,
        "script_path": script_path,
    }
    
    # Check memory
    diagnostics["system_memory"] = get_system_memory()
    
    # Check TensorFlow
    diagnostics["tensorflow"] = check_tensorflow()
    
    # Check model files
    diagnostics["model_files"] = check_model_file(model_dir)
    
    # Test model loading
    diagnostics["model_load_test"] = test_model_load(script_path, model_dir)
    
    print(json.dumps(diagnostics, indent=2))

if __name__ == "__main__":
    main()
