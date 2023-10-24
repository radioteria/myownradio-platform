#!/usr/bin/env sh

pulseaudio --disable-shm=true --realtime=true --exit-idle-time=-1 & PULSE_PID=$!
Xvfb :0 -screen 0 1280x720x30+32 & XVFB_PID=$!

cargo run

wait $PULSE_PID $XVFB_PID
