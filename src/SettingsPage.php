<?php

namespace Nerbiz\Wordclass;

use Nerbiz\Wordclass\InputFields\AbstractInputField;

class SettingsPage
{
    /**
     * The title of the settings page
     * @var string
     */
    protected $pageTitle = 'Theme settings';

    /**
     * The slug of the parent page, if this needs to be a subpage
     * @var string|null
     */
    protected $parentSlug = null;

    /**
     * The settings page slug, will be prepended with prefix
     * @var string
     */
    protected $pageSlug;

    /**
     * The capability required for using the settings page
     * @var string
     */
    protected $capability = 'manage_options';

    /**
     * The icon of the menu item
     * @var string
     */
    protected $icon = 'dashicons-admin-settings';

    /**
     * The button position in the menu
     * @var int
     */
    protected $menuPosition;

    /**
     * The sections of the settings page
     * @var SettingsPageSection[]
     */
    protected $sections = [];

    /**
     * Add a settings section to the settings page
     * @param  string               $id       Section ID, prefix will be prepended
     * @param  string               $title
     * @param  string|null          $subtitle
     * @param  AbstractInputField[] $fields   Input fields for the settings
     * @return self
     */
    public function addSection(
        string $id,
        string $title,
        ?string $subtitle = null,
        array $fields = []
    ): self {
        // Use the section ID as the input field name prefix
        foreach ($fields as $inputField) {
            $inputField->setNamePrefix($id);
        }

        // Add the section
        $this->sections[] = new SettingsPageSection($id, $title, $subtitle, $fields);

        return $this;
    }

    /**
     * Add the settings page
     * @return self
     */
    public function create(): self
    {
        add_action('admin_menu', function () {
            // Store values, if submitted
            $this->storeValues();

            // The function that renders the settings page
            $renderFunction = function () {
                // For use in the template
                $settingsPage = $this;
                require __DIR__ . '/../includes/html/settings-page-template.php';
            };

            // Add the settings page
            $pageSlug = Init::getPrefix() . '-' . $this->getPageSlug();
            if ($this->parentSlug !== null) {
                // As a subpage, if a parent slug is given
                add_submenu_page(
                    $this->parentSlug,
                    $this->pageTitle,
                    $this->pageTitle,
                    $this->capability,
                    $pageSlug,
                    $renderFunction
                );
            } else {
                // Or as a separate page
                add_menu_page(
                    $this->pageTitle,
                    $this->pageTitle,
                    $this->capability,
                    $pageSlug,
                    $renderFunction,
                    $this->icon,
                    $this->menuPosition
                );
            }
        }, 100);

        return $this;
    }

    /**
     * Store submitted values
     * @return void
     */
    protected function storeValues(): void
    {
        // Return when POST is empty
        if (count($_POST) < 1) {
            return;
        }

        // Check if the current user is allowed to update the values
        if (! current_user_can($this->capability)) {
            wp_die(__("You don't have the right permissions to update these settings.", 'wordclass'));
        }

        // Check if the nonce is valid
        if (! wp_verify_nonce($_POST['_wpnonce'] ?? '', $this->getPageSlug())) {
            wp_die(__('Invalid nonce value, please refresh the page and try again.', 'wordclass'));
        }

        // Store all submitted values
        foreach ($this->sections as $section) {
            foreach ($section->getFields() as $field) {
                $name = $field->getPrefixedName();

                if (isset($_POST[$name])) {
                    update_option($name, $_POST[$name]);
                }
            }
        }

        // Show the default 'Settings saved' message
        add_settings_error('general', 'settings_updated', __('Settings saved.'), 'updated');
    }

    /**
     * @return string
     */
    public function getPageTitle(): string
    {
        return $this->pageTitle;
    }

    /**
     * @param string $pageTitle
     * @return self
     */
    public function setPageTitle(string $pageTitle): self
    {
        $this->pageTitle = $pageTitle;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getParentSlug(): ?string
    {
        return $this->parentSlug;
    }

    /**
     * @param string|null $parentSlug
     * @return self
     */
    public function setParentSlug(?string $parentSlug): self
    {
        $this->parentSlug = $parentSlug;

        return $this;
    }

    /**
     * @return string
     */
    public function getPageSlug(): string
    {
        // Derive the page slug if it's not set yet
        if ($this->pageSlug === null) {
            $this->setPageSlug(Utilities::createSlug($this->pageTitle));
        }

        return $this->pageSlug;
    }

    /**
     * @param string $pageSlug
     * @return self
     */
    public function setPageSlug(string $pageSlug): self
    {
        $this->pageSlug = $pageSlug;

        return $this;
    }

    /**
     * @return string
     */
    public function getCapability(): string
    {
        return $this->capability;
    }

    /**
     * @param string $capability
     * @return self
     */
    public function setCapability(string $capability): self
    {
        $this->capability = $capability;

        return $this;
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return $this->icon;
    }

    /**
     * @param string $icon
     * @return self
     */
    public function setIcon(string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * @return int
     */
    public function getMenuPosition(): int
    {
        return $this->menuPosition;
    }

    /**
     * @param int $menuPosition
     * @return self
     */
    public function setMenuPosition(int $menuPosition): self
    {
        $this->menuPosition = $menuPosition;

        return $this;
    }

    /**
     * @return SettingsPageSection[]
     */
    public function getSections(): array
    {
        return $this->sections;
    }
}
