<?php
/**
 * Mmd Vendor model build request for ATOL service.
 *
 * @author    Dmytro Kohut <dmkoh@smile.fr>
 * @copyright 2020 Smile
 */

declare(strict_types=1);

namespace Mmd\Atol\Model;

use Exception;
use Magento\Sales\Model\AbstractModel;
use Mmd\Atol\Api\PaymentInterface;
use Mmd\Atol\Api\RequestInterface;
use Mmd\Atol\Api\VendorInterface;
use Mmd\Atol\Helper\Data as AtolHelper;

/**
 * Class Vendor
 *
 * @package Mmd\Atol\Model
 */
class Vendor implements VendorInterface
{
    /**
     * @var Discount
     */
    protected $atolDiscount;

    /**
     * @var RequestFactory
     */
    protected $requestFactory;

    /**
     * @var PaymentFactory
     */
    protected $paymentFactory;

    /**
     * @var AtolHelper
     */
    protected $atolHelper;

    /**
     * @var ItemsCalculator
     */
    protected $itemsCalculator;

    /**
     * Vendor constructor.
     *
     * @param Discount $atolDiscount
     * @param AtolHelper $atolHelper
     * @param RequestFactory $requestFactory
     * @param PaymentFactory $paymentFactory
     * @param ItemsCalculator $itemsCalculator
     */
    public function __construct(
        Discount $atolDiscount,
        RequestFactory $requestFactory,
        PaymentFactory $paymentFactory,
        AtolHelper $atolHelper,
        ItemsCalculator $itemsCalculator
    ) {
        $this->atolHelper = $atolHelper;
        $this->atolDiscount = $atolDiscount;
        $this->requestFactory = $requestFactory;
        $this->paymentFactory = $paymentFactory;
        $this->itemsCalculator = $itemsCalculator;
    }

    /**
     * Build request.
     *
     * @param AbstractModel $salesEntity
     * @param string|null $paymentMethod
     * @param string|null $shippingPaymentObject
     * @param array $receiptData
     *
     * @throws Exception
     *
     * @return RequestInterface
     */
    public function buildRequest(
        AbstractModel $salesEntity,
        ?string $paymentMethod = null,
        ?string $shippingPaymentObject = null,
        array $receiptData = []
    ): RequestInterface {
        /** @var RequestInterface $request */
        $request = $this->requestFactory->create();
        switch ($salesEntity->getEntityType()) {
            case 'invoice':
                $request->setOperationType(RequestInterface::SELL_OPERATION_TYPE);
                break;
            case 'creditmemo':
                $request->setOperationType(RequestInterface::REFUND_OPERATION_TYPE);
                break;
        }

        $order = $salesEntity->getOrder() ?? $salesEntity;

        $shippingTax = $this->atolHelper->getConfig(AtolHelper::SHIPPING_TAX);
        $taxValue = $this->atolHelper->getConfig(AtolHelper::TAX_OPTIONS);

        if (!$this->atolHelper->getConfig(AtolHelper::DEFAULT_SHIPPING_NAME)) {
            $order->setShippingDescription(
                $this->atolHelper->getConfig(AtolHelper::CUSTOM_SHIPPING_NAME)
            );
        }

        $recalculatedReceiptData = $receiptData
            ?: $this->atolDiscount->getRecalculated($salesEntity, $taxValue, $shippingTax);

        $items = $this->itemsCalculator->calculateItems(
            $recalculatedReceiptData,
            $salesEntity,
            $paymentMethod,
            $shippingPaymentObject
        );

        $telephone = $order->getBillingAddress()
            ? (string) $order->getBillingAddress()->getTelephone()
            : '';

        $request
            ->setExternalId($this->atolHelper->generateExternalId($salesEntity))
            ->setSalesEntityId((int) $salesEntity->getEntityId())
            ->setEmail($order->getCustomerEmail())
            ->setPhone($telephone)
            ->setCompanyEmail($this->atolHelper->getGlobalConfig(AtolHelper::STORE_EMAIL))
            ->setPaymentAddress($this->atolHelper->getConfig(AtolHelper::PAYMENT_ADDRESS))
            ->setSno($this->atolHelper->getConfig(AtolHelper::ATOL_SNO))
            ->setInn($this->atolHelper->getConfig(AtolHelper::ATOL_INN))
            ->setItems($items);

        $this->addPayment($salesEntity, $request);

        return $request;
    }

    /**
     * Add payment to request.
     *
     * @param AbstractModel $salesEntity
     * @param RequestInterface $request
     *
     * @return void
     */
    protected function addPayment(AbstractModel $salesEntity, RequestInterface $request): void
    {
        //Basic payment
        if ($salesEntity->getGrandTotal() > 0.00) {
            $request
                ->addPayment(
                    $this->paymentFactory->create()
                        ->setType(PaymentInterface::PAYMENT_TYPE_BASIC)
                        ->setSum((float) round($salesEntity->getGrandTotal(), 2))
                );
        }

        //"GiftCard applied" payment
        if ($this->atolHelper->isGiftCardApplied($salesEntity)) {
            $request
                ->addPayment(
                    $this->paymentFactory->create()
                        ->setType(PaymentInterface::PAYMENT_TYPE_AVANS)
                        ->setSum((float) round($salesEntity->getGiftCardsAmount(), 2))
                );
        }
    }
}
