<?php
/**
 * Payments list.
 *
 * @author Dmytro Kohut <dmkoh@smile.fr>
 * @copyright 2020 Smile
 */

declare(strict_types=1);

namespace Mmd\Atol\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Payment\Helper\Data;

/**
 * Class Payments
 *
 * @package Mmd\Atol\Model\Source
 */
class Payments implements OptionSourceInterface
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var Data
     */
    protected $paymentHelper;

    /**
     * Constructor.
     *
     * @param Data $paymentHelper
     */
    public function __construct(
        Data $paymentHelper
    ) {
        $this->paymentHelper = $paymentHelper;
    }

    /**
     * Get options.
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        if ($this->options === null) {
            $this->options = $this->paymentHelper->getPaymentMethodList(true, true, false);
        }

        return $this->options;
    }
}
