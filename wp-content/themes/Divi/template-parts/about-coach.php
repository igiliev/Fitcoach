<?php
// template-parts/about-coach.php
// Variables are passed from the shortcode function
?>
<div class="about-coach-section">
    <div class="about-container">
        <div class="about-content">
            <!-- Main Heading -->
            <h2 class="about-title">
                <?php echo esc_html($main_title); ?> <span class="title-highlight"><?php echo esc_html($highlight_title); ?></span>
            </h2>

            <!-- Description -->
            <p class="about-description"><?php echo esc_html($description); ?></p>

            <!-- Features Grid -->
            <div class="features-grid">
                <div class="feature-item">
                    <div class="feature-icon feature-icon-blue">
                        <?php echo $feature1_icon; ?>
                    </div>
                    <div class="feature-content">
                        <h3><?php echo esc_html($feature1_title); ?></h3>
                        <p><?php echo esc_html($feature1_desc); ?></p>
                    </div>
                </div>

                <div class="feature-item">
                    <div class="feature-icon feature-icon-green">
                        <?php echo $feature2_icon; ?>
                    </div>
                    <div class="feature-content">
                        <h3><?php echo esc_html($feature2_title); ?></h3>
                        <p><?php echo esc_html($feature2_desc); ?></p>
                    </div>
                </div>

                <div class="feature-item">
                    <div class="feature-icon feature-icon-orange">
                        <?php echo $feature3_icon; ?>
                    </div>
                    <div class="feature-content">
                        <h3><?php echo esc_html($feature3_title); ?></h3>
                        <p><?php echo esc_html($feature3_desc); ?></p>
                    </div>
                </div>

                <div class="feature-item">
                    <div class="feature-icon feature-icon-purple">
                        <?php echo $feature4_icon; ?>
                    </div>
                    <div class="feature-content">
                        <h3><?php echo esc_html($feature4_title); ?></h3>
                        <p><?php echo esc_html($feature4_desc); ?></p>
                    </div>
                </div>
            </div>

            <!-- Quote -->
            <blockquote class="coach-quote">
                <p>"<?php echo esc_html($quote_text); ?>"</p>
                <cite>- <?php echo esc_html($quote_author); ?></cite>
            </blockquote>
        </div>

        <!-- Image Section -->
        <div class="about-image">
            <?php if($coach_image): ?>
                <img src="<?php echo esc_url($coach_image); ?>" alt="Personal Trainer" />

                <!-- Guarantee Box -->
                <div class="guarantee-box">
                    <h4><?php echo esc_html($guarantee_title); ?></h4>
                    <p><?php echo esc_html($guarantee_text); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>