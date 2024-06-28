<!DOCTYPE html>
<html>

<head>
    <title>Pusher Test</title>
    <script src="https://js.pusher.com/7.0/pusher.min.js"></script>
    <script>
        Pusher.logToConsole = true;

        var pusher = new Pusher('77642123cb41e7c8aa66', {
            cluster: 'ap1',
            forceTLS: true
        });

        var channel = pusher.subscribe('cache-channel');
        channel.bind('CacheUpdated', function (data) {
            console.log('Received:', data);
        });

        // Error handling for WebSocket connection
        pusher.connection.bind('error', function (err) {
            if (err.error.data.code === 4004) {
                console.log('Over limit!');
            } else {
                console.error('WebSocket error:', err);
            }
        });
    </script>
</head>

<body>
    <h1>Pusher Test</h1>
    <p>Open your browser console to see Pusher logs.</p>
    <p>Pusher Key: {{ config('broadcasting.connections.pusher.key') }}</p>
    <p>Pusher Cluster: {{ config('broadcasting.connections.pusher.options.cluster') }}</p>
    <p>Pusher Host: {{ config('broadcasting.connections.pusher.options.host') }}</p>
    <p>Pusher Port: {{ config('broadcasting.connections.pusher.options.port') }}</p>
</body>

</html>