<?php
/**
 * Copyright © 2019 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_magento233_blank01 extension
 * NOTICE OF LICENSE
 *
 * @category Magenest
 * @package Magenest_magento233_blank01
 */

namespace Magenest\RopeSampleData\Model;

use Exception;
use Magento\Cms\Model\PageFactory;
use Magento\Framework\File\Csv;
use Magento\Framework\Setup\SampleData\Context as SampleDataContext;
use Magento\Framework\Setup\SampleData\FixtureManager;
use Magento\Store\Model\Store;

class Page
{
    /**
     * @var Csv
     */
    protected $csvReader;
    /**
     * @var PageFactory
     */
    protected $pageFactory;
    /**
     * @var FixtureManager
     */
    private $fixtureManager;

    /**
     * Page constructor.
     * @param SampleDataContext $sampleDataContext
     * @param PageFactory $pageFactory
     */
    public function __construct(
        SampleDataContext $sampleDataContext,
        PageFactory $pageFactory
    ) {
        $this->fixtureManager = $sampleDataContext->getFixtureManager();
        $this->csvReader = $sampleDataContext->getCsvReader();
        $this->pageFactory = $pageFactory;
    }

    /**
     * @param array $fixtures
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

                    /** @var \Magento\Cms\Model\Page $page */
                    $page = $this->pageFactory->create();
                    $page->load($row['identifier'], 'identifier');
                    $page->addData($row);
                    $page->setCustomLayoutUpdateXml(null);
                    $page->setStores([Store::DEFAULT_STORE_ID]);
                    $page->save();
                }
            }
            $result['success'] = true;
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $result['message'] = $message;
        }

        return $result;


    }
}
