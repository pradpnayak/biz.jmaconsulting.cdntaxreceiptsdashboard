<?php

require_once 'CRM/Core/Page.php';

class CRM_CDN_Page_GenerateTaxReceipt extends CRM_Core_Page {
  function run() {
    $values = $_POST;
    $contributionId = CRM_Utils_Array::value('contributionid', $values);
    if (CRM_Utils_Array::value('receiptid', $values)) {
      $contribution = new CRM_Contribute_DAO_Contribution();
      $contribution->id = $contributionId;
      $contribution->find(TRUE);
      list($result, $method, $pdf) = cdntaxreceipts_issueTaxReceipt($contribution);
      self::sendfile($contributionId, $contribution->contact_id, $pdf);
    }
  }
 
  public static function sendFile($contributionId, $contactId, $filename) {
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
}
