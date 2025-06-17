<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Model\Config\Cron;

use Magento\Cron\Model\Config\Source\Frequency;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Overdose\CMSContent\Model\Config;

class SaveValue extends Value
{
    /**
     * @var ConfigInterface
     */
    private $configInterface;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param ConfigInterface $configInterface
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        ConfigInterface $configInterface,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
        $this->configInterface = $configInterface;
    }

    /**
     * @return SaveValue
     * @throws \Exception
     */
    public function afterSave(): SaveValue
    {
        $time = $this->getData(Config::CRON_ARRAY_PATH_TIME_VALUE);
        $frequency = $this->getData(Config::CRON_ARRAY_PATH_FREQUENCY_VALUE);

        $cronExprArray = [
            (int)$time[1], //Minute
            (int)$time[0], //Hour
            $frequency == Frequency::CRON_MONTHLY ? '1' : '*', //Day of the Month
            '*', //Month of the Year
            $frequency == Frequency::CRON_WEEKLY ? '1' : '*', //Day of the Week
        ];

        $cronExprString = implode(' ', $cronExprArray);

        try {
            $this->configInterface->saveConfig(
                Config::CRON_STRING_PATH,
                $cronExprString,
                $this->getScope(),
                $this->getScopeId()
            );
        } catch (\Exception $e) {
            throw new \Exception(__('We can\'t save the cron expression.'));
        }

        return parent::afterSave();
    }
}
