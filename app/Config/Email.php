<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Email extends BaseConfig
{
    public string $fromEmail  = '';
    public string $fromName   = '';
    public string $recipients = '';
    public string $userAgent  = 'CodeIgniter';
    public string $protocol   = 'mail';
    public string $mailPath   = '/usr/sbin/sendmail';
    public string $SMTPHost   = '';
    public string $SMTPAuthMethod = 'login';
    public string $SMTPUser   = '';
    public string $SMTPPass   = '';
    public int    $SMTPPort   = 25;
    public int    $SMTPTimeout = 5;
    public bool   $SMTPKeepAlive = false;
    public string $SMTPCrypto = 'tls';
    public bool   $wordWrap   = true;
    public int    $wrapChars  = 76;
    public string $mailType   = 'text';
    public string $charset    = 'UTF-8';
    public bool   $validate   = false;
    public int    $priority   = 3;
    public string $CRLF       = "\r\n";
    public string $newline    = "\r\n";
    public bool   $BCCBatchMode = false;
    public int    $BCCBatchSize = 200;
    public bool   $DSN        = false;

    public function __construct()
{
    parent::__construct();

    $this->fromEmail = getenv('EMAIL_FROM_EMAIL') ?: $this->fromEmail;
    $this->fromName  = getenv('EMAIL_FROM_NAME') ?: $this->fromName;
    $this->protocol   = getenv('EMAIL_PROTOCOL') ?: $this->protocol;
    $this->SMTPHost   = getenv('EMAIL_SMTP_HOST') ?: $this->SMTPHost;
    $this->SMTPUser   = getenv('EMAIL_SMTP_USER') ?: $this->SMTPUser;
    $this->SMTPPass   = getenv('EMAIL_SMTP_PASS') ?: $this->SMTPPass;
    $this->SMTPCrypto = getenv('EMAIL_SMTP_CRYPTO') ?: $this->SMTPCrypto;
    $this->SMTPPort   = getenv('EMAIL_SMTP_PORT') ?: $this->SMTPPort;
}
}