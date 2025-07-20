<?php

declare(strict_types=1);

namespace App\Services\Admin;

class HelperService
{
    /**
     * Generate sku from name and id
     *
     * @param string $name
     * @param int $id
     * @return string
     */
    public function generateSku(string $name, int $id) : string
    {
        $words = explode(' ', $name);
        if (count($words) >= 2) {
            return mb_strtoupper(
                    mb_substr($words[0], 0, 1, 'UTF-8') .
                    mb_substr(end($words), 0, 1, 'UTF-8'),
                    'UTF-8') . '-' . $id;
        }
        return $this->makeInitialsFromSingleWord($name, $id);
    }

    /**
     * Make initials from a word with no spaces
     *
     * @param string $name
     * @param int $id
     * @return string
     */
    protected function makeInitialsFromSingleWord(string $name, int $id) : string
    {
        preg_match_all('#([A-Z]+)#', $name, $capitals);
        if (count($capitals[1]) >= 2) {
            return mb_substr(implode('', $capitals[1]), 0, 2, 'UTF-8') . '-' . $id;
        }
        return mb_strtoupper(mb_substr($name, 0, 2, 'UTF-8'), 'UTF-8') . '-' . $id;
    }
}
