<?php
class wpiCustomFilter {
    var $fields;
    var $field_headings;
    var $unset_cols;
    
    public function __construct() {
        $wpi_admin_custom_filter_options = get_option( 'wpi_custom_filter_settings' );
        $this->post_type = esc_attr($_GET['post_type']);
        
        $wpi_cf_fields=esc_attr($wpi_admin_custom_filter_options[$this->post_type]);
        $wpi_cf_labels=esc_attr($wpi_admin_custom_filter_options[$this->post_type.'_labels']);
        
        if($wpi_cf_fields != NULL) $post_types_arr[]=$post_type;
        $this->post_type_arr=$post_types_arr;
        
        if($wpi_cf_labels==NULL) $wpi_cf_labels=$wpi_cf_fields;
        $this->wpi_cf_fields = explode(',',$wpi_cf_fields);
        $this->wpi_cf_labels = explode(',',$wpi_cf_labels);
        
        add_filter('manage_edit-'.$this->post_type.'_columns', array($this, 'wpi_table_head'));
        add_action('manage_'.$this->post_type.'_posts_custom_column', array($this, 'wpi_table_content'), 10, 2);
        add_filter('manage_edit-'.$this->post_type.'_sortable_columns', array($this, 'wpi_table_sorting'));
        add_filter('request', array($this, 'wpi_column_orderby'));

        add_filter('parse_query', array($this, 'wpi_admin_posts_filter'));
        add_action('restrict_manage_posts', array($this, 'wpi_admin_posts_filter_restrict_manage_posts'));
        
        $this->fields=$this->wpi_cf_fields;
        $this->field_headings=$this->wpi_cf_labels;
        $this->unset_cols=array('comments','taxonomy-term_category','tags','author');
    }

    function wpi_table_head($defaults) {
        $i=0;
        foreach($this->fields as $field){ 
            $defaults[$field] = $this->field_headings[$i];
            $i++;
        }
        
        foreach($this->unset_cols as $ucols){ 
            unset($defaults[$ucols]);
        }

        return $defaults;
    }

    function wpi_table_content($column, $post_id) {
        foreach($this->fields as $field){
            if($column == $field)
               echo get_post_meta($post_id, $field, true); 
        }
    }

    function wpi_table_sorting($columns) {
        foreach($this->fields as $field){ 
            $columns[$field] = $field;
        }
        return $columns;
        
    }

    function wpi_column_orderby($vars) {
        if (is_admin() && $pagenow == 'edit.php') {
        foreach($this->fields as $field){
            if (isset($vars['orderby']) && $field == $vars['orderby']) {
                $vars = array_merge($vars, array(
                    'meta_key' => $field,
                    'orderby' => 'meta_value'
                 ));
            }
        }
        }
        return $vars;
        
    }

    function wpi_admin_posts_filter($query) {
        global $pagenow;

        $qv = &$query->query_vars;
        if (is_admin() && $pagenow == 'edit.php') {
            if ($_GET['filter_action'] == 'Filter') {
                global $wpdb;
                $i=0;
                foreach($this->fields as $field){
                    if ($_GET[$field] == NULL){
                        $rows=$wpdb->get_results("SELECT DISTINCT meta_value FROM ".$wpdb->prefix."postmeta WHERE meta_key='".$field."'");
                        foreach($rows as $row)
                            $field_vals[$i][]=$row->meta_value;
                    }
                    else
                        $field_vals[$i] = array($_GET[$field]);
                    $i++;
                }
                
                $count=0;
                foreach($this->fields as $field){
                    $meta_arrays[] = array(
                        'key' => $field,
                        'value' => $field_vals[$count]
                        );
                    $count++;
                }
                
                
                $args = array(
                    'meta_query' => array(
                        $meta_arrays
                    )
                );
 
            }
            $query->set('meta_query', $args);
            return $query;
        }
    }

    function wpi_admin_posts_filter_restrict_manage_posts() {
        global $wpdb;
        global $pagenow;
        if (is_admin() && $pagenow == 'edit.php') {
            foreach($this->fields as $field){
               $result = $wpdb->get_results("SELECT DISTINCT meta_value FROM ".$wpdb->prefix."postmeta WHERE meta_key='".$field."'");
            echo '<select name="'.$field.'">';
            echo '<option value="">ALL</option>';
            foreach ($result as $print) {
                ?>
                <option value="<?php echo $print->meta_value; ?>" <?php if ($_GET[$field] == $print->meta_value) {
                    echo 'selected="selected"';
                } ?>><?php echo $print->meta_value; ?></option>	
                <?php
            }
            echo '</select>'; 
            }

        }
    }

}
?>
