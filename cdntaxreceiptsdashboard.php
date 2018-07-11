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

  if ($objectName == 'contribution' && CRM_Core_Smarty::singleton()->get_template_vars('context') == 'user') {
    foreach ($values as &$contribution) {
      list($issuedOn, $receiptId) = cdntaxreceipts_issued_on($contribution['contribution_id']);
      $contribId = $contribution['contribution_id'];
      if (cdntaxreceipts_eligibleForReceipt($contribId)) {
        if (isset($receiptId)) {
          $contribution['tax_receipt'] = "<a class='cdn-receipt' data-receiptid='{$receiptId}' data-contributiondid='{$contribId}' href='" . CRM_Utils_System::url('civicrm/generatetaxreceipt', "reset=1") . "'>Re-issue</a>";
        }
        else {
          $receiptId = 0;
          $contribution['tax_receipt'] = "<a class='cdn-receipt' data-receiptid='{$receiptId}' data-contributiondid='{$contribId}' href='" . CRM_Utils_System::url('civicrm/generatetaxreceipt', "reset=1") . "'>Issue</a>";
        }
      }
    }
  }
}

function cdntaxreceiptsdashboard_civicrm_pageRun(&$page) {
  if (get_class($page) == "CRM_Contact_Page_View_UserDashBoard") {
    $enabled = checkRelatedExtensions('org.civicrm.cdntaxreceipts');
    if (!$enabled) {
      return;
    }
    CRM_Core_Resources::singleton()->addScriptFile('biz.jmaconsulting.cdntaxreceiptsdashboard', 'templates/js/cdn.js');
  }
}