const fs        = require('fs');
const path      = require('path');
const { glob }  = require('glob');
const jsPhpData = require('js-php-data');


/**
 * Generates metadata for all modules in PHP format.
 */
function generateAllModulesMetadataPhp() {
  const searchPattern = 'packages/module-library/src/components/**/module.json';
  const metadata      = {};

  glob(searchPattern, (error, files) => {
    if (error) {
      console.error(error);
    }

    files.forEach(fullFilePath => {
      // Get the module directory.
      const moduleDir = path.dirname(fullFilePath);

      // Define the base directory from which to derive the module name.
      const baseDir = path.join(process.cwd(), 'packages/module-library/src/components');

      // Set the module name.
      //
      // The module name in our file should match the relative path from the Module
      // Library components directory. This retains the modules names that had been
      // set prior to this update, while allowing for modules in subdirectories to
      // indicate this in their ID.
      //
      // @example
      //
      // ```
      // ['cta'] // src/components/cta/module.json
      // ['woocommerce-product-images'] // src/components/woocommerce/product-images/module.json
      // ```
      const moduleName = path.relative(baseDir, moduleDir).replace(/woocommerce\//g, 'woocommerce-');

      // Read the file content.
      const fileContent = fs.readFileSync(fullFilePath, 'utf8');

      // Parse the JSON content.
      const parsed = JSON.parse(fileContent);

      // Remove the _comment from the parsed object.
      const parsedWithoutComment = Object.keys(parsed).reduce((acc, key) => {
        if ('_comment' !== key) {
          acc[key] = parsed[key];
        }

        return acc;
      }, {});

      metadata[moduleName] = parsedWithoutComment;
    });

    // Get the keys of the metadata object.
    const metadataKeys = Object.keys(metadata);

    // Sort the metadata object by keys.
    const metadataSorted = metadataKeys.sort().reduce((accumulator, key) => {
      accumulator[key] = metadata[key];
      return accumulator;
    }, {});

    // Convert the metadata object to a PHP array string.
    const phpArrayString = jsPhpData(metadataSorted);

    // Define the server directory.
    const serverDir = path.resolve('../server');

    // Define the output path.
    const outputPath = path.join(serverDir, '_all_modules_metadata.php');

    // Write the PHP array string to the output path.
    fs.writeFile(outputPath, `<?php\n// phpcs:ignoreFile -- !!! THIS IS AN AUTOMATICALLY GENERATED FILE - DO NOT EDIT !!!\nreturn ${phpArrayString};\n`, () => {
      console.info(`Generated ${outputPath}`);
    });
  });
}

generateAllModulesMetadataPhp();
