/**
 * hardware.h -- Hardware fingerprinting
 *
 * Generates a stable, unique identifier for the current machine
 * by combining CPU, disk, network, and Windows machine GUID data.
 */

#ifndef IMS_HARDWARE_H
#define IMS_HARDWARE_H

#include "php_ims.h"

/**
 * Generate hardware fingerprint.
 *
 * Combines:
 * - CPU ID (CPUID instruction or WMI Win32_Processor)
 * - Primary disk serial number (WMI Win32_DiskDrive)
 * - Primary MAC address (GetAdaptersInfo)
 * - Windows Machine GUID (HKLM\SOFTWARE\Microsoft\Cryptography)
 *
 * Result is SHA-256 hash of concatenated values: stable across reboots,
 * unique per physical machine.
 *
 * @param out_hex  Buffer for hex string (129 bytes min: 128 + null)
 * @param buf_size Size of output buffer
 * @return SUCCESS or FAILURE
 */
int ims_hardware_generate(char *out_hex, size_t buf_size);

/**
 * Verify a hardware ID matches current machine.
 *
 * @param stored_id  Previously stored hardware ID
 * @return 1 if match, 0 if mismatch
 */
int ims_hardware_verify(const char *stored_id);

#endif /* IMS_HARDWARE_H */
