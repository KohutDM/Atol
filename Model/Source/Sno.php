<?php
/**
 * Tax system.
 *
 * @author Dmytro Kohut <dmkoh@smile.fr>
 * @copyright 2020 Smile
 */

declare(strict_types=1);

namespace Mmd\Atol\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Sno
 *
 * @package Mmd\Atol\Model\Source
 */
class Sno implements OptionSourceInterface
{
    /**
     * #@+
     * SNO const.
     */
    const RECEIPT_SNO_OSN = 'osn';
    const RECEIPT_SNO_USN_INCOME = 'usn_income';
    const RECEIPT_SNO_USN_INCOME_OUTCOME = 'usn_income_outcome';
    const RECEIPT_SNO_ENVD = 'envd';
    const RECEIPT_SNO_ESN = 'esn';
    const RECEIPT_SNO_PATENT = 'patent';
    /**#@-*/

    /**
     * Get options.
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => self::RECEIPT_SNO_OSN,
                'label' => __('osn'),
            ],
            [
                'value' => self::RECEIPT_SNO_USN_INCOME,
                'label' => __('usn_income'),
            ],
            [
                'value' => self::RECEIPT_SNO_USN_INCOME_OUTCOME,
                'label' => __('usn_income_outcome'),
            ],
            [
                'value' => self::RECEIPT_SNO_ENVD,
                'label' => __('envd'),
            ],
            [
                'value' => self::RECEIPT_SNO_ESN,
                'label' => __('esn'),
            ],
            [
                'value' => self::RECEIPT_SNO_PATENT,
                'label' => __('patent'),
            ],
        ];
    }
}
