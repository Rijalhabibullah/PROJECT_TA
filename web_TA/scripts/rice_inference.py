"""
Python CLI untuk inferensi model rice leaf disease.
Dipanggil langsung dari Laravel, tanpa Flask API.
"""

import argparse
import base64
import json
import os
import sys
from io import BytesIO

import numpy as np
import requests
from PIL import Image


CLASS_NAMES = ["Bacterialblight", "Brownspot", "Leafsmut"]
IMG_SIZE = (224, 224)

try:
    from tensorflow import keras
    TENSORFLOW_AVAILABLE = True
    TENSORFLOW_IMPORT_ERROR = None
except Exception:
    keras = None
    TENSORFLOW_AVAILABLE = False
    TENSORFLOW_IMPORT_ERROR = str(sys.exc_info()[1])

MODEL = None
MODEL_PATH = None


def _read_json_input() -> dict:
    raw = sys.stdin.read().strip()
    if not raw:
        return {}
    try:
        data = json.loads(raw)
        return data if isinstance(data, dict) else {}
    except json.JSONDecodeError:
        return {}


def _emit(data: dict, exit_code: int = 0) -> None:
    print(json.dumps(data, ensure_ascii=True))
    raise SystemExit(exit_code)


def _find_model_file(model_dir: str) -> str | None:
    candidates = [
        "rice_leaf_disease_model.keras",
        "rice_leaf_disease_model.h5",
        "rice_leaf_disease_model.json",
    ]

    for file_name in candidates:
        full_path = os.path.join(model_dir, file_name)
        if os.path.isfile(full_path):
            return full_path
    return None


def _load_model(model_dir: str):
    global MODEL, MODEL_PATH

    if MODEL is not None:
        return MODEL

    if not TENSORFLOW_AVAILABLE:
        raise RuntimeError("TensorFlow tidak tersedia pada environment Python ini.")

    model_file = _find_model_file(model_dir)
    if model_file is None:
        raise RuntimeError(
            "Model tidak ditemukan. Pastikan salah satu file ini ada: "
            "rice_leaf_disease_model.keras, rice_leaf_disease_model.h5, rice_leaf_disease_model.json"
        )

    MODEL_PATH = model_file

    if model_file.endswith(".keras") or model_file.endswith(".h5"):
        MODEL = keras.models.load_model(model_file)
        return MODEL

    with open(model_file, "r", encoding="utf-8") as f:
        model_json = f.read()

    MODEL = keras.models.model_from_json(model_json)

    weights_base = model_file.replace(".json", "")
    weight_candidates = [
        f"{weights_base}.h5",
        f"{weights_base}_weights.h5",
    ]

    for weights_file in weight_candidates:
        if os.path.isfile(weights_file):
            MODEL.load_weights(weights_file)
            return MODEL

    raise RuntimeError("Model JSON ditemukan, tetapi file weights tidak ditemukan.")


def _preprocess_image(image_bytes: bytes):
    image = Image.open(BytesIO(image_bytes))
    if image.mode != "RGB":
        image = image.convert("RGB")

    try:
        resample_filter = Image.Resampling.LANCZOS
    except AttributeError:
        resample_filter = Image.LANCZOS

    image = image.resize(IMG_SIZE, resample_filter)

    img_array = np.array(image, dtype="float32") / 255.0
    img_array = np.expand_dims(img_array, axis=0)
    return img_array


def _predict(image_bytes: bytes, model_dir: str) -> dict:
    model = _load_model(model_dir)

    img_array = _preprocess_image(image_bytes)
    predictions = model.predict(img_array, verbose=0)

    predicted_idx = int(np.argmax(predictions[0]))
    predicted_class = CLASS_NAMES[predicted_idx]
    confidence = float(predictions[0][predicted_idx])

    all_predictions = {
        class_name: float(predictions[0][idx])
        for idx, class_name in enumerate(CLASS_NAMES)
    }

    return {
        "success": True,
        "predicted_class": predicted_class,
        "confidence": confidence,
        "all_predictions": all_predictions,
        "model_path": MODEL_PATH,
    }


