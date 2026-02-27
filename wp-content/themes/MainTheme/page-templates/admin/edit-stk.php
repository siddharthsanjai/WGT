<?php

global $wpdb, $stk_record_manager, $common_class;

$record_id      = isset($_GET['id']) ? $_GET['id'] : 0;
$record_section = isset($_GET['section']) ? $_GET['section'] : 0;
$record         = $stk_record_manager->get_record_with_holders($record_id);

if (!$record) {
    echo '<div class="notice notice-error"><p>Award not found.</p></div>';
    return;
}

$user       = get_userdata($record['user_id']);
$user_label = $user ? esc_html($user->display_name . ' (' . $user->user_login . ') - ' . $user->user_email) : 'Unassigned';

$customers = get_users([
    'role'    => 'customer',
    'orderby' => 'display_name',
    'order'   => 'ASC',
]);
$cat     = wgt_get_category_by_fee_id($record['category_fee_id']);
$holder1 = $record['holders'][0] ?? [];
$holder2 = $record['holders'][1] ?? [];
$holder3 = $record['holders'][2] ?? [];
$banner_image_id          = isset($record['images']['banner']['src']) ? intval($record['images']['banner']['src']) : 0;
$featured_image_id        = isset($record['images']['featured']['src']) ? intval($record['images']['featured']['src']) : 0;
$evidence_image_ids       = isset($record['images']['evidence']) ? $record['images']['evidence'] : [];
$media_coverage_image_ids = isset($record['images']['media_coverage']) ? $record['images']['media_coverage'] : [];
$videos                   = isset($record['videos']) ? $record['videos'] : [];
$consent_form_id          = isset($record['consent_form']) ? intval($record['consent_form']) : 0;
$common_class->update_seen_status($record_id, 'super_talented_kids');
$payment                  = isset($record['payment']) ? $record['payment'] : [];
$save_disabled            = disable_save_button_for_old_applications($record['created_at']);

