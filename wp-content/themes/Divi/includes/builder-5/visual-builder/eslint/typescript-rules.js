const typescriptRules = {
  'no-use-before-define':                    'off',
  '@typescript-eslint/no-use-before-define': ['error', { functions: false }],
  '@typescript-eslint/no-explicit-any':      'off',
  '@typescript-eslint/naming-convention':    [
    'error',

    /**
     * Group Selectors.
     *
     * @link https://github.com/typescript-eslint/typescript-eslint/blob/main/packages/eslint-plugin/docs/rules/naming-convention.md#group-selectors
     */
    {
      selector: 'variableLike',
      format:   ['camelCase'],
    },
    {
      selector: 'method',
      format:   ['camelCase'],
    },
    {
      selector: 'typeLike',
      format:   ['PascalCase'],
    },

    /**
     * Individual Selectors.
     * Note: Individual Selectors do not have higher priority or specificity than Group Selectors and they
     * overlap with Group Selectors. Please prefer Individual Selectors whenever possible.
     *
     * @link https://github.com/typescript-eslint/typescript-eslint/blob/main/packages/eslint-plugin/docs/rules/naming-convention.md#individual-selectors
     */
    {
      selector: 'import',
      format:   ['camelCase', 'PascalCase'],
    },
    {
      selector: 'classProperty',
      format:   ['camelCase', 'UPPER_CASE'],
    },
    {
      selector: 'variable',
      format:   ['camelCase', 'PascalCase', 'UPPER_CASE'],
    },
    {
      selector:          'parameter',
      format:            ['camelCase'],
      leadingUnderscore: 'allow',
    },
    {
      selector: 'accessor',
      format:   ['camelCase'],
    },
    {
      selector: 'enumMember',
      format:   ['camelCase'],
    },

    {
      selector: 'parameterProperty',
      format:   ['camelCase'],
    },
    {
      selector: 'typeParameter',
      format:   ['PascalCase'],
      prefix:   ['T'],
    },
    {
      selector: 'interface',
      format:   ['PascalCase'],
      custom:   { regex: '^I[A-Z]', match: false },
    },
  ],

  // Already enforced by @typescript-eslint/no-extra-semi.
  'no-extra-semi': 'off',

  // Already enforced by @typescript-eslint/no-unused-vars.
  'no-unused-vars': 'off',

  // Already enforced by tsc.
  'no-undef': 'off',

  // Temporarily disabling `react/prop-types` because it fails to recognize TS' type
  // for no reason when certain coding style is employed (eg. returned value of the
  // component has conditional before hand). The fate of this rule will be discussed
  // on the next dev meeting (March 2th, 2021).
  'react/prop-types': 'off',

  // Incompatible with tsc.
  'react/static-property-placement': 'off',

  // Typescript already has return type.
  'jsdoc/require-returns': 'off',

  // Typescript already has return type.
  'jsdoc/require-param': 'off',

  '@typescript-eslint/consistent-type-imports': [
    'error',
    {
      disallowTypeAnnotations: false,
      fixStyle:                'inline-type-imports',
    },
  ],

  // Enable namespace. Historycally, ES2015 module is preferred over namespace because namespace
  // was introduced as a way to prevent type collition with the actual code. However, namespace
  // in Divi 5 project is used to organize large type and grouping type into reasonable
  // structure which prevent type and interface names from being too long. For more information, see:
  // - https://github.com/typescript-eslint/typescript-eslint/issues/324#issuecomment-477399687
  // - https://elegantthemes.slack.com/archives/C015MC4NXHT/p1696032842991429
  '@typescript-eslint/no-namespace': 'off',
};

module.exports = { typescriptRules };
