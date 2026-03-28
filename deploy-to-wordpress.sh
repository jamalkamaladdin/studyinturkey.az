#!/bin/bash
# Serverdə: /var/www/studyinturkey.az-git/ içində saxlayın.
# İstifadə: ./deploy-to-wordpress.sh  (əvvəl cd həmin qovluğa)
set -euo pipefail
REPO="$(cd "$(dirname "$0")" && pwd)"
WP="/var/www/studyinturkey.az"
cd "$REPO"
git pull origin main
for p in sit-multilang sit-developer sit-developer-application; do
	rm -rf "$WP/wp-content/plugins/$p"
	cp -a "$REPO/$p" "$WP/wp-content/plugins/"
done
rm -rf "$WP/wp-content/themes/sit-developer-theme"
cp -a "$REPO/sit-developer-theme" "$WP/wp-content/themes/"
chown -R www-data:www-data \
	"$WP/wp-content/plugins/sit-multilang" \
	"$WP/wp-content/plugins/sit-developer" \
	"$WP/wp-content/plugins/sit-developer-application" \
	"$WP/wp-content/themes/sit-developer-theme"
echo "Deployed to $WP/wp-content"
