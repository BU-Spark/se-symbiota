<p align="center">
    <a href="https://symbiota.org/" target="_blank">
        <picture>
		    <source width="500" media="(prefers-color-scheme: dark)" srcset="https://github.com/user-attachments/assets/94a3507e-675f-4fe8-8504-12a567f268e9">
   		    <source width="500" media="(prefers-color-scheme: light)" srcset="https://github.com/user-attachments/assets/a3897966-7240-4345-ae27-af095adfdde0">
            <img width="500px" src="https://github.com/user-attachments/assets/94a3507e-675f-4fe8-8504-12a567f268e9" />
		</picture>
    </a>
</p>

This fork of the Symbiota code is actively being developed by the Symbiota Support Hub (SSH, https://symbiota.org/about-us) development team at the University of Kansas Biodiversity Institute.
Even though SSH code developments are regularly pushed back to this repository, we recommend that you download/fork code directly from the
Symbiota/Symbiota repository (https://github.com/Symbiota/Symbiota) to ensure that you obtain the most recently code changes.

# Welcome to the Symbiota code repository

## ABOUT THIS SOFTWARE

The Symbiota Software Project is building a library of webtools to aid biologists in establishing specimen based virtual floras and faunas. This project developed from the realization that complex, information rich biodiversity portals are best built through collaborative efforts between software developers, biologist, wildlife managers, and citizen scientist. The central premise of this open source software project is that through a partnership between software engineers and the scientific community, higher quality and more publicly useful biodiversity portals can be built. An open source software framework allows the technicians to create the tools, thus freeing the biologist to concentrate their efforts on the curation of quality datasets. In this manner, we can create something far greater than a single entity is capable of doing on their own.

More information about this project can be accessed through [https://symbiota.org](https://symbiota.org).

For documentation and user guides please visit [Symbiota Docs](https://docs.symbiota.org/).

## ACKNOWLEDGEMENTS

Symbiota has been generously funded by the U.S. National Science Foundation. The Global Institute of Sustainability (GIOS) at Arizona State University has also been a major supporters of the Symbiota initiative since the very beginning. Arizona State University Vascular Plant and Lichen Herbarium have been intricately involved in the development from the start. Sky Island Alliance and the Arizona-Sonora Desert Museum have both been long-term participants in the development of this product.

## FEATURES

- Specimen Search Engine
  - Taxonomic Thesaurus for querying taxonomic synonyms
  - Google Map and Google Earth mapping capabilities
  - Dynamic species list generated from specimens records
- Flora/Fauna Management System
  - Static species list (local floras/faunas)
- Interactive Identification Keys
  - Key generation for are species list within system
  - Key generator based on a point locality
- Image Library

## LIMITATIONS

- Tested thoroughly on Linux and Windows operating systems
- Code should work with an PHP enabled web server, though central development and testing done using Apache HTTP Server
- Development and testing preformed using MariaDB. If you are using Oracle MySQL instead, please [report any issues](https://github.com/Symbiota/Symbiota/issues/new).

## INSTALLATION

Please read the [INSTALL.md](docs/INSTALL.md) file for installation instructions.

## UPDATES

Please read the [UPDATE.md](docs/UPDATE.md) file for instructions on how to update Symbiota.

## CONTRIBUTING

Please visit the [CONTRIBUTING.md](docs/CONTRIBUTING.md) page for guidance on contributing to the main Symbiota codebase.

# How to change file size of php in server OR Server Configuration for Large File Uploads

###(Shortcut: Just run `bash update_php.sh` in your terminal)

This guide provides a step-by-step process to increase PHP file upload limits and update the application logic to support large data imports (e.g., 175MB+ ZIP archives).

---

## **Step 1: Identify the Active Configuration**

Before making changes, verify which configuration file your server is actually using

1. Deploy an `info.php` file containing `<?php phpinfo(); ?>` to your server's root directory (`/var/www/html/symbiota`).
2. Navigate to `http://localhost:8080/info.php` in your browser.
3. Locate the **"Loaded Configuration File"** row to identify the active `php.ini` path.
   - **Your Server Path:** `/etc/php/8.1/apache2/php.ini`.

---

## **Step 2: Update Server Limits (php.ini)**

To support 512MB uploads, you must modify the system configuration. Open the `php.ini` file identified in Step 1 and update these directives:

````ini
; Maximum allowed size for uploaded files.
upload_max_filesize = 512M

; Maximum size of POST data that PHP will accept.
post_max_size = 512M

; Maximum amount of memory a script may consume.
memory_limit = 1024M

# Finalizing Upload Configuration: Steps 3 & 4

After identifying your `php.ini` path and updating the server limits, you must align the application's validation logic and restart the service for changes to take effect.

---

## **Step 3: Update Application Logic**
The application uses a JavaScript function to validate file sizes on the client side before they are sent to the server. If this is not updated, the browser will block the upload even if the server is configured correctly.

**File:** `collections/admin/specupload.php`
**Action:** Update the `verifyFileSize` function with the following logic to correctly handle PHP unit strings (G, M, K) and convert them to bytes:

```javascript
function verifyFileSize(inputObj) {
    inputObj.form.ulfnoverride.value = '';
    if (!window.FileReader) return;

    <?php
    $maxUploadStr = ini_get('upload_max_filesize');
    $unit = strtoupper(substr($maxUploadStr, -1));
    $maxUpload = (int)$maxUploadStr;

    // Convert PHP ini string to bytes for JS comparison
    switch($unit) {
        case 'G':
            $maxUpload *= 1024; // To MB
        case 'M':
            $maxUpload *= 1024; // To KB
        case 'K':
            $maxUpload *= 1024; // To Bytes
            break;
        default:
            $maxUpload *= 1048576; // Default to MB if no unit
    }'

### **Step 4: Apply Changes (Restart Server)**

After modifying your `php.ini` file and updating the application logic, the changes will **not** take effect until the Apache service is restarted. This step ensures that the PHP engine re-reads the configuration file and recognizes the new `upload_max_filesize` and `post_max_size` limits.

---

### **Execute the Restart**
Depending on your server environment, use one of the following commands in your terminal. Since your system information shows you are operating as the **root** user, the `sudo` command is not required.

* **Primary Command:**
    ```bash
    service apache2 restart
    ```
* **Alternative (if `service` is not found):**
    ```bash
    /etc/init.d/apache2 restart
    ```

---

### **Troubleshooting & Verification**
* **"sudo: not found":** If you receive this error, it confirms you are already logged in as **root**; continue by running the command without `sudo`[cite: 5, 241].
* **Docker Environments:** If your server is running inside a Docker container (common for `linuxkit` systems), you may need to restart the container from your host machine's terminal using: `docker restart <container_id>`.'
* **Confirm Success:** Refresh your `info.php` page in the browser and verify that the **Local Value** for `upload_max_filesize` now displays your updated limit (e.g., **512M**) instead of the default **2M**.
````
