<?php
/**
 * ---------------------------------------------------------------------
   MyOOS [Shopsystem]
   https://www.oos-shop.de

   Copyright (c) 2003 - 2024  by the MyOOS Development Team.
   ----------------------------------------------------------------------
   Based on:

   File: products_attributes.php,v 1.52 2003/07/10 20:46:01 dgw_
         products_attributes.php,v 1.48 2002/11/22 14:45:49 dgw_
   ----------------------------------------------------------------------
   osCommerce, Open Source E-Commerce Solutions
   http://www.oscommerce.com

   Copyright (c) 2003 osCommerce
   ----------------------------------------------------------------------
   Released under the GNU General Public License
   ----------------------------------------------------------------------
 */

define('OOS_VALID_MOD', 'yes');
require 'includes/main.php';

require 'includes/functions/function_categories.php';
require 'includes/functions/function_products_attributes.php';
require 'includes/classes/class_upload.php';


$aLanguages = oos_get_languages();
$nLanguages = is_countable($aLanguages) ? count($aLanguages) : 0;


$page_info = '';
if (isset($_GET['option_page'])) {
    $option_page = intval($_GET['option_page']);
    $page_info .= 'option_page=' . $option_page . '&';
}

if (isset($_GET['value_page'])) {
    $value_page =  intval($_GET['value_page']);
    $page_info .= 'value_page=' . $value_page . '&';
}

if (isset($_GET['attribute_page'])) {
    $attribute_page = intval($_GET['attribute_page']);
    $page_info .= 'attribute_page=' . $attribute_page . '&';
}

if (oos_is_not_null($page_info)) {
    $page_info = substr($page_info, 0, -1);
}

$options = ['image_versions' => [
    // The empty image version key defines options for the original image.
    // Keep in mind: these image manipulations are inherited by all other image versions from this point onwards.
    // Also note that the property 'no_cache' is not inherited, since it's not a manipulation.
    '' => [
        // Automatically rotate images based on EXIF meta data:
        'auto_orient' => true,
    ],
    'large' => [
        // 'auto_orient' => TRUE,
        // 'crop' => TRUE,
        // 'jpeg_quality' => 82,
        // 'no_cache' => TRUE, (there's a caching option, but this remembers thumbnail sizes from a previous action!)
        // 'strip' => TRUE, (this strips EXIF tags, such as geolocation)
        'max_width' => 1200,
        // either specify width, or set to 0. Then width is automatically adjusted - keeping aspect ratio to a specified max_height.
        'max_height' => 1200,
    ],
    'medium_large' => [
        // 'auto_orient' => TRUE,
        // 'crop' => TRUE,
        // 'jpeg_quality' => 82,
        // 'no_cache' => TRUE, (there's a caching option, but this remembers thumbnail sizes from a previous action!)
        // 'strip' => TRUE, (this strips EXIF tags, such as geolocation)
        'max_width' => 600,
        // either specify width, or set to 0. Then width is automatically adjusted - keeping aspect ratio to a specified max_height.
        'max_height' => 600,
    ],
    'medium' => [
        // 'auto_orient' => TRUE,
        // 'crop' => TRUE,
        // 'jpeg_quality' => 82,
        // 'no_cache' => TRUE, (there's a caching option, but this remembers thumbnail sizes from a previous action!)
        // 'strip' => TRUE, (this strips EXIF tags, such as geolocation)
        'max_width' => 420,
        // either specify width, or set to 0. Then width is automatically adjusted - keeping aspect ratio to a specified max_height.
        'max_height' => 455,
    ],
    'small' => [
        // 'auto_orient' => TRUE,
        // 'crop' => TRUE,
        // 'jpeg_quality' => 82,
        // 'no_cache' => TRUE, (there's a caching option, but this remembers thumbnail sizes from a previous action!)
        // 'strip' => TRUE, (this strips EXIF tags, such as geolocation)
        'max_width' => 150,
        // either specify width, or set to 0. Then width is automatically adjusted - keeping aspect ratio to a specified max_height.
        'max_height' => 150,
    ],
    'min' => [
        // 'auto_orient' => TRUE,
        // 'crop' => TRUE,
        // 'jpeg_quality' => 82,
        // 'no_cache' => TRUE, (there's a caching option, but this remembers thumbnail sizes from a previous action!)
        // 'strip' => TRUE, (this strips EXIF tags, such as geolocation)
        'max_width' => 45,
        // either specify width, or set to 0. Then width is automatically adjusted - keeping aspect ratio to a specified max_height.
        'max_height' => 45,
    ],
]];


$nPage = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
$action = filter_string_polyfill(filter_input(INPUT_GET, 'action')) ?: 'default';

