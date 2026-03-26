<?php

use App\Models\GeneralSetting;
use App\Services\SriService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('local');
    config(['sri.soap_raw_path' => 'testing/soap']);
});

class StubAuthorizeSoapClient extends SoapClient
{
    public function __construct(private object $response)
    {
        parent::__construct(null, [
            'location' => 'http://localhost',
            'uri' => 'http://localhost',
        ]);
    }

    public function __call(string $name, array $arguments): mixed
    {
        return $this->response;
    }
}

class TestSriAuthorizeApiClient extends \App\Services\Sri\SriApiClient
{
    public function __construct(private SoapClient $stubClient)
    {
        parent::__construct(
            urls: ['recepcion' => 'http://localhost', 'autorizacion' => 'http://localhost'],
            signaturePath: '',
            signaturePassword: '',
        );
    }

    protected function makeSoapClient(string $wsdl, array $options): SoapClient
    {
        return $this->stubClient;
    }
}

class TestSriAuthorizeService extends SriService
{
    public function __construct(SoapClient $client)
    {
        parent::__construct();
        $this->apiClient = new TestSriAuthorizeApiClient($client);
    }
}

it('attempts authorization when recepcion is recibida', function () {
    GeneralSetting::create([]);

    $service = new SriService;

    $response = [
        'status' => 'RECIBIDA',
        'message' => 'Comprobante recibido correctamente',
    ];

    expect($service->shouldAttemptAuthorization($response))->toBeTrue();
});

it('attempts authorization when recepcion is in processing', function () {
    GeneralSetting::create([]);

    $service = new SriService;

    $response = [
        'status' => 'DEVUELTA',
        'message' => 'CLAVE DE ACCESO EN PROCESAMIENTO - detalle',
    ];

    expect($service->shouldAttemptAuthorization($response))->toBeTrue();
});

it('does not attempt authorization for other devuelta messages', function () {
    GeneralSetting::create([]);

    $service = new SriService;

    $response = [
        'status' => 'DEVUELTA',
        'message' => 'ARCHIVO NO CUMPLE ESTRUCTURA XML',
    ];

    expect($service->shouldAttemptAuthorization($response))->toBeFalse();
});

it('parses authorization number and date from authorization response', function () {
    GeneralSetting::create([]);

    $response = (object) [
        'RespuestaAutorizacionComprobante' => (object) [
            'autorizaciones' => (object) [
                'autorizacion' => (object) [
                    'estado' => 'AUTORIZADO',
                    'numeroAutorizacion' => '1234567890',
                    'fechaAutorizacion' => '2026-01-23T12:10:00-05:00',
                    'mensajes' => (object) [
                        'mensaje' => (object) [
                            'mensaje' => 'OK',
                        ],
                    ],
                ],
            ],
        ],
    ];

    $service = new TestSriAuthorizeService(new StubAuthorizeSoapClient($response));

    $result = $service->authorize('2001202601120748180300110020010000000311234567814');

    expect($result['status'])->toBe('AUTORIZADO');
    expect($result['authorization_number'])->toBe('1234567890');
    expect($result['date'])->toBe('2026-01-23T12:10:00-05:00');
    expect($result['message'])->toBe('OK');
});
