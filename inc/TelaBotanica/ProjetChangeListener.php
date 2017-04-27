<?php

/*
 * This file is part of WpAlgolia library.
 * (c) Raymond Rutjes for Algolia <raymond.rutjes@algolia.com>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace WpAlgolia\TelaBotanica;

use WpAlgolia\BuddypressGroupsIndex;

class ProjetChangeListener
{
	/**
	 * @var ProjetsIndex
	 */
	private $index;

	private $postType = 'bp_groups';

	/**
	 * @param ProjetsIndex $index
	 */
	public function __construct(BuddypressGroupsIndex $index)
	{
		$this->index = $index;
		// TODO
		// add_action('save_post', array($this, 'pushRecords'), 10, 2);
		// add_action('before_delete_post', array($this, 'deleteRecords'));
		// add_action('wp_trash_post', array($this, 'deleteRecords'));
	}

	/**
	 * @param int              $groupId
	 * @param \BP_Groups_Group $group
	 */
	public function pushRecords($groupId, $group)
	{
		// TODO
		// if ($this->postType !== $post->post_type) {
		//		 return;
		// }

		// TODO
		// if ($post->post_status !== 'publish' || !empty($post->post_password)) {
		//		 return $this->deleteRecords($postId);
		// }

		$this->index->pushRecordsForGroup($group);
	}

	/**
	 * @param int $groupId
	 */
	public function deleteRecords($groupId)
	{
		// TODO
		// $post = get_post($groupId);
		// if ($group instanceof \BP_Groups_Group && $post->post_type === $this->postType) {
		//		 $this->index->deleteRecordsForPost($post);
		// }
	}
}
