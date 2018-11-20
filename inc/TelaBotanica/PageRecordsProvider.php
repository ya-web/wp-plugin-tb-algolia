<?php

/*
 * This file is part of WpAlgolia library.
 * (c) Raymond Rutjes for Algolia <raymond.rutjes@algolia.com>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace WpAlgolia\TelaBotanica;

use WpAlgolia\WpQueryRecordsProvider;


class PageRecordsProvider extends WpQueryRecordsProvider
{
  // excluded posts and included post_types for function shouldIndex()
  private $do_not_index = array( 22035, 24192 );
  private $postType = array( 'tb_outil', 'tb_thematique' , 'page' );
  // to handly add one index for thématiques whitch is not a page but an archive page
  private $first_loop = true;

  /**
   * @param \WP_Post $post
   *
   * @return array
   */
  public function getRecordsForPost( \WP_Post $post )
  {
    if ( !$this->shouldIndex( $post ) ) {
      return [];
    }

    $post_date = [
      'formatted' => sprintf( _x( '%s à %s', '%s = date et %s = heure', 'telabotanica' ),
        date_i18n( get_option( 'date_format' ), get_post_time( 'U', false, $post ) ),
        date_i18n( get_option( 'time_format' ), get_post_time( 'U', false, $post ) )
      ),
      'timestamp' => get_post_time( 'U', false, $post ),
      'datetime'  => get_post_time( 'Y-m-d\\TG:i:s\\Z', true, $post )
    ];

    $menu_order = absint( $post->menu_order );

    // displayed post_types
    if ( 'tb_outil' === $post->post_type ) {
      $post_type = 'Outils';
    } elseif ( 'tb_thematique' === $post->post_type ) {
      $post_type = 'Thématiques';
    } else {
      $post_type = 'Autres Pages';
    }

    $record = [
      'objectID'                 => (string) $post->ID,
      'post_id'                  => $post->ID,
      'post_date'                => $post_date,
      'post_title'               => $this->prepareTextContent( get_the_title( $post->ID ) ),
      'post_subtitle'            => $this->prepareTextContent( get_metadata( 'post', $post->ID, 'cover_subtitle')[0]  ),
      'post_excerpt'             => $this->prepareTextContent( $post->post_excerpt ),
      'post_status'              => $post->post_status,
      'post_name'                => $post->post_name,
      'post_parent'              => $post->post_parent,
      'post_type'                => $post_type,
      'permalink'                => get_permalink( $post->ID ),
      'breadcrumb'               => $this->pageBreadcrumb( $post->post_parent, $post->ID , $post->post_type ),
      'menu_order'               => $menu_order,
      'post_classes'             => get_post_class( '', $post->ID ),
    ];

    if ( 20104 === $post->ID ) {// Outils
      $record = $this->prepareRecords( $this->createSpecialPagecontent( $post->ID, 'tb_outil' ), $record );

    } elseif ( 20092 === $post->ID ) {// Participer
      $record = $this->prepareRecords( $this->createSpecialPagecontent( $post->ID, 'tb_participer' ), $record );

    } else {
      $record = $this->prepareRecords( $this->createPageTextContent( $post->ID ), $record );

    }

    // Since "page" Tématique is a wordpress archive and has no record in database
    // it can't be automatically indexed, so we have to cheet adding it manually
    if( $this->first_loop ) {
      array_push($record, [
        'objectID'        => '26905',
        'post_id'         => 26905,
        'post_author'     => 2,
        'post_date'       => [ 'formatted' => '4 avril 2017 à 12 h 05 min', 'timestamp' => null, 'datetime' => '2017-04-04T21:05:05Z' ],
        'post_title'      => 'Thématique',
        'post_subtitle'   => 'Toutes les thématiques',
        'post_excerpt'    => 'Flora Data - L\'observatoire participatif de la flore - Herbiers - Toutes les informations sur les collections - Les plantes messicoles - Découvrir les plantes messicoles - Phytosociologie - Découvrir la phytosociologie - Relais locaux - Découvrir les telabotanistes relais',
        'post_content'    => 'Flora Data - L\'observatoire participatif de la flore - Herbiers - Toutes les informations sur les collections - Les plantes messicoles - Découvrir les plantes messicoles - Phytosociologie - Découvrir la phytosociologie - Relais locaux - Découvrir les telabotanistes relais - Sciences participatives - Observer et participer',
        'post_status'     => 'publish',
        'post_name'       => 'thematique',
        'post_parent'     => 0,
        'post_type_label' => 'Autres Pages',
        'permalink'       => site_url() . '/thematiques/',
        'breadcrumb'      => [ 'Thématiques' => site_url() . '/thematiques/' , 'Accueil' => site_url() ],
        'menu_order'      => 25,
        'post_classes'    => null
      ]);

      $this->first_loop = false;
    }
    return $record;
  }

  /**
   * @param \WP_Post $post
   *
   * @return bool
   */
  public function shouldIndex( \WP_Post $post )
  {
    // Should be a page or tb_thematique
    if ( !in_array( $post->post_type, $this->postType ) ) {
        return false;
    }

    if ( in_array( $post->ID, $this->do_not_index ) ) {
      return false;
    }

    // Should be published and not have a password
    if ( $post->post_status !== 'publish' || !empty( $post->post_password ) ) {
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
      'post_type'        => $this->postType,
      'post_status'      => 'publish',
      'suppress_filters' => true
    ];
  }

  private function prepareTextContent( $content )
  {
    $content = strip_tags( $content );
    $content = preg_replace( '#[\n\r]+#s', ' ', $content );

    return $content;
  }

  private function createPageTextContent( $post_id )
  {
    $meta_content[] = apply_filters( 'the_content', $post->post_content );

    // content text in code of template
    if ( 20160 === $post_id ) { // chorodep
      $meta_content[] =  'Chorologie départementale - <p>Consultez la répartition des espèces de France métropolitaine par département.<br>Ces données proviennent de la base <!--<a href="#">-->"Chorologie départementale" de Philippe Julve<!--</a>-->.<br>Signalez une nouvelle entrée ou une erreur sur le <a href="http://www.tela-botanica.org/projets-9">projet chorologie départementale</a>.</p> - Par département - Liste des taxons - Carte - ';

    } elseif ( 20164 === $post_id ) { // coel
      $meta_content[] =  'Rechercher parmi les collections - <p>Recherchez une collection botanique, une institution hébergeant un herbier, une personne ou une publication.<br>Complétez l\'inventaire sur <a href="https://www.tela-botanica.org/outils/collections-en-ligne">Collections En Ligne</a>.</p> - Résultats de la recherche - Rechercher - Rechercher une collection - Rechercher une personne - Rechercher une publication - Résultats de recherche - ';
    }

    $post_meta_components  = get_metadata( 'post', $post_id,'components' )?: array();

    $integrated_posts = array_merge(
      array_keys( $post_meta_components , 'articles' ),
      array_keys( $post_meta_components , 'tools' )
    );

    //exemples in post ID 21365 and 20114
    $postmetas = get_metadata('post', $post_id );
    $postmetas_keys = array_keys( $postmetas );

    foreach ( $postmetas_keys as $postmetas_key ) {
      if(
        preg_match( '/^((?!_).+_(?:intro|title|text|content|description))$/', $postmetas_key )
        && !preg_match('/^(?:<iframe.+<\/iframe>|video embed)$/', $postmetas[$postmetas_key][0] )
        // iframe postmetas_key is 'component_[\d]+_items_[\d]+_content'
      ) {
        array_push( $meta_content, $postmetas[$postmetas_key][0] );

      } else if ( preg_match( '/^components_([\d]+)_items$/', $postmetas_key , $matches ) && in_array( $matches[1], $integrated_posts ) ) {
        // There should be only one intergrated article in one of these items... just in case, we do the foreach
        foreach ( unserialize( $postmetas[$postmetas_key][0] ) as $integrated_posts_id ) {
          // when a post is integrated in another only its title and its excerpt are displayed
          $meta_content[] = get_the_title( $integrated_posts_id );
          if( '' !== get_the_excerpt( $integrated_posts_id ) ) {
            $meta_content[] = get_the_excerpt( $integrated_posts_id );

          }
        }
      } else if ( preg_match( '/^((?!(?:_|component_)).+(?:text|title|content|description))$/', $postmetas_key ) ) {
        // Other contents in metas (not components)
        $meta_content[] = $postmetas[$postmetas_key][0];

      }
    }

    if ( count( $meta_content ) ) {
      // remove NULL, FALSE and Empty Strings (""), but leave values of 0
      $meta_content = array_filter( $meta_content, 'strlen' );

      return $meta_content;

    } else {
      return [];

    }
  }

  // Useful for "Outils" and "Comment Participer"
  private function createSpecialPagecontent( $post_id , $custom_type )
  {
    foreach ( $this->createPageTextContent( $post_id ) as $content ) {
      $custom_content[] =  $content;
    }

    $integrated_posts = get_posts( array( 'post_type' => $custom_type , 'post_status' => 'publish' ));
    foreach ( $integrated_posts as $integrated_post ) {
      if( '' !== get_the_title( $integrated_post->ID )) {
        $custom_content[] = get_the_title( $integrated_post->ID );

      }
      foreach ( $this->createPageTextContent( $integrated_post->ID ) as $integrated_content ) {
        $custom_content[] = $integrated_content;

      }
    }

    if ( count( $custom_content ) ) {
      // remove NULL, FALSE and Empty Strings (""), but leave values of 0
      $custom_content = array_filter( $custom_content, 'strlen' );

      return $custom_content;

    } else {
      return [];

    }
  }

  // We only take the 600 first bytes of the content.
  // If more is needed, content should be split across multiples records
  // and the DISTINCT feature should be used.
  private function prepareRecords( $content_array , $record )
  {
    $remaining_content = '';

    foreach ( $content_array as $key => $content ) {
      $content = $this->prepareTextContent( $content );
      $temp_string = ('' !== $remaining_content)? $remaining_content . ' - ' . $content : $content;

      if (  600 >= strlen( $content ) ) {
        if ( 600 >= strlen( $temp_string ) ) {

          $remaining_content = $temp_string;

        } else {
          $prepared_content_array[] = $remaining_content;

          $remaining_content = $content;
        }
      } else {

        while ( 600 < strlen( $temp_string ) ) {
          $length = mb_strripos( mb_substr( $temp_string, 0, 600 ), ' ');
          $loop_content = mb_substr( $temp_string , 0, $length );
          $prepared_content_array[] = $loop_content;
          $loop_content .= ' ';

          $temp_string = str_replace( $loop_content, '', $temp_string );
        }
        $remaining_content = $temp_string;
      }
    }

    $prepared_content_array[] = $remaining_content;

    $record['post_content'] = array_shift( $prepared_content_array );
    $record['part'] = 1;
    $prepared_record[] = $record;
    // other objectIDs will be gérérated by algolia
    unset($record['objectID']);

    foreach ( $prepared_content_array as $key => $prepared_content ) {
      $record['part'] = $key + 2;
      $record['post_content'] = $prepared_content;
      array_push( $prepared_record, $record );
    }

    return $prepared_record;
  }

  private function pageBreadcrumb( $parent , $post_id , $post_type )
  {
    $breadcrumb = array( html_entity_decode( get_the_title( $post_id ) ) => get_permalink( $post_id ) );
    // try with ID = 20618
    while( $parent ) {
      $breadcrumb = array( html_entity_decode( get_the_title( $parent ) ) => get_permalink( $parent ) ) + $breadcrumb;
      $parent = wp_get_post_parent_id( $parent )?:null;
    }
    if ( 'tb_outil' === $post_type ) {
      $breadcrumb = array( 'outils' => get_permalink( 20104 ) ) + $breadcrumb;
    }
    if ( 'tb_thematique' === $post_type ) {
      $breadcrumb = array( 'thématiques' => site_url() . '/thematiques/' ) + $breadcrumb;
    }
   $breadcrumb = array( 'Accueil' => site_url() ) + $breadcrumb;

    return $breadcrumb;
  }

}
