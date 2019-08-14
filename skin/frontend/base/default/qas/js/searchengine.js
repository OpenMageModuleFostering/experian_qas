/**
 * Experian Qas Singleline and Verification search engine JS
 *
 * @category  Experian
 * @package   Experian_Qas
 * @copyright 2012 Experian
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

function saveAddress(addressForm, addressType) {
    if (checkout.loadWaiting!=false) return;
    var validator = new Validation(addressForm.form);
    if (validator.validate()) {
        checkout.setLoadWaiting(addressType);
        new Ajax.Request(
            addressForm.saveUrl,
            {
                method: 'post',
                onComplete: addressForm.onComplete,
                onSuccess: addressForm.onSave,
                onFailure: checkout.ajaxFailure.bind(checkout),
                parameters: Form.serialize(addressForm.form)
            }
        );
    }
}
