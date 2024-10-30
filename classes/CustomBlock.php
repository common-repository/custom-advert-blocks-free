<?php

class CustomBlock {

    public $id = 0;
    public $title = "";
    public $published = 1;
    public $sync = 0;

    public function __construct() {
        
    }

    static public function findById($id) {
        global $wpdb;
        $id=(int)$id;
        $sql1="SELECT id FROM ". $wpdb->prefix . 'custom_block limit 0,3;';
        $row1 = $wpdb->get_col($sql1);
        $sql = "SELECT * FROM " . $wpdb->prefix . "custom_block WHERE `id` = " . $id;
        $row = $wpdb->get_row($sql);

        if (!$row)
            return null;

        if (!in_array($row->id,$row1))
        {
            return null;
        }
        $block = new self;
        $block->id = (int) $row->id;
        $block->title = stripslashes($row->title);
        $block->published = (int) $row->published;
        if (isset($row->sync)) {
            $block->sync = (int) $row->sync;
        } else {
            $block->sync = 0;
        }

        return $block;
    }

    static public function findAll() {
        global $wpdb;
        $cnt=3;
        $sql = "SELECT `id` FROM " . $wpdb->prefix . "custom_block limit 0,{$cnt}";
        $ids = $wpdb->get_results($sql);

        $blocks = array();

        foreach ($ids as $row) {
            $blocks[] = self::findById($row->id);
        }

        return $blocks;
    }

    public function getItems() {
        global $wpdb;
        $items = array();

        $sql = "SELECT * FROM " . $wpdb->prefix . "custom_block_item WHERE `block_id` = '" . $this->id . "' ORDER BY `created` ASC";
        $rows = $wpdb->get_results($sql);
        foreach ($rows as $row) {
            $item = new CustomBlockItem();
            $item->id = $row->id;
            $item->block_id = $row->block_id;
            $item->type_id = $row->type_id;
            $item->title = stripslashes($row->title);
            $item->html = stripslashes($row->html);
            $item->show = $row->show;
            $item->show_index = $row->show_index;
            $item->click = $row->click;
            $item->published = $row->published;
            $item->geotargeting = (bool) $row->geotargeting;
            $item->content_filter = (bool) $row->content_filter;
            $item->subhead = (bool) $row->subhead;
            $items[] = $item;
        }

        return $items;
    }

    public function activate() {
        $this->published = 1;
        $this->save();
    }

    public function deactivate() {
        $this->published = 0;
        $this->save();
    }

    public function delete() {
        foreach ($this->getItems() as $item) {
            $item->delete();
        }

        global $wpdb;
        $sql = "DELETE FROM " . $wpdb->prefix . "custom_block WHERE `id` = '" . $this->id . "'";
        $wpdb->query($sql);
    }

    public function save() {
        global $wpdb;
        if ($this->id) {
            $sql = "UPDATE " . $wpdb->prefix . "custom_block SET `title` = '" . $this->title . "', `published` = '" . $this->published . "', `sync` = '" . $this->sync . "' WHERE `id` = '" . $this->id . "'";
        } else {
            $sql = "INSERT INTO " . $wpdb->prefix . "custom_block (`title`, `published`, `sync`) VALUES ('" . $this->title . "','" . $this->published . "','" . $this->sync . "')";
        }
        $wpdb->query($sql);
        if (!$this->id)
            $this->id = $wpdb->insert_id;
    }

    function get_parents_recursive($id, $array = null) {
        $tmp_term = get_term($id, 'category');
        if ($tmp_term->parent) {
            $array[] = $tmp_term->parent;
            return $this->get_parents_recursive($tmp_term->parent, $array);
        } else {
            return $array;
        }
    }

