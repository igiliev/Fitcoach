const { chain, find, isEmpty, remove } = require('lodash');


// The prop names that we want to transform and group under the `features` prop.
const PROP_NAMES_TO_TRANSFORM = ['sticky', 'responsive', 'hover'];

/**
 * Finds and extract target props from the JSX attributes.
 *
 * @param {Array} attributes Array of JSX attributes.
 * @param {object} jscodeshift The jscodeshift API.
 * @returns {object} - An ObjectExpression containing the feature props.
 */
function extractTargetProps(attributes, jscodeshift) {
  return jscodeshift.objectExpression(
    chain(attributes)
      .filter(attr => PROP_NAMES_TO_TRANSFORM.includes(attr.name.name))
      .map(attr => jscodeshift.objectProperty(jscodeshift.identifier(attr.name.name), attr.value.expression))
      .value(),
  );
}

/**
 * Extracts the existing `features` attribute from the array of JSX attributes.
 *
 * @param {Array} attributes Array of JSX attributes.
 * @returns {object} - The existing `features` JSX attribute, if it exists.
 */
function extractFeaturesProp(attributes) {
  return find(attributes, attr => 'features' === attr.name.name);
}

/**
 * Transforms the JSX `<FieldContainer />` instances in the code to group target props under `features` prop.
 *
 * @param {object} root The root of the AST.
 * @param {object} jscodeshift The jscodeshift API.
 */
function transform(root, jscodeshift) {
  root
    .find(jscodeshift.JSXOpeningElement, { name: { name: 'FieldContainer' } })
    .forEach(path => {
      const { attributes } = path.node;

      const targetProps  = extractTargetProps(attributes, jscodeshift);
      const featuresProp = extractFeaturesProp(attributes);

      // If no target props were found, skip.
      if (isEmpty(targetProps.properties)) {
        return;
      }

      if (featuresProp) {
        // If `features` prop already exists, merge the new props into it.
        featuresProp.value.expression.properties = [
          ...featuresProp.value.expression.properties,
          ...targetProps.properties,
        ];
      } else {
        // If no `features` prop exists, add one.
        attributes.push(jscodeshift.jsxAttribute(jscodeshift.jsxIdentifier('features'), jscodeshift.jsxExpressionContainer(targetProps)));
      }

      // Remove the old props from the attributes list.
      remove(attributes, attr => PROP_NAMES_TO_TRANSFORM.includes(attr.name.name));
    });
}

/**
 * The main export for jscodeshift to use.
 * Transforms the provided source code and returns the modified source.
 *
 * @param {object} fileInfo Information about the processed file.
 * @param {object} api The jscodeshift API.
 * @returns {string} - The transformed source code.
 */
module.exports = function main(fileInfo, api) {
  const { jscodeshift } = api;
  const root            = jscodeshift(fileInfo.source);

  transform(root, jscodeshift);

  return root.toSource({
    quote:      'single',
    useTabs:    false,
    wrapColumn: 120,
  });
};

module.exports.parser = 'tsx';
