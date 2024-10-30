<?php

/*
Plugin Name: Content Molecules
Plugin URI: http://www.marcuspope.com/wordpress/
Description: Enables the creation of reusable and dynamic content that can be embedded throughout the Wordpress platform via shortcodes.
Author: Marcus E. Pope, marcuspope
Author URI: http://www.marcuspope.com
Version: 1.3

Copyright 2011 Marcus E. Pope (email : me@marcuspope.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

add_action('init', array('EMC2_Molecule', 'init'));

register_activation_hook(__FILE__, array("EMC2_Molecule", "flush_rewrites"));

class EMC2_Molecule {
    
    //cached indexes used for faster data lookups
    public static $elements;
    
    //property map for public namespace variables
    public static $props = array(
        'post_type' => 'emc2_molecule_pt', //cannot be longer than 20 characters... why?!
        'meta_box' => 'emc2_content_molecules_meta',
        'transient' => 'emc2_molecule_cache'
    );


    public static function flush_rewrites() {
        //After creating the custom post type, flush the rewrite rules to ensure proper access
        EMC2_Molecule::init();
        flush_rewrite_rules();
    }
    
    public static function get_rendered_content($id, $atts = array()) {
        
        //Renders a dynamic sidebar and or content post type 
        if (!isset($id)) return "";

        $html = '';
        if (array_key_exists($id, EMC2_Molecule::$elements)) {
            $html = EMC2_Molecule::$elements[$id]->post_content;
            foreach ($atts as $k => $v) {
                $html = preg_replace('/{' . $k . '}/i', $v, $html);
            }
            $html = preg_replace('/{[_0-9a-z]+}/i', "", $html);
        }
        else {
            //autogenerate draft molecule for this content
            $user = get_userdatabylogin('admin');
            
            wp_insert_post(array(
                'comment_status' => 'closed',
                'ping_status' => 'closed',
                'post_author' => $user->ID,
                'post_content' => "<!--UNUSED CONTENT MOLECULE -->",
                'post_excerpt' => "",
                'post_name' => strtolower($id),
                'post_status' => 'draft',
                'post_title' => "Unused Molecule: " . $id,
                'post_type' => EMC2_Molecule::$props['post_type']
            ));
        }

        //process any shortcodes contained within content molecules
        //and mark the molecule in the html source for easier debugging
        return "<!-- MOLECULE: " . $id . "-->\r\n" . do_shortcode($html);
    }

    public static function render($id) {
        echo EMC2_Molecule::get_rendered_content($id);
    }
    
    static function is_save($id, $type = null) {
        //returns true if this is a full user-actioned save
        if (wp_is_post_autosave($id)) return;                  //ignore autosaves
        if (wp_is_post_revision($id)) return;                  //ignore revision updates
        if (array_key_exists("_inline_edit", $_POST)) return;  //ignore inline edits from list manager
        if (isset($type) && !isset($_POST['post_type']) || $_POST['post_type'] != $type) return; 
        
        return true; //gotta be the right hook, right?
    }

    static function reset_transient_cache($t = 'asdf') {
        //handler / filter called any time a molecule is modified in any way
        delete_transient(EMC2_Molecule::$props['transient']);
    }
    
    static function manage_posts_columns_handler($cols) {
        $sort = array();
        //Inject slug just before date column
        foreach ($cols as $k => $v) {
            if ($k == "date") {
                $sort['slug'] = __("Slug");
                $sort['author'] = __("Author");
            }
            $sort[$k] = $v;
        }
        
        return $sort;
    }
    
    static function manage_posts_custom_column_handler($col_name) {
        global $post;
        if ($col_name = "slug") echo $post->post_name;
    }
    
    static function manage_column_sorting_handler($cols) {
        $cols['slug'] = 'slug';
        return $cols;
    }
    
    static function custom_colum_sort_param_handler($vars) {
        //customize request to support sorting by slug
        if (isset($vars['orderby']) && $vars['orderby'] == 'slug') {
            $vars = array_merge($vars, array(
                'meta_key' => '',
                'orderby'  => 'name'
            ));
        }
        return $vars;
    }
    
    static function init_admin() {
        //save custom properties on this custom post type
        if (@$_REQUEST['post_type'] == EMC2_Molecule::$props['post_type']) {
            
            $mod_actions = array('deleted_post', 'trashed_post', 'edit_post', 'publish_post', 'publish_future_post');
            foreach($mod_actions as $action) {
                add_filter($action, array('EMC2_Molecule', 'reset_transient_cache'));   
            }
            
            //customize columns of molecule list view
            add_filter('manage_posts_columns', array('EMC2_Molecule', 'manage_posts_columns_handler'));
            add_filter('manage_posts_custom_column', array('EMC2_Molecule', 'manage_posts_custom_column_handler'));
            add_filter('manage_edit-emc2_molecule_pt_sortable_columns', array('EMC2_Molecule', 'manage_column_sorting_handler'));
            add_filter("request", array('EMC2_Molecule', 'custom_colum_sort_param_handler'));
        }
    }

    static function add_shortcode_handler($atts, $content) {
        //create shortcode for handling molecules in post and page content
        $atts = array_merge($atts, shortcode_atts(array(
            'id' => '',
            'content' => $content
        ), $atts));
        
        return EMC2_Molecule::get_rendered_content($atts['id'], $atts);
    }        

    static function init() {
        //Custom post type Content Molecules - used to allow admin management of aside content
        register_post_type(EMC2_Molecule::$props['post_type'], array(
            'labels' => array(
                'name' => __('Molecules'),
                'singular_name' => __('Molecule'),
                'all_items' => __('All Molecules'),
                'add_new_item' => __('Add New Molecule'),
                'edit_item' => __('Edit Molecule'),
                'view_item' => __('View Molecule'),
                'search_items' => __('Search Molecules'),
                'not_found' => __('No Molecules Defined'),
                'not_found_in_trash' => __('No Molecules found in Trash'),
            ),
            'menu_position' => 60,
            'public' => true,
            'publicly_queryable' => true,
            'has_archive' => true,
            'rewrite' => array(
                'slug' => 'molecule'               
            ),
            'exclude_from_search' => true,
            'supports' => array(
                'title', 'editor','excerpt', 'revisions'
            )
        ));

        add_shortcode("m", array('EMC2_Molecule', 'add_shortcode_handler'));
        
        if (!isset(EMC2_Molecule::$elements)) {
            if ((EMC2_Molecule::$elements = get_transient(EMC2_Molecule::$props['transient'])) === false) {
                //query all content_molecules for cache!
                $q = new WP_Query(array(
                    'post_type' => EMC2_Molecule::$props['post_type'],
                    'post_status' => 'any',
                    'post_count' => -1,
                    'posts_per_page' => -1
                ));
    
                foreach ($q->posts as $p) {
                    //cache all content molecules by their slug
                    EMC2_Molecule::$elements[$p->post_name] = $p;
                }
                
                //store molecules for a while, don't worry, we'll kill the cache when the molecule set is modified in any way.
                set_transient(EMC2_Molecule::$props['transient'], EMC2_Molecule::$elements, 60*60*24*7);    
            }
        }
        
        if (is_admin()) {
            //add customized fields for molecule administration
            EMC2_Molecule::init_admin();
        }
    }
}