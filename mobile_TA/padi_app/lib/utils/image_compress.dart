import 'dart:io';
import 'dart:typed_data';
import 'dart:ui' as ui;

import 'package:flutter/foundation.dart';
import 'package:flutter_image_compress/flutter_image_compress.dart';
import 'package:path_provider/path_provider.dart';

Future<File> compressImageFile(File input, {
  int quality = 80,
  int maxBytes = 500 * 1024,
}) async {
  if (kIsWeb) {
    return input;
  }

  final inputSize = await input.length();
  if (inputSize <= maxBytes) {
    return input;
  }

  try {
    // Decode image dimensions to keep the original resolution
    final Uint8List bytes = await input.readAsBytes();
    final ui.Codec codec = await ui.instantiateImageCodec(bytes);
    final ui.FrameInfo frameInfo = await codec.getNextFrame();
    final int originalWidth = frameInfo.image.width;
    final int originalHeight = frameInfo.image.height;

    final tempDir = await getTemporaryDirectory();
    final targetPath =
        '${tempDir.path}/img_${DateTime.now().millisecondsSinceEpoch}.jpg';

    final compressed = await FlutterImageCompress.compressAndGetFile(
      input.path,
      targetPath,
      quality: quality,
      minWidth: originalWidth,
      minHeight: originalHeight,
      format: CompressFormat.jpeg,
    );

    if (compressed == null) {
      return input;
    }

    return File(compressed.path);
  } catch (e) {
    debugPrint('Error compressing image: $e');
    return input;
  }
}
