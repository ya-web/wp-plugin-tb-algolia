<?php

/*
 * This file is part of WpAlgolia library.
 * (c) Raymond Rutjes for Algolia <raymond.rutjes@algolia.com>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace WpAlgolia\TelaBotanica;

use WpAlgolia\Index\IndexSettings;

class EvenementsIndexSettingsFactory
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
                    'unordered(post_content)',
                    'unordered(event_description)',
                    'unordered(event_place)',
                    'unordered(event_contact)',
                    'unordered(category)',
                    'unordered(post_tag)',
                ],
                'attributesForFaceting' => [
                    'category',
                    'post_tag',
                    'post_author.display_name',
                    'event_is_free',
                    'event_place.city',
                ],
                'customRanking' => [
                    'desc(post_date)',
                ],
                'attributeForDistinct' => 'post_id',
                'distinct'             => true,
                'attributesToSnippet'  => [
                    'post_title:30',
                    'event_description:30',
                    'post_content:30',
                ],
                'snippetEllipsisText' => '…',
            ]
        );
    }
}
