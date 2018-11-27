# Domains in Google Cloud infrastructure.
Every change in domain needs to be done manually. To pass traffic through domain name you need to modify `ingress.yml` which serves as webserver and listens on domain names.

## Adding Domain
Let's say we want to listen on new domain name.

Open [ingress.yml](/project-base/kubernetes/ingress.yml) file and add new domain block into `spec -> rules`:

```
rules:
    -   host:
        http:
            paths:
            -   path: /
                backend:
                    serviceName: webserver-php-fpm
                    servicePort: 8080
```

Open [.ci/deploy.sh](/project-base/.ci/deploy.sh) file and set your new domain to ingress.yml and web server host name:

```
NEW_DOMAIN_NAME=${NEW_DOMAIN_NAME}
yq write --inplace kubernetes/ingress.yml spec.rules[${DOMAIN_INDEX}].host ${NEW_DOMAIN_NAME}
yq write --inplace kubernetes/deployments/webserver-php-fpm.yml spec.template.spec.hostAliases[0].hostnames[+] ${NEW_DOMAIN_NAME}
```

*Note*: ${DOMAIN_INDEX} is a index of domain in ingress.yml

## HTTPs

Allowing HTTPs is done in a three steps.

1. Create secret from certificates:

    ```
    // /kubernetes/kustomize/overlays/production/kustomization.yml

    secretGenerator:
      name: domain-${DOMAIN_ID}-ssl-certificate
      commands:
        tls.key: "cat /tmp/domain-ssl-certificates/${DOMAIN_ID}/tls.key || true"
        tls.crt: "cat /tmp/domain-ssl-certificates/${DOMAIN_ID}/tls.crt || true"
        ca.crt: "cat /tmp/domain-ssl-certificates/${DOMAIN_ID}/ca.crt || true"
    ```

    *Note*: Replace `${DOMAIN_ID}` by number of domain. For each domain should exist folder with certificates.

1. Register certificates in ingress.yml

    ```
    // /kubernetes/ingress.yml

    spec
        tls:
            hosts:
            - ${MY_DOMAIN_HOSTNAME}
            secretName: domain-${DOMAIN_ID}-ssl-certificates
    ```

1. Obtain your ssl certificates and mount them into Build pack:

    *Note:* For each domain there is one folder with certificate.

    ```
    docker run -it \
      -v ./:/tmp \
      -v ~/path/to/crts:/tmp/domain-ssl-certificates/1 \
      -v ~/path/to/crts-2:/tmp/domain-ssl-certificates/2 \
      shopsys/kubernetes-buildpack
    ```
