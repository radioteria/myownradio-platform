#!/usr/bin/env sh

xvfb-run -a --server-args="-nolisten tcp -screen 0 1280x720x30+32" cargo run
