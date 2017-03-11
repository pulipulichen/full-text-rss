#!/usr/bin/env bash

BASEDIR=$(dirname "$0")
cd "$BASEDIR"
"$BASEDIR"/phantomjs "$BASEDIR"/phantomjs-exec.js "$1" "$2" > /dev/null
cat  "$BASEDIR"/phantomjs_cache/phantomjs_output.html