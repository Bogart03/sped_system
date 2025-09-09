<?php
/**
 * TCPDF Loader
 * Place this file in your project root or include folder
 * Make sure you have TCPDF library downloaded from https://tcpdf.org/
 * Folder structure example:
 *  - tcpdf/
 *      - tcpdf.php
 *      - config/
 *      - fonts/
 *      - ...
 *  - tcpdf.php  (this loader file)
 */

// Define path to TCPDF library
define('TCPDF_PATH', __DIR__ . '/tcpdf/'); // Adjust if your folder is elsewhere

// Include main TCPDF library (search for tcpdf.php inside TCPDF_PATH)
require_once(TCPDF_PATH . 'tcpdf.php');
?>
