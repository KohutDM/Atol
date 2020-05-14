<?php
/**
 * Mmd Items Calculator model.
 *
 * @author    Dmytro Kohut <dmkoh@smile.fr>
 * @copyright 2020 Smile
 */

declare(strict_types=1);

namespace Mmd\Atol\Model;

use Exception;
use Magento\Sales\Model\AbstractModel;
use Mmd\Atol\Api\ItemInterface;
use Mmd\Atol\Exception\NonFatalErrorException;
use Mmd\Atol\Helper\Data as atolHelper;

/**
 * Class ItemsCalculator
 *
 * @package Mmd\Atol\Model
 */
class ItemsCalculator
{
    /**
     * #@+
     * Items calculator const.
     */
    const TAX_SUM = 'tax_sum';
    const CUSTOM_DECLARATION = 'custom_declaration';
    const COUNTRY_CODE = 'country_code';
    /**#@-*/

    /**
     * @var atolHelper
     */
    protected $atolHelper;

    /**
     * @var ItemFactory
     */
    protected $itemFactory;

    /**
     * ItemsCalculator constructor.
     *
     * @param atolHelper $atolHelper
     * @param ItemFactory $itemFactory
     */
    public function __construct(
        atolHelper $atolHelper,
        ItemFactory $itemFactory
    ) {
        $this->atolHelper = $atolHelper;
        $this->itemFactory = $itemFactory;
    }

    /**
     * Calculate items.
     *
     * @param array $recalculatedReceiptData
     * @param AbstractModel $salesEntity
     * @param string|null $paymentMethod
     * @param string|null $shippingPaymentObject
     *
     * @throws Exception
     *
     * @return array
     */
    public function calculateItems(
        array $recalculatedReceiptData,
        AbstractModel $salesEntity,
        ?string $paymentMethod,
        ?string $shippingPaymentObject
    ): array {
        $items = [];
        foreach ($recalculatedReceiptData[Discount::ITEMS] as $key => $itemData) {
            //For orders without Shipping (Virtual products)
            if ($key == Discount::SHIPPING && $itemData[Discount::NAME] === null) {
                continue;
            }

            $this->validateItemArray($itemData);

            //How to handle GiftCards - see Atol API documentation
            $itemPaymentMethod = $this->atolHelper->isGiftCard($salesEntity, $itemData[Discount::NAME])
                ? Item::PAYMENT_METHOD_ADVANCE
                : ($paymentMethod ?: Item::PAYMENT_METHOD_FULL_PAYMENT);
            $itemPaymentObject = $this->atolHelper->isGiftCard($salesEntity, $itemData[Discount::NAME])
                ? Item::PAYMENT_OBJECT_PAYMENT
                : ($key == Discount::SHIPPING && $shippingPaymentObject
                    ? $shippingPaymentObject
                    : Item::PAYMENT_OBJECT_BASIC);

            $items[] = $this->buildItem($itemData, $itemPaymentMethod, $itemPaymentObject);
        }

        return $items;
    }

    /**
     * Built item.
     *
     * @param array $itemData
     * @param string $itemPaymentMethod
     * @param string $itemPaymentObject
     *
     * @return ItemInterface
     */
    protected function buildItem(array $itemData, string $itemPaymentMethod, string $itemPaymentObject): ItemInterface
    {
        /** @var ItemInterface $item */
        $item = $this->itemFactory->create();
        $item
            ->setName($itemData[Discount::NAME])
            ->setPrice($itemData[Discount::PRICE])
            ->setSum($itemData[Discount::SUM])
            ->setQuantity($itemData[Discount::QUANTITY] ?? 1)
            ->setTax($itemData[Discount::TAX])
            ->setPaymentMethod($itemPaymentMethod)
            ->setPaymentObject($itemPaymentObject)
            ->setTaxSum($itemData[self::TAX_SUM] ?? 0.0)
            ->setCustomsDeclaration($itemData[self::CUSTOM_DECLARATION] ?? '')
            ->setCountryCode($itemData[self::COUNTRY_CODE] ?? '');

        return $item;
    }

    /**
     * Validate item array.
     *
     * @param array $item
     *
     * @throws Exception
     *
     * @return void
     */
    protected function validateItemArray(array $item): void
    {
        $reason = false;
        if (!isset($item['name']) || $item['name'] === null || $item['name'] === '') {
            $reason = __('One of items has undefined name.');
        }

        if (!isset($item['tax']) || $item['tax'] === null) {
            $reason = __('Item %1 has undefined tax.', $item['name']);
        }

        if ($reason) {
            throw new NonFatalErrorException(
                __('Can not send data to Atol. Reason: %1', $reason)
            );
        }
    }
}