switch ($action) {
    case 'add_product_options':
        for ($i = 0, $n = is_countable($aLanguages) ? count($aLanguages) : 0; $i < $n; $i ++) {
            $option_name =  oos_db_prepare_input($_POST['option_name']);
            $option_type = oos_db_prepare_input($_POST['option_type']);

            $products_optionstable = $oostable['products_options'];
            $dbconn->Execute("INSERT INTO $products_optionstable (products_options_id, products_options_name, products_options_languages_id,products_options_type) VALUES ('" . intval($_POST['products_options_id']) . "', '" . oos_db_input($option_name[$aLanguages[$i]['id']]) . "', '" . oos_db_input($aLanguages[$i]['id']) . "', '" . oos_db_input($option_type) . "')");
        }
        switch ($option_type) {
            case PRODUCTS_OPTIONS_TYPE_TEXT:
            case PRODUCTS_OPTIONS_TYPE_FILE:

                $products_options_values_to_products_optionstable = $oostable['products_options_values_to_products_options'];
                $dbconn->Execute("INSERT INTO $products_options_values_to_products_optionstable (products_options_values_id, products_options_id) values ('" . PRODUCTS_OPTIONS_VALUES_TEXT_ID .  "', '" .  intval($products_options_id) .  "')");
                break;
        }
        oos_redirect_admin(oos_href_link_admin($aContents['products_properties'], $page_info));
        break;

    case 'add_product_option_values':
        for ($i = 0, $n = is_countable($aLanguages) ? count($aLanguages) : 0; $i < $n; $i ++) {
            $value_name = oos_db_prepare_input($_POST['value_name']);

            $products_options_valuestable = $oostable['products_options_values'];
            $dbconn->Execute("INSERT INTO $products_options_valuestable (products_options_values_id, products_options_values_languages_id, products_options_values_name) VALUES ('" . intval($_POST['value_id']) . "', '" . intval($aLanguages[$i]['id']) . "', '" . oos_db_input($value_name[$aLanguages[$i]['id']]) . "')");
        }

        $products_options_values_to_products_optionstable = $oostable['products_options_values_to_products_options'];
        $dbconn->Execute("INSERT INTO $products_options_values_to_products_optionstable (products_options_id, products_options_values_id) VALUES ('" . $_POST['option_id'] . "', '" . $_POST['value_id'] . "')");
        oos_redirect_admin(oos_href_link_admin($aContents['products_properties'], $page_info));
        break;

    case 'setflag':
        if (isset($_GET['aID'])) {
            oos_set_attributes_status($_GET['aID'], $_GET['flag']);
        }
        oos_redirect_admin(oos_href_link_admin($aContents['products_properties'], $page_info));
        break;

    case 'add_product_attributes':
        if (isset($_FILES['files'])) {
            foreach ($_FILES['files']['name'] as $key => $name) {
                if (empty($name)) {
                    // purge empty slots
                    unset($_FILES['files']['name'][$key]);
                    unset($_FILES['files']['type'][$key]);
                    unset($_FILES['files']['tmp_name'][$key]);
                    unset($_FILES['files']['error'][$key]);
                    unset($_FILES['files']['size'][$key]);
                }
            }
        }

        $oProductImage = new upload('options_values_image', $options);

        $dir_fs_catalog_images = OOS_ABSOLUTE_PATH . OOS_IMAGES . 'product/';
        $oProductImage->set_destination($dir_fs_catalog_images);

        if ($oProductImage->parse() && oos_is_not_null($oProductImage->filename)) {
            $options_values_image = $oProductImage->filename;
        } else {
            $options_values_image = '';
        }

        $_POST['value_price'] = str_replace(',', '.', (string) $_POST['value_price']);

        // 0 = Download id
        $values_id = (isset($_POST['values_id'])) ? intval($_POST['values_id']) : 0;

        /*
        $products_optionstable = $oostable['products_options'];
        $products_options_result = $dbconn->Execute("SELECT products_options_type FROM $products_optionstable WHERE products_options_id = '" . intval($_POST['options_id']) . "'");
        $products_options_array = $products_options_result->fields;
        $values_id = (($products_options_array['products_options_type'] == PRODUCTS_OPTIONS_TYPE_TEXT) or ($products_options_array['products_options_type'] == PRODUCTS_OPTIONS_TYPE_FILE)) ? PRODUCTS_OPTIONS_VALUE_TEXT_ID : $values_id;
        */

        // todo remove options_values_base_unit
        $options_values_base_unit = '';
        if (isset($_POST['options_values_base_price'])) {
            $options_values_base_price = oos_db_prepare_input($_POST['options_values_base_price']);
            $options_values_quantity = oos_db_prepare_input($_POST['options_values_quantity']);
            $options_values_base_quantity = oos_db_prepare_input($_POST['options_values_base_quantity']);
            $options_values_units_id = intval($_POST['options_values_units_id']);
        } else {
            $options_values_base_price = 1;
            $options_values_quantity = 1;
            $options_values_base_quantity = 1;

            $options_values_units_id = 0;
        }

        $products_attributestable = $oostable['products_attributes'];
        $dbconn->Execute(
            "INSERT INTO $products_attributestable 
						(products_attributes_id, 
						products_id,
						options_id,
						options_values_model,
						options_values_image,
						options_values_id,
						options_values_price,
						options_values_base_price,
						options_values_quantity,
						options_values_base_quantity,
						options_values_base_unit,
						options_values_units_id,
						price_prefix,
						options_sort_order) 
						VALUES ('', 
								'" . oos_db_prepare_input($_POST['products_id']) . "', 
								'" . oos_db_prepare_input($_POST['options_id']) . "', 
								'" . oos_db_prepare_input($_POST['options_values_model']) . "', 
								'" . oos_db_prepare_input($options_values_image) . "',
								'" . oos_db_prepare_input($values_id) . "', 
								'" . oos_db_prepare_input($_POST['value_price']) . "', 
								'" . oos_db_prepare_input($options_values_base_price) . "',
								'" . oos_db_prepare_input($options_values_quantity) . "',
								'" . oos_db_prepare_input($options_values_base_quantity) . "',
								'" . oos_db_prepare_input($options_values_base_unit) . "',
								'" . oos_db_prepare_input($options_values_units_id) . "', 								
								'" . oos_db_prepare_input($_POST['price_prefix']) . "', 
								'" . oos_db_prepare_input($_POST['sort_order']) . "')"
        );
        $products_attributes_id = $dbconn->Insert_ID();

        if ((DOWNLOAD_ENABLED == 'true') && $_POST['products_attributes_filename'] != '') {
            $products_attributes_maxdays  = $_POST['products_attributes_maxdays'] ?? DOWNLOAD_MAX_DAYS;
            $products_attributes_maxcount = $_POST['products_attributes_maxcount'] ?? DOWNLOAD_MAX_COUNT;

            $products_attributes_downloadtable = $oostable['products_attributes_download'];
            $dbconn->Execute(
                "INSERT INTO $products_attributes_downloadtable 
							(products_attributes_id,
							products_attributes_filename,
							products_attributes_maxdays,
							products_attributes_maxcount)
							VALUES (" . oos_db_prepare_input($products_attributes_id) . ", 
								'" . oos_db_prepare_input($_POST['products_attributes_filename']) . "', 
								'" . oos_db_prepare_input($products_attributes_maxdays) . "', 
								'" . oos_db_prepare_input($products_attributes_maxcount) . "')"
            );
        }
        oos_redirect_admin(oos_href_link_admin($aContents['products_properties'], $page_info));
        break;

    case 'update_option_name':
        for ($i = 0, $n = is_countable($aLanguages) ? count($aLanguages) : 0; $i < $n; $i ++) {
            $option_name = oos_db_prepare_input($_POST['option_name']);
            $option_type = oos_db_prepare_input($_POST['option_type']);
            $products_optionstable = $oostable['products_options'];
            $dbconn->Execute("UPDATE $products_optionstable SET products_options_name = '" . $option_name[$aLanguages[$i]['id']] . "', products_options_type = '" . $option_type . "' WHERE products_options_id = '" . intval($_POST['option_id']) . "' AND products_options_languages_id = '" . $aLanguages[$i]['id'] . "'");
        }
        switch ($option_type) {
            case PRODUCTS_OPTIONS_TYPE_TEXT:
            case PRODUCTS_OPTIONS_TYPE_FILE:
                $products_options_values_to_products_optionstable = $oostable['products_options_values_to_products_options'];
                $dbconn->Execute("INSERT INTO $products_options_values_to_products_optionstable VALUES (NULL, '" . intval($_POST['option_id']) . "', '" . PRODUCTS_OPTIONS_VALUES_TEXT_ID . "')");
                break;
            default:
                $products_options_values_to_products_optionstable = $oostable['products_options_values_to_products_options'];
                $dbconn->Execute("DELETE FROM $products_options_values_to_products_optionstable WHERE products_options_values_id = '" . PRODUCTS_OPTIONS_VALUES_TEXT_ID . "'");
        }
        oos_redirect_admin(oos_href_link_admin($aContents['products_properties'], $page_info));
        break;

    case 'update_value':
        for ($i = 0, $n = is_countable($aLanguages) ? count($aLanguages) : 0; $i < $n; $i ++) {
            $value_name = oos_db_prepare_input($_POST['value_name']);

            $products_options_valuestable = $oostable['products_options_values'];
            $dbconn->Execute("UPDATE $products_options_valuestable SET products_options_values_name = '" . $value_name[$aLanguages[$i]['id']] . "' WHERE products_options_values_id = '" . intval($_POST['value_id']) . "' AND  products_options_values_languages_id= '" . $aLanguages[$i]['id'] . "'");
        }

        $products_options_values_to_products_optionstable = $oostable['products_options_values_to_products_options'];
        // $dbconn->Execute("UPDATE $products_options_values_to_products_optionstable SET products_options_id = '" . intval($_POST['option_id']) . "', products_options_values_id = '" . intval($_POST['value_id']) . "'  WHERE products_options_values_to_products_options_id = '" . intval($_POST['value_id']) . "'");
        $dbconn->Execute("UPDATE $products_options_values_to_products_optionstable SET products_options_id = '" . intval($_POST['option_id']) . "' WHERE products_options_values_id = '" . intval($_POST['value_id']) . "'");

        oos_redirect_admin(oos_href_link_admin($aContents['products_properties'], $page_info));
        break;

    case 'update_product_attribute':
        if (isset($_POST['products_previous_image'])) {
            $options_values_image = oos_db_prepare_input($_POST['products_previous_image']);
        }

        if (isset($_FILES['files'])) {
            foreach ($_FILES['files']['name'] as $key => $name) {
                if (empty($name)) {
                    // purge empty slots
                    unset($_FILES['files']['name'][$key]);
                    unset($_FILES['files']['type'][$key]);
                    unset($_FILES['files']['tmp_name'][$key]);
                    unset($_FILES['files']['error'][$key]);
                    unset($_FILES['files']['size'][$key]);
                }
            }
        }

        $oProductImage = new upload('options_values_image', $options);

        $dir_fs_catalog_images = OOS_ABSOLUTE_PATH . OOS_IMAGES . 'product/';
        $oProductImage->set_destination($dir_fs_catalog_images);

        if ($oProductImage->parse() && oos_is_not_null($oProductImage->filename)) {
            $options_values_image = $oProductImage->filename;
        }

        if ((isset($_POST['remove_image']) && ($_POST['remove_image'] == 'yes')) && (isset($_POST['products_previous_image']))) {
            $image = oos_db_prepare_input($_POST['products_previous_image']);
            oos_remove_product_image($image);
            $options_values_image = '';
        }


        $_POST['value_price'] = str_replace(',', '.', (string) $_POST['value_price']);


        /*
        $products_optionstable = $oostable['products_options'];
        $products_options_result = $dbconn->Execute("SELECT products_options_type FROM $products_optionstable WHERE products_options_id = '" . intval($_POST['options_id']) . "'");
        $products_options_array = $products_options_result->fields;

        if (!empty($products_options_array['products_options_type'])) {
        switch ($products_options_array['products_options_type']) {
            case PRODUCTS_OPTIONS_TYPE_TEXT:
            case PRODUCTS_OPTIONS_TYPE_FILE:
                $values_id = PRODUCTS_OPTIONS_VALUE_TEXT_ID;
                break;
        default:
                $values_id = oos_db_prepare_input($_POST['values_id']);
        }
        }
        */
        // 0 = Download id
        $values_id = (isset($_POST['values_id'])) ? intval($_POST['values_id']) : 0;

        // todo remove options_values_base_unit
        $options_values_base_unit = '';

        if (isset($_POST['options_values_base_price'])) {
            $options_values_base_price = oos_db_prepare_input($_POST['options_values_base_price']);
            $options_values_quantity = oos_db_prepare_input($_POST['options_values_quantity']);
            $options_values_base_quantity = oos_db_prepare_input($_POST['options_values_base_quantity']);
            $options_values_units_id = intval($_POST['options_values_units_id']);
        } else {
            $options_values_base_price = 1;
            $options_values_quantity = 1;
            $options_values_base_quantity = 1;
            $options_values_units_id = 0;
        }


        $products_attributestable = $oostable['products_attributes'];
        $dbconn->Execute(
            "UPDATE $products_attributestable 
						SET products_id = '" . oos_db_prepare_input($_POST['products_id']) . "',
						options_id = '" . oos_db_prepare_input($_POST['options_id']) . "',
						options_values_model = '" . oos_db_prepare_input($_POST['options_values_model']) . "',
						options_values_image  = '" . oos_db_prepare_input($options_values_image) . "',
						options_values_id = '" . oos_db_prepare_input($values_id) . "',
						options_values_price = '" . oos_db_prepare_input($_POST['value_price']) . "',
						options_values_base_price = '" . oos_db_prepare_input($options_values_base_price) . "',
						options_values_quantity = '" . oos_db_prepare_input($options_values_quantity) . "',
						options_values_base_quantity = '" . oos_db_prepare_input($options_values_base_quantity) . "',
						options_values_base_unit = '" . oos_db_prepare_input($options_values_base_unit) . "',
						options_values_units_id = '" . oos_db_prepare_input($options_values_units_id) . "',						
						price_prefix = '" . oos_db_prepare_input($_POST['price_prefix']) . "',
						 options_sort_order = '" . oos_db_prepare_input($_POST['sort_order']) . "' WHERE products_attributes_id = '" . intval($_POST['attribute_id']) . "'"
        );


        if ((DOWNLOAD_ENABLED == 'true') && $_POST['products_attributes_filename'] != '') {
            $products_attributes_maxdays  = $_POST['products_attributes_maxdays'] ?? DOWNLOAD_MAX_DAYS;
            $products_attributes_maxcount = $_POST['products_attributes_maxcount'] ?? DOWNLOAD_MAX_COUNT;

            $products_attributes_downloadtable = $oostable['products_attributes_download'];
            $download_result = $dbconn->Execute("SELECT products_attributes_filename FROM $products_attributes_downloadtable WHERE products_attributes_id = '" . intval($_POST['attribute_id']) . "'");
            if (!$download_result->RecordCount()) {
                $dbconn->Execute(
                    "INSERT INTO $products_attributes_downloadtable 
							(products_attributes_id,
							products_attributes_filename,
							products_attributes_maxdays,
							products_attributes_maxcount)
							VALUES ('" . intval($_POST['attribute_id']) . "', 
								'" . oos_db_prepare_input($_POST['products_attributes_filename']) . "', 
								'" . oos_db_prepare_input($products_attributes_maxdays) . "', 
								'" . oos_db_prepare_input($products_attributes_maxcount) . "')"
                );
            } else {
                $products_attributes_downloadtable = $oostable['products_attributes_download'];
                $dbconn->Execute(
                    "UPDATE $products_attributes_downloadtable
                        SET products_attributes_filename ='" . oos_db_input($_POST['products_attributes_filename']) . "',
                            products_attributes_maxdays ='" . oos_db_input($products_attributes_maxdays) . "',
                            products_attributes_maxcount ='" . oos_db_input($products_attributes_maxcount) . "'
                        WHERE products_attributes_id = '" . intval($_POST['attribute_id']) . "'"
                );
            }
        }
        oos_redirect_admin(oos_href_link_admin($aContents['products_properties'], $page_info));
        break;
    case 'delete_option':
        $products_optionstable = $oostable['products_options'];
        $dbconn->Execute("DELETE FROM $products_optionstable WHERE products_options_id = '" . intval($_GET['option_id']) . "'");

        $products_options_values_to_products_optionstable = $oostable['products_options_values_to_products_options'];
        //  $dbconn->Execute("DELETE FROM $products_options_values_to_products_optionstable WHERE products_options_id = '" . intval($_GET['option_id']) . "' AND products_options_values_id = '" . PRODUCTS_OPTIONS_VALUES_TEXT_ID . "'");
        $dbconn->Execute("DELETE FROM $products_options_values_to_products_optionstable WHERE products_options_id = '" . intval($_GET['option_id']) . "'");
        oos_redirect_admin(oos_href_link_admin($aContents['products_properties'], $page_info));
        break;
    case 'delete_value':
        $products_options_valuestable = $oostable['products_options_values'];
        $dbconn->Execute("DELETE FROM $products_options_valuestable WHERE products_options_values_id = '" . intval($_GET['value_id']) . "'");
        $products_options_values_to_products_optionstable = $oostable['products_options_values_to_products_options'];
        $dbconn->Execute("DELETE FROM $products_options_values_to_products_optionstable WHERE products_options_values_id = '" . intval($_GET['value_id']) . "'");
        oos_redirect_admin(oos_href_link_admin($aContents['products_properties'], $page_info));
        break;
    case 'delete_attribute':
        $products_attributestable = $oostable['products_attributes'];
        $dbconn->Execute("DELETE FROM $products_attributestable WHERE products_attributes_id = '" . intval($_GET['attribute_id']) . "'");
        $products_attributes_downloadtable = $oostable['products_attributes_download'];
        $dbconn->Execute("DELETE FROM $products_attributes_downloadtable WHERE products_attributes_id = '" . intval($_GET['attribute_id']) . "'");
        oos_redirect_admin(oos_href_link_admin($aContents['products_properties'], $page_info));
        break;
}


