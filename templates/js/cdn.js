CRM.$(function($) {
  $('.cdn-receipt').click(function(event) {
    event.preventDefault();
      $.ajax({
        type: "POST",
        url: CRM.url('civicrm/generatetaxreceipt'),
        data: {'receiptid': $(this).data("receiptid")},
        dataType: 'json',
        success: function(cdn){
         alert(cdn);

        }
      });
    return false;
  });
});
