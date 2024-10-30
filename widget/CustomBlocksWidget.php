<?php

class CB_Widget extends WP_Widget {

	public function __construct() {
		parent::__construct(false, __('Custom Blocks', 'custom-blocks-free'));
	}

	public function widget( $args, $instance ) {
            echo $args['before_widget'];
            if (isset($instance["title"]) && $instance["title"])
            {
                echo $args['before_title'];
                echo $instance["title"];
                echo $args['after_title'];
            }
            echo do_shortcode('[block id="'.$instance["id"].'"]');
            echo $args['after_widget'];
	}

	public function form( $instance ) {
            global $wpdb;
        $title = "";
        $id = 0;

        if (!empty($instance)) {
            $title = $instance["title"];
            $id = $instance["id"];
        }

        $tableId = $this->get_field_id("title");
        $tableName = $this->get_field_name("title");
        echo '<label for="' . $tableId . '">'.__('Title', 'custom-blocks-free').'</label><br>';
        echo '<input id="' . $tableId . '" type="text" name="' .
            $tableName . '" value="' . $title . '"><br>';
        
        $tableId = $this->get_field_id("id");
        $tableName = $this->get_field_name("id");
        echo '<label for="' . $tableId . '">'.__('Choose the block', 'custom-blocks-free').'</label><br>';
        if ($select=$wpdb->get_results("SELECT id,title FROM ".$wpdb->prefix."custom_block WHERE published=1",ARRAY_A))
        {
            echo '<select id="' . $tableId . '" name="' .$tableName . '">';
            foreach ($select as $value)
            {
                $selected=($value['id']==$id)?' selected':'';
                echo '<option value="'.$value['id'].'" '.$selected.'>'.$value['title'].'</option>';
            }
            echo '</select>';
        }
	}

	public function update( $new_instance, $old_instance ) {
            $values = array();
            $values["title"] = $new_instance["title"];
            $values["id"] = $new_instance["id"];
            return $values;
	}
}

function cb_register_widgets()
{
    register_widget('CB_Widget');
}

add_action('widgets_init', 'cb_register_widgets');

 