$products_options_types_list = [];
$products_options_typestable = $oostable['products_options_types'];
$products_options_types_sql = "SELECT products_options_types_id, products_options_types_name
                                 FROM $products_options_typestable
                                 WHERE products_options_types_languages_id = '" . intval($_SESSION['language_id']) . "'
                                 ORDER BY products_options_types_id";
$products_options_types_result = $dbconn->Execute($products_options_types_sql);
while ($products_options_type_array = $products_options_types_result->fields) {
    $products_options_types_list[$products_options_type_array['products_options_types_id']] = $products_options_type_array['products_options_types_name'];

    // Move that ADOdb pointer!
    $products_options_types_result->MoveNext();
}

$products_units_array = [];
$unit_of_measure = [];
$products_units_array = [['id' => '0', 'text' => TEXT_NONE]];
$products_unitstable = $oostable['products_units'];
$products_units_result = $dbconn->Execute("SELECT products_units_id, products_unit_name, unit_of_measure FROM $products_unitstable WHERE languages_id = '" . intval($_SESSION['language_id']) . "' ORDER BY products_unit_name");
while ($products_units = $products_units_result->fields) {
    $products_units_array[] = ['id' => $products_units['products_units_id'], 'text' => $products_units['products_unit_name']];
    if ((!empty($products_units['unit_of_measure'])) && (!in_array($products_units['unit_of_measure'], $unit_of_measure))) {
        $unit_of_measure[] = $products_units['unit_of_measure'];
    }

    // Move that ADOdb pointer!
    $products_units_result->MoveNext();
}

