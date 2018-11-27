# Production on Google Cloud Platform using Kubernetes, Terraform and Kustomize

Shopsys Framework provides a way how to deploy your site on Google Cloud using Kubernetes manifests, Terraform configurations and Kustomize written into repository.

We created shell scripts that encapsulate functionality of Terraform and Kustomize on google-cloud. This documents only describes functionality of each technology.

If you want to know about the script usage, read [Deploy to Google Cloud from CI](deploy-to-google-cloud-from-ci.md).

## Google Cloud
Create a user on Google Cloud.

After creating user create project.

### Obtain Parameters and service-account.json

1. service-account.json

    Read more here https://cloud.google.com/iam/docs/creating-managing-service-account-keys

2. Project id

3. Your Login to Google Cloud

### Allow API
Allow API so you can control resources and providers using terraform.

Read more here:
https://cloud.google.com/apis/docs/enable-disable-apis

## Terraform
To be able to create infrastructure on Google Cloud, we need a tool that is able to communicate with google cloud API. For this purposes we decided to use Terraform.

[Terraform](https://www.terraform.io/) is tool that allows you to create, change and delete infrastructure on popular cloud providers and many other platforms.

We use it to declare database providers, clusters and network on Google Cloud using declarative configurations that are part of the repository.

### Applying infrastructure using Terraform
Terraform applies infrastructure based on configuration provided, Shopsys Framework got prepared configuration in [infrastructure/google-cloud](/project-base/infrastructure/google-cloud).


Terraform needs to be initialized, which installs all the providers.

```
terraform init
```

Each change needs to be applied. Terraform creates a tfstate file which is current state of infrastructure installed, that means that if you change something in infrastructure Terraform will not apply all changes again, but just compare current state with desire state and change only desired changes.

Apply infrastructure change:

```
terraform apply
```

To stop running infrastructure:

```
terraform destroy
```

Always keep in mind to have tfstate file available, if you lost this file and try to stop running infrastructure, nothing will happen because terraform will not know what to stop.

## Kustomize
Production environment is little bit different then one used on CI. For example, on google cloud we use postgres and redis provided by google cloud platform.

That means that we do not use always same manifests, with Kustomize you can divide your manifests into `variants`, for example CI, production etc. These variants are located in [kubernetes/kustomize/overlays](/project-base/kubernetes/kustomize). Each variant has `kustomization.yml` which can independently select own manifests using `resources` or generate config maps, create secrets etc.  

Final command for applying kubernetes using Kustomize would look something like this:

Select environment, in our case `production` and go to variant folder:

```
cd kubernetes/overlays/production
```

Build final manifest from variant:

```
kustomize build
```

This outputs a final yaml file into stream, you can use this output with kubectl like this:

```
kustomize build | kubectl apply -f -
```


## What next?

[Deploy your application on your CI](./deploy-to-google-cloud-from-ci.md)

[Domains and HTTPs](./domains.md)
