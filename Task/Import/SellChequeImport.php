<?php
/**
 * Sell Cheque import task.
 *
 * @author    Dmytro Kohut <dmkoh@smile.fr>
 * @copyright 2020 Smile
 */

declare(strict_types=1);

namespace Mmd\Atol\Task\Import;

use Exception;
use Magento\Framework\DataObject\Factory;
use Magento\Sales\Model\Order\Invoice;
use Mmd\Atol\Helper\Data;
use Mmd\Atol\Task\Export\AbstractExport;
use Mmd\Atol\WebserviceFunction\Report;
use Mmd\Atol\WebserviceFunction\Token;

/**
 * Class SellChequeImport
 *
 * @package Mmd\Atol\Task\Import
 */
class SellChequeImport extends AbstractExport
{
    /**
     * Sell cheque task code.
     */
    const TASK_CODE = 'sellChequeImport';

    /**
     * @var Factory
     */
    protected $dataObjectFactory;

    /**
     * @var array
     */
    protected $functions;

    /**
     * @var Data
     */
    protected $atolHelper;

    /**
     * SellChequeImport constructor.
     *
     * @param Report $report
     * @param Token$token
     * @param Data $atolHelper
     * @param Factory $dataObjectFactory
     */
    public function __construct(
        Report $report,
        Token $token,
        Data $atolHelper,
        Factory $dataObjectFactory
    ) {
        $this->functions = [
            'sellChequeImport' => $report,
            'getToken' => $token
        ];
        $this->atolHelper = $atolHelper;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * Run import.
     *
     * @throws Exception
     *
     * @return bool
     */
    protected function execute(): bool
    {
        $this->sellChequeImport();

        $this->getInvoice()
            ->setAtolStatus(Data::STATUS_DONE)
            ->save();

        return true;
    }

    /**
     * Import sell cheque from Atol.
     *
     * @throws Exception
     *
     * @return bool
     */
    protected function sellChequeImport(): bool
    {
        $groupCode = $this->atolHelper->getConfig(Data::ATOL_GROUP_CODE);
        $invoice = $this->getInvoice();
        $invoiceDataObject = $this->dataObjectFactory->create();
        $responseWithToken = $this->getResponseWithToken() ?? [];
        $invoiceDataObject = $this->getEntityDataObjectImport(
            $invoice,
            $invoiceDataObject,
            $responseWithToken,
            $groupCode
        );
        $this->callFunction($this->functions['sellChequeImport'], $invoiceDataObject);

        return true;
    }

    /**
     * Get invoice for import
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
