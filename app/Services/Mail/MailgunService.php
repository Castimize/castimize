<?php

declare(strict_types=1);

namespace App\Services\Mail;

use Mailgun\Mailgun;

class MailgunService
{
    private Mailgun $mailgun;

    public function __construct()
    {
        $this->mailgun = Mailgun::create(config('services.mailgun.secret'));
    }

    public function send(string $to, string $subject, array $parameters = []): void
    {
        $from = sprintf('%s <%s>', config('mail.from.name'), config('mail.from.address'));
        if (app()->environment() !== 'production') {
            $from = sprintf('%s <%s>', config('services.mailgun.sandbox.name'), config('services.mailgun.sandbox.address'));
            $to = sprintf('%s <%s>', 'Oscar Louis Knoeff', 'info@castimize.com');
        }

        $params = [
            'from' => $from,
            'to' => $to,
            'subject' => $subject,
        ];

        $params = array_merge($params, $parameters);

        $this->mailgun->messages()->send(
            domain: config('services.mailgun.domain'),
            params: $params,
        );
    }
}
