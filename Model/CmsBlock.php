<?php
/**
 * Copyright © 2019 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Rope Theme extension
 * NOTICE OF LICENSE
 *
 * @category Magenest
 * @package Linh Phung
 */

namespace Magenest\RopeSampleData\Model;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Cms\Model\BlockFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\State;
use Magento\Framework\File\Csv;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Setup\SampleData\Context as SampleDataContext;
use Magento\Framework\Setup\SampleData\FixtureManager;
use Magento\Store\Model\Store;
use Magento\Widget\Model\ResourceModel\Widget\Instance\Collection;
use Magento\Widget\Model\Widget\InstanceFactory;

class CmsBlock
{

    /**
     * @var CollectionFactory
     */
    protected $categoryFactory;

    /**
     * @var InstanceFactory
     */
    protected $widgetFactory;

    /**
     * @var \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory
     */
    protected $themeCollectionFactory;

    /**
     * @var BlockFactory
     */
    protected $cmsBlockFactory;

    /**
     * @var \Magento\Widget\Model\ResourceModel\Widget\Instance\CollectionFactory
     */
    protected $appCollectionFactory;

    /**
     * @var FixtureManager
     */
    protected $fixtureManager;

    /**
     * @var Csv
     */
    protected $csvReader;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var State
     */
    private $appState;

    /**
     * CmsBlock constructor.
     * @param SampleDataContext $sampleDataContext
     * @param InstanceFactory $widgetFactory
     * @param \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory $themeCollectionFactory
     * @param BlockFactory $cmsBlockFactory
     * @param \Magento\Widget\Model\ResourceModel\Widget\Instance\CollectionFactory $appCollectionFactory
     * @param CollectionFactory $categoryFactory
     * @param Json|null $serializer
     * @param State|null $appState
     */
    public function __construct(
        SampleDataContext $sampleDataContext,
        InstanceFactory $widgetFactory,
        \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory $themeCollectionFactory,
        BlockFactory $cmsBlockFactory,
        \Magento\Widget\Model\ResourceModel\Widget\Instance\CollectionFactory $appCollectionFactory,
        CollectionFactory $categoryFactory,
        Json $serializer = null,
        State $appState = null
    ) {
        $this->fixtureManager = $sampleDataContext->getFixtureManager();
        $this->csvReader = $sampleDataContext->getCsvReader();
        $this->widgetFactory = $widgetFactory;
        $this->themeCollectionFactory = $themeCollectionFactory;
        $this->cmsBlockFactory = $cmsBlockFactory;
        $this->appCollectionFactory = $appCollectionFactory;
        $this->categoryFactory = $categoryFactory;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
        $this->appState = $appState ?: ObjectManager::getInstance()->get(State::class);
    }

    /**
     * Loop through list of fixture files and install widget data
     *
     * @param string[] $fixtures
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function install(array $fixtures)
    {
        $pageGroupConfig = [
            'pages' => [
                'block' => '',
                'for' => 'all',
                'layout_handle' => 'default',
                'template' => 'widget/static_block/default.phtml',
                'page_id' => '',
            ],
            'all_pages' => [
                'block' => '',
                'for' => 'all',
                'layout_handle' => 'default',
                'template' => 'widget/static_block/default.phtml',
                'page_id' => '',
            ],
            'anchor_categories' => [
                'entities' => '',
                'block' => '',
                'for' => 'all',
                'is_anchor_only' => 0,
                'layout_handle' => 'catalog_category_view_type_layered',
                'template' => 'widget/static_block/default.phtml',
                'page_id' => '',
            ],
        ];
        $result = [
          'success'  => false,
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
                    /** @var Collection $instanceCollection */
                    $instanceCollection = $this->appCollectionFactory->create();
                    $instanceCollection->addFilter('title', $row['title']);
                    if ($instanceCollection->count() > 0) {
                        continue;
                    }
                    /** @var \Magento\Cms\Model\Block $block */
                    $block = $this->cmsBlockFactory->create()->load($row['block_identifier'], 'identifier');
                    if (!$block) {
                        continue;
                    }
                    $widgetInstance = $this->widgetFactory->create();

                    $code = $row['type_code'];
                    $themeId = $this->themeCollectionFactory->create()->getThemeByFullPath($row['theme_path'])->getId();
                    $type = $widgetInstance->getWidgetReference('code', $code, 'type');
                    $pageGroup = [];
                    $group = $row['page_group'];
                    $pageGroup['page_group'] = $group;
                    $pageGroup[$group] = array_merge(
                        $pageGroupConfig[$group],
                        $this->serializer->unserialize($row['group_data'])
                    );
                    if (!empty($pageGroup[$group]['entities'])) {
                        $pageGroup[$group]['entities'] = $this->getCategoryByUrlKey(
                            $pageGroup[$group]['entities']
                        )->getId();
                    }

                    $widgetInstance->setType($type)->setCode($code)->setThemeId($themeId);
                    $widgetInstance->setTitle($row['title'])
                        ->setSortOrder($row['sort_order'])
                        ->setStoreIds([Store::DEFAULT_STORE_ID])
                        ->setWidgetParameters(['block_id' => $block->getId()])
                        ->setPageGroups([$pageGroup]);
                    $this->appState->emulateAreaCode(
                        'frontend',
                        [$widgetInstance, 'save']
                    );
                }
            }
            $result['success'] = true;

        } catch (\Exception $exception) {
            $message = $exception->getMessage();
            $result['message'] = $message;
        }
        return $result;
    }

    /**
     * @param string $urlKey
     * @return \Magento\Framework\DataObject
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getCategoryByUrlKey($urlKey)
    {
        $category = $this->categoryFactory->create()
            ->addAttributeToFilter('url_key', $urlKey)
            ->addUrlRewriteToResult()
            ->getFirstItem();
        return $category;
    }
}
