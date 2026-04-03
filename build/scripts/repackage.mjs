/**
 * Repackage the NSIS installer from the patched win-unpacked directory.
 *
 * This imports the NativePHP electron-builder config (ES module)
 * and runs electron-builder with the correct settings.
 *
 * Usage: node build/scripts/repackage.mjs
 */

import { join, resolve } from 'path';
import { pathToFileURL } from 'url';

const projectRoot = resolve(join(import.meta.dirname, '../../'));
const distDir = join(projectRoot, 'dist');
const jsDir = join(projectRoot, 'vendor/nativephp/electron/resources/js');

const { build } = await import(pathToFileURL(join(jsDir, 'node_modules/electron-builder/out/index.js')).href);

// Set env vars that electron-builder.js config needs
process.env.NATIVEPHP_APP_ID = 'com.techmiddle.ims';
process.env.NATIVEPHP_APP_NAME = 'Installment Management System';
process.env.NATIVEPHP_APP_FILENAME = 'installment-management-system';
process.env.NATIVEPHP_APP_VERSION = '1.0.0';
process.env.NATIVEPHP_APP_AUTHOR = 'Techmiddle Technologies';
process.env.NATIVEPHP_APP_COPYRIGHT = 'Copyright 2026 Techmiddle Technologies';
process.env.NATIVEPHP_BUILDING = 'true';
process.env.NATIVEPHP_UPDATER_ENABLED = 'false';
process.env.APP_PATH = projectRoot;
process.env.NATIVEPHP_DEEPLINK_SCHEME = 'ims';

// Import the NativePHP electron-builder config
const configModule = await import(pathToFileURL(join(jsDir, 'electron-builder.js')).href);
const config = configModule.default;

// Override directories
config.directories = {
    output: distDir,
    buildResources: join(jsDir, 'build'),
};

console.log('Repackaging installer from patched win-unpacked...');
console.log('Config app ID:', config.appId);
console.log('Dist dir:', distDir);

const { Platform } = await import(pathToFileURL(join(jsDir, 'node_modules/electron-builder/out/index.js')).href);

try {
    await build({
        targets: Platform.WINDOWS.createTarget('nsis', 8), // 8 = Arch.x64
        config,
        prepackaged: join(distDir, 'win-unpacked'),
        projectDir: jsDir,
    });
    console.log('\nInstaller created successfully!');
} catch (err) {
    console.error('Build failed:', err.message);
    process.exit(1);
}
