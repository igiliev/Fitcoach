<?php
// template-parts/programs-section.php
// Variables are passed from the shortcode function
?>
<div class="programs-section">
    <div class="programs-container">
        <!-- Section Header -->
        <div class="programs-header">
            <h2 class="programs-title">
                <?php echo esc_html($main_title); ?> <span class="title-highlight"><?php echo esc_html($highlight_title); ?></span>
            </h2>
            <p class="programs-subtitle"><?php echo esc_html($subtitle); ?></p>
        </div>

        <!-- Programs Grid -->
        <div class="programs-grid">
            <!-- Program 1 -->
            <div class="program-card program-<?php echo esc_attr($program1_color); ?>">
                <div class="program-header">
                    <div class="program-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <circle cx="12" cy="12" r="6"/>
                            <circle cx="12" cy="12" r="2"/>
                        </svg>
                    </div>
                    <div class="program-duration">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12,6 12,12 16,14"/>
                        </svg>
                        <?php echo esc_html($program1_duration); ?>
                    </div>
                </div>

                <div class="program-content">
                    <h3 class="program-title"><?php echo esc_html($program1_title); ?></h3>
                    <p class="program-description"><?php echo esc_html($program1_description); ?></p>
                    <p class="program-intensity"><?php echo esc_html($program1_intensity); ?></p>

                    <ul class="program-features">
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 6L9 17l-5-5"/>
                            </svg>
                            <?php echo esc_html($program1_feature1); ?>
                        </li>
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 6L9 17l-5-5"/>
                            </svg>
                            <?php echo esc_html($program1_feature2); ?>
                        </li>
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 6L9 17l-5-5"/>
                            </svg>
                            <?php echo esc_html($program1_feature3); ?>
                        </li>
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 6L9 17l-5-5"/>
                            </svg>
                            <?php echo esc_html($program1_feature4); ?>
                        </li>
                    </ul>

                    <a href="<?php echo esc_url($program1_button_url); ?>" class="program-button">
                        <?php echo esc_html($program1_button_text); ?>
                    </a>
                </div>
            </div>

            <!-- Program 2 -->
            <div class="program-card program-<?php echo esc_attr($program2_color); ?>">
                <div class="program-header">
                    <div class="program-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 2v20l6-6 6 6V2z"/>
                        </svg>
                    </div>
                    <div class="program-duration">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12,6 12,12 16,14"/>
                        </svg>
                        <?php echo esc_html($program2_duration); ?>
                    </div>
                </div>

                <div class="program-content">
                    <h3 class="program-title"><?php echo esc_html($program2_title); ?></h3>
                    <p class="program-description"><?php echo esc_html($program2_description); ?></p>
                    <p class="program-intensity"><?php echo esc_html($program2_intensity); ?></p>

                    <ul class="program-features">
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 6L9 17l-5-5"/>
                            </svg>
                            <?php echo esc_html($program2_feature1); ?>
                        </li>
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 6L9 17l-5-5"/>
                            </svg>
                            <?php echo esc_html($program2_feature2); ?>
                        </li>
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 6L9 17l-5-5"/>
                            </svg>
                            <?php echo esc_html($program2_feature3); ?>
                        </li>
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 6L9 17l-5-5"/>
                            </svg>
                            <?php echo esc_html($program2_feature4); ?>
                        </li>
                    </ul>

                    <a href="<?php echo esc_url($program2_button_url); ?>" class="program-button">
                        <?php echo esc_html($program2_button_text); ?>
                    </a>
                </div>
            </div>

            <!-- Program 3 -->
            <div class="program-card program-<?php echo esc_attr($program3_color); ?>">
                <div class="program-header">
                    <div class="program-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                        </svg>
                    </div>
                    <div class="program-duration">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12,6 12,12 16,14"/>
                        </svg>
                        <?php echo esc_html($program3_duration); ?>
                    </div>
                </div>

                <div class="program-content">
                    <h3 class="program-title"><?php echo esc_html($program3_title); ?></h3>
                    <p class="program-description"><?php echo esc_html($program3_description); ?></p>
                    <p class="program-intensity"><?php echo esc_html($program3_intensity); ?></p>

                    <ul class="program-features">
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 6L9 17l-5-5"/>
                            </svg>
                            <?php echo esc_html($program3_feature1); ?>
                        </li>
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 6L9 17l-5-5"/>
                            </svg>
                            <?php echo esc_html($program3_feature2); ?>
                        </li>
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 6L9 17l-5-5"/>
                            </svg>
                            <?php echo esc_html($program3_feature3); ?>
                        </li>
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 6L9 17l-5-5"/>
                            </svg>
                            <?php echo esc_html($program3_feature4); ?>
                        </li>
                    </ul>

                    <a href="<?php echo esc_url($program3_button_url); ?>" class="program-button">
                        <?php echo esc_html($program3_button_text); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>