resource "google_storage_bucket" "file-store" {
  name     = "shopsys-file-store-bucket"
  location = "EU"
}

data "google_service_account" "gcs-service-account" {
  account_id = "${var.GOOGLE_CLOUD_ACCOUNT_ID}"
}

resource "google_service_account_key" "gcs-service-account" {
  service_account_id = "${data.google_service_account.gcs-service-account.name}"
}

resource "kubernetes_namespace" "shopsys-production" {
  depends_on = ["google_container_cluster.production-k8s-cluster"]

  metadata {
    name = "shopsys-production"
  }

  provisioner "local-exec" {
    command = "kubectl config set-context $(kubectl config current-context) --namespace=shopsys-production"
  }
}

resource "kubernetes_secret" "gcs-service-account" {
  metadata {
    name      = "gcs-service-account"
    namespace = "${kubernetes_namespace.shopsys-production.id}"
  }

  data {
    service-account.json = "${base64decode(google_service_account_key.gcs-service-account.private_key)}"
  }
}

output "gcs-service-account-json-key" {
  value     = "${base64decode(google_service_account_key.gcs-service-account.private_key)}"
  sensitive = true
}
