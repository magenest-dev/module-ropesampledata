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
namespace Magenest\RopeSampleData\Model\Block;

class Converter
{
    /**
     * Convert CSV format row to array
     *
     * @param array $row
     * @return array
     */
    public function convertRow($row)
    {
        $data = [];
        foreach ($row as $field => $value) {
            if ('content' == $field) {
                $data['block'][$field] = $this->replaceMatches($value);
                continue;
            }
            $data['block'][$field] = $value;
        }
        return $data;
    }
    /**
     * @param string $content
     * @return mixed
     */
    protected function replaceMatches($content)
    {
        $matches = $this->getMatches($content);
        if (!empty($matches['value'])) {
            $replaces = $this->getReplaces($matches);
            $content = preg_replace($replaces['regexp'], $replaces['value'], $content);
        }
        return $content;
    }

    /**
     * @param string $content
     * @return array
     */
    protected function getMatches($content)
    {
        $regexp = '/{{(category[^ ]*) key="([^"]+)"}}/';
        preg_match_all($regexp, $content, $matchesCategory);
        $regexp = '/{{(product[^ ]*) sku="([^"]+)"}}/';
        preg_match_all($regexp, $content, $matchesProduct);
        $regexp = '/{{(attribute) key="([^"]*)"}}/';
        preg_match_all($regexp, $content, $matchesAttribute);
        return [
            'type' => $matchesCategory[1] + $matchesAttribute[1] + $matchesProduct[1],
            'value' => $matchesCategory[2] + $matchesAttribute[2] + $matchesProduct[2]
        ];
    }

    /**
     * @param array $matches
     * @return array
     */
    protected function getReplaces($matches)
    {
        $replaceData = [];

        foreach ($matches['value'] as $matchKey => $matchValue) {
            $callback = "matcher" . ucfirst(trim($matches['type'][$matchKey]));
            $matchResult = call_user_func_array([$this, $callback], [$matchValue]);
            if (!empty($matchResult)) {
                $replaceData = array_merge_recursive($replaceData, $matchResult);
            }
        }
        return $replaceData;
    }
}
