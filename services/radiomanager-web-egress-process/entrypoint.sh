#!/usr/bin/env bash

APP="/radiomanager-web-egress-process"

Xvfb :0 -screen 0 1280x720x30+32 & XVFB_PID=$!
pulseaudio --disable-shm=true --realtime=true --exit-idle-time=-1 & PULSE_PID=$!

trap 'pkill -INT -f "^$APP"; wait $PID' INT
trap 'pkill -TERM -f "^$APP"; wait $PID' TERM
trap 'pkill -USR1 -f "^$APP"; wait $PID' USR1

$APP & PID=$!

wait $PID $XVFB_PID $PULSE_PID
