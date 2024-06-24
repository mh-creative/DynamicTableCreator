<?php
class Dynamic_Table_Creator {

    public static function init() {
        add_action('admin_menu', array(__CLASS__, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_admin_scripts'));
        add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_frontend_scripts'));
        add_action('wp_ajax_save_table', array(__CLASS__, 'save_table'));
        add_action('wp_ajax_delete_table', array(__CLASS__, 'delete_table'));
        add_shortcode('display_table', array(__CLASS__, 'display_table_shortcode'));
    }

    public static function add_admin_menu() {
        add_menu_page(
            'Dynamic Table Creator',
            'Table Creator',
            'manage_options',
            'dynamic-table-creator',
            array(__CLASS__, 'create_admin_page'),
            'dashicons-editor-table',
            100
        );

        add_submenu_page(
            'dynamic-table-creator',
            'Manage Tables',
            'Manage Tables',
            'manage_options',
            'manage-tables',
            array(__CLASS__, 'manage_tables_page')
        );
    }

    public static function create_admin_page() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'dynamic_tables';
    
        $table_id = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
        $table_data = '';
        $table_title = '';
        $is_header_row = 0;
    
        if ($table_id) {
            $table = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $table_id), ARRAY_A);
            if ($table) {
                $table_title = $table['title'];
                $table_data = $table['table_data'];
                $is_header_row = $table['is_header_row'];
    
                error_log("Loading table with ID: $table_id, Title: $table_title, is_header_row: $is_header_row");
            }
        }
        ?>
        <div class="wrap">
            <h1>Dynamic Table Creator</h1>
            <form id="table-creator-form" enctype="multipart/form-data">
                <input type="hidden" id="table-id" name="table-id" value="<?php echo esc_attr($table_id); ?>">
                <label for="table-title">Table Title:</label>
                <input type="text" id="table-title" name="table-title" required value="<?php echo esc_attr($table_title); ?>">
                <label for="csv-file">Upload CSV File:</label>
                <input type="file" id="csv-file" name="csv-file" accept=".csv">
                <label for="is-header-row">
                    <input type="checkbox" id="is-header-row" name="is-header-row" <?php checked($is_header_row, 1); ?> value="1"> First row is header
                </label>
                <button type="button" id="add-row" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Add Row</button>
                <button type="button" id="add-column" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Add Column</button>
                <button type="button" id="export-csv" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Export CSV</button>
                <button type="button" id="select-all-rows" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Select All Rows</button>
                <button type="button" id="select-all-columns" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Select All Columns</button>
                <button type="button" id="delete-rows" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Delete Selected Rows</button>
                <button type="button" id="delete-columns" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Delete Selected Columns</button>
                <div class="scrollbar-container mt-5">
                    <table id="dynamic-table" class="min-w-full w-full divide-y divide-gray-200">
                        <thead class="bg-gray-800 text-white">
                            <tr>
                                <th class="text-left"></th>
                                <?php if ($table_data) :
                                    $rows = json_decode($table_data, true);
                                    $columns = isset($rows[0]) ? count($rows[0]) : 0;
                                    for ($i = 1; $i <= $columns; $i++) : ?>
                                        <th scope="col" class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider min-w-[300px] max-w-[300px]">
                                            <input type="checkbox" class="column-checkbox"> Column <?php echo $i; ?>
                                        </th>
                                    <?php endfor;
                                else : ?>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider min-w-[300px] max-w-[300px]">
                                        <input type="checkbox" class="column-checkbox"> Column 1
                                    </th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if ($table_data) :
                                foreach ($rows as $index => $row) : ?>
                                    <tr>
                                        <th class="w-36 bg-gray-50 px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[120px] max-w-[150px]" scope="row"><input type="checkbox" class="row-checkbox"> Row <?php echo $index + 1; ?></th>
                                        <?php foreach ($row as $cell) : ?>
                                            <td contenteditable="true" class="contenteditable px-3 py-4 whitespace-nowrap text-sm text-gray-500 text-left min-w-[300px] max-w-[300px]"><?php echo wp_kses_post($cell); ?> 
                                            <button type="button" class="add-media">
                                                <svg class="w-8 h-5 mb-0 custom-svg custom-svg-hover" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"/>
                                                </svg>
                                            </button>
                                        </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach;
                            else : ?>
                                <tr>
                                <th class="w-36 bg-gray-50 px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[120px] max-w-[150px]" scope="row"><input type="checkbox" class="row-checkbox"> Row 1</th>
                                    <td contenteditable="true" class="contenteditable px-3 py-4 whitespace-nowrap text-sm text-gray-500 text-left min-w-[300px] max-w-[300px]">Editable Cell 
                                    <button type="button" class="add-media">
                                        <svg class="w-8 h-5 mb-0 custom-svg custom-svg-hover" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"/>
                                        </svg>
                                    </button>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <input type="submit" value="Save Table" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded mt-3">
            </form>
        </div>
        <?php
    }
    

    public static function manage_tables_page() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'dynamic_tables';
        $tables = $wpdb->get_results("SELECT * FROM $table_name");
    
        ?>
        <div class="wrap">
            <h1>Manage Tables</h1>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Shortcode</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($tables) : ?>
                        <?php foreach ($tables as $table) : ?>
                            <tr>
                                <td><?php echo esc_html($table->id); ?></td>
                                <td><?php echo esc_html($table->title); ?></td>
                                <td>[display_table id="<?php echo esc_attr($table->id); ?>"]</td>
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=dynamic-table-creator&edit=' . $table->id); ?>">Edit</a> |
                                    <a href="#" class="delete-table" data-id="<?php echo esc_attr($table->id); ?>">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="4">No tables found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    

    public static function enqueue_admin_scripts($hook_suffix) {
        wp_enqueue_script('dynamic-table-creator-js', plugins_url('../js/dynamic-table-creator.js', __FILE__), array('jquery'), '1.0', true);
        wp_enqueue_style('dynamic-table-creator-css', plugins_url('../css/dynamic-table-creator.css', __FILE__));
    
        // Enqueue custom-built Tailwind CSS, Flowbite & Font Awesome 
        wp_enqueue_style('tailwindcss', plugins_url('../css/output.css', __FILE__)); // Use your custom-built Tailwind CSS
        wp_enqueue_script('flowbite', 'https://unpkg.com/flowbite@1.5.3/dist/flowbite.js', array(), '1.5.3', true);
    
        // Enqueue WordPress Media Uploader
        wp_enqueue_media();
    
        // Localize the script with the AJAX URL
        wp_localize_script('dynamic-table-creator-js', 'ajax_object', array(
            'ajax_url' => admin_url('admin-ajax.php')
        ));
    }
    
    public static function enqueue_frontend_scripts() {
        // Enqueue custom-built Tailwind CSS and Flowbite for front end
        wp_enqueue_style('tailwindcss', plugins_url('../css/output.css', __FILE__)); // Use your custom-built Tailwind CSS
        wp_enqueue_script('flowbite', 'https://unpkg.com/flowbite@1.5.3/dist/flowbite.js', array(), '1.5.3', true);
        wp_enqueue_style('dynamic-table-creator-css', plugins_url('../css/dynamic-table-creator.css', __FILE__));
    }
    
    

    public static function save_table() {
        if (!current_user_can('manage_options')) {
            error_log('Unauthorized user trying to save table.');
            wp_die('Unauthorized user');
        }
    
        global $wpdb;
        $table_name = $wpdb->prefix . 'dynamic_tables';
        $title = sanitize_text_field($_POST['title']);
        $table = json_encode($_POST['table'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $id = intval($_POST['table-id']);
        $is_header_row = isset($_POST['is-header-row']) && $_POST['is-header-row'] == 1 ? 1 : 0;
    
        error_log("Saving table with ID: $id, Title: $title, Table Data: $table, is_header_row: $is_header_row");
    
        if ($id) {
            $result = $wpdb->update(
                $table_name,
                array(
                    'title' => $title,
                    'table_data' => $table,
                    'is_header_row' => $is_header_row
                ),
                array('id' => $id)
            );
            error_log("Update result: " . var_export($result, true));
        } else {
            $result = $wpdb->insert(
                $table_name,
                array(
                    'title' => $title,
                    'table_data' => $table,
                    'is_header_row' => $is_header_row
                )
            );
            error_log("Insert result: " . var_export($result, true));
        }
    
        if ($result !== false) {
            wp_send_json_success('Table saved successfully!');
        } else {
            wp_send_json_error('Error saving table.');
        }
    }    

    public static function delete_table() {
        if (!current_user_can('manage_options')) {
            error_log('Unauthorized user trying to delete table.');
            wp_die('Unauthorized user');
        }

        if (!isset($_POST['id'])) {
            error_log('No table ID provided.');
            wp_send_json_error('No table ID provided.');
            return;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'dynamic_tables';
        $id = intval($_POST['id']);

        error_log("Attempting to delete table with ID: $id");

        $deleted = $wpdb->delete($table_name, array('id' => $id));

        if ($deleted !== false) {
            error_log("Table with ID $id deleted successfully.");
            wp_send_json_success('Table deleted successfully!');
        } else {
            error_log("Error deleting table with ID $id.");
            wp_send_json_error('Error deleting table.');
        }
    }

    public static function upgrade_database() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'dynamic_tables';

        // Check if the column exists before adding it
        $column = $wpdb->get_results("SHOW COLUMNS FROM `{$table_name}` LIKE 'is_header_row'");
        if (empty($column)) {
            $wpdb->query("ALTER TABLE $table_name ADD is_header_row TINYINT(1) DEFAULT 0");
        }
    }
    public static function display_table_shortcode($atts) {
        $atts = shortcode_atts(array(
            'id' => '',
        ), $atts, 'display_table');
    
        if (empty($atts['id'])) {
            return 'Table ID is required.';
        }
    
        global $wpdb;
        $table_name = $wpdb->prefix . 'dynamic_tables';
        $id = intval($atts['id']);
    
        $result = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id), ARRAY_A);
    
        if (!$result) {
            return 'Table not found.';
        }
    
        $table_data = json_decode($result['table_data'], true);
        $is_header_row = $result['is_header_row'];
    
        $output = '<div class="scrollbar-container w-[calc(100vw-100px)] mx-[50px]">';
        $output .= '<table class="min-w-full w-full divide-y divide-gray-200">';
    
        if ($is_header_row && !empty($table_data)) {
            $output .= '<thead class="text-xs uppercase bg-gray-800 text-white"><tr>';
            foreach ($table_data[0] as $column) {
                $output .= '<th scope="col" class="px-6 py-4 text-left">' . wp_kses_post($column) . '</th>';
            }
            $output .= '</tr></thead>';
            array_shift($table_data); // Remove header row from table data
        }
    
        $output .= '<tbody class="bg-white divide-y divide-gray-200">';
        if (is_array($table_data)) {
            foreach ($table_data as $row) {
                $output .= '<tr>';
                foreach ($row as $cell) {
                    $output .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">' . wp_kses_post($cell) . '</td>';
                }
                $output .= '</tr>';
            }
        }
        $output .= '</tbody></table></div>';
    
        return $output;
    }
    
}

add_action('plugins_loaded', array('Dynamic_Table_Creator', 'init'));
add_action('plugins_loaded', array('Dynamic_Table_Creator', 'upgrade_database'));

?>
