<?php

declare(strict_types=1);

namespace App\Services\Admin;

use Illuminate\Support\Facades\Log;
use Normalizer;

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

    /**
     * Countries where local script (CJK etc.) is accepted natively by Shippo/UPS.
     * Transliteration of these scripts produces incorrect romanization and breaks address validation.
     */
    private const SKIP_TRANSLITERATION_COUNTRIES = ['JP', 'CN', 'TW', 'HK', 'KR'];

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
        $country = strtoupper($address['country'] ?? '');
        if (in_array($country, self::SKIP_TRANSLITERATION_COUNTRIES, true)) {
            return $address;
        }

        $modified = false;
        $originalFields = [];

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
                    $truncated = mb_substr($address[$field], 0, $maxLength);

                    Log::warning('Address field truncated to max length for Shippo', [
                        'field' => $field,
                        'original' => $address[$field],
                        'truncated' => $truncated,
                        'max_length' => $maxLength,
                    ]);

                    $address[$field] = $truncated;
                }
            }
        }

        if ($modified) {
            Log::info('Address transliterated for Shippo', [
                'original_fields' => $originalFields,
            ]);
        }

        return $address;
    }

    // Characters that do not decompose via Unicode NFD normalization.
    private const SPECIAL_CHAR_MAP = [
        'ß' => 'ss', 'ẞ' => 'SS',
        'ø' => 'o', 'Ø' => 'O',
        'ł' => 'l', 'Ł' => 'L',
        'đ' => 'd', 'Đ' => 'D',
        'æ' => 'ae', 'Æ' => 'AE',
        'œ' => 'oe', 'Œ' => 'OE',
        'ŀ' => 'l', 'Ŀ' => 'L',
        'ŧ' => 't', 'Ŧ' => 'T',
        'ħ' => 'h', 'Ħ' => 'H',
        'ı' => 'i',
    ];

    public function transliterateString(string $input): string
    {
        if ($input === '' || $this->isAscii($input)) {
            return $input;
        }

        // Step 1: replace characters that NFD normalization cannot decompose
        $result = str_replace(
            array_keys(self::SPECIAL_CHAR_MAP),
            array_values(self::SPECIAL_CHAR_MAP),
            $input
        );

        // Step 2: NFD decomposition + strip combining diacritical marks (ö→o, é→e, etc.)
        if (class_exists('Normalizer')) {
            $normalized = Normalizer::normalize($result, Normalizer::FORM_KD);
            if ($normalized !== false) {
                $result = (string) preg_replace('/\p{Mn}/u', '', $normalized);
            }
        }

        // Step 3: ICU transliterator for remaining non-Latin scripts (CJK, Cyrillic, etc.)
        if (! $this->isAscii($result)) {
            $transliterator = transliterator_create(
                'Katakana-Latin; Hiragana-Latin; Han-Latin; Any-Latin; Latin-ASCII; [:Nonspacing Mark:] Remove'
            );

            if ($transliterator === null) {
                $transliterator = transliterator_create('Any-Latin; Latin-ASCII');
            }

            if ($transliterator !== null) {
                $result = transliterator_transliterate($transliterator, $result) ?: $result;
            }
        }

        // Step 4: strip any remaining non-printable-ASCII bytes as absolute safety net
        $result = preg_replace('/[^\x20-\x7E]/', '', $result) ?? '';
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
