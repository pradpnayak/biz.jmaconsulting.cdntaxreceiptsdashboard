<?php

require_once 'cdntaxreceiptsdashboard.civix.php';

/**
 * Implementation of hook_civicrm_config
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function cdntaxreceiptsdashboard_civicrm_config(&$config) {
  _cdntaxreceiptsdashboard_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function cdntaxreceiptsdashboard_civicrm_xmlMenu(&$files) {
  _cdntaxreceiptsdashboard_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function cdntaxreceiptsdashboard_civicrm_install() {
  _cdntaxreceiptsdashboard_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function cdntaxreceiptsdashboard_civicrm_uninstall() {
  _cdntaxreceiptsdashboard_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function cdntaxreceiptsdashboard_civicrm_enable() {
  _cdntaxreceiptsdashboard_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function cdntaxreceiptsdashboard_civicrm_disable() {
  _cdntaxreceiptsdashboard_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function cdntaxreceiptsdashboard_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _cdntaxreceiptsdashboard_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function cdntaxreceiptsdashboard_civicrm_managed(&$entities) {
  _cdntaxreceiptsdashboard_civix_civicrm_managed($entities);
}

/**
 * Implementation of hook_civicrm_caseTypes
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function cdntaxreceiptsdashboard_civicrm_caseTypes(&$caseTypes) {
  _cdntaxreceiptsdashboard_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implementation of hook_civicrm_alterSettingsFolders
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function cdntaxreceiptsdashboard_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _cdntaxreceiptsdashboard_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implementation of hook_civicrm_check
 */
function cdntaxreceiptsdashboard_civicrm_check(&$messages) {
  $cdnMessages = cdn_check_dependencies(TRUE);

  foreach ($cdnMessages as &$message) {
    $message->setLevel(5);
  }

  $messages += $cdnMessages;
}

/**
 * Function to check if related Grant extension is enabled/disabled
 *
 * return array of enabled extensions 
 */
function checkRelatedExtensions($name) {
  $enableDisable = NULL;
  $sql = "SELECT is_active FROM civicrm_extension WHERE full_name = '{$name}'";
  $enableDisable = CRM_Core_DAO::singleValueQuery($sql);
  return $enableDisable;
}

/**
 * Checks all dependencies for the extension
 *
 * @returns array  Array with one CRM_Utils_Check_Message object for each unmet dependency
 */
function cdn_check_dependencies($display = TRUE) {
  $messages = array();

  $enabled = checkRelatedExtensions('org.civicrm.cdntaxreceipts');
  if (!$enabled) {
    $messages[] = new CRM_Utils_Check_Message(
      'cdn_taxreceiptcheck',
        ts('This extension requires CDNTaxReceipts extension to be downloaded and installed.'),
        ts('CDN Tax Receipts dashboard requirements not met')
    );
    // Now display a nice alert for all these messages
    if ($display) {
      foreach ($messages as $message) {
        CRM_Core_Session::setStatus($message->getMessage(), $message->getTitle(), 'error');
      }
    }
  }
  return $messages;
}

function cdntaxreceiptsdashboard_civicrm_searchColumns($objectName, &$headers,  &$values, &$selector) {
  $enabled = checkRelatedExtensions('org.civicrm.cdntaxreceipts');
  if (!$enabled) {
    return;
  }

  if ($objectName == 'contribution') {
    foreach ($values as &$contribution) {
      list($issuedOn, $receiptId) = cdntaxreceipts_issued_on($contribution['contribution_id']);
      if (isset($receiptId)) {
        $existingReceipt = cdntaxreceipts_load_receipt($receiptId);
        $receipt = $existingReceipt;
        $reissue = 1;
      }
      else {
        $receipt = array();
        $reissue = 0;
      }

      // make tax receipt available for download, or do not display link if ineligible
      if (cdntaxreceipts_eligibleForReceipt($contribution['contribution_id'])) {
        $contribObject = (object)$contribution;
        $contribObject->id = $contribution['contribution_id'];
        list($result, $method, $pdf) = cdntaxreceipts_issueTaxReceipt($contribObject);
        sendFile($contribution['contribution_id'], $contribution['contact_id'], $pdf);

        // Make links
        $headers[6] = array(
          'name' => ts('Tax Receipt'),
          'sort' => 'tax_receipt',
          'field_name' => 'tax_receipt',
          'direction' => 4,
          'weight' => 60,
        );

        if ($reissue) {
          $values; // FIXME
        }
      }
    }
    exit;
  }
}
 
function sendFile($contributionId, $contactId, $filename) {
  if ($filename && file_exists($filename)) {
    // set up headers and stream the file
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename='.basename($filename));
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($filename));
    ob_clean();
    flush();
    readfile($filename);

    CRM_Utils_System::civiExit();
  }
  else {
    $statusMsg = ts('File has expired. Please retrieve receipt from the email archive.', array('domain' => 'biz.jmaconsulting.cdntaxreceiptsdashboard'));
    CRM_Core_Session::setStatus( $statusMsg, '', 'error' );
  }
}