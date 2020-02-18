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

namespace Magenest\RopeSampleData\Controller\Adminhtml\SampleData;

use Magenest\RopeSampleData\Helper\VersionHelper;
use Magenest\RopeSampleData\Model\Block;
use Magenest\RopeSampleData\Model\CmsBlock;
use Magenest\RopeSampleData\Model\Page;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\JsonFactory;

class Apply extends Action
{
    protected $cmsBlock;
    protected $_versionHelper;
    /**
     * @var JsonFactory
     */
    private $jsonResultFactory;

    /**
     * @var Page
     */
    private $page;

    /**
     * @var Block
     */
    private $block;

    /**
     * Apply constructor.
     * @param Action\Context $context
     * @param CmsBlock $cmsBlock
     * @param Page $page
     * @param Block $block
     * @param VersionHelper $versionHelper
     * @param JsonFactory $jsonResultFactory
     */
    public function __construct(
        Action\Context $context,
        CmsBlock $cmsBlock,
        Page $page,
        Block $block,
        VersionHelper $versionHelper,
        JsonFactory $jsonResultFactory
    ) {
        parent::__construct($context);
        $this->cmsBlock = $cmsBlock;
        $this->jsonResultFactory = $jsonResultFactory;
        $this->_versionHelper = $versionHelper;
        $this->page = $page;
        $this->block = $block;
    }

    public function execute()
    {
        $dataResult = [
            'success' => true,
            'message' => '',
            'error' => false
        ];
        $apply = false;
        if ($this->getRequest()->getParam('isAjax')) {
            $versionApply = $this->getRequest()->getParam('version');
            $versions = $this->_versionHelper->getVersionSampleData();
            if ($versionApply) {
                foreach ($versions as $key => $value) {
                    if ($key == $versionApply) {
                        $apply = $this->ApplySampleData($value);
                    }
                }
            }
        }
        if ($apply) {
            $dataResult['message'] = __('You have applied sample data successfully.');
        } else {
            $dataResult['success'] = $apply;
            $dataResult['error'] = true;
            $dataResult['message'] = __("An error occurred during the application of sample data.");
        }
        $result = $this->jsonResultFactory->create();
        $result->setData($dataResult);
        return $result;

        // TODO: Implement execute() method.
    }

    public function ApplySampleData($dataSample)
    {
        if ($dataSample) {
            try {
                foreach ($dataSample as $key => $value) {
                    switch ($key) {
                        case 'widget':
                            $installWidgets = $this->cmsBlock->install([$value]);
                            if (!$installWidgets['success']) {
                                return false;
                            }
                            break;
                        case 'block':
                            $installBlocks = $this->block->install([$value]);
                            if (!$installBlocks['success']) {
                                return false;
                            }
                            break;
                        case 'page':
                            $installPages = $this->page->install([$value]);
                            if (!$installPages['success']) {
                                return false;
                            }
                            break;

                    }
                }
                return true;
            } catch (\Exception $e) {
                return false;
            }

        }
    }
}
