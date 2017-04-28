<?php

/*
 * This file is part of WpAlgolia library.
 * (c) Raymond Rutjes for Algolia <raymond.rutjes@algolia.com>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace WpAlgolia\TelaBotanica;

use WpAlgolia\Index\IndexSettings;

class ActualitesIndexSettingsFactory
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
					'unordered(taxonomies)',
					'unordered(post_content)',
				),
				'attributesForFaceting' => array(
					'taxonomies',
					'taxonomies_hierarchical',
					'post_author.display_name',
					'post_author.is_free',
				),
				'customRanking' => array(
					'desc(post_date)',
				),
				'attributeForDistinct' => 'post_id',
				'distinct' => true,
				'attributesToSnippet' => array(
					'post_title:30',
					'post_content:30',
				),
				'snippetEllipsisText' => '…',
			)
		);
	}
}
