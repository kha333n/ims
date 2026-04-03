/**
 * network.c -- License server communication
 *
 * TODO: Implement all functions. This is the skeleton.
 */

#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include "php_ims.h"
#include "network.h"
#include "crypto.h"
#include "server_pubkey.h"

#include <curl/curl.h>
#include <string.h>
#include <stdlib.h>

/* ── Response buffer for libcurl ────────────────────────────── */

typedef struct {
    char *data;
    size_t size;
} response_buffer_t;

static size_t write_callback(void *contents, size_t size, size_t nmemb, void *userp)
{
    size_t realsize = size * nmemb;
    response_buffer_t *buf = (response_buffer_t *)userp;

    char *ptr = realloc(buf->data, buf->size + realsize + 1);
    if (!ptr) return 0;

    buf->data = ptr;
    memcpy(&(buf->data[buf->size]), contents, realsize);
    buf->size += realsize;
    buf->data[buf->size] = '\0';

    return realsize;
}

/* ── Init / Shutdown ────────────────────────────────────────── */

int ims_network_init(void)
{
    if (curl_global_init(CURL_GLOBAL_DEFAULT) != CURLE_OK) {
        return FAILURE;
    }
    return SUCCESS;
}

void ims_network_shutdown(void)
{
    curl_global_cleanup();
}

/* ── Helper: POST JSON to license server ────────────────────── */

static int post_json(const char *endpoint, const char *json_body,
                     response_buffer_t *response)
{
    CURL *curl = curl_easy_init();
    if (!curl) return -1;

    char url[512];
    snprintf(url, sizeof(url), "%s%s", IMS_LICENSE_SERVER_URL, endpoint);

    struct curl_slist *headers = NULL;
    headers = curl_slist_append(headers, "Content-Type: application/json");
    headers = curl_slist_append(headers, "Accept: application/json");

    response->data = malloc(1);
    response->size = 0;

    curl_easy_setopt(curl, CURLOPT_URL, url);
    curl_easy_setopt(curl, CURLOPT_POSTFIELDS, json_body);
    curl_easy_setopt(curl, CURLOPT_HTTPHEADER, headers);
    curl_easy_setopt(curl, CURLOPT_WRITEFUNCTION, write_callback);
    curl_easy_setopt(curl, CURLOPT_WRITEDATA, (void *)response);
    curl_easy_setopt(curl, CURLOPT_TIMEOUT, 15L);
    curl_easy_setopt(curl, CURLOPT_CONNECTTIMEOUT, 10L);

    CURLcode res = curl_easy_perform(curl);

    curl_slist_free_all(headers);
    curl_easy_cleanup(curl);

    return (res == CURLE_OK) ? 0 : -1;
}

/* ── Activate ───────────────────────────────────────────────── */

int ims_network_activate(
    const char *key,
    const char *hardware_id,
    char *out_expires,
    char *out_customer,
    char *out_message)
{
    /* TODO:
     * 1. Generate nonce (random 32 bytes hex)
     * 2. Build JSON: {key, hardware_id, nonce, timestamp}
     * 3. POST to /api/v1/activate
     * 4. Parse response JSON
     * 5. Verify Ed25519 signature using IMS_SERVER_PUBLIC_KEY
     * 6. Verify nonce matches
     * 7. Verify timestamp freshness (< 300 seconds)
     * 8. Extract expires_at, customer_name into out params
     * 9. Return 1 on success, 0 on failure
     */
    snprintf(out_message, 512, "Network activation not yet implemented");
    return 0;
}

/* ── Deactivate ─────────────────────────────────────────────── */

int ims_network_deactivate(
    const char *key,
    const char *hardware_id,
    char *out_message)
{
    /* TODO: Similar to activate but POST to /api/v1/deactivate */
    snprintf(out_message, 512, "Network deactivation not yet implemented");
    return 0;
}

/* ── Validate ───────────────────────────────────────────────── */

int ims_network_validate(
    const char *key,
    const char *hardware_id,
    char *out_expires)
{
    /* TODO:
     * 1. POST to /api/v1/validate with {key, hardware_id, nonce}
     * 2. Verify Ed25519 signature
     * 3. If valid: update out_expires, return 1
     * 4. If invalid/unreachable: return 0
     */
    return 0;
}