    public function checkContentAvailableBlock($block_id, $post_id = null) {
        global $wpdb;
        $table = $wpdb->prefix . "custom_block_item_rules";
        //проверка подкатегорий
        $block_id=(int)$block_id;
        $subhead = $wpdb->get_var('SELECT subhead FROM ' . $wpdb->prefix . 'custom_block_item WHERE id=' . $block_id);
        if ($subhead == '1') {
            $subhead = true;
        } else {
            $subhead = false;
        }
        if ($post_id) {
            $post_category = array();
            $cat_tmp = get_the_terms((int) $post_id, 'category');
            if ($cat_tmp) {
                foreach ($cat_tmp as $ct) {
                    $post_category[] = $ct->term_id;
                }
            }
            if ($subhead && $post_category) {
                $tmp_cat = $post_category;
                foreach ($tmp_cat as $tc) {
                    $tmp_recusive = $this->get_parents_recursive($tc);
                    if ($tmp_recusive) {
                        $post_category = array_merge($post_category, $tmp_recusive);
                    }
                    $post_category = array_unique($post_category);
                }
            }
            if (count($post_category)) {
                $cat_post = implode(',', $post_category);
                $sql = 'SELECT allow FROM ' . $table . ' WHERE item_id=' . $block_id . ' AND (post_id=' . $post_id . ' OR category_id IN (' . $cat_post . ')) GROUP BY allow';
            } else {
                $sql = 'SELECT allow FROM ' . $table . ' WHERE item_id=' . $block_id . ' AND (post_id=' . $post_id . ') GROUP BY allow';
            }
            $query = $wpdb->get_var($sql);
            if ($query == '0') {
                return $this->checking_by_taxonomy($block_id, $post_id, false);
            } elseif ($query == '1') {
                return $this->checking_by_taxonomy($block_id, $post_id, true);
            }

            $templates = $wpdb->get_col("SELECT template_id FROM {$wpdb->prefix}custom_block_template_meta WHERE meta_key='include_block' AND meta_value={$block_id};");
            if ($templates) {
                //check get content rules
                $count_rules_template = (int) $wpdb->get_var("SELECT count(*) FROM {$wpdb->prefix}custom_block_template_meta WHERE template_id IN (" . implode(',', $templates) . ") AND meta_key='content' AND meta_value='1';");
                if ($count_rules_template > 0) {
                    $rules_template = $wpdb->get_results("SELECT meta_key,meta_value FROM {$wpdb->prefix}custom_block_template_meta WHERE template_id IN (" . implode(',', $templates) . ") AND meta_key IN ('content_by_category','content_by_post','content_by_category_ban','content_by_post_ban');");
                    if (count($post_category) && count($rules_template)) {
                        if (in_array($post_id, $this->get_content_from_object($rules_template, 'content_by_post_ban'))) {
                            return false;
                        }
                        
                        if ($this->get_content_from_object($rules_template, 'content_by_post')) {
                            if (in_array($post_id, $this->get_content_from_object($rules_template, 'content_by_post'))) {
                                return true;
                            } else {
                                return false;
                            }
                        }
                        
                        if (array_intersect($post_category, $this->get_content_from_object($rules_template, 'content_by_category_ban'))) {
                            return false;
                        }
                        
                        if ($this->get_content_from_object($rules_template, 'content_by_category')) {
                            if (array_intersect($post_category, $this->get_content_from_object($rules_template, 'content_by_category'))) {
                                return true;
                            } else {
                                return false;
                            }
                        }
                        
                    } elseif (count($rules_template)) {
                        if (in_array($post_id, $this->get_content_from_object($rules_template, 'content_by_post_ban'))) {
                            return false;
                        }
                        if ($this->get_content_from_object($rules_template, 'content_by_post')) {
                            if (in_array($post_id, $this->get_content_from_object($rules_template, 'content_by_post'))) {
                                return true;
                            } else {
                                return false;
                            }
                        }
                    }
                }
            }
        }
        //находим кол-во разрешающих правил для данного рекламного блока
        if ($wpdb->get_var('SELECT COUNT(*) FROM ' . $table . ' WHERE item_id=' . $block_id . ' AND allow=1') > 0) {
            return false;
        } else {
            return true;
        }
    }

    function get_content_from_object($obj, $name) {
        $return_values = array();
        if ($obj) {
            foreach ($obj as $obj_el) {
                if ($obj_el->meta_key == $name) {
                    $return_values[] = (int) $obj_el->meta_value;
                }
            }
        }
        return $return_values;
    }

