<?php

declare(strict_types=1);

namespace App\Services\Admin;

use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class AddressTransliterationService
{
    private const TRANSLITERABLE_FIELDS = [
        'name',
        'company',
        'address_line1',
        'address_line2',
        'city',
        'state',
    ];

    private const FIELD_MAX_LENGTHS = [
        'name' => 35,
        'company' => 35,
        'address_line1' => 35,
        'address_line2' => 35,
        'city' => 30,
        'state' => 35,
    ];

    public function transliterateAddress(array $address): array
    {
        $modified = false;
        $originalFields = [];
        $fieldLengthErrors = [];

        foreach (self::TRANSLITERABLE_FIELDS as $field) {
            if (isset($address[$field]) && is_string($address[$field])) {
                $original = $address[$field];
                $address[$field] = $this->transliterateString($address[$field]);

                if ($original !== $address[$field]) {
                    $modified = true;
                    $originalFields[$field] = $original;
                }

                $maxLength = self::FIELD_MAX_LENGTHS[$field] ?? null;
                if ($maxLength !== null && mb_strlen($address[$field]) > $maxLength) {
                    $fieldLengthErrors[$field] = [
                        'value' => $address[$field],
                        'length' => mb_strlen($address[$field]),
                        'max_length' => $maxLength,
                    ];
                }
            }
        }

        if (! empty($fieldLengthErrors)) {
            $errorMessages = [];
            foreach ($fieldLengthErrors as $field => $error) {
                $errorMessages[] = sprintf(
                    '%s exceeds maximum length of %d characters (current: %d)',
                    $field,
                    $error['max_length'],
                    $error['length']
                );
            }

            throw new InvalidArgumentException(
                'Address validation failed: '.implode('; ', $errorMessages)
            );
        }

        if ($modified) {
            Log::info('Address transliterated for Shippo', [
                'original_fields' => $originalFields,
            ]);
        }

        return $address;
    }

    public function transliterateString(string $input): string
    {
        if ($input === '' || $this->isAscii($input)) {
            return $input;
        }

        $transliterator = transliterator_create(
            'Katakana-Latin; Hiragana-Latin; Han-Latin; Any-Latin; Latin-ASCII; [:Nonspacing Mark:] Remove'
        );

        if ($transliterator === null) {
            $transliterator = transliterator_create('Any-Latin; Latin-ASCII');
        }

        $result = $transliterator !== null
            ? transliterator_transliterate($transliterator, $input)
            : $input;

        // preg_replace can return null on error, use input as fallback
        $result = preg_replace('/[^\x20-\x7E]/', '', $result ?: $input) ?? $input;
        $result = preg_replace('/\s+/', ' ', $result) ?? $result;

        return trim($result);
    }

    public function isAscii(string $input): bool
    {
        return mb_check_encoding($input, 'ASCII');
    }

    public function containsJapanese(string $input): bool
    {
        return (bool) preg_match('/[\x{3040}-\x{309F}\x{30A0}-\x{30FF}\x{4E00}-\x{9FAF}]/u', $input);
    }
}
