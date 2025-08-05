<?php
// template-parts/testimonials-section.php
// Variables are passed from the shortcode function

$star_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="#ff6b35" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-star-icon lucide-star"><path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"/></svg>';
?>
<div class="testimonials-section">
    <div class="testimonials-container">
        <!-- Section Header -->
        <div class="testimonials-header">
            <div class="header-wrapper">
                <h2 class="testimonials-title">
                    <?php echo esc_html($main_title); ?>
                </h2>
                <span class="title-highlight"><?php echo esc_html($highlight_title); ?></span>
            </div>
            <p class="testimonials-subtitle"><?php echo esc_html($subtitle); ?></p>
        </div>

        <!-- Testimonials Grid -->
        <div class="testimonials-grid">
            <!-- Testimonial 1 -->
            <div class="testimonial-card">
                <div class="testimonial-quote-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h4v10h-10z"/>
                    </svg>
                </div>
                <div class="testimonial-rating">
                    <?php for($i=1; $i<=5; $i++): ?>
                        <span class="star filled"><?php echo $star_icon; ?></span>
                    <?php endfor; ?>
                </div>
                <blockquote class="testimonial-quote">
                    "<?php echo esc_html($testimonial1_quote); ?>"
                </blockquote>
                <div class="testimonial-author">
                    <?php if($testimonial1_image): ?>
                        <img src="<?php echo esc_url($testimonial1_image); ?>" alt="<?php echo esc_attr($testimonial1_name); ?>" class="author-image">
                    <?php else: ?>
                        <div class="author-avatar"><?php echo substr($testimonial1_name, 0, 1); ?></div>
                    <?php endif; ?>
                    <div class="author-info">
                        <h4 class="author-name"><?php echo esc_html($testimonial1_name); ?></h4>
                        <p class="author-result"><?php echo esc_html($testimonial1_result); ?></p>
                    </div>
                </div>
            </div>

            <!-- Testimonial 2 -->
            <div class="testimonial-card">
                <div class="testimonial-quote-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h4v10h-10z"/>
                    </svg>
                </div>
                <div class="testimonial-rating">
                    <?php for($i=1; $i<=5; $i++): ?>
                        <span class="star filled"><?php echo $star_icon; ?></span>
                    <?php endfor; ?>
                </div>
                <blockquote class="testimonial-quote">
                    "<?php echo esc_html($testimonial2_quote); ?>"
                </blockquote>
                <div class="testimonial-author">
                    <?php if($testimonial2_image): ?>
                        <img src="<?php echo esc_url($testimonial2_image); ?>" alt="<?php echo esc_attr($testimonial2_name); ?>" class="author-image">
                    <?php else: ?>
                        <div class="author-avatar"><?php echo substr($testimonial2_name, 0, 1); ?></div>
                    <?php endif; ?>
                    <div class="author-info">
                        <h4 class="author-name"><?php echo esc_html($testimonial2_name); ?></h4>
                        <p class="author-result"><?php echo esc_html($testimonial2_result); ?></p>
                    </div>
                </div>
            </div>

            <!-- Testimonial 3 -->
            <div class="testimonial-card">
                <div class="testimonial-quote-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h4v10h-10z"/>
                    </svg>
                </div>
                <div class="testimonial-rating">
                    <?php for($i=1; $i<=5; $i++): ?>
                        <span class="star filled"><?php echo $star_icon; ?></span>
                    <?php endfor; ?>
                </div>
                <blockquote class="testimonial-quote">
                    "<?php echo esc_html($testimonial3_quote); ?>"
                </blockquote>
                <div class="testimonial-author">
                    <?php if($testimonial3_image): ?>
                        <img src="<?php echo esc_url($testimonial3_image); ?>" alt="<?php echo esc_attr($testimonial3_name); ?>" class="author-image">
                    <?php else: ?>
                        <div class="author-avatar"><?php echo substr($testimonial3_name, 0, 1); ?></div>
                    <?php endif; ?>
                    <div class="author-info">
                        <h4 class="author-name"><?php echo esc_html($testimonial3_name); ?></h4>
                        <p class="author-result"><?php echo esc_html($testimonial3_result); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="testimonials-cta">
            <h3 class="cta-title"><?php echo esc_html($cta_title); ?></h3>
            <p class="cta-subtitle"><?php echo esc_html($cta_subtitle); ?></p>
            <a href="<?php echo esc_url($cta_button_url); ?>" class="cta-button">
                <?php echo esc_html($cta_button_text); ?>
            </a>
        </div>
    </div>
</div>