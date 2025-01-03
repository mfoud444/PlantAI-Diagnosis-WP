<?php
/**
 * Plugin Name: Health Diagnosis Plugin 
 * Description: A WordPress plugin for health diagnosis.
 * Version: 1.0.0
 * Author: Mohammed Foud
 * Author URI: https://mfoud4444.web.app
 */


if (!defined('ABSPATH')) {
    exit;
}



define('HEALTH_DIAGNOSIS_PLUGIN_VERSION', '1.0.0');
define('HEALTH_DIAGNOSIS_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('HEALTH_DIAGNOSIS_PLUGIN_URL', plugin_dir_url(__FILE__));


require_once HEALTH_DIAGNOSIS_PLUGIN_PATH . 'hd-plugin/admin.php';
require_once HEALTH_DIAGNOSIS_PLUGIN_PATH . 'hd-plugin/client.php';


global $jal_db_version;
$jal_db_version = '1.0';

/*Creating or Updating the Table*/
function jal_install()
{
    global $wpdb;
    global $jal_db_version;

    $table_name_plant = $wpdb->prefix . 'plant_ai';
    $table_name_disease = $wpdb->prefix . 'disease_ai';
    $table_name_modelai = $wpdb->prefix . 'model_ai';
    $table_name_history = $wpdb->prefix . 'history_user_detection';
    $table_name_translations = $wpdb->prefix . 'translations';

    $table_name_product_disease = $wpdb->prefix . 'product_disease';

   // Execute queries
   require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    $charset_collate = $wpdb->get_charset_collate();

    // Check if plant table already exists
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name_plant'") != $table_name_plant) {
        // Plant table creation query...
        $sql_plant = "CREATE TABLE $table_name_plant (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            picture varchar(255) ,
            status boolean NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        dbDelta($sql_plant);
    }

    // Check if disease table already exists
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name_disease'") != $table_name_disease) {
        // Disease table creation query...
        $sql_disease = "CREATE TABLE $table_name_disease (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            plant_id mediumint(9) NOT NULL,
            key_label INT NOT NULL,
            pictures text,
            status boolean NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            FOREIGN KEY (plant_id) REFERENCES $table_name_plant(id) ON DELETE CASCADE
        ) $charset_collate;";
        dbDelta($sql_disease);
    }


    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name_translations'") != $table_name_translations) {
        $sql_translations = "CREATE TABLE $table_name_translations (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            language_code varchar(5) NOT NULL,
            translated_text text NOT NULL,
            entity_type varchar(50) NOT NULL,
            entity_id mediumint(9) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY (entity_type, entity_id, language_code)
        ) $charset_collate;";
        dbDelta($sql_translations);
    }

    // Check if model AI table already exists
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name_modelai'") != $table_name_modelai) {
        // Model AI table creation query...
        $sql_model_ai = "CREATE TABLE $table_name_modelai (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            plant_id mediumint(9) NOT NULL,
            name varchar(255) NOT NULL,
            description text DEFAULT NULL,
            github_url varchar(255) NOT NULL,
            model_json_file varchar(255) NOT NULL,
            model_weights_file varchar(255) NOT NULL,
            model_meta_file varchar(255) NOT NULL,
            model_class_file varchar(255) NOT NULL,
            version varchar(255) DEFAULT NULL,
            status boolean NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            FOREIGN KEY (plant_id) REFERENCES $table_name_plant(id) ON DELETE CASCADE
        ) $charset_collate;";
        dbDelta($sql_model_ai);
    }

    // Check if history table already exists
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name_history'") != $table_name_history) {
        // History table creation query...
        $sql_history = "CREATE TABLE $table_name_history (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            id_user bigint(20) UNSIGNED NOT NULL,
            picture varchar(255) NOT NULL,
            prediction_result_value varchar(255) NOT NULL,
            plant_id mediumint(9) NOT NULL,
            correct_disease_id mediumint(9) NOT NULL,
            model_id mediumint(9) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            FOREIGN KEY (id_user) REFERENCES {$wpdb->users}(ID),
            FOREIGN KEY (plant_id) REFERENCES $table_name_plant(id) ON DELETE CASCADE,
            FOREIGN KEY (correct_disease_id) REFERENCES $table_name_disease(id) ON DELETE CASCADE,
            FOREIGN KEY (model_id) REFERENCES $table_name_modelai(id) ON DELETE CASCADE
        ) $charset_collate;";
        dbDelta($sql_history);
    }

       // Create the product_disease table
       if ($wpdb->get_var("SHOW TABLES LIKE '$table_name_product_disease'") != $table_name_product_disease) {
        $sql_product_disease = "CREATE TABLE $table_name_product_disease (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            disease_id mediumint(9) NOT NULL,
            product_id bigint(20) UNSIGNED NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY (id),
            FOREIGN KEY (disease_id) REFERENCES $table_name_disease(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES {$wpdb->prefix}posts(ID) ON DELETE CASCADE
        ) $charset_collate;";
        dbDelta($sql_product_disease);
    }

    // Update plugin version
    add_option('jal_db_version', $jal_db_version);
}



