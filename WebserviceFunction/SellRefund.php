<?php
/**
 * Sell refund creditmemo.
 *
 * @author    Dmytro Kohut <dmkoh@smile.fr>
 * @copyright 2020 Smile
 */

declare(strict_types=1);

namespace Mmd\Atol\WebserviceFunction;

/**
 * Class SellRefund
 *
 * @package Mmd\Atol\WebserviceFunction
 */
class SellRefund extends AbstractFunction
{
    /**
     * Additional url part for sell refund creditmemo.
     *
     * @return string
     */
    public function getAdditionnalUrlPart(): string
    {
        return $this->object->getData('groupCode') . '/sell_refund?token=' . $this->object->getData('token');
    }
}
