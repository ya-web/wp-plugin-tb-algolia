<?php

/*
 * This file is part of WpAlgolia library.
 * (c) Raymond Rutjes for Algolia <raymond.rutjes@algolia.com>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace WpAlgolia\TelaBotanica;

use WpAlgolia\WpQueryRecordsProvider;

class ActualiteRecordsProvider extends WpQueryRecordsProvider
{
    private $categorySlug = 'actualites';

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
                'user_url'     => $user->user_url,
                'user_login'   => $user->user_login,
            ];
        } else {
            $user_data = [
                'raw'          => '',
                'login'        => '',
                'display_name' => '',
                'id'           => '',
            ];
        }
        $post_date = get_post_time('U', false, $post);
        $post_date_formatted = get_the_date('', $post);
        $post_date_gmt = $post->post_date_gmt;
        $post_modified = get_post_modified_time('U', false, $post);
        $comment_count = absint($post->comment_count);
        $comment_status = absint($post->comment_status);
        $ping_status = absint($post->ping_status);
        // $menu_order = absint($post->menu_order);

        $record = [
            'objectID'                 => (string) $post->ID,
            'post_id'                  => $post->ID,
            'post_author'              => $user_data,
            'post_date'                => $post_date,
            'post_date_formatted'      => $post_date_formatted,
            'post_date_gmt'            => $post_date_gmt,
            'post_title'               => $this->prepareTextContent(get_the_title($post->ID)),
            'post_excerpt'             => $this->prepareTextContent($post->post_excerpt),
            'post_content'             => mb_substr($this->prepareTextContent(apply_filters('the_content', $post->post_content)), 0, 600), // We only take the 600 first bytes of the content. If more is needed, content should be split across multiples records and the DISTINCT feature should be used.
            'post_status'              => $post->post_status,
            'post_name'                => $post->post_name,
            'post_modified'            => $post_modified,
            'post_parent'              => $post->post_parent,
            'post_type'                => $post->post_type,
            'post_mime_type'           => $post->post_mime_type,
            'permalink'                => get_permalink($post->ID),
            'comment_count'            => $comment_count,
            'comment_status'           => $comment_status,
            // 'ping_status'              => $ping_status,
            // 'menu_order'               => $menu_order,
            'guid'                     => $post->guid,
            // 'wpml'                     => $langInfo,
            //'site_id'                   => get_current_blog_id(),
        ];

        // Push all taxonomies by default, including custom ones.
        $taxonomy_objects = get_object_taxonomies($post->post_type, 'objects');

        // $taxonomies_hierarchical = array();
        foreach ($taxonomy_objects as $taxonomy) {
            $terms = get_the_terms($post->ID, $taxonomy->name);
            $terms = is_array($terms) ? $terms : [];

            // if ( $taxonomy->hierarchical ) {
            // 	$taxonomies_hierarchical[ $taxonomy->name ] = Utils::get_taxonomy_tree( $terms, $taxonomy->name );
            // }

            $record[$taxonomy->name] = wp_list_pluck($terms, 'name');
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
        // Should be in Actualites category
        $category_actualites = get_category_by_slug($this->categorySlug);
        $category = get_the_category($post->ID);
        if (empty($category)) {
            return false;
        }
        $category_parent_id = $category[0]->category_parent;
        if ($category_actualites->cat_ID !== $category_parent_id) {
            return false;
        }

        // Should be published and not have a password
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
