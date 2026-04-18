<?php

namespace App\Services;

use RuntimeException;
use Symfony\Component\Process\Process;

class PythonClassificationService
{
    private string $pythonExecutable;
    private string $scriptPath;
    private string $modelDirectory;

    public function __construct()
    {
        $this->pythonExecutable = env('PYTHON_EXECUTABLE', 'python');
        $this->scriptPath = env('PYTHON_CLASSIFIER_SCRIPT', base_path('scripts/rice_inference.py'));
        $this->modelDirectory = env('RICE_MODEL_DIR', base_path('../rice leaf diseases dataset'));
    }

    public function classifyFromBase64(array $payload): array
    {
        return $this->runAction('classify', $payload, 120);
    }

    public function classifyFromUrl(array $payload): array
    {
        return $this->runAction('classify-from-url', $payload, 120);
    }

    public function health(): array
    {
        return $this->runAction('health');
    }

    public function info(): array
    {
        return $this->runAction('info');
    }

    private function runAction(string $action, array $payload = [], int $timeout = 60): array
    {
        if (!is_file($this->scriptPath)) {
            throw new RuntimeException("Script classifier tidak ditemukan di {$this->scriptPath}");
        }

        $command = [
            $this->pythonExecutable,
            $this->scriptPath,
            $action,
            '--model-dir',
            $this->modelDirectory,
        ];

        $process = new Process($command, base_path(), $this->buildProcessEnvironment());
        $process->setTimeout($timeout);
        $process->setInput(json_encode($payload, JSON_UNESCAPED_SLASHES));
        $process->run();

        if (!$process->isSuccessful()) {
            $stderr = trim($process->getErrorOutput());
            $stdout = trim($process->getOutput());
            throw new RuntimeException($stderr !== '' ? $stderr : ($stdout !== '' ? $stdout : 'Gagal menjalankan proses inferensi Python'));
        }

        $output = trim($process->getOutput());
        if ($output === '') {
            throw new RuntimeException('Proses inferensi Python tidak mengembalikan output.');
        }

        $decoded = json_decode($output, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('Output inferensi Python bukan JSON yang valid.');
        }

        return $decoded;
    }

    private function buildProcessEnvironment(): array
    {
        $environment = array_merge($_SERVER, $_ENV);

        // Prevent Python from using conflicting host-level overrides.
        unset($environment['PYTHONHOME'], $environment['PYTHONPATH']);

        $pythonDir = dirname($this->pythonExecutable);
        $currentPath = getenv('PATH') ?: ($environment['PATH'] ?? '');
        $environment['PATH'] = $pythonDir . PATH_SEPARATOR . $currentPath;

        if (!isset($environment['SystemRoot']) || $environment['SystemRoot'] === '') {
            $environment['SystemRoot'] = getenv('SystemRoot') ?: 'C:\\Windows';
        }

        if (!isset($environment['WINDIR']) || $environment['WINDIR'] === '') {
            $environment['WINDIR'] = getenv('WINDIR') ?: 'C:\\Windows';
        }

        $fallbackUserProfile = getenv('USERPROFILE') ?: ('C:\\Users\\' . (getenv('USERNAME') ?: 'Public'));

        if (!isset($environment['USERPROFILE']) || $environment['USERPROFILE'] === '') {
            $environment['USERPROFILE'] = $fallbackUserProfile;
        }

        if (!isset($environment['HOMEDRIVE']) || $environment['HOMEDRIVE'] === '') {
            $environment['HOMEDRIVE'] = getenv('HOMEDRIVE') ?: substr($fallbackUserProfile, 0, 2);
        }

        if (!isset($environment['HOMEPATH']) || $environment['HOMEPATH'] === '') {
            $environment['HOMEPATH'] = getenv('HOMEPATH') ?: substr($fallbackUserProfile, 2);
        }

        if (!isset($environment['APPDATA']) || $environment['APPDATA'] === '') {
            $environment['APPDATA'] = getenv('APPDATA') ?: ($fallbackUserProfile . '\\AppData\\Roaming');
        }

        if (!isset($environment['LOCALAPPDATA']) || $environment['LOCALAPPDATA'] === '') {
            $environment['LOCALAPPDATA'] = getenv('LOCALAPPDATA') ?: ($fallbackUserProfile . '\\AppData\\Local');
        }

        if (!isset($environment['TEMP']) || $environment['TEMP'] === '') {
            $environment['TEMP'] = getenv('TEMP') ?: ($fallbackUserProfile . '\\AppData\\Local\\Temp');
        }

        if (!isset($environment['TMP']) || $environment['TMP'] === '') {
            $environment['TMP'] = getenv('TMP') ?: $environment['TEMP'];
        }

        return $environment;
    }
}