    function checking_by_taxonomy($block_id, $post_id, $type_answer) {

        global $wpdb;
        $table = $wpdb->prefix . "custom_block_item_rules";
        $results = $wpdb->get_results('SELECT * FROM ' . $table . ' WHERE item_id=' . $block_id . ' AND ( taxonomy_id IS NOT NULL OR term_id IS NOT NULL )');
        if ($results) {
            $data = array();
            $taxonomies = array();
            foreach ($results as $res) {
                $allow_type = 'ban';
                if ($res->allow == '1') {
                    $allow_type = 'allow';
                }
                if ($res->taxonomy_id) {
                    $data['tax_' . $allow_type][] = $res->taxonomy_id;
                    $taxonomies[] = $res->taxonomy_id;
                }
                if ($res->term_id) {
                    $data['term_' . $allow_type][] = (int) $res->term_id;
                }
            }

            $terms_id_post = wp_get_post_terms($post_id, $taxonomies, array("fields" => "ids"));
            if (isset($data['term_ban']) && $data['term_ban'] && array_intersect($terms_id_post, $data['term_ban'])) {
                return false;
            }

            if (isset($data['term_allow']) && $data['term_allow'] && array_intersect($terms_id_post, $data['term_allow'])) {
                return true;
            } elseif (isset($data['term_allow']) && $data['term_allow']) {
                return false;
            }
            return $type_answer;
        } else {
            return $type_answer;
        }
        return $type_answer;
    }

    function object_to_array($obj) {
        if (is_object($obj))
            $obj = (array) $obj;
        if (is_array($obj)) {
            $new = array();
            foreach ($obj as $key => $val) {
                $new[$key] = $this->object_to_array($val);
            }
        } else
            $new = $obj;
        return $new;
    }

