<?php

namespace App\Services;

use DOMDocument;

class SriXmlValidator
{
    /**
     * @return array{valid: bool, message: string, errors: array<int, string>}
     */
    public function validate(string $xml, string $xsdPath): array
    {
        if (! is_file($xsdPath)) {
            return [
                'valid' => false,
                'message' => 'Archivo XSD no encontrado: '.$xsdPath,
                'errors' => [],
            ];
        }

        $previousErrors = libxml_use_internal_errors(true);
        libxml_clear_errors();

        $document = new DOMDocument();
        $loaded = $document->loadXML($xml);

        if (! $loaded) {
            $errors = $this->collectErrors();
            libxml_use_internal_errors($previousErrors);

            return [
                'valid' => false,
                'message' => 'XML invalido antes de validar XSD.',
                'errors' => $errors,
            ];
        }

        $handlerErrors = [];
        $previousHandler = set_error_handler(function (int $severity, string $message) use (&$handlerErrors): bool {
            $handlerErrors[] = trim($message);

            return true;
        });

        $isValid = $document->schemaValidate($xsdPath);

        if ($previousHandler !== null) {
            set_error_handler($previousHandler);
        } else {
            restore_error_handler();
        }

        $errors = array_merge($handlerErrors, $this->collectErrors());
        libxml_use_internal_errors($previousErrors);

        if (! $isValid || $errors !== []) {
            return [
                'valid' => false,
                'message' => 'El XML no cumple el esquema XSD.',
                'errors' => $errors,
            ];
        }

        return [
            'valid' => true,
            'message' => 'XML valido segun XSD.',
            'errors' => [],
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function collectErrors(): array
    {
        $errors = [];

        foreach (libxml_get_errors() as $error) {
            $message = trim($error->message);

            if ($error->line > 0) {
                $message = 'Linea '.$error->line.': '.$message;
            }

            $errors[] = $message;
        }

        libxml_clear_errors();

        return $errors;
    }
}
