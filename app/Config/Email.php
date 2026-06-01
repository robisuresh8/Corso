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

    $this->fromEmail  = 'robisuresh3@gmail.com';
    $this->fromName   = 'Corso E-Learning';
    $this->protocol   = 'smtp';
    $this->SMTPHost   = 'smtp-relay.brevo.com';
    $this->SMTPUser   = 'a73dd7001asmtp-brevo.com';
    $this->SMTPPass   = 'xsmtpsib-7e10907e6b8eac0848bee225dbcaae99c1bd2c3bcf7f7f65e5debe94e1fe4927-dNdP9HhlVoyoth6h';
    $this->SMTPCrypto = 'tls';
    $this->SMTPPort   = 587;
}
}