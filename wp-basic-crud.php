<?php
/**
* Plugin Name: Whatshapp Rotator
* Description: Tugas Kp
* Version:     2.1.3
* Plugin URI: https://labarta.es/wp-basic-crud-plugin-wordpress/
* Author:      Labarta
* Author URI:  https://labarta.es/
* License:     GPLv2 or later
* License URI: https://www.gnu.org/licenses/gpl-2.0.html
* Text Domain: wpbc
* Domain Path: /languages
*/

defined( 'ABSPATH' ) or die( '¡Sin trampas!' );

function rotatorinit(){
    if(isset($_GET['something'])) {
        wpbc_handler_link_wa_page();
      }

}

add_action('init', 'rotatorinit');


require plugin_dir_path( __FILE__ ) . 'includes/metabox-p1.php';    

function wpbc_custom_admin_styles() {
    wp_enqueue_style('custom-styles', plugins_url('/css/styles.css', __FILE__ ));
    }
add_action('admin_enqueue_scripts', 'wpbc_custom_admin_styles');


function wpbc_plugin_load_textdomain() {
load_plugin_textdomain( 'wpbc', false, basename( dirname( __FILE__ ) ) . '/languages' ); 
}
add_action( 'plugins_loaded', 'wpbc_plugin_load_textdomain' );


global $wpbc_db_version;
$wpbc_db_version = '1.1.0'; 


function wpbc_install()
{
    global $wpdb;
    global $wpbc_db_version;

    $table_name = $wpdb->prefix . 'datacs'; 


    $sql = "CREATE TABLE " . $table_name . " (
      id int(11) NOT NULL,
      name VARCHAR (50) NOT NULL,
      phone VARCHAR(15) NOT NULL,
      click int(11) NOT NULL,
      note text NOT NULL,
      PRIMARY KEY  (phone)
    );";


    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    $table_name2 = $wpdb->prefix . 'data_performa'; 


    $sql = "CREATE TABLE " . $table_name2 . " (
        time DATE NOT NULL DEFAULT CURRENT_TIMESTAMP,
      phone VARCHAR(15) NOT NULL,
      FOREIGN KEY (phone) REFERENCES ".$table_name."(phone)
    );";


    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    add_option('wpbc_db_version', $wpbc_db_version);

    $installed_ver = get_option('wpbc_db_version');
    if ($installed_ver != $wpbc_db_version) {
        $sql = "CREATE TABLE " . $table_name . " (
          id int(11) NOT NULL,
          name VARCHAR (50) NOT NULL,
          phone VARCHAR(15) NOT NULL,
          PRIMARY KEY  (phone)
        );";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        $sql = "CREATE TABLE " . $table_name2 . " (
            time DATE NOT NULL DEFAULT CURRENT_TIMESTAMP,
          phone VARCHAR(15) NOT NULL,
          FOREIGN KEY (phone) REFERENCES ".$table_name."(phone)
        );";
    
    
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        update_option('wpbc_db_version', $wpbc_db_version);
    }
}

register_activation_hook(__FILE__, 'wpbc_install');


function wpbc_install_data()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'datacs'; 
    $table_name2 = $wpdb->prefix . 'data_performa'; 

}

register_activation_hook(__FILE__, 'wpbc_install_data');


function wpbc_update_db_check()
{
    global $wpbc_db_version;
    if (get_site_option('wpbc_db_version') != $wpbc_db_version) {
        wpbc_install();
    }
}

add_action('plugins_loaded', 'wpbc_update_db_check');



if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}


