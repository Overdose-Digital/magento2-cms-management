<?php

namespace Overdose\CMSContent\Block\Adminhtml\Import\Upload;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Store\Api\StoreRepositoryInterface;
use Overdose\CMSContent\Api\ContentImportInterface;
use Overdose\CMSContent\Model\Source\CmsMode;
use Overdose\CMSContent\Model\Source\MediaMode;

class Form extends Generic
{
    /**
     * @var MediaMode
     */
    protected $mediaMode;
    /**
     * @var CmsMode
     */
    protected $cmsMode;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param MediaMode $mediaMode
     * @param CmsMode $cmsMode
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        MediaMode $mediaMode,
        CmsMode $cmsMode,
        array $data = []
    ) {
        $this->mediaMode = $mediaMode;
        $this->cmsMode = $cmsMode;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return Form
     * @throws LocalizedException
     */
    protected function _prepareForm()
    {
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'enctype' => 'multipart/form-data',
                    'action' => $this->getUrl('*/*/post'),
                    'method' => 'post'
                ]
            ]
        );

        $form->setHtmlIdPrefix('import_');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('Upload'),
                'class' => 'fieldset-wide',
            ]
        );

        $fieldsetMode = $form->addFieldset(
            'mode_fieldset',
            [
                'legend' => __('Import mode'),
                'class' => 'fieldset-wide',
            ]
        );

        $fieldset->addField(
            'zipfile',
            'file',
            [
                'name' => 'zipfile',
                'label' => __('ZIP File'),
                'title' => __('ZIP File'),
                'required' => true,
            ]
        );

        $fieldsetMode->addField(
            'cms_mode',
            'select',
            [
                'name' => 'cms_mode',
                'label' => __('CMS import mode'),
                'title' => __('CMS import mode'),
                'required' => true,
                'values' => $this->cmsMode->toOptionArray(),
            ]
        );

        $fieldsetMode->addField(
            'media_mode',
            'select',
            [
                'name' => 'media_mode',
                'label' => __('Media import mode'),
                'title' => __('Media import mode'),
                'required' => true,
                'values' => $this->mediaMode->toOptionArray(),
            ]
        );

        $values = [
            'cms_mode' => ContentImportInterface::OD_CMS_MODE_UPDATE,
            'media_mode' => ContentImportInterface::OD_MEDIA_MODE_UPDATE,
        ];

        // Set defaults
        $form->setValues($values);

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
