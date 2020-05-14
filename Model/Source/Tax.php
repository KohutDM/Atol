<?php
/**
 * Tax list.
 *
 * @author Dmytro Kohut <dmkoh@smile.fr>
 * @copyright 2020 Smile
 */

declare(strict_types=1);

namespace Mmd\Atol\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Mmd\Atol\Helper\Data;

/**
 * Class Tax
 *
 * @package Mmd\Atol\Model\Source
 */
class Tax implements OptionSourceInterface
{
    /**
     * #@+
     * Tax const.
     */
    const TAX_NONE = 'none';
    const TAX_VAT0 = 'vat0';
    const TAX_VAT10 = 'vat10';
    const TAX_VAT20 = 'vat20';
    const TAX_VAT110 = 'vat110';
    const TAX_VAT120 = 'vat120';
    /**#@-*/

    /**
     * @var Data
     */
    protected $atolHelper;

    /**
     * Tax constructor.
     *
     * @param Data $atolHelper
     */
    public function __construct(
        Data $atolHelper
    ) {
        $this->atolHelper = $atolHelper;
    }

    /**
     * Get options.
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => self::TAX_NONE,
                'label' => __($this->atolHelper->getConfig(Data::WITHOUT_VAT)),
            ],
            [
                'value' => self::TAX_VAT0,
                'label' => __($this->atolHelper->getConfig(Data::VAT0)),
            ],
            [
                'value' => self::TAX_VAT10,
                'label' => __($this->atolHelper->getConfig(Data::VAT10)),
            ],
            [
                'value' => self::TAX_VAT20,
                'label' => __($this->atolHelper->getConfig(Data::VAT20)),
            ],
            [
                'value' => self::TAX_VAT110,
                'label' => __($this->atolHelper->getConfig(Data::VAT110)),
            ],
            [
                'value' => self::TAX_VAT120,
                'label' => __($this->atolHelper->getConfig(Data::VAT120)),
            ],
        ];
    }

    /**
     * Get all taxes.
     *
     * @return array
     */
    public function getAllTaxes(): array
    {
        return [
            self::TAX_NONE,
            self::TAX_VAT0,
            self::TAX_VAT10,
            self::TAX_VAT20,
            self::TAX_VAT110,
            self::TAX_VAT120,
        ];
    }
}
