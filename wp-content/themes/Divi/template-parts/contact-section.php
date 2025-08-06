<?php
// template-parts/contact-section.php
// Variables are passed from the shortcode function

// Contact icons
$phone_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>';

$email_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>';

$whatsapp_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>';

$time_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12,6 12,12 16,14"/></svg>';

$send_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22,2 15,22 11,13 2,9 22,2"/></svg>';
?>

<div class="contact-section">
    <div class="contact-container">
        <!-- Section Header -->
        <!-- Contact Content -->
        <div class="contact-content">
            <!-- Left Column - Contact Info -->
            <div class="contact-info">
                <h3 class="info-title">Get In Touch</h3>

                <div class="contact-items">
                    <div class="contact-item">
                        <div class="contact-item-icon phone-icon">
                            <?php echo $phone_icon; ?>
                        </div>
                        <div class="contact-item-content">
                            <h4>Phone</h4>
                            <p><?php echo esc_html($phone); ?></p>
                        </div>
                    </div>

                    <div class="contact-item">
                        <div class="contact-item-icon email-icon">
                            <?php echo $email_icon; ?>
                        </div>
                        <div class="contact-item-content">
                            <h4>Email</h4>
                            <p><?php echo esc_html($email); ?></p>
                        </div>
                    </div>

                    <div class="contact-item">
                        <div class="contact-item-icon whatsapp-icon">
                            <?php echo $whatsapp_icon; ?>
                        </div>
                        <div class="contact-item-content">
                            <h4>WhatsApp</h4>
                            <p><?php echo esc_html($whatsapp); ?></p>
                        </div>
                    </div>

                    <div class="contact-item">
                        <div class="contact-item-icon time-icon">
                            <?php echo $time_icon; ?>
                        </div>
                        <div class="contact-item-content">
                            <h4>Response Time</h4>
                            <p><?php echo esc_html($response_time); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Free Consultation Box -->
                <div class="consultation-box">
                    <h4 class="consultation-title"><?php echo esc_html($consultation_title); ?></h4>
                    <p class="consultation-description"><?php echo esc_html($consultation_description); ?></p>
                    <a href="<?php echo esc_url($consultation_button_url); ?>" class="consultation-button">
                        <?php echo esc_html($consultation_button_text); ?>
                    </a>
                </div>
            </div>

            <!-- Right Column - Contact Form -->
            <div class="contact-form-wrapper">
                <form class="contact-form" action="<?php echo esc_attr($form_action); ?>" method="<?php echo esc_attr($form_method); ?>">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input class="form-input" type="text" id="first_name" name="first_name" placeholder="John" required>
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input class="form-input" type="text" id="last_name" name="last_name" placeholder="Doe">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email_address">Email Address</label>
                        <input class="form-input" type="email" id="email_address" name="email_address" placeholder="john@example.com" required>
                    </div>

                    <div class="form-group">
                        <label for="phone_number">Phone Number</label>
                        <input class="form-input" type="tel" id="phone_number" name="phone_number" placeholder="+1 (555) 123-4567">
                    </div>

                    <div class="form-group">
                        <label for="fitness_goals">Fitness Goals</label>
                        <select id="fitness_goals" class="form-input" name="fitness_goals">
                            <option value="">Select your primary goal</option>
                            <option value="weight_loss">Weight Loss</option>
                            <option value="muscle_building">Muscle Building</option>
                            <option value="athletic_performance">Athletic Performance</option>
                            <option value="general_fitness">General Fitness</option>
                            <option value="nutrition_guidance">Nutrition Guidance</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="current_situation">Tell me about your current situation</label>
                        <textarea id="current_situation" name="current_situation" placeholder="Tell me about your current fitness level, any challenges you're facing, and what you hope to achieve..." rows="4"></textarea>
                    </div>

                    <button type="submit" class="form-submit-button">
                        <?php echo $send_icon; ?>
                        <?php echo esc_html($send_button_text); ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>