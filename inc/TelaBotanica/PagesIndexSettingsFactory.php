<?php

/*
 * This file is part of WpAlgolia library.
 * (c) Raymond Rutjes for Algolia <raymond.rutjes@algolia.com>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace WpAlgolia\TelaBotanica;

use WpAlgolia\Index\IndexSettings;

class PagesIndexSettingsFactory
{
    /**
     * @return IndexSettings
     */
    public function create()
    {
        return new IndexSettings(
            [
                'searchableAttributes' => [
                    'unordered(post_title)',
                    'unordered(post_subtitle)',
                    'unordered(post_content)'
                ],
                'attributesForFaceting' => [
                    'post_type'
                ],
                'customRanking' => [
                    'desc(post_title)',
                    'asc(part)'
                ],
                'attributeForDistinct' => 'post_id',
                'distinct'             => true,
                'attributesToSnippet'  => [
                    'post_title:30',
                    'post_content:30',
                ],
                'snippetEllipsisText' => '…',
            ]
        );
    }
}
