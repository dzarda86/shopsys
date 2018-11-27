# Deploy your application to Google Cloud on your CI/CD
This tutorial describes how to setup your CI/CD to deploy your application into Google Cloud.

For this example we will be using Jenkins as our CI.

## Kubernetes Buildpack
To deploy project to Google Cloud, you need couple of technologies, to not bother users with installing these technologies, we created a [shopsys/kubernetes-buildpack](https://github.com/shopsys/kubernetes-buildpack) image, which can be used for deployment.


## Scripts
### Deploy to Kubernetes
Script used for building current state of and application into google cloud

[deploy-to-kubernetes.sh](/project-base/.ci/deploy-to-kubernetes.sh)

#### Environment variables
| Variable name          | Description
| -------------          |-------------
| DOCKER_USERNAME        | docker login
| DOCKER_PASSWORD        | docker password
| FIRST_DOMAIN_HOSTNAME  | domain url for first domain
| SECOND_DOMAIN_HOSTNAME | domain url for first domain
| SERVICE_ACCOUNT_LOGIN  | login to your google account
| PROJECT_ID             | project id of your google project


#### Mounts

##### Docker Socket
Docker socket is used to build and push image of php-fpm
```
-v /var/run/docker.sock:/var/run/docker.sock \
```

##### Terraform state
Terraform state mounted locally to be able to apply changes or destroy terraform infrastructure.

```
-v ~/google-cloud/.terraform/tfstate:/tmp/infrastructure/google-cloud/tfstate \
```

##### Google Account Service
Mount your service-account.json obtained from google-cloud

```
-v ~/google-cloud/service-account.json:/tmp/infrastructure/google-cloud/service-account.json \
```
#### Usage
Use Kubernetes buildpack with environment variable set:

```
docker run \
  -v $PWD:/tmp \
  -v /var/run/docker.sock:/var/run/docker.sock \
  -v ~/google-cloud/.terraform/tfstate:/tmp/infrastructure/google-cloud/tfstate \
  -v ~/google-cloud/service-account.json:/tmp/infrastructure/google-cloud/service-account.json \
  -e DOCKER_USERNAME \
  -e DOCKER_PASSWORD \
  -e GIT_COMMIT \
  -e FIRST_DOMAIN_HOSTNAME \
  -e SECOND_DOMAIN_HOSTNAME \
  -e PROJECT_ID \
  -e SERVICE_ACCOUNT_LOGIN \
  --rm \
  shopsys/kubernetes-buildpack:latest \
  .ci/deploy.sh
```

### Destroy Google Cloud Infrastructure
Script used for deleting infrastructure on google. Script does not need any variable just a mount of Terraform `tfstate` file.

[destroy-google-cloud-infrastructure.sh](/project-base/.ci/destroy-google-cloud-infrastructure.sh)