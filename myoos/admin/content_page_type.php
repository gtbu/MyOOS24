<?php
/** ---------------------------------------------------------------------

   MyOOS [Shopsystem]
   https://www.oos-shop.de

   Copyright (c) 2003 - 2024  by the MyOOS Development Team.
   ----------------------------------------------------------------------
   Based on:

   osCommerce, Open Source E-Commerce Solutions
   http://www.oscommerce.com

   Copyright (c) 2003 osCommerce
   ----------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------- */

define('OOS_VALID_MOD', 'yes');
require 'includes/main.php';

/**
 * Return Page Type Name
 *
 * @param  $page_type_id
 * @param  $language
 * @return string
 */
function oosGetPageTypeName($page_type_id, $language_id = '')
{
    if (empty($language_id) || !is_numeric($language_id)) {
        $language_id = intval($_SESSION['language_id']);
    }

    // Get database information
    $dbconn = & oosDBGetConn();
    $oostable = & oosDBGetTables();

    $page_type_sql = "SELECT page_type_name 
                      FROM " . $oostable['page_type'] . " 
                      WHERE page_type_id = '" . $page_type_id . "' 
                      AND page_type_languages_id = '" . intval($language_id) . "'";
    $page_type = $dbconn->Execute($page_type_sql);

    return $page_type->fields['page_type_name'];
}


/**
 * Return Page Type
 *
 * @return array
 */
function oosGetPageType()
{
    $page_type_array = [];

    // Get database information
    $dbconn = & oosDBGetConn();
    $oostable = & oosDBGetTables();

    $page_type_sql = "SELECT page_type_id, page_type_name 
                      FROM " . $oostable['page_type'] . " 
                      WHERE page_type_languages_id = '" . intval($_SESSION['language_id']) . "' 
                      ORDER BY page_type_id";
    $page_type_result = $dbconn->Execute($page_type_sql);
    while ($page_type = $page_type_result->fields) {
        $page_type_array[] = ['id' => $page_type['page_type_id'], 'text' => $page_type['page_type_name']];
        // Move that ADOdb pointer!
        $page_type_result->MoveNext();
    }

    return $page_type_array;
}

$nPage = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
$action = filter_string_polyfill(filter_input(INPUT_GET, 'action')) ?: 'default';

switch ($action) {
    case 'insert':
    case 'save':
        $page_type_id = filter_input(INPUT_GET, 'ptID', FILTER_VALIDATE_INT);

        $languages = oos_get_languages();
        for ($i = 0, $n = is_countable($languages) ? count($languages) : 0; $i < $n; $i++) {
            $language_id = $languages[$i]['id'];

            $sql_data_array = ['page_type_name' => oos_db_prepare_input($_POST['page_type_name'][$language_id])];

            if ($action == 'insert') {
                if (oos_empty($page_type_id)) {
                    $next_id_result = $dbconn->Execute("SELECT max(page_type_id) as page_type_id FROM " . $oostable['page_type'] . "");
                    $next_id = $next_id_result->fields;
                    $page_type_id = $next_id['page_type_id'] + 1;
                }

                $insert_sql_data = ['page_type_id' => $page_type_id, 'page_type_languages_id' => $language_id];

                $sql_data_array = [...$sql_data_array, ...$insert_sql_data];

                oos_db_perform($oostable['page_type'], $sql_data_array);
            } elseif ($action == 'save') {
                oos_db_perform($oostable['page_type'], $sql_data_array, 'UPDATE', "page_type_id = '" . oos_db_input($page_type_id) . "' and page_type_languages_id = '" . intval($language_id) . "'");
            }
        }

        oos_redirect_admin(oos_href_link_admin($aContents['content_page_type'], 'page=' . $nPage . '&ptID=' . $page_type_id));
        break;

    case 'deleteconfirm':
        $ptID = filter_input(INPUT_GET, 'ptID', FILTER_VALIDATE_INT);

        $dbconn->Execute("DELETE FROM " . $oostable['page_type'] . " WHERE page_type_id = '" . oos_db_input($ptID) . "'");

        oos_redirect_admin(oos_href_link_admin($aContents['content_page_type'], 'page=' . $nPage));
        break;

    case 'delete':
        $ptID = filter_input(INPUT_GET, 'ptID', FILTER_VALIDATE_INT);

        $status_result = $dbconn->Execute("SELECT COUNT(*) AS total FROM " . $oostable['block_to_page_type'] . " WHERE page_type_id = '" . oos_db_input($ptID) . "'");
        $status = $status_result->fields;

        $remove_status = true;
        if ($status['total'] > 0) {
            $remove_status = false;
            $messageStack->add(ERROR_STATUS_USED_IN_ORDERS, 'error');
        }
        break;
}

