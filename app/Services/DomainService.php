<?php

namespace App\Services;

use App\Models\Domain;
use App\Models\Page;
use Illuminate\Support\Str;

class DomainService
{
    public function create(Page $page, string $domainName): Domain
    {
        return Domain::create([
            'user_id' => $page->user_id,
            'page_id' => $page->id,
            'domain' => strtolower(trim($domainName)),
            'status' => 'pending',
            'ssl_status' => 'pending',
            'verification_token' => Str::random(32),
        ]);
    }

    public function verify(Domain $domain): array
    {
        $targetDomain = config('app.domain', 'pagebuilder.test');

        // Check for CNAME record
        $records = @dns_get_record($domain->domain, DNS_CNAME);

        if ($records) {
            foreach ($records as $record) {
                if (isset($record['target']) && $record['target'] === $targetDomain) {
                    $domain->update([
                        'status' => 'verified',
                        'verified_at' => now(),
                    ]);

                    return [
                        'verified' => true,
                        'message' => 'Domain verified successfully',
                    ];
                }
            }
        }

        // Check for TXT verification record
        $txtRecords = @dns_get_record('_pagebuilder.' . $domain->domain, DNS_TXT);

        if ($txtRecords) {
            foreach ($txtRecords as $record) {
                if (isset($record['txt']) && $record['txt'] === $domain->verification_token) {
                    $domain->update([
                        'status' => 'verified',
                        'verified_at' => now(),
                    ]);

                    return [
                        'verified' => true,
                        'message' => 'Domain verified successfully via TXT record',
                    ];
                }
            }
        }

        $domain->update(['status' => 'pending']);

        return [
            'verified' => false,
            'message' => 'Domain verification failed. Please check your DNS settings.',
        ];
    }

    public function checkSsl(Domain $domain): array
    {
        if ($domain->status !== 'verified') {
            return [
                'status' => 'pending',
                'message' => 'Domain must be verified before SSL can be provisioned',
            ];
        }

        // Check if SSL is already active
        $context = stream_context_create([
            'ssl' => [
                'capture_peer_cert' => true,
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);

        $socket = @stream_socket_client(
            "ssl://{$domain->domain}:443",
            $errno,
            $errstr,
            10,
            STREAM_CLIENT_CONNECT,
            $context
        );

        if ($socket) {
            $params = stream_context_get_params($socket);
            if (isset($params['options']['ssl']['peer_certificate'])) {
                $domain->update([
                    'ssl_status' => 'active',
                    'ssl_provisioned_at' => now(),
                ]);

                return [
                    'status' => 'active',
                    'message' => 'SSL certificate is active',
                ];
            }
        }

        // If not active, mark as provisioning (Let's Encrypt would handle this)
        if ($domain->ssl_status === 'pending') {
            $domain->update(['ssl_status' => 'provisioning']);
        }

        return [
            'status' => $domain->ssl_status,
            'message' => 'SSL certificate is being provisioned. This may take a few minutes.',
        ];
    }

    public function findByDomain(string $domainName): ?Domain
    {
        return Domain::where('domain', strtolower($domainName))
            ->where('status', 'verified')
            ->first();
    }
}
