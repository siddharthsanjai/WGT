<?php
//Footer File
?>

<!-- Footer Box Start -->
<div class="footer-box dark-section">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <!-- Footer Header Start -->
                <div class="footer-header">
                    <!-- Section Title Start -->
                    <div class="section-title">
                        <h2 class="text-anime-style-2" data-cursor="-opaque">International Book of Records

                            Join Our Newsletter</h2>
                    </div>
                    <!-- Section Title End -->

                    <!-- Footer Newsletter Form Start -->
                    <div class="footer-newsletter-form wow fadeInUp">
                        <form id="newslettersForm" action="#" method="POST">
                            <div class="form-group">
                                <input type="email" name="mail" class="form-control" id="mail"
                                    placeholder="Enter your email" required>
                                <button type="submit" class="btn-default btn-highlighted">subscribe</button>
                            </div>
                        </form>
                    </div>
                    <!-- Footer Newsletter Form End -->
                </div>
                <!-- Footer Header End -->
            </div>

            <div class="col-lg-4">
                <!-- About Footer Start -->
                <div class="about-footer">
                    <!-- Footer Logo Start -->
                    <div class="footer-logo">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/logo.png" alt="">
                    </div>
                    <!-- Footer Logo End -->

                    <!-- About Footer Content Start -->
                    <div class="about-footer-content">
                        <p>INTERNATIONAL BOOK OF RECORDS is a platform for the people to showcase their unique talent
                            and inspire the world to go one step further.</p>
                    </div>
                    <!-- About Footer Content End -->

                    <!-- Footer Social Link Start -->
                    <div class="footer-social-links">
                        <h3>Follow Us On:</h3>
                        <ul>
                            <li><a href="https://x.com/WGTrecords"><i class="fa-brands fa-x-twitter"></i></a></li>
                            <li><a href="https://www.instagram.com/worldgottalentbookofrecords/"><i
                                        class="fa-brands fa-instagram"></i></a></li>
                            <li><a href="https://www.linkedin.com/company/world-got-talent-book-of-records/"><i
                                        class="fa-brands fa-linkedin-in"></i></a></li>
                            <li><a href="https://www.youtube.com/@WorldGotTalentBookOfRecords"><i
                                        class="fa-brands fa-youtube"></i></a></li>
                            <li><a href="https://www.facebook.com/share/1KfTXaU9kg/"><i
                                        class="fa-brands fa-facebook-f"></i></a></li>
                        </ul>
                    </div>
                    <!-- Footer Social Link End -->
                </div>
                <!-- About Footer End -->
            </div>

            <div class="col-lg-2 col-md-3">
                <!-- Footer Links Start -->
                <div class="footer-links footer-menu">
                    <h3>quick links</h3>
                    <?php
                    wp_nav_menu(array(
                        'theme_location' => 'footer',
                        'container' => false,
                    ));
                    ?>
                </div>
                <!-- Footer Links End -->
            </div>

            <div class="col-lg-3 col-md-4">
                <!-- Footer Links Start -->
                
                <!-- Footer Links End -->
            </div>

            <div class="col-lg-3 col-md-5">
                <!-- Footer Contact Details Start -->
                <div class="footer-links footer-contact-details">
                    <h3>Get in Touch:</h3>
                    <!-- Footer Contact Item Start -->
                    <div class="footer-contact-item">
                        <div class="icon-box">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icon-location-white.svg"
                                alt="">
                        </div>
                        <div class="footer-contact-item-content">
                            <h3>Our Location</h3>
                            <p>Admin Office: G1, Ganpati Towers, Lawrence Road, Dayanand Nagar, Amritsar, Punjab, 143001
                                <br>
                                Head Office: 25 B, Divine Square, Palm Groove, Airport Road, Amritsar, Punjab, India</p>
                        </div>
                    </div>
                    <!-- Footer Contact Item End -->

                    <!-- Footer Contact Item Start -->
                    <div class="footer-contact-item">
                        <div class="icon-box">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icon-phone-white.svg"
                                alt="">
                        </div>
                        <div class="footer-contact-item-content">
                            <h3>Phone Number</h3>
                            <p><a href="tel:+91123456789">+(91) - 123 456 789</a></p>
                        </div>
                    </div>
                    <!-- Footer Contact Item End -->
                </div>
                <!-- Footer Contact Details End -->
            </div>
        </div>
    </div>

    <!-- Footer Copyright Start -->
    <div class="footer-copyright">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <!-- Footer Copyright Text Start -->
                    <div class="footer-copyright-text">
                        <p>Copyright © 2025 All Rights Reserved.</p>
                    </div>
                    <!-- Footer Copyright Text End -->
                </div>

                <div class="col-md-6">
                    <!-- Footer Privacy Policy Start -->
                    <div class="footer-privacy-policy">
                        <ul>
                            <li><a href="#">terms & condition</a></li>
                            <li><a href="#">privacy policy</a></li>
                        </ul>
                    </div>
                    <!-- Footer Privacy Policy End -->
                </div>
            </div>
        </div>
    </div>
    <!-- Footer Copyright End -->
</div>
<!-- Footer Box End -->
</footer>
<!-- Footer End -->
<?php wp_footer(); ?>
</body>

</html>