class Custom_Table_Example_List_Table extends WP_List_Table
 { 
    function __construct()
    {
        global $status, $page;

        parent::__construct(array(
            'singular' => 'contact',
            'plural'   => 'contacts',
        ));
    }


    function column_default($item, $column_name)
    {
        return $item[$column_name];
    }


    function column_phone($item)
    {
        return '<em>' . $item['phone'] . '</em>';
    }

    function column_id($item)
    {
        

        return '</em>' . $item['id'] . '</em>';
    }

    function column_click($item)
    {
        

        return '</em>' . $item['click'] . '</em>';
    }


    function column_name($item)
    {

        $actions = array(
            'edit' => sprintf('<a href="?page=contacts_form&id=%s">%s</a>', $item['id'], __('Edit', 'wpbc')),
            'delete' => sprintf('<a href="?page=%s&action=delete&id=%s">%s</a>', $_REQUEST['page'], $item['id'], __('Delete', 'wpbc')),
        );

        return sprintf('%s %s',
            $item['name'],
            $this->row_actions($actions)
        );
    }


    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="id[]" value="%s" />', 
            $item['id']
        );
    }

    function get_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />', 
            'name'      => __('Name', 'wpbc'),
            'phone'     => __('Phone', 'wpbc'),
            'id'     => __('Id', 'wpbc'),
            'click'     => __('Total Click', 'wpbc'),


        );
        return $columns;
    }

    function get_sortable_columns()
    {
        $sortable_columns = array(
            'name'      => array('name', true),
            'phone'     => array('phone', true),
            'id'     => array('id', true),
            'click'     => array('click', true),
        );
        return $sortable_columns;
    }

    function get_bulk_actions()
    {
        $actions = array(
            'delete' => 'Delete'
        );
        return $actions;
    }

    function process_bulk_action()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'datacs'; 

        if ('delete' === $this->current_action()) {
            $ids = isset($_REQUEST['id']) ? $_REQUEST['id'] : array();
            $firstId=$ids[0];
            $lastId=$ids[0];
            if (is_array($ids)){ 
                $firstId= $ids[0];
                $lastId = end($ids);
                $ids = implode(',', $ids);
            };

            if (!empty($ids)) {
                $user_count = $wpdb->get_var( 
                    "SELECT COUNT(*) 
                    FROM {$wpdb->prefix}datacs" );
                $wpdb->query("DELETE FROM $table_name WHERE id IN($ids)");
                $j=$firstId;
                for ($i=$lastId; $i < $user_count ; $i++) {
                    $wpdb->query("UPDATE $table_name SET id = $j WHERE id = ".($i+1));
                    $j++;
                }
            }
        }
    }

    function prepare_items()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'datacs'; 

        $per_page = 10; 

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        
        $this->_column_headers = array($columns, $hidden, $sortable);
       
        $this->process_bulk_action();
        
        $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name");


        $paged = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged']) - 1) : 0;
        $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'name';
        $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'asc';


        $this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY $orderby $order LIMIT %d OFFSET %d", $per_page, $paged), ARRAY_A);


        $this->set_pagination_args(array(
            'total_items' => $total_items, 
            'per_page' => $per_page,
            'total_pages' => ceil($total_items / $per_page) 
        ));
    }
}

function wpbc_admin_menu()
{
    add_menu_page(__('WA Rotator', 'wpbc'), __('WA Rotator', 'wpbc'), 'activate_plugins', 'contacts', 'wpbc_contacts_page_handler');
    
    add_submenu_page('contacts', __('Contact CS', 'wpbc'), __('Contact CS', 'wpbc'), 'activate_plugins', 'contacts', 'wpbc_contacts_page_handler');
   
    add_submenu_page('contacts', __('Add new', 'wpbc'), __('Add new', 'wpbc'), 'activate_plugins', 'contacts_form', 'wpbc_contacts_form_page_handler');

    add_submenu_page('contacts', __('Link', 'wpbc'), __('Link', 'wpbc'), 'activate_plugins', 'wa_setting_page', 'wpbc_page_handler_link');

    add_submenu_page('contacts', __('Data', 'wpbc'), __('Hitz', 'wpbc'), 'activate_plugins', 'wa_data_page', 'wpbc_page_handler_data');

    add_submenu_page('contacts', __('Wa', 'wpbc'), __('Wa', 'wpbc'), 'activate_plugins', 'wa_link_page', 'wpbc_handler_link_wa_page');
    
}

add_action('admin_menu', 'wpbc_admin_menu');


function wpbc_validate_contact($item)
{
    $messages = array();

    if (empty($item['name'])) $messages[] = __('Name is required', 'wpbc');
    if(!empty($item['phone']) && !absint(intval($item['phone'])))  $messages[] = __('Phone can not be less than zero');
    if(!empty($item['phone']) && !preg_match('/[0-9]+/', $item['phone'])) $messages[] = __('Phone must be number');
    

    if (empty($messages)) return true;
    return implode('<br />', $messages);
}


function wpbc_languages()
{
    load_plugin_textdomain('wpbc', false, dirname(plugin_basename(__FILE__)));
}

add_action('init', 'wpbc_languages');

function wpbc_page_handler_link(){ 

    ?><p>Coba klik link dibawah ini</p><?php

   wa_link_page();

    ?> <br> <br> atau <br> <br> tulis fungsi "[whatsup-plugin]" di laman wordpress<?php
}

