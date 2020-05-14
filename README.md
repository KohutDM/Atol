**ATOL MODULE FOR MAGENTO 2**

Integration with the Atol service https://online.atol.ru/lib/. 
Which provides services of electronic cash registers.

**Module functionality**

Data (invoice/creditmemo) transfer to Atol:
- atol status "new" is set to the invoice/creditmemo automatically when they 
are created;
- invoice/creditmemo transfer is made by cron tasks selecting them by their
atol status "new" (using Smile Connector module);
- after successful response from Atol, invoice/creditmemo get status "wait";

Getting registration data (invoice/creditmemo) from Atol:
- using cron and Smile Connector module;
- after successful response from Atol, invoice/creditmemo get status "done";

**Events**
- sales_order_invoice_save_commit_after (invoice get atol status "new");
- sales_order_creditmemo_save_after (creditmemo get atol status "new").

**Cron**
- "mmd_atol_export_invoice_to_atol"
- "mmd_atol_check_sell_cheque_in_atol"
- "mmd_atol_export_creditmemo_to_atol"
- "mmd_atol_check_refund_cheque_in_atol"