if (!isset($value_page)) {
    $value_page = 1;
}

if (!isset($attribute_page)) {
    $attribute_page = 1;
}


require 'includes/header.php';
?>
<script nonce="<?php echo NONCE; ?>">
function go_option() {
  if (document.option_order_by.selected.options[document.option_order_by.selected.selectedIndex].value != "none") {
    location = "<?php echo oos_href_link_admin($aContents['products_properties'], 'option_page=' . (isset($_GET['option_page']) ? intval($_GET['option_page']) : 1)); ?>&option_order_by="+document.option_order_by.selected.options[document.option_order_by.selected.selectedIndex].value;
  }
}
</script>

<!-- body //-->
<div class="wrapper">
    <!-- Header //-->
    <header class="topnavbar-wrapper">
        <!-- Top Navbar //-->
        <?php require 'includes/menue.php'; ?>
    </header>
    <!-- END Header //-->
    <aside class="aside">
        <!-- Sidebar //-->
        <div class="aside-inner">
            <?php require 'includes/blocks.php'; ?>
        </div>
        <!-- END Sidebar (left) //-->
    </aside>

    <!-- Main section //-->
    <section>
        <!-- Page content //-->
        <div class="content-wrapper">
						
			<!-- Breadcrumbs //-->
			<div class="content-heading">
				<div class="col-lg-12">
					<h2><?php echo HEADING_TITLE_ATRIB; ?></h2>
					<ol class="breadcrumb">
						<li class="breadcrumb-item">
							<?php echo '<a href="' . oos_href_link_admin($aContents['default']) . '">' . HEADER_TITLE_TOP . '</a>'; ?>
						</li>
						<li class="breadcrumb-item">
							<?php echo '<a href="' . oos_href_link_admin($aContents['categories'], 'selected_box=catalog') . '">' . BOX_HEADING_CATALOG . '</a>'; ?>
						</li>
						<li class="breadcrumb-item active">
							<strong><?php echo HEADING_TITLE_ATRIB; ?></strong>
						</li>
					</ol>
				</div>
			</div>
			<!-- END Breadcrumbs //-->
			
			<div class="wrapper wrapper-content">
				<div class="row">
					
			<!-- body_text //-->
			<div class="col-md-6">
				<!-- options and values//-->
				<div class="table-responsive">
					<table class="table table-hover w-100">
