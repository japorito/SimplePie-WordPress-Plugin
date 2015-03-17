<?php

class WordPress_Cache extends SimplePie_Cache_DB
{
    protected $options;

    protected $id;

    protected $wpdb;

    /**
     * Create a new cache object
     *
     * @param string $location Location string (from SimplePie::$cache_location)
     * @param string $name Unique ID for the cache
     * @param string $type Either TYPE_FEED for SimplePie data, or TYPE_IMAGE for image data
     */
    public function __construct($location, $name, $type) {
        global $wpdb;

        $this->wpdb = $wpdb;

        $this->options = array(
            'user' => null,
            'pass' => null,
            'host' => '127.0.0.1',
            'port' => null,
            'path' => '',
            'prefix' => 'wppie_',
            'extras' => array(),
        );
        $this->options = array_merge_recursive($this->options, SimplePie_Cache::parse_URL($location));

        // Path is prefixed with a "/"
        $this->options['givenname'] = $this->options['host'];

        $this->id = $name . $type;

        $this->wpdb->query('CREATE TABLE IF NOT EXISTS `' . $this->options['prefix'] . 'cache_data` (`id` TEXT CHARACTER SET utf8 NOT NULL, `items` SMALLINT NOT NULL DEFAULT 0, `data` BLOB NOT NULL, `mtime` INT UNSIGNED NOT NULL, UNIQUE (`id`(125)))');

        $this->wpdb->query('CREATE TABLE IF NOT EXISTS `' . $this->options['prefix'] . 'items` (`feed_id` TEXT CHARACTER SET utf8 NOT NULL, `id` TEXT CHARACTER SET utf8 NOT NULL, `data` TEXT CHARACTER SET utf8 NOT NULL, `posted` INT UNSIGNED NOT NULL, INDEX `feed_id` (`feed_id`(125)))');
    }

