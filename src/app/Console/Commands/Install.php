<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Question\Question;

class Install extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mailcare:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simplify installation process';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->welcome();

        $this->generateAppKey();

        $credentials = $this->requestDatabaseCredentials();

        $this->migrateDatabase($credentials);

        $this->clearCache();

        $this->goodbye();

        return 0;
    }

    protected function welcome()
    {
        $this->info('>> Welcome to the MailCare installation process! <<');
    }

    protected function generateAppKey()
    {
        if (empty(config('app.key'))) {
            $this->call('key:generate');

            $this->line('~ Secret key properly generated.');
        }
    }

    protected function requestDatabaseCredentials()
    {
        $credentials = [
            'DB_DATABASE' => $this->ask('Database name'),
            'DB_PORT' => $this->ask('Database port', 3306),
            'DB_USERNAME' => $this->ask('Database user'),
            'DB_PASSWORD' => $this->askHiddenWithDefault('Database password (leave blank for no password)'),
        ];

        $this->updateEnvironmentFile($credentials);

        $this->line('~ .env file successfully updated with credentials.');

        return $credentials;
    }

    protected function migrateDatabase($credentials)
    {
        if ($this->confirm('Do you want to migrate the database?', false)) {
            $this->migrateDatabaseWithFreshCredentials($credentials);

            $this->line('~ Database successfully migrated.');
        }
    }

    protected function clearCache()
    {
        $this->call('cache:clear');

        $this->line('~ Cache successfully cleared.');
    }

    protected function goodbye()
    {
        $this->info('>> The installation process is complete. Enjoy your new disposable email address service! <<');
    }

    protected function updateEnvironmentFile($updatedValues)
    {
        $envFile = $this->laravel->environmentFilePath();
        foreach ($updatedValues as $key => $value) {
            file_put_contents($envFile, preg_replace(
                "/{$key}=(.*)/",
                "{$key}={$value}",
                file_get_contents($envFile)
            ));
        }
    }

    protected function askHiddenWithDefault($question, $fallback = true)
    {
        $question = new Question($question, 'null');
        $question->setHidden(true)->setHiddenFallback($fallback);

        return $this->output->askQuestion($question);
    }

    protected function migrateDatabaseWithFreshCredentials($credentials)
    {
        foreach ($credentials as $key => $value) {
            $configKey = strtolower(str_replace('DB_', '', $key));
            if ($configKey === 'password' && $value == 'null') {
                config(["database.connections.mysql.{$configKey}" => '']);
                continue;
            }
            config(["database.connections.mysql.{$configKey}" => $value]);
        }
        $this->call('migrate');
    }
}
