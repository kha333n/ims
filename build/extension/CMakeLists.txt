cmake_minimum_required(VERSION 3.20)
project(php_ims C)

# ── PHP Configuration ──────────────────────────────────────────
# Set PHP_INCLUDE_DIR to the PHP dev headers directory
# This should match the PHP version used by NativePHP
if(NOT DEFINED PHP_INCLUDE_DIR)
    message(FATAL_ERROR "PHP_INCLUDE_DIR must be set to the PHP development headers directory")
endif()

# ── Find Dependencies ──────────────────────────────────────────
# libcurl (static)
if(NOT DEFINED CURL_ROOT)
    set(CURL_ROOT "${CMAKE_SOURCE_DIR}/deps/curl")
endif()

# libsodium (static)
if(NOT DEFINED SODIUM_ROOT)
    set(SODIUM_ROOT "${CMAKE_SOURCE_DIR}/deps/sodium")
endif()

# ── Source Files ───────────────────────────────────────────────
set(SOURCES
    src/php_ims.c
    src/license.c
    src/integrity.c
    src/crypto.c
    src/network.c
    src/hardware.c
    src/functions.c
)

set(HEADERS
    src/php_ims.h
    src/license.h
    src/integrity.h
    src/integrity_hashes.h
    src/crypto.h
    src/network.h
    src/hardware.h
    src/functions.h
    src/server_pubkey.h
)

# ── Build Shared Library (.dll) ────────────────────────────────
add_library(php_ims SHARED ${SOURCES} ${HEADERS})

# PHP extension naming: php_ims.dll (no lib prefix on Windows)
set_target_properties(php_ims PROPERTIES
    PREFIX ""
    OUTPUT_NAME "php_ims"
)

# ── Include Directories ────────────────────────────────────────
target_include_directories(php_ims PRIVATE
    ${PHP_INCLUDE_DIR}
    ${PHP_INCLUDE_DIR}/main
    ${PHP_INCLUDE_DIR}/TSRM
    ${PHP_INCLUDE_DIR}/Zend
    ${PHP_INCLUDE_DIR}/ext
    ${CURL_ROOT}/include
    ${SODIUM_ROOT}/include
    ${CMAKE_SOURCE_DIR}/src
)

# ── Link Libraries ─────────────────────────────────────────────
# Windows system libraries
target_link_libraries(php_ims PRIVATE
    # PHP
    ${PHP_INCLUDE_DIR}/../lib/php8ts.lib

    # libcurl (static)
    ${CURL_ROOT}/lib/libcurl_a.lib

    # libsodium (static)
    ${SODIUM_ROOT}/lib/sodium.lib

    # Windows APIs
    crypt32.lib     # DPAPI (CryptProtectData/CryptUnprotectData)
    advapi32.lib    # Registry access
    ws2_32.lib      # Winsock (for libcurl)
    wldap32.lib     # LDAP (for libcurl)
    normaliz.lib    # IDN (for libcurl)
    iphlpapi.lib    # Network adapter info (hardware fingerprint)
    ole32.lib       # COM (WMI queries for hardware info)
    oleaut32.lib    # COM automation
    wbemuuid.lib    # WMI UUIDs
)

# ── Compiler Definitions ──────────────────────────────────────
target_compile_definitions(php_ims PRIVATE
    PHP_WIN32
    ZEND_WIN32
    ZEND_DEBUG=0
    ZTS=1
    COMPILE_DL_IMS
    CURL_STATICLIB
    SODIUM_STATIC
    HAVE_IMS=1
    # Uncomment for dev builds with test license keys:
    # DEV_BUILD
)

# ── Compiler Flags ─────────────────────────────────────────────
if(MSVC)
    target_compile_options(php_ims PRIVATE
        /W3         # Warning level 3
        /O2         # Optimize for speed
        /GL         # Whole program optimization
        /GS         # Buffer security checks
    )
    target_link_options(php_ims PRIVATE
        /LTCG       # Link-time code generation
    )
endif()

# ── Install ────────────────────────────────────────────────────
install(TARGETS php_ims
    LIBRARY DESTINATION ext
    RUNTIME DESTINATION ext
)
