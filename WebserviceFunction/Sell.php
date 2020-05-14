<?php
/**
 * Sell invoice.
 *
 * @author    Dmytro Kohut <dmkoh@smile.fr>
 * @copyright 2020 Smile
 */

declare(strict_types=1);

namespace Mmd\Atol\WebserviceFunction;

/**
 * Class Sell
 *
 * @package Mmd\Atol\WebserviceFunction
 */
class Sell extends AbstractFunction
{
    /**
     * Additional url part for sell invoice.
     *
     * @return string
     */
    public function getAdditionnalUrlPart(): string
    {
        return $this->object->getData('groupCode') . '/sell?token=' . $this->object->getData('token');
    }
}
