<?php
/**
 * Copyright © 2019 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest extension
 * NOTICE OF LICENSE
 *
 * @category Magenest
 * @package Magenest_RopeSampleData
 */

namespace Magenest\RopeSampleData\Model;

use Exception;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Cms\Model\BlockFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\File\Csv;
use Magento\Framework\Setup\SampleData\Context as SampleDataContext;
use Magento\Framework\Setup\SampleData\FixtureManager;
use Magento\Store\Model\Store;

class Block
{

    /**
     * @var Csv
     */
    protected $csvReader;
    /**
     * @var BlockFactory
     */
    protected $blockFactory;
    /**
     * @var Block\Converter
     */
    protected $converter;
    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;
    /**
     * @var FixtureManager
     */
    private $fixtureManager;

    public function __construct(
        SampleDataContext $sampleDataContext,
        BlockFactory $blockFactory,
        Block\Converter $converter,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->fixtureManager = $sampleDataContext->getFixtureManager();
        $this->csvReader = $sampleDataContext->getCsvReader();
        $this->blockFactory = $blockFactory;
        $this->converter = $converter;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @param array $fixtures
     * @return mixed
     * @throws Exception
     */
    public function install(array $fixtures)
    {
        $result = [
            'success' => false,
            'message' => ''
        ];
        try {
            foreach ($fixtures as $fileName) {
                $fileName = $this->fixtureManager->getFixture($fileName);
                if (!file_exists($fileName)) {
                    continue;
                }
                $rows = $this->csvReader->getData($fileName);
                $header = array_shift($rows);
                foreach ($rows as $row) {
                    $data = [];
                    foreach ($row as $key => $value) {
                        $data[$header[$key]] = $value;
                    }
                    $row = $data;

                    $cmsBlock = $this->blockFactory->create();
                    $cmsBlock->getResource()->load($cmsBlock, $row['identifier']);

                    if (!$cmsBlock->getData()) {
                        $cmsBlock->setData($row);
                    } else {
                        $cmsBlock->addData($row);
                    }
                    $cmsBlock->setStores([Store::DEFAULT_STORE_ID]);
                    $cmsBlock->save();
                }
            }
            $result['success'] = true;
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $result['message'] = $message;
        }

        return $result;
    }

    /**
     * @param array $data
     * @return \Magento\Cms\Model\Block
     * @throws Exception
     */
    protected function saveCmsBlock($data)
    {
        $cmsBlock = $this->blockFactory->create();
        $cmsBlock->getResource()->load($cmsBlock, $data['identifier']);
        if (!$cmsBlock->getData()) {
            $cmsBlock->setData($data);
        } else {
            $cmsBlock->addData($data);
        }
        $cmsBlock->setStores([Store::DEFAULT_STORE_ID]);
        $cmsBlock->setIsActive(1);
        $cmsBlock->save();
        return $cmsBlock;
    }

    /**
     * @param string $blockId
     * @param string $categoryId
     * @return void
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    protected function setCategoryLandingPage($blockId, $categoryId)
    {
        $categoryCms = [
            'landing_page' => $blockId,
            'display_mode' => 'PRODUCTS_AND_PAGE',
        ];
        if (!empty($categoryId)) {
            $category = $this->categoryRepository->get($categoryId);
            $category->setData($categoryCms);
            $this->categoryRepository->save($categoryId);
        }
    }
}
