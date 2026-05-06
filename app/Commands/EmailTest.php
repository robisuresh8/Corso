<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Throwable;

/**
 * Verify SMTP / mail settings from .env (Config\Email).
 * Run: php spark email:test you@example.com
 */
class EmailTest extends BaseCommand
{
    protected $group       = 'App';
    protected $name        = 'email:test';
    protected $description = 'Send a test email using email.* settings in .env';
    protected $usage       = 'email:test <recipient@example.com>';

    public function run(array $params): void
    {
        $to = trim((string) ($params[0] ?? ''));
        if ($to === '') {
            $to = (string) env('email.testRecipient', '');
        }

        if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            CLI::error('Provide a valid recipient: php spark email:test your@email.com');
            CLI::write('Optional: set email.testRecipient in .env for a default address.');

            return;
        }

        $config = config('Email');

        if ($config->fromEmail === '' || !filter_var($config->fromEmail, FILTER_VALIDATE_EMAIL)) {
            CLI::error('Set email.fromEmail in .env to a valid sender address.');

            return;
        }

        if ($config->protocol === 'smtp') {
            if ($config->SMTPHost === '' || str_contains($config->SMTPHost, 'example.com')) {
                CLI::error('Replace email.SMTPHost in .env with your real SMTP host (e.g. smtp.gmail.com).');

                return;
            }
            if ($config->SMTPUser === '' || str_contains($config->SMTPUser, 'your_smtp')) {
                CLI::error('Set email.SMTPUser (and email.SMTPPass) in .env to your mailbox credentials.');

                return;
            }
        }

        CLI::write('=== Email test ===', 'green');
        CLI::write('From: ' . $config->fromEmail . ' (' . ($config->fromName !== '' ? $config->fromName : 'Corso') . ')');
        CLI::write('To:   ' . $to);
        if ($config->protocol === 'smtp') {
            CLI::write('SMTP: ' . $config->SMTPHost . ':' . $config->SMTPPort . ' crypto=' . ($config->SMTPCrypto !== '' ? $config->SMTPCrypto : '(none)') . ' user=' . $config->SMTPUser);
        } else {
            CLI::write('Protocol: ' . $config->protocol);
        }
        CLI::newLine();

        $email = \Config\Services::email();
        $email->setFrom($config->fromEmail, $config->fromName !== '' ? $config->fromName : 'Corso E-Learning');
        $email->setTo($to);
        $email->setSubject('Corso — test email');
        $email->setMailType('html');
        $email->setMessage('<p>If you see this message, outgoing mail is configured correctly.</p>');
        $email->setAltMessage("If you see this message, outgoing mail is configured correctly.\n");

        try {
            if ($email->send()) {
                CLI::write('Sent successfully. Check inbox and spam folder.', 'green');
            } else {
                CLI::error('Send returned false. Debug output:');
                CLI::write($email->printDebugger(['headers', 'subject', 'body']));
            }
        } catch (Throwable $e) {
            CLI::error('Exception: ' . $e->getMessage());
            CLI::newLine();
            CLI::write('Common fixes:', 'yellow');
            CLI::write('  • Gmail: use an App Password, not your normal password.');
            CLI::write('  • Gmail: email.fromEmail should match the Google account you use for SMTP.');
            CLI::write('  • Port 465: try email.SMTPPort = 465 and email.SMTPCrypto = ssl');
            CLI::write('  • Port 587: use email.SMTPPort = 587 and email.SMTPCrypto = tls');
            CLI::write('  • Increase email.SMTPTimeout in .env if the server is slow.');
        }
    }
}
