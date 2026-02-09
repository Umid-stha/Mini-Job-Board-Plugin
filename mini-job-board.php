<?php 
/*
*Plugin Name: Mini Job Board
*Description: A simple Lightweight job board
*Version: 1.0.0
*Author: Umid Shrestha
*/

namespace MJB;

if (!defined('ABSPATH')) {
    exit;
}

require_once plugin_dir_path(__FILE__) . 'mjb-application.php';

class MiniJobBoard {
    public function __construct() {

        add_action('init', array($this, 'register_job_post_type'));
        
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));

        register_activation_hook(__FILE__, array($this, 'activate'));

        register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        add_action('init', array($this, 'create_job_type_taxonomy'));
        add_action('init', array($this, 'create_job_location_taxonomy'));

        add_action('add_meta_boxes', array($this, 'add_job_meta_boxes'));

        add_action('save_post', array($this, 'save_meta_fields'));

        add_action('admin_enqueue_scripts', function($hook) {
            global $post;
            if( ($hook === 'post-new.php' || $hook === 'post.php') && $post->post_type === 'job' ) {
                wp_enqueue_editor();
                wp_enqueue_style(
                    'job-admin-css',
                    plugin_dir_url(__FILE__) . '../assets/admin.css'
                );
            }
        });

        $application = new Applications();
        $application->init();
    }

    public function register_job_post_type() {
        $labels = array(
            'name' => 'Jobs',
            'singular_name' => 'Job',
            'add_new' => 'Add New Job',
            'add_new_item' => 'Add New Job',
            'edit_item' => 'Edit Job',
            'new_item' => 'New Job',
            'view_item' => 'View Job',
            'search_items' => 'Search Jobs',
            'not_found' => 'No jobs found',
            'not_found_in_trash' => 'No jobs found in Trash',
            'all_items' => 'All Jobs',
            'menu_name' => 'Job Board',
            'name_admin_bar' => 'Job Board'
        );

        $args = array(
            'labels' => $labels,
            'public' => true,
            'has_archive' => true,
            'supports' => array('title', 'editor'),
            'menu_position' => 5,
            'menu_icon' => 'dashicons-businessman',
            
        );

        register_post_type('job', $args);
    }

    public function activate() {
        $this->register_job_post_type();
        flush_rewrite_rules();
    }

    public function deactivate() {
        flush_rewrite_rules();
    }

    public function enqueue_styles() {
        wp_enqueue_style('mini-job-board-style', plugin_dir_url(__FILE__) . 'assets/style.css');
    }

    public function create_job_type_taxonomy() {
        $labels = array(
		    'name'              => _x( 'Job Types', 'taxonomy general name' ),
            'singular_name'     => _x( 'Job Type', 'taxonomy singular name' ),
            'search_items'      => __( 'Search Job Type' ),
            'all_items'         => __( 'All Job Types' ),
            'edit_item'         => __( 'Edit Job Type' ),
            'add_new_item'      => __( 'Add New Job Type' ),
            'menu_name'         => __( 'Job Type' ),
        );
        $args   = array(
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => [ 'slug' => 'job_type' ],
        );
        register_taxonomy( 'job_type', [ 'job' ], $args );
    }

    public function create_job_location_taxonomy() {
        $labels = array(
		    'name'              => _x( 'Job Locations', 'taxonomy general name' ),
            'singular_name'     => _x( 'Job Location', 'taxonomy singular name' ),
            'search_items'      => __( 'Search Job Location' ),
            'all_items'         => __( 'All Job Locations' ),
            'edit_item'         => __( 'Edit Job Location' ),
            'add_new_item'      => __( 'Add New Job Location' ),
            'menu_name'         => __( 'Job Location' ),
    );
        $args   = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'query_var'         => true,
            'rewrite'           => [ 'slug' => 'job_location' ],
        );
        register_taxonomy( 'job_location', [ 'job' ], $args );
    }

    public function add_job_meta_boxes() {
        remove_post_type_support('job', 'editor'); // Remove default editor

        add_meta_box(
            'job_description',       // meta box ID
            'Job Description',       // custom label
            array($this, 'job_description_callback'), // callback to render editor
            'job',                   // post type
            'normal',                // context
            'high'  
            );

        $screen = 'job';

        add_meta_box(
            'job_company_name',
            'Company Name',
            array($this, 'company_name_html'),
            $screen
        );
        add_meta_box(
            'job_information',
            'Job Information',
            array($this, 'job_information_html'),
            $screen
        );

        }

    public function company_name_html( $post ) {
        $value = get_post_meta( $post->ID, 'company_name', true);
        ?>
         <div class="job-meta-field">
            <label class="job-meta-label" for="company_name">Company Name:</label>
            <input 
                class="job-meta-input" 
                name="company_name" 
                type="text" 
                id="company_name" 
                value="<?= esc_attr( $value ) ?>"
            />
        </div>
        <?php 
    }

    public function job_information_html($post) {
        $salary = get_post_meta( $post->ID, 'salary', true);
        ?>
        <div class="job-meta-field">
            <label class="job-meta-label" for="salary">Salary:</label>
            <input 
                class="job-meta-input" 
                name="salary" 
                type="number" 
                id="salary" 
                value="<?= esc_attr( $salary ) ?>"
            />
        </div>
        <?php
    }

    public function job_description_callback($post) {
        $content = get_post_meta($post->ID, 'job_description', true); // meta key
        wp_editor(
            $content,
            'job_description',       // editor ID
            [
                'wpautop' => true,
                'textarea_name' => 'job_description', // important to save later
                'media_buttons' => false,
                'textarea_rows' => 10,
                'tinymce' => false,
                'quicktags' => true,
            ]
        );
    }

    public function save_meta_fields( $post_id ) {
        if(array_key_exists('company_name', $_POST)) {
            $company_name = sanitize_text_field($_POST['company_name']);
            update_post_meta( $post_id, 'company_name', $company_name);
        }
        if(array_key_exists('job_description', $_POST)) {
            $description= sanitize_text_field($_POST['job_description']);
            update_post_meta( $post_id, 'job_description', $description);
        }
        if(array_key_exists('salary', $_POST)) {
            $salary = sanitize_text_field($_POST['salary']);
            update_post_meta( $post_id, 'salary', $salary);
        }
    }

}

new MiniJobBoard();
