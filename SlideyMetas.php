<?php

namespace Gregwar\Slidey;

/**
 * Metas manager
 */
class SlideyMetas
{
    const INDEX_SLUG = 'index';

    public $cacheFile;

    protected $metas = array();

    /**
     * Creates a meta manager for the given filename
     */
    public function __construct($filename)
    {
        $this->cacheFile = $filename;

        if (file_exists($filename)) {
            $data = @include($filename);

            if ($data) {
                foreach ($data as $file => $entry) {
                    $this->metas[$file] = new SlideyMeta($file, $entry);
                }
            }
        }
    }

    /**
     * Get all metas
     */
    public function getAll()
    {
        return $this->metas;
    }

    /**
     * Get the meta for the given file
     */
    public function metaForFile($file)
    {
        if (!isset($this->metas[$file])) {
            $this->metas[$file] = new SlideyMeta($file);
        }

        return $this->metas[$file];
    }

    /**
     * Adds the index
     */
    public function addIndex()
    {
        $this->metas[self::INDEX_SLUG] = new SlideyMeta(self::INDEX_SLUG, array(
            'number' => 0,
            'chapter' => 'Table des matières',
            'slug' => self::INDEX_SLUG,
            'parts' => array(),
            'type' => 'index',
        ));
    }

    /**
     * Save the meta to the cache file
     */
    public function save()
    {
        $datas = array();

        foreach ($this->metas as $file => $meta) {
            $datas[$file] = $meta->getAll();
        }

        file_put_contents($this->cacheFile, '<?php return '.var_export($datas, true).';');
    }

    /**
     * Generates summary array
     */
    public function generateSummary()
    {
        $summary = array();

        foreach ($this->metas as $file => $meta) {
            if ($meta->get('type') != 'annex') {
                $data = $meta->getAll();

                foreach ($data['parts'] as $k => &$part) {
                    $part = array(
                        'title' => $part,
                        'number' => $k,
                    );
                }

                if (isset($data['annexes'])) {
                    foreach ($data['annexes'] as &$annex) {
                        $annex = $this->metaForFile($annex)->getAll();
                    }
                }

                $summary[$meta->get('number')] = $data;
            }
        }

        ksort($summary);

        return $summary;
    }
}
