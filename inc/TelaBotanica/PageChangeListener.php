<?php

/*
 * This file is part of WpAlgolia library.
 * (c) Raymond Rutjes for Algolia <raymond.rutjes@algolia.com>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace WpAlgolia\TelaBotanica;

use WpAlgolia\PostsIndex;

class PageChangeListener
{
    /**
     * @var PostsIndex
     */
    private $index;

    private $postType = array( 'page', 'tb_thematique', 'tb_outil' );

    /**
     * @param PostsIndex $index
     */
    public function __construct( PostsIndex $index )
    {
        $this->index = $index;
        add_action( 'save_post', [$this, 'pushRecords'], 10, 2 );
        add_action( 'before_delete_post', [$this, 'deleteRecords'] );
        add_action( 'wp_trash_post', [$this, 'deleteRecords'] );
    }

    /**
     * @param int      $postId
     * @param \WP_Post $post
     */
    public function pushRecords( $postId, $post )
    {
        // Should be a post
        if ( !in_array( $post->post_type, $this->postType ) ) {
            return;
        }

        if ( !$this->index->getRecordsProvider()->shouldIndex( $post ) ) {
            return;
        }

        $this->index->pushRecordsForPost( $post );
    }

    /**
     * @param int $postId
     */
    public function deleteRecords( $postId )
    {
        $post = get_post( $postId );

        if ( $post instanceof \WP_Post && in_array( $post->post_type, $this->postType ) ) {
            $this->index->deleteRecordsForPost( $post );
        }
    }
}
