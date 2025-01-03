#!/bin/bash

# Create the directory if it doesn't exist
mkdir -p wp-health-diagnosis-plugin
mkdir -p wp-health-diagnosis-plugin/hd-plugin
# Copy files and folders to hd-plugin directory
cp health-diagnosis-plugin.php wp-health-diagnosis-plugin/



cp data.json wp-health-diagnosis-plugin/

cp hd-plugin/admin.php wp-health-diagnosis-plugin/hd-plugin
cp hd-plugin/client.php wp-health-diagnosis-plugin/hd-plugin
cp hd-plugin/app_vue.php wp-health-diagnosis-plugin/hd-plugin

mkdir -p wp-health-diagnosis-plugin/hd-plugin/frontend
mkdir -p wp-health-diagnosis-plugin/hd-plugin/frontend/dist
cp -r hd-plugin/frontend/dist/. wp-health-diagnosis-plugin/hd-plugin/frontend/dist


zip -r wp-health-diagnosis-plugin.zip wp-health-diagnosis-plugin

echo "Files and folders copied successfully to hd-plugin directory."
