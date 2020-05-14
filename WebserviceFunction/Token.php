<?php
/**
 * Get Atol token.
 *
 * @author    Dmytro Kohut <dmkoh@smile.fr>
 * @copyright 2020 Smile
 */

declare(strict_types=1);

namespace Mmd\Atol\WebserviceFunction;

/**
 * Class Create
 *
 * @package Mmd\Atol\WebserviceFunction
 */
class Token extends AbstractFunction
{
    /**
     * Additional url part for create order.
     *
     * @return string
     */
    public function getAdditionnalUrlPart(): string
    {
        return 'getToken';
    }
}