//sini
add_shortcode('whatsup-plugin', 'wa_link_page');

function wa_link_page(){

    global $konten; 
    global $nilai;
    global $wpdb;

    $user_count = $wpdb->get_var( 
    "SELECT COUNT(*) 
    FROM {$wpdb->prefix}datacs" );

    //jumlah total akun user
    $total=$user_count; 

    //dataklik
    $namaFile = 'data1.txt';
    $nilai = file_get_contents($namaFile);
        
    if($nilai >= $total){
        $konten = '1';
        
    }else{
        $konten = $nilai + 1;
    }
    

    $file = fopen($namaFile, 'w');
    fwrite($file, $konten);
    fclose($file);

    echo ("nilai =");
    echo $nilai;
    echo("---");
    echo ("konten =");
    echo $konten;
    echo("---");
    echo ("total =");
    echo $total;


    $id=$konten;
    $kontak = $wpdb->get_var( $wpdb->prepare
    ( "SELECT phone from {$wpdb->prefix}datacs 
    where id = %d", $id ) 
    );

    $pesan = $wpdb->get_var( $wpdb->prepare
    ( "SELECT note from {$wpdb->prefix}datacs 
    where id = %d", $id ) 
    );
    
    
    $url = 'https://api.whatsapp.com/send?phone='.$kontak.'&text='.$pesan;

    ?>
    <br>
    <form action="<?php echo get_site_url() ?>/wp-content/plugins/wp-basic-crud/SRC/wa-rotator.php" method="post">
        <input type="hidden" name="url" value=<?php echo $url ?> >
        <button type ="submit">
            Beli Di sini
        </button>
    </form>
    <br>
    
    <br>
   
    <br>
    
    <?php

    $data_performa = $wpdb->prefix . 'data_performa';
    $item['phone'] = $kontak;
    $result = $wpdb->insert($data_performa, $item);

    $wpdb->query("UPDATE {$wpdb->prefix}datacs SET 
         `click` = (`click` + 1)
       WHERE `phone` = '$kontak'");
    
}


function wpbc_page_handler_data(){

    global $wpdb;
    
    $results = $wpdb->get_results("SELECT * FROM wp_datacs as datacs LEFT JOIN wp_data_performa as data_performa ON datacs.phone = data_performa.phone");
    table_users($results);
    return $results;
}

add_shortcode('data_statistik', 'wpbc_page_handler_data');

function table_users($data){
    $no=1;
    ?>
        <table>
            <thead>
                <tr>
                    <th>No. </th>
                    <th>Nama</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach( $data as $row){
                    echo"
                    <tr>
                        <td>".$no++."</td>
                        <td>".$row->name."</td>
                        <td>".$row->time."</td>
                    </tr>
                    ";
                }
                ?>

             
            </tbody>
        </table>
    <?php
}

function waUrl(){
    
    global $konten; 
    global $nilai;
    global $wpdb;

    $user_count = $wpdb->get_var( 
    "SELECT COUNT(*) 
    FROM {$wpdb->prefix}datacs" );

    //jumlah total akun user
    $total=$user_count; 

    //dataklik
    $namaFile = 'data1.txt';
    $nilai = file_get_contents($namaFile);
        
    if($nilai >= $total){
        $konten = '1';
        
    }else{
        $konten = $nilai + 1;
    }
    
    $id=$konten;
    $kontak = $wpdb->get_var( $wpdb->prepare
    ( "SELECT phone from {$wpdb->prefix}datacs 
    where id = %d", $id ) 
    );

    $pesan = $wpdb->get_var( $wpdb->prepare
    ( "SELECT note from {$wpdb->prefix}datacs 
    where id = %d", $id ) 
    );

    $data_performa = $wpdb->prefix . 'data_performa';
    $item['phone'] = $kontak;
    $result = $wpdb->insert($data_performa, $item);

    $wpdb->query("UPDATE {$wpdb->prefix}datacs SET 
         `click` = (`click` + 1)
       WHERE `phone` = '$kontak'");

    return 'https://api.whatsapp.com/send?phone='.$kontak.'&text='.$pesan;
}


function wpbc_handler_link_wa_page(){
    $url = waUrl();
    echo "<meta content=1;url=".$url." http-equiv='refresh'/>" ;
    exit;
}

//total klik




