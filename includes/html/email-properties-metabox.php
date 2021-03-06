<?php

use Nerbiz\WordClass\Init;

// A metabox ID and WP_Post object are required
if (! isset($metaboxId, $currentPost) || ! ($currentPost instanceof WP_Post)) {
    return;
}

// Define input field labels
$recipientLabel = __('Recipient', 'wordclass');
$attachmentsLabel = __('Attachments', 'wordclass');
$headersLabel = __('Headers', 'wordclass');

// Get post meta values
$emailMeta = get_post_meta($currentPost->ID);
$recipientValue = $emailMeta['email_properties_recipient'][0] ?? '';
$attachmentsValue = $emailMeta['email_properties_attachments'][0] ?? '';
$headersValue = $emailMeta['email_properties_headers'][0] ?? '';

$nonceName = sprintf('%s_email_properties_nonce', Init::getPrefix());
wp_nonce_field(-1, $nonceName);
?>

<div class="email-properties-metabox">
    <p>
        <?php
        $fieldName = 'email_properties_recipient';
        echo sprintf(
            '<label for="%s">%s</label><br>
            <input type="text" class="regular-text" name="%s" id="%s" value="%s" placeholder="%s">',
            $fieldName,
            $recipientLabel,
            $fieldName,
            $fieldName,
            $recipientValue,
            $recipientLabel
        );
        ?>
    </p>

    <p>
        <?php
        $fieldName = 'email_properties_attachments';
        echo sprintf(
            '<label for="%s">%s</label><br>
            <textarea class="regular-text" name="%s" id="%s">%s</textarea>',
            $fieldName,
            $attachmentsLabel,
            $fieldName,
            $fieldName,
            $attachmentsValue
        );
        ?>
    </p>

    <p>
        <?php
        $fieldName = 'email_properties_headers';
        echo sprintf(
            '<label for="%s">%s</label><br>
            <textarea class="regular-text" name="%s" id="%s">%s</textarea>',
            $fieldName,
            $headersLabel,
            $fieldName,
            $fieldName,
            $headersValue
        );
        ?>
    </p>
</div>
