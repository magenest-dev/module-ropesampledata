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

namespace Magenest\RopeSampleData\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class VersionHelper extends AbstractHelper
{
    public function __construct(Context $context)
    {
        parent::__construct($context);
    }

    public function getVersionSampleData()
    {
        $version  = [
            'ver1' => [
                'block' => 'Magenest_RopeSampleData::fixtures/blocks/block_ver1.csv',
                'page'  => 'Magenest_RopeSampleData::fixtures/pages/page_ver1.csv',
                'widget' => 'Magenest_RopeSampleData::fixtures/widgets/widget_ver1.csv'
            ]
        ];
        return $version;
    }
}
