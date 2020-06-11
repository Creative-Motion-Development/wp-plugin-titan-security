(function ($) {

    var VARS = [
        'titan_interstitial_user',
        'titan_interstitial_token',
        'titan_interstitial_session',
    ];

    function WTitanLoginInterstitial($el, options) {

        if ($.isPlainObject($el)) {
            options = $el;
            $el = null;
        }

        if (!$el) {
            $('form').each(function () {
                var $form = $(this);

                if ($form.attr('id').indexOf('titan-') === 0) {
                    $el = $form;
                    return false;
                }
            });
        }

        if (!$el) {
            throw Error('No $el found.');
        }

        this.$el = $el;
        this.options = $.extend({
            checkInterval: 5000,
            onStateChange: $.noop,
            onProgressed: (function () {
                this.submitToProceed();
            }).bind(this),
        }, options || {});

        this.current = $el.prop('id').replace('titan-', '');
        this.vars = {};
        this.intervalId = null;
        this.currentState = [];
    }

    /**
     * Initialize the interstitial.
     */
    WTitanLoginInterstitial.prototype.init = function () {
        for (var i = 0; i < VARS.length; i++) {
            this.vars[VARS[i]] = $('input[name="' + VARS[i] + '"]', this.$el).val();
        }

        this.intervalId = setInterval(this.checkIfProgressed.bind(this), this.options.checkInterval);
    };

    /**
     * Make an ajax request.
     *
     * @return {$.promise}
     */
    WTitanLoginInterstitial.prototype.ajax = function (data) {
        return wp.ajax.post(
            'titan-login-interstitial-ajax',
            $.extend({}, this.vars, data),
        );
    };

    /**
     * Fetch the latest interstitial state.
     *
     * @return {$.promise}
     */
    WTitanLoginInterstitial.prototype.fetchState = function () {
        return wp.ajax.post(
            'titan-login-interstitial-ajax',
            $.extend({titan_interstitial_get_state: true}, this.vars),
        );
    };

    WTitanLoginInterstitial.prototype.checkIfProgressed = function () {
        this.fetchState().then((function (response) {
            if (response.logged_in || response.current !== this.current) {
                this.options.onProgressed(response);
            } else if (JSON.stringify(response.state) !== JSON.stringify(this.currentState)) {
                this.options.onStateChange(response.state, this.currentState);
                this.currentState = response.state;
            }
        }).bind(this)).fail((function (response) {
            console.error(response);
            clearInterval(this.intervalId);
        }).bind(this));
    };

    WTitanLoginInterstitial.prototype.submitToProceed = function () {

        var $form = $('<form />')
            .prop('method', 'post')
            .prop('action', this.$el.attr('action'))
            .css({display: 'none'});

        $form.append(
            $('<input />')
                .prop('type', 'hidden')
                .prop('name', 'action')
                .prop('value', 'titan-' + this.current),
        );

        for (var i = 0; i < VARS.length; i++) {
            $form.append(
                $('<input />')
                    .prop('type', 'hidden')
                    .prop('name', VARS[i])
                    .prop('value', this.vars[VARS[i]]),
            );
        }

        $form.appendTo(document.body);
        $form.submit();
    };

    WTitanLoginInterstitial.prototype.setOnProgressed = function (callback) {
        this.options.onProgressed = callback;
    };

    WTitanLoginInterstitial.prototype.setOnStateChange = function (callback) {
        this.options.onStateChange = callback;
    };

    window.WTitanLoginInterstitial = WTitanLoginInterstitial;
})(jQuery);
