<?php
/**
 * @license GPL2 or any later version
 *
 * @property string $id alias column for counter_id
 * @property string $counter_id database column
 * @property string $material_id database column
 * @property float|null $longitude database column
 * @property float|null $latitude database column
 * @property int|null $mkdate database column
 */
class OERDownloadcounter extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'oer_downloadcounter';
        parent::configure($config);
    }

    public static function addCounter($material_id)
    {
        $counter = new self();
        $counter['material_id'] = $material_id;
        if (Config::get()->oer_GEOLOCATOR_API) {
            list($url, $lon, $lat) = explode(" ", Config::get()->oer_GEOLOCATOR_API);
            $url = sprintf($url, $_SERVER['REMOTE_ADDR']);
            $output = @file_get_contents($url, false, get_default_http_stream_context($url));
            $output = json_decode($output, true);
            if (isset($output[$lon])) {
                $counter['longitude'] = $output[$lon];
            }
            if (isset($output[$lat])) {
                $counter['latitude'] = $output[$lat];
            }
        }
        $counter->store();
        return $counter;
    }
}