    public function rollItem($type_id, $post_id = null, $rotation = null, $time = null, $adblock = false) {
        global $wpdb;
        if (is_array($type_id)) {
            if (count($type_id) == 1) {
                $types = $type_id;
                $type_id = array_pop($types);
                unset($types);
            } else {
                $types = $type_id;
                $type_id = array_pop($types);
            }
        }
        $system_type_id = array(0, 1, 2);
        if (!in_array($type_id, $system_type_id)) {
            // Проверяем не отключени ли показ объявлений для заданного типа
            $sql = "SELECT `id` FROM " . $wpdb->prefix . "custom_block_resolution WHERE `block_id` = " . $this->id . " AND `resolution_id` = '" . $type_id . "'";
            if (!(int) $wpdb->get_var($sql))
                $type_id = 1;
            // Получаем список кодов для заданного разрешения
            $sql = "SELECT count(*) FROM " . $wpdb->prefix . "custom_block_item WHERE `block_id` = '" . $this->id . "' AND `type_id` = '" . $type_id . "'";
            $count = (int) $wpdb->get_var($sql);
            if ($count == 0) {
                if (isset($types) && count($types) > 0) {
                    return $this->rollItem($types, $post_id, $rotation, $time);
                }
                $sql = "SELECT count(*) FROM " . $wpdb->prefix . "custom_block_item WHERE `block_id` = '" . $this->id . "' AND `type_id` = 1";
                $count = (int) $wpdb->get_var($sql);
                if ($count) {
                    return $this->rollItem(1, $post_id, $rotation, $time);
                } else {
                    return $this->rollItem(0, $post_id, $rotation, $time);
                }
            }
            // Получаем ID опубликованных кодов для заданного расширения
            $sql = "SELECT * FROM " . $wpdb->prefix . "custom_block_item WHERE `block_id` = '" . $this->id . "' AND `published` = 1 AND `type_id` ='" . $type_id . "'";
            $rows = $wpdb->get_results($sql);
            if (count($rows) == 0) {
                $sql = "SELECT * FROM " . $wpdb->prefix . "custom_block_item WHERE `block_id` = '" . $this->id . "' AND `published` = 1";
                $rows = $wpdb->get_results($sql);
            }
        } else {
            if ($adblock) {
                $sql = "SELECT * FROM " . $wpdb->prefix . "custom_block_item WHERE `block_id` = '" . $this->id . "' AND `published` = 1 AND `type_id`=2";
                $rows = $wpdb->get_results($sql);
            } else {
                $sql = "SELECT * FROM " . $wpdb->prefix . "custom_block_item WHERE `block_id` = '" . $this->id . "' AND `published` = 1 AND `type_id`=1";
                if (!$rows = $wpdb->get_results($sql)) {
                    $sql = "SELECT * FROM " . $wpdb->prefix . "custom_block_item WHERE `block_id` = '" . $this->id . "' AND `published` = 1 AND `type_id`=0";
                    $rows = $wpdb->get_results($sql);
                }
            }
        }
        // Массив дозволленных ID. Из него потом выбираем рандомом
        $allowedIds = array();
        $disallowedIds = array();
        foreach ($rows as $row) {
            $templates_geo = false;
            $templates_content = false;
            $meta_keys_templates = array();
            if ($got_templates = $wpdb->get_col("SELECT template_id FROM " . $wpdb->prefix . "custom_block_template_meta WHERE meta_key='include_block' AND meta_value='" . $row->id . "';")) {
                $meta_keys_templates = $wpdb->get_results("SELECT meta_key,meta_value FROM " . $wpdb->prefix . "custom_block_template_meta WHERE template_id IN (" . implode(',', $got_templates) . ");");
                foreach ($meta_keys_templates as $mkt) {
                    if ($mkt->meta_key == 'geo' && $mkt->meta_value == '1') {
                        $templates_geo = true;
                    }
                    if ($mkt->meta_key == 'content' && $mkt->meta_value == '1') {
                        $templates_content = true;
                    }
                }
            }

            if ($row->content_filter || $templates_content) {
                //проверяем доступен ли контент
                if (!$this->checkContentAvailableBlock($row->id, $post_id)) {
                    continue;
                }
            }

            if ($row->geotargeting || $templates_geo) {
                // Получаем список GeoIP правил
                $sql = "SELECT * FROM (SELECT * FROM " . $wpdb->prefix . "custom_block_geoip WHERE item_id='" . $row->id . "' ORDER BY country_id ASC) a ORDER BY city_id";
                $rows2 = $wpdb->get_results($sql);

                if ($meta_keys_templates && $templates_geo) {
                    foreach ($meta_keys_templates as $mkt) {
                        switch ($mkt->meta_key) {
                            case 'geo_country_access':
                                $obj_row = new stdClass();
                                $obj_row->country_id = $mkt->meta_value;
                                $obj_row->allow = 1;
                                $rows2[] = $obj_row;
                                break;
                            case 'geo_country_ban':
                                $obj_row = new stdClass();
                                $obj_row->country_id = $mkt->meta_value;
                                $obj_row->allow = 0;
                                $rows2[] = $obj_row;
                                break;
                            case 'geo_city_access':
                                $obj_row = new stdClass();
                                $obj_row->city_id = $mkt->meta_value;
                                $obj_row->allow = 1;
                                $rows2[] = $obj_row;
                                break;
                            case 'geo_city_ban':
                                $obj_row = new stdClass();
                                $obj_row->city_id = $mkt->meta_value;
                                $obj_row->allow = 0;
                                $rows2[] = $obj_row;
                                break;
                        }
                    }
                }

                // Если правил нет, но просто добавляем код в список
                if (isset($rows2) and sizeof($rows2) == 0) {
                    $allowedIds[] = $row->id;

                    continue;
                }
                $country_id = (int) @$_COOKIE['country_id'];
                $city_id = (int) @$_COOKIE['city_id'];
                if (!$city_id) {
                    $cbp = new CustomBlocksPlugin();
                    $result = $cbp->init_action();
                    if ($result && isset($result[1])) {
                        $country_id = (int) $result[0];
                        $city_id = (int) $result[1];
                    }
                }
                $geo_array = array();
                foreach ($rows2 as $row2) {
                    if (isset($row2->country_id) && $row2->country_id) {
                        $geo_array[$row2->allow]['country'][] = $row2->country_id;
                    }

                    if (isset($row2->city_id) && $row2->city_id) {
                        $geo_array[$row2->allow]['city'][] = $row2->city_id;
                    }
                }
                //checked rules
                if ($this->check_geo_target($geo_array, $country_id, $city_id, $row->id)) {
                    $allowedIds[] = $row->id;
                }
            } else {
                $allowedIds[] = $row->id;
            }
        }
        if (isset($types) && count($types) > 0 && !sizeof($allowedIds)) {
            return $this->rollItem($types, $post_id, $rotation, $time);
        }
        if (!sizeof($allowedIds)) {
            return null;
        }
        $allowedIds = array_unique($allowedIds, SORT_NUMERIC);
        $allowedIds = CustomBlocksPlugin::check_time_targeting($allowedIds, $time);
        if (count($allowedIds) > 0) {
            sort($allowedIds);
            return CustomBlockItem::findById($this->rotationBlock($allowedIds, $rotation));
        } else {
            if (isset($types) && count($types) > 0) {
                return $this->rollItem($types, $post_id, $rotation, $time);
            }
            $result = $wpdb->get_var('SELECT id FROM ' . $wpdb->prefix . 'custom_block_item WHERE type_id=0 AND block_id=' . $this->id);
            if ($result) {
                return CustomBlockItem::findById($result);
            }
        }
    }

