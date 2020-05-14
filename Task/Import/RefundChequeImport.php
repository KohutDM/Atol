<?php
/**
 * Refund Cheque import task.
 *
 * @author    Dmytro Kohut <dmkoh@smile.fr>
 * @copyright 2020 Smile
 */

declare(strict_types=1);

namespace Mmd\Atol\Task\Import;

use Exception;
use Magento\Framework\DataObject\Factory;
use Magento\Sales\Model\Order\Creditmemo;
use Mmd\Atol\Helper\Data;
use Mmd\Atol\Task\Export\AbstractExport;
use Mmd\Atol\WebserviceFunction\Report;
use Mmd\Atol\WebserviceFunction\Token;

/**
 * Class RefundChequeImport
 *
 * @package Mmd\Atol\Task\Import
 */
class RefundChequeImport extends AbstractExport
{
    /**
     * Refund cheque import task code.
     */
    const TASK_CODE = 'refundChequeImport';

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
     * RefundChequeImport constructor.
     *
     * @param Report $report
     * @param Token $token
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
            'refundChequeImport' => $report,
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
        $this->refundChequeImport();

        $this->getCreditmemo()
            ->setAtolStatus(Data::STATUS_DONE)
            ->save();

        return true;
    }

    /**
     * Import refund cheque from Atol.
     *
     * @throws Exception
     *
     * @return bool
     */
    protected function refundChequeImport(): bool
    {
        $groupCode = $this->atolHelper->getConfig(Data::ATOL_GROUP_CODE);
        $creditmemo = $this->getCreditmemo();
        $creditmemoDataObject = $this->dataObjectFactory->create();
        $responseWithToken = $this->getResponseWithToken() ?? [];
        $creditmemoDataObject = $this->getEntityDataObjectImport(
            $creditmemo,
            $creditmemoDataObject,
            $responseWithToken,
            $groupCode
        );
        $this->callFunction($this->functions['refundChequeImport'], $creditmemoDataObject);

        return true;
    }

    /**
     * Get creditmemo for import
     *
     * @return Creditmemo
     */
    protected function getCreditmemo(): Creditmemo
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
