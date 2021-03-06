<?php

namespace Nerbiz\WordClass;

use Nerbiz\WordClass\InputFields\CheckboxInputField;
use Nerbiz\WordClass\InputFields\EditorInputField;
use Nerbiz\WordClass\InputFields\PasswordInputField;
use Nerbiz\WordClass\InputFields\TextInputField;
use PHPMailer\PHPMailer\PHPMailer;
use WP_Error;
use WP_Post;
use WP_Query;

class Mail
{
    /**
     * Enable SMTP for all mails, add a settings page
     * @param string|null $encryptionKey The key for encrypting/decrypting the SMTP password
     * @return self
     */
    public function addSmtpSupport(?string $encryptionKey = null): self
    {
        $this->addSmtpSettingsPage();
        $this->addOptionHooks($encryptionKey);
        $this->addSmtpMailHook();

        return $this;
    }

    /**
     * Add the settings page for SMTP settings
     * @return void
     */
    protected function addSmtpSettingsPage(): void
    {
        // Create the settings page
        $settingsPage = new SettingsPage();
        $settingsPage->setParentSlug('options-general.php')
            ->setPageTitle(__('SMTP settings', 'wordclass'))
            ->addSection(
                new SettingsPageSection('smtp', __('SMTP values', 'wordclass'), null, [
                    new CheckboxInputField('enable', __('Enable SMTP?', 'wordclass')),
                    new TextInputField('host', __('Host', 'wordclass')),
                    new TextInputField('port', __('Port', 'wordclass')),
                    new TextInputField('encryption', __('Encryption', 'wordclass')),
                    new TextInputField('username', __('Username', 'wordclass')),
                    new PasswordInputField('password', __('Password', 'wordclass'), __('Encryption is used to store the password', 'wordclass')),
                ])
            )
            ->addSection(
                new SettingsPageSection('smtp_test', __('Test settings', 'wordclass'), null, [
                    new TextInputField('recipient', __('Recipient', 'wordclass')),
                    new TextInputField('subject', __('Subject', 'wordclass')),
                    new EditorInputField('content', __('Content', 'wordclass')),
                    new CheckboxInputField('enable', __('Send testmail?', 'wordclass'), __('If checked, a testmail will be sent when saving these settings', 'wordclass')),
                ])
            )
            ->create();
    }

    /**
     * Add hooks for storing/reading options
     * @param string|null $encryptionKey
     * @return void
     */
    protected function addOptionHooks(?string $encryptionKey = null): void
    {
        $crypto = new Crypto($encryptionKey);
        $passwordField = Init::getPrefix() . '_smtp_password';
        $enableTestField = Init::getPrefix() . '_smtp_test_enable';

        // Encrypt the SMTP password before storing
        add_filter('pre_update_option_' . $passwordField, function ($newValue, $oldValue) use ($crypto) {
            return $crypto->encrypt($newValue);
        }, 10, 2);

        // Decrypt the SMTP password before using
        add_filter('option_' . $passwordField, function ($value, $optionName) use ($crypto) {
            return $crypto->decrypt($value);
        }, 10, 2);

        // Send a testmail if requested
        add_filter('pre_update_option_' . $enableTestField, function ($newValue, $oldValue) {
            if ($newValue == 1) {
                $this->sendTestMail();
            }

            // Always reset to unchecked
            return '';
        }, 10, 2);
    }

    /**
     * Apply the SMTP settings to all outgoing WP mail
     * @return void
     */
    protected function addSmtpMailHook(): void
    {
        add_action('phpmailer_init', function (PHPMailer $phpMailer) {
            $options = new Options();

            if ($options->get('smtp_enable') === null) {
                return $phpMailer;
            }

            $phpMailer->isSMTP();
            $phpMailer->Host = $options->get('smtp_host');
            $phpMailer->Port = $options->get('smtp_port');
            $phpMailer->SMTPSecure = $options->get('smtp_encryption');

            $username = $options->get('smtp_username');
            $password = $options->get('smtp_password');
            if ($username !== null || $password !== null) {
                $phpMailer->SMTPAuth = true;
                $phpMailer->Username = $username;
                $phpMailer->Password = $password;
            }

            return $phpMailer;
        });
    }

