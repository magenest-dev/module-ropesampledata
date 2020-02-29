<?php
/**
 * Copyright © 2020 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * rope_theme extension
 * NOTICE OF LICENSE
 *
 * @category Magenest
 * @package rope_theme
 * @package linhphung
 */

namespace Magenest\RopeSampleData\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Filesystem\Directory\ReadFactory;

class SystemInformation extends Field
{
    protected $_template = 'Magenest_RopeSampleData::system/config/apply_sample_data.phtml';
    protected $directory_list;
    protected $readFactory;
    protected $componentRegistrar;
    protected $moduleReader;
    protected $_backendUrl;

    protected $_versionHelper;


    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        ComponentRegistrar $componentRegistrar,
        ReadFactory $readFactory,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \Magento\Framework\Module\Dir\Reader $moduleReader,
        \Magenest\RopeSampleData\Helper\VersionHelper $versionHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->directory_list = $directoryList;
        $this->readFactory = $readFactory;
        $this->componentRegistrar = $componentRegistrar;
        $this->moduleReader = $moduleReader;
        $this->_backendUrl = $backendUrl;
        $this->_versionHelper = $versionHelper;
    }

    public function getPathFileSample()
    {
        $moduleDir = $this->getDirectory();
        $directoryRead = $this->readFactory->create($moduleDir);
        $filesPath = $directoryRead->read();
        if (isset($filesPath)) {
            return $filesPath;
        }

        return false;
    }

    public function getDirectory()
    {
        $viewDir = $this->moduleReader->getModuleDir('', 'Magenest_RopeSampleData');
        return $viewDir . '/fixtures/widgets/';
    }

    public function getApplySampleDataUrl()
    {
        /**
         * ropesample => font name
         * sampledata => module name
         * apply      => action
         * */
        $url = $this->_backendUrl->getUrl("ropesample/sampledata/apply");
        return $url;
    }

    public function getVersionSampleData()
    {
        return $this->_versionHelper->getVersionSampleData();

    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }
}
