<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ConfigurePostfix extends Command
{
    protected $signature = 'mailcare:configure-postfix
                            {configDirectory? : path to your Postfix\'s configuration}
                            {domain? : email addresses\' domain}
                            {user=forge}';

    protected $description = 'Configure Postfix with your domain.';

    public function handle()
    {
        $confirmation = $this->confirm('This script can be dangerous for your server, do you want to continue?');

        if ($this->input->isInteractive() and ! $confirmation) {
            $this->error('Aborting!');
            return;
        }

        $configDirectory = $this->argument('configDirectory') ?? '/etc/postfix';
        $newDomain = $this->argument('domain') ?? basename(base_path());

        $this->configureMainConfigFile($configDirectory, $newDomain);
        $this->configureMasterConfigFile($configDirectory);
        $this->reloadPostfix();

        return 0;
    }

    public function configureMainConfigFile($configDirectory, $newDomain)
    {
        $mainConfigPath = Str::finish($configDirectory, '/') . 'main.cf';
        $this->info("Configure $mainConfigPath");

        if (! file_exists($mainConfigPath)) {
            $this->error("The config file does not exists.");
            return;
        }

        $newLine = "myhostname = $newDomain";
        $previousLine = $this->editLine($mainConfigPath, '/^myhostname = (.*)$/m', $newLine);

        if (! $previousLine) {
            $this->error("Didn't find the key 'myhostname' in your configuration file.");
            return;
        }

        $this->line("1. replaced 'myhostname' key.");
        $this->line("from    $previousLine");
        $this->line("to      $newLine");

        $smtpdRecipientRestrictions = "smtpd_recipient_restrictions = permit_mynetworks, reject_unauth_destination";
        $localRecipients = "local_recipient_maps =";
        file_put_contents($mainConfigPath, "\n$smtpdRecipientRestrictions\n", FILE_APPEND);
        file_put_contents($mainConfigPath, "$localRecipients\n", FILE_APPEND);

        $this->line("2. added some more configuration.");
        $this->line("        $smtpdRecipientRestrictions");
        $this->line("        $localRecipients");
    }

    public function configureMasterConfigFile($configDirectory)
    {
        $masterConfigPath = Str::finish($configDirectory, '/') . 'master.cf';
        $this->info("\nConfigure $masterConfigPath");

        if (! file_exists($masterConfigPath)) {
            $this->error("The config file does not exists..");
            return;
        }

        $this->addWebhookDefinition($masterConfigPath);
        $this->activateWebhook($masterConfigPath);
    }

    private function addWebhookDefinition($masterConfigPath)
    {
        $artisan = base_path('artisan');
        $command = "php $artisan mailcare:email-receive";

        $user = $this->argument('user');
        $newLine = "mailcare unix - n n - - pipe flags=F user=$user argv={$command}";
        file_put_contents($masterConfigPath, "\n$newLine\n", FILE_APPEND);

        $this->line("1. hook created.");
        $this->line("        $newLine");
    }

    private function activateWebhook($masterConfigPath)
    {
        $newLine = "smtp inet n - - - - smtpd -o content_filter=mailcare:dummy";
        $previousLine = $this->editLine($masterConfigPath, '/^smtp(\s+)inet(.*)$/m', $newLine);

        if (! $previousLine) {
            $this->error("Didn't find the key 'smtp inet' in your configuration file..");
            return;
        }

        $this->line("2. activated in 'smtp inet' definition");
        $this->line("from    $previousLine");
        $this->line("to      $newLine");
    }

    public function reloadPostfix()
    {
        $this->info("\nReload postfix");

        if ($this->commandExists('systemctl')) {
            $this->line("1. using 'systemctl reload postfix'");
            shell_exec('systemctl reload postfix');
        } elseif ($this->commandExists('service')) {
            $this->line("1. using 'service postfix reload'");
            shell_exec('service postfix reload');
        } else {
            $this->error("Neither systemctl nor service found. Ignoring Postfix reload.");
        }
    }

    private function editLine($filePath, $regexp, $newLine)
    {
        $previousContent = file_get_contents($filePath);

        $matches = [];
        $found = preg_match($regexp, $previousContent, $matches);

        if (! $found) {
            return null;
        }

        $previousLine = Arr::first($matches);

        $newContent = str_replace($previousLine, $newLine, $previousContent);
        file_put_contents($filePath, $newContent);

        return $previousLine;
    }

    private function commandExists($command)
    {
        $returnCode = shell_exec("command -v $command >/dev/null 2>&1; printf $?");
        return $returnCode === '0';
    }
}
