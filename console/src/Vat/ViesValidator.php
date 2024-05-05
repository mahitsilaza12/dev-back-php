<?php
declare(strict_types=1);

namespace App\Vat;

use DragonBe\Vies\Vies;

class ViesValidator implements VatValidatorInterface
{
    /**
     * @var Vies
     */
    private $viesService;

    /**
     * @param Vies $viesService
     */
    public function __construct(Vies $viesService)
    {
        $this->viesService = $viesService;
    }

    /**
     * @param string $vatNumber
     *
     * @return bool
     */
    public function validate(string $vatNumber): bool
    {
        $countryCode = substr($vatNumber, 0, 2);
        $number = substr($vatNumber, 2);

        $response = $this->viesService->validateVat($countryCode, $number);

        return $response->isValid();
    }
}