require 'includes/header.php';
?>
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
                    <h2><?php echo HEADING_TITLE; ?></h2>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <?php echo '<a href="' . oos_href_link_admin($aContents['default']) . '">' . HEADER_TITLE_TOP . '</a>'; ?>
                        </li>
                        <li class="breadcrumb-item">
                            <?php echo '<a href="' . oos_href_link_admin($aContents['content_block'], 'selected_box=content') . '">' . BOX_HEADING_CONTENT . '</a>'; ?>
                        </li>
                        <li class="breadcrumb-item active">
                            <strong><?php echo HEADING_TITLE; ?></strong>
                        </li>
                    </ol>    
                </div>
            </div>
            <!-- END Breadcrumbs //-->    
            
            <div class="wrapper wrapper-content">
                <div class="row">
                    <div class="col-lg-12">                    
<!-- body_text //-->
    <div class="table-responsive">
        <table class="table w-100">
          <tr>
            <td valign="top">
                <table class="table table-striped table-hover w-100">
                    <thead class="thead-dark">
                        <tr>
                            <th><?php echo TABLE_HEADING_PAGE_TYPE; ?></th>
                            <th class="text-right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</th>
                        </tr>    
                    </thead>            
            
<?php
$rows = 0;
$aDocument = [];

$page_type_result_raw = "SELECT 
                                  page_type_id, page_type_name 
                              FROM 
                                  " . $oostable['page_type'] . " 
                              WHERE 
                                  page_type_languages_id = '" . intval($_SESSION['language_id']) . "' 
                              ORDER BY 
                                  page_type_id";
$page_type_split = new splitPageResults($nPage, MAX_DISPLAY_SEARCH_RESULTS, $page_type_result_raw, $page_type_result_numrows);
$page_type_result = $dbconn->Execute($page_type_result_raw);
while ($page_type = $page_type_result->fields) {
    $rows++;
    if ((!isset($_GET['ptID']) || (isset($_GET['ptID']) && ($_GET['ptID'] == $page_type['page_type_id']))) && !isset($oInfo) && (!str_starts_with((string) $action, 'new'))) {
        $oInfo = new objectInfo($page_type);
    }

    if (isset($oInfo) && is_object($oInfo) && ($page_type['page_type_id'] == $oInfo->page_type_id)) {
        $aDocument[] = ['id' => $rows,
                        'link' => oos_href_link_admin($aContents['content_page_type'], 'page=' . $nPage . '&ptID=' . $oInfo->page_type_id . '&action=edit')];
        echo '              <tr id="row-' . $rows .'">' . "\n";
    } else {
        $aDocument[] = ['id' => $rows,
                        'link' => oos_href_link_admin($aContents['content_page_type'], 'page=' . $nPage . '&ptID=' . $page_type['page_type_id'])];
        echo '              <tr id="row-' . $rows .'">' . "\n";
    }

    echo '                <td class="dataTableContent">' . $page_type['page_type_name'] . '</td>' . "\n"; ?>
                <td class="dataTableContent" align="right"><?php if (isset($oInfo) && is_object($oInfo) && ($page_type['page_type_id'] == $oInfo->page_type_id)) {
                    echo '<button class="btn btn-info" type="button"><i class="fa fa-eye-slash" title="' . IMAGE_ICON_INFO . '" aria-hidden="true"></i></i></button>';
                } else {
                    echo '<a href="' . oos_href_link_admin($aContents['content_page_type'], 'page=' . $nPage . '&ptID=' . $page_type['page_type_id']) . '"><button class="btn btn-default" type="button"><i class="fa fa-eye-slash"></i></button></a>';
                } ?>&nbsp;</td>
              </tr>
    <?php
                // Move that ADOdb pointer!
                $page_type_result->MoveNext();
}
?>
              <tr>
                <td colspan="2"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $page_type_split->display_count($page_type_result_numrows, MAX_DISPLAY_SEARCH_RESULTS, $nPage, TEXT_DISPLAY_NUMBER_OF_PAGE_TYPES); ?></td>
                    <td class="smallText" align="right"><?php echo $page_type_split->display_links($page_type_result_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $nPage); ?></td>
                  </tr>
