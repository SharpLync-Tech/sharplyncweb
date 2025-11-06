# ============================================
# SharpLync Web Deployment Workflow
# ============================================
# Builds and deploys the Laravel/PHP SharpLync web app
# to Azure Web App "sharplyncweb"
# ============================================

name: Build and deploy PHP app to Azure Web App - sharplyncweb

on:
  push:
    branches:
      - main
  workflow_dispatch:

jobs:
  build:
    runs-on: ubuntu-latest
    permissions:
      contents: read

    steps:
      # ✅ 1. Checkout repo
      - name: Checkout repository
        uses: actions/checkout@v4

      # ✅ 2. Setup PHP environment
      - name: Set up PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          extensions: mbstring, bcmath, intl, mysql, dom, curl, gd, xml
          tools: composer

      # ✅ 3. Validate composer.json exists
      - name: Check if composer.json exists
        id: check_files
        uses: andstor/file-existence-action@v2
        with:
          files: 'composer.json'

      # ✅ 4. Install dependencies (includes microsoft/microsoft-graph)
      - name: Run composer install if composer.json exists
        if: steps.check_files.outputs.files_exists == 'true'
        run: |
          echo "📦 Installing PHP dependencies..."
          composer validate --no-check-publish
          composer install --prefer-dist --no-progress --no-interaction --no-dev
          php artisan key:generate || true
          php artisan config:cache || true
          php artisan route:cache || true
          php artisan view:cache || true

      # ✅ 5. Upload artifact for deploy
      - name: Upload artifact for deployment
        uses: actions/upload-artifact@v4
        with:
          name: php-app
          path: .

  deploy:
    runs-on: ubuntu-latest
    needs: build
    permissions:
      id-token: write
      contents: read

    steps:
      # ✅ 6. Download artifact
      - name: Download artifact from build job
        uses: actions/download-artifact@v4
        with:
          name: php-app

      # ✅ 7. Azure login
      - name: Login to Azure
        uses: azure/login@v2
        with:
          creds: ${{ secrets.AZURE_CREDENTIALS }}

      # ✅ 8. Deploy to Azure Web App
      - name: Deploy to Azure Web App
        uses: azure/webapps-deploy@v3
        with:
          app-name: 'sharplyncweb'
          slot-name: 'Production'
          package: .