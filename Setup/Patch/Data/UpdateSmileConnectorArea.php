<?php
/**
 * Update Smile Connector area.
 *
 * @author    Dmytro Kohut <dmkoh@smile.fr>
 * @copyright 2020 Smile
 */

declare(strict_types=1);

namespace Mmd\Atol\Setup\Patch\Data;

use Exception;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Config\Model\ResourceModel\Config as ResourceConfig;
use Smile\Connector\Api\AreaRepositoryInterface;
use Mmd\Atol\Helper\Data;

/**
 * Class UpdateSmileConnectorArea
 *
 * @package Mmd\Atol\Setup\Patch\Data
 */
class UpdateSmileConnectorArea implements DataPatchInterface
{
    /**
     * Connector area const.
     */
    const CONNECTOR_AREA = 'main';

    /**
     * Resource config.
     *
     * @var ResourceConfig
     */
    protected $areaRepository;

    /**
     * Atol helper.
     *
     * @var Data
     */
    protected $atolHelper;

    /**
     * UpdateSmileConnectorArea constructor.
     *
     * @param AreaRepositoryInterface $areaRepository
     * @param Data $atolHelper
     */
    public function __construct(
        AreaRepositoryInterface $areaRepository,
        Data $atolHelper
    ) {
        $this->areaRepository = $areaRepository;
        $this->atolHelper = $atolHelper;
    }

    /**
     * @return DataPatchInterface|void
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     * @throws Exception
     *
     * @throws AlreadyExistsException
     */
    public function apply()
    {
        $value['atol'] = [
            'webservice_url' => $this->atolHelper->getConfig(Data::ATOL_WEBSERVICE_URL),
            'webservice_timeout' => $this->atolHelper->getConfig(Data::ATOL_WEBSERVICE_TIMEOUT),
            'webservice_username' => $this->atolHelper->getConfig(Data::ATOL_LOGIN),
            'webservice_password' => $this->atolHelper->decrypt($this->atolHelper->getConfig(Data::ATOL_PASSWORD))
        ];

        $area = $this->areaRepository->getByIdentifier(self::CONNECTOR_AREA);
        $area->setInterfaceFields(json_encode($value));
        $this->areaRepository->save($area);
    }

    /**
     * @return array|string[]
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @return array|string[]
     */
    public static function getDependencies()
    {
        return [];
    }
}
