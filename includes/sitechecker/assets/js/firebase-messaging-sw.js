importScripts('https://www.gstatic.com/firebasejs/7.9.1/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/7.9.1/firebase-messaging.js');

firebase.initializeApp({
    apiKey: "AIzaSyAAkQhmoqd-emHHsK5CP79eVUcci7umGMs",
    authDomain: "titan-security.firebaseapp.com",
    databaseURL: "https://titan-security.firebaseio.com",
    projectId: "titan-security",
    storageBucket: "titan-security.appspot.com",
    messagingSenderId: "313719964045",
    appId: "1:313719964045:web:7bd9fa554204728914bdc4",
    measurementId: "G-G5LGKBYFE5"
});

const messaging = firebase.messaging();

// Customize notification handler
messaging.setBackgroundMessageHandler(function (payload) {
    console.log('Handling background message', payload);

    // Copy data object to get parameters in the click handler
    payload.data.data = JSON.parse(JSON.stringify(payload.data));

    return self.registration.showNotification(payload.data.title, payload.data);
});

self.addEventListener('notificationclick', function (event) {
    const target = event.notification.data.click_action || '/';
    event.notification.close();

    // This looks to see if the current is already open and focuses if it is
    event.waitUntil(clients.matchAll({
        type: 'window',
        includeUncontrolled: true
    }).then(function (clientList) {
        // clientList always is empty?!
        for (var i = 0; i < clientList.length; i++) {
            var client = clientList[i];
            if (client.url === target && 'focus' in client) {
                return client.focus();
            }
        }

        return clients.openWindow(target);
    }));
});