<!-- options //-->
<?php
if ($action == 'delete_product_option') { // delete product option
    $products_optionstable = $oostable['products_options'];
    $options = $dbconn->Execute("SELECT products_options_id, products_options_name FROM $products_optionstable WHERE products_options_id = '" . intval($_GET['option_id']) . "' AND products_options_languages_id = '" . intval($_SESSION['language_id']) . "'");
    $options_values = $options->fields; ?>
              <tr>
                <td class="pageHeading">&nbsp;<?php echo $options_values['products_options_name']; ?>&nbsp;</td>
                <td>&nbsp;&nbsp;</td>
              </tr>
              <tr>
                <td>

                <table class="table table-striped table-hover w-100">
    <?php
    $productstable = $oostable['products'];
    $products_options_valuestable = $oostable['products_options_values'];
    $products_attributestable = $oostable['products_attributes'];
    $products_descriptiontable = $oostable['products_description'];
    $products = $dbconn->Execute("SELECT p.products_id, pd.products_name, pov.products_options_values_name FROM $productstable p, $products_options_valuestable pov, $products_attributestable pa, $products_descriptiontable pd WHERE pd.products_id = p.products_id AND pov.products_options_values_languages_id = '" . intval($_SESSION['language_id']) . "' AND pd.products_languages_id = '" . intval($_SESSION['language_id']) . "' AND pa.products_id = p.products_id AND pa.options_id='" . intval($_GET['option_id']) . "' AND pov.products_options_values_id = pa.options_values_id ORDER BY pd.products_name");
    if ($products->RecordCount()) {
        ?>
                    <thead class="thead-dark">
                        <tr>
                            <th class="text-center">&nbsp;<?php echo TABLE_HEADING_ID; ?>&nbsp;</th>
                            <th>&nbsp;<?php echo TABLE_HEADING_PRODUCT; ?>&nbsp;</th>
                            <th>&nbsp;<?php echo TABLE_HEADING_OPT_VALUE; ?>&nbsp;</th>
                        </tr>
                    </thead>
        <?php
        $rows = 0;
        while ($products_values = $products->fields) {
            $rows++; ?>
                  <tr class="<?php echo(floor($rows / 2) == ($rows / 2) ? 'table-secondary' : 'table-light'); ?>">
                    <td align="center" class="smallText">&nbsp;<?php echo $products_values['products_id']; ?>&nbsp;</td>
                    <td class="smallText">&nbsp;<?php echo $products_values['products_name']; ?>&nbsp;</td>
                    <td class="smallText">&nbsp;<?php echo $products_values['products_options_values_name']; ?>&nbsp;</td>
                  </tr>
            <?php
            // Move that ADOdb pointer!
            $products->MoveNext();
        } ?>
                  <tr>
                    <td colspan="3" class="main"><br><?php echo TEXT_WARNING_OF_DELETE; ?></td>
                  </tr>
                  <tr>
                    <td align="right" colspan="3" class="main"><br><?php echo '<a class="btn btn-sm btn-warning mb-20" href="' . oos_href_link_admin($aContents['products_properties'], 'value_page=' . $value_page . '&attribute_page=' . $attribute_page) . '" role="button"><strong>' . BUTTON_CANCEL . '</strong></a>'; ?>&nbsp;</td>
                  </tr>
        <?php
    } else {
        ?>
                  <tr>
                    <td class="main" colspan="3"><br><?php echo TEXT_OK_TO_DELETE; ?></td>
                  </tr>
                  <tr>
                    <td class="main" align="right" colspan="3"><br><?php echo '<a class="btn btn-sm btn-danger mb-20" href="' . oos_href_link_admin($aContents['products_properties'], 'action=delete_option&option_id=' . intval($_GET['option_id'])) . '" role="button"><strong>' . BUTTON_DELETE . '</strong></a>'; ?>&nbsp;&nbsp;&nbsp;<?php echo '<a class="btn btn-sm btn-warning mb-20" href="' . oos_href_link_admin($aContents['products_properties'], '&page=' . $nPage) . '" role="button"><strong>' . BUTTON_CANCEL . '</strong></a>'; ?>&nbsp;</td>
                  </tr>
        <?php
    } ?>
                </table></td>
              </tr>
    <?php
} else {
    if (isset($_GET['option_order_by'])) {
        $option_order_by = $_GET['option_order_by'];
    } else {
        $option_order_by = 'products_options_id';
    } ?>
              <tr>
                <td colspan="3" class="pageHeading">&nbsp;<?php echo HEADING_TITLE_OPT; ?>&nbsp;</td>
                <td class="text-right"><br><form name="option_order_by" action="<?php echo $aContents['products_properties']; ?>"><select name="selected" onChange="go_option()"><option value="products_options_id"<?php if ($option_order_by == 'products_options_id') {
                    echo ' checked="checked"';
                } ?>><?php echo TEXT_OPTION_ID; ?></option><option value="products_options_name"<?php if ($option_order_by == 'products_options_name') {
                    echo ' checked="checked"';
                } ?>><?php echo TEXT_OPTION_NAME; ?></option></select></form></td>
              </tr>
              <tr>
                <td colspan="4" class="smallText">
    <?php
    $per_page = MAX_ROW_LISTS_OPTIONS;
    $products_optionstable = $oostable['products_options'];
    $options = "SELECT * FROM $products_optionstable WHERE products_options_languages_id = '" . intval($_SESSION['language_id']) . "' ORDER BY " . $option_order_by;
    if (!isset($option_page)) {
        $option_page = 1;
    }
    $prev_option_page = $option_page - 1;
    $next_option_page = $option_page + 1;

    $option_result = $dbconn->Execute($options);

    $option_page_start = ($per_page * $option_page) - $per_page;
    $num_rows = $option_result->RecordCount();

    if ($num_rows <= $per_page) {
        $num_pages = 1;
    } elseif (($num_rows % $per_page) == 0) {
        $num_pages = ($num_rows / $per_page);
    } else {
        $num_pages = ($num_rows / $per_page) + 1;
    }
    $num_pages = (int) $num_pages;

    $options = $options . " LIMIT $option_page_start, $per_page";

    // Previous
    if ($prev_option_page) {
        echo '<a href="' . oos_href_link_admin($aContents['products_properties'], 'option_page=' . $prev_option_page) . '"> &lt;&lt; </a> | ';
    }

    for ($i = 1; $i <= $num_pages; $i++) {
        if ($i != $option_page) {
            echo '<a href="' . oos_href_link_admin($aContents['products_properties'], 'option_page=' . $i) . '">' . $i . '</a> | ';
        } else {
            echo '<b><font color=red>' . $i . '</font></b> | ';
        }
    }

    // Next
    if ($option_page != $num_pages) {
        echo '<a href="' . oos_href_link_admin($aContents['products_properties'], 'option_page=' . $next_option_page) . '"> &gt;&gt; </a>';
    } ?>
                </td>
              </tr>
                    <thead class="thead-dark">
                        <tr>
                            <th>&nbsp;<?php echo TABLE_HEADING_ID; ?>&nbsp;</th>
                            <th>&nbsp;<?php echo TABLE_HEADING_OPT_NAME; ?>&nbsp;</th>
                            <th>&nbsp;<?php echo TABLE_HEADING_OPT_TYPE; ?>&nbsp;</th>
                            <th class="text-center">&nbsp;<?php echo TABLE_HEADING_ACTION; ?>&nbsp;</th>
                        </tr>
                    </thead>
    <?php
    $next_id = 1;
    $rows = 0;
    $options = $dbconn->Execute($options);
    while ($options_values = $options->fields) {
        $rows++; ?>
              <tr class="<?php echo(floor($rows / 2) == ($rows / 2) ? 'table-secondary' : 'table-light'); ?>">
        <?php
        if (($action == 'update_option') && ($_GET['option_id'] == $options_values['products_options_id'])) {
            echo '<form name="option" action="' . oos_href_link_admin($aContents['products_properties'], 'action=update_option_name') . '" method="post">';
            $inputs = '';
            for ($i = 0, $n = is_countable($aLanguages) ? count($aLanguages) : 0; $i < $n; $i ++) {
                $option_name = $dbconn->Execute("SELECT products_options_name FROM " . $oostable['products_options'] . " WHERE products_options_id = '" . $options_values['products_options_id'] . "' AND  products_options_languages_id = '" . $aLanguages[$i]['id'] . "'");
                $option_name = $option_name->fields;
                if ($nLanguages > 1) {
                    $inputs .= oos_flag_icon($aLanguages[$i]);
                }
                $inputs .= ':&nbsp;<input type="text" name="option_name[' . $aLanguages[$i]['id'] . ']" size="20" value="' . $option_name['products_options_name'] . '">&nbsp;<br>';
            } ?>
                <td align="center" class="smallText">&nbsp;<?php echo $options_values['products_options_id']; ?><input type="hidden" name="option_id" value="<?php echo $options_values['products_options_id']; ?>">&nbsp;</td>
                <td class="smallText"><?php echo $inputs; ?></td>
                <td class="smallText"><?php echo oos_draw_option_type_pull_down_menu('option_type', $options_values['products_options_type']); ?>&nbsp;</td>
                <td class="smallText"><?php echo oos_submit_button(BUTTON_UPDATE); ?>&nbsp;<?php echo '<a class="btn btn-sm btn-warning mb-20" href="' . oos_href_link_admin($aContents['products_properties'], '') . '" role="button"><strong>' . BUTTON_CANCEL . '</strong></a>'; ?></a>&nbsp;</td>
            <?php
            echo '</form>' . "\n";
        } else {
            ?>
                <td align="center" class="smallText">&nbsp;<?php echo $options_values['products_options_id']; ?>&nbsp;</td>
                <td class="smallText">&nbsp;<?php echo $options_values['products_options_name']; ?>&nbsp;</td>
                <td align="center" class="smallText">&nbsp;<?php echo oos_options_type_name($options_values['products_options_type']); ?>&nbsp;</td>
                <td class="smallText">&nbsp;<?php echo '<a class="btn btn-sm btn-primary mb-20" href="' . oos_href_link_admin($aContents['products_properties'], 'action=update_option&option_id=' . $options_values['products_options_id'] . '&option_order_by=' . $option_order_by . '&option_page=' . $option_page) . '" role="button"><strong>' . BUTTON_EDIT . '</strong></a>'; ?>&nbsp;&nbsp;<?php echo '<a class="btn btn-sm btn-danger mb-20" href="' . oos_href_link_admin($aContents['products_properties'], 'action=delete_product_option&option_id=' . $options_values['products_options_id']) , '" role="button"><strong>' . BUTTON_DELETE . '</strong></a>'; ?>&nbsp;</td>
            <?php
        } ?>
              </tr>
        <?php
        $products_optionstable = $oostable['products_options'];
        $max_options_id_result = $dbconn->Execute("SELECT max(products_options_id) + 1 as next_id FROM $products_optionstable");
        $max_options_id_values = $max_options_id_result->fields;
        $next_id = $max_options_id_values['next_id'];

        // Move that ADOdb pointer!
        $options->MoveNext();
    }

    if ($action != 'update_option') {
        ?>
			<tr>
                <td colspan="4"></td>
			</tr>
            <tr class="table-warning">
        <?php
        echo '<form name="options" action="' . oos_href_link_admin($aContents['products_properties'], 'action=add_product_options&option_page=' . $option_page) . '" method="post"><input type="hidden" name="products_options_id" value="' . $next_id . '">';
        $inputs = '';
        for ($i = 0, $n = is_countable($aLanguages) ? count($aLanguages) : 0; $i < $n; $i ++) {
            if ($nLanguages > 1) {
                $inputs .= oos_flag_icon($aLanguages[$i]);
            }
            $inputs .= ':&nbsp;<input type="text" name="option_name[' . $aLanguages[$i]['id'] . ']" size="20">&nbsp;<br>';
        } ?>
                <td align="center" class="smallText">&nbsp;<?php echo $next_id; ?>&nbsp;</td>
                <td class="smallText"><?php echo $inputs; ?></td>
                <td class="smallText"><?php echo oos_draw_option_type_pull_down_menu('option_type'); ?></td>
                <td class="smallText">&nbsp;<?php echo oos_submit_button(BUTTON_INSERT); ?>&nbsp;</td>
        <?php
        echo '</form>'; ?>
              </tr>
        <?php
    }
}
?>
				</table>
			</div>
		</div>
