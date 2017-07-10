<?php

/*
 * This file is part of WpAlgolia library.
 * (c) Raymond Rutjes for Algolia <raymond.rutjes@algolia.com>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace WpAlgolia\TelaBotanica;

use WpAlgolia\WpQueryRecordsProvider;

class EvenementRecordsProvider extends WpQueryRecordsProvider
{
    private $categorySlug = 'evenements';

    /**
     * @param \WP_Post $post
     *
     * @return array
     */
    public function getRecordsForPost(\WP_Post $post)
    {
        // global $sitepress;

        // $langInfo = wpml_get_language_information(null, $post->ID);

        // $current_lang = $sitepress->get_current_language(); //save current language
        // $sitepress->switch_lang($langInfo['language_code']);

        if (!$this->shouldIndex($post)) {
            return [];
        }

        $user = get_userdata($post->post_author);
        if ($user instanceof \WP_User) {
            $user_data = [
                'user_id'      => $user->ID,
                'display_name' => $user->display_name,
                'permalink'    => bp_core_get_user_domain($user->ID),
                'user_login'   => $user->user_login,
            ];
        } else {
            $user_data = [
                'user_id'       => '',
                'display_name'  => '',
                'permalink'     => '',
                'user_login'    => '',
            ];
        }
        $post_date = [
          'formatted' => sprintf(_x('%s à %s', '%s = date et %s = heure', 'telabotanica'),
            date_i18n(get_option('date_format'), get_post_time('U', false, $post)),
            date_i18n(get_option('time_format'), get_post_time('U', false, $post))
          ),
          'timestamp' => get_post_time('U', false, $post),
          'datetime'  => get_post_time('Y-m-d\\TG:i:s\\Z', true, $post)
        ];
        $comment_count = absint($post->comment_count);
        $comment_status = absint($post->comment_status);
        // $menu_order = absint($post->menu_order);

        $record = [
            'objectID'                 => (string) $post->ID,
            'post_id'                  => $post->ID,
            'post_author'              => $user_data,
            'post_date'                => $post_date,
            'post_title'               => $this->prepareTextContent(get_the_title($post->ID)),
            'post_excerpt'             => $this->prepareTextContent($post->post_excerpt),
            'post_content'             => mb_substr($this->prepareTextContent(apply_filters('the_content', $post->post_content)), 0, 600), // We only take the 600 first bytes of the content. If more is needed, content should be split across multiples records and the DISTINCT feature should be used.
            'post_status'              => $post->post_status,
            'post_name'                => $post->post_name,
            'post_parent'              => $post->post_parent,
            'post_type'                => $post->post_type,
            'permalink'                => get_permalink($post->ID),
            'comment_count'            => $comment_count,
            'comment_status'           => $comment_status,
            // 'menu_order'               => $menu_order,
            // 'guid'                     => $post->guid,
            // 'wpml'                     => $langInfo,
            //'site_id'                   => get_current_blog_id(),
            'post_classes'             => get_post_class('is-event', $post->ID),

            // specific to evenements
            'event_description'        => get_field('description', $post->ID),
            'event_is_free'            => get_field('is_free', $post->ID),
            'event_prices'             => get_field('prices', $post->ID),
            'event_contact'            => get_field('contact', $post->ID),
            'event_place'              => get_field('place', $post->ID),
        ];

        // Formatted place
        if ($record['event_place'] && function_exists('telabotanica_format_place')) {
            $record['event_place']->formatted = telabotanica_format_place($record['event_place']);
        }

        // Geoloc
        if ($record['event_place'] && $record['event_place']->latlng) {
            $record['_geoloc'] = $record['event_place']->latlng;
            unset($record['event_place']->latlng);
        }

        // Event dates
        $date_timestamp = strtotime(get_field('date', $post->ID, false));
        $date_end_timestamp = strtotime(get_field('date_end', $post->ID, false));
        $record['event_date'] = [
            'datetime' => date_i18n('Y-m-d', $date_timestamp),
            'day'      => date_i18n('j', $date_timestamp),
            'month'    => date_i18n('M', $date_timestamp),
            'year'     => date_i18n('Y', $date_timestamp),
        ];
        $record['event_date_end'] = $date_end_timestamp ? [
            'datetime' => date_i18n('Y-m-d', $date_end_timestamp),
            'day'      => date_i18n('j', $date_end_timestamp),
            'month'    => date_i18n('M', $date_end_timestamp),
            'year'     => date_i18n('Y', $date_end_timestamp),
        ] : null;

        // Push all taxonomies by default, including custom ones.
        $taxonomy_objects = get_object_taxonomies($post->post_type, 'objects');

        $record['category_links'] = [];
        foreach ($taxonomy_objects as $taxonomy) {
            $terms = get_the_terms($post->ID, $taxonomy->name);
            $terms = is_array($terms) ? $terms : [];

            $record[$taxonomy->name] = wp_list_pluck($terms, 'name');

            if ('category' == $taxonomy->name) {
                foreach ($terms as $category) {
                    $record['category_links'][] = [
                        'href' => get_category_link($category->term_id),
                        'text' => $category->name
                    ];
                }
            }
        }

        // Retrieve featured image.
        $featuredImage = get_the_post_thumbnail_url($post, 'post-thumbnail');
        $record['featured_image'] = $featuredImage ? $featuredImage : '';

        // Retrieve tags.
        $tags = wp_get_post_tags($post->ID);
        $record['tags'] = wp_list_pluck($tags, 'name');

        // $sitepress->switch_lang($current_lang); // restore previous language

        return [$record];
    }

    /**
     * @param \WP_Post $post
     *
     * @return bool
     */
    public function shouldIndex(\WP_Post $post)
    {
        // Should be in Evenements category
        $category_evenements = get_category_by_slug($this->categorySlug);
        $category = get_the_category($post->ID);
        if (empty($category)) {
            return false;
        }
        $category_parent_id = $category[0]->category_parent;
        if (! isset($category_evenements->cat_ID) || $category_evenements->cat_ID !== $category_parent_id) {
            return false;
        }

        // Should be published
        if ($post->post_status !== 'publish' || !empty($post->post_password)) {
            return false;
        }

        return true;
    }

    /**
     * @return array
     */
    protected function getDefaultQueryArgs()
    {
        return [
            'post_type'        => 'post',
            'post_status'      => 'publish',
            'suppress_filters' => true
        ];
    }

    private function prepareTextContent($content)
    {
        $content = strip_tags($content);
        $content = preg_replace('#[\n\r]+#s', ' ', $content);

        return $content;
    }
}
