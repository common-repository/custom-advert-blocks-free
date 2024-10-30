<?php

class CustomBlockItem {

    public $new = true;
    public $id = 0;
    public $block_id = 0;
    public $type_id = 0;
    public $title = "";
    public $html = "";
    public $show = 0;
    public $click = 0;
    public $published = 0;
    public $geotargeting = 0;
    public $content_filter = 0;
    public $show_index = 0;
    public $subhead = 0;

    public function __construct() {
        
    }

    static public function findById($id) {
        global $wpdb;
        $id=(int)$id;
        $sql = "SELECT * FROM " . $wpdb->prefix . "custom_block_item WHERE `id` = " . $id;
        $row = $wpdb->get_row($sql);

        if (!$row)
            return null;

        $item = new self;
        $item->id = $row->id;
        $item->block_id = $row->block_id;
        $item->type_id = $row->type_id;
        $item->title = stripslashes($row->title);
        $item->html = stripslashes($row->html);
        $item->show = $row->show;
        $item->show_index = $row->show_index;
        $item->click = $row->click;
        $item->published = $row->published;
        $item->geotargeting = $row->geotargeting;
        $item->content_filter = $row->content_filter;
        $item->new = false;
        $item->subhead = $row->subhead;
        return $item;
    }

    public function save() {
        global $wpdb;
        $table = $wpdb->prefix . 'custom_block_item';
        if (!$this->new) {
            $data = array(
                'title' => $this->title,
                'html' => $this->html,
                'block_id' => $this->block_id,
                'type_id' => $this->type_id,
                'show' => $this->show,
                'click' => $this->click,
                'geotargeting' => $this->geotargeting,
                'content_filter' => $this->content_filter,
                'published' => $this->published,
                'show_index' => $this->show_index,
                'subhead' => $this->subhead,
            );
            $wpdb->update($table, $data, array('id' => $this->id), array('%s', '%s', '%d', '%d', '%d', '%d', '%d', '%d', '%d'));
        } else {
            $data = array(
                'block_id' => $this->block_id,
                'type_id' => $this->type_id,
                'title' => $this->title,
                'html' => $this->html,
                'show' => $this->show,
                'click' => $this->click,
                'published' => $this->published,
                'geotargeting' => $this->geotargeting,
                'content_filter' => $this->content_filter,
                'show_index' => $this->show_index,
                'subhead' => $this->subhead,
            );
            $wpdb->insert($table, $data, array('%d', '%d', '%s', '%s', '%d', '%d', '%d', '%d', '%d'));
        }
        if (!$this->id) {
            $this->id = $wpdb->insert_id;
        }
        return $this->id;
    }

    public function delete() {
        global $wpdb;

        $sql = "DELETE FROM " . $wpdb->prefix . "custom_block_item WHERE `id` = '" . $this->id . "'";
        $wpdb->query($sql);
    }

}