    public function check_geo_target($geo_array, $country, $city, $element_id) {
        $geo_array = $this->country_have_city_rules($geo_array, $element_id);
        //level 1 disallow city
        if (isset($geo_array[0]['city'])) {
            if (in_array($city, $geo_array[0]['city'])) {
                return false;
            }
        }
        //level 2 allow city
        if (isset($geo_array[1]['city'])) {
            if (in_array($city, $geo_array[1]['city'])) {
                return true;
            }
        }
        //level 3 disallow country
        if (isset($geo_array[0]['country']) && in_array($country, $geo_array[0]['country'])) {
            return false;
        }
        //level 4 allow country
        if (isset($geo_array[1]['country'])) {
            if ($geo_array[1]['country']) {
                if (in_array($country, $geo_array[1]['country'])) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
        return true;
    }

    public function country_have_city_rules($geo, $element_id) {
        global $wpdb;
        $allow = 0;
        if (isset($geo[$allow]['city']) && $geo[$allow]['city']) {
            $uses_country = $wpdb->get_col("SELECT country_id FROM " . $wpdb->prefix . "custom_block_geoip WHERE allow='" . $allow . "' AND item_id='" . $element_id . "' AND city_id IN ('" . implode("','", $geo[$allow]['city']) . "');");
            $tmp = $geo[$allow]['country'];
            $geo[$allow]['country'] = array_diff($tmp, $uses_country);
        }
        $allow = 1;
        if (isset($geo[$allow]['city']) && $geo[$allow]['city']) {
            $uses_country = $wpdb->get_col("SELECT country_id FROM " . $wpdb->prefix . "custom_block_geoip WHERE allow='" . $allow . "' AND item_id='" . $element_id . "' AND city_id IN ('" . implode("','", $geo[$allow]['city']) . "');");
            $tmp = $geo[$allow]['country'];
            $geo[$allow]['country'] = array_diff($tmp, $uses_country);
        }

        return $geo;
    }

    public function resolutionEnabled($resolution_id) {
        if (!$this->id)
            return true;

        global $wpdb;
        $sql = "SELECT `id` FROM " . $wpdb->prefix . "custom_block_resolution WHERE `block_id` = " . $this->id . " AND `resolution_id` = '" . $resolution_id . "'";
        return (bool) (int) $wpdb->get_var($sql);
    }

    public function rotationBlock($allowedIds, $rotation) {
        if ($rotation && !is_array($rotation)) {
            $t_rotation = base64_decode($rotation);
            $t_rotation = json_decode($t_rotation);
            $rotation = $this->object_to_array($t_rotation);
        }

        if (!$rotation || ($rotation && is_array($rotation) && count($rotation) == 1)) {
            $showes = $this->get_showes($allowedIds);
            if ($showes) {
                foreach ($showes as $key => $list) {
                    return $key;
                }
            }
            return $allowedIds[0];
        }

        if (count($allowedIds) == 1) {
            return $allowedIds[0];
        } elseif (count($allowedIds) > 1) {
            if (isset($rotation['last'][$this->id])) {
                $last_id_block = (int) $rotation['last'][$this->id];
                foreach ($this->get_showes($allowedIds) as $key => $list) {
                    if ($last_id_block <> $key) {
                        return $key;
                    }
                }
                return $allowedIds[0];
            }
        }
        return $allowedIds[0];
    }

    public function get_showes($allowedIds) {
        global $wpdb;
        $sql = "SELECT id,show_index FROM `" . $wpdb->prefix . "custom_block_item` WHERE id IN (" . implode(', ', $allowedIds) . ") ORDER BY show_index;";
        $result = $wpdb->get_results($sql);
        $list = array();
        if ($result) {
            foreach ($result as $value) {
                $list[$value->id] = $value->show_index;
            }
        }
        return $list;
    }

}
