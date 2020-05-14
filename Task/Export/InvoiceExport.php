<?php
/**
 * Invoice export task.
 *
 * @author    Dmytro Kohut <dmkoh@smile.fr>
 * @copyright 2020 Smile
 */

declare(strict_types=1);

namespace Mmd\Atol\Task\Export;

use Exception;
use Magento\Framework\DataObject\Factory;
use Magento\Sales\Model\Order\Invoice;
use Mmd\Atol\Api\RequestInterface;
use Mmd\Atol\Api\VendorInterface;
use Mmd\Atol\Helper\Data;
use Mmd\Atol\WebserviceFunction\Sell;
use Mmd\Atol\WebserviceFunction\Token;

/**
 * Class InvoiceExport
 *
 * @package Mmd\Atol\Task\Export
 */
class InvoiceExport extends AbstractExport
{
    /**
     * Invoice export task code.
     */
    const TASK_CODE = 'invoiceExport';

    /**
     * @var Factory
     */
    protected $dataObjectFactory;

    /**
     * @var array
     */
    protected $functions;

    /**
     * @var VendorInterface
     */
    protected $vendor;

    /**
     * @var Data
     */
    protected $atolHelper;

    /**
     * InvoiceExport constructor.
     *
     * @param VendorInterface $vendor
     * @param Sell $sell
     * @param Token $token
     * @param Data $atolHelper
     * @param Factory $dataObjectFactory
     */
    public function __construct(
        VendorInterface $vendor,
        Sell $sell,
        Token $token,
        Data $atolHelper,
        Factory $dataObjectFactory
    ) {
        $this->functions = [
            'sell' => $sell,
            'getToken' => $token
        ];
        $this->vendor = $vendor;
        $this->atolHelper = $atolHelper;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * Run export.
     *
     * @throws Exception
     *
     * @return bool
     */
    protected function execute(): bool
    {
        $this->exportInvoice();

        $this->getInvoice()
            ->setAtolStatus(Data::STATUS_WAIT)
            ->setAtolUuid($this->webservice->getResponse()['uuid'])
            ->save();

        return true;
    }

    /**
     * Form request.
     *
     * @param Invoice $invoice
     *
     * @return RequestInterface;
     */
    protected function formRequest(Invoice $invoice): RequestInterface
    {
        return $this->vendor->buildRequest($invoice);
    }

    /**
     * Export invoice to Atol.
     *
     * @throws Exception
     *
     * @return bool
     */
    protected function exportInvoice(): bool
    {
        $groupCode = $this->atolHelper->getConfig(Data::ATOL_GROUP_CODE);
        $invoice = $this->getInvoice();
        $invoiceDataObject = $this->dataObjectFactory->create();
        $request = $this->formRequest($invoice);
        $responseWithToken = $this->getResponseWithToken() ?? [];
        $invoiceDataObject = $this->getEntityDataObjectExport(
            $invoiceDataObject,
            $request,
            $responseWithToken,
            $groupCode
        );
        $this->callFunction($this->functions['sell'], $invoiceDataObject);

        return true;
    }

    /**
     * Get invoice to export
     *
     * @return Invoice
     */
    protected function getInvoice(): Invoice
    {
        return $this->getObject();
    }

    /**
     * Get Atol response with token.
     *
     * @throws Exception
     *
     * @return array|null
     */
    protected function getResponseWithToken(): ?array
    {
        $atolCredits = $this->dataObjectFactory->create();
        $atolCredits->addData([
            'login' => $this->atolHelper->getConfig(Data::ATOL_LOGIN),
            'pass' => $this->atolHelper->decrypt($this->atolHelper->getConfig(Data::ATOL_PASSWORD))
        ]);

        return $this->callFunction($this->functions['getToken'], $atolCredits);
    }
}
