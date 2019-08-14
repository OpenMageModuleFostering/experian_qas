/**
 * Experian Qas Intuitive JS
 *
 * @category  Experian
 * @package   Experian_Qas
 * @copyright 2012 Experian
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * Quick Address Search form
 */
Varien.addressSearchForm = Class.create();
Varien.addressSearchForm.prototype = {
    initialize : function(form, field, emptyText, country, region, city, zip, street, street2){
        this.form   = $(form);
        this.field  = $(field);
        this.emptyText = emptyText;
        
        this.country = country;
        this.region = region;
        this.city = city;
        this.zip = zip;
        this.street = street;
        this.street2 = street2;

        Event.observe(this.field, 'focus', this.focus.bind(this));
        Event.observe(this.field, 'blur', this.blur.bind(this));
        this.blur();
    },

    focus : function(event){
        if(this.field.value==this.emptyText){
            this.field.value='';
        }

    },

    blur : function(event){
        if(this.field.value==''){
            this.field.value=this.emptyText;
        }
    },
    
    getQueryString : function() {
        this.queryString = '';
        var street = $(this.street).value;
        var zip = $(this.zip).value;
        var city = $(this.city).value;
        var country = $(this.country).value;
        this.queryString += QUERY_PARAM + '=' + encodeURIComponent(street) + 
            /*' ' + encodeURIComponent(zip) +
            ' ' + encodeURIComponent(city) +*/
            '&' + QUERY_COUNTRY + '=' + encodeURIComponent(country);
        
        return this.queryString;
    },

    initAutocomplete : function(url, destinationElement){
        new Ajax.Autocompleter(
            this.field,
            destinationElement,
            url,
            {
                paramName: this.field.name, 
                callback: this.getQueryString.bind(this),
                method: 'get',
                minChars: 2,
                updateElement: this._selectAutocompleteItem.bind(this),
                onShow : function(element, update) {
                    if(!update.style.position || update.style.position=='absolute') {
                        update.style.position = 'absolute';
                        Position.clone(element, update, {
                            setHeight: false,
                            offsetTop: element.offsetHeight
                        });
                    }
                    Effect.Appear(update,{duration:0});
                }

            }
        );
    },
    
    _selectAutocompleteItem : function(element){
        if(typeof element.attributes.moniker != 'undefined'){
            new Ajax.Request(
                NORMALIZE_URL,
                {
                    method: 'post',
                    parameters: QUERY_MONIKER+'='+element.attributes.moniker.value+'&'+QUERY_COUNTRY+'='+$(this.country).value,
                    onComplete: this.onComplete,
                    onFailure: function(response){
                        alert('An error occurred while processing your request');
                        this.onComplete;
                    },
                    onSuccess: function(response){
                        if (response && response.responseText){
                            if (typeof(response.responseText) == 'string') {
                                eval('result = ' + response.responseText);
                            }
                            $(this.street).value = result.street_1;
                            $(this.street2).value = result.street_2;
                            $(this.city).value = result.city;
                            $(this.zip).value = result.zip;
                            
                            /* Special case for region option */
                            var optionValueToSelect = false;
                            $$('#' + this.region + ' option').each(function(option) {
                                if (option.text == result.region) {
                                    optionValueToSelect = option.value;
                                }
                            });
                            if (optionValueToSelect != false) {
                                $(this.region).value = optionValueToSelect;
                            } else {
                                $(this.region).value = result.region;
                            }
                        }
                    }.bind(this)
                }
            )
        }
    }    
}
