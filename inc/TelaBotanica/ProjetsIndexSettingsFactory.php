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
			array(
				'searchableAttributes' => array(
					'name',
					'unordered(description)',
				),
				'attributesForFaceting' => array(
					'archive',
					'tela',
					'categories',
				),
				'unretrievableAttributes' => array(
					'members_ids',
				),
				'customRanking' => array(
					'desc(member_count)',
				),
				'attributesToSnippet' => array(
					'description:10',
				),
				'snippetEllipsisText' => '…',
			)
		);
	}
}
