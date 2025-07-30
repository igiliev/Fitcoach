<?php
// template-parts/hero-section.php
// Variables are passed from the shortcode function
?>
<div class="custom-hero-section">
    <div class="hero-container">
        <div class="hero-content">
            <!-- Badge -->
            <div class="hero-badge">
                <span class="star-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="#ff6b35" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-star-icon lucide-star"><path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"/></svg>
                </span>
                <?php echo esc_html($badge_text); ?>
            </div>

            <!-- Main Heading -->
            <h1 class="hero-title">
                <?php echo esc_html($main_title); ?>
                <span class="title-highlight"><?php echo esc_html($highlight_title); ?></span>
            </h1>

            <!-- Description -->
            <p class="hero-description"><?php echo esc_html($description); ?></p>

            <!-- Buttons -->
            <div class="hero-buttons">
                <a href="<?php echo esc_url($primary_btn_url); ?>" class="btn-primary">
                    <?php echo esc_html($primary_btn_text); ?>
                </a>
                <a href="<?php echo esc_url($secondary_btn_url); ?>" class="btn-secondary">
                    <span class="play-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-play-icon lucide-play"><path d="M5 5a2 2 0 0 1 3.008-1.728l11.997 6.998a2 2 0 0 1 .003 3.458l-12 7A2 2 0 0 1 5 19z"/></svg>
                    </span>
                    <?php echo esc_html($secondary_btn_text); ?>
                </a>
            </div>

            <!-- Stats -->
            <div class="hero-stats">
                <div class="stat-item">
                    <div class="stat-icon">
                        <span class="users-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#2EA3F2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-users-icon lucide-users"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><path d="M16 3.128a4 4 0 0 1 0 7.744"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><circle cx="9" cy="7" r="4"/></svg>
                        </span>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo esc_html($stat1_number); ?></div>
                        <div class="stat-label"><?php echo esc_html($stat1_label); ?></div>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon">
                        <span class="trophy-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ff6b35" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trophy-icon lucide-trophy"><path d="M10 14.66v1.626a2 2 0 0 1-.976 1.696A5 5 0 0 0 7 21.978"/><path d="M14 14.66v1.626a2 2 0 0 0 .976 1.696A5 5 0 0 1 17 21.978"/><path d="M18 9h1.5a1 1 0 0 0 0-5H18"/><path d="M4 22h16"/><path d="M6 9a6 6 0 0 0 12 0V3a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1z"/><path d="M6 9H4.5a1 1 0 0 1 0-5H6"/></svg>
                        </span>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo esc_html($stat2_number); ?></div>
                        <div class="stat-label"><?php echo esc_html($stat2_label); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hero Image -->
        <div class="hero-image">
            <?php if($hero_image): ?>
                <img src="<?php echo esc_url($hero_image); ?>" alt="Fitness Coach" />
            <?php endif; ?>
        </div>
    </div>
</div>