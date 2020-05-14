<?php
/**
 * Creditmemo export task.
 *
 * @author    Dmytro Kohut <dmkoh@smile.fr>
 * @copyright 2020 Smile
 */

declare(strict_types=1);

namespace Mmd\Atol\Task\Export;

use Exception;
use Magento\Framework\DataObject\Factory;
use Magento\Sales\Model\Order\Creditmemo;
use Mmd\Atol\Api\RequestInterface;
use Mmd\Atol\Api\VendorInterface;
use Mmd\Atol\Helper\Data;
use Mmd\Atol\WebserviceFunction\SellRefund;
use Mmd\Atol\WebserviceFunction\Token;

/**
 * Class CreditmemoExport
 *
 * @package Mmd\Atol\Task\Export
 */
class CreditmemoExport extends AbstractExport
{
    /**
     * Creditmemo export task code.
     */
    const TASK_CODE = 'creditmemoExport';

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
     * CreditmemoExport constructor.
     *
     * @param VendorInterface $vendor
     * @param SellRefund $sellRefund
     * @param Token $token
     * @param Data $atolHelper
     * @param Factory $dataObjectFactory
     */
    public function __construct(
        VendorInterface $vendor,
        SellRefund $sellRefund,
        Token $token,
        Data $atolHelper,
        Factory $dataObjectFactory
    ) {
        $this->functions = [
            'sell_refund' => $sellRefund,
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
        $this->exportCreditmemo();

        $this->getCreditmemo()
            ->setAtolStatus(Data::STATUS_WAIT)
            ->setAtolUuid($this->webservice->getResponse()['uuid'])
            ->save();

        return true;
    }

    /**
     * Form request.
     *
     * @param Creditmemo $creditmemo
     *
     * @return RequestInterface
     */
    protected function formRequest(Creditmemo $creditmemo): RequestInterface
    {
        return $this->vendor->buildRequest($creditmemo);
    }

    /**
     * Export creditmemo to Atol.
     *
     * @throws Exception
     *
     * @return bool
     */
    protected function exportCreditmemo(): bool
    {
        $groupCode = $this->atolHelper->getConfig(Data::ATOL_GROUP_CODE);
        $creditmemo = $this->getCreditmemo();
        $creditmemoDataObject = $this->dataObjectFactory->create();
        $request = $this->formRequest($creditmemo);
        $responseWithToken = $this->getResponseWithToken() ?? [];
        $creditmemoDataObject = $this->getEntityDataObjectExport(
            $creditmemoDataObject,
            $request,
            $responseWithToken,
            $groupCode
        );
        $this->callFunction($this->functions['sell_refund'], $creditmemoDataObject);

        return true;
    }

    /**
     * Get creditmemo to export.
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
