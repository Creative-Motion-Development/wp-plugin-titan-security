/**
 * @var {String} wtitan
 */

(function ($) {
    firebase.initializeApp({
        apiKey: "AIzaSyA1cTRtYRpVmACeaqbdnHnruvoWEj_LLdM",
        authDomain: "titan-checker-dev.firebaseapp.com",
        databaseURL: "https://titan-checker-dev.firebaseio.com",
        projectId: "titan-checker-dev",
        storageBucket: "titan-checker-dev.appspot.com",
        messagingSenderId: "468240947431",
        appId: "1:468240947431:web:d77511f15ae3b6f433ea42",
        measurementId: "G-WR3G7792E4"
    });

    var storageTokenKey = 'firebase-messaging-token';
    var subscribe_bt = $('#subscribe');
    var unsubscribe_bt = $('#unsubscribe');

    if (
        'Notification' in window &&
        'serviceWorker' in navigator &&
        'localStorage' in window &&
        'fetch' in window &&
        'postMessage' in window
    ) {
        var messaging = firebase.messaging();

        // already granted
        if (Notification.permission === 'granted') {
            getToken();
        }

        subscribe_bt.on('click', function() {
            showNotice('Subscribing...', 'info', 1500);
            getToken();
        });

        unsubscribe_bt.on('click', function() {
            messaging.getToken()
                .then(function(currentToken) {
                    messaging.deleteToken(currentToken)
                        .then(function() {
                            console.log('Token deleted');
                            sendTokenToServer(undefined);
                            resetUI();
                        })
                });
        });

        navigator.serviceWorker
            .register(wtitan.path, {scope:wtitan.scope})
            .then(function() {
                console.log("ServiceWorker was registered");
            });

        messaging.onMessage(function(payload) {
            console.log('Message received', payload);

            Notification.requestPermission()
                .then(function(permission) {
                    if (permission === 'granted') {
                        navigator.serviceWorker.ready.then(function(registration) {
                            // Copy data object to get parameters in the click handler
                            payload.data.data = JSON.parse(JSON.stringify(payload.data));

                            registration.showNotification(payload.data.title, payload.data);
                        }).catch(function(error) {
                            // registration failed :(
                            showError('ServiceWorker registration failed', error);
                        });
                    }
                })
        });

        // Callback fired if Instance ID token is updated.
        messaging.onTokenRefresh(function() {
            messaging.getToken()
                .then(function(refreshedToken) {
                    console.log('Token refreshed');
                    // Send Instance ID token to app server.
                    sendTokenToServer(refreshedToken);
                })
                .catch(function(error) {
                    showError('Unable to retrieve refreshed token', error);
                });
        });

    } else {
        if (!('Notification' in window)) {
            showError('Notification not supported');
        } else if (!('serviceWorker' in navigator)) {
            showError('ServiceWorker not supported');
        } else if (!('localStorage' in window)) {
            showError('LocalStorage not supported');
        } else if (!('fetch' in window)) {
            showError('fetch not supported');
        } else if (!('postMessage' in window)) {
            showError('postMessage not supported');
        }

        if(!window.location.protocol.startsWith('https')) {
            showError('Need HTTPS');
        }

        console.warn('This browser does not support desktop notification.');
        console.log('Is HTTPS', window.location.protocol.startsWith('https'));
        console.log('Support Notification', 'Notification' in window);
        console.log('Support ServiceWorker', 'serviceWorker' in navigator);
        console.log('Support LocalStorage', 'localStorage' in window);
        console.log('Support fetch', 'fetch' in window);
        console.log('Support postMessage', 'postMessage' in window);

        subscribe_bt.attr('disabled', 'disabled');
    }

    /**
     * @param {String} currentToken
     */
    function sendTokenToServer (currentToken) {
        if(typeof currentToken === 'undefined') {
            window.localStorage.removeItem(storageTokenKey);
        }

        if(!isTokenSentToServer(currentToken)) {
            $.post(ajaxurl, {
                action: 'push_token',
                _wpnonce: wtitan.pushTokenNonce,
                token: currentToken
            }, function(response) {
                if(response.success) {
                    showNotice(response.data.message, 'success', 5000);
                } else {
                    showNotice(response.data.error_message, 'danger', 5000);
                }
            });
            setSentTokenToServer(currentToken);
        }
    }

    /**
     * @param {String} currentToken
     * @returns {boolean}
     */
    function isTokenSentToServer(currentToken) {
        return window.localStorage.getItem(storageTokenKey) === currentToken;
    }

    /**
     * @param {String} currentToken
     */
    function setSentTokenToServer(currentToken) {
        if (currentToken) {
            window.localStorage.setItem(storageTokenKey, currentToken);
        } else {
            window.localStorage.removeItem(storageTokenKey);
        }
    }

    function showError (error, error_data) {
        if (typeof error_data !== "undefined") {
            console.error(error, error_data);
        } else {
            console.error(error);
        }

        showNotice(error, 'danger', 0);
    }

    function getToken() {
        messaging.requestPermission()
            .then(function() {
                messaging.getToken()
                    .then(function(currentToken) {
                        if (currentToken) {
                            console.log("Token received: ", currentToken);
                            sendTokenToServer(currentToken);
                            subscribe_bt.hide();
                            unsubscribe_bt.show();
                        } else {
                            showError('No Instance ID token available. Request permission to generate one');
                            setSentTokenToServer(undefined);
                        }
                    })
                    .catch(function(error) {
                        showError('An error occurred while retrieving token', error);
                        setSentTokenToServer(undefined);
                    });
            })
            .catch(function(error) {
                showError('Unable to get permission to notify', error);
            });
    }

    function showNotice(message, type, timeout) {
        if(typeof type === 'undefined') {
            type = 'success';
        }

        if(typeof timeout === 'undefined') {
            timeout = 5000;
        }

        if(typeof $ === 'undefined' || typeof $.wbcr_factory_clearfy_000 === 'undefined') {
            return;
        }

        var noticeId = $.wbcr_factory_clearfy_000.app.showNotice(message, type);
        if(timeout > 0) {
            setTimeout(function() {
                $.wbcr_factory_clearfy_000.app.hideNotice(noticeId);
            }, timeout);
        }
    }

    function resetUI() {
        subscribe_bt.show();
        unsubscribe_bt.hide();
    }
})(jQuery);