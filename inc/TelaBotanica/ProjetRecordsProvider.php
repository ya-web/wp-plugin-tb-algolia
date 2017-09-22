<?php

/*
 * This file is part of WpAlgolia library.
 * (c) Raymond Rutjes for Algolia <raymond.rutjes@algolia.com>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace WpAlgolia\TelaBotanica;

use WpAlgolia\BuddypressGroupRecordsProvider;

class ProjetRecordsProvider extends BuddypressGroupRecordsProvider
{
    /**
     * @param \BP_Groups_Group $group
     *
     * @return array
     */
    public function getRecordsForGroup(\BP_Groups_Group $group)
    {
        $record = [];
        $record['objectID'] = $group->id;
        $record['name'] = $group->name;
        $record['creator_id'] = $group->creator_id;
        $record['description'] = $group->description;
        $record['last_activity'] = strtotime($group->last_activity); // @WARNING fuseau horaire de PHP
        $record['permalink'] = bp_get_group_permalink($group);
        $record['image'] = bp_core_fetch_avatar([
            'item_id' => $group->id,
            'object'  => 'group',
            'html'    => false
        ]);
        $record['cover_image'] = bp_attachments_get_attachment('url', [
            'object_dir' => 'groups',
            'item_id'    => $group->id
        ]);
        $categories = array_map(function ($category) {
            return bp_groups_get_group_type_object($category)->labels['name'];
        }, bp_groups_get_group_type($group->id, false) ?: []);
        $record['categories'] = ($categories === false ? [] : $categories);
        $record['tela'] = bp_groups_has_group_type($group->id, 'tela-botanica');
        $record['archive'] = bp_groups_has_group_type($group->id, 'archive');
        $record['member_count'] = intval(groups_get_total_member_count($group->id));
        $description_complete = groups_get_groupmeta($group->id, 'description-complete');
        $record['description_complete'] = strip_tags($description_complete);
        $members_ids = [];
        $members = groups_get_group_members(['group_id' => $group->id]);
        foreach ($members['members'] as $member) {
            $members_ids[] = $member->ID;
        }
        if (!in_array($group->creator_id, $members_ids)) {
            $members_ids[] = $group->creator_id;
        }
        $record['members_ids'] = $members_ids;
        $record['visibility'] = bp_get_group_status($group);

        $record = (array) apply_filters('algolia_group_record', $record, $group);

        return [$record];
    }

    /**
     * @return array
     */
    protected function getDefaultQueryArgs()
    {
        return [
            'order'       => 'ASC',
            'orderby'     => 'name',
            'type'        => 'alphabetical',
            'show_hidden' => false
        ];
    }
}
