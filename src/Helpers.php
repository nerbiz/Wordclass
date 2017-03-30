<?php

namespace Wordclass;

class Helpers {
    /**
     * Get the URL of a featured image
     * @param  Integer  $imageid  The ID of the image
     * @param  String   $type     The type of data to return
     *                              url: the image URL
     *                              array: [url, width, height, is_intermediate]
     *                              html: an <img> element
     * @param  String   $size     The size of the image
     *                              thumbnail | medium | large | full | <custom>
     * @return String|Array
     */
    public static function getImage($imageid, $type='url', $size='full') {
        switch($type) {
            case 'url':
                return wp_get_attachment_image_url($imageid, $size);
                break;

            case 'array':
                return wp_get_attachment_image_src($imageid, $size);
                break;

            case 'html':
                return wp_get_attachment_image($imageid, $size);
                break;
            
            default:
                return '';
                break;
        }
    }



    /**
     * Wrapper for getImage, using post featured image
     */
    public static function getFeaturedImage($postid, $type='url', $size='full') {
        $imageId = get_post_thumbnail_id($postid);
        return static::getImage($imageId, $type, $size);
    }



    /**
     * Wrapper for getImage, using post meta
     */
    public static function getMetaImage($postid, $metaname, $type='url', $size='full') {
        $imageId = get_post_meta($postid, $metaname, true);
        return static::getImage($imageId, $type, $size);
    }



    /**
     * Get the slug of a taxonomy
     * @param  String|null  $name  null gets this slug, string: gets slug of given taxonomy
     * @return String
     */
    public function getTaxonomySlug($name) {
        $taxonomy = get_taxonomies(['name' => $name], 'objects');
        return $taxonomy[$name]->rewrite['slug'];
    }



    /**
     * Get a list of items, of a given taxonomy
     * @param  String  $taxonomy
     * @return Array
     */
    public static function getTaxonomyItems($taxonomy) {
        return get_terms([
            'taxonomy'   => $taxonomy,
            'hide_empty' => false
        ]);
    }



    /**
     * Get the taxonomies a post has
     * @param  Integer  $postid
     * @param  String   $taxonomy
     * @return Array
     */
    public static function getPostTaxonomies($postid, $taxonomy) {
        return wp_get_object_terms($postid, $taxonomy);
    }
}