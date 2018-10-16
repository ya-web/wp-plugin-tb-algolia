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

    /**
     * @param ProjetsIndex $index
     */
    public function __construct(BuddypressGroupsIndex $index)
    {
        $this->index = $index;
        add_action('groups_group_after_save', [$this, 'pushRecords']);
        add_action('groups_avatar_uploaded', [$this, 'updateGroupAvatar']);
        add_action('bp_groups_delete_group', [$this, 'deleteRecords']);
    }

    public function updateGroupAvatar($groupId) {
        $this->pushRecords(groups_get_group($groupId));
    }

    /**
     * @param \BP_Groups_Group $group
     */
    public function pushRecords(\BP_Groups_Group $group)
    {
        $should_index = true;

        // index only public groups
        $visibility = bp_get_group_status($group);
        if (!in_array($visibility, ['public', 'private'])) {
            $should_index = false;
        }

        // compatibility with bp-moderate-group-creation plugin
        $published_state = groups_get_groupmeta($group->id, 'published');
        if ('0' === $published_state) {
            $should_index = false;
        }

        if ($should_index) {
            $this->index->pushRecordsForGroup($group);
        } else {
            $this->deleteRecords($group);
        }
    }

    /**
     * @param \BP_Groups_Group $group
     */
    public function deleteRecords(\BP_Groups_Group $group)
    {
        if ($group instanceof \BP_Groups_Group) {
            $this->index->deleteRecordsForGroup($group);
        }
    }
}
