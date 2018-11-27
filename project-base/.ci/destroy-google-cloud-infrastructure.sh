#!/bin/sh -ex
# Jump to Kustomize folder to choose which overlay will be built
cd /tmp/kubernetes/kustomize

# Delete production manifests
#kustomize build overlays/production | kubectl delete -f -

# Jump to Google Cloud infrastructure folder
cd /tmp/infrastructure/google-cloud

# Apply changes in infrastructure
TF_VAR_GOOGLE_CLOUD_ACCOUNT_ID=${SERVICE_ACCOUNT_LOGIN} TF_VAR_GOOGLE_CLOUD_PROJECT_ID=${PROJECT_ID} terraform destroy