<?php
class wpiCustomSortFilterSettings
{

    public function __construct()
    {
       add_action( 'admin_menu', array($this,'wpi_add_admin_menu') );
       add_action( 'admin_init', array($this,'wpi_settings_init' ));
    }

    /**
     * Add options page
     */

    function wpi_add_admin_menu(  ) { 

            add_options_page( 'Custom Admin columns for Sorting and Filtering', 'Custom Admin columns for Sorting and Filtering', 'manage_options', 'custom_admin_columns_for_sorting_and_filtering', array($this,'wpi_options_page') );

    }
    
    function wpi_settings_init(  ) { 

            register_setting( 'pluginPage', 'wpi_custom_filter_settings' );

            add_settings_section(
                    'wpi_pluginPage_section', 
                    __( 'Add Custom Fields', 'test' ), 
                    array($this,'wpi_settings_section_callback'), 
                    'pluginPage'
            );

            add_settings_field( 
                    'wpi_text_field_0', 
                    __( '', 'test' ), 
                    array($this,'wpi_text_field_0_render'), 
                    'pluginPage', 
                    'wpi_pluginPage_section' 
            );


    }


    function wpi_text_field_0_render(  ) { 

            $options = get_option( 'wpi_custom_filter_settings' );
            
            echo "<table><tr><th>Post Type</th><th>Type</th><th>Parameters</th></tr>";
            $args=array('_builtin'=>FALSE);
            
            foreach ( get_post_types( $args, 'names' ) as $post_type ) {
                $post_type_lables=$post_type."_labels";
                echo "<tr>";
                echo "<td><label for='".$post_type."_field'><strong>".$post_type."</strong></label></td>";
                echo "<td>Fields</td>";
                echo "<td><input type='text' id='".$post_type."_field' name='wpi_custom_filter_settings[".$post_type."]' size='50' value='".$options[$post_type]."'></td>";
                echo "</tr>";
                
                echo "<tr>";
                echo "<td><label for='".$post_type."_label'><strong>".$post_type."</strong></label></td>";
                echo "<td>Labels</td>";
                echo "<td><input type='text' id='".$post_type."_label' name='wpi_custom_filter_settings[".$post_type."_labels]' size='50' value='".$options[$post_type_lables]."'></td>";
                echo "</tr>";
                
                echo "<tr>";
                echo "<td colspan=3></td>";
                echo "</tr>";
            }
            echo "</table>";

    }
    
    
    function wpi_settings_section_callback(  ) { 

	echo __( 'Add your custom fields and labels for sorting and filtering for the corresponding post type as comma separated values in the Parameters Box', 'test' );

    }


    function wpi_options_page(  ) { 

            ?>
            <form action='options.php' method='post'>

                    <h2>Custom Admin columns for Sorting and Filtering</h2>

                    <?php
                    settings_fields( 'pluginPage' );
                    do_settings_sections( 'pluginPage' );
                    submit_button();
                    ?>

            </form>
            <?php

    }
}
if( is_admin() )
    new wpiCustomSortFilterSettings();

