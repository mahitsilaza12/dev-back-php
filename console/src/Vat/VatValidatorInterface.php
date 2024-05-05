<?php
declare(strict_types=1);

namespace App\Vat;

interface VatValidatorInterface
{
    /**
     * @param string $vatNumber
     *
     * @return bool
     */
    public function validate(string $vatNumber): bool;
}
