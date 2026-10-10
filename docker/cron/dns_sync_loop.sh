#!/bin/sh

interval=600
child_pid=

stop() {
    trap - INT TERM
    if [ -n "$child_pid" ]; then
        kill "$child_pid" 2>/dev/null || true
    fi
    exit 0
}

trap stop INT TERM

while :; do
    /usr/local/bin/sync_dns.sh &
    child_pid=$!
    wait "$child_pid"
    status=$?
    child_pid=

    if [ "$status" -ne 0 ]; then
        echo "[$(date)] DNS sync failed with status $status; retrying in ${interval}s" >&2
    fi

    sleep "$interval" &
    child_pid=$!
    wait "$child_pid"
    child_pid=
done
