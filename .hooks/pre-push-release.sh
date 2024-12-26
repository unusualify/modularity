#!/bin/bash

# Get current branch name
BRANCH_NAME=$(git symbolic-ref --short HEAD)

# Only run on release/* branches
if [[ $BRANCH_NAME =~ ^release/ ]]; then
    echo "📦 Running release branch pre-push hooks..."
    
    # Run Pint
    echo "🎨 Running Laravel Pint..."
    vendor/bin/pint
    
    # Check for Vue changes and build if necessary
    if git diff --name-only HEAD origin/$BRANCH_NAME | grep -q "^vue/\(src\|public\)/"; then
        echo "🏗️ Building Vue..."
        cd vue && npm run build
        cd ..
        
        # Stage vue/dist if it exists and has changes
        if [ -d "vue/dist" ] && git status vue/dist --porcelain | grep .; then
            git add vue/dist
            git commit -m "build(vue): update build artifacts"
        fi
    fi
    
    # Stage and commit any Pint changes
    if git status --porcelain | grep .; then
        git add .
        git commit -m "style(laravel): lint coding styles"
    fi
fi
