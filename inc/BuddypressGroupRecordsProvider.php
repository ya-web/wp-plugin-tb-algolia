<?php

/*
 * This file is part of WpAlgolia library.
 * (c) Raymond Rutjes for Algolia <raymond.rutjes@algolia.com>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace WpAlgolia;

use WpAlgolia\Index\RecordsProvider;

abstract class BuddypressGroupRecordsProvider implements RecordsProvider
{
	/**
	 * @param int $perPage
	 *
	 * @return int
	 */
	public function getTotalPagesCount($perPage)
	{
		$results = $this->newQuery(array(
			'per_page' => (int) $perPage
		));

		return (int) ceil($results['total'] / $perPage);
	}

	/**
	 * @param int $page
	 * @param int $perPage
	 *
	 * @return array
	 */
	public function getRecords($page, $perPage)
	{
		$query = $this->newQuery(array(
			'per_page' => $perPage,
			'page'     => $page,
		));

		return $this->getRecordsForQuery($query);
	}

	/**
	 * @param mixed $id
	 *
	 * @return array
	 */
	public function getRecordsForId($id)
	{
		$group = groups_get_group( $id );

		if (!$group instanceof \BP_Groups_Group) {
			return array();
		}

		return $this->getRecordsForGroup($group);
	}

	/**
	 * @param \BP_Groups_Group $group
	 *
	 * @return array
	 */
	abstract public function getRecordsForGroup(\BP_Groups_Group $group);

	/**
	 * @return array
	 */
	abstract protected function getDefaultQueryArgs();

	/**
	 * @param array $args
	 *
	 * @return array
	 */
	private function newQuery(array $args = array())
	{
		$defaultArgs = $this->getDefaultQueryArgs();

		$args = array_merge($defaultArgs, $args);
		$query = \BP_Groups_Group::get($args);

		return $query;
	}

	/**
	 * @param array $query
	 *
	 * @return array
	 */
	private function getRecordsForQuery(array $query = array())
	{
		$records = array();

		foreach ($query['groups'] as $group) {
			if (!$group instanceof \BP_Groups_Group) {
				continue;
			}

			// index only public groups
			$visibility = bp_get_group_status($group);
			if ( !in_array( $visibility, ['public', 'private']) ) continue;

			// compatibility with bp-moderate-group-creation plugin
			$published_state = groups_get_groupmeta($group->id, 'published');
			if ( '0' === $published_state ) continue;

			$records = array_merge($records, $this->getRecordsForGroup($group));
		}

		return $records;
	}
}
