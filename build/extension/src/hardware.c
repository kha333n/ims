/**
 * hardware.c -- Hardware fingerprinting
 *
 * TODO: Implement all functions. This is the skeleton.
 */

#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include "php_ims.h"
#include "hardware.h"
#include "crypto.h"

#include <windows.h>
#include <iphlpapi.h>
#include <stdio.h>
#include <string.h>

/* ── Helper: Read Windows Machine GUID from registry ────────── */

static int read_machine_guid(char *out, size_t out_size)
{
    HKEY hKey;
    DWORD type, size = (DWORD)out_size;

    if (RegOpenKeyExA(HKEY_LOCAL_MACHINE,
            "SOFTWARE\\Microsoft\\Cryptography",
            0, KEY_READ | KEY_WOW64_64KEY, &hKey) != ERROR_SUCCESS) {
        return -1;
    }

    LONG result = RegQueryValueExA(hKey, "MachineGuid", NULL, &type,
                                    (LPBYTE)out, &size);
    RegCloseKey(hKey);

    return (result == ERROR_SUCCESS && type == REG_SZ) ? 0 : -1;
}

/* ── Helper: Get primary MAC address ────────────────────────── */

static int read_mac_address(char *out, size_t out_size)
{
    /* TODO:
     * Use GetAdaptersInfo() to find primary network adapter
     * Format MAC as hex string
     */
    out[0] = '\0';
    return -1;
}

/* ── Helper: Get disk serial number ─────────────────────────── */

static int read_disk_serial(char *out, size_t out_size)
{
    /* TODO:
     * Use WMI (Win32_DiskDrive) or GetVolumeInformation()
     * to get the primary disk serial number
     */
    out[0] = '\0';
    return -1;
}

/* ── Helper: Get CPU ID ─────────────────────────────────────── */

static int read_cpu_id(char *out, size_t out_size)
{
    /* TODO:
     * Use CPUID instruction or WMI (Win32_Processor)
     * to get processor identification string
     */
    out[0] = '\0';
    return -1;
}

/* ── Main: Generate fingerprint ─────────────────────────────── */

int ims_hardware_generate(char *out_hex, size_t buf_size)
{
    if (buf_size < 129) return FAILURE;

    char machine_guid[256] = {0};
    char mac_address[64] = {0};
    char disk_serial[256] = {0};
    char cpu_id[256] = {0};

    /* Collect all available identifiers */
    read_machine_guid(machine_guid, sizeof(machine_guid));
    read_mac_address(mac_address, sizeof(mac_address));
    read_disk_serial(disk_serial, sizeof(disk_serial));
    read_cpu_id(cpu_id, sizeof(cpu_id));

    /* Concatenate all identifiers */
    char combined[1024];
    snprintf(combined, sizeof(combined), "%s|%s|%s|%s",
             machine_guid, mac_address, disk_serial, cpu_id);

    /* SHA-256 hash the combined string */
    /* TODO: Use ims_crypto_sha256 (need a string-based version, not file-based) */

    /* For now, placeholder */
    memset(out_hex, '0', 128);
    out_hex[128] = '\0';

    return SUCCESS;
}

int ims_hardware_verify(const char *stored_id)
{
    char current[129];
    if (ims_hardware_generate(current, sizeof(current)) != SUCCESS) {
        return 0;
    }
    return (strcmp(current, stored_id) == 0) ? 1 : 0;
}
