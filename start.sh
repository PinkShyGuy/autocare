#!/bin/bash

# Debug environment
echo "=== Environment Debug ==="
echo "PORT: $PORT"
echo "All environment variables with PORT:"
env | grep -i port || echo "No PORT variables found"

# Set port with multiple fallbacks
if [ -n "$PORT" ]; then
    SERVER_PORT=$PORT
    echo "Using PORT from environment: $SERVER_PORT"
elif [ -n "$RAILWAY_STATIC_PORT" ]; then
    SERVER_PORT=$RAILWAY_STATIC_PORT
    echo "Using RAILWAY_STATIC_PORT: $SERVER_PORT"
else
    SERVER_PORT=8080
    echo "Using default port: $SERVER_PORT"
fi

echo "=== Starting PHP Server ==="
echo "Binding to: 0.0.0.0:$SERVER_PORT"
echo "Document root: $(pwd)"
echo "Available files:"
ls -la

# Start server with more verbose output
echo "Executing: php -S 0.0.0.0:$SERVER_PORT -t ."
exec php -d display_errors=1 \
     -d log_errors=1 \
     -d error_log=/dev/stderr \
     -d auto_prepend_file= \
     -d auto_append_file= \
     -S 0.0.0.0:$SERVER_PORT \
     -t . 2>&1