<?php

/*
 * This file is part of WpAlgolia library.
 * (c) Raymond Rutjes for Algolia <raymond.rutjes@algolia.com>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace WpAlgolia\TelaBotanica;

use WpAlgolia\Index\IndexSettings;

class ProjetsIndexSettingsFactory
{
    /**
     * @return IndexSettings
     */
    public function create()
    {
        return new IndexSettings(
            [
                'searchableAttributes' => [
                    'name',
                    'unordered(description)',
                ],
                'attributesForFaceting' => [
                    'archive',
                    'tela',
                    'categories',
                ],
                'unretrievableAttributes' => [
                    'members_ids',
                ],
                'customRanking' => [
                    'desc(member_count)',
                ],
                'attributesToSnippet' => [
                    'description:10',
                ],
                'snippetEllipsisText' => '…',
            ]
        );
    }
}
