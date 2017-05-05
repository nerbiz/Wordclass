# Wordclass classes

## Wordclass\Admin

### ::showBar()
Show or hide the admin bar at the top, when viewing the frontend.

#### Example
```php
Wordclass\Admin::showBar(true);
Wordclass\Admin::showBar(false);
```

### ::roleRedirect()
Redirect to a custom URL after login, specified per role. Each URL is filtered with esc_url(). Unspecified roles will just redirect to the default Wordpress backend.

#### Example
```php
// As an array
Wordclass\Admin::roleRedirect([
    'administrator' => 'some/custom/url',
    'editor'        => 'another/url'
]);

// As strings, in case of 1 role/URL
Wordclass\Admin::roleRedirect('administrator', 'some/custom/url');
```

---

## Wordclass\Assets
Uses the `CanPreventAssetsCaching` trait.

### ::add(), ::addAdmin(), ::addLogin()
Add CSS and/or JavaScript assets to a page. Assets can be added to the theme ('add'), to the Wordpress backend ('addAdmin') and the Wordpress login page ('addLogin'). Theme asset URLs are relative to the (child-)theme directory, actual URLs are kept as-is.

#### Example
```php
// Enable cache busting
Wordclass\Assets::preventCache(true);

// Add 1 CSS asset at a time
Wordclass\Assets::add('css', 'theme', 'assets/css/theme.css');
Wordclass\Assets::add('css', 'theme', '//fonts.googleapis.com/css?family=Open+Sans');
Wordclass\Assets::add('css', 'theme', 'https://fonts.googleapis.com/css?family=Open+Sans');
// Add 1 JavaScript asset at a time
Wordclass\Assets::add('js', 'theme', 'assets/js/theme.js');

// Add multiple CSS assets at the same time
Wordclass\Assets::add('css', [
    'theme' => 'assets/css/theme.css',
    // With custom options
    'extra' => [
        // Path to the asset
        'path'  => 'assets/css/extra.css',
        // Asset dependencies, default is an empty array
        'after' => ['theme'],
        // The media which this asset is for, default is 'all'
        'media' => 'all'
    ],
]);

// Add multiple JavaScript assets at the same time
Wordclass\Assets::add('js', [
    'theme' => 'assets/js/theme.js',
    // With custom options
    'extra' => [
        // Path to the asset
        'path'  => 'assets/js/extra.js',
        // Asset dependencies, default is an empty array
        'after' => ['theme'],
        // Add this asset to the header (false) or footer (true), default is true
        'footer' => true
    ],
]);
```

---

## Wordclass\Helpers

### ::getImage(), ::getFeaturedImage(), ::getMetaImage()
Get information about an image resource, either general, featured or a custom field. The first argument is the ID of the image. The second 'size' argument accepts any registered image size, like 'thumbnail', 'medium', 'large', 'full' or a custom added size (default 'full'). The third argument specifies the type of info that will be returned (default 'url').

#### Example
```php
// Get the URL of an image
$imageUrl = Wordclass\Helpers::getImage(35, 'full', 'url');
// Get an array of image info: [url, width, height, is_intermediate]
$imageArray = Wordclass\Helpers::getImage(35, 'full', 'array');
// Get an image as an <img> tag
$imageTag = Wordclass\Helpers::getImage(35, 'full', 'html');

// Convenience wrapper for featured images
// The first argument is a post ID
$featuredImage = Wordclass\Helpers::getFeaturedImage(21, 'full', 'url');

// Convenience wrapper for custom field images
// The first argument is a post ID, the second is a custom field name
$metaImage = Wordclass\Helpers::getMetaImage(21, 'poster', 'full', 'url');
```

### ::getTaxonomySlug()
Get the slug of a taxonomy.

#### Example
```php
// This could return 'genre', as in www.example.com/genre/action
$slug = Wordclass\Helpers::getTaxonomySlug('movie_genres');
```

### ::getTaxonomyItems()
Get all the items of a given taxonomy.

#### Example
```php
// Get all the movie genres, for example
$genres = Wordclass\Helpers::getTaxonomyItems('movie_genres');
```

### ::getPostTaxonomies()
Get the taxonomies that a post has.

#### Example
```php
// Get all the movie genres of post ID 21, which would be a movie post type
$movieGenres = Wordclass\Helpers::getPostTaxonomies(21, 'movie_genres');
```

---

## Wordclass\Metabox
Depends on [webdevstudios/cmb2](https://github.com/WebDevStudios/CMB2).
Uses the `CanSetTextDomain` trait.

### ::create()
This starts a metabox creation chain. The chain is: `create()->addField()->add()`, where `addField()` can be chained multiple times.

#### Example
```php
// Set the text domain for all following metaboxes
Wordclass\Metabox::setTextDomain(TEXT_DOMAIN);

// create() sets the ID and title of the metabox, as well as the post types to apply this metabox to.
// The third (post types) argument can be a string (1 post type) or an array (multiple post types). Wordclass\PostType instances are also accepted.
// The fourth argument is optional and specifies metabox options (see https://cmb2.io/api/source-class-CMB2.html#47-71). The 'id', 'title' and 'object_types' options are set with the first 3 arguments, this array sets any other option(s).
Wordclass\Metabox::create('metabox-1', 'Movie properties', 'movie', [])
    // Please refer to https://cmb2.io/docs/field-types for all the available options per field type.
    // I'll try my best to make everything translatable, I just don't know of all the option names yet.
    ->addField([
        'id'      => 'poster',
        'type'    => 'file',
        // Translated with text domain
        'name'    => 'Movie poster',
        // The options below are different per field type
        'options' => [
            'url' => false
        ],
        'text'    => [
            // Translated with text domain
            'add_upload_file_text' => 'Select image'
        ]
    ])
    // Another field can be added with chaining
    ->addField([
        'id'   => 'plot',
        'type' => 'wysiwyg',
        // Translated with text domain
        'name' => 'Movie plot'
    ])
    // This adds the metabox
    ->add();
```