    /**
     * Save data to the cache
     *
     * @param array|SimplePie $data Data to store in the cache. If passed a SimplePie object, only cache the $data property
     * @return bool Successfulness
     */
    public function save($data) {
        if ($this->wpdb === null)
        {
            return false;
        }

        if ($data instanceof SimplePie)
        {
            $data = clone $data;

            $prepared = self::prepare_simplepie_object_for_cache($data);

            $query = $this->wpdb->prepare('SELECT COUNT(*) as ItemCount FROM `' . $this->options['prefix'] . 'cache_data` WHERE `id` = %s', $this->id);
            if ($query)
            {
                $results = $this->wpdb->get_results($query);
                if ($this->wpdb->num_rows && $results[0]->ItemCount > 0)
                {
                    $items = count($prepared[1]);
                    if ($items)
                    {
                        $sql = 'UPDATE `' . $this->options['prefix'] . 'cache_data` SET `items` = %d, `data` = %s, `mtime` = %d WHERE `id` = %s';
                        $query = $this->wpdb->prepare($sql, $items, $prepared[0], time(), $this->id);
                    }
                    else
                    {
                        $sql = 'UPDATE `' . $this->options['prefix'] . 'cache_data` SET `data` = %s, `mtime` = %d WHERE `id` = %s';
                        $query = $this->wpdb->prepare($sql, $prepared[0], time(), $this->id);
                    }

                    if (!$this->wpdb->query($query))
                    {
                        return false;
                    }
                }
                else
                {
                    $query = 'INSERT INTO `' . $this->options['prefix'] . 'cache_data` (`id`, `items`, `data`, `mtime`) VALUES(%s, %d, %s, %d)';
                    $query = $this->wpdb->prepare($query, $this->id, count($prepared[1]), $prepared[0], time());

                    if (!$this->wpdb->query($query))
                    {
                        return false;
                    }
                }

                $ids = array_keys($prepared[1]);
                if (!empty($ids))
                {
                    foreach ($ids as $id)
                    {
                        $database_ids[] = "\"" . esc_sql($id) . "\"";
                    }

                    $query = 'SELECT `id` FROM `' . $this->options['prefix'] . 'items` WHERE `id` = ' . implode(' OR `id` = ', $database_ids) . ' AND `feed_id` = %s';
                    $query = $this->wpdb->prepare($query, $this->id);
                    $results = $this->wpdb->get_results($query);

                    $existing_ids = array();
                    for ($idx = 0; $idx < $this->wpdb->num_rows; $idx++)
                    {
                        $row = $results[$idx]->id;
                        $existing_ids[] = $row;
                    }

                    $new_ids = array_diff($ids, $existing_ids);

                    foreach ($new_ids as $new_id)
                    {
                        if (!($date = $prepared[1][$new_id]->get_date('U')))
                        {
                            $date = time();
                        }

                        $query = 'INSERT INTO `' . $this->options['prefix'] . 'items` (`feed_id`, `id`, `data`, `posted`) VALUES(%s, %s, %s, %d)';
                        $query = $this->wpdb->prepare($query, $this->id, $new_id, serialize($prepared[1][$new_id]->data), $date);

                        if (!$this->wpdb->query($query))
                        {
                            return false;
                        }
                    }
                    return true;
                }
                else
                {
                    return true;
                }
            }
        }
        else
        {
            $query = 'SELECT `id` FROM `' . $this->options['prefix'] . 'cache_data` WHERE `id` = %s';
            $query = $this->wpdb->prepare($query, $this->id);
            $results = $this->wpdb->get_results($query);

            if ($this->wpdb->num_rows > 0)
            {
                $query = 'UPDATE `' . $this->options['prefix'] . 'cache_data` SET `items` = 0, `data` = %s, `mtime` = %d WHERE `id` = %s';
                $query = $this->wpdb->prepare($query, serialize($data), time(), $this->id);

                if ($this->wpdb->query($query))
                {
                    return true;
                }
            }
            else
            {
                $query = 'INSERT INTO `' . $this->options['prefix'] . 'cache_data` (`id`, `items`, `data`, `mtime`) VALUES(%s, 0, %s, %d)';
                $query = $this->wpdb->prepare($this->id, serialize($data), time());

                if ($this->wpdb->query($query))
                {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Retrieve the data saved to the cache
     *
     * @return array Data for SimplePie::$data
     */
    public function load()
    {
        if ($this->wpdb === null)
        {
            return false;
        }

        $query = 'SELECT `items`, `data` FROM `' . $this->options['prefix'] . 'cache_data` WHERE `id` = %s';
        $query = $this->wpdb->prepare($query, $this->id);
        $results = $this->wpdb->get_results($query);

        if ($this->wpdb->num_rows && ($row = $results[0]))
        {
            $data = unserialize($row->data);

            if (isset($this->options['items'][0]))
            {
                $items = (int) $this->options['items'][0];
            }
            else
            {
                $items = (int) $row->items;
            }

            if ($items !== 0)
            {
                if (isset($data['child'][SIMPLEPIE_NAMESPACE_ATOM_10]['feed'][0]))
                {
                    $feed =& $data['child'][SIMPLEPIE_NAMESPACE_ATOM_10]['feed'][0];
                }
                elseif (isset($data['child'][SIMPLEPIE_NAMESPACE_ATOM_03]['feed'][0]))
                {
                    $feed =& $data['child'][SIMPLEPIE_NAMESPACE_ATOM_03]['feed'][0];
                }
                elseif (isset($data['child'][SIMPLEPIE_NAMESPACE_RDF]['RDF'][0]))
                {
                    $feed =& $data['child'][SIMPLEPIE_NAMESPACE_RDF]['RDF'][0];
                }
                elseif (isset($data['child'][SIMPLEPIE_NAMESPACE_RSS_20]['rss'][0]))
                {
                    $feed =& $data['child'][SIMPLEPIE_NAMESPACE_RSS_20]['rss'][0];
                }
                else
                {
                    $feed = null;
                }

                if ($feed !== null)
                {
                    $sql = 'SELECT `data` FROM `' . $this->options['prefix'] . 'items` WHERE `feed_id` = %s ORDER BY `posted` DESC';
                    if ($items > 0)
                    {
                        $sql .= ' LIMIT ' . $items;
                    }

                    $query = $this->wpdb->prepare($sql, $this->id);
                    $results = $this->wpdb->get_results($query);

                    for ($idx = 0; $idx < $this->wpdb->num_rows; $idx++)
                    {
                        $row = $results[$idx]->data;
                        $feed['child'][SIMPLEPIE_NAMESPACE_ATOM_10]['entry'][] = unserialize($row);
                    }

                    if (!$this->wpdb->num_rows)
                    {
                        return false;
                    }
                }
            }

            return $data;
        }

        return false;
    }

    /**
     * Retrieve the last modified time for the cache
     *
     * @return int Timestamp
     */
    public function mtime()
    {
        if ($this->wpdb === null)
        {
            return false;
        }

        $query = 'SELECT `mtime` FROM `' . $this->options['prefix'] . 'cache_data` WHERE `id` = %s';
        $query = $this->wpdb->prepare($query, $this->id);
        $results = $this->wpdb->get_results($query);

        if ($this->wpdb->num_rows && ($time = $results[0]->mtime))
        {
            return $time;
        }
        else
        {
            return false;
        }
    }

    /**
     * Set the last modified time to the current time
     *
     * @return bool Success status
     */
    public function touch()
    {
        if ($this->wpdb === null)
        {
            return false;
        }

        $query = 'UPDATE `' . $this->options['prefix'] . 'cache_data` SET `mtime` = %d WHERE `id` = %s';
        $query = $this->wpdb->prepare($query, time(), $this->id);

        if ($this->wpdb->query($query) > 0)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * Remove the cache
     *
     * @return bool Success status
     */
    public function unlink()
    {
        if ($this->wpdb === null)
        {
            return false;
        }

        $query = 'DELETE FROM `' . $this->options['prefix'] . 'cache_data` WHERE `id` = %s';
        $query = $this->wpdb->prepare($query, $this->id);
        $query2 = 'DELETE FROM `' . $this->options['prefix'] . 'items` WHERE `feed_id` = %s';
        $query2 = $this->wpdb->prepare($query2, $this->id);
        if ($this->wpdb->query($query) && $this->wpdb->query($query2))
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}

SimplePie_Cache::register('wpdb', 'WordPress_Cache');

?>
