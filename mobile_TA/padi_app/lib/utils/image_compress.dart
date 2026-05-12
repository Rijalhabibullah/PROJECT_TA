import 'dart:io';

import 'package:flutter/foundation.dart';
import 'package:flutter_image_compress/flutter_image_compress.dart';
import 'package:path_provider/path_provider.dart';

Future<File> compressImageFile(File input, {
  int minWidth = 1280,
  int minHeight = 1280,
  int quality = 75,
  int maxBytes = 500 * 1024,
}) async {
  if (kIsWeb) {
    return input;
  }

  final inputSize = await input.length();
  if (inputSize <= maxBytes) {
    return input;
  }

  final tempDir = await getTemporaryDirectory();
  final targetPath =
      '${tempDir.path}/img_${DateTime.now().millisecondsSinceEpoch}.jpg';

  final compressed = await FlutterImageCompress.compressAndGetFile(
    input.path,
    targetPath,
    quality: quality,
    minWidth: minWidth,
    minHeight: minHeight,
    format: CompressFormat.jpeg,
  );

  if (compressed == null) {
    return input;
  }

  return File(compressed.path);
}
