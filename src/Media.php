<?php

namespace Nerbiz\Wordclass;

class Media
{
    /**
     * Set the size of featured images
     * @param  int  $width
     * @param  int  $height
     * @param  bool $crop Whether to resize (false) or crop (true) images
     * @return self
     */
    public function setFeaturedImageSize(int $width, int $height, bool $crop = false): self
    {
        add_action('after_setup_theme', function () use ($width, $height, $crop) {
            set_post_thumbnail_size($width, $height, $crop);
        });

        return $this;
    }

    /**
     * Add a new image size, and add it to the size chooser
     * @param  string $name          Key name for the $sizes array
     * @param  string $nameInChooser Name in the size chooser
     * @param  int    $width
     * @param  int    $height
     * @param  bool   $crop          Whether to resize (false) or crop (true) images
     * @return self
     */
    public function addImageSize(
        string $name,
        string $nameInChooser,
        int $width,
        int $height,
        bool $crop = false
    ): self {
        add_action('after_setup_theme', function () use ($name, $nameInChooser, $width, $height, $crop) {
            add_image_size($name, $width, $height, $crop);

            // Set the image size name for the chooser
            add_filter('image_size_names_choose', function ($sizes) use ($name, $nameInChooser) {
                $sizes[$name] = $nameInChooser;
                return $sizes;
            });
        });

        return $this;
    }

    /**
     * Add upload support for a specific file type
     * @param string $name
     * @param string $mimeType
     * @return self
     */
    public function addUploadSupport(string $name, string $mimeType): self
    {
        add_filter('upload_mimes', function (array $mimeTypes) use ($name, $mimeType) {
            return array_merge($mimeTypes, [
                $name => $mimeType,
            ]);
        }, 10);

        return $this;
    }
}