?>
<div class="container mt-5">
    <div class="bg-dark text-white p-3 rounded-top">
        <h4>Edit Award</h4>
    </div>
    <div class="border border-top-0 rounded-bottom p-4 bg-white">
        <div class="row">
            <div class="col-md-3">
                <div class="nav flex-column nav-pills" role="tablist">
                    <a href="<?= 'admin.php?page=wgt&tab=stk&section=basicdetails&id=' . esc_attr($record_id); ?>" class="nav-link <?= ($record_section === 'basicdetails' ? 'active' : ''); ?>">Basic Details</a>
                    <a href="<?= 'admin.php?page=wgt&tab=stk&section=addressdetails&id=' . esc_attr($record_id); ?>" class="nav-link <?= ($record_section === 'addressdetails' ? 'active' : ''); ?>">Address Details</a>
                    <a href="<?= 'admin.php?page=wgt&tab=stk&section=recorddetails&id=' . esc_attr($record_id); ?>" class="nav-link <?= ($record_section === 'recorddetails' ? 'active' : ''); ?>">Award Details</a>
                    <a href="<?= 'admin.php?page=wgt&tab=stk&section=evidenceimages&id=' . esc_attr($record_id); ?>" class="nav-link <?= ($record_section === 'evidenceimages' ? 'active' : ''); ?>">Evidance Images</a>
                    <a href="<?= 'admin.php?page=wgt&tab=stk&section=mediacoverageimages&id=' . esc_attr($record_id); ?>" class="nav-link <?= ($record_section === 'mediacoverageimage' ? 'active' : ''); ?>">Media Coverage Images</a>
                    <a href="<?= 'admin.php?page=wgt&tab=stk&section=evidencevideos&id=' . esc_attr($record_id); ?>" class="nav-link <?= ($record_section === 'evidencevideo' ? 'active' : ''); ?>">Evidance Videos</a>
                    <a href="<?= 'admin.php?page=wgt&tab=stk&section=bannerandfeatureimagevideos&id=' . esc_attr($record_id); ?>" class="nav-link <?= ($record_section === 'bannerandfeatureimagevideo' ? 'active' : ''); ?>">Banner and Feature Image Videos</a>
                    <a href="<?= 'admin.php?page=wgt&tab=stk&section=concentform&id=' . esc_attr($record_id); ?>" class="nav-link <?= ($record_section === 'concentform' ? 'active' : ''); ?>">Concent Form</a>
                    <a href="<?= 'admin.php?page=wgt&tab=stk&section=payment&id=' . esc_attr($record_id); ?>" class="nav-link <?= ($record_section === 'payment' ? 'active' : ''); ?>">Payment Details</a>
                </div>
            </div>

            <div class="col-md-9">
                <div class="tab-content">
                    <form id="record-edit-form" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="wgt_record_nonce" value="<?php echo wp_create_nonce('wgt_record_nonce_update'); ?>">
                        <input type="hidden" name="action" value="edit_stk_section">
                        <input type="hidden" name="record_id" value="<?= esc_attr($record_id); ?>">
                        <input type="hidden" name="section" value="<?= esc_attr($record_section); ?>">
                        <!-- Basic Details -->
                        <div class="tab-pane <?= ($record_section === 'basicdetails' ? 'active' : '') ?>" id="basicdetails">
                            <div class="mb-3">
                                <label class="form-label">Assigned User Account</label>
                                <select id="assigned_user" name="assigned_user" class="form-select mb-3" required>
                                    <option value="">Assigned User Account</option>
                                    <?php foreach ($customers as $customer): ?>
                                        <option value="<?php echo esc_attr($customer->ID); ?>" <?= ($record['user_id'] == $customer->ID) ? 'selected' : ''; ?>>
                                            <?php echo esc_html($customer->display_name . ' (' . $customer->user_email . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Category</label>
                                <select name="applying_as" id="applying_as" class="form-select" required>
                                    <option value="1" <?= selected($cat->index, '1', false); ?> data-type="solo">Solo</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Award Holder Name</label>
                                <input class="form-control" type="text" name="holder_name" value="<?= esc_attr($holder1['name'] ?? ''); ?>">
                            </div>

                            <div class="mb-3" data-applying-as="solo">
                                <label class="form-label">Date of Birth</label>
                                <input class="form-control" type="date" name="dob" value="<?= esc_attr($holder1['dob'] ?? ''); ?>">
                            </div>

                            <div class="mb-3" data-applying-as="solo">
                                <label class="form-label">Award Holder's Parent Name</label>
                                <input class="form-control" type="text" name="holder_parent_name" value="<?= esc_attr($holder1['parent_name'] ?? ''); ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Approval Status</label>
                                <select class="form-select mb-3" name="approval_status" required>
                                    <option value="">Select</option>
                                    <option <?= selected('Under Review', $record['approval_status']); ?>>Under Review</option>
                                    <option <?= selected('Need Revision', $record['approval_status']); ?>>Need Revision</option>
                                    <option <?= selected('Accepted', $record['approval_status']); ?>>Accepted</option>
                                    <option <?= selected('Evidence Received', $record['approval_status']); ?>>Evidence Received</option>
                                    <option <?= selected('Need More Details', $record['approval_status']); ?>>Need More Details</option>
                                    <option <?= selected('Eligible', $record['approval_status']); ?>>Eligible</option>
                                    <option <?= selected('Ready to Approve', $record['approval_status']); ?>>Ready to Approve</option>
                                    <option <?= selected('Approved', $record['approval_status']); ?>>Approved</option>
                                    <option <?= selected('Rejected', $record['approval_status']); ?>>Rejected</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Cancel Reason</label>
                                <input class="form-control" type="text" name="cancel_reason" value="<?= esc_attr($record['cancel_reason'] ?? ''); ?>">
                            </div>
                        </div>

                        <?php if ($record_section === 'addressdetails') { ?>
                            <!-- Record Details -->
                            <div class="tab-pane <?= ($record_section === 'addressdetails' ? 'active' : '') ?>" id="addressdetails">
                                <div class="mb-3">
                                    <label class="form-label">Address <span class="text-danger">*</span></label>
                                    <input class="form-control" type="text" name="address" value="<?= (! empty($record['address'])) ? $record['address'] : ''; ?>" re>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Country <span class="text-danger">*</span></label>
                                    <select class="form-select" name="country" id="country" required>
                                        <?php
                                        if (function_exists('WC')) {
                                            $countries = WC()->countries->get_countries();
                                            foreach ($countries as $code => $name) {
                                                echo '<option value="' . esc_attr($code) . '" ' . selected($name, $record['country'], false) . '>' . esc_html($name) . '</option>';
                                            }
                                        } else {
                                            echo '<option value="">WooCommerce not available</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">State <span class="text-danger">*</span></label>
                                    <?php
                                    if (function_exists('WC')) {
                                        $countries = WC()->countries->get_countries(); // ['IN' => 'India', 'US' => 'United States', ...]
                                        $countryName = trim($record['country']);
                                        $countryCode = array_search($countryName, $countries);

                                        if ($countryCode) {
                                            $states = WC()->countries->get_states($countryCode);

                                            // If WooCommerce has states for this country
                                            if (!empty($states)) {
                                                echo '<select class="form-select state-select" name="rstate" required>';
                                                foreach ($states as $code => $name) {
                                                    $isSelected =
                                                        (strcasecmp(trim($record['state']), $code) === 0) ||
                                                        (strcasecmp(trim($record['state']), $name) === 0);

                                                    echo '<option value="' . esc_attr($code) . '" ' . selected($isSelected, true, false) . '>' . esc_html($name) . '</option>';
                                                }
                                                echo '</select>';
                                            } else {
                                                // No states — show a text field instead
                                                echo '<input type="text" name="rstate" class="form-control" placeholder="Enter your state" value="' . esc_attr($record['state']) . '" required>';
                                            }
                                        } else {
                                            echo '<input type="text" name="rstate" class="form-control" placeholder="Enter your state" value="' . esc_attr($record['state']) . '" required>';
                                        }
                                    } else {
                                        echo '<input type="text" name="rstate" class="form-control" placeholder="Enter your state" value="' . esc_attr($record['state']) . '" required>';
                                    }
                                    ?>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">City <span class="text-danger">*</span></label>
                                    <input class="form-control" type="text" name="city" value="<?= (! empty($record['city'])) ? $record['city'] : ''; ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Zipcode <span class="text-danger">*</span></label>
                                    <input class="form-control" type="text" name="zipcode" value="<?= (! empty($record['zipcode'])) ? $record['zipcode'] : ''; ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Mobile No <span class="text-danger">*</span></label>
                                    <input type="tel" id="mobile_display" name="mobile_display" class="form-control"
                                        value="<?= (! empty($record['mobile'])) ? esc_attr($record['mobile']) : ''; ?>">

                                    <input type="hidden" name="phone_country_code" id="phone_country_code"
                                        value="<?= (! empty($record['cc'])) ? esc_attr($record['cc']) : ''; ?>">

                                    <input type="hidden" name="phone_number" id="phone_number"
                                        value="<?= (! empty($record['mobile'])) ? esc_attr($record['mobile']) : ''; ?>">
                                </div>
                            </div>
                        <?php } ?>

                        <?php if ($record_section === 'recorddetails') { ?>
                            <!-- Record Details -->
                            <div class="tab-pane <?= ($record_section === 'recorddetails' ? 'active' : '') ?>" id="recorddetails">
                                <div class="mb-3">
                                    <label class="form-label">Award Date</label>
                                    <input class="form-control" type="date" name="date" value="<?= esc_attr($record['date'] ?? ''); ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Purpose</label>
                                    <select name="purpose" class="form-select" required>
                                        <option value="">Select</option>
                                        <option value="1" <?= selected($record['purpose_id'], '1', false); ?>>Personal Achievement</option>
                                        <option value="2" <?= selected($record['purpose_id'], '2', false); ?>>Brand Awareness</option>
                                        <option value="3" <?= selected($record['purpose_id'], '3', false); ?>>Fundraising</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Slug</label>
                                    <a href="<?= (! empty($record['slug'])) ? site_url() . '/super-talented-kids/' . $record['slug'] : ''; ?>" target="_blank">URL&#8599;</a>
                                    <input class="form-control" type="text" name="slug" value="<?= (! empty($record['slug'])) ? $record['slug'] : ''; ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <?php
                                    $content   = ! empty($record['description']) ? $record['description'] : '';
                                    $editor_id = 'record_description';
                                    $settings  = array(
                                        'textarea_name' => 'description',  // this will be the POST field name
                                        'media_buttons' => false,          // hide "Add Media" button (set true if you want it)
                                        'textarea_rows' => 8,
                                        'teeny'         => true,           // simpler toolbar (set false for full)
                                        'quicktags'     => true,           // allow text/HTML view
                                    );

                                    wp_editor($content, $editor_id, $settings);
                                    ?>
                                </div>
                            </div>
                        <?php } ?>

                        <?php if ($record_section === 'evidenceimages') { ?>
                            <!-- Record Details -->
                            <?php $srcs = array_column($evidence_image_ids, 'src'); ?>
                            <div class="tab-pane <?= ($record_section === 'evidenceimages' ? 'active' : '') ?>" id="evidenceimages">
                                <h3>Evidence Images <span>(Total <?php echo count($srcs); ?>)</span></h3>
                                <button type="button" class="button upload-media-button" data-target="evidence_images">Upload Images</button>
                                <input type="hidden" id="evidence_images" name="evidence_images" value="<?php echo esc_attr(implode(',', ($srcs))); ?>">

                                <small style="display:block;margin-top:8px;">
                                    <ul style="color: #00a8e1; list-style: disc; padding-left: 20px;">
                                        <li>Recommended Image Dimension: 640 x 360 pixels</li>
                                        <li>Only Image Files Allowed</li>
                                        <li>Max 50 images allowed</li>
                                    </ul>
                                </small>

                                <!-- Preview Container -->
                                <div class="media-preview" id="preview_evidence_images" style="margin-top: 15px; display: flex; flex-wrap: wrap; gap: 20px;">
                                    <?php
                                    if (!empty($evidence_image_ids)) {
                                        foreach ($evidence_image_ids as $image) {
                                            $id = $image['src'];
                                            $thumb = wp_get_attachment_image_url($id, 'medium');
                                            $full  = wp_get_attachment_url($id);

                                            echo '<div class="preview-item" data-id="' . esc_attr($id) . '" 
                                                style="border:1px solid #ddd; padding:10px; border-radius:8px; width:200px; text-align:center; background:#fff; position:relative;">
                                                
                                                <div style="margin-bottom:10px;">
                                                    <img src="' . esc_url($thumb) . '" style="width:100%; border-radius:4px;">
                                                </div>
                                                
                                                <div style="font-size:14px; margin-top:5px;">
                                                    <a href="' . esc_url($full) . '" download style="color:#0066cc; margin:0 10px;">Download</a> |
                                                    <a href="#" class="remove-image" style="color:red; margin-left:10px;">Delete</a>
                                                </div>
                                            </div>';
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                        <?php } ?>

                        <?php if ($record_section === 'mediacoverageimages') { ?>
                            <!-- Media Coverage Images -->
                            <div class="tab-pane <?= ($record_section === 'mediacoverageimages' ? 'active' : '') ?>" id="mediacoverageimages">
                                <h3>Media Coverage Images <span>(Total <?php echo count($existing_images ?? []); ?>)</span></h3>

                                <button type="button" class="button upload-media-button" data-target="media_coverage_images">Choose files</button>
                                <input type="hidden" id="media_coverage_images" name="media_coverage_images" value="<?php echo esc_attr(implode(',', $image_ids ?? [])); ?>">

                                <small style="display:block;margin-top:8px;">
                                    <ul style="color: #00a8e1; list-style: disc; padding-left: 20px;">
                                        <li>Recommended Image Dimension: 640 x 360 pixels</li>
                                        <li>Only Image Files Allowed</li>
                                        <li>Max 50 images allowed</li>
                                    </ul>
                                </small>

                                <!-- Preview Container -->
                                <div class="media-preview" id="preview_media_coverage_images" style="margin-top: 15px; display: flex; flex-wrap: wrap; gap: 15px;">
                                    <?php if (!empty($media_coverage_image_ids)) : ?>
                                        <?php foreach ($media_coverage_image_ids as $image) :
                                            $id = $image['src'];
                                            $url = wp_get_attachment_image_url($id, 'medium');
                                            $full_url = wp_get_attachment_url($id);
                                        ?>
                                            <div class="preview-item" data-id="<?= esc_attr($id); ?>" style="border:1px solid #ddd; padding:10px; border-radius:8px; width:200px; text-align:center; background:#fff;">

                                                <input type="checkbox" class="select-image" style="margin-bottom:8px;">

                                                <div style="margin-bottom:10px;">
                                                    <img src="<?= esc_url($url); ?>" style="width:100%; border-radius:4px;">
                                                </div>

                                                <div style="font-size:14px; margin-top:5px;">
                                                    <a href="<?= esc_url($full_url); ?>" download style="color:#0066cc; margin:0 10px;">Download</a> |
                                                    <a href="#" class="remove-image" style="color:red; margin-left:10px;">Delete</a>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php } ?>

                        <?php if ($record_section === 'evidencevideos') { ?>
                            <!-- Record Details -->
                            <div class="tab-pane <?= ($record_section === 'evidencevideos' ? 'active' : '') ?>" id="evidencevideos">
                                <h3>Evidence Videos <span>(Total <?php echo count($videos ?? []); ?>)</span></h3>

                                <button type="button" class="button add-video-button">+ Add Video</button>
                                <input type="hidden" id="evidence_videos" name="evidence_videos" value="<?php echo esc_attr(json_encode($videos ?? [])); ?>">

                                <small style="display:block;margin-top:8px;">
                                    <ul style="color: #00a8e1; list-style: disc; padding-left: 20px;">
                                        <li>Only YouTube videos allowed</li>
                                        <li>Paste YouTube URL <strong>or</strong> iframe embed code</li>
                                        <li>Max 5 videos allowed</li>
                                    </ul>
                                </small>

                                <!-- Video Container -->
                                <div class="video-preview-container" id="video-preview-container" style="margin-top:15px;">
                                    <?php
                                    if (!empty($videos)) {
                                        $i = 1;
                                        foreach ($videos as $video) {
                                            $video = $video['src'];
                                            $preview = '';
                                            if (strpos($video, '<iframe') !== false) {
                                                // already embed code
                                                $preview = $video;
                                            } else {
                                                // try to convert URL
                                                $preview = wp_oembed_get($video);
                                            }

                                            echo '<div class="video-item" style="margin-bottom:20px;">
                                                <label>Youtube Video #' . $i . '</label>
                                                <div style="display:flex;gap:10px;align-items:center;margin-bottom:10px;">
                                                    <input type="text" class="form-control video-input" value="' . esc_attr($video) . '" placeholder="Paste Youtube URL or Embed Code Here" style="flex:1;">
                                                    <button type="button" class="button remove-video" style="background:#f44336;color:#fff;">Remove</button>
                                                </div>
                                                <div class="video-embed" style="margin-top:10px;">' . $preview . '</div>
                                            </div>';
                                            $i++;
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                        <?php } ?>

                        <?php if ($record_section === 'bannerandfeatureimagevideos') { ?>
                            <div class="tab-pane <?= ($record_section === 'bannerandfeatureimagevideos' ? 'active' : '') ?>" id="bannerandfeatureimagevideos">
                                <!-- Banner Image -->
                                <div class="image-upload-section">
                                    <h3>Banner Image</h3>
                                    <button type="button" class="button upload-single-image" data-target="banner_image">Upload Banner Image</button>
                                    <input type="hidden" id="banner_image" name="banner_image" value="<?php echo esc_attr($banner_image_id ?? ''); ?>">

                                    <small style="display:block;margin-top:8px;">
                                        <ul style="color: #00a8e1; list-style: disc; padding-left: 20px;">
                                            <li>Recommended Image Dimension: 1920 x 1080 pixels</li>
                                            <li>Only Image Files Allowed</li>
                                        </ul>
                                    </small>

                                    <div class="image-preview" id="preview_banner_image" style="margin-top:15px;" data-image>
                                        <?php if (!empty($banner_image_id)) :
                                            $url = wp_get_attachment_url($banner_image_id); ?>
                                            <div class="preview-item" data-id="<?php echo esc_attr($banner_image_id); ?>" style="position:relative;">
                                                <img src="<?php echo esc_url($url); ?>" style="max-width:100%;height:auto;">
                                                <div style="margin-top:5px;">
                                                    <a href="<?php echo esc_url($url); ?>" download class="button button-primary">Download</a>
                                                    <button type="button" class="button button-danger remove-single-image">Delete</button>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <hr>

                                <!-- Featured Image -->
                                <div class="image-upload-section">
                                    <h3>Featured Image</h3>
                                    <button type="button" class="button upload-single-image" data-target="featured_image">Upload Featured Image</button>
                                    <input type="hidden" id="featured_image" name="featured_image" value="<?php echo esc_attr($featured_image_id ?? ''); ?>">

                                    <small style="display:block;margin-top:8px;">
                                        <ul style="color: #00a8e1; list-style: disc; padding-left: 20px;">
                                            <li>Recommended Image Dimension: 1920 x 1080 pixels</li>
                                            <li>Only Image Files Allowed</li>
                                        </ul>
                                    </small>

                                    <div class="image-preview" id="preview_featured_image" style="margin-top:15px;">
                                        <?php if (!empty($featured_image_id)) :
                                            $url = wp_get_attachment_url($featured_image_id); ?>
                                            <div class="preview-item" data-id="<?php echo esc_attr($featured_image_id); ?>" style="position:relative;">
                                                <img src="<?php echo esc_url($url); ?>" style="max-width:100%;height:auto;">
                                                <div style="margin-top:5px;">
                                                    <a href="<?php echo esc_url($url); ?>" download class="button button-primary">Download</a>
                                                    <button type="button" class="button button-danger remove-single-image">Delete</button>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                            </div>
                        <?php } ?>

                        <!-- Consent Form -->
                        <?php if ($record_section === 'concentform') { ?>
                            <div class="tab-pane <?= ($record_section === 'concentform' ? 'active' : '') ?>" id="concentform">
                                <h3>Consent Form</h3>

                                <div id="consent-form-preview" style="margin-bottom:15px;">
                                    <?php if (!empty($consent_form_id)) :
                                        $url = wp_get_attachment_url($consent_form_id); ?>
                                        <div class="preview-item" data-id="<?php echo esc_attr($consent_form_id); ?>" style="border:1px solid #ddd;padding:15px;border-radius:6px;background:#fafafa;">
                                            <p><strong>Uploaded Consent Form:</strong></p>
                                            <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:10px;">
                                                <a href="<?php echo esc_url($url); ?>" target="_blank" class="button button-primary">📄 View</a>
                                                <a href="<?php echo esc_url($url); ?>" download class="button">⬇ Download</a>
                                                <button type="button" class="button button-danger remove-consent-form">❌ Delete</button>
                                            </div>
                                        </div>
                                    <?php else : ?>
                                        <p style="color:#999;">No consent form uploaded.</p>
                                    <?php endif; ?>
                                </div>

                                <button type="button" class="button button-secondary upload-consent-form" data-target="consent_form">+ Upload Consent Form</button>
                                <input type="hidden" id="consent_form" name="consent_form" value="<?php echo esc_attr($consent_form_id ?? ''); ?>">

                                <small style="display:block;margin-top:8px;">
                                    <ul style="color:#00a8e1; list-style:disc; padding-left:20px;">
                                        <li>Only <b>PDF</b> files are allowed</li>
                                    </ul>
                                </small>
                            </div>
                        <?php } ?>


                        <!-- Payment Details -->
                        <?php if ($record_section === 'payment') { ?>
                            <div class="tab-pane <?= ($record_section === 'payment' ? 'active' : '') ?>" id="payment">
                                <table class="table table-borderless">
                                    <tbody>
                                        <tr>
                                            <th style="width: 200px;">Reference ID:</th>
                                            <td><?php echo esc_html($payment['id']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Receipt ID:</th>
                                            <td><?php echo esc_html($payment['receipt_id']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Payment Date:</th>
                                            <td><?php echo isset($payment['payment_date']) ? date('d-m-Y', strtotime($payment['payment_date'])) : 'N/A'; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Payable Amount:</th>
                                            <td><?= $payment['currency']; ?> <?php echo number_format($payment['amount'], 2); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Country:</th>
                                            <td><?php echo esc_html($payment['country']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Currency:</th>
                                            <td><?php echo esc_html($payment['currency']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Payment Gateway:</th>
                                            <td><?php echo esc_html($payment['gateway']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Gateway Reference ID:</th>
                                            <td><?php echo !empty($payment['gateway_ref_id']) ? esc_html($payment['gateway_ref_id']) : 'N/A'; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Status:</th>
                                            <td>
                                                <span class="badge bg-success"><?php echo esc_html($payment['status']); ?></span>
                                                <!-- Action Dropdown -->
                                                <?= get_the_payment_modal($payment); ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Is Payment Captured?:</th>
                                            <td><?php echo $payment['payment_captured'] ? '✔ Yes' : '✖ No'; ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        <?php } ?>

                        <?php if ($record_section !== 'payment' && !$save_disabled) { ?>
                            <div class="mt-4">
                                <button class="btn btn-primary" type="submit">Submit</button>
                            </div>
                        <?php } ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php
