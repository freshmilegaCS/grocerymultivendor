<script src="https://www.gstatic.com/firebasejs/8.6.1/firebase-app.js"></script>
<script src="https://www.gstatic.com/firebasejs/8.6.1/firebase-messaging.js"></script>

<script>
    // Your Firebase config object
    
    var firebaseConfig = <?php echo $settings['fcm_credentials']; ?> ;

    // Initialize Firebase
    firebase.initializeApp(firebaseConfig);
    const messaging = firebase.messaging();

    // Request permission and get token
    messaging.requestPermission()
        .then(function() {
            console.log('Notification permission granted.');
            return messaging.getToken();
        })
        .then(function(token) {
            console.log('FCM Token:', token);
            $.ajax({
                url: "/seller/notification/token/update",
                type: "POST",
                data: {
                    token
                },
                dataType: "json",
                success: function(response) {},
            });
        })
        .catch(function(err) {
            console.log('Unable to get permission to notify.', err);
        });

    // Handle incoming messages
    messaging.onMessage((payload) => {
        console.log('Message received. ', payload);
        // Customize notification here
        const notificationTitle = payload.notification.title;
        const notificationOptions = {
            body: payload.notification.body,
            icon: payload.notification.icon
        };

        if (Notification.permission === 'granted') {
            new Notification(notificationTitle, notificationOptions);
        }
    });
</script>