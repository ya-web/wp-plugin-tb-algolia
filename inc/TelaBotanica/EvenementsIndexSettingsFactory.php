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
			array(
				'searchableAttributes' => array(
					'unordered(post_title)',
					'unordered(title1)',
					'unordered(title2)',
					'unordered(title3)',
					'unordered(title4)',
					'unordered(title5)',
					'unordered(title6)',
					'unordered(taxonomies)',
					'unordered(content)',
				),
				'attributesForFaceting' => array(
					'taxonomies',
					'taxonomies_hierarchical',
					'post_author.display_name',
				),
				'customRanking' => array(
					'desc(is_sticky)',
					'desc(post_date)',
				),
				'attributeForDistinct' => 'post_id',
				'distinct' => true,
				'attributesToSnippet' => array(
					'post_title:30',
					'title1:30',
					'title2:30',
					'title3:30',
					'title4:30',
					'title5:30',
					'title6:30',
					'content:30',
				),
				'snippetEllipsisText' => '…',
			)
		);
	}
}
