<?php
/*
Plugin Name: Menu Duplicator
Description: Lets you duplicate your menu very easily
Author: Swayam Tejwani
Version: 1.0
*/

define( 'MENU_DUPLICATOR_DIR',       plugin_dir_path( __FILE__ ) );
define( 'MENU_DUPLICATOR_URL',       plugin_dir_url( __FILE__ ) );


/*
* To Provide settings link on plugins page in backend.
*/
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'menu_duplicator_add_action_links' );

function menu_duplicator_add_action_links ( $links ) {
 $settings_page_link = array(
 '<a href="' . admin_url( 'admin.php?page=menu-duplicator' ) . '" title="Menu Duplicator Settings">Settings</a>',
 );
return array_merge(  $settings_page_link, $links );
}



/*
* Registers page in wordpress backend.
*/
function menu_duplicator_options_page(){
	
		global $menu_duplicator_page;
		$menu_duplicator_page = add_menu_page('Menu Duplicator','Menu Duplicator','manage_options', 'menu-duplicator', 'menu_duplicator_output_page');
	
}

add_action('admin_menu','menu_duplicator_options_page');


/*
* Function to output settings page form.
*/
function menu_duplicator_output_page(){
	  ?>
    <div class="wrap">
        <div id="icon-options-general" class="icon32"><br /></div>
            <h2><?php _e( 'Menu Duplicator' ); ?></h2>
			
			<?php
			$all_menus = wp_get_nav_menus();
			
			 if ( empty( $all_menus ) ) : ?>
                <p><?php _e( "You haven't created any Menus yet." ); ?></p>
            <?php else: 
			
			echo '<span style="display:block; margin-top:20px;">Select menu to duplicate</span>';
			
			echo '<select style="display:block; margin-top:10px;" name="base_menu" id="all_menus">';
			
			echo '<option value="">Select</option>';
			
			foreach($all_menus as $menu){
				echo '<option value="'.$menu->term_id.'">'.$menu->name.'</option>';
			}
			
			echo '</select>';
			
			echo '<span style="display:block; margin-top:20px;">New Menu Name</span>';
			echo '<input style="display:block; margin-top:10px;" type="text" name="new_menu_called" id="new_menu_called" placeholder="Menu Name">';
			echo '<input id="duplicator_btn" style="display:block; margin-top:20px;" type="button" class="button-primary" value="Duplicate Menu">';
			echo '<div id="response"></div>';
			
			endif;
			
			?>
			
			
	</div>
	
	<?php
	
}


/*
* Enqueue javascript needed for ajax & field validation for backend form.
*/

function menu_duplicator_enqueue_assets(){
	
	$screen = get_current_screen();
	//toplevel_page_menu-duplicator
	global $menu_duplicator_page;
	
	if($screen->id == $menu_duplicator_page){
		
		wp_enqueue_script( 'menu-duplicator-js', MENU_DUPLICATOR_URL.'js/menu-duplicator.js', array('jquery'), '1.0', true );
		
	}
}
add_action('admin_enqueue_scripts','menu_duplicator_enqueue_assets');


/*
* Ajax Callback to perform menu duplication
*/
function menu_duplicator_perform_duplication(){

	if(isset($_POST)){
		
		$new_name = sanitize_text_field($_POST['new_name']);
		$menu_id = intval($_POST['menu_id']);
		
		$old_menu = wp_get_nav_menu_object( $menu_id );
        $old_menu_items = wp_get_nav_menu_items( $menu_id );
		
		$new_menu_id = wp_create_nav_menu( $new_name );
		
		 if ( ! $new_menu_id ) {
           echo "0";
        }else{
			// key is the original db ID, val is the new
        $rel = array();

        $i = 1;
        foreach ( $old_menu_items as $menu_item ) {
            $args = array(
                'menu-item-db-id'       => $menu_item->db_id,
                'menu-item-object-id'   => $menu_item->object_id,
                'menu-item-object'      => $menu_item->object,
                'menu-item-position'    => $i,
                'menu-item-type'        => $menu_item->type,
                'menu-item-title'       => $menu_item->title,
                'menu-item-url'         => $menu_item->url,
                'menu-item-description' => $menu_item->description,
                'menu-item-attr-title'  => $menu_item->attr_title,
                'menu-item-target'      => $menu_item->target,
                'menu-item-classes'     => implode( ' ', $menu_item->classes ),
                'menu-item-xfn'         => $menu_item->xfn,
                'menu-item-status'      => $menu_item->post_status
            );

            $parent_id = wp_update_nav_menu_item( $new_menu_id, 0, $args );

            $rel[$menu_item->db_id] = $parent_id;

            if ( $menu_item->menu_item_parent ) {
                $args['menu-item-parent-id'] = $rel[$menu_item->menu_item_parent];
                $parent_id = wp_update_nav_menu_item( $new_menu_id, $parent_id, $args );
            }

            $i++;
        }
		
		echo $new_menu_id;
		}
		
	}
	die;
}
add_action( 'wp_ajax_perform_duplication', 'menu_duplicator_perform_duplication' );