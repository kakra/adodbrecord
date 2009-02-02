#!/bin/bash

for i in *.php; do php $i; done | fgrep "Test cases run"
