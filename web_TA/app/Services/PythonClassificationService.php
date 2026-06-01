<?php

namespace App\Services;

use RuntimeException;
use Symfony\Component\Process\Process;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\FlockStore;

class PythonClassificationService
{
    private string $pythonExecutable;
    private string $scriptPath;
    private string $modelDirectory;
    private static ?\Symfony\Component\Lock\Lock $modelLoadLock = null;
    private static bool $modelLoaded = false;

    public function __construct()
    {
        $this->pythonExecutable = env('PYTHON_EXECUTABLE', 'python');
        $this->scriptPath = env('PYTHON_CLASSIFIER_SCRIPT', base_path('scripts/rice_inference.py'));
        $this->modelDirectory = env('RICE_MODEL_DIR', base_path('../rice leaf diseases dataset'));
    }

    public function classifyFromBase64(array $payload): array
    {
        // Add retry logic for transient failures
        $maxRetries = 3;
        $lastError = null;
        
        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                // Queue classification with exclusive lock to prevent concurrent model loads
                return $this->runActionWithLock('classify', $payload, 120);
            } catch (RuntimeException $e) {
                $lastError = $e;
                $errorMsg = $e->getMessage();
                
                // Only retry on specific errors (memory, timeout)
                if (strpos($errorMsg, 'timeout') === false && 
                    strpos($errorMsg, 'memory') === false &&
                    strpos($errorMsg, 'TensorFlow') === false) {
                    throw $e;
                }
                
                if ($attempt < $maxRetries) {
                    // Wait before retry (exponential backoff)
                    sleep($attempt);
                    continue;
                }
            }
        }
        
        throw $lastError ?? new RuntimeException('Klasifikasi gagal setelah percobaan berulang');
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

    /**
     * Run action with file lock to prevent concurrent model loads
     * @param string $action
     * @param array $payload
     * @param int $timeout
     * @return array
     */
    private function runActionWithLock(string $action, array $payload = [], int $timeout = 60): array
    {
        $lockPath = storage_path('locks/model_' . md5($this->modelDirectory) . '.lock');
        @mkdir(dirname($lockPath), 0755, true);
        
        $lockFile = fopen($lockPath, 'c');
        if (!$lockFile) {
            throw new RuntimeException("Tidak dapat membuat lock file di {$lockPath}");
        }
        
        // Wait for exclusive lock (blocking) with timeout
        $lockStart = time();
        $lockTimeout = min(60, $timeout - 5); // Reserve 5 seconds for execution
        
        while (!flock($lockFile, LOCK_EX | LOCK_NB)) {
            if (time() - $lockStart > $lockTimeout) {
                fclose($lockFile);
                throw new RuntimeException('Model sedang diproses, silahkan coba lagi dalam beberapa detik');
            }
            usleep(100000); // 100ms wait
        }
        
        try {
            // Reset model state to load fresh
            $payload['_skip_cache'] = true;
            $result = $this->runAction($action, $payload, $timeout);
            return $result;
        } finally {
            flock($lockFile, LOCK_UN);
            fclose($lockFile);
        }
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
        $process->setIdleTimeout($timeout - 5); // Prevent premature termination
        $process->setInput(json_encode($payload, JSON_UNESCAPED_SLASHES));
        
        try {
            $process->mustRun();
        } catch (\Symfony\Component\Process\Exception\ProcessFailedException $e) {
            $stderr = trim($process->getErrorOutput());
            $stdout = trim($process->getOutput());
            
            // Enhanced error logging
            $errorLog = "Python Process Error:\n";
            $errorLog .= "Action: {$action}\n";
            $errorLog .= "Exit Code: {$process->getExitCode()}\n";
            if ($stderr) $errorLog .= "STDERR: {$stderr}\n";
            if ($stdout) $errorLog .= "STDOUT: {$stdout}\n";
            
            \Log::error($errorLog);
            
            throw new RuntimeException($stderr ?: ($stdout ?: 'Gagal menjalankan proses inferensi Python'));
        }

        $output = trim($process->getOutput());
        if ($output === '') {
            throw new RuntimeException('Proses inferensi Python tidak mengembalikan output.');
        }

        $decoded = json_decode($output, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('Output inferensi Python bukan JSON yang valid: ' . substr($output, 0, 100));
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
