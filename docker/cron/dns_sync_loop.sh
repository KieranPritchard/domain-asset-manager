#!/bin/sh

# This script runs the sync_dns.sh script in a loop, with a specified interval between each execution.

# The script also handles termination signals to gracefully stop the loop and any running child processes.
interval=600
child_pid=

stop() {
    # Resets the trap to default behavior for INT and TERM signals
    trap - INT TERM
    # If a child process is running, it attempts to kill it
    if [ -n "$child_pid" ]; then
        kill "$child_pid" 2>/dev/null || true
    fi
    # Exits the script with a success status
    exit 0
}

# Sets up traps for INT and TERM signals to call the stop function
trap stop INT TERM

# The main loop of the script runs indefinitely, executing the sync_dns.sh script and waiting for it to finish.
while :; do
    # Executes the sync_dns.sh script in the background and captures its PID
    /usr/local/bin/sync_dns.sh &
    child_pid=$!
    wait "$child_pid"
    status=$?
    child_pid=

    # If the sync_dns.sh script exits with a non-zero status, it logs an error message and retries after the specified interval.
    if [ "$status" -ne 0 ]; then
        echo "[$(date)] DNS sync failed with status $status; retrying in ${interval}s" >&2
    fi

    # Waits for the specified interval before the next iteration of the loop, allowing for graceful termination during the sleep period.
    sleep "$interval" &
    child_pid=$!
    wait "$child_pid"
    child_pid=
done
