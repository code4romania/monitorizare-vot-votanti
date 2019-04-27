<?php

namespace App\Helpers;

use Intervention\Image\ImageManager;

/**
 * Class IncidentImageUpload.
 */
class IncidentImageUpload
{
    const INTERNAL_PATH = '/public/assets/media/images/';
    const EXTERNAL_PATH = '/assets/media/images/';
    const IMG_PREFIX = 'image_incident_';

    /**
     * incidentImageBasePath.
     *
     * @return string
     */
    public static function imageBasePath()
    {
        return base_path() . static::INTERNAL_PATH;
    }

    /**
     * incidentImageName.
     *
     * @param int $id
     * @param string $extension
     * @return string
     */
    public static function imageName(int $id, string $extension)
    {
        return sprintf('%s%d.%s', static::IMG_PREFIX, $id, $extension);
    }

    /**
     * saveImage.
     *
     * @param string $imageName
     * @return string
     */
    public static function saveImage(string $imageName)
    {
        $manager = new ImageManager();
        $path = static::imageBasePath() . $imageName;
        $image = $manager->make($path);
        $image->resize(1024, 1024 * $image->height() / $image->width());
        $image->save($path);

        return static::EXTERNAL_PATH . $imageName;
    }
}
