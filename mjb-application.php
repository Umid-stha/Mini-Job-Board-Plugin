<?php

namespace MJB;

if(!defined('ABSPATH')){
    exit;
}

class Applications{
    public function init() {
        add_action('init', array($this, 'register_application_post_type'));
        add_shortcode( 'application', array($this, 'application_shortcode_callback'));
    }

    public function register_application_post_type() {
        $labels = [
            'name' => __('Applications', 'mjb'),
            'singular_name' => __('Application', 'mjb'),
        ];
        $args = [
            'labels' => $labels,
            'public' => true,
            'supports' => ['title'],
            'menu_icon' => 'dashicons-portfolio',
        ];
        register_post_type( 'application', $args);
    }

    public function application_shortcode_callback() {
        ob_start();
        ?>
            <div class="apply-form-wrapper">
                <h2 class="apply-title">Apply for this Job</h2>
                <p class="apply-subtitle">Fill in your details and upload your resume</p>

                <form class="apply-form" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="name" placeholder="Your Name" required>
                    </div>

                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" placeholder="email@example.com" required>
                    </div>

                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" name="phone" placeholder="98XXXXXXXX">
                    </div>

                    <div class="form-group">
                        <label>Resume</label>
                        <input type="file" name="resume" accept=".pdf,.doc,.docx" required>
                    </div>

                    <div class="form-group">
                        <label>Cover Message</label>
                        <textarea name="message" rows="4" placeholder="Why should we hire you?"></textarea>
                    </div>

                    <button type="submit" class="apply-btn">
                        Apply Now
                    </button>
                </form>
            </div>
        <?php
        return ob_get_clean();
    }
}