    /**
     * Send a testmail, using the filled in values
     * @return void
     */
    protected function sendTestMail(): void
    {
        add_action('wp_mail_failed', function(WP_Error $error) {
            // Add an admin error notice
            add_action('admin_notices', function () use ($error) {
                echo sprintf(
                    '<div class="notice notice-error is-dismissible"><p>%s<br>%s</p></div>',
                    __('An error occured when trying to send the testmail:', 'wordclass'),
                    $error->get_error_message()
                );
            });
        });

        // (Try to) send the email
        $options = new Options();
        $headers = [
            'Content-Type: text/html; charset=' . get_bloginfo('charset'),
            'From: ' . sprintf('%s <%s>', get_bloginfo('name'), get_option('admin_email')),
        ];

        $mailIsSent = wp_mail(
            $options->get('smtp_test_recipient'),
            $options->get('smtp_test_subject'),
            nl2br($options->get('smtp_test_content')),
            $headers
        );

        // Mail is sent successfully
        if ($mailIsSent) {
            // Add an admin success notice
            add_action('admin_notices', function () {
                echo sprintf(
                    '<div class="notice notice-success is-dismissible"><p>%s</p></div>',
                    __('The testmail was sent successfully.', 'wordclass')
                );
            });
        }
    }

    /**
     * Store every sent mail as a post
     * @return self
     */
    public function storeSentEmails(): self
    {
        // Include the metaboxes CSS
        (new Assets())->addAdminCss(
            Init::getPrefix() . '-admin-metaboxes',
            Init::getVendorUri('nerbiz/wordclass/includes/css/admin-metaboxes.css')
        );

        $cptSentEmail = $this->addSentEmailPostType();
        $this->adjustSentEmailPostColumns($cptSentEmail);
        $this->addSentEmailMetaHook($cptSentEmail);
        $this->addStoreEmailHook();

        return $this;
    }

    /**
     * Add the post type containing sent emails
     * @return PostType
     */
    protected function addSentEmailPostType(): PostType
    {
        // Create the post type
        $cptSentEmail = (new PostType('sent_email'))
            ->setSingularName(__('Sent email', 'wordclass'))
            ->setPluralName(__('Sent emails', 'wordclass'))
            ->setFeatures(['title', 'editor'])
            ->setArguments([
                'menu_icon' => 'dashicons-email-alt',
                'public' => true,
                'publicly_queryable' => false,
                // Enable Gutenberg editor
                'show_in_rest' => true,
            ])
            ->register();

        // Add a metabox for the post type
        add_action('add_meta_boxes', function () use ($cptSentEmail) {
            $metaboxId = 'email_properties';

            add_meta_box(
                $metaboxId,
                __('Email properties', 'wordclass'),
                function (WP_Post $currentPost, array $boxProperties) use ($metaboxId) {
                    require __DIR__ . '/../includes/html/email-properties-metabox.php';
                },
                $cptSentEmail->getName()
            );
        });

        return $cptSentEmail;
    }

