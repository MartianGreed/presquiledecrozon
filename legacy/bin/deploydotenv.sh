#/bin/sh

echo GOOGLE_MAPS_API_KEY=$(echo $PLATFORM_VARIABLES | base64 --decode | jq .SCALEWAY_ENDPOINT) >> .env.prod
echo GOOGLE_MAPS_API_URL=$(echo $PLATFORM_VARIABLES | base64 --decode | jq .SCALEWAY_KEY) >> .env.prod
echo BUNNYCDN_BUCKET=$(echo $PLATFORM_VARIABLES | base64 --decode | jq .SCALEWAY_SECRET) >> .env.prod
echo BUNNYCDN_API_KEY=$(echo $PLATFORM_VARIABLES | base64 --decode | jq .SCALEWAY_BUCKET) >> .env.prod
echo BUNNYCDN_REGION=$(echo $PLATFORM_VARIABLES | base64 --decode | jq .BUNNYCDN_BUCKET) >> .env.prod
echo CDN_URL=$(echo $PLATFORM_VARIABLES | base64 --decode | jq .BUNNYCDN_API_KEY) >> .env.prod
echo STRIPE_SECRET_KEY=$(echo $PLATFORM_VARIABLES | base64 --decode | jq .BUNNYCDN_API_KEY) >> .env.prod
echo STRIPE_PUBLIC_KEY=$(echo $PLATFORM_VARIABLES | base64 --decode | jq .BUNNYCDN_API_KEY) >> .env.prod