<?php
 if ($action == 'default') {
     ?>
                  <tr>
                    <td colspan="2" align="right"><?php echo '<a href="' . oos_href_link_admin($aContents['content_page_type'], 'page=' . $nPage . '&action=new') . '">' . oos_button(BUTTON_INSERT) . '</a>'; ?></td>
                  </tr>
    <?php
 }
?>
                </table></td>
              </tr>
            </table></td>
<?php
  $heading = [];
$contents = [];

switch ($action) {
    case 'new':
        $heading[] = ['text' => '<b>' . TEXT_INFO_HEADING_NEW_PAGE_TYPE . '</b>'];

        $contents = ['form' => oos_draw_form('id', 'status', $aContents['content_page_type'], 'page=' . $nPage . '&action=insert', 'post', false)];
        $contents[] = ['text' => TEXT_INFO_INSERT_INTRO];

        $page_type_inputs_string = '';
        $languages = oos_get_languages();
        for ($i = 0, $n = is_countable($languages) ? count($languages) : 0; $i < $n; $i++) {
            $page_type_inputs_string .= '<br>' . oos_flag_icon($languages[$i]) . '&nbsp;' . oos_draw_input_field('page_type_name[' . $languages[$i]['id'] . ']');
        }

        $contents[] = ['text' => '<br>' . TEXT_INFO_PAGE_TYPE_NAME . $page_type_inputs_string];
        $contents[] = ['align' => 'center', 'text' => '<br>' . oos_submit_button(BUTTON_INSERT) . ' <a class="btn btn-sm btn-warning mb-20" href="' . oos_href_link_admin($aContents['content_page_type'], 'page=' . $nPage) . '" role="button"><strong>' . BUTTON_CANCEL . '</strong></a>'];

        break;
    case 'edit':
        $heading[] = ['text' => '<b>' . TEXT_INFO_HEADING_EDIT_PAGE_TYPE . '</b>'];

        $contents = ['form' => oos_draw_form('id', 'status', $aContents['content_page_type'], 'page=' . $nPage . '&ptID=' . $oInfo->page_type_id  . '&action=save', 'post', false)];
        $contents[] = ['text' => TEXT_INFO_EDIT_INTRO];

        $page_type_inputs_string = '';
        $languages = oos_get_languages();
        for ($i = 0, $n = is_countable($languages) ? count($languages) : 0; $i < $n; $i++) {
            $page_type_inputs_string .= '<br>' . oos_flag_icon($languages[$i]) . '&nbsp;' . oos_draw_input_field('page_type_name[' . $languages[$i]['id'] . ']', oosGetPageTypeName($oInfo->page_type_id, $languages[$i]['id']));
        }

        $contents[] = ['text' => '<br>' . TEXT_INFO_PAGE_TYPE_NAME . $page_type_inputs_string];
        $contents[] = ['align' => 'center', 'text' => '<br>' . oos_submit_button(BUTTON_UPDATE) . ' <a class="btn btn-sm btn-warning mb-20" href="' . oos_href_link_admin($aContents['content_page_type'], 'page=' . $nPage . '&ptID=' . $oInfo->page_type_id) . '" role="button"><strong>' . BUTTON_CANCEL . '</strong></a>'];

        break;

    case 'delete':
        $heading[] = ['text' => '<b>' . TEXT_INFO_HEADING_DELETE_PAGE_TYPE . '</b>'];

        $contents = ['form' => oos_draw_form('id', 'status', $aContents['content_page_type'], 'page=' . $nPage . '&ptID=' . $oInfo->page_type_id  . '&action=deleteconfirm', 'post', false)];
        $contents[] = ['text' => TEXT_INFO_DELETE_INTRO];
        $contents[] = ['text' => '<br><b>' . $oInfo->page_type_name . '</b>'];
        if ($remove_status) {
            $contents[] = ['align' => 'center', 'text' => '<br>' . oos_submit_button(BUTTON_DELETE) . ' <a class="btn btn-sm btn-warning mb-20" href="' . oos_href_link_admin($aContents['content_page_type'], 'page=' . $nPage . '&ptID=' . $oInfo->page_type_id) . '" role="button"><strong>' . BUTTON_CANCEL . '</strong></a>'];
        }

        break;

    default:
        if (isset($oInfo) && is_object($oInfo)) {
            $heading[] = ['text' => '<b>' . $oInfo->page_type_name . '</b>'];

            $contents[] = ['align' => 'center', 'text' => '<a href="' . oos_href_link_admin($aContents['content_page_type'], 'page=' . $nPage . '&ptID=' . $oInfo->page_type_id . '&action=edit') . '">' . oos_button(BUTTON_EDIT) . '</a> <a href="' . oos_href_link_admin($aContents['content_page_type'], 'page=' . $nPage . '&ptID=' . $oInfo->page_type_id . '&action=delete') . '">' . oos_button(BUTTON_DELETE) . '</a>'];

            $page_type_inputs_string = '';
            $languages = oos_get_languages();
            for ($i = 0, $n = is_countable($languages) ? count($languages) : 0; $i < $n; $i++) {
                $page_type_inputs_string .= '<br>' . oos_flag_icon($languages[$i]) . '&nbsp;' . oosGetPageTypeName($oInfo->page_type_id, $languages[$i]['id']);
            }

            $contents[] = ['text' => $page_type_inputs_string];
        }
        break;
}

if ((oos_is_not_null($heading)) && (oos_is_not_null($contents))) {
    ?>
    <td class="w-25" valign="top">
        <table class="table table-striped">
      <?php
      $box = new box();
    echo $box->infoBox($heading, $contents); ?>
        </table> 
    </td> 
      <?php
}
?>
          </tr>
        </table>
    </div>
<!-- body_text_eof //-->

                </div>
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

if (isset($aDocument) || !empty($aDocument)) {
    echo '<script nonce="' . NONCE . '">' . "\n";
    $nDocument = is_countable($aDocument) ? count($aDocument) : 0;
    for ($i = 0, $n = $nDocument; $i < $n; $i++) {
        echo 'document.getElementById(\'row-'. $aDocument[$i]['id'] . '\').addEventListener(\'click\', function() { ' . "\n";
        echo 'document.location.href = "' . $aDocument[$i]['link'] . '";' . "\n";
        echo '});' . "\n";
    }
    echo '</script>' . "\n";
}

?>
<script nonce="<?php echo NONCE; ?>">
let element = document.getElementById('page');
if (element) {

	let form = document.getElementById('pages'); 

	element.addEventListener('change', function() { 
		form.submit(); 
	});
}
</script>
<?php

require 'includes/nice_exit.php';
