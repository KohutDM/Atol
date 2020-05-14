<?php
/**
 * Vendor interface build request for ATOL service.
 *
 * @category  Mmd
 * @package   Mmd_Atol
 * @author    Dmytro Kohut <dmkoh@smile.fr>
 * @copyright 2020 Smile
 */

declare(strict_types=1);

namespace Mmd\Atol\Api;

use Magento\Sales\Model\AbstractModel;

/**
 * Interface VendorInterface
 *
 * @package Mmd\Atol\Api\Data
 */
interface VendorInterface
{
    /**
     * Build request.
     *
     * @param AbstractModel $salesEntity
     * @param string|null $paymentMethod
     * @param string|null $shippingPaymentObject
     * @param array $receiptData
     *
     * @return RequestInterface
     */
    public function buildRequest(
        AbstractModel $salesEntity,
        ?string $paymentMethod = null,
        ?string $shippingPaymentObject = null,
        array $receiptData = []
    ): RequestInterface;
}
