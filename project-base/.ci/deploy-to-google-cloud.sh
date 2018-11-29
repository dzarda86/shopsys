#!/bin/sh -ex

# Login to Docker Hub for pushing images into register
echo ${DOCKER_PASSWORD} | docker login --username ${DOCKER_USERNAME} --password-stdin

# Create unique docker image tag with commit hash
DOCKER_IMAGE_TAG=production-commit-${GIT_COMMIT}

## Docker image for application php-fpm container
docker image pull ${DOCKER_USERNAME}/php-fpm:${DOCKER_IMAGE_TAG} || (
    echo "Image not found (see warning above), building it instead..." &&
    docker image build \
        --tag ${DOCKER_USERNAME}/php-fpm:${DOCKER_IMAGE_TAG} \
        --target production \
        -f docker/php-fpm/Dockerfile \
        . &&
    docker image push ${DOCKER_USERNAME}/php-fpm:${DOCKER_IMAGE_TAG}
)

# Create real parameters files to be modified and applied to the cluster as configmaps
cp app/config/domains_urls.yml.dist app/config/domains_urls.yml
cp app/config/parameters.yml.dist app/config/parameters.yml

# Replace docker images for php-fpm of application and microservices
yq write --inplace kubernetes/deployments/webserver-php-fpm.yml spec.template.spec.containers[0].image ${DOCKER_USERNAME}/php-fpm:${DOCKER_IMAGE_TAG}
yq write --inplace kubernetes/deployments/webserver-php-fpm.yml spec.template.spec.initContainers[0].image ${DOCKER_USERNAME}/php-fpm:${DOCKER_IMAGE_TAG}
yq write --inplace kubernetes/deployments/webserver-php-fpm.yml spec.template.spec.initContainers[1].image ${DOCKER_USERNAME}/php-fpm:${DOCKER_IMAGE_TAG}

# Set domain name into ingress controller so ingress can listen on domain name
yq write --inplace kubernetes/ingress.yml spec.rules[0].host ${FIRST_DOMAIN_HOSTNAME}
yq write --inplace kubernetes/ingress.yml spec.rules[1].host ${SECOND_DOMAIN_HOSTNAME}

# Set domain into webserver hostnames
yq write --inplace kubernetes/deployments/webserver-php-fpm.yml spec.template.spec.hostAliases[0].hostnames[+] ${FIRST_DOMAIN_HOSTNAME}
yq write --inplace kubernetes/deployments/webserver-php-fpm.yml spec.template.spec.hostAliases[0].hostnames[+] ${SECOND_DOMAIN_HOSTNAME}

# Set domain urls
yq write --inplace project-base/app/config/domains_urls.yml domains_urls[0].url http://${FIRST_DOMAIN_HOSTNAME}
yq write --inplace project-base/app/config/domains_urls.yml domains_urls[1].url http://${SECOND_DOMAIN_HOSTNAME}

# Add a mask for trusted proxies so that load balanced traffic is trusted and headers from outside of the network are not lost
yq write --inplace app/config/parameters.yml parameters.trusted_proxies[+] 10.0.0.0/8

cd /tmp/infrastructure/google-cloud

# Authenticate yourself with service.account.json file.
export GOOGLE_APPLICATION_CREDENTIALS=/tmp/infrastructure/google-cloud/service-account.json
gcloud config set container/use_application_default_credentials true

# Activate Service Account
gcloud auth activate-service-account --key-file=service-account.json

# Set project by ID into gcloud config
gcloud config set project ${PROJECT_ID}

gcloud projects add-iam-policy-binding ${PROJECT_ID} \
    --member serviceAccount:${SERVICE_ACCOUNT_LOGIN}@${PROJECT_ID}.iam.gserviceaccount.com \
    --role roles/owner

# Initialize terraform, installs all the providers
terraform init

# Set .kube/config from tfstate if the output variable is defined
# If the cluster resource does not exist yet, the .kube/config will be created during its provisioning
KUBE_CONFIG=$(terraform output production-k8s-cluster-kube-config 2> /dev/null || true)
if [ -n "$KUBE_CONFIG" ]; then
  echo "$KUBE_CONFIG" > ~/.kube/config
fi

# Apply changes in infrastructure
TF_VAR_GOOGLE_CLOUD_ACCOUNT_ID=${SERVICE_ACCOUNT_LOGIN} TF_VAR_GOOGLE_CLOUD_PROJECT_ID=${PROJECT_ID} terraform apply --auto-approve

# Jump to kustomize folder to choose which overlay will be built
cd /tmp/kubernetes/kustomize

kustomize build overlays/production | kubectl apply -f -
