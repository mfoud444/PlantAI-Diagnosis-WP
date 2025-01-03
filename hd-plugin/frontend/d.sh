#!/bin/bash
pnpm run build
cp src/views/home/Steps/model.json /home/mohammed/Desktop/htdocs/htdocs/wordpress65/wp-content/plugins/wp-health-diagnosis-plugin/hd-plugin/frontend/dist/assets
cp src/views/home/Steps/weights.bin /home/mohammed/Desktop/htdocs/htdocs/wordpress65/wp-content/plugins/wp-health-diagnosis-plugin/hd-plugin/frontend/dist/assets