function jal_install_data() {
    global $wpdb;
    $table_name_plant = $wpdb->prefix . 'plant_ai';
    $table_name_disease = $wpdb->prefix . 'disease_ai';
    $table_name_modelai = $wpdb->prefix . 'model_ai';
    $table_name_translations = $wpdb->prefix . 'translations';

    // Read JSON file
    $json_data = file_get_contents( plugin_dir_path(__FILE__) . '/data.json');

    // Decode JSON data
    $data = json_decode($json_data, true);

    // Define plant folders and labels
    $plant_folder = array_keys($data);
    $plant_labels = array();
    foreach ($data as $key => $value) {
        array_push($plant_labels, $value['label']['en']);
    }

    // Insert plants, diseases, and models
    foreach ($plant_labels as $key => $label) {
        // Insert plant
        $wpdb->insert(
            $table_name_plant,
            array(
                'status' => 1,
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ),
            array('%d', '%s', '%s')
        );
        $plant_id = $wpdb->insert_id;

        // Insert plant translation
        $wpdb->insert(
            $table_name_translations,
            array(
                'language_code' => 'en',
                'translated_text' => $label,
                'entity_type' => 'plant',
                'entity_id' => $plant_id,
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ),
            array('%s', '%s', '%s', '%d', '%s', '%s')
        );

        // Insert Arabic plant translation
        $wpdb->insert(
            $table_name_translations,
            array(
                'language_code' => 'ar',
                'translated_text' => $data[$plant_folder[$key]]['label']['ar'],
                'entity_type' => 'plant',
                'entity_id' => $plant_id,
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ),
            array('%s', '%s', '%s', '%d', '%s', '%s')
        );

        // Insert diseases
        $disease_labels = array_keys($data[$plant_folder[$key]]['diseases']);
        foreach ($disease_labels as $disease_key => $disease_name) {
            $description = $data[$plant_folder[$key]]['diseases'][$disease_name]['description']['en'];
            $health_condition = $data[$plant_folder[$key]]['diseases'][$disease_name]['health_condition']['en'];

            // Insert disease
            $wpdb->insert(
                $table_name_disease,
                array(
                    'plant_id' => $plant_id,
                    'key_label' => $disease_key,
                    'status' => 1,
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                ),
                array('%d', '%d', '%d', '%s', '%s')
            );
            $disease_id = $wpdb->insert_id;

            // Insert disease translation
            $wpdb->insert(
                $table_name_translations,
                array(
                    'language_code' => 'en',
                    'translated_text' => $disease_name,
                    'entity_type' => 'disease',
                    'entity_id' => $disease_id,
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                ),
                array('%s', '%s', '%s', '%d', '%s', '%s')
            );

            // Insert Arabic disease translation
            $wpdb->insert(
                $table_name_translations,
                array(
                    'language_code' => 'ar',
                    'translated_text' => $data[$plant_folder[$key]]['diseases'][$disease_name]['disease_name']['ar'],
                    'entity_type' => 'disease',
                    'entity_id' => $disease_id,
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                ),
                array('%s', '%s', '%s', '%d', '%s', '%s')
            );

            // Insert disease description translation
            $wpdb->insert(
                $table_name_translations,
                array(
                    'language_code' => 'en',
                    'translated_text' => $description,
                    'entity_type' => 'disease_description',
                    'entity_id' => $disease_id,
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                ),
                array('%s', '%s', '%s', '%d', '%s', '%s')
            );

            // Insert Arabic disease description translation
            $wpdb->insert(
                $table_name_translations,
                array(
                    'language_code' => 'ar',
                    'translated_text' => $data[$plant_folder[$key]]['diseases'][$disease_name]['description']['ar'],
                    'entity_type' => 'disease_description',
                    'entity_id' => $disease_id,
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                ),
                array('%s', '%s', '%s', '%d', '%s', '%s')
            );

            // Insert disease health condition translation
            $wpdb->insert(
                $table_name_translations,
                array(
                    'language_code' => 'en',
                    'translated_text' => $health_condition,
                    'entity_type' => 'disease_health_condition',
                    'entity_id' => $disease_id,
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                ),
                array('%s', '%s', '%s', '%d', '%s', '%s')
            );

            // Insert Arabic disease health condition translation
            $wpdb->insert(
                $table_name_translations,
                array(
                    'language_code' => 'ar',
                    'translated_text' => $data[$plant_folder[$key]]['diseases'][$disease_name]['health_condition']['ar'],
                    'entity_type' => 'disease_health_condition',
                    'entity_id' => $disease_id,
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                ),
                array('%s', '%s', '%s', '%d', '%s', '%s')
            );
        }




            $github_url = 'https://github.com/shahd1995913/models_world_of_plants_2022/blob/main/'.$plant_folder[$key].'/model';

            $github_url = str_replace('/blob', '', $github_url);
           // Generate random characters for the folder name
           $random_chars = uniqid('');

           // Generate random folder name starting with "model"
           $random_folder_name = 'model_' . $random_chars;

           // Download files from GitHub and save them in the same random folder
           $model_json_file_path = download_and_save_file($github_url, 'model.json', $random_folder_name);
           if (is_wp_error($model_json_file_path)) {
               return $model_json_file_path; 
           }

           $model_weights_file_path = download_and_save_file($github_url, 'weights.bin', $random_folder_name);
           if (is_wp_error($model_weights_file_path)) {
               return $model_weights_file_path; 
           }

           $model_meta_file_path = download_and_save_file($github_url, 'metadata.json', $random_folder_name);
           if (is_wp_error($model_meta_file_path)) {
               return $model_meta_file_path; 
           }

           $model_class_file_path = download_and_save_file($github_url, 'class.json', $random_folder_name);
           if (is_wp_error($model_class_file_path)) {
               return $model_class_file_path; 
           }

            // Insert model for the plant
            $model_data = array(
                'plant_id' => $plant_id,
                'name' => $label . ' Model',
                'description' => 'A machine learning model trained to identify diseases and pests affecting ' . $label . ' plants.',
                'github_url' => $github_url,
                'model_json_file' => $model_json_file_path,
                'model_weights_file' => $model_weights_file_path,
                'model_meta_file' => $model_meta_file_path,
                'model_class_file' => $model_class_file_path,
                'version' => '1.0',
                'status' => 1,
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            );

            $wpdb->insert(
                $table_name_modelai,
                $model_data,
                array(
                    '%d',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%d',
                    '%s',
                    '%s'
                )
            );

    }

        
}




