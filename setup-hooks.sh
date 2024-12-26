#!/bin/bash
cp .hooks/pre-push-release.sh .git/hooks/
cp .hooks/pre-push .git/hooks/
chmod +x .git/hooks/pre-push-release.sh
chmod +x .git/hooks/pre-push
