#!/bin/bash

# Set default port if not provided
if [ -z "$PORT" ]; then
    export PORT=8080
    echo "PORT not set, using default: $PORT"
else
    echo "Using PORT: $PORT"
fi

# Debug information
echo "Environment variables:"
env | grep -E "(PORT|MYSQL)" || echo "No MYSQL env vars found"

echo "Current directory contents:"
ls -la

echo "Starting PHP built-in server..."
echo "Listening on 0.0.0.0:$PORT"
echo "Document root: $(pwd)"

# Start PHP server
exec php -d display_errors=1 \
     -d log_errors=1 \
     -d error_log=/dev/stderr \
     -S 0.0.0.0:$PORT \
     -t .