def action_classify(model_dir: str) -> None:
    payload = _read_json_input()
    image_base64 = payload.get("image")

    if not image_base64 or not isinstance(image_base64, str):
        _emit({"success": False, "message": "Field 'image' (base64) diperlukan"}, 0)

    try:
        image_bytes = base64.b64decode(image_base64)
        result = _predict(image_bytes, model_dir)
        _emit(result, 0)
    except Exception as e:
        _emit({"success": False, "message": str(e)}, 0)


def action_classify_from_url(model_dir: str) -> None:
    payload = _read_json_input()
    image_url = payload.get("image_url")

    if not image_url or not isinstance(image_url, str):
        _emit({"success": False, "message": "Field 'image_url' diperlukan"}, 0)

    try:
        response = requests.get(image_url, timeout=10)
        if response.status_code != 200:
            _emit({"success": False, "message": "Gagal download image dari URL"}, 0)

        result = _predict(response.content, model_dir)
        result["url"] = image_url
        _emit(result, 0)
    except Exception as e:
        _emit({"success": False, "message": str(e)}, 0)


def action_health(model_dir: str) -> None:
    if not TENSORFLOW_AVAILABLE:
        message = "TensorFlow tidak tersedia"
        if TENSORFLOW_IMPORT_ERROR:
            message = f"TensorFlow tidak tersedia: {TENSORFLOW_IMPORT_ERROR}"

        _emit(
            {
                "status": "error",
                "message": message,
                "model_loaded": False,
                "python_executable": sys.executable,
                "classes": CLASS_NAMES,
            },
            0,
        )

    try:
        model = _load_model(model_dir)
        _emit(
            {
                "status": "ok",
                "message": "Model siap digunakan",
                "model_loaded": True,
                "model_path": MODEL_PATH,
                "python_executable": sys.executable,
                "classes": CLASS_NAMES,
                "input_shape": str(model.input_shape),
            },
            0,
        )
    except Exception as e:
        _emit(
            {
                "status": "error",
                "message": str(e),
                "model_loaded": False,
                "python_executable": sys.executable,
                "classes": CLASS_NAMES,
            },
            0,
        )


def action_info(model_dir: str) -> None:
    if not TENSORFLOW_AVAILABLE:
        message = "TensorFlow tidak tersedia"
        if TENSORFLOW_IMPORT_ERROR:
            message = f"TensorFlow tidak tersedia: {TENSORFLOW_IMPORT_ERROR}"

        _emit(
            {
                "model_loaded": False,
                "message": message,
                "python_executable": sys.executable,
                "classes": CLASS_NAMES,
                "number_of_classes": len(CLASS_NAMES),
            },
            0,
        )

    try:
        model = _load_model(model_dir)
        _emit(
            {
                "model_loaded": True,
                "model_path": MODEL_PATH,
                "python_executable": sys.executable,
                "classes": CLASS_NAMES,
                "number_of_classes": len(CLASS_NAMES),
                "input_shape": str(model.input_shape),
                "number_of_layers": len(model.layers),
                "total_parameters": int(model.count_params()),
            },
            0,
        )
    except Exception as e:
        _emit(
            {
                "model_loaded": False,
                "message": str(e),
                "python_executable": sys.executable,
                "classes": CLASS_NAMES,
                "number_of_classes": len(CLASS_NAMES),
            },
            0,
        )


def main() -> None:
    parser = argparse.ArgumentParser(description="Rice leaf disease inference CLI")
    parser.add_argument(
        "action",
        choices=["classify", "classify-from-url", "health", "info"],
    )
    parser.add_argument(
        "--model-dir",
        required=True,
        help="Direktori tempat file model berada",
    )
    args = parser.parse_args()

    if args.action == "classify":
        action_classify(args.model_dir)
    if args.action == "classify-from-url":
        action_classify_from_url(args.model_dir)
    if args.action == "health":
        action_health(args.model_dir)
    if args.action == "info":
        action_info(args.model_dir)


if __name__ == "__main__":
    main()
