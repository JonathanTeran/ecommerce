<?php

namespace App\Services\Sri {
    function shell_exec(string $command): ?string
    {
        return \SriShellExecStub::handle($command);
    }
}

namespace {
    use App\Services\Sri\SriApiClient;

    final class SriShellExecStub
    {
        public static array $calls = [];

        public static array $responses = [];

        public static function fake(array $responses): void
        {
            self::$calls = [];
            self::$responses = $responses;
        }

        public static function handle(string $command): ?string
        {
            self::$calls[] = $command;

            foreach (self::$responses as $needle => $response) {
                if (str_contains($command, $needle)) {
                    return $response;
                }
            }

            return null;
        }
    }

    function readPkcs12ViaCli(string $path, string $password): array
    {
        $service = (new \ReflectionClass(SriApiClient::class))->newInstanceWithoutConstructor();
        $method = new \ReflectionMethod(SriApiClient::class, 'readPkcs12ViaCli');
        $method->setAccessible(true);

        return $method->invoke($service, $path, $password);
    }

    it('uses legacy openssl output when available', function () {
        $certificate = <<<'CERT'
-----BEGIN CERTIFICATE-----
CERTDATA
-----END CERTIFICATE-----
CERT;

        $privateKey = <<<'KEY'
-----BEGIN PRIVATE KEY-----
KEYDATA
-----END PRIVATE KEY-----
KEY;

        $payload = "Bag Attributes\n{$certificate}\n{$privateKey}\n";

        SriShellExecStub::fake(['-legacy' => $payload]);

        $result = readPkcs12ViaCli('/tmp/firma.p12', 'secret');

        expect($result['cert'])->toBe($certificate);
        expect($result['pkey'])->toBe($privateKey);
        expect(SriShellExecStub::$calls)->toHaveCount(1);
        expect(SriShellExecStub::$calls[0])->toContain('-legacy');
    });

    it('falls back to non-legacy openssl output when legacy output is empty', function () {
        $certificate = <<<'CERT'
-----BEGIN CERTIFICATE-----
CERTDATA
-----END CERTIFICATE-----
CERT;

        $privateKey = <<<'KEY'
-----BEGIN PRIVATE KEY-----
KEYDATA
-----END PRIVATE KEY-----
KEY;

        $payload = "Bag Attributes\n{$certificate}\n{$privateKey}\n";

        SriShellExecStub::fake([
            '-legacy' => '',
            'pkcs12 -in' => $payload,
        ]);

        $result = readPkcs12ViaCli('/tmp/firma.p12', 'secret');

        expect($result['cert'])->toBe($certificate);
        expect($result['pkey'])->toBe($privateKey);
        expect(SriShellExecStub::$calls)->toHaveCount(2);
        expect(SriShellExecStub::$calls[1])->not->toContain('-legacy');
    });
}
