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
                        <span class="award-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-award-icon lucide-award"><path d="m15.477 12.89 1.515 8.526a.5.5 0 0 1-.81.47l-3.58-2.687a1 1 0 0 0-1.197 0l-3.586 2.686a.5.5 0 0 1-.81-.469l1.514-8.526"/><circle cx="12" cy="8" r="6"/></svg>
                        </span>
                    </div>
                    <div class="feature-content">
                        <h3><?php echo esc_html($feature1_title); ?></h3>
                        <p><?php echo esc_html($feature1_desc); ?></p>
                    </div>
                </div>

                <div class="feature-item">
                    <div class="feature-icon feature-icon-green">
                        <span class="award-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-book-open-icon lucide-book-open"><path d="M12 7v14"/><path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"/></svg>
                        </span>
                    </div>
                    <div class="feature-content">
                        <h3><?php echo esc_html($feature2_title); ?></h3>
                        <p><?php echo esc_html($feature2_desc); ?></p>
                    </div>
                </div>

                <div class="feature-item">
                    <div class="feature-icon feature-icon-orange">
                        <span class="award-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-target-icon lucide-target"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg>
                        </span>
                    </div>
                    <div class="feature-content">
                        <h3><?php echo esc_html($feature3_title); ?></h3>
                        <p><?php echo esc_html($feature3_desc); ?></p>
                    </div>
                </div>

                <div class="feature-item">
                    <div class="feature-icon feature-icon-purple">
                        <span class="award-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-heart-icon lucide-heart"><path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"/></svg>
                        </span>
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