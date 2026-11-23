<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class BackupService
{
    public function directory(): string
    {
        return 'backups';
    }

    public function create(): string
    {
        $disk = Storage::disk('local');
        $disk->makeDirectory($this->directory());

        $connection = config('database.default');
        $config = config("database.connections.{$connection}");
        $driver = $config['driver'] ?? $connection;
        $base = 'backup-' . now()->format('Ymd-His');

        if ($driver === 'sqlite') {
            $database = $config['database'];

            if (! is_string($database) || $database === ':memory:' || ! is_file($database)) {
                throw new \RuntimeException('No se puede respaldar una base SQLite en memoria o inexistente.');
            }

            $relative = $this->directory() . '/' . $base . '.sqlite';
            $disk->put($relative, file_get_contents($database));

            return $relative;
        }

        if ($driver === 'pgsql') {
            $relative = $this->directory() . '/' . $base . '.dump';
            $target = $disk->path($relative);

            $process = new Process([
                'pg_dump',
                '--no-owner',
                '--no-acl',
                '--format=custom',
                '--host=' . ($config['host'] ?? '127.0.0.1'),
                '--port=' . ($config['port'] ?? '5432'),
                '--username=' . ($config['username'] ?? ''),
                '--file=' . $target,
                (string) ($config['database'] ?? ''),
            ]);

            $process->setEnv(['PGPASSWORD' => (string) ($config['password'] ?? '')]);
            $process->setTimeout(300);
            $process->run();

            if (! $process->isSuccessful() || ! is_file($target)) {
                throw new \RuntimeException('No se pudo generar el respaldo con pg_dump: ' . $process->getErrorOutput());
            }

            return $relative;
        }

        if ($driver === 'mysql') {
            $relative = $this->directory() . '/' . $base . '.sql';
            $target = $disk->path($relative);

            $process = new Process([
                'mysqldump',
                '--host=' . ($config['host'] ?? '127.0.0.1'),
                '--port=' . ($config['port'] ?? '3306'),
                '--user=' . ($config['username'] ?? ''),
                '--result-file=' . $target,
                (string) ($config['database'] ?? ''),
            ]);

            $process->setEnv(['MYSQL_PWD' => (string) ($config['password'] ?? '')]);
            $process->setTimeout(300);
            $process->run();

            if (! $process->isSuccessful() || ! is_file($target)) {
                throw new \RuntimeException('No se pudo generar el respaldo con mysqldump: ' . $process->getErrorOutput());
            }

            return $relative;
        }

        throw new \RuntimeException('El motor de base de datos no tiene respaldo automático configurado.');
    }

    public function files(): array
    {
        $disk = Storage::disk('local');

        return collect($disk->files($this->directory()))
            ->map(fn (string $path) => [
                'name' => basename($path),
                'path' => $path,
                'size' => $disk->size($path),
                'modified' => $disk->lastModified($path),
            ])
            ->sortByDesc('modified')
            ->values()
            ->all();
    }
}
