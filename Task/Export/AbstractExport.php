<?php
/**
 * Abstract export class for invoice and creditmemo.
 *
 * @author    Dmytro Kohut <dmkoh@smile.fr>
 * @copyright 2020 Smile
 */

declare(strict_types=1);

namespace Mmd\Atol\Task\Export;

use Magento\Framework\DataObject;
use Magento\Sales\Model\AbstractModel;
use Mmd\Atol\Api\RequestInterface;
use Mmd\Atol\WebserviceFunction\AbstractFunction;
use Smile\Connector\Task\AbstractTask;

/**
 * Class AbstractExport
 *
 * @package Mmd\Atol\Task\Export
 */
abstract class AbstractExport extends AbstractTask
{
    /**
     * Execute the task for a activated area.
     *
     * @return bool
     */
    public function executeEnable(): bool
    {
        return $this->execute();
    }

    /**
     * Execute webservice call.
     *
     * @return bool
     */
    abstract protected function execute(): bool;

    /**
     * Execute the task for a no-activated area, nothing to do.
     *
     * @return bool
     */
    public function executeDisable(): bool
    {
        return true;
    }

    /**
     * Call function.
     *
     * @param AbstractFunction $function
     * @param DataObject $dataObject
     *
     * @return array|null
     */
    protected function callFunction(AbstractFunction $function, DataObject $dataObject): ?array
    {
        $function->setObject($dataObject);
        $this->webservice->call($function);

        return $this->webservice->getResponse();
    }

    /**
     * Get entity data object for export operations.
     *
     * @param DataObject $entityDataObject
     * @param RequestInterface $request
     * @param array $response
     * @param string $groupCode
     *
     * @return DataObject
     */
    protected function getEntityDataObjectExport(
        DataObject $entityDataObject,
        RequestInterface $request,
        array $response,
        string $groupCode
    ): DataObject {
        $requestArray = $request->__toArray();
        foreach ($requestArray['receipt']['items'] as &$item) {
            $item = $item->jsonSerialize();
        }
        foreach ($requestArray['receipt']['payments'] as &$payment) {
            $payment = $payment->jsonSerialize();
        }
        $requestArray['groupCode'] = $groupCode;
        $requestArray['token'] = $response['token'];
        $requestArray['timestamp'] = $response['timestamp'];

        return $entityDataObject->addData($requestArray);
    }

    /**
     * Get entity data object for import operations.
     *
     * @param AbstractModel $entity
     * @param DataObject $entityDataObject
     * @param array $response
     * @param string $groupCode
     *
     * @return DataObject
     */
    protected function getEntityDataObjectImport(
        AbstractModel $entity,
        DataObject $entityDataObject,
        array $response,
        string $groupCode
    ): DataObject {
        $requestArray['groupCode'] = $groupCode;
        $requestArray['token'] = $response['token'];
        $requestArray['uuid'] = $entity->getAtolUuid();

        return $entityDataObject->addData($requestArray);
    }
}
