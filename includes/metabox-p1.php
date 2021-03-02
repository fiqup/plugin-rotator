<?php
function wpbc_contacts_page_handler()
{
    global $wpdb;

    $table = new Custom_Table_Example_List_Table();
    $table->prepare_items();

    $message = '';
    if ('delete' === $table->current_action()) {
        $message = '<div class="updated below-h2" id="message"><p>' . sprintf(__('Items deleted: %d', 'wpbc'), count((array)$_REQUEST['id'])) . '</p></div>';
    }
    ?>
<div class="wrap">

    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2><?php _e('Contacts', 'wpbc')?> <a class="add-new-h2"
                                 href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=contacts_form');?>"><?php _e('Add new', 'wpbc')?></a>
    </h2>
    <?php echo $message; ?>

    <form id="contacts-table" method="POST">
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
        <?php $table->display() ?>
    </form>

</div>
<?php
}


function wpbc_contacts_form_page_handler()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'datacs'; 

    $message = '';
    $notice = '';

    $default = array(
        'id' => '',
        'name'      => '',
        'phone'     => '',
        'note'      => '',
    );


    if ( isset($_REQUEST['nonce']) && wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__))) {
        
        $item = shortcode_atts($default, $_REQUEST);     

        $item_valid = wpbc_validate_contact($item);
        if ($item_valid === true) {
            if ($item['id'] == '') {
                
                $user_count = $wpdb->get_var( 
                    "SELECT COUNT(*) 
                    FROM {$wpdb->prefix}datacs" );
                    $id=$user_count+1;
                    $item['id'] = $id;
                    
                    $result = $wpdb->insert($table_name, $item);
                    
                    if ($result) {
                        $message = __('Item was successfully saved', 'wpbc');
                    } else {
                        $notice = __('There was an error while saving item', 'wpbc');
                    }
                } else {
                $result = $wpdb->update($table_name, $item, array('id' => $item['id']));
                if ($result) {
                    $message = __('Item was successfully updated', 'wpbc');
                } else {
                    $notice = __('There was an error while updating item', 'wpbc');
                }
            }
        } else {
            
            $notice = $item_valid;
        }
    }
    else {
        
        $item = $default;
        if (isset($_REQUEST['phone'])) {
            $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE phone = %s", $_REQUEST['phone']), ARRAY_A);
            if (!$item) {
                $item = $default;
                $notice = __('Item not found', 'wpbc');
            }
        }
    }

    
    add_meta_box('contacts_form_meta_box', __('Contact data', 'wpbc'), 'wpbc_contacts_form_meta_box_handler', 'contact', 'normal', 'default');

    ?>
<div class="wrap">
    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2><?php _e('Contact', 'wpbc')?> <a class="add-new-h2"
                                href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=contacts');?>"><?php _e('back to list', 'wpbc')?></a>
    </h2>

    <?php if (!empty($notice)): ?>
    <div id="notice" class="error"><p><?php echo $notice ?></p></div>
    <?php endif;?>
    <?php if (!empty($message)): ?>
    <div id="message" class="updated"><p><?php echo $message ?></p></div>
    <?php endif;?>
    <form id="form" method="POST">
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(basename(__FILE__))?>"/>
        <div class="metabox-holder" id="poststuff">
            <div id="post-body">
                <div id="post-body-content">
                    
                    <?php do_meta_boxes('contact', 'normal', $item); ?>
                    <input type="submit" value="<?php _e('Save', 'wpbc')?>" id="submit" class="button-primary" name="submit">
                </div>
            </div>
        </div>
    </form>
</div>
<?php
}

function wpbc_contacts_form_meta_box_handler($item)
{
    ?>
<tbody >
		
	<div class="formdatabc">		
		
    <form >

		<div class="form2bc">
        <p>	
		    <label for="name"><?php _e('Name:', 'wpbc')?></label>
		<br>	
            <input id="name" name="name" type="text" value="<?php echo esc_attr($item['name'])?>"
                    required>
        </p>
		</div>	
       
		<div class="form2bc">
        <p>	  
            <label for="phone"><?php _e('Phone:', 'wpbc')?></label> 
		<br>
			<input id="phone" name="phone" type="tel" value="<?php echo esc_attr($item['phone'])?>">
		</p>
		</div>

        <div class="form2bc">
        <p>P.S input nomer telpon menggunakan awalan 62, bukan 0</p>
        </div>
	
		<div class="form2bc">
        <p>	  
            <label for="note"><?php _e('Note:', 'wpbc')?></label> 
		<br>
			<input id="note" name="note" type="tel" value="<?php echo esc_attr($item['note'])?>" placeholder="Saya ingin memesan ">
		</p>
		</div>

        <div class="form2bc">
        <p>P.S Abaikan kolom "Note" untuk menggunakan pesan default</p>
        </div>
        
		</form>
		</div>
</tbody>
<?php
}



