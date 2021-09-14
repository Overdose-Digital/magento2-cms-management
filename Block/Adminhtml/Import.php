<?php

namespace Overdose\CMSContent\Block\Adminhtml;

use Magento\Backend\Block\Widget\Form\Container;

class Import extends Container
{
    protected $_mode = 'upload';

    protected function _construct()
    {
        $this->_objectId = 'import';
        $this->_blockGroup = 'Overdose_CMSContent';
        $this->_controller = 'adminhtml_import';

        parent::_construct();

        $this->buttonList->remove('back');
        $this->buttonList->update('save', 'label', __('Import'));
    }

    public function getHeaderText(): string
    {
        return __('Import CMS');
    }
}