    /**
     * Customize the columns on the sent emails post overview
     * @param PostType $postType
     * @return void
     */
    protected function adjustSentEmailPostColumns(PostType $postType): void
    {
        // Add orderby methods
        foreach (['recipient', 'attachments', 'headers'] as $name) {
            $methodName = sprintf('meta_email_%s', $name);

            PostColumnsEditor::addOrderByMethod($methodName, function (WP_Query $query) use ($name) {
                $metaKey = sprintf('email_properties_%s', $name);
                $query->set('orderby', 'meta_value');
                $query->set('meta_key', $metaKey);
            });
        }

        // Create column objects
        $recipientColumn = (new PostColumn('recipient', __('Recipient', 'wordclass')))
            ->setAfter('title')
            ->setOrderBy('meta_email_recipient')
            ->setRenderFunction(function (int $postId) {
                return htmlentities2(get_post_meta($postId, 'email_properties_recipient', true));
            });

        $contentColumn = (new PostColumn('content', __('Content', 'wordclass')))
            ->setAfter('recipient')
            ->setRenderFunction(function (int $postId) {
                $content = wp_strip_all_tags(get_the_content(null, false, $postId));
                if (mb_strlen($content) > 150) {
                    return mb_substr($content, 0, 150) . '...';
                }

                return $content;
            });

        $attachmentsColumn = (new PostColumn('attachments', __('Attachments', 'wordclass')))
            ->setAfter('content')
            ->setOrderBy('meta_email_attachments')
            ->setRenderFunction(function (int $postId) {
                $attachmentsString = htmlentities2(
                    get_post_meta($postId, 'email_properties_attachments', true)
                );

                if (trim($attachmentsString) === '') {
                    return '-';
                }

                return str_replace(PHP_EOL, '<br><br>', $attachmentsString);
            });

        $headersColumn = (new PostColumn('headers', __('Headers', 'wordclass')))
            ->setAfter('attachments')
            ->setOrderBy('meta_email_headers')
            ->setRenderFunction(function (int $postId) {
                $headersString = htmlentities2(
                    get_post_meta($postId, 'email_properties_headers', true)
                );

                if (trim($headersString) === '') {
                    return '-';
                }

                return str_replace(PHP_EOL, '<br><br>', $headersString);
            });

        // Apply column adjustments
        (new PostColumnsEditor([$postType]))
            ->addColumn($recipientColumn)
            ->addColumn($contentColumn)
            ->addColumn($attachmentsColumn)
            ->addColumn($headersColumn)
            ->apply();
    }

    /**
     * Add a hook for storing sent email post meta
     * @param PostType $postType
     * @return void
     */
    protected function addSentEmailMetaHook(PostType $postType): void
    {
        add_action('save_post', function (int $postId, WP_Post $post, bool $update) use ($postType) {
            // Check for the right post type
            if ($post->post_type !== $postType->getName()) {
                return;
            }

            // Check if the nonce is valid
            $nonceName = sprintf('%s_email_properties_nonce', Init::getPrefix());
            if (isset($_POST[$nonceName]) && ! wp_verify_nonce($_POST[$nonceName] ?? '')) {
                return;
            }

            // Skip autosaving
            if (wp_is_post_autosave($post) !== false) {
                return;
            }

            // Store the meta values
            foreach ([
                'email_properties_recipient',
                'email_properties_attachments',
                'email_properties_headers',
            ] as $metaField) {
                if (! isset($_POST[$metaField])) {
                    continue;
                }

                update_post_meta($postId, $metaField, $_POST[$metaField]);
            }
        }, 10, 3);
    }

    /**
     * Store emails when they're sent
     * @return void
     */
    protected function addStoreEmailHook(): void
    {
        add_filter('wp_mail', function (array $mailProperties) {
            // Store the sent email
            $postId = wp_insert_post([
                'post_type' => Init::getPrefix() . '_sent_email',
                'post_status' => 'publish',
                'post_title' => trim($mailProperties['subject'] !== '')
                    ? $mailProperties['subject']
                    : __('(no subject)', 'wordclass'),
                'post_content' => '<!-- wp:paragraph -->'
                    . (trim($mailProperties['message'] !== '')
                        ? $mailProperties['message']
                        : __('(no content)', 'wordclass'))
                    . '<!-- /wp:paragraph -->',
            ]);

            // Store the recipient as post meta
            update_post_meta($postId, 'email_properties_recipient', $mailProperties['to']);

            // Store the attachments as post meta
            $attachmentsString = implode(PHP_EOL, $mailProperties['attachments']);
            update_post_meta($postId, 'email_properties_attachments', $attachmentsString);

            // Store the headers as post meta
            $headersString = implode(PHP_EOL, $mailProperties['headers']);
            update_post_meta($postId, 'email_properties_headers', $headersString);
        });
    }
}