register_activation_hook(__FILE__, 'jal_install');
register_activation_hook( __FILE__, 'jal_install_data');



function health_diagnosis_plugin_uninstall() {
    global $wpdb;

    $table_name_plant = $wpdb->prefix . 'plant_ai';
    $table_name_disease = $wpdb->prefix . 'disease_ai';
    $table_name_modelai = $wpdb->prefix . 'model_ai';
    $table_name_history = $wpdb->prefix . 'history_user_detection'; 
    $table_name_translations = $wpdb->prefix . 'translations';

    $table_name_product_disease = $wpdb->prefix . 'product_disease';
    // Drop the tables

    $wpdb->query("DROP TABLE IF EXISTS $table_name_history");
    $wpdb->query("DROP TABLE IF EXISTS $table_name_modelai");
    $wpdb->query("DROP TABLE IF EXISTS $table_name_product_disease");
    $wpdb->query("DROP TABLE IF EXISTS $table_name_disease");
    $wpdb->query("DROP TABLE IF EXISTS $table_name_translations");
    $wpdb->query("DROP TABLE IF EXISTS $table_name_plant");



    // Delete plugin options
    delete_option('jal_db_version');

 
}

register_uninstall_hook(__FILE__, 'health_diagnosis_plugin_uninstall');

function myplugin_update_db_check()
{
    global $jal_db_version;
    if (get_site_option('jal_db_version') != $jal_db_version) {
        jal_install();
    }
}
add_action('plugins_loaded', 'myplugin_update_db_check');