<?php

/*
 * This file is part of WpAlgolia library.
 * (c) Raymond Rutjes for Algolia <raymond.rutjes@algolia.com>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace WpAlgolia;

use WpAlgolia\Index\Index;
use WpAlgolia\Index\IndexSettings;
use WpAlgolia\Index\RecordsProvider;

class BuddypressGroupsIndex extends Index
{
		/**
		 * @var string
		 */
		private $name;

		/**
		 * @var Client
		 */
		private $client;

		/**
		 * @var IndexSettings
		 */
		private $settings;

		/**
		 * @var WpQueryRecordsProvider
		 */
		private $recordsProvider;

		/**
		 * @param string                         $name
		 * @param Client                         $client
		 * @param IndexSettings                  $settings
		 * @param BuddypressGroupRecordsProvider $recordsProvider
		 */
		public function __construct($name, Client $client, IndexSettings $settings, BuddypressGroupRecordsProvider $recordsProvider)
		{
				$this->name = $name;
				$this->client = $client;
				$this->settings = $settings;
				$this->recordsProvider = $recordsProvider;
		}

		/**
		 * @return string
		 */
		public function getName()
		{
				return $this->name;
		}

		/**
		 * @param \BP_Groups_Group $group
		 */
		public function deleteRecordsForGroup(\BP_Groups_Group $group)
		{
				$records = $this->recordsProvider->getRecordsForGroup($group);
				$recordIds = array();
				foreach ($records as $record) {
						if (!isset($record['objectID'])) {
								continue;
						}

						$recordIds[] = $record['objectID'];
				}

				if (empty($recordIds)) {
						return;
				}

				$this->getAlgoliaIndex()->deleteObjects($recordIds);
		}

		/**
		 * @param \BP_Groups_Group $group
		 *
		 * @return int
		 */
		public function pushRecordsForGroup(\BP_Groups_Group $group)
		{
				$records = $this->recordsProvider->getRecordsForGroup($group);
				$totalRecordsCount = count($records);

				if (empty($totalRecordsCount)) {
						return 0;
				}

				$this->getAlgoliaIndex()->addObjects($records);

				return $totalRecordsCount;
		}

		/**
		 * @return RecordsProvider
		 */
		public function getRecordsProvider()
		{
				return $this->recordsProvider;
		}

		/**
		 * @return IndexSettings
		 */
		protected function getSettings()
		{
				return $this->settings;
		}

		/**
		 * @return Client
		 */
		protected function getAlgoliaClient()
		{
				return $this->client;
		}
}
