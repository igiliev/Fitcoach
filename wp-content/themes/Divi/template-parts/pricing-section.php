<?php
// template-parts/pricing-section.php
// Variables are passed from the shortcode function

// Define icons
$icons = array(
    'lightning' => '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 16.326A7 7 0 1 1 15.71 8h1.79a4.5 4.5 0 0 1 .5 8.973"/><path d="m13 12-3 5h4l-3 5"/></svg>',
    'star' => '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"/></svg>',
    'crown' => '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-crown-icon lucide-crown"><path d="M11.562 3.266a.5.5 0 0 1 .876 0L15.39 8.87a1 1 0 0 0 1.516.294L21.183 5.5a.5.5 0 0 1 .798.519l-2.834 10.246a1 1 0 0 1-.956.734H5.81a1 1 0 0 1-.957-.734L2.02 6.02a.5.5 0 0 1 .798-.519l4.276 3.664a1 1 0 0 0 1.516-.294z"/><path d="M5 21h14"/></svg>',
    'check' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>'
);
?>

<div class="pricing-section">
    <div class="pricing-container">
        <!-- Section Header -->
        <div class="pricing-header">
            <h2 class="pricing-title">
                <?php echo esc_html($main_title); ?> <span class="title-highlight"><?php echo esc_html($highlight_title); ?></span>
            </h2>
            <p class="pricing-subtitle"><?php echo esc_html($subtitle); ?></p>
        </div>

        <!-- Pricing Cards -->
        <div class="pricing-grid">
            <!-- Essential Plan -->
            <div class="pricing-card">
                <div class="card-icon">
                    <?php echo $icons[$essential_icon]; ?>
                </div>
                <h3 class="card-title"><?php echo esc_html($essential_title); ?></h3>
                <p class="card-description"><?php echo esc_html($essential_description); ?></p>
                <div class="card-price">
                    <span class="price"><?php echo esc_html($essential_price); ?></span>
                    <span class="period"><?php echo esc_html($essential_period); ?></span>
                </div>
                <ul class="features-list">
                    <li><?php echo $icons['check']; ?> <?php echo esc_html($essential_feature1); ?></li>
                    <li><?php echo $icons['check']; ?> <?php echo esc_html($essential_feature2); ?></li>
                    <li><?php echo $icons['check']; ?> <?php echo esc_html($essential_feature3); ?></li>
                    <li><?php echo $icons['check']; ?> <?php echo esc_html($essential_feature4); ?></li>
                    <li><?php echo $icons['check']; ?> <?php echo esc_html($essential_feature5); ?></li>
                </ul>
                <a href="<?php echo esc_url($essential_button_url); ?>" class="pricing-button">
                    <?php echo esc_html($essential_button_text); ?>
                </a>
            </div>

            <!-- Premium Plan -->
            <div class="pricing-card premium-card">
                <?php if($premium_popular === 'true'): ?>
                    <div class="popular-badge">Most Popular</div>
                <?php endif; ?>
                <div class="card-icon">
                    <?php echo $icons[$premium_icon]; ?>
                </div>
                <h3 class="card-title"><?php echo esc_html($premium_title); ?></h3>
                <p class="card-description"><?php echo esc_html($premium_description); ?></p>
                <div class="card-price">
                    <span class="price"><?php echo esc_html($premium_price); ?></span>
                    <span class="period"><?php echo esc_html($premium_period); ?></span>
                </div>
                <ul class="features-list">
                    <li><?php echo $icons['check']; ?> <?php echo esc_html($premium_feature1); ?></li>
                    <li><?php echo $icons['check']; ?> <?php echo esc_html($premium_feature2); ?></li>
                    <li><?php echo $icons['check']; ?> <?php echo esc_html($premium_feature3); ?></li>
                    <li><?php echo $icons['check']; ?> <?php echo esc_html($premium_feature4); ?></li>
                    <li><?php echo $icons['check']; ?> <?php echo esc_html($premium_feature5); ?></li>
                    <li><?php echo $icons['check']; ?> <?php echo esc_html($premium_feature6); ?></li>
                </ul>
                <a href="<?php echo esc_url($premium_button_url); ?>" class="pricing-button premium-button">
                    <?php echo esc_html($premium_button_text); ?>
                </a>
            </div>

            <!-- Elite Plan -->
            <div class="pricing-card">
                <div class="card-icon">
                    <?php echo $icons[$elite_icon]; ?>
                </div>
                <h3 class="card-title"><?php echo esc_html($elite_title); ?></h3>
                <p class="card-description"><?php echo esc_html($elite_description); ?></p>
                <div class="card-price">
                    <span class="price"><?php echo esc_html($elite_price); ?></span>
                    <span class="period"><?php echo esc_html($elite_period); ?></span>
                </div>
                <ul class="features-list">
                    <li><?php echo $icons['check']; ?> <?php echo esc_html($elite_feature1); ?></li>
                    <li><?php echo $icons['check']; ?> <?php echo esc_html($elite_feature2); ?></li>
                    <li><?php echo $icons['check']; ?> <?php echo esc_html($elite_feature3); ?></li>
                    <li><?php echo $icons['check']; ?> <?php echo esc_html($elite_feature4); ?></li>
                    <li><?php echo $icons['check']; ?> <?php echo esc_html($elite_feature5); ?></li>
                    <li><?php echo $icons['check']; ?> <?php echo esc_html($elite_feature6); ?></li>
                    <li><?php echo $icons['check']; ?> <?php echo esc_html($elite_feature7); ?></li>
                </ul>
                <a href="<?php echo esc_url($elite_button_url); ?>" class="pricing-button elite-button">
                    <?php echo esc_html($elite_button_text); ?>
                </a>
            </div>
        </div>

        <!-- Guarantee Section -->
        <div class="guarantee-section">
            <h3 class="guarantee-title"><?php echo esc_html($guarantee_title); ?></h3>
            <p class="guarantee-text"><?php echo esc_html($guarantee_text); ?></p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const pricingCards = document.querySelectorAll('.pricing-card');
    const pricingButtons = document.querySelectorAll('.pricing-button');

    // Add click event to each pricing card
    pricingCards.forEach(function(card, index) {
        card.addEventListener('click', function(e) {
            // Don't trigger if clicking the button directly
            if (e.target.classList.contains('pricing-button')) {
                return;
            }

            // Remove active class from all cards
            pricingCards.forEach(function(c) {
                c.classList.remove('active');
            });

            // Add active class to clicked card
            card.classList.add('active');

            // Update button states
            updateButtonStates();
        });
    });

    // Add click event to buttons
    pricingButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();

            // Remove active from all cards
            pricingCards.forEach(function(c) {
                c.classList.remove('active');
            });

            // Add active to parent card
            const parentCard = button.closest('.pricing-card');
            parentCard.classList.add('active');

            // Update button states
            updateButtonStates();

            // Optional: Add some action here like form submission, redirect, etc.
            console.log('Selected plan:', parentCard.querySelector('.card-title').textContent);

            // You can add actual functionality here:
            // window.location.href = button.getAttribute('href');
        });
    });

    function updateButtonStates() {
        pricingButtons.forEach(function(button) {
            const parentCard = button.closest('.pricing-card');

            if (parentCard.classList.contains('active')) {
                button.textContent = 'Selected ✓';
                button.style.pointerEvents = 'none';
            } else {
                // Reset button text based on plan
                if (parentCard.querySelector('.card-title').textContent.includes('Essential')) {
                    button.textContent = 'Choose Essential';
                } else if (parentCard.querySelector('.card-title').textContent.includes('Premium')) {
                    button.textContent = 'Choose Premium';
                } else if (parentCard.querySelector('.card-title').textContent.includes('Elite')) {
                    button.textContent = 'Choose Elite';
                }
                button.style.pointerEvents = 'auto';
            }
        });
    }
});
</script>