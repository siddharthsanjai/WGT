<?php get_header();
?>
<!-- Preloader Start -->
<div class="preloader">
    <div class="loading-container">
        <div class="loading"></div>
        <div id="loading-icon"><img src="<?php echo get_template_directory_uri(); ?>/images/loader.svg" alt=""></div>
    </div>
</div>
<!-- Preloader End -->

<!-- Page Header Start -->
<div class="page-header">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <!-- Page Header Box Start -->
                <div class="page-header-box">
                    <h1 class="text-anime-style-2" data-cursor="-opaque">Contact <span>us</span></h1>
                    <nav class="wow fadeInUp">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="./">home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Contact us</li>
                        </ol>
                    </nav>
                </div>
                <!-- Page Header Box End -->
            </div>
        </div>
    </div>
</div>
<!-- Page Header End -->

<!-- Scrolling Ticker Section Start -->

<!-- Scrolling Ticker Section End -->

<!-- Page Contact Us Start -->
<div class="page-contact-us">
    <div class="container">
        <div class="row section-row">
            <div class="col-lg-12">
                <!-- Section Title Start -->
                <div class="section-title section-title-center">

                    <h3 class="wow fadeInUp" data-wow-delay="0.2s">contact us</h3>
                    <h2 class="text-anime-style-2" data-cursor="-opaque">Contact <span>With Us</span></h2>
                </div>
                <!-- Section Title End -->
            </div>
        </div>

        <div class="row align-items-center">
            <div class="col-lg-6">
                <!-- Contact Us Image Start -->
                <div class="contact-us-image">
                    <figure class="image-anime reveal">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/contactus.avif"
                            alt="">
                    </figure>
                </div>
                <!-- Contact Us Image End -->
            </div>

            <div class="col-lg-6">
                <!-- Contact Info List Start -->
                <div class="contact-info-list">
                    <!-- Contact Info Item Start -->
                    <div class="contact-info-item wow fadeInUp">
                        <div class="icon-box">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icon-phone-white.svg"
                                alt="">
                        </div>
                        <div class="contact-info-content">
                            <h3>Phone Number</h3>
                            <p><a href="tel:+91-7973418589">+91 - 7973418589</a></p>
                            <p><a href="tel:+91-7973418589">+91 - 7973418589</a></p>
                        </div>
                    </div>
                    <!-- Contact Info Item End -->

                    <!-- Contact Info Item Start -->
                    <div class="contact-info-item wow fadeInUp" data-wow-delay="0.2s">
                        <div class="icon-box">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icon-mail-white.svg"
                                alt="">
                        </div>
                        <div class="contact-info-content">
                            <h3>E-mail address</h3>
                            <p><a
                                    href="mailto:enquiry@internationalbookofrecords.com">enquiry@internationalbookofrecords.com</a>
                            </p>
                            <!-- <p><a href="mailto:support@domainname.com">support@domainname.com</a></p> -->
                        </div>
                    </div>
                    <!-- Contact Info Item End -->

                    <!-- Contact Info Item Start -->
                    <div class="contact-info-item wow fadeInUp" data-wow-delay="0.4s">
                        <div class="icon-box">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icon-location-white.svg"
                                alt="">
                        </div>
                        <div class="contact-info-content">
                            <h3>location</h3>
                            
                            <h6>Head Office:</h6>
                            <p>25 B, Divine Square, Palm Groove , airport road, Amritsar, Punjab, India</p>
                            <h6>Admin Office:</h6>
                            <p>G1, Ganpati Towers, Lawrence Road, Dayanand Nagar, Amritsar, Punjab
                            143001</p>
                        </div>
                    </div>
                    <!-- Contact Info Item End -->
                </div>
                <!-- Contact Info List End -->
            </div>

            <div class="col-lg-12">
                <!-- Contact Us Form Start -->
                <div class="conatct-us-form">
                    <!-- Google Map Iframe Start -->
                    <div class="google-map order-lg-1 order-2">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d13585.853385096718!2d74.86048579216002!3d31.64854089121508!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x390d04ec315d913d%3A0xf5f80c8f68861389!2sInternational%20Book%20Of%20Records!5e0!3m2!1sen!2sin!4v1772019066946!5m2!1sen!2sin" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                    <!-- Google Map Iframe End -->

                    <!-- Contact Form Start -->
                    <div class="contact-form dark-section order-lg-1 order-1">
                        <!-- Section Title Start -->
                        <div class="section-title">
                            <h2 class="text-anime-style-2" data-cursor="-opaque">send us message</h2>
                        </div>
                        <!-- Section Title End -->

                        <!-- Contact Form Start -->
                        <form id="contactForm" action="#" method="POST" data-toggle="validator" class="wow fadeInUp"
                            data-wow-delay="0.2s">
                            <div class="row">
                                <div class="form-group col-md-6 mb-4">
                                    <input type="text" name="fname" class="form-control" id="fname"
                                        placeholder="First Name" required>
                                    <div class="help-block with-errors"></div>
                                </div>

                                <div class="form-group col-md-6 mb-4">
                                    <input type="text" name="lname" class="form-control" id="lname"
                                        placeholder="Last Name" required>
                                    <div class="help-block with-errors"></div>
                                </div>

                                <div class="form-group col-md-6 mb-4">
                                    <input type="email" name="email" class="form-control" id="email"
                                        placeholder="Email Address" required>
                                    <div class="help-block with-errors"></div>
                                </div>

                                <div class="form-group col-md-6 mb-4">
                                    <input type="text" name="phone" class="form-control" id="phone"
                                        placeholder="Phone No." required>
                                    <div class="help-block with-errors"></div>
                                </div>

                                <div class="form-group col-md-12 mb-5">
                                    <textarea name="message" class="form-control" id="message" rows="4"
                                        placeholder="Write Message..."></textarea>
                                    <div class="help-block with-errors"></div>
                                </div>

                                <div class="col-lg-12">
                                    <div class="contact-form-btn">
                                        <button type="submit" class="btn-default btn-highlighted"><span>submit
                                                now</span></button>
                                        <div id="msgSubmit" class="h3 hidden"></div>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <!-- Contact Form End -->
                    </div>
                    <!-- Contact Form End -->
                </div>
                <!-- Contact Us Form End -->
            </div>
        </div>
    </div>
</div>
<!-- Page Contact Us End -->
<?php get_footer(); ?>