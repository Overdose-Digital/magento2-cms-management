<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Block\Adminhtml\Import\Button;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class Import implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData(): array
    {
        return [
            'label'      => __('Import'),
            'class'      => 'action-secondary',
            'sort_order' => 20,
            'on_click'   => '',
            'data_attribute' => [
                'mage-init' => [
                    'buttonAdapter' => [
                        'actions' => [
                            [
                                'targetName' => 'cmscontent_import_form.cmscontent_import_form',
                                'actionName' => 'save',
                                'params' => [
                                    false
                                ]
                            ]
                        ]
                    ]
                ]
            ],
        ];
    }
}