<!-- options eof //-->
		<div class="col-md-6">

			<div class="table-responsive">
				<table class="table table-hover w-100">
<!-- value //-->
<?php
if ($action == 'delete_option_value') { // delete product option value
    $products_options_valuestable = $oostable['products_options_values'];
    $values = $dbconn->Execute("SELECT products_options_values_id, products_options_values_name FROM $products_options_valuestable WHERE products_options_values_id = '" . intval($_GET['value_id']) . "' AND products_options_values_languages_id = '" . intval($_SESSION['language_id']) . "'");
    $values_values = $values->fields; ?>
              <tr>
                <td colspan="3" class="pageHeading">&nbsp;<?php echo $values_values['products_options_values_name']; ?>&nbsp;</td>
                <td>&nbsp;&nbsp;</td>
              </tr>
              <tr>
                <td>

                <table class="table table-striped table-hover w-100">
    <?php
    $productstable = $oostable['products'];
    $products_attributestable = $oostable['products_attributes'];
    $products_optionstable = $oostable['products_options'];
    $products_descriptiontable = $oostable['products_description'];
    $products = $dbconn->Execute("SELECT p.products_id, pd.products_name, po.products_options_name FROM $productstable p, $products_attributestable pa, $products_optionstable po, $products_descriptiontable pd WHERE pd.products_id = p.products_id AND pd.products_languages_id = '" . intval($_SESSION['language_id']) . "' AND po.products_options_languages_id = '" . intval($_SESSION['language_id']) . "' AND pa.products_id = p.products_id AND pa.options_values_id='" . intval($_GET['value_id']) . "' AND po.products_options_id = pa.options_id ORDER BY pd.products_name");
    if ($products->RecordCount()) {
        ?>
                    <thead class="thead-dark">
                        <tr>
                            <th class="text-center">&nbsp;<?php echo TABLE_HEADING_ID; ?>&nbsp;</th>
                            <th>&nbsp;<?php echo TABLE_HEADING_PRODUCT; ?>&nbsp;</th>
                            <th>&nbsp;<?php echo TABLE_HEADING_OPT_NAME; ?>&nbsp;</th>
                        </tr>
                    </thead>
        <?php
        $rows = 0;
        while ($products_values = $products->fields) {
            $rows++; ?>
                  <tr class="<?php echo(floor($rows / 2) == ($rows / 2) ? 'table-secondary' : 'table-light'); ?>">
                    <td align="center" class="smallText">&nbsp;<?php echo $products_values['products_id']; ?>&nbsp;</td>
                    <td class="smallText">&nbsp;<?php echo $products_values['products_name']; ?>&nbsp;</td>
                    <td class="smallText">&nbsp;<?php echo $products_values['products_options_name']; ?>&nbsp;</td>
                  </tr>
            <?php
            // Move that ADOdb pointer!
            $products->MoveNext();
        } ?>

                  <tr>
                    <td class="main" colspan="3"><br><?php echo TEXT_WARNING_OF_DELETE; ?></td>
                  </tr>
                  <tr>
                    <td class="main" align="right" colspan="3"><br><?php echo '<a class="btn btn-sm btn-warning mb-20" href="' . oos_href_link_admin($aContents['products_properties'], 'value_page=' . $value_page . '&attribute_page=' . $attribute_page) . '" role="button"><strong>' . BUTTON_CANCEL . '</strong></a>'; ?>&nbsp;</td>
                  </tr>
        <?php
    } else {
        ?>
                  <tr>
                    <td class="main" colspan="3"><br><?php echo TEXT_OK_TO_DELETE; ?></td>
                  </tr>
                  <tr>
                    <td class="main" align="right" colspan="3"><br><?php echo '<a class="btn btn-sm btn-danger mb-20" href="' . oos_href_link_admin($aContents['products_properties'], 'action=delete_value&value_id=' . $_GET['value_id']) . '" role="button"><strong>' . BUTTON_DELETE . '</strong></a>'; ?>&nbsp;&nbsp;&nbsp;<?php echo '<a class="btn btn-sm btn-warning mb-20" href="' . oos_href_link_admin($aContents['products_properties'], '&option_page=' . $option_page . '&value_page=' . $value_page . '&attribute_page=' . $attribute_page) . '" role="button"><strong>' . BUTTON_CANCEL . '</strong></a>'; ?></a>&nbsp;</td>
                  </tr>
        <?php
    } ?>
                </table></td>
              </tr>
    <?php
} else {
    ?>
              <tr>
                <td colspan="3" class="pageHeading">&nbsp;<?php echo HEADING_TITLE_VAL; ?>&nbsp;</td>
                <td>&nbsp;&nbsp;</td>
              </tr>
              <tr>
                <td colspan="4" class="smallText">
    <?php
    $per_page = MAX_ROW_LISTS_OPTIONS;
    $products_options_valuestable = $oostable['products_options_values'];
    $products_options_values_to_products_optionstable = $oostable['products_options_values_to_products_options'];
    $values = "SELECT pov.products_options_values_id, pov.products_options_values_name, pov2po.products_options_id FROM $products_options_valuestable pov left join $products_options_values_to_products_optionstable pov2po on pov.products_options_values_id = pov2po.products_options_values_id WHERE pov.products_options_values_languages_id = '" . intval($_SESSION['language_id']) . "' ORDER BY pov.products_options_values_id";

    if (!isset($value_page)) {
        $value_page = 1;
    }
    $prev_value_page = $value_page - 1;
    $next_value_page = $value_page + 1;

    $value_result = $dbconn->Execute($values);

    $value_page_start = ($per_page * $value_page) - $per_page;
    $num_rows = $value_result->RecordCount();

    if ($num_rows <= $per_page) {
        $num_pages = 1;
    } elseif (($num_rows % $per_page) == 0) {
        $num_pages = ($num_rows / $per_page);
    } else {
        $num_pages = ($num_rows / $per_page) + 1;
    }
    $num_pages = (int) $num_pages;

    $values = $values . " LIMIT $value_page_start, $per_page";

    // Previous
    if ($prev_value_page) {
        echo '<a href="' . oos_href_link_admin($aContents['products_properties'], 'option_order_by=' . $option_order_by . '&value_page=' . $prev_value_page) . '"> &lt;&lt; </a> | ';
    }

    for ($i = 1; $i <= $num_pages; $i++) {
        if ($i != $value_page) {
            echo '<a href="' . oos_href_link_admin($aContents['products_properties'], 'option_order_by=' . $option_order_by . '&value_page=' . $i) . '">' . $i . '</a> | ';
        } else {
            echo '<b><font color=red>' . $i . '</font></b> | ';
        }
    }

    // Next
    if ($value_page != $num_pages) {
        echo '<a href="' . oos_href_link_admin($aContents['products_properties'], 'option_order_by=' . $option_order_by . '&value_page=' . $next_value_page) . '"> &gt;&gt;</a> ';
    } ?>
                </td>
              </tr>

                    <thead class="thead-dark">
                        <tr>
                            <th>&nbsp;<?php echo TABLE_HEADING_ID; ?>&nbsp;</th>
                            <th>&nbsp;<?php echo TABLE_HEADING_OPT_NAME; ?>&nbsp;</th>
                            <th>&nbsp;<?php echo TABLE_HEADING_OPT_VALUE; ?>&nbsp;</th>
                            <th class="text-center">&nbsp;<?php echo TABLE_HEADING_ACTION; ?>&nbsp;</th>
                        </tr>
                    </thead>
    <?php
    $next_id = 1;
    $rows = 0;
    $values = $dbconn->Execute($values);
    while ($values_values = $values->fields) {
        $options_name = oos_options_name($values_values['products_options_id']);
        $option_id = $values_values['products_options_id'];
        $values_name = $values_values['products_options_values_name'];
        $rows++; ?>
              <tr class="<?php echo(floor($rows / 2) == ($rows / 2) ? 'table-secondary' : 'table-light'); ?>">
        <?php
        if (($action == 'update_option_value') && ($_GET['value_id'] == $values_values['products_options_values_id'])) {
            echo '<form name="values" action="' . oos_href_link_admin($aContents['products_properties'], 'action=update_value') . '" method="post">';
            $inputs = '';
            for ($i = 0, $n = is_countable($aLanguages) ? count($aLanguages) : 0; $i < $n; $i ++) {
                $products_options_valuestable = $oostable['products_options_values'];
                $value_name = $dbconn->Execute("SELECT products_options_values_name FROM $products_options_valuestable WHERE products_options_values_id = '" . $values_values['products_options_values_id'] . "' AND products_options_values_languages_id= '" . $aLanguages[$i]['id'] . "'");
                $value_name = $value_name->fields;
                if ($nLanguages > 1) {
                    $inputs .= oos_flag_icon($aLanguages[$i]);
                }
                $inputs .= ':&nbsp;<input type="text" name="value_name[' . $aLanguages[$i]['id'] . ']" size="15" value="' . $value_name['products_options_values_name'] . '">&nbsp;<br>';
            } ?>
                <td align="center" class="smallText">&nbsp;<?php echo $values_values['products_options_values_id']; ?><input type="hidden" name="value_id" value="<?php echo $values_values['products_options_values_id']; ?>">&nbsp;</td>
                <td align="center" class="smallText">&nbsp;<?php echo "\n"; ?><select name="option_id">
            <?php
            $products_optionstable = $oostable['products_options'];
            $options = $dbconn->Execute("SELECT products_options_id, products_options_name FROM $products_optionstable WHERE products_options_languages_id = '" . intval($_SESSION['language_id']) . "' ORDER BY products_options_name");
            while ($options_values = $options->fields) {
                echo "\n" . '<option name="' . $options_values['products_options_name'] . '" value="' . $options_values['products_options_id'] . '"';
                if ($values_values['products_options_id'] == $options_values['products_options_id']) {
                    echo ' selected';
                }
                echo '>' . $options_values['products_options_name'] . '</option>';

                // Move that ADOdb pointer!
                $options->MoveNext();
            } ?>
                </select>&nbsp;</td>
                <td class="smallText"><?php echo $inputs; ?></td>
                <td align="center" class="smallText">&nbsp;<?php echo oos_submit_button(BUTTON_UPDATE); ?>&nbsp;<?php echo '<a class="btn btn-sm btn-warning mb-20" href="' . oos_href_link_admin($aContents['products_properties'], '') . '" role="button"><strong>' .  BUTTON_CANCEL . '</strong></a>'; ?>&nbsp;</td>
            <?php
            echo '</form>';
        } else {
            ?>
                <td align="center" class="smallText">&nbsp;<?php echo $values_values["products_options_values_id"]; ?>&nbsp;</td>
                <td align="center" class="smallText">&nbsp;<?php echo $options_name; ?>&nbsp;</td>
                <td class="smallText">&nbsp;<?php echo $values_name; ?>&nbsp;</td>
                <td align="center" class="smallText">&nbsp;<?php echo '<a class="btn btn-sm btn-primary mb-20" href="' . oos_href_link_admin($aContents['products_properties'], 'action=update_option_value&value_id=' . $values_values['products_options_values_id'] . '&value_page=' . $value_page) . '" role="button"><strong>' . BUTTON_EDIT . '</strong></a>'; ?>&nbsp;&nbsp;<?php echo '<a class="btn btn-sm btn-danger mb-20" href="' . oos_href_link_admin($aContents['products_properties'], 'action=delete_option_value&value_id=' . $values_values['products_options_values_id'] . '&option_id=' . $option_id) , '" role="button"><strong>' . BUTTON_DELETE . '</strong></a>'; ?>&nbsp;</td>

            <?php
        }
        $products_options_valuestable = $oostable['products_options_values'];
        $max_values_id_result = $dbconn->Execute("SELECT max(products_options_values_id) + 1 as next_id FROM $products_options_valuestable");
        $max_values_id_values = $max_values_id_result->fields;
        $next_id = $max_values_id_values['next_id'];

        // Move that ADOdb pointer!
        $values->MoveNext();
    } ?>
              </tr>

    <?php
    if ($action != 'update_option_value') {
        ?>
			<tr>
                <td colspan="4"></td>
			</tr>
            <tr class="table-warning">

        <?php
        echo '<form name="values" action="' . oos_href_link_admin($aContents['products_properties'], 'action=add_product_option_values&value_page=' . $value_page) . '" method="post">'; ?>
                <td align="center" class="smallText">&nbsp;<?php echo $next_id; ?>&nbsp;</td>
                <td align="center" class="smallText">&nbsp;<select name="option_id">
        <?php
        $products_optionstable = $oostable['products_options'];
        $options = $dbconn->Execute("SELECT products_options_id, products_options_name FROM $products_optionstable WHERE products_options_languages_id = '" . intval($_SESSION['language_id']) . "' ORDER BY products_options_name");
        while ($options_values = $options->fields) {
            echo '<option name="' . $options_values['products_options_name'] . '" value="' . $options_values['products_options_id'] . '">' . $options_values['products_options_name'] . '</option>';

            // Move that ADOdb pointer!
            $options->MoveNext();
        }

        $inputs = '';
        for ($i = 0, $n = is_countable($aLanguages) ? count($aLanguages) : 0; $i < $n; $i ++) {
            if ($nLanguages > 1) {
                $inputs .= oos_flag_icon($aLanguages[$i]);
            }
            $inputs .= ':&nbsp;<input type="text" name="value_name[' . $aLanguages[$i]['id'] . ']" size="15">&nbsp;<br>';
        } ?>
                </select>&nbsp;</td>
                <td class="smallText"><input type="hidden" name="value_id" value="<?php echo $next_id; ?>"><?php echo $inputs; ?></td>
                <td align="center" class="smallText">&nbsp;<?php echo oos_submit_button(BUTTON_INSERT); ?>&nbsp;</td>
        <?php
        echo '</form>'; ?>
              </tr>
        <?php
    }
}
?>
				</table>
			</div>
		</div>
<!-- option value eof //-->

                </div>
            </div>
        </div>

    </section>
    <!-- Page footer //-->
    <footer>
        <span>&copy; <?php echo date('Y'); ?> - <a href="https://www.oos-shop.de" target="_blank" rel="noopener">MyOOS [Shopsystem]</a></span>
    </footer>
</div>

<?php
require 'includes/bottom.php';
require 'includes/nice_exit.php';
