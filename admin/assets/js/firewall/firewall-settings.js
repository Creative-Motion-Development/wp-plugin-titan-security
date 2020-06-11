(function ($) {
    'use strict';

    jQuery(document).ready(function ($) {
        let excludedRulesFieldElem = $('#js-wtitan-excluded-rules__field'),
            excludedRules = ("" !== excludedRulesFieldElem.val()) ? excludedRulesFieldElem.val().split(',') : [];

        $('.js-wtitan-excluded-rules__checkbox').click(function () {
            let ruleID = parseInt($(this).val());

            if ($(this).is(":checked")) {
                excludedRules.splice($.inArray(parseInt(ruleID), excludedRules), 1);
            } else {
                excludedRules.push(ruleID);
            }

            excludedRulesFieldElem.val(excludedRules.join(','));
        });
    });
})(jQuery);
