<?php

namespace PHPSTORM_META {
    override(
        \Nerbiz\Wordclass\Factory::make(0),
        map([
            'Admin'        => \Nerbiz\Wordclass\Admin::class,
            'Assets'       => \Nerbiz\Wordclass\Assets::class,
            'Editor'       => \Nerbiz\Wordclass\Editor::class,
            'Factory'      => \Nerbiz\Wordclass\Factory::class,
            'Fixes'        => \Nerbiz\Wordclass\Fixes::class,
            'Helpers'      => \Nerbiz\Wordclass\Helpers::class,
            'Init'         => \Nerbiz\Wordclass\Init::class,
            'Metabox'      => \Nerbiz\Wordclass\Metabox::class,
            'PageBuilder'  => \Nerbiz\Wordclass\PageBuilder::class,
            'Plugins'      => \Nerbiz\Wordclass\Plugins::class,
            'PostType'     => \Nerbiz\Wordclass\PostType::class,
            'SettingsPage' => \Nerbiz\Wordclass\SettingsPage::class,
            'Shortcodes'   => \Nerbiz\Wordclass\Shortcodes::class,
            'Taxonomy'     => \Nerbiz\Wordclass\Taxonomy::class,
            'Theme'        => \Nerbiz\Wordclass\Theme::class,
            'Utilities'    => \Nerbiz\Wordclass\Utilities::class,
            'Webpack'      => \Nerbiz\Wordclass\Webpack::class,
            'Widgets'      => \Nerbiz\Wordclass\Widgets::class,
        ])